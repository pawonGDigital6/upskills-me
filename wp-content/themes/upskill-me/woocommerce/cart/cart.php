<?php
/**
 * Cart page: one block per course (review call 19 Nov, item 3).
 *
 * Theme override of woocommerce/templates/cart/cart.php (WooCommerce 11.2.0).
 * Only the line loop differs: the lines of one course are drawn as one block,
 * the course (picture, category, title, summary) once, then a row per level
 * with its seats stepper, line price and "Remove", then the course total.
 * Every level is still its own WooCommerce cart line, so prices, checkout,
 * the order and Group Registration's one team per line are unchanged.
 * Layout: the review's proposed cart (a bordered block; level, seats stepper,
 * line price; course total), plus a small "Remove" icon per level.
 * The form, quantity inputs, remove links, nonce, "Update cart" button and
 * every WooCommerce hook and filter are kept as in WooCommerce's template.
 *
 * When WooCommerce updates its cart.php (WooCommerce → Status lists this file
 * as outdated), compare the two and carry the change over.
 *
 * Grouping: upskill_cart_packages() (inc/lms-store.php). Styles:
 * src/global/scss/plugins/woocommerce/_store.scss. Course block and level
 * row: template-parts/commerce/cart-item.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package upskill-me
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" class="product-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove item', 'woocommerce' ); ?></span></th>
				<th scope="col" class="product-thumbnail"><span class="screen-reader-text"><?php esc_html_e( 'Thumbnail image', 'woocommerce' ); ?></span></th>
				<th scope="col" class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th scope="col" class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
				<th scope="col" class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
				<th scope="col" class="product-subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			$upskill_cart = WC()->cart->get_cart();

			foreach ( upskill_cart_packages() as $upskill_package ) {
				$upskill_rows  = array();
				$upskill_seats = 0;
				$upskill_total = 0.0;

				foreach ( $upskill_package['keys'] as $cart_item_key ) {
					$cart_item  = $upskill_cart[ $cart_item_key ];
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					/** This filter is documented in woocommerce/templates/cart/cart.php. */
					$visible = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

					if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
						$upskill_rows[ $cart_item_key ] = array( $cart_item, $_product, $product_id );
						$upskill_seats                 += (int) $cart_item['quantity'];
						$upskill_total                 += (float) $cart_item['line_subtotal'] + ( WC()->cart->display_prices_including_tax() ? (float) $cart_item['line_subtotal_tax'] : 0 );
					}
				}

				if ( ! $upskill_rows ) {
					continue;
				}

				// The course, once: picture, category, title and summary.
				$upskill_first     = reset( $upskill_rows );
				$upskill_first_key = key( $upskill_rows );
				$upskill_permalink = apply_filters( 'woocommerce_cart_item_permalink', $upskill_first[1]->is_visible() ? $upskill_first[1]->get_permalink( $upskill_first[0] ) : '', $upskill_first[0], $upskill_first_key );
				/** This filter is documented in woocommerce/templates/cart/cart.php. */
				$upskill_thumb = apply_filters( 'woocommerce_cart_item_thumbnail', $upskill_first[1]->get_image(), $upskill_first[0], $upskill_first_key );
				?>
				<tr class="cart-group cart-group__head" data-cart-package>
					<td class="product-thumbnail">
						<?php
						if ( ! $upskill_permalink ) {
							echo $upskill_thumb; // PHPCS: XSS ok.
						} else {
							printf( '<a href="%s" tabindex="-1" aria-hidden="true">%s</a>', esc_url( $upskill_permalink ), $upskill_thumb ); // PHPCS: XSS ok.
						}
						?>
					</td>
					<td class="cart-group__course" colspan="5">
						<?php
						get_template_part(
							'template-parts/commerce/cart-item',
							null,
							array(
								'part'      => 'course',
								'item'      => upskill_store_item( $upskill_first[1] ),
								'cart_item' => $upskill_first[0],
								'key'       => $upskill_first_key,
							)
						);
						?>
					</td>
				</tr>
				<?php
				// A row per level (each a WooCommerce cart line).
				foreach ( $upskill_rows as $cart_item_key => list( $cart_item, $_product, $product_id ) ) {
					$cart_item_name = WC()->cart->get_item_product_name( $cart_item, $_product );

					/** This filter is documented in woocommerce/templates/cart/cart.php. */
					$product_name = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
					$upskill_item  = upskill_store_item( $_product );
					$upskill_label = $upskill_item['level'] ? $upskill_item['level'] . ' – ' . $upskill_item['title'] : $upskill_item['title'];
					?>
					<tr class="woocommerce-cart-form__cart-item cart-group__level <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<td role="rowheader" class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
							<?php
							get_template_part(
								'template-parts/commerce/cart-item',
								null,
								array(
									'part'      => 'level',
									'item'      => $upskill_item,
									'cart_item' => $cart_item,
									'key'       => $cart_item_key,
								)
							);

							do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

							// Meta data.
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name );

							// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
							}
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
						<?php
						if ( $_product->is_sold_individually() ) {
							$min_quantity = 1;
							$max_quantity = 1;
						} else {
							$min_quantity = 0;
							$max_quantity = $_product->get_max_purchase_quantity();
						}

						$product_quantity = woocommerce_quantity_input(
							array(
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $max_quantity,
								'min_value'    => $min_quantity,
								// The input's label: "Team Leader – Course title" (the filtered name above is the whole course block).
								'product_name' => $upskill_label,
							),
							$_product,
							false
						);

						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
						?>
						</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>

						<td class="product-remove">
							<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
							?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr class="cart-group cart-group__total">
					<td class="cart-group__total-label" colspan="2">
						<?php
						/* translators: %s: number of seats, e.g. "7 seats". */
						echo esc_html( sprintf( __( 'Course total · %s', 'upskill-me' ), sprintf( _n( '%s seat', '%s seats', $upskill_seats, 'upskill-me' ), number_format_i18n( $upskill_seats ) ) ) );
						?>
					</td>
					<td class="cart-group__total-price" colspan="4"><?php echo wp_kses_post( wc_price( $upskill_total ) ); ?></td>
				</tr>
				<?php
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<tr>
				<td colspan="6" class="actions">

					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
