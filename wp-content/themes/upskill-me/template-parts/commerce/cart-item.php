<?php
/**
 * Cart line: the course part of a cart row (Figma Cart 49325:15876).
 *
 * Category line, title (to the course page) and summary (part "name", the
 * woocommerce_cart_item_name filter, printed through wp_kses_post), then the
 * "Cohort" (level) select (part "cohort", woocommerce_after_cart_item_name,
 * which can hold form controls). The select's name is read by upskill_cart_switch_levels()
 * when WooCommerce's "Update cart" runs (js/components/cart.js submits it on
 * change). The seats stepper, line total and "Remove" are WooCommerce's own
 * cells, refined in inc/lms-store.php and woocommerce/_store.scss.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type string $part      name|cohort.
 *     @type array  $item      upskill_store_item() row.
 *     @type array  $cart_item WooCommerce cart line.
 *     @type string $key       Cart item key.
 * }
 */

$upskill_item   = $args['item'];
$upskill_line   = $args['cart_item'];
$upskill_key    = $args['key'];
$upskill_parent = ! empty( $upskill_line['variation_id'] ) ? wc_get_product( $upskill_line['product_id'] ) : null;
$upskill_levels = $upskill_parent && function_exists( 'upskill_product_levels' ) ? upskill_product_levels( $upskill_parent ) : array();

if ( 'name' === ( $args['part'] ?? 'name' ) ) :
	?>
<span class="cart-line">
	<span class="cart-line__category text-[10px] leading-[1.5] text-brand-700">
		<?php echo esc_html( trim( ( $upskill_item['category'] ? $upskill_item['category'] . ' · ' : '' ) . __( 'On-the-job training', 'upskill-me' ) ) ); ?>
	</span>
	<a class="cart-line__title block pt-1 text-base font-medium text-gray-900 hover:text-brand-700" href="<?php echo esc_url( $upskill_item['url'] ); ?>"><?php echo esc_html( $upskill_item['title'] ); ?></a>
	<?php if ( $upskill_item['summary'] ) : ?>
		<span class="cart-line__summary block pt-1.5 text-base text-gray-500"><?php echo esc_html( $upskill_item['summary'] ); ?></span>
	<?php endif; ?>
</span>
	<?php
	return;
endif;
?>
	<?php if ( count( $upskill_levels ) > 1 ) : ?>
		<span class="cart-line__cohort flex items-center gap-2.5 pt-4">
			<label class="text-sm text-gray-500" for="<?php echo esc_attr( 'cohort-' . $upskill_key ); ?>"><?php esc_html_e( 'Cohort', 'upskill-me' ); ?></label>
			<?php // Figma dropdown: 203x46 pill, 14/500 #55555f. ?>
			<span class="inline-grid *:col-start-1 *:row-start-1">
				<select class="cart-line__select h-[46px] w-[203px] max-w-full cursor-pointer appearance-none rounded-full border border-line bg-white pr-11 pl-4 text-sm font-medium text-[#55555f] focus:border-brand-700 focus:outline-none" id="<?php echo esc_attr( 'cohort-' . $upskill_key ); ?>" name="<?php echo esc_attr( 'upskill_level[' . $upskill_key . ']' ); ?>" data-cart-level>
					<?php foreach ( $upskill_levels as $upskill_level ) : ?>
						<option value="<?php echo esc_attr( $upskill_level['id'] ); ?>"<?php selected( (int) $upskill_line['variation_id'], (int) $upskill_level['id'] ); ?>><?php echo esc_html( $upskill_level['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="pointer-events-none mr-4 self-center justify-self-end text-gray-950" aria-hidden="true"><?php upskill_icon( 'field-chevron', 'size-4' ); ?></span>
			</span>
		</span>
	<?php elseif ( $upskill_item['level'] ) : ?>
		<span class="cart-line__cohort flex items-center gap-2.5 pt-4 text-sm text-gray-500"><?php esc_html_e( 'Cohort', 'upskill-me' ); ?> <strong class="font-medium text-[#55555f]"><?php echo esc_html( $upskill_item['level'] ); ?></strong></span>
	<?php endif; ?>
