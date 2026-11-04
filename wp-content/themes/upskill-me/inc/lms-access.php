<?php
/**
 * LMS roles and access.
 *
 * Who is who:
 *   - Super Admin: WordPress administrator (manage_options). Builds courses,
 *     products and prices in wp-admin; has access to every course.
 *   - Organisation admin: LearnDash Group Leader (Group Registration makes
 *     the buyer one). Buys training and seats, manages their groups on the
 *     front end (/organisation/, /groups-dashboard/); opens only the courses
 *     of the groups they lead (LearnDash → Groups → Settings → Group Leader
 *     User Settings: "Managed Groups Auto-enrollment" on, "Course
 *     Auto-enrollment", "Bypass Course limits" and "Manage Courses" off).
 *   - Learner: a member of an organisation's group. Opens only the courses
 *     of their groups; training is bought by their organisation.
 *
 * Course and lesson access itself is LearnDash's (courses are "Closed": a
 * lesson URL of a course the user has no access to redirects to the course
 * page). The course page (learndash/ld30/course.php) then explains what to do.
 *
 * @package upskill-me
 */

if ( ! function_exists( 'upskill_is_org_admin' ) ) {
	/**
	 * Is the user an organisation admin (a Group Leader who is not a Super Admin)?
	 *
	 * @param int $user_id User ID (default: current user).
	 * @return bool
	 */
	function upskill_is_org_admin( $user_id = 0 ) {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

		return $user && $user->exists() && ! user_can( $user, 'manage_options' ) && in_array( 'group_leader', (array) $user->roles, true );
	}
}

if ( ! function_exists( 'upskill_is_learner' ) ) {
	/**
	 * Is the user a learner: a member of an organisation's group who neither
	 * leads a group nor administers the site? (A signed-in customer outside any
	 * group can still buy training and becomes an organisation admin.)
	 *
	 * @param int $user_id User ID (default: current user).
	 * @return bool
	 */
	function upskill_is_learner( $user_id = 0 ) {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

		if ( ! $user || ! $user->exists() || user_can( $user, 'manage_options' ) || upskill_is_org_admin( $user->ID ) ) {
			return false;
		}

		return function_exists( 'learndash_get_users_group_ids' ) && (bool) learndash_get_users_group_ids( $user->ID, true );
	}
}

/**
 * wp-admin is the Super Admin's (and shop staff's). Everyone else, Group
 * Leaders included, is sent to their dashboard on the site (WooCommerce's
 * redirect to My Account, which inc/lms-commerce.php forwards to the
 * organisation or learner dashboard). LearnDash lets Group Leaders in at
 * priority 20 (learndash_check_group_leader_access); this runs after it.
 * AJAX and admin-post requests are never blocked (WooCommerce skips them).
 */
add_filter(
	'woocommerce_prevent_admin_access',
	static function ( $prevent ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) || current_user_can( 'edit_posts' ) ) {
			return $prevent;
		}

		return true;
	},
	30
);

/**
 * No WordPress toolbar for the same people: it only leads into wp-admin.
 */
add_filter(
	'show_admin_bar',
	static function ( $show ) {
		if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return $show;
	},
	30
);

/**
 * Learners do not buy training: their organisation does. Stops a learner's
 * add to cart (the course page shows them a message instead of the buy box).
 */
add_filter(
	'woocommerce_add_to_cart_validation',
	static function ( $passed ) {
		if ( $passed && upskill_is_learner() ) {
			wc_add_notice( __( 'Training is bought by your organisation. Contact your organisation admin to request access.', 'upskill-me' ), 'error' );
			return false;
		}

		return $passed;
	},
	5
);
