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
		// A level (variation) belongs to its training: the parent product's course is the training page.
		if ( $product->is_type( 'variation' ) ) {
			$parent  = wc_get_product( $product->get_parent_id() );
			$product = $parent ? $parent : $product;
		}

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

if ( ! function_exists( 'upskill_level_course_ids' ) ) {
	/**
	 * Level courses. A training sold at levels keeps its own course (the
	 * product's "Courses" field: its page and buy box) and has one course per
	 * level with that level's sessions (each variation's "Courses" field; All
	 * Levels lists them all). Level courses are opened only by the people
	 * enrolled in them, so course listings leave them out. Read from the
	 * plugins' own fields: a course linked from a variation and from no product.
	 *
	 * @return int[]
	 */
	function upskill_level_course_ids() {
		static $ids = null;

		if ( null !== $ids ) {
			return $ids;
		}

		global $wpdb;

		$rows   = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT p.post_type, m.meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE m.meta_key = '_related_course' AND p.post_type IN ( 'product', 'product_variation' ) AND p.post_status <> 'trash'"
		);
		$linked = array(
			'product'           => array(),
			'product_variation' => array(),
		);

		foreach ( $rows as $row ) {
			$linked[ $row->post_type ] = array_merge( $linked[ $row->post_type ], array_map( 'intval', (array) maybe_unserialize( $row->meta_value ) ) );
		}

		$ids = array_values( array_diff( array_unique( $linked['product_variation'] ), $linked['product'] ) );

		return $ids;
	}
}

/**
 * Category, industry and level "N sessions" chips count the courses in a
 * term; level courses are part of a training already counted.
 */
add_filter(
	'upskill_term_session_count',
	static function ( $count, $term ) {
		$levels = upskill_level_course_ids();

		if ( ! $levels || ! $term instanceof WP_Term ) {
			return $count;
		}

		$in_term = array_filter( $levels, static fn( $course_id ) => has_term( (int) $term->term_id, $term->taxonomy, $course_id ) );

		return max( 0, $count - count( $in_term ) );
	},
	10,
	2
);

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

/**
 * A level course opens for the people who have it (its learners and their
 * organisation admin). Anyone else (a visitor, a learner given another
 * level) goes to the training's page, which is the one to buy from or ask
 * about; the level course page would otherwise offer every level under one
 * level's name.
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! is_singular( 'sfwd-courses' ) || current_user_can( 'edit_posts' ) ) {
			return;
		}

		$course_id = (int) get_queried_object_id();

		if ( ! in_array( $course_id, upskill_level_course_ids(), true ) || ( is_user_logged_in() && sfwd_lms_has_access( $course_id, get_current_user_id() ) ) ) {
			return;
		}

		$product  = upskill_course_product( $course_id );
		$training = $product ? upskill_product_course( $product ) : 0;

		if ( $training && $training !== $course_id && 'publish' === get_post_status( $training ) ) {
			wp_safe_redirect( get_permalink( $training ), 302 );
			exit;
		}
	}
);

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

if ( ! function_exists( 'upskill_refund_group_note' ) ) {
	/**
	 * Refunded or cancelled seat orders: LearnDash removes the buyer's course
	 * access, but Group Registration leaves the group, its seats and its
	 * members untouched. Changing those is a judgement call (seats may come
	 * from several orders, members may be mid-course), so it stays a manual
	 * admin step; this adds an order note naming the group to check.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	function upskill_refund_group_note( $order_id ) {
		global $wpdb;

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$group_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_value = %s",
				$wpdb->esc_like( 'wdm_group_reg_order_id_' ) . '%',
				(string) $order->get_id()
			)
		);

		foreach ( $group_ids as $group_id ) {
			$order->add_order_note(
				sprintf(
					/* translators: 1: group name, 2: group ID, 3: members, 4: total seats. */
					__( 'Group "%1$s" (#%2$d) still has %3$d member(s) and %4$d seat(s). Check it in LearnDash LMS → Groups: reduce the seats or remove members if the refund should take access away.', 'upskill-me' ),
					get_the_title( $group_id ),
					$group_id,
					function_exists( 'learndash_get_groups_user_ids' ) ? count( learndash_get_groups_user_ids( $group_id ) ) : 0,
					(int) get_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, true )
				)
			);
		}
	}
}
add_action( 'woocommerce_order_refunded', 'upskill_refund_group_note' );
add_action( 'woocommerce_order_status_cancelled', 'upskill_refund_group_note' );

/**
 * A paid order of training (every line virtual) completes straight away, so
 * Group Registration sets up the group and seats without anyone approving it
 * (it acts on "Completed"). Only runs when a gateway reports a successful
 * payment (card/PayPal); bank transfer orders still wait "On hold" for the
 * Super Admin to confirm the money arrived.
 */
add_filter(
	'woocommerce_payment_complete_order_status',
	static function ( $status, $order_id, $order ) {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );

		if ( ! $order || 'processing' !== $status ) {
			return $status;
		}

		foreach ( $order->get_items() as $item ) {
			$product = $item instanceof WC_Order_Item_Product ? $item->get_product() : null;

			if ( ! $product || ! $product->is_virtual() ) {
				return $status;
			}
		}

		return 'completed';
	},
	10,
	3
);

/**
 * Group Registration saves "Group Name" on an order line twice for products
 * with levels (once from the group name, once from its per-variation copy,
 * which its "Autofill group name" setting fills with the same value), so
 * orders, emails and the admin order screen listed it twice. Only the
 * display drops an identical repeat; the stored meta, which Group
 * Registration reads back, is untouched.
 */
add_filter(
	'woocommerce_order_item_get_formatted_meta_data',
	static function ( $formatted_meta ) {
		$seen = array();

		foreach ( (array) $formatted_meta as $meta_id => $meta ) {
			$signature = $meta->key . "\0" . $meta->value;

			if ( isset( $seen[ $signature ] ) ) {
				unset( $formatted_meta[ $meta_id ] );
			} else {
				$seen[ $signature ] = true;
			}
		}

		return $formatted_meta;
	},
	20
);

/**
 * A group bought at a level is named "<training> — <level>". The buy box
 * names the group after the course, so two levels of one training gave two
 * groups with the same name on the groups dashboard, the organisation pages
 * and the course page. Group Registration applies this filter only when an
 * order line creates a new group (its own callback, priority 10, has already
 * set the "Group Name" from the line); extra seats for an existing group never
 * reach it. The level is read from the variation the line was paid for, so it
 * holds whatever path put the line in the cart (buy box, cart level switch).
 */
add_filter(
	'wdm_group_name',
	static function ( $group_title, $leader, $product_id, $order_id, $item ) {
		$variation = $item instanceof WC_Order_Item_Product && $item->get_variation_id() ? wc_get_product( $item->get_variation_id() ) : null;
		$level     = $variation && function_exists( 'upskill_store_item' ) ? upskill_store_item( $variation )['level'] : '';

		if ( '' === $level || str_ends_with( (string) $group_title, $level ) ) {
			return $group_title;
		}

		/* translators: 1: group (training) name, 2: level the seats were bought at. */
		return sprintf( __( '%1$s — %2$s', 'upskill-me' ), $group_title, $level );
	},
	20,
	5
);

/**
 * Buy box seat list (call 13 Nov, item 1): one row per level, each with its
 * own seats, and one "Add to Cart" for all of them. The form posts
 * `upskill_seats[<variation ID>] = <seats>`; every level above 0 becomes its
 * own cart line of the same product, so each still buys its own group at its
 * own price, exactly as when a level was bought on its own.
 *
 * Each line goes through WooCommerce's own add-to-cart with the request set
 * for that level, because Group Registration reads the level, its attributes
 * and the group name from the request in its cart data hooks. Its add-to-cart
 * validation is the one exception: it reads the level with filter_input(),
 * which only sees the original request (no single level here), so it is left
 * out for these lines and its two effects are set here instead (the group
 * name for the level, a new group). Other validation still runs, and the
 * level is checked against the product first. Runs just before WooCommerce's
 * handler (wp_loaded, 20), which then has nothing left to do.
 */
add_action(
	'wp_loaded',
	static function () {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce's add-to-cart takes no nonce either; every value is checked against the product.
		if ( empty( $_POST['upskill_seats'] ) || ! is_array( $_POST['upskill_seats'] ) || empty( $_REQUEST['add-to-cart'] ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$product_id = absint( wp_unslash( $_REQUEST['add-to-cart'] ) );
		$product    = wc_get_product( $product_id );
		$requested  = array_map( 'absint', wp_unslash( $_POST['upskill_seats'] ) );

		// Stop WooCommerce's own handler from adding the form's product a second time.
		unset( $_REQUEST['add-to-cart'], $_POST['add-to-cart'], $_GET['add-to-cart'] );

		if ( ! $product ) {
			return;
		}

		$levels = array();
		foreach ( upskill_product_levels( $product ) as $level ) {
			$levels[ (int) $level['id'] ] = $level;
		}

		// Group Registration's validation (see above), set aside for these lines only.
		$ldgr_validation = array();
		foreach ( (array) ( $GLOBALS['wp_filter']['woocommerce_add_to_cart_validation']->callbacks ?? array() ) as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) && 'handle_woo_add_to_cart_validation' === $callback['function'][1] ) {
					$ldgr_validation[] = array( $callback['function'], $priority, $callback['accepted_args'] );
					remove_filter( 'woocommerce_add_to_cart_validation', $callback['function'], $priority );
				}
			}
		}

		$added  = 0;
		$seats  = 0;
		$failed = false;

		foreach ( $requested as $level_id => $quantity ) {
			$level_id = (int) $level_id;

			if ( $quantity < 1 || ! isset( $levels[ $level_id ] ) ) {
				continue;
			}

			$quantity   = min( 999, $quantity );
			$variation  = $product->is_type( 'variable' ) ? $level_id : 0;
			$attributes = $levels[ $level_id ]['attributes'];

			// The request as a single-level purchase of this level would send it.
			$_POST['variation_id'] = $variation;
			$_POST['quantity']     = $quantity;
			foreach ( $attributes as $name => $value ) {
				$_POST[ $name ] = $value;
			}
			// What Group Registration's validation would set: a new group, and (with its "autofill group name" option on) the group name for this level.
			$_POST['ldgr_dynamic_option'] = 'create_new';
			$_POST['ldgr_dynamic_value']  = '';
			if ( $variation && isset( $_POST['ldgr_group_name'] ) && 'on' === get_option( 'ldgr_autofill_group_name' ) ) {
				$_POST[ 'ldgr_group_name_' . $variation ] = sanitize_text_field( wp_unslash( $_POST['ldgr_group_name'] ) );
			}

			$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation, $attributes );

			if ( $passed && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation, $attributes ) ) {
				++$added;
				$seats += $quantity;
			} else {
				$failed = true;
			}

			foreach ( array_keys( $attributes ) as $name ) {
				unset( $_POST[ $name ] );
			}
		}
		foreach ( $ldgr_validation as $callback ) {
			add_filter( 'woocommerce_add_to_cart_validation', $callback[0], $callback[1], $callback[2] );
		}
		// phpcs:enable

		if ( ! $added ) {
			if ( ! $failed ) {
				wc_add_notice( __( 'Add at least one seat to a level.', 'upskill-me' ), 'error' );
			}
			return;
		}

		wc_add_notice(
			sprintf(
				/* translators: 1: number of seats, 2: training name. */
				_n( '%1$s seat of %2$s added to your cart.', '%1$s seats of %2$s added to your cart.', $seats, 'upskill-me' ),
				number_format_i18n( $seats ),
				$product->get_name()
			)
		);

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	},
	19
);
