<?php
/**
 * Store screens: cart, checkout and the order confirmation ("order received").
 *
 * Figma: Cart 49325:15876, Post Checkout Flow 49325:20216 (checkout has no
 * design; it follows the cart). WooCommerce's classic shortcodes and their
 * templates stay in charge (Group Registration relies on them, and payment,
 * validation and order processing are untouched); this file only adds to them
 * through WooCommerce's own hooks and filters:
 *
 * - Cart lines show the course (category, title, summary), its level as a
 *   "Cohort" select, "Access On demand", a seats stepper and "Remove".
 *   Changing the level swaps the line for the chosen level's variation with
 *   the same seats and Group Registration data (native cart API).
 * - The cart totals become the "Order summary" card (packages, seats,
 *   discount, Continue to Checkout, reassurance list).
 * - The order-received page gets the confirmation strip, "What you've
 *   purchased", "What happens next", "Need help?" and an order summary card,
 *   in place of WooCommerce's order details table.
 *
 * Styles: src/global/scss/plugins/woocommerce/_store.scss (plugin markup) and
 * Tailwind in template-parts/commerce/ (theme markup).
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_store_item' ) ) {
	/**
	 * What a cart or order line shows about its training.
	 *
	 * @param WC_Product|false $product The line's product (a variation for a level).
	 * @return array{course: int, title: string, url: string, level: string, category: string, summary: string}
	 */
	function upskill_store_item( $product ) {
		$item = array(
			'course'   => 0,
			'title'    => $product ? $product->get_name() : '',
			'url'      => $product ? $product->get_permalink() : '',
			'level'    => '',
			'category' => '',
			'summary'  => '',
		);

		if ( ! $product ) {
			return $item;
		}

		$parent = $product->is_type( 'variation' ) ? wc_get_product( $product->get_parent_id() ) : $product;

		if ( $parent ) {
			$item['title'] = $parent->get_name();
		}

		if ( $product->is_type( 'variation' ) ) {
			$item['level'] = implode( ' / ', array_filter( array_map( 'strval', $product->get_variation_attributes( false ) ) ) );
		}

		$course = function_exists( 'upskill_product_course' ) ? upskill_product_course( $product ) : 0;

		if ( $course ) {
			$terms           = get_the_terms( $course, 'training_category' );
			$item['course']  = $course;
			$item['title']   = get_the_title( $course );
			$item['url']     = get_permalink( $course );
			$item['category'] = is_array( $terms ) ? $terms[0]->name : '';
			$item['summary'] = function_exists( 'upskill_learner_course_summary' ) ? upskill_learner_course_summary( $course ) : '';
		}

		return $item;
	}
}

if ( ! function_exists( 'upskill_store_reassurance' ) ) {
	/**
	 * The short ticked list under an order summary.
	 *
	 * @param string $context cart|order.
	 * @return string[]
	 */
	function upskill_store_reassurance( $context = 'cart' ) {
		if ( 'order' === $context ) {
			// Figma repeats the cart's list here; after payment these are the relevant points.
			return array(
				__( 'Assign seats to your people from your organisation account.', 'upskill-me' ),
				__( 'Your people get their login details by email when you add them.', 'upskill-me' ),
				__( 'Follow progress and certificates from your dashboard.', 'upskill-me' ),
			);
		}

		return array(
			__( 'Three complete sessions are free to watch before you buy.', 'upskill-me' ),
			__( 'Assign seats to your people after checkout - no learner details needed now.', 'upskill-me' ),
			__( 'Change cohort depth per role at any point before payment.', 'upskill-me' ),
		);
	}
}

/*
 * ------------------------------------------------------------------------
 * Cart and checkout lines
 * ------------------------------------------------------------------------
 */

/**
 * Line name: the course block on the cart, title and level at checkout. Runs
 * after Group Registration's filter (which appends the group name; the group
 * data itself stays on the line and the order).
 */
add_filter(
	'woocommerce_cart_item_name',
	static function ( $name, $cart_item, $cart_item_key ) {
		if ( ! function_exists( 'is_cart' ) ) {
			return $name;
		}

		$item = upskill_store_item( $cart_item['data'] ?? false );

		if ( is_cart() ) {
			ob_start();
			get_template_part(
				'template-parts/commerce/cart-item',
				null,
				array(
					'part'      => 'name',
					'item'      => $item,
					'cart_item' => $cart_item,
					'key'       => $cart_item_key,
				)
			);
			return (string) ob_get_clean();
		}

		if ( is_checkout() ) {
			return '<span class="store-line__title">' . esc_html( $item['title'] ) . '</span>' . ( $item['level'] ? '<span class="store-line__level">' . esc_html( $item['level'] ) . '</span>' : '' );
		}

		return $name;
	},
	30,
	3
);

/**
 * Cart line "Cohort" select (form controls cannot go through the name
 * filter, which WooCommerce prints with wp_kses_post).
 */
add_action(
	'woocommerce_after_cart_item_name',
	static function ( $cart_item, $cart_item_key ) {
		get_template_part(
			'template-parts/commerce/cart-item',
			null,
			array(
				'part'      => 'cohort',
				'item'      => upskill_store_item( $cart_item['data'] ?? false ),
				'cart_item' => $cart_item,
				'key'       => $cart_item_key,
			)
		);
	},
	10,
	2
);

/**
 * Unit price reads "per seat".
 */
add_filter(
	'woocommerce_cart_item_price',
	static function ( $price ) {
		return is_cart() ? $price . ' <span class="cart-price__unit">' . esc_html__( 'per seat', 'upskill-me' ) . '</span>' : $price;
	}
);

/**
 * Cart seats: "Access On demand", a − / + stepper around WooCommerce's own
 * quantity input (js/components/cart.js), and "seats".
 */
add_filter(
	'woocommerce_cart_item_quantity',
	static function ( $quantity_html ) {
		if ( ! is_cart() || false === strpos( $quantity_html, 'input' ) || false !== strpos( $quantity_html, 'type="hidden"' ) ) {
			return $quantity_html;
		}

		return sprintf(
			'<div class="cart-seats"><span class="cart-seats__access">%1$s <strong>%2$s</strong></span><span class="cart-seats__stepper"><button class="cart-seats__step" type="button" data-seat-step="-1" aria-label="%3$s">&minus;</button>%4$s<button class="cart-seats__step" type="button" data-seat-step="1" aria-label="%5$s">+</button></span><span class="cart-seats__unit">%6$s</span></div>',
			esc_html__( 'Access', 'upskill-me' ),
			esc_html__( 'On demand', 'upskill-me' ),
			esc_attr__( 'Fewer seats', 'upskill-me' ),
			$quantity_html,
			esc_attr__( 'More seats', 'upskill-me' ),
			esc_html__( 'seats', 'upskill-me' )
		);
	},
	30
);

/**
 * "Remove" with a bin icon instead of WooCommerce's ×; the link (URL, nonce,
 * data attributes) is WooCommerce's own. Its label names the course only
 * (WooCommerce builds it from the whole line name).
 */
add_filter(
	'woocommerce_cart_item_remove_link',
	static function ( $link, $cart_item_key ) {
		$line  = WC()->cart->get_cart_item( $cart_item_key );
		$title = $line ? upskill_store_item( $line['data'] )['title'] : '';

		if ( $title ) {
			/* translators: %s: course title. */
			$link = preg_replace( '/aria-label="[^"]*"/', 'aria-label="' . esc_attr( sprintf( __( 'Remove %s from cart', 'upskill-me' ), $title ) ) . '"', $link );
		}

		return str_replace( '&times;', upskill_get_icon( 'bin', 'cart-remove__icon' ) . '<span class="cart-remove__label">' . esc_html__( 'Remove', 'upskill-me' ) . '</span>', $link );
	},
	10,
	2
);

if ( ! function_exists( 'upskill_cart_switch_levels' ) ) {
	/**
	 * Cart "Cohort" selects: swap a line for another level (variation) of the
	 * same product, keeping its seats and Group Registration data. Runs inside
	 * WooCommerce's own "Update cart" request (nonce checked by WooCommerce).
	 *
	 * @param bool $cart_updated Whether WooCommerce changed the cart.
	 * @return bool
	 */
	function upskill_cart_switch_levels( $cart_updated ) {
		$levels = isset( $_POST['upskill_level'] ) && is_array( $_POST['upskill_level'] ) ? wp_unslash( $_POST['upskill_level'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- WooCommerce verified the cart nonce; values are cast below.
		$cart   = WC()->cart;
		$done   = false;

		foreach ( $levels as $key => $variation_id ) {
			$key          = sanitize_key( $key );
			$variation_id = absint( $variation_id );
			$line         = $cart->get_cart_item( $key );

			if ( ! $line || ! $variation_id || (int) $line['variation_id'] === $variation_id ) {
				continue;
			}

			$variation = wc_get_product( $variation_id );

			// Only another purchasable level of the same product.
			if ( ! $variation || ! $variation->is_type( 'variation' ) || (int) $variation->get_parent_id() !== (int) $line['product_id'] || ! $variation->is_purchasable() ) {
				continue;
			}

			// Keep the line's own data (Group Registration's flag and group name), not WooCommerce's working keys.
			$data = array_diff_key( $line, array_flip( array( 'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax' ) ) );

			if ( isset( $data['ldgr_group_name'] ) ) {
				$data[ 'ldgr_group_name_' . $variation_id ] = $data['ldgr_group_name'];
			}

			$cart->remove_cart_item( $key );

			if ( $cart->add_to_cart( (int) $line['product_id'], (int) $line['quantity'], $variation_id, $variation->get_variation_attributes(), $data ) ) {
				$done = true;
			} else {
				// Put the original line back if the new level could not be added.
				$cart->restore_cart_item( $key );
			}
		}

		if ( $done ) {
			$GLOBALS['upskill_cart_level_switched'] = true;
			wc_add_notice( '<strong>' . esc_html__( 'Cohort updated.', 'upskill-me' ) . '</strong> ' . esc_html__( 'Your selection is saved. Seats and pricing update at checkout.', 'upskill-me' ), 'notice' );
		}

		return $cart_updated || $done;
	}
}
add_filter( 'woocommerce_update_cart_action_cart_updated', 'upskill_cart_switch_levels' );

/**
 * After a level switch, the "Cohort updated" notice replaces WooCommerce's
 * generic "Cart updated."
 */
add_filter(
	'woocommerce_add_success',
	static function ( $message ) {
		return ! empty( $GLOBALS['upskill_cart_level_switched'] ) && __( 'Cart updated.', 'woocommerce' ) === $message ? '' : $message; // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- comparing WooCommerce's own string.
	}
);

/**
 * Coupons are entered at checkout ("Have a coupon?"); the cart, as drawn,
 * has no coupon field. Applied coupons still show in the totals.
 */
add_filter(
	'woocommerce_coupons_enabled',
	static function ( $enabled ) {
		return $enabled && ! ( function_exists( 'is_cart' ) && is_cart() );
	}
);

/**
 * "Continue Browsing" under the cart lines.
 */
add_action(
	'woocommerce_after_cart_table',
	static function () {
		$url = function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : home_url( '/' );
		echo '<div class="cart-browse">';
		upskill_button(
			array(
				'url'   => $url,
				'title' => __( 'Continue Browsing', 'upskill-me' ),
			),
			array(
				'variant' => 'light',
				'class'   => 'min-h-[52px]',
			)
		);
		echo '</div>';
	}
);

/**
 * Order summary card: heading, packages and seats above WooCommerce's totals.
 */
add_action(
	'woocommerce_before_cart_totals',
	static function () {
		$cart  = WC()->cart;
		$lines = count( $cart->get_cart() );
		?>
		<h2 class="cart-summary__title"><?php esc_html_e( 'Order summary', 'upskill-me' ); ?></h2>
		<dl class="cart-summary__counts">
			<div><dt><?php esc_html_e( 'Packages', 'upskill-me' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $lines ) ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Seats', 'upskill-me' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $cart->get_cart_contents_count() ) ); ?></dd></div>
		</dl>
		<?php
	}
);

/**
 * "Discount -" while no coupon is applied (applied coupons show as
 * WooCommerce's own rows after the subtotal). Just before the total:
 * WooCommerce's earlier totals hooks only run for carts that ship.
 */
add_action(
	'woocommerce_cart_totals_before_order_total',
	static function () {
		if ( 'yes' !== get_option( 'woocommerce_enable_coupons' ) || WC()->cart->get_coupons() ) {
			return;
		}
		?>
		<tr class="cart-discount cart-discount--none">
			<th><?php esc_html_e( 'Discount', 'upskill-me' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Discount', 'upskill-me' ); ?>">-</td>
		</tr>
		<?php
	}
);

// "Continue to Checkout" in the site's button style, then the reassurance list.
remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
add_action(
	'woocommerce_proceed_to_checkout',
	static function () {
		upskill_button(
			array(
				'url'   => wc_get_checkout_url(),
				'title' => __( 'Continue to Checkout', 'upskill-me' ),
			),
			array( 'class' => 'cart-summary__checkout min-h-12 w-full justify-between text-base' )
		);
	},
	20
);
add_action(
	'woocommerce_after_cart_totals',
	static function () {
		get_template_part( 'template-parts/commerce/reassurance', null, array( 'context' => 'cart' ) );
	}
);

/**
 * Empty cart: back to the training library.
 */
add_filter(
	'woocommerce_return_to_shop_redirect',
	static function ( $url ) {
		return function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : $url;
	}
);
add_filter(
	'woocommerce_return_to_shop_text',
	static function () {
		return __( 'Browse Training', 'upskill-me' );
	}
);

/**
 * Checkout: organisations buy, so "Company name" reads "Organisation name"
 * (shown when WooCommerce → Settings → Customizer → Checkout sets the
 * company field to optional or required).
 */
add_filter(
	'woocommerce_default_address_fields',
	static function ( $fields ) {
		if ( isset( $fields['company'] ) ) {
			$fields['company']['label'] = __( 'Organisation name', 'upskill-me' );
		}

		return $fields;
	}
);

/*
 * ------------------------------------------------------------------------
 * Order received (after payment)
 * ------------------------------------------------------------------------
 */

if ( ! function_exists( 'upskill_received_order' ) ) {
	/**
	 * The order the "order received" page may show: the same checks as
	 * WooCommerce (order key in the URL, and the buyer logged in for an
	 * account order). For the page header only; WooCommerce renders the rest.
	 *
	 * @return WC_Order|null
	 */
	function upskill_received_order() {
		if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-received' ) ) {
			return null;
		}

		$order = wc_get_order( absint( get_query_var( 'order-received' ) ) );
		$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- order key lookup, as WooCommerce does.

		if ( ! $order instanceof WC_Order || ! hash_equals( $order->get_order_key(), $key ) ) {
			return null;
		}

		if ( $order->get_customer_id() && get_current_user_id() !== $order->get_customer_id() ) {
			return null;
		}

		return $order;
	}
}

/**
 * The confirmation strip and the order summary card open the page; the
 * WooCommerce "Thank you" line and its overview list give way to them.
 */
add_action(
	'woocommerce_before_thankyou',
	static function ( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || $order->has_status( 'failed' ) ) {
			return;
		}

		get_template_part( 'template-parts/commerce/order-summary', null, array( 'order' => $order ) );
		get_template_part( 'template-parts/commerce/order-overview', null, array( 'order' => $order ) );
	},
	5
);
add_filter(
	'woocommerce_thankyou_order_received_text',
	static function ( $text, $order ) {
		// Only when the page shows the order; an unknown order keeps WooCommerce's line.
		return $order instanceof WC_Order && ! $order->has_status( 'failed' ) ? '' : $text;
	},
	10,
	2
);

/**
 * "What you've purchased" in place of WooCommerce's order details table (the
 * full order, with the billing address, stays in My Account → Orders and the
 * order email).
 */
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
add_action(
	'woocommerce_thankyou',
	static function ( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order && ! $order->has_status( 'failed' ) ) {
			get_template_part( 'template-parts/commerce/order-items', null, array( 'order' => $order ) );
		}
	},
	10
);

/**
 * "Need help?" after "What happens next".
 */
add_action(
	'woocommerce_thankyou',
	static function ( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order && ! $order->has_status( 'failed' ) ) {
			get_template_part( 'template-parts/commerce/need-help' );
		}
	},
	30
);

/**
 * Checkout: no "Order notes" (delivery notes do not apply to online training).
 */
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

/**
 * Bank transfer instructions only while the order waits for payment
 * (WooCommerce prints them on every bank transfer confirmation).
 */
add_action(
	'woocommerce_thankyou_bacs',
	static function ( $order_id ) {
		$order    = wc_get_order( $order_id );
		$gateways = WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();

		if ( $order && $order->is_paid() && isset( $gateways['bacs'] ) ) {
			remove_action( 'woocommerce_thankyou_bacs', array( $gateways['bacs'], 'thankyou_page' ) );
		}
	},
	1
);
