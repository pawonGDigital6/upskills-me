<?php
/**
 * Header cart link (beside search and account, on every width).
 *
 * The badge counts the training in the cart (cart lines, not seats) and is
 * hidden while the cart is empty. On the cart page js/components/cart.js keeps
 * it in step as WooCommerce updates the cart in place.
 *
 * @package upskill-me
 */

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}

$upskill_count = count( WC()->cart->get_cart() );
?>
<a class="site-header__icon site-header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-cart-link>
	<?php /* translators: %d: number of items in the cart (kept as %d for js/components/cart.js). */ ?>
	<span class="screen-reader-text" data-cart-label data-label-empty="<?php esc_attr_e( 'Cart', 'upskill-me' ); ?>" data-label-one="<?php esc_attr_e( 'Cart, %d item', 'upskill-me' ); ?>" data-label-many="<?php esc_attr_e( 'Cart, %d items', 'upskill-me' ); ?>">
		<?php
		echo esc_html(
			$upskill_count
				/* translators: %d: number of items in the cart. */
				? sprintf( _n( 'Cart, %d item', 'Cart, %d items', $upskill_count, 'upskill-me' ), $upskill_count )
				: __( 'Cart', 'upskill-me' )
		);
		?>
	</span>
	<?php upskill_icon( 'cart', 'col-start-1 row-start-1' ); ?>
	<span class="col-start-1 row-start-1 mt-1 mr-0.5 grid h-4 min-w-4 place-items-center self-start justify-self-end rounded-full bg-brand-700 px-1 text-[10px] leading-none font-semibold text-white" aria-hidden="true" data-cart-count<?php echo $upskill_count ? '' : ' hidden'; ?>><?php echo esc_html( number_format_i18n( $upskill_count ) ); ?></span>
</a>
