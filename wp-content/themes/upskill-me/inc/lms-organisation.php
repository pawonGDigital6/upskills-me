<?php
/**
 * LMS organisations: what a group leader sees.
 *
 * A paid order makes the buyer a LearnDash Group Leader of a group with N seats
 * (Group Registration, Phase 5). The organisation dashboard
 * (template-org-dashboard.php) summarises those groups read-only from
 * LearnDash and Group Registration data: seats, members, course progress.
 * Enrolling, removing and reporting stay in Group Registration's own groups
 * dashboard ([wdm_group_users] on /groups-dashboard/), which is only restyled.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_org_dashboard_page_id' ) ) {
	/**
	 * The page using the organisation dashboard template.
	 *
	 * @return int
	 */
	function upskill_org_dashboard_page_id() {
		static $id = null;

		if ( null === $id ) {
			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
					'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => 'template-org-dashboard.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			$id    = $pages ? (int) $pages[0] : 0;
		}

		return $id;
	}
}

if ( ! function_exists( 'upskill_groups_dashboard_url' ) ) {
	/**
	 * Group Registration's groups dashboard (enrol and manage people). It picks
	 * a group from a POSTed wdm_group_id, so links to one group are small forms
	 * (template-org-dashboard.php).
	 *
	 * @return string
	 */
	function upskill_groups_dashboard_url() {
		$page = function_exists( 'ldgr_get_groups_dashboard_page' ) ? (int) ldgr_get_groups_dashboard_page() : 0;
		$url  = $page ? get_permalink( $page ) : home_url( '/groups-dashboard/' );

		return $url;
	}
}

if ( ! function_exists( 'upskill_org_groups' ) ) {
	/**
	 * Groups the user leads.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	function upskill_org_groups( $user_id ) {
		if ( ! function_exists( 'learndash_get_administrators_group_ids' ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'intval', (array) learndash_get_administrators_group_ids( $user_id ) ), static fn( $id ) => 'publish' === get_post_status( $id ) ) );
	}
}

if ( ! function_exists( 'upskill_org_progress' ) ) {
	/**
	 * One member's progress in one course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return array { percent (int), status: not_started|in_progress|completed, activity (int timestamp) }
	 */
	function upskill_org_progress( $user_id, $course_id ) {
		$status  = function_exists( 'learndash_course_status' ) ? learndash_course_status( $course_id, $user_id, true ) : 'not_started';
		$total   = (int) learndash_get_course_steps_count( $course_id );
		$done    = (int) learndash_course_get_completed_steps( $user_id, $course_id );
		$percent = 'completed' === $status ? 100 : ( $total ? (int) round( 100 * min( $done, $total ) / $total ) : 0 );
		$touched = (int) get_user_meta( $user_id, 'learndash_course_' . $course_id . '_enrolled_at', true );
		$last    = function_exists( 'learndash_activity_course_get_latest_completed_step' ) ? (array) learndash_activity_course_get_latest_completed_step( $user_id, $course_id ) : array();

		return array(
			'percent'  => $percent,
			'status'   => in_array( $status, array( 'completed', 'in_progress' ), true ) ? $status : ( $done ? 'in_progress' : 'not_started' ),
			'activity' => ! empty( $last['activity_completed'] ) ? (int) $last['activity_completed'] : $touched,
		);
	}
}

if ( ! function_exists( 'upskill_org_group_summary' ) ) {
	/**
	 * Seats, members and completion for one group.
	 *
	 * @param int $group_id Group ID.
	 * @return array { id, title, courses (int[]), members (int[]), seats, used, remaining, unlimited, completion (int %), rows (array[]) }
	 */
	function upskill_org_group_summary( $group_id ) {
		$courses   = array_map( 'intval', (array) learndash_group_enrolled_courses( $group_id ) );
		$members   = array_map( 'intval', (array) learndash_get_groups_user_ids( $group_id ) );
		$unlimited = (bool) get_post_meta( $group_id, 'ldgr_unlimited_seats', true );
		$total     = (int) get_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, true );
		$left      = (int) get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
		$seats     = $total ? $total : count( $members ) + $left;
		$rows      = array();
		$sum       = 0;

		foreach ( $members as $member ) {
			foreach ( $courses as $course ) {
				$progress = upskill_org_progress( $member, $course );
				$sum     += $progress['percent'];
				$rows[]   = array_merge( $progress, array( 'user' => $member, 'course' => $course ) );
			}
		}

		return array(
			'id'         => (int) $group_id,
			'title'      => get_the_title( $group_id ),
			'courses'    => $courses,
			'members'    => $members,
			'seats'      => $seats,
			'used'       => count( $members ),
			'remaining'  => max( 0, $unlimited ? 0 : $seats - count( $members ) ),
			'unlimited'  => $unlimited,
			'completion' => $rows ? (int) round( $sum / count( $rows ) ) : 0,
			'rows'       => $rows,
		);
	}
}

if ( ! function_exists( 'upskill_org_dashboard_guard' ) ) {
	/**
	 * The organisation dashboard is for group leaders (and administrators):
	 * visitors go to log in, other signed-in people to their learning.
	 *
	 * @return void
	 */
	function upskill_org_dashboard_guard() {
		if ( ! is_page_template( 'template-org-dashboard.php' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( get_permalink() ) );
			exit;
		}

		$user = wp_get_current_user();

		if ( ! current_user_can( 'manage_options' ) && ! in_array( 'group_leader', (array) $user->roles, true ) ) {
			wp_safe_redirect( function_exists( 'upskill_learner_dashboard_url' ) ? upskill_learner_dashboard_url() : home_url( '/' ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'upskill_org_dashboard_guard' );

if ( ! function_exists( 'upskill_group_registration_needs_assets' ) ) {
	/**
	 * Keep LearnDash's scripts (and jQuery) on pages with a Group Registration
	 * shortcode ([wdm_group_users], [ldgr_…]): its dashboard uses them, and
	 * inc/learndash.php only recognises LearnDash's own shortcodes.
	 *
	 * @param bool $needs Whether the view renders LearnDash content.
	 * @return bool
	 */
	function upskill_group_registration_needs_assets( $needs ) {
		if ( $needs || ! is_singular() ) {
			return $needs;
		}

		return (bool) preg_match( '/\[(wdm_|ldgr)/', (string) get_post_field( 'post_content', get_queried_object_id() ) );
	}
}
add_filter( 'upskill_needs_learndash_assets', 'upskill_group_registration_needs_assets' );
