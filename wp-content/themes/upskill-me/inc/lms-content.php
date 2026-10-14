<?php
/**
 * LMS content model: the specification's back-office classification.
 *
 * The client specification ("Admin - Uploading content") lists what is
 * recorded for every training video. LearnDash covers the core of it (title,
 * description, video file, featured image, supporting material, published or
 * draft); ACF "Session: Details" adds code, version, format, duration, key
 * points and notes. The public filters Industry, Category (the spec's
 * "discipline") and Level (the spec's "role") are in inc/taxonomies.php.
 *
 * This file adds the three remaining lists, used by the admin only (not shown
 * on the site, no archive pages):
 *   - training_type      Compliance / Performance (single select)
 *   - organisation_type  NFP / Private / Public / Government (multi select)
 *   - jurisdiction       Australia, NSW, NZ… (multi select)
 *
 * Like the public filters they sit on the LearnDash course, the thing that is
 * sold and assigned. They are edited through the ACF group "Course:
 * Classification" (acf-json/group_upskill_course_classification.json), which
 * enforces the single choice for Type; their own term screens are under
 * LearnDash LMS → Courses.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_lms_admin_taxonomies' ) ) {
	/**
	 * Admin-only course taxonomies: slug => [ singular, plural, starting terms ].
	 *
	 * The starting terms are the examples the specification gives; the
	 * administrator completes the lists.
	 *
	 * @return array
	 */
	function upskill_lms_admin_taxonomies() {
		return array(
			'training_type'     => array( __( 'Training type', 'upskill-me' ), __( 'Training types', 'upskill-me' ), array( 'Compliance', 'Performance' ) ),
			'organisation_type' => array( __( 'Organisation type', 'upskill-me' ), __( 'Organisation types', 'upskill-me' ), array( 'NFP', 'Private', 'Public', 'Government' ) ),
			'jurisdiction'      => array( __( 'Jurisdiction', 'upskill-me' ), __( 'Jurisdictions', 'upskill-me' ), array( 'Australia', 'NSW', 'New Zealand' ) ),
		);
	}
}

if ( ! function_exists( 'upskill_register_lms_admin_taxonomies' ) ) {
	/**
	 * Register the admin-only course taxonomies.
	 */
	function upskill_register_lms_admin_taxonomies() {
		foreach ( upskill_lms_admin_taxonomies() as $taxonomy => $names ) {
			list( $singular, $plural ) = $names;

			register_taxonomy(
				$taxonomy,
				array( 'sfwd-courses' ),
				array(
					'labels'            => array(
						'name'          => $plural,
						'singular_name' => $singular,
						/* translators: %s: taxonomy name. */
						'all_items'     => sprintf( __( 'All %s', 'upskill-me' ), $plural ),
						/* translators: %s: taxonomy name. */
						'edit_item'     => sprintf( __( 'Edit %s', 'upskill-me' ), $singular ),
						/* translators: %s: taxonomy name. */
						'add_new_item'  => sprintf( __( 'Add New %s', 'upskill-me' ), $singular ),
						'menu_name'     => $plural,
					),
					'public'            => false,
					'show_ui'           => true,
					'show_in_menu'      => true,
					'show_admin_column' => true,
					'show_in_rest'      => false,
					'hierarchical'      => true,
					'rewrite'           => false,
					'query_var'         => false,
					// Edited through the ACF "Course: Classification" group instead.
					'meta_box_cb'       => false,
				)
			);
		}
	}
}
add_action( 'init', 'upskill_register_lms_admin_taxonomies', 6 );

if ( ! function_exists( 'upskill_seed_lms_admin_terms' ) ) {
	/**
	 * Add the specification's example terms once, so the lists are not empty.
	 * Runs in the admin only and records that it has run; deleting a term
	 * later does not bring it back.
	 */
	function upskill_seed_lms_admin_terms() {
		if ( get_option( 'upskill_lms_admin_terms_seeded' ) ) {
			return;
		}

		foreach ( upskill_lms_admin_taxonomies() as $taxonomy => $names ) {
			foreach ( $names[2] as $term ) {
				if ( ! term_exists( $term, $taxonomy ) ) {
					wp_insert_term( $term, $taxonomy );
				}
			}
		}

		update_option( 'upskill_lms_admin_terms_seeded', 1, false );
	}
}
add_action( 'admin_init', 'upskill_seed_lms_admin_terms' );

if ( ! function_exists( 'upskill_session_notes_timestamp' ) ) {
	/**
	 * Session notes: stamp each new note with the date and time it was saved,
	 * and keep the list newest first, as the specification asks ("contains
	 * date and time stamp listed chronologically newest at top").
	 *
	 * Runs on the repeater type filter before ACF's own (priority 10), which
	 * saves the rows and replaces the value with their count.
	 *
	 * @param mixed      $rows    ACF repeater value (rows of sub field key => value).
	 * @param int|string $post_id Post ID.
	 * @param array      $field   Field settings.
	 * @return mixed
	 */
	function upskill_session_notes_timestamp( $rows, $post_id, $field ) {
		if ( 'field_upskill_session_notes' !== ( $field['key'] ?? '' ) || ! is_array( $rows ) ) {
			return $rows;
		}

		$date_key = 'field_upskill_session_note_date';
		$text_key = 'field_upskill_session_note_text';
		$now      = current_time( 'Y-m-d H:i:s' );

		$rows = array_values(
			array_filter(
				$rows,
				static function ( $row ) use ( $text_key ) {
					return isset( $row[ $text_key ] ) && '' !== trim( (string) $row[ $text_key ] );
				}
			)
		);

		foreach ( $rows as $i => $row ) {
			if ( empty( $row[ $date_key ] ) ) {
				$rows[ $i ][ $date_key ] = $now;
			}
		}

		usort(
			$rows,
			static function ( $a, $b ) use ( $date_key ) {
				return strcmp( (string) $b[ $date_key ], (string) $a[ $date_key ] );
			}
		);

		return $rows;
	}
}
add_filter( 'acf/update_value/type=repeater', 'upskill_session_notes_timestamp', 5, 3 );
