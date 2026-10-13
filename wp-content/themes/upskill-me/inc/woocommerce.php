<?php
/**
 * WooCommerce integration.
 *
 * WooCommerce sells the training (LearnDash's WooCommerce module maps
 * products to courses; Group Registration turns a purchase with N seats into
 * a group the buyer enrols people into). Its front-end CSS/JS loads on every
 * view by default; the marketing pages use none of it, so it is removed again
 * outside the store, cart, checkout and account screens and pages that use a
 * WooCommerce block or shortcode.
 *
 * @package upskill-me
 */

if ( ! function_exists( 'upskill_needs_woocommerce_assets' ) ) {
	/**
	 * Does the current view render WooCommerce content?
	 *
	 * @return bool
	 */
	function upskill_needs_woocommerce_assets() {
		$needs = false;

		if ( function_exists( 'is_woocommerce' ) ) {
			if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
				$needs = true;
			} elseif ( is_singular() ) {
				$content = (string) get_post_field( 'post_content', get_queried_object_id() );
				$needs   = false !== strpos( $content, '<!-- wp:woocommerce/' )
					|| (bool) preg_match( '/\[(woocommerce_|product|add_to_cart|shop_messages|recent_products|featured_products|sale_products)/', $content );
			}
		}

		/**
		 * Filters whether WooCommerce front-end assets load on the current view.
		 *
		 * @param bool $needs True when the view renders WooCommerce content.
		 */
		return (bool) apply_filters( 'upskill_needs_woocommerce_assets', $needs );
	}
}

if ( ! function_exists( 'upskill_dequeue_woocommerce_assets' ) ) {
	/**
	 * Remove WooCommerce styles and scripts on views that do not use them.
	 */
	function upskill_dequeue_woocommerce_assets() {
		if ( ! class_exists( 'WooCommerce' ) || upskill_needs_woocommerce_assets() ) {
			return;
		}

		foreach ( array( wp_styles(), wp_scripts() ) as $assets ) {
			foreach ( $assets->queue as $handle ) {
				$src = isset( $assets->registered[ $handle ] ) ? (string) $assets->registered[ $handle ]->src : '';

				if ( false !== strpos( $src, '/plugins/woocommerce/' ) ) {
					$assets === wp_styles() ? wp_dequeue_style( $handle ) : wp_dequeue_script( $handle );
				}
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'upskill_dequeue_woocommerce_assets', 1000 );
add_action( 'wp_print_footer_scripts', 'upskill_dequeue_woocommerce_assets', 1 );
