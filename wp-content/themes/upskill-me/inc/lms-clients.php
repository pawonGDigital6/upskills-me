<?php
/**
 * Client portals (Figma "RSL page": Log in 49315:68443, Product Detail 49315:20130).
 *
 * A client organisation (e.g. RSL Queensland) gets a branded sign-in page: a
 * page using the "Client log in" template (template-client-login.php) with the
 * client's name and logo (ACF "Page: Client portal"). Everything else is the
 * normal site: same courses, prices, checkout and dashboards.
 *
 * Anyone who opens that page, or signs in there, is co-branded for that
 * client: their browser keeps a cookie, and once signed in the choice is saved
 * on their account (ACF "User: Client portal", editable by administrators).
 * Co-branded people see the client's logo beside Upskillme in the header, and
 * the logos and client name in the course buy box (learndash/ld30/course.php).
 * A new client is a new page, not new code.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const UPSKILL_CLIENT_COOKIE   = 'upskill_client';
const UPSKILL_CLIENT_META     = 'upskill_client';
const UPSKILL_CLIENT_TEMPLATE = 'template-client-login.php';

if ( ! function_exists( 'upskill_is_client_page' ) ) {
	/**
	 * Is this a published client sign-in page?
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	function upskill_is_client_page( $page_id ) {
		$page_id = (int) $page_id;

		return $page_id > 0
			&& 'publish' === get_post_status( $page_id )
			&& UPSKILL_CLIENT_TEMPLATE === get_page_template_slug( $page_id );
	}
}

if ( ! function_exists( 'upskill_client' ) ) {
	/**
	 * A client portal's details.
	 *
	 * @param int $page_id Client sign-in page ID.
	 * @return array|null { id, name, logo (attachment ID), url }, or null when it is not a client page.
	 */
	function upskill_client( $page_id ) {
		if ( ! upskill_is_client_page( $page_id ) ) {
			return null;
		}

		$name = function_exists( 'get_field' ) ? trim( (string) get_field( 'client_name', $page_id ) ) : '';

		return array(
			'id'   => (int) $page_id,
			'name' => '' !== $name ? $name : get_the_title( $page_id ),
			'logo' => function_exists( 'get_field' ) ? (int) get_field( 'client_logo', $page_id ) : 0,
			'url'  => get_permalink( $page_id ),
		);
	}
}

if ( ! function_exists( 'upskill_current_client' ) ) {
	/**
	 * The client that co-brands the site for the current visitor: the one saved
	 * on their account, else the one their browser came through.
	 *
	 * @return array|null upskill_client() row, or null.
	 */
	function upskill_current_client() {
		$page_id = is_user_logged_in() ? (int) get_user_meta( get_current_user_id(), UPSKILL_CLIENT_META, true ) : 0;

		if ( ! $page_id && isset( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] ) ) {
			$page_id = absint( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] );
		}

		return $page_id ? upskill_client( $page_id ) : null;
	}
}

if ( ! function_exists( 'upskill_client_logo' ) ) {
	/**
	 * The client's logo image (responsive, from the media library).
	 *
	 * @param array  $client upskill_client() row.
	 * @param string $class  Classes for the <img>.
	 * @param string $sizes  The sizes attribute (its rendered width).
	 */
	function upskill_client_logo( $client, $class = '', $sizes = '100px' ) {
		if ( empty( $client['logo'] ) ) {
			return;
		}

		echo wp_get_attachment_image(
			$client['logo'],
			'medium',
			false,
			array(
				'class'    => $class,
				'alt'      => $client['name'],
				'sizes'    => $sizes,
				'loading'  => 'eager',
				'decoding' => 'async',
			)
		);
	}
}

if ( ! function_exists( 'upskill_remember_client' ) ) {
	/**
	 * Remember a client for this browser and, when signed in, on the account.
	 *
	 * @param int $page_id Client sign-in page ID.
	 * @param int $user_id User to save it on (0: none).
	 */
	function upskill_remember_client( $page_id, $user_id = 0 ) {
		if ( ! headers_sent() ) {
			setcookie(
				UPSKILL_CLIENT_COOKIE,
				(string) (int) $page_id,
				array(
					'expires'  => 0, // Until the browser closes; signed-in people keep the client on their account.
					'path'     => COOKIEPATH ? COOKIEPATH : '/',
					'domain'   => COOKIE_DOMAIN,
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}

		$_COOKIE[ UPSKILL_CLIENT_COOKIE ] = (string) (int) $page_id;

		if ( $user_id && (int) get_user_meta( $user_id, UPSKILL_CLIENT_META, true ) !== (int) $page_id ) {
			update_user_meta( $user_id, UPSKILL_CLIENT_META, (int) $page_id );
		}
	}
}

if ( ! function_exists( 'upskill_forget_client' ) ) {
	/**
	 * Return this browser, and optionally an account, to the normal site.
	 *
	 * @param int $user_id User to clear the saved client from (0: browser only).
	 */
	function upskill_forget_client( $user_id = 0 ) {
		if ( isset( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] ) && ! headers_sent() ) {
			setcookie(
				UPSKILL_CLIENT_COOKIE,
				'',
				array(
					'expires'  => time() - YEAR_IN_SECONDS,
					'path'     => COOKIEPATH ? COOKIEPATH : '/',
					'domain'   => COOKIE_DOMAIN,
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}

		unset( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] );

		if ( $user_id ) {
			delete_user_meta( $user_id, UPSKILL_CLIENT_META );
		}
	}
}

if ( ! function_exists( 'upskill_client_switch_url' ) ) {
	/**
	 * Link that takes a signed-in person back to the normal site (clears the
	 * client from their account and browser). Nonce-protected.
	 *
	 * @return string
	 */
	function upskill_client_switch_url() {
		return wp_nonce_url( add_query_arg( 'upskill_client', 'standard', home_url( '/' ) ), 'upskill_client_standard' );
	}
}

/**
 * Opening a client's sign-in page co-brands this browser only. The client is
 * saved on an account only when someone signs in through that page (wp_login
 * below), so staff or admins who merely open it while signed in aren't tied to it.
 */
add_action(
	'template_redirect',
	static function () {
		if ( is_page() && upskill_is_client_page( get_queried_object_id() ) ) {
			upskill_remember_client( get_queried_object_id() );
		}
	}
);

/**
 * "Switch to the standard site" (signed in): clear the client from the
 * account and the browser, then go to the home page.
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! isset( $_GET['upskill_client'] ) || 'standard' !== $_GET['upskill_client'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below.
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'upskill_client_standard' ) ) {
			return;
		}

		upskill_forget_client( get_current_user_id() );
		wp_safe_redirect( home_url( '/' ) );
		exit;
	},
	5
);

/**
 * Opening the standard "Log in" page (e.g. from a client page's "Use the
 * standard sign-in" link) returns this browser to the normal site. A client
 * saved on a signed-in person's account stays until they use "Switch to the
 * standard site" (upskill_client_switch_url()) or an administrator clears it
 * under Users → Edit → Client portal.
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! isset( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] ) || ! is_page() || 'template-login.php' !== get_page_template_slug( get_queried_object_id() ) ) {
			return;
		}

		upskill_forget_client();
	}
);

/**
 * Signing in after coming through a client page saves that client on the
 * account, so it follows the person to other browsers.
 */
add_action(
	'wp_login',
	static function ( $login, $user ) {
		$page_id = isset( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] ) ? absint( $_COOKIE[ UPSKILL_CLIENT_COOKIE ] ) : 0;

		if ( $user instanceof WP_User && upskill_is_client_page( $page_id ) ) {
			upskill_remember_client( $page_id, $user->ID );
		}
	},
	10,
	2
);

/**
 * Signing out returns a client's people to their own sign-in page.
 * Runs after the theme's default (the "Log in" page, inc/lms-accounts.php).
 */
add_filter(
	'logout_redirect',
	static function ( $redirect_to, $requested, $user ) {
		if ( $requested || ! $user instanceof WP_User ) {
			return $redirect_to;
		}

		$client = upskill_client( (int) get_user_meta( $user->ID, UPSKILL_CLIENT_META, true ) );

		return $client ? add_query_arg( 'loggedout', 'true', $client['url'] ) : $redirect_to;
	},
	20,
	3
);

/**
 * Client sign-in pages are unlisted: not indexed (WordPress and Rank Math
 * robots) and left out of Rank Math's sitemap.
 */
add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( is_page() && upskill_is_client_page( get_queried_object_id() ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}

		return $robots;
	}
);

add_filter(
	'rank_math/frontend/robots',
	static function ( $robots ) {
		if ( is_page() && upskill_is_client_page( get_queried_object_id() ) ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'nofollow';
		}

		return $robots;
	}
);

add_filter(
	'rank_math/sitemap/entry',
	static function ( $url, $type, $post ) {
		// Rank Math passes a plain row object here, not a WP_Post.
		return 'post' === $type && is_object( $post ) && isset( $post->ID ) && upskill_is_client_page( $post->ID ) ? false : $url;
	},
	10,
	3
);

/**
 * The "Client portal" user field lists client sign-in pages only.
 */
add_filter(
	'acf/fields/post_object/query/key=field_upskill_user_client',
	static function ( $args ) {
		$args['meta_key']   = '_wp_page_template'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin field lookup.
		$args['meta_value'] = UPSKILL_CLIENT_TEMPLATE; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value

		return $args;
	}
);
