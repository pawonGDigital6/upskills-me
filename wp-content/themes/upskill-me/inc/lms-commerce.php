<?php
/**
 * LMS commerce: how a course is sold.
 *
 * Native pieces only: a WooCommerce product sells the course (LearnDash's
 * WooCommerce module links a product, or each variation, to courses through
 * "Related courses"), and LearnDash Group Registration turns a purchase of N
 * seats into a group the buyer manages. The course page shows the product's
 * levels as the buy box and posts to WooCommerce's own add-to-cart.
 *
 * Model (assumption until the client confirms Q1/Q2, see the Phase 5 docs):
 * one variable product per training, attribute "Level" with the variations
 * Team Member, Manager, Director and All Levels; each variation's Related
 * courses are that level's course(s); quantity = seats.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_course_product' ) ) {
	/**
	 * The published product that sells a course (directly or via a variation).
	 *
	 * @param int $course_id Course ID.
	 * @return WC_Product|null
	 */
	function upskill_course_product( $course_id ) {
		static $cache = array();

		$course_id = (int) $course_id;

		if ( array_key_exists( $course_id, $cache ) ) {
			return $cache[ $course_id ];
		}

		$cache[ $course_id ] = null;

		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		// _related_course is a serialised array of IDs: match the exact integer.
		$ids = get_posts(
			array(
				'post_type'   => array( 'product', 'product_variation' ),
				'post_status' => 'publish',
				'numberposts' => 20,
				'fields'      => 'ids',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_related_course',
						'value'   => 'i:' . $course_id . ';',
						'compare' => 'LIKE',
					),
				),
			)
		);

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );

			if ( $product && $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( $product && 'publish' === $product->get_status() && $product->is_purchasable() ) {
				$cache[ $course_id ] = $product;
				break;
			}
		}

		return $cache[ $course_id ];
	}
}

if ( ! function_exists( 'upskill_product_course' ) ) {
	/**
	 * The course a product (or any of its variations) sells.
	 *
	 * @param WC_Product $product Product.
	 * @return int Course ID or 0.
	 */
	function upskill_product_course( $product ) {
		$ids = array( $product->get_id() );

		if ( $product->is_type( 'variable' ) ) {
			$ids = array_merge( $ids, $product->get_children() );
		}

		foreach ( $ids as $id ) {
			$courses = array_filter( array_map( 'intval', (array) get_post_meta( $id, '_related_course', true ) ) );

			if ( $courses ) {
				return (int) reset( $courses );
			}
		}

		return 0;
	}
}

if ( ! function_exists( 'upskill_product_levels' ) ) {
	/**
	 * The levels a product offers, cheapest first, the bundle ("All Levels") last.
	 *
	 * @param WC_Product $product Product.
	 * @return array[] { id (variation or product ID), attributes, label, price (float), price_html, text, bundle (bool) }
	 */
	function upskill_product_levels( $product ) {
		if ( ! $product->is_type( 'variable' ) ) {
			return array(
				array(
					'id'         => $product->get_id(),
					'attributes' => array(),
					'label'      => $product->get_name(),
					'price'      => (float) $product->get_price(),
					'price_html' => wc_price( $product->get_price() ),
					'text'       => '',
					'bundle'     => false,
				),
			);
		}

		$levels = array();

		foreach ( $product->get_available_variations( 'objects' ) as $variation ) {
			if ( ! $variation->is_purchasable() || ! $variation->is_in_stock() ) {
				continue;
			}

			$attributes = $variation->get_variation_attributes();
			$label      = implode( ' / ', array_filter( array_map( 'strval', array_values( $variation->get_attributes() ) ) ) );
			$courses    = array_filter( (array) get_post_meta( $variation->get_id(), '_related_course', true ) );

			$levels[] = array(
				'id'         => $variation->get_id(),
				'attributes' => $attributes,
				'label'      => $label ? $label : $variation->get_name(),
				'price'      => (float) $variation->get_price(),
				'price_html' => wc_price( $variation->get_price() ),
				'text'       => wp_strip_all_tags( $variation->get_description() ),
				// A variation selling more than one course, or named "All…", is the bundle.
				'bundle'     => count( $courses ) > 1 || 0 === stripos( $label, 'all' ),
			);
		}

		usort(
			$levels,
			static function ( $a, $b ) {
				return array( (int) $a['bundle'], $a['price'] ) <=> array( (int) $b['bundle'], $b['price'] );
			}
		);

		return $levels;
	}
}

if ( ! function_exists( 'upskill_product_redirect' ) ) {
	/**
	 * The course page is the product page: send the WooCommerce product page
	 * (and the empty shop) to the course or the training library.
	 *
	 * @return void
	 */
	function upskill_product_redirect() {
		if ( ! function_exists( 'is_product' ) ) {
			return;
		}

		if ( is_product() ) {
			$course = upskill_product_course( wc_get_product( get_queried_object_id() ) );

			if ( $course && 'publish' === get_post_status( $course ) ) {
				wp_safe_redirect( get_permalink( $course ), 301 );
				exit;
			}
		} elseif ( is_shop() && function_exists( 'upskill_library_page_url' ) && upskill_library_page_url() ) {
			wp_safe_redirect( upskill_library_page_url(), 302 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'upskill_product_redirect' );

if ( ! function_exists( 'upskill_course_duration' ) ) {
	/**
	 * Total running time of a course's sessions ("1 hr 24 min"), from the
	 * session "Duration" fields (m:ss or h:mm:ss).
	 *
	 * @param int[] $sessions Session IDs.
	 * @return string Empty when no durations are set.
	 */
	function upskill_course_duration( $sessions ) {
		$seconds = 0;

		foreach ( $sessions as $session ) {
			$parts = array_map( 'intval', explode( ':', (string) get_field( 'duration', $session ) ) );

			if ( count( $parts ) >= 2 ) {
				$seconds += 3 === count( $parts ) ? $parts[0] * 3600 + $parts[1] * 60 + $parts[2] : $parts[0] * 60 + $parts[1];
			}
		}

		if ( ! $seconds ) {
			return '';
		}

		$minutes = (int) round( $seconds / 60 );
		$hours   = intdiv( $minutes, 60 );

		/* translators: 1: hours, 2: minutes. */
		return $hours ? sprintf( __( '%1$d hr %2$d min', 'upskill-me' ), $hours, $minutes % 60 ) : sprintf( __( '%d min', 'upskill-me' ), max( 1, $minutes ) );
	}
}

if ( ! function_exists( 'upskill_thankyou_next_steps' ) ) {
	/**
	 * "What happens next" under WooCommerce's own order confirmation (Figma
	 * Post Checkout Flow 49325:20216): set up the team (group leaders) or start
	 * learning. The order details above it stay WooCommerce's.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	function upskill_thankyou_next_steps( $order_id ) {
		$order = $order_id ? wc_get_order( $order_id ) : null;

		if ( ! $order || $order->has_status( 'failed' ) ) {
			return;
		}

		get_template_part( 'template-parts/commerce/next-steps', null, array( 'order' => $order ) );
	}
}
add_action( 'woocommerce_thankyou', 'upskill_thankyou_next_steps', 20 );

if ( ! function_exists( 'upskill_account_page_redirect' ) ) {
	/**
	 * WooCommerce My Account is the billing area (orders, invoices, addresses,
	 * account details), not a dashboard. Its Dashboard tab sends the user to
	 * their LMS dashboard (organisation admin → /organisation/, team member →
	 * /my-learning/); visitors go to the "Log in" page instead of a second
	 * login form. Every endpoint (orders, view-order, edit-address,
	 * edit-account, lost-password, customer-logout) keeps working, so links in
	 * WooCommerce's emails still open.
	 *
	 * @return void
	 */
	function upskill_account_page_redirect() {
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || is_wc_endpoint_url() ) {
			return;
		}

		$url = is_user_logged_in() && function_exists( 'upskill_account_link' ) ? upskill_account_link()['url'] : wp_login_url();

		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}
}
add_action( 'template_redirect', 'upskill_account_page_redirect' );

/**
 * My Account → Addresses: billing only. Training is virtual, so a shipping
 * address is never used.
 */
add_filter(
	'woocommerce_my_account_get_addresses',
	static function ( $addresses ) {
		unset( $addresses['shipping'] );

		return $addresses;
	}
);

/**
 * My Account tabs: drop Dashboard (it redirects, above) and Downloads
 * (courses are videos in LearnDash, never WooCommerce downloads).
 */
add_filter(
	'woocommerce_account_menu_items',
	static function ( $items ) {
		unset( $items['dashboard'], $items['downloads'] );

		return $items;
	}
);
