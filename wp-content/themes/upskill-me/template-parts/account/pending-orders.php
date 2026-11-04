<?php
/**
 * Orders awaiting payment, as a pointer on the organisation and learner
 * dashboards.
 *
 * A bank transfer order stays "On hold" until the Super Admin confirms the
 * payment and marks it Completed; only then does Group Registration create
 * the group, the seats and the organisation admin role. Until then the
 * dashboard would look empty, so this says why and links to the order's own
 * page in My Account (Orders & Billing) instead of repeating its details.
 *
 * @package upskill-me
 */

if ( ! function_exists( 'wc_get_orders' ) || ! is_user_logged_in() ) {
	return;
}

$upskill_orders = wc_get_orders(
	array(
		'customer_id' => get_current_user_id(),
		'status'      => array( 'wc-on-hold', 'wc-pending' ),
		'limit'       => 5,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);

if ( ! $upskill_orders ) {
	return;
}
?>
<div class="pending-orders mt-6 flex flex-col gap-2 rounded-lg border border-brand-100 bg-brand-25 p-4 text-sm leading-[1.5] text-gray-700 lg:mt-8 lg:p-5" role="status">
	<p class="font-medium text-gray-950">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %d: number of orders. */
				_n( 'You have %d order awaiting payment confirmation.', 'You have %d orders awaiting payment confirmation.', count( $upskill_orders ), 'upskill-me' ),
				count( $upskill_orders )
			)
		);
		?>
	</p>
	<p><?php esc_html_e( 'Your training and seats are set up as soon as the payment is confirmed. We’ll email you when it’s ready.', 'upskill-me' ); ?></p>
	<p class="flex flex-wrap gap-x-4 gap-y-1">
		<?php foreach ( $upskill_orders as $upskill_order ) : ?>
			<a class="font-medium text-brand-700 hover:underline" href="<?php echo esc_url( $upskill_order->get_view_order_url() ); ?>">
				<?php
				/* translators: %s: order number. */
				echo esc_html( sprintf( __( 'View order #%s', 'upskill-me' ), $upskill_order->get_order_number() ) );
				?>
			</a>
		<?php endforeach; ?>
	</p>
</div>
