<?php
/**
 * LearnDash integration.
 *
 * LearnDash enqueues its front-end CSS/JS (LD30 theme, course grid, blocks)
 * on every view. The marketing pages use none of it, so on views without
 * LearnDash content those assets are removed again, keeping those pages as
 * light as they were before LearnDash. LearnDash screens, and any page that
 * uses a LearnDash block or shortcode, keep them.
 *
 * @package upskill-me
 */

if ( ! function_exists( 'upskill_needs_learndash_assets' ) ) {
	/**
	 * Does the current view render LearnDash content?
	 *
	 * @return bool
	 */
	function upskill_needs_learndash_assets() {
		$needs = false;

		if ( function_exists( 'learndash_get_post_types' ) ) {
			$post_types = (array) learndash_get_post_types();

			if ( is_singular( $post_types ) || is_post_type_archive( $post_types ) ) {
				$needs = true;
			} elseif ( is_singular() ) {
				$content = (string) get_post_field( 'post_content', get_queried_object_id() );
				// LearnDash blocks (wp:learndash/...) and shortcodes ([ld_profile], [learndash_login], [course_content] ...).
				$needs = false !== strpos( $content, '<!-- wp:learndash/' )
					|| (bool) preg_match( '/\[(ld_|ld-|learndash|course_|lesson_|quiz|student|visitor|groupinfo|usermeta|user_groups)/', $content );
			}
		}

		/**
		 * Filters whether LearnDash front-end assets load on the current view.
		 *
		 * @param bool $needs True when the view renders LearnDash content.
		 */
		return (bool) apply_filters( 'upskill_needs_learndash_assets', $needs );
	}
}

if ( ! function_exists( 'upskill_dequeue_learndash_assets' ) ) {
	/**
	 * Remove LearnDash styles and scripts on views that do not use them.
	 */
	function upskill_dequeue_learndash_assets() {
		if ( ! defined( 'LEARNDASH_VERSION' ) || upskill_needs_learndash_assets() ) {
			return;
		}

		foreach ( array( wp_styles(), wp_scripts() ) as $assets ) {
			foreach ( $assets->queue as $handle ) {
				$src = isset( $assets->registered[ $handle ] ) ? (string) $assets->registered[ $handle ]->src : '';

				// The toolbar's LearnDash menu keeps its styling for logged-in editors.
				if ( false !== strpos( $src, '/plugins/sfwd-lms/' ) && ( false === strpos( $src, '/admin-bar/' ) || ! is_admin_bar_showing() ) ) {
					$assets === wp_styles() ? wp_dequeue_style( $handle ) : wp_dequeue_script( $handle );
				}
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'upskill_dequeue_learndash_assets', 1000 );

if ( ! function_exists( 'upskill_dequeue_orphan_jquery' ) ) {
	/**
	 * LearnDash's quiz library enqueues jQuery on every view. Once the
	 * LearnDash (and WooCommerce) assets are gone, drop it as well unless a
	 * script still on the page depends on it.
	 */
	function upskill_dequeue_orphan_jquery() {
		if ( ! defined( 'LEARNDASH_VERSION' ) || upskill_needs_learndash_assets() ) {
			return;
		}

		$scripts = wp_scripts();
		$pending = array_diff( $scripts->queue, array( 'jquery' ) );
		$seen    = array();

		// Walk every remaining script's dependency tree looking for jQuery.
		while ( $pending ) {
			$handle = array_shift( $pending );

			if ( isset( $seen[ $handle ] ) || ! isset( $scripts->registered[ $handle ] ) ) {
				continue;
			}

			$seen[ $handle ] = true;
			$deps            = (array) $scripts->registered[ $handle ]->deps;

			if ( array_intersect( $deps, array( 'jquery', 'jquery-core', 'jquery-migrate' ) ) ) {
				return;
			}

			$pending = array_merge( $pending, $deps );
		}

		wp_dequeue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', 'upskill_dequeue_orphan_jquery', 1001 );
add_action( 'wp_print_footer_scripts', 'upskill_dequeue_learndash_assets', 1 );
