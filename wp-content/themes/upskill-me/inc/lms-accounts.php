<?php
/**
 * LMS accounts: one sign-in for learners and organisations.
 *
 * Sign-in, sign-up and password reset are LearnDash's own (LearnDash LMS →
 * Settings → Registration): its Registration page, Reset Password page and the
 * login handling it adds to every WordPress login form (a failed login comes
 * back to the form with ?login=failed). Where each role lands after signing in
 * is Group Registration's setting (LearnDash LMS → Group Registration
 * Settings → "Redirect users after successful login").
 *
 * The theme adds the Figma "Log in" page (template-login.php) and points
 * WordPress's login, lost-password, register and logout links at these pages,
 * so no screen falls back to wp-login.php or WooCommerce's My Account.
 *
 * Every page is found from its setting, never by ID: the login page is the
 * published page using the "Log in" template.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_login_page_id' ) ) {
	/**
	 * The published page using the "Log in" template, or 0.
	 *
	 * @return int
	 */
	function upskill_login_page_id() {
		static $id = null;

		if ( null === $id ) {
			$pages = get_posts(
				array(
					'post_type'              => 'page',
					'post_status'            => 'publish',
					'numberposts'            => 1,
					'fields'                 => 'ids',
					'meta_key'               => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one indexed lookup per request.
					'meta_value'             => 'template-login.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			$id = $pages ? (int) $pages[0] : 0;
		}

		return $id;
	}
}

if ( ! function_exists( 'upskill_login_destination' ) ) {
	/**
	 * Where a user goes after signing in when no page asked for them.
	 *
	 * Group Registration's redirect setting decides for organisation admins
	 * (Group Leaders) and learners (Subscribers); administrators go to the
	 * dashboard; anyone else (e.g. a WooCommerce customer) to the learner page
	 * Group Registration names, then the home page.
	 *
	 * @param WP_User $user Signed-in user.
	 * @return string
	 */
	function upskill_login_destination( $user ) {
		if ( user_can( $user, 'manage_options' ) ) {
			return admin_url();
		}

		$url = (string) apply_filters( 'login_redirect', '', '', $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter: lets Group Registration choose.

		if ( '' === $url || upskill_is_login_url( $url ) ) {
			$learner = (int) get_option( 'ldgr_redirect_group_user' );
			$url     = $learner ? (string) get_permalink( $learner ) : home_url( '/' );
		}

		return $url;
	}
}

if ( ! function_exists( 'upskill_account_link' ) ) {
	/**
	 * The user's account link (drawer button; the header icon when no menu
	 * rows exist). Signed out: the "Log in" page. Signed in: the user's
	 * landing page from upskill_login_destination() (Group Registration's
	 * redirect setting), so organisation admins open the organisation
	 * dashboard and team members My learning.
	 *
	 * @return array{title: string, url: string, target: string}
	 */
	function upskill_account_link() {
		$user = wp_get_current_user();

		if ( ! $user->exists() ) {
			return array(
				'title'  => __( 'Log in', 'upskill-me' ),
				'url'    => wp_login_url(),
				'target' => '',
			);
		}

		if ( user_can( $user, 'manage_options' ) ) {
			$title = __( 'Dashboard', 'upskill-me' );
		} elseif ( in_array( 'group_leader', (array) $user->roles, true ) ) {
			$title = __( 'Organisation Dashboard', 'upskill-me' );
		} else {
			$title = __( 'My Learning', 'upskill-me' );
		}

		return array(
			'title'  => $title,
			'url'    => upskill_login_destination( $user ),
			'target' => '',
		);
	}
}

if ( ! function_exists( 'upskill_account_menu_items' ) ) {
	/**
	 * Rows of the header account menu (Figma 49398:107717). Every row points
	 * at a LearnDash or Group Registration screen:
	 *   - signed out: the drawer's two sign-in links (Site Settings → Header →
	 *     Learner / Organisation sign in) and the training library;
	 *   - organisation admin (Group Leader): organisation dashboard, its
	 *     training/seats and team sections, Group Registration's enrolment;
	 *   - team member: My learning, its course list, LearnDash's Profile
	 *     (progress and certificates);
	 *   - site admin: wp-admin plus both dashboards.
	 *
	 * @return array[] { title, text, url }
	 */
	function upskill_account_menu_items() {
		$user = wp_get_current_user();

		if ( ! $user->exists() ) {
			$learner_signin = upskill_option( 'learner_signin' );
			$org_signin     = upskill_option( 'organisation_signin' );
			$items          = array(
				array( ! empty( $learner_signin['title'] ) ? $learner_signin['title'] : __( 'Learner Sign in', 'upskill-me' ), __( 'Continue your training', 'upskill-me' ), ! empty( $learner_signin['url'] ) ? $learner_signin['url'] : wp_login_url() ),
				array( ! empty( $org_signin['title'] ) ? $org_signin['title'] : __( 'Organisation Sign in', 'upskill-me' ), __( 'Manage your team’s training and seats', 'upskill-me' ), ! empty( $org_signin['url'] ) ? $org_signin['url'] : wp_login_url() ),
				array( __( 'Find Training', 'upskill-me' ), __( 'Training packages for your team', 'upskill-me' ), function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : '' ),
			);

			return upskill_account_menu_rows( $items );
		}

		$org     = function_exists( 'upskill_org_dashboard_page_id' ) && upskill_org_dashboard_page_id() ? (string) get_permalink( upskill_org_dashboard_page_id() ) : '';
		$learner = function_exists( 'upskill_learner_dashboard_url' ) ? upskill_learner_dashboard_url() : '';
		$items   = array();

		if ( user_can( $user, 'manage_options' ) ) {
			$items = array(
				array( __( 'Dashboard', 'upskill-me' ), __( 'Manage the site and its training', 'upskill-me' ), admin_url() ),
				array( __( 'Organisation', 'upskill-me' ), __( 'The organisation admin view', 'upskill-me' ), $org ),
				array( __( 'My Learning', 'upskill-me' ), __( 'The team member view', 'upskill-me' ), $learner ),
			);
		} elseif ( in_array( 'group_leader', (array) $user->roles, true ) ) {
			$items = array(
				array( __( 'Dashboard', 'upskill-me' ), __( 'Your training at a glance', 'upskill-me' ), $org ),
				array( __( 'Training & Seats', 'upskill-me' ), __( 'Courses and seats you have bought', 'upskill-me' ), $org ? $org . '#org-training' : '' ),
				array( __( 'Team Results', 'upskill-me' ), __( 'Track your team’s progress', 'upskill-me' ), $org ? $org . '#org-team' : '' ),
				array( __( 'Manage Team', 'upskill-me' ), __( 'Enrol and manage team members', 'upskill-me' ), function_exists( 'upskill_groups_dashboard_url' ) ? upskill_groups_dashboard_url() : '' ),
			);
		} else {
			$profile = function_exists( 'upskill_learndash_profile_url' ) ? upskill_learndash_profile_url() : '';
			$items   = array(
				array( __( 'Dashboard', 'upskill-me' ), __( 'Your training at a glance', 'upskill-me' ), $learner ),
				array( __( 'My Courses', 'upskill-me' ), __( 'Continue your learning journey', 'upskill-me' ), $learner ? $learner . '#current-courses' : '' ),
				array( __( 'Results', 'upskill-me' ), __( 'Track your learning progress', 'upskill-me' ), $profile ? $profile : ( $learner ? $learner . '#completed-courses' : '' ) ),
			);
		}

		return upskill_account_menu_rows( $items );
	}
}

if ( ! function_exists( 'upskill_account_menu_rows' ) ) {
	/**
	 * [ title, text, url ] lists to menu rows, dropping rows without a URL.
	 *
	 * @param array[] $items Rows as [ title, text, url ].
	 * @return array[] { title, text, url }
	 */
	function upskill_account_menu_rows( $items ) {
		$rows = array();

		foreach ( $items as $item ) {
			if ( $item[2] ) {
				$rows[] = array(
					'title' => $item[0],
					'text'  => $item[1],
					'url'   => $item[2],
				);
			}
		}

		return $rows;
	}
}

if ( ! function_exists( 'upskill_is_login_url' ) ) {
	/**
	 * Does the URL point at the login page (with any query string)?
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	function upskill_is_login_url( $url ) {
		$page = upskill_login_page_id();

		return $page && 0 === strpos( strtok( (string) $url, '?#' ), (string) get_permalink( $page ) );
	}
}

/**
 * Login links (WordPress, LearnDash and plugins call wp_login_url()) open the
 * "Log in" page. Re-authentication for wp-admin keeps wp-login.php.
 */
add_filter(
	'login_url',
	static function ( $login_url, $redirect, $force_reauth ) {
		$page = upskill_login_page_id();

		if ( ! $page || $force_reauth ) {
			return $login_url;
		}

		$url = get_permalink( $page );

		return $redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url ) : $url;
	},
	10,
	3
);

/**
 * "Forgot password" goes to LearnDash's Reset Password page, ahead of
 * WooCommerce's My Account endpoint (which filters at priority 10).
 */
add_filter(
	'lostpassword_url',
	static function ( $url ) {
		$page = function_exists( 'learndash_get_reset_password_page_id' ) ? learndash_get_reset_password_page_id() : 0;

		return $page ? get_permalink( $page ) : $url;
	},
	20
);

/**
 * "Sign up" goes to LearnDash's Registration page while sign-up is open
 * (Settings → General → Membership: "Anyone can register").
 */
add_filter(
	'register_url',
	static function ( $url ) {
		$page = function_exists( 'learndash_registration_page_get_id' ) ? learndash_registration_page_get_id() : 0;

		return $page ? get_permalink( $page ) : $url;
	}
);

/**
 * LearnDash's Registration page: "Already have an account? Log In" opens the
 * "Log in" page rather than a second, hidden login form.
 */
add_filter(
	'learndash_registration_login_link_redirect',
	static function ( $url ) {
		return upskill_login_page_id() ? wp_login_url() : $url;
	}
);

/**
 * The login form posts redirect_to = the login page carrying the real
 * destination (so a failed attempt returns to the form, which LearnDash
 * does). On success, unwrap it: a destination the visitor asked for (e.g. the
 * session they were opening) wins; otherwise the role's landing page.
 * Runs after Group Registration (10), which ignores the requested page.
 */
add_filter(
	'login_redirect',
	static function ( $redirect_to, $requested, $user ) {
		if ( ! $user instanceof WP_User || ! upskill_is_login_url( $requested ) ) {
			return $redirect_to;
		}

		$query = array();
		wp_parse_str( (string) wp_parse_url( $requested, PHP_URL_QUERY ), $query );
		$asked = isset( $query['redirect_to'] ) ? wp_validate_redirect( $query['redirect_to'], '' ) : '';

		if ( $asked && ! upskill_is_login_url( $asked ) ) {
			return $asked;
		}

		return upskill_login_redirect_landing( $redirect_to, $user );
	},
	20,
	3
);

if ( ! function_exists( 'upskill_login_redirect_landing' ) ) {
	/**
	 * The landing page when no destination was asked for: Group Registration's
	 * choice if it made one, else upskill_login_destination().
	 *
	 * @param string  $redirect_to Redirect after the earlier filters.
	 * @param WP_User $user        Signed-in user.
	 * @return string
	 */
	function upskill_login_redirect_landing( $redirect_to, $user ) {
		if ( $redirect_to && ! upskill_is_login_url( $redirect_to ) && ! user_can( $user, 'manage_options' ) ) {
			return $redirect_to;
		}

		return upskill_login_destination( $user );
	}
}

/**
 * Learners keep WordPress's own profile screen (name, email, password,
 * sessions), which the dashboard's "Profile" and "Password & Security" link
 * to. WooCommerce otherwise turns away everyone without editing rights from
 * wp-admin, sending them to its My Account page.
 */
add_filter(
	'woocommerce_prevent_admin_access',
	static function ( $prevent ) {
		global $pagenow;

		return 'profile.php' === $pagenow ? false : $prevent;
	}
);

/**
 * Logging out returns to the "Log in" page with a confirmation, instead of
 * wp-login.php.
 */
add_filter(
	'logout_redirect',
	static function ( $redirect_to, $requested ) {
		$page = upskill_login_page_id();

		if ( ! $page || $requested ) {
			return $redirect_to;
		}

		return add_query_arg( 'loggedout', 'true', get_permalink( $page ) );
	},
	10,
	2
);
