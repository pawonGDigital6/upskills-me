<?php
/**
 * LMS learner: the data behind the learner dashboard ("My learning").
 *
 * Everything is read from LearnDash: enrolled courses, progress (completed /
 * total steps), status, completion date and the last activity it records. No
 * progress is stored by the theme.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_learner_courses' ) ) {
	/**
	 * The user's courses with their progress, grouped by status.
	 *
	 * @param int $user_id User ID.
	 * @return array {
	 *     @type array $current   Started or not started, most recent activity first.
	 *     @type array $completed Completed, most recently completed first.
	 *     @type int   $started   Number in progress (started, not finished).
	 *     @type int   $total     Number of courses the user can take.
	 * }
	 */
	function upskill_learner_courses( $user_id ) {
		$data = array(
			'current'   => array(),
			'completed' => array(),
			'started'   => 0,
			'total'     => 0,
		);

		if ( ! $user_id || ! function_exists( 'learndash_user_get_enrolled_courses' ) ) {
			return $data;
		}

		foreach ( learndash_user_get_enrolled_courses( $user_id ) as $course_id ) {
			if ( 'publish' !== get_post_status( $course_id ) ) {
				continue;
			}

			$course = upskill_learner_course( $user_id, (int) $course_id );

			if ( 'completed' === $course['status'] ) {
				$data['completed'][] = $course;
			} else {
				$data['current'][] = $course;
				$data['started']  += 'in_progress' === $course['status'] ? 1 : 0;
			}
		}

		$data['total'] = count( $data['current'] ) + count( $data['completed'] );

		usort( $data['current'], static fn( $a, $b ) => $b['activity'] <=> $a['activity'] );
		usort( $data['completed'], static fn( $a, $b ) => $b['completed_on'] <=> $a['completed_on'] );

		return $data;
	}
}

if ( ! function_exists( 'upskill_learner_course' ) ) {
	/**
	 * One course as the dashboard shows it.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return array
	 */
	function upskill_learner_course( $user_id, $course_id ) {
		$total     = (int) learndash_get_course_steps_count( $course_id );
		$done      = (int) learndash_course_get_completed_steps( $user_id, $course_id );
		$status    = (string) learndash_course_status( $course_id, $user_id, true );
		$percent   = $total ? (int) round( min( $done, $total ) / $total * 100 ) : 0;
		$next_step = 'completed' === $status ? 0 : (int) learndash_user_progress_get_first_incomplete_step( $user_id, $course_id );
		$category  = get_the_terms( $course_id, 'training_category' );

		// LearnDash records an activity row per course; its updated time is the
		// learner's last activity on the course.
		$activity = learndash_get_user_activity(
			array(
				'user_id'       => $user_id,
				'post_id'       => $course_id,
				'course_id'     => $course_id,
				'activity_type' => 'course',
			)
		);
		$last     = $activity ? (int) max( (int) $activity->activity_updated, (int) $activity->activity_started ) : 0;

		return array(
			'id'           => $course_id,
			'title'        => get_the_title( $course_id ),
			'url'          => get_permalink( $course_id ),
			'continue_url' => $next_step ? learndash_get_step_permalink( $next_step, $course_id ) : get_permalink( $course_id ),
			'summary'      => upskill_learner_course_summary( $course_id ),
			'category'     => $category && ! is_wp_error( $category ) ? $category[0]->name : '',
			'status'       => $status ? $status : 'not_started',
			'percent'      => 'completed' === $status ? 100 : $percent,
			'activity'     => $last,
			'completed_on' => 'completed' === $status ? (int) learndash_user_get_course_completed_date( $user_id, $course_id ) : 0,
		);
	}
}

if ( ! function_exists( 'upskill_learner_course_summary' ) ) {
	/**
	 * A course's one-paragraph summary: its excerpt, else the start of its
	 * description.
	 *
	 * @param int $course_id Course ID.
	 * @return string Plain text.
	 */
	function upskill_learner_course_summary( $course_id ) {
		$post = get_post( $course_id );

		if ( ! $post ) {
			return '';
		}

		$text = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) );

		return wp_trim_words( $text, 28 );
	}
}

if ( ! function_exists( 'upskill_learner_dashboard_url' ) ) {
	/**
	 * The learner dashboard ("My learning") page URL, or '' when there is none.
	 *
	 * @return string
	 */
	function upskill_learner_dashboard_url() {
		$pages = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'numberposts'            => 1,
				'fields'                 => 'ids',
				'meta_key'               => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => 'template-learner-dashboard.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return $pages ? (string) get_permalink( $pages[0] ) : '';
	}
}

if ( ! function_exists( 'upskill_learndash_profile_url' ) ) {
	/**
	 * LearnDash's profile page (the page holding its Profile block or
	 * [ld_profile]), which lists every course with its status; '' if none.
	 *
	 * @return string
	 */
	function upskill_learndash_profile_url() {
		global $wpdb;

		$id = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one lookup on the dashboard only.
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND ( post_content LIKE '%<!-- wp:learndash/ld-profile%' OR post_content LIKE '%[ld_profile%' ) ORDER BY ID LIMIT 1"
		);

		return $id ? (string) get_permalink( $id ) : '';
	}
}

if ( ! function_exists( 'upskill_completed_course_for' ) ) {
	/**
	 * A course the user has completed, as the "Course completed" page shows
	 * it: the requested one if they completed it, else their latest completion.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Requested course ID (0 for the latest).
	 * @return array|null upskill_learner_course() row plus `level` and
	 *                    `certificate` (LearnDash certificate URL or ''), or null.
	 */
	function upskill_completed_course_for( $user_id, $course_id ) {
		if ( ! $user_id || ! function_exists( 'learndash_course_completed' ) ) {
			return null;
		}

		if ( ! $course_id || ! learndash_course_completed( $user_id, $course_id ) ) {
			$completed = upskill_learner_courses( $user_id )['completed'];
			$course_id = $completed ? $completed[0]['id'] : 0;
		}

		if ( ! $course_id ) {
			return null;
		}

		$course                = upskill_learner_course( $user_id, $course_id );
		$level                 = get_the_terms( $course_id, 'cohort' );
		$course['level']       = $level && ! is_wp_error( $level ) ? $level[0]->name : '';
		$course['certificate'] = (string) learndash_get_course_certificate_link( $course_id, $user_id );

		return $course;
	}
}

/**
 * LearnDash redirects to the "Global Course Completion Page" without saying
 * which course was finished; add it, so that page can show the course.
 */
add_filter(
	'learndash_course_completion_url',
	static function ( $url, $course_id, $page_id = 0 ) {
		if ( $page_id && 'template-course-completed.php' === get_page_template_slug( $page_id ) ) {
			$url = add_query_arg( 'course', (int) $course_id, $url );
		}

		return $url;
	},
	10,
	3
);

/**
 * The dashboard and the "Course completed" page are for signed-in people:
 * anyone else is sent to the Log in page and brought back afterwards.
 */
add_action(
	'template_redirect',
	static function () {
		if ( is_page_template( array( 'template-learner-dashboard.php', 'template-course-completed.php' ) ) && ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( get_permalink() ) );
			exit;
		}
	}
);
