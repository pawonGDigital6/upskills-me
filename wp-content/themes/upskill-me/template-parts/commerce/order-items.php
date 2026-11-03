<?php
/**
 * Order received: "What you've purchased" (Figma Post Checkout Flow
 * 49325:20216). One row per order line: picture, category line, course,
 * summary, then cohort (level), access, seats and status. Takes the place of
 * WooCommerce's order details table on this page only; the full order (with
 * the billing address) stays in My Account → Orders and in the order email.
 *
 * @package upskill-me
 *
 * @var array $args { @type WC_Order $order The order. }
 */

$upskill_order = $args['order'];
$upskill_paid  = $upskill_order->is_paid();
?>
<section class="order-items mt-16" aria-labelledby="order-items-heading">
	<?php upskill_eyebrow( __( 'Your training', 'upskill-me' ), 'train' ); ?>
	<h2 class="pt-4 text-2xl leading-[1.1] font-medium tracking-[-0.01em] text-gray-950" id="order-items-heading"><?php esc_html_e( 'What you’ve purchased', 'upskill-me' ); ?></h2>
	<p class="pt-5 text-[15px] leading-[1.62] tracking-[-0.01em] text-[#55555a]"><?php esc_html_e( 'Your selected training and cohort details are shown below.', 'upskill-me' ); ?></p>

	<ul class="mt-6 border-y border-gray-100">
		<?php foreach ( $upskill_order->get_items() as $upskill_line ) : ?>
			<?php
			$upskill_product = $upskill_line->get_product();
			$upskill_item    = upskill_store_item( $upskill_product );
			$upskill_image   = $upskill_item['course'] ? get_post_thumbnail_id( $upskill_item['course'] ) : ( $upskill_product ? $upskill_product->get_image_id() : 0 );
			?>
			<li class="flex flex-col gap-4 border-gray-100 py-6 not-first:border-t sm:flex-row sm:gap-5">
				<?php if ( $upskill_image ) : ?>
					<?php upskill_image( $upskill_image, 'full', array( 'class' => 'h-[53px] w-[84px] flex-none self-start rounded-lg object-cover', 'alt' => '', 'sizes' => '84px' ) ); ?>
				<?php endif; ?>
				<div class="flex min-w-0 flex-1 flex-col">
					<p class="text-sm text-brand-700"><?php echo esc_html( trim( ( $upskill_item['category'] ? $upskill_item['category'] . ' · ' : '' ) . __( 'On-the-job training', 'upskill-me' ) ) ); ?></p>
					<h3 class="pt-1 text-base font-medium text-gray-950"><?php echo esc_html( $upskill_item['title'] ? $upskill_item['title'] : $upskill_line->get_name() ); ?></h3>
					<?php if ( $upskill_item['summary'] ) : ?>
						<p class="pt-1 text-base text-gray-500"><?php echo esc_html( $upskill_item['summary'] ); ?></p>
					<?php endif; ?>
					<dl class="flex flex-wrap gap-x-6 gap-y-3 pt-4 text-[15px] leading-[1.62] tracking-[-0.01em]">
						<?php if ( $upskill_item['level'] ) : ?>
							<div><dt class="text-xs text-[#6b6b72]"><?php esc_html_e( 'Cohort', 'upskill-me' ); ?></dt><dd class="pt-0.5 font-medium text-gray-900"><?php echo esc_html( $upskill_item['level'] ); ?></dd></div>
						<?php endif; ?>
						<div><dt class="text-xs text-[#6b6b72]"><?php esc_html_e( 'Access', 'upskill-me' ); ?></dt><dd class="pt-0.5 font-medium text-gray-900"><?php esc_html_e( 'On demand', 'upskill-me' ); ?></dd></div>
						<div><dt class="text-xs text-[#6b6b72]"><?php esc_html_e( 'Seats', 'upskill-me' ); ?></dt><dd class="pt-0.5 font-medium text-gray-900"><?php echo esc_html( number_format_i18n( $upskill_line->get_quantity() ) ); ?></dd></div>
						<div>
							<dt class="text-xs text-[#6b6b72]"><?php esc_html_e( 'Status', 'upskill-me' ); ?></dt>
							<dd class="pt-0.5">
								<?php // Figma pill: #f1edfa, 12/500 #5a3d8c (no theme tokens). ?>
								<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-[3px] text-xs font-medium <?php echo $upskill_paid ? 'bg-[#f1edfa] text-[#5a3d8c]' : 'bg-gray-50 text-gray-700'; ?>">
									<?php if ( $upskill_paid ) : ?>
										<?php upskill_icon( 'tick-sm', 'size-[11px]' ); ?>
									<?php endif; ?>
									<?php echo esc_html( $upskill_paid ? __( 'Confirmed', 'upskill-me' ) : __( 'Awaiting payment', 'upskill-me' ) ); ?>
								</span>
							</dd>
						</div>
					</dl>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
