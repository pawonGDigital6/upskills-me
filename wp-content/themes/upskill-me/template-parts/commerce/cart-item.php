<?php
/**
 * Cart: a course block's parts (Figma Cart 49325:15876; one block per course
 * since the review call of 19 Nov, item 3, woocommerce/cart/cart.php).
 *
 * Part "course": the course, once per block, as the review's proposed cart
 * layout draws it: title (to the course page), then "Category · On-the-job
 * training".
 *
 * Part "name": the cart line name WooCommerce passes through
 * woocommerce_cart_item_name (printed with wp_kses_post): category line,
 * title and summary.
 *
 * Part "level": one level row's name: the level and, for extra seats bought
 * for an existing team (Group Registration's "increase seats"), which team.
 * The seats stepper, line price and "Remove" are WooCommerce's own cells, refined in inc/lms-store.php and
 * woocommerce/_store.scss. A level can no longer be switched in the cart
 * (the "Cohort" select went with the 19 Nov review): each row is one level.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type string $part      course|name|level.
 *     @type array  $item      upskill_store_item() row.
 *     @type array  $cart_item WooCommerce cart line.
 *     @type string $key       Cart item key.
 * }
 */

$upskill_item = $args['item'];
$upskill_line = $args['cart_item'];
// Extra seats for an existing group (Group Registration's "increase seats").
$upskill_add_to = 'increase_seats' === ( $upskill_line['ldgr_dynamic_option'] ?? '' ) ? (int) ( $upskill_line['ldgr_dynamic_value'] ?? 0 ) : 0;

if ( 'level' === ( $args['part'] ?? 'name' ) ) :
	?>
<span class="cart-level">
	<span class="cart-level__name block"><?php echo esc_html( $upskill_item['level'] ? $upskill_item['level'] : $upskill_item['title'] ); ?></span>
	<?php if ( $upskill_add_to ) : ?>
		<span class="cart-line__seats-for block pt-1 text-sm text-gray-500">
			<?php
			/* translators: %s: group (team) name. */
			printf( esc_html__( 'Extra seats for %s', 'upskill-me' ), '<strong class="font-medium text-[#55555f]">' . esc_html( get_the_title( $upskill_add_to ) ) . '</strong>' );
			?>
		</span>
	<?php endif; ?>
</span>
	<?php
	return;
endif;

$upskill_category = trim( ( $upskill_item['category'] ? $upskill_item['category'] . ' · ' : '' ) . __( 'On-the-job training', 'upskill-me' ) );

if ( 'course' === $args['part'] ) :
	?>
<span class="cart-course">
	<a class="cart-course__title block" href="<?php echo esc_url( $upskill_item['url'] ); ?>"><?php echo esc_html( $upskill_item['title'] ); ?></a>
	<span class="cart-course__category block"><?php echo esc_html( $upskill_category ); ?></span>
</span>
	<?php
	return;
endif;
?>
<span class="cart-line">
	<span class="cart-line__category text-[10px] leading-[1.5] text-brand-700">
		<?php echo esc_html( $upskill_category ); ?>
	</span>
	<a class="cart-line__title block pt-1 text-base font-medium text-gray-900 hover:text-brand-700" href="<?php echo esc_url( $upskill_item['url'] ); ?>"><?php echo esc_html( $upskill_item['title'] ); ?></a>
	<?php if ( $upskill_item['summary'] ) : ?>
		<span class="cart-line__summary block pt-1.5 text-base text-gray-500"><?php echo esc_html( $upskill_item['summary'] ); ?></span>
	<?php endif; ?>
</span>
