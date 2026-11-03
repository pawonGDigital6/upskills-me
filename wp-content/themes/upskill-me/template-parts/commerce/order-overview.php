<?php
/**
 * Order received: the page header and confirmation strip (Figma Post Checkout
 * Flow 49325:20216): the tick badge and "Purchase confirmed", the heading and
 * thanks, then confirmation, order number, purchase date and training
 * sessions. Replaces WooCommerce's thanks line and overview list; it sits in
 * the left column beside the order summary (template-store.php leaves the
 * heading to it for a valid order).
 *
 * A bank transfer order waits for payment: the strip says so, and names the
 * payment method, until the order is marked paid.
 *
 * @package upskill-me
 *
 * @var array $args { @type WC_Order $order The order. }
 */

$upskill_order = $args['order'];
$upskill_paid  = $upskill_order->is_paid();
$upskill_cells = array(
	__( 'Confirmation', 'upskill-me' )      => $upskill_paid ? __( 'Purchase successful', 'upskill-me' ) : __( 'Awaiting payment', 'upskill-me' ),
	__( 'Order number', 'upskill-me' )      => '#' . $upskill_order->get_order_number(),
	__( 'Purchase date', 'upskill-me' )     => wc_format_datetime( $upskill_order->get_date_created(), 'd F Y' ),
	__( 'Training sessions', 'upskill-me' ) => number_format_i18n( count( $upskill_order->get_items() ) ),
);

if ( ! $upskill_paid && $upskill_order->get_payment_method_title() ) {
	$upskill_cells[ __( 'Payment', 'upskill-me' ) ] = $upskill_order->get_payment_method_title();
}
?>
<header class="order-hero">
	<div class="flex flex-wrap items-center gap-5">
		<?php // Figma: a 64px lilac disc with a 30px tick inside a 4px #f1ecfb ring. ?>
		<span class="grid size-[72px] place-items-center rounded-full border-4 border-brand-100" aria-hidden="true">
			<span class="grid size-16 place-items-center rounded-full border border-brand-200 bg-brand-50 text-brand-700 shadow-xs"><?php upskill_icon( 'check', 'size-[18px]' ); ?></span>
		</span>
		<span class="rounded-full px-[13px] py-1.5 text-[10px] leading-[1.62] font-semibold tracking-[0.12em] text-gray-950 uppercase <?php echo $upskill_paid ? 'bg-signal' : 'bg-brand-100'; ?>"><?php echo esc_html( $upskill_paid ? __( 'Purchase confirmed', 'upskill-me' ) : __( 'Order received', 'upskill-me' ) ); ?></span>
	</div>
	<?php upskill_heading( $upskill_paid ? __( 'Your training is <em>ready.</em>', 'upskill-me' ) : __( 'Your seats are <em>reserved.</em>', 'upskill-me' ), 'h1', 'pt-6 text-h1 font-semibold text-gray-900' ); ?>
	<p class="pt-4 text-base text-[#6b6b72]">
		<?php
		echo esc_html(
			$upskill_paid
				? __( 'Thanks for choosing Upskillme. Your selected training has been successfully confirmed.', 'upskill-me' )
				: __( 'Thanks for choosing Upskillme. Your seats are reserved and become available as soon as your payment is confirmed.', 'upskill-me' )
		);
		?>
	</p>
</header>
<dl class="order-overview mt-8 grid grid-cols-2 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card md:grid-flow-col md:auto-cols-fr md:grid-cols-none">
	<?php foreach ( $upskill_cells as $upskill_label => $upskill_value ) : ?>
		<div class="flex flex-col gap-[5px] border-gray-100 p-5 max-md:odd:border-r max-md:[&:nth-child(n+3)]:border-t md:not-first:border-l">
			<dt class="text-sm text-gray-500"><?php echo esc_html( $upskill_label ); ?></dt>
			<dd class="text-base font-medium text-gray-900"><?php echo esc_html( $upskill_value ); ?></dd>
		</div>
	<?php endforeach; ?>
</dl>
