<?php
/**
 * Order received: the order summary card (Figma Post Checkout Flow
 * 49325:20216, right column): sessions, seats, total, "Go to My Account"
 * and the ticked list. Sits in the right column of WooCommerce's
 * .woocommerce-order (grid in woocommerce/_store.scss).
 *
 * @package upskill-me
 *
 * @var array $args { @type WC_Order $order The order. }
 */

$upskill_order = $args['order'];
$upskill_seats = 0;

foreach ( $upskill_order->get_items() as $upskill_line ) {
	$upskill_seats += (int) $upskill_line->get_quantity();
}

$upskill_account = function_exists( 'upskill_account_link' ) ? upskill_account_link() : array( 'url' => wc_get_page_permalink( 'myaccount' ) );
?>
<aside class="order-summary flex flex-col rounded-[24px] bg-white p-6 shadow-card" aria-labelledby="order-summary-heading">
	<h2 class="text-[19.2px] leading-[1.62] font-bold tracking-[-0.02em] text-gray-900" id="order-summary-heading"><?php esc_html_e( 'Order summary', 'upskill-me' ); ?></h2>
	<dl class="mt-5 flex flex-col gap-[21px] text-base">
		<div class="flex justify-between gap-4"><dt class="text-[#55555a]"><?php esc_html_e( 'Sessions', 'upskill-me' ); ?></dt><dd class="text-gray-900"><?php echo esc_html( number_format_i18n( count( $upskill_order->get_items() ) ) ); ?></dd></div>
		<div class="flex justify-between gap-4"><dt class="text-[#55555a]"><?php esc_html_e( 'Seats', 'upskill-me' ); ?></dt><dd class="text-gray-900"><?php echo esc_html( number_format_i18n( $upskill_seats ) ); ?></dd></div>
		<div class="flex items-start justify-between gap-4 border-t border-gray-900/10 pt-6 font-medium text-gray-900">
			<dt><?php esc_html_e( 'Total', 'upskill-me' ); ?></dt>
			<dd class="text-right">
				<?php echo wp_kses_post( $upskill_order->get_formatted_order_total() ); ?>
				<?php if ( wc_tax_enabled() && $upskill_order->get_total_tax() > 0 ) : ?>
					<span class="block text-xs font-normal text-gray-500">
						<?php
						/* translators: %s: tax amount. */
						echo wp_kses_post( sprintf( __( 'includes %s GST', 'upskill-me' ), wc_price( $upskill_order->get_total_tax(), array( 'currency' => $upskill_order->get_currency() ) ) ) );
						?>
					</span>
				<?php endif; ?>
			</dd>
		</div>
	</dl>
	<?php
	upskill_button(
		array(
			'url'   => $upskill_account['url'],
			'title' => __( 'Go to My Account', 'upskill-me' ),
		),
		array(
			'variant' => 'light',
			'class'   => 'mt-5 min-h-12 w-full justify-between border-gray-900/10',
		)
	);
	get_template_part( 'template-parts/commerce/reassurance', null, array( 'context' => 'order' ) );
	?>
</aside>
