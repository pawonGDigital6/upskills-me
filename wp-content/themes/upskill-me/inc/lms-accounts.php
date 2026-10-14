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
