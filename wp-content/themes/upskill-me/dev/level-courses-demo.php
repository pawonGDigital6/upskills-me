<?php
/**
 * Demo data: one LearnDash course per level for every training (feature
 * branch "feature/level-courses"). Data only. The theme code that keeps level
 * courses out of the listings lives in inc/lms-commerce.php and inc/lms-library.php.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/upskill-me/dev/level-courses-demo.php setup
 *   wp eval-file wp-content/themes/upskill-me/dev/level-courses-demo.php revert
 *
 * setup (it can be run again, and creates no duplicates):
 * - For every published variable product (a training), its own "Courses"
 *   field is the training course (its page and buy box).
 * - Each level variation (Team Member, Team Leader, Manager, Director)
 *   gets its own course "<training> — <level>". It copies the training's
 *   image, excerpt, category, industry, certificate, review and access settings.
 * - Content per level: a training with enough sessions shares them out
 *   between the levels (Shared Course Steps, no copies). Otherwise each
 *   level gets demo copies of the training's sessions, titled and coded by
 *   level (the same video, not marked free).
 * - The variation's "Courses" field becomes its level course; "All Levels"
 *   gets every level course.
 *
 * revert: every variation links the training course again, and the courses
 * and sessions this script created are moved to the trash.
 *
 * Everything created carries the meta `_upskill_level_demo`.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) || ! ( ( defined( 'WP_CLI' ) && WP_CLI ) || defined( 'UPSKILL_RUN_LEVEL_DEMO' ) ) ) {
	exit;
}

$upskill_mode = isset( $args[0] ) ? (string) $args[0] : 'setup';
$upskill_log  = static function ( $line ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $line );
	} else {
		echo $line, "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
};

// Client's level order (pricing email, 28 Oct; names from the client's 13 Nov answer): Team Member L1 … Director L4.
$upskill_level_no = array(
	'Team Member'      => 1,
	'Team Leader'      => 2,
	'Manager'          => 3,
	'Director'         => 4,
);

if ( ! function_exists( 'wc_get_products' ) || ! class_exists( 'LDLMS_Factory_Post' ) ) {
	$upskill_log( 'WooCommerce and LearnDash must be active.' );
	return;
}

if ( 'revert' === $upskill_mode ) {
	foreach ( wc_get_products( array( 'type' => 'variable', 'limit' => -1 ) ) as $upskill_product ) {
		$upskill_training = array_map( 'intval', (array) get_post_meta( $upskill_product->get_id(), '_related_course', true ) );

		foreach ( $upskill_product->get_children() as $upskill_variation ) {
			if ( $upskill_training && get_post_meta( $upskill_variation, '_upskill_level_demo_link', true ) ) {
				update_post_meta( $upskill_variation, '_related_course', $upskill_training );
				delete_post_meta( $upskill_variation, '_upskill_level_demo_link' );
			}
		}
	}

	$upskill_created = get_posts(
		array(
			'post_type'   => array( 'sfwd-courses', 'sfwd-lessons' ),
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_upskill_level_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	foreach ( $upskill_created as $upskill_id ) {
		wp_trash_post( $upskill_id );
	}

	$upskill_log( sprintf( 'Reverted: variations link their training course again; %d level courses and demo sessions moved to the trash.', count( $upskill_created ) ) );
	return;
}

// A copy of a session for one level: same video and fields, the level in the title and code, never free.
$upskill_copy_session = static function ( $session_id, $level, $number ) {
	$source = get_post( $session_id );
	$title  = preg_replace( '/^Session:\s*/', '', $source->post_title );
	$copy   = wp_insert_post(
		array(
			'post_type'    => 'sfwd-lessons',
			'post_status'  => 'publish',
			'post_title'   => sprintf( 'Session: %s (%s)', $title, $level ),
			'post_excerpt' => $source->post_excerpt,
			'post_content' => $source->post_content,
			'menu_order'   => $source->menu_order,
		),
		true
	);

	if ( is_wp_error( $copy ) ) {
		return 0;
	}

	foreach ( get_post_meta( $session_id ) as $key => $values ) {
		if ( in_array( $key, array( '_edit_lock', '_edit_last', 'course_id', 'lesson_id', '_sfwd-lessons' ), true ) || 0 === strpos( $key, 'ld_course_' ) || 0 === strpos( $key, 'rank_math' ) ) {
			continue;
		}
		update_post_meta( $copy, $key, maybe_unserialize( $values[0] ) );
	}

	$settings                              = (array) get_post_meta( $session_id, '_sfwd-lessons', true );
	$settings['sfwd-lessons_sample_lesson'] = '';
	$settings['sfwd-lessons_course']        = 0;
	update_post_meta( $copy, '_sfwd-lessons', $settings );

	$code = (string) get_post_meta( $session_id, 'session_code', true );
	if ( '' !== $code ) {
		update_post_meta( $copy, 'session_code', $code . '-L' . $number );
	}
	update_post_meta( $copy, '_upskill_level_demo', $session_id );

	return (int) $copy;
};

foreach ( wc_get_products( array( 'type' => 'variable', 'status' => 'publish', 'limit' => -1 ) ) as $upskill_product ) {
	$upskill_training = (int) current( array_map( 'intval', (array) get_post_meta( $upskill_product->get_id(), '_related_course', true ) ) );

	if ( ! $upskill_training || 'sfwd-courses' !== get_post_type( $upskill_training ) ) {
		$upskill_log( 'Skipped "' . $upskill_product->get_name() . '": its own Courses field has no training course.' );
		continue;
	}

	$upskill_sessions = array_map( 'intval', learndash_course_get_steps_by_type( $upskill_training, 'sfwd-lessons' ) );
	$upskill_levels   = array();
	$upskill_bundle   = 0;

	foreach ( $upskill_product->get_children() as $upskill_variation_id ) {
		$upskill_name = wc_get_product( $upskill_variation_id )->get_attribute( 'level' );

		if ( isset( $upskill_level_no[ $upskill_name ] ) ) {
			$upskill_levels[ $upskill_name ] = $upskill_variation_id;
		} elseif ( 0 === stripos( $upskill_name, 'all' ) ) {
			$upskill_bundle = $upskill_variation_id;
		}
	}

	uksort( $upskill_levels, static fn( $a, $b ) => $upskill_level_no[ $a ] <=> $upskill_level_no[ $b ] );
	// Sharing needs LearnDash's Shared Course Steps (Settings → Courses → Course Builder); without it a session would move out of the training course, so copies are used instead.
	$upskill_shared_steps = class_exists( 'LearnDash_Settings_Section' ) && 'yes' === LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' );
	$upskill_share        = $upskill_shared_steps && count( $upskill_sessions ) >= 2 * count( $upskill_levels ) ? (int) floor( count( $upskill_sessions ) / count( $upskill_levels ) ) : 0;
	$upskill_made  = array();
	$upskill_index = 0;

	foreach ( $upskill_levels as $upskill_level => $upskill_variation_id ) {
		$upskill_title = get_the_title( $upskill_training ) . ' — ' . $upskill_level;
		$upskill_title = html_entity_decode( $upskill_title, ENT_QUOTES, 'UTF-8' );

		// An existing level course: made by this script, or one with the same title (the 6 Nov pilot).
		$upskill_course = get_posts(
			array(
				'post_type'   => 'sfwd-courses',
				'post_status' => array( 'publish', 'draft', 'private' ),
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_upskill_level_demo',
						'value' => $upskill_variation_id,
					),
				),
			)
		);
		$upskill_course = $upskill_course ? (int) $upskill_course[0] : 0;

		if ( ! $upskill_course ) {
			$upskill_same = get_posts(
				array(
					'post_type'   => 'sfwd-courses',
					'post_status' => array( 'publish', 'draft', 'private' ),
					'title'       => $upskill_title,
					'numberposts' => 1,
					'fields'      => 'ids',
				)
			);
			$upskill_course = $upskill_same ? (int) $upskill_same[0] : 0;
		}

		$upskill_new = ! $upskill_course;

		if ( $upskill_new ) {
			$upskill_course = (int) wp_insert_post(
				array(
					'post_type'    => 'sfwd-courses',
					'post_status'  => 'publish',
					'post_title'   => $upskill_title,
					'post_excerpt' => get_post_field( 'post_excerpt', $upskill_training ),
				)
			);
		} else {
			wp_update_post(
				array(
					'ID'          => $upskill_course,
					'post_status' => 'publish',
				)
			);
		}

		update_post_meta( $upskill_course, '_upskill_level_demo', $upskill_variation_id );
		set_post_thumbnail( $upskill_course, get_post_thumbnail_id( $upskill_training ) );
		foreach ( array( 'industry', 'training_category' ) as $upskill_tax ) {
			wp_set_object_terms( $upskill_course, wp_get_post_terms( $upskill_training, $upskill_tax, array( 'fields' => 'ids' ) ), $upskill_tax );
		}
		foreach ( array( 'course_price_type', 'certificate', 'show_reviews' ) as $upskill_key ) {
			learndash_update_setting( $upskill_course, $upskill_key, learndash_get_setting( $upskill_training, $upskill_key ) );
		}

		// Sessions: keep a level course's existing ones (a re-run), else share or copy.
		$upskill_steps = array_map( 'intval', learndash_course_get_steps_by_type( $upskill_course, 'sfwd-lessons' ) );

		if ( ! $upskill_steps ) {
			if ( $upskill_share ) {
				$upskill_steps = array_slice( $upskill_sessions, $upskill_index * $upskill_share, $upskill_share );
			} else {
				foreach ( $upskill_sessions as $upskill_session ) {
					$upskill_steps[] = $upskill_copy_session( $upskill_session, $upskill_level, $upskill_level_no[ $upskill_level ] );
				}
			}

			$upskill_tree = array(
				'sfwd-lessons' => array(),
				'sfwd-quiz'    => array(),
			);
			foreach ( array_filter( $upskill_steps ) as $upskill_step ) {
				$upskill_tree['sfwd-lessons'][ $upskill_step ] = array(
					'sfwd-topic' => array(),
					'sfwd-quiz'  => array(),
				);
			}
			LDLMS_Factory_Post::course_steps( $upskill_course )->set_steps( $upskill_tree );
		}

		// A demo copy belongs to its level course only: its primary course (session page links, progression).
		foreach ( learndash_course_get_steps_by_type( $upskill_course, 'sfwd-lessons' ) as $upskill_step ) {
			if ( get_post_meta( $upskill_step, '_upskill_level_demo', true ) ) {
				learndash_update_setting( $upskill_step, 'course', $upskill_course );
				update_post_meta( $upskill_step, 'course_id', $upskill_course );
			}
		}

		if ( ! get_post_meta( $upskill_variation_id, '_upskill_level_demo_link', true ) ) {
			update_post_meta( $upskill_variation_id, '_upskill_level_demo_link', get_post_meta( $upskill_variation_id, '_related_course', true ) );
		}
		update_post_meta( $upskill_variation_id, '_related_course', array( $upskill_course ) );

		$upskill_made[ $upskill_level ] = $upskill_course;
		++$upskill_index;

		$upskill_log(
			sprintf(
				'%s course %d "%s": %d session(s) %s ← %s',
				$upskill_new ? 'Created' : 'Kept',
				$upskill_course,
				$upskill_title,
				count( learndash_course_get_steps_by_type( $upskill_course, 'sfwd-lessons' ) ),
				$upskill_share ? '(shared from the training)' : '(demo copies)',
				$upskill_level
			)
		);
	}

	if ( $upskill_bundle && $upskill_made ) {
		if ( ! get_post_meta( $upskill_bundle, '_upskill_level_demo_link', true ) ) {
			update_post_meta( $upskill_bundle, '_upskill_level_demo_link', get_post_meta( $upskill_bundle, '_related_course', true ) );
		}
		update_post_meta( $upskill_bundle, '_related_course', array_values( $upskill_made ) );
	}
}

$upskill_log( 'Done. The training courses and their products are unchanged; only the level variations link the level courses.' );
