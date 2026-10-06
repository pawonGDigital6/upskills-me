<?php
/**
 * FAQs block.
 *
 * Figma: Homepage section 49325:14550 (1440) / Container 49196:7755 (390).
 *
 * Questions are FAQ records, chosen by topic or hand-picked, so one answer is
 * maintained once however many pages show it. The help card is block-owned.
 * The first answer is open, and opening another closes it.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-faqs section-y bg-brand-50' );

$query_args = array(
	'post_type'              => 'faq',
	'post_status'            => 'publish',
	'no_found_rows'          => true,
	'update_post_term_cache' => false,
	'update_post_meta_cache' => false,
);

$selected = (array) get_field( 'selected_faqs' );

if ( 'selected' === get_field( 'selection_mode' ) && $selected ) {
	$query_args['post__in']       = array_map( 'absint', $selected );
	$query_args['orderby']        = 'post__in';
	$query_args['posts_per_page'] = count( $selected );
} else {
	$count                        = (int) get_field( 'number_of_faqs' );
	$query_args['posts_per_page'] = $count > 0 ? $count : 6;
	$query_args['orderby']        = array(
		'menu_order' => 'ASC',
		'date'       => 'ASC',
	);

	$topic = get_field( 'faq_topic' );

	if ( $topic ) {
		$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- one indexed term lookup.
			array(
				'taxonomy' => 'faq_category',
				'terms'    => (array) $topic,
			),
		);
	}
}

$faqs = new WP_Query( $query_args );
$card = get_field( 'help_card' );
$uid  = wp_unique_id( 'faq-' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="flex flex-col gap-8 lg:flex-row lg:gap-14">
			<?php // 514 of the 1360 content box. ?>
			<div class="flex flex-col justify-between gap-8 lg:w-[37.8%] lg:flex-none">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'button' => false,
						'class'  => 'block-faqs__header',
					)
				);
				?>

				<?php if ( ! empty( $card['title'] ) ) : ?>
					<div class="flex flex-col items-start gap-6 rounded-card border border-gray-100 bg-gray-25 p-4 shadow-[0_1px_1px_rgb(10_13_18/5%)]">
						<div class="flex flex-col gap-4 text-lg">
							<p class="font-medium text-gray-950"><?php echo esc_html( $card['title'] ); ?></p>
							<?php if ( ! empty( $card['text'] ) ) : ?>
								<p class="text-gray-500"><?php echo esc_html( $card['text'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php
						upskill_button(
							isset( $card['button'] ) ? $card['button'] : array(),
							array(
								'variant' => 'light',
								'icon'    => 'play',
								'class'   => 'min-h-[50px]',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $faqs->have_posts() ) : ?>
				<div class="flex-1 border-t border-gray-900/10 lg:mt-12" data-disclosure-group>
					<?php while ( $faqs->have_posts() ) : ?>
						<?php
						$faqs->the_post();
						$open     = 0 === $faqs->current_post;
						$panel_id = $uid . '-' . get_the_ID();
						?>
						<div class="faq border-b border-gray-900/10">
							<h3>
								<button class="faq__question flex min-h-[77.5px] w-full items-center justify-between gap-6 py-5 text-left text-lg font-medium text-gray-950 lg:text-xl" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-disclosure>
									<?php the_title(); ?>
									<span class="faq__toggle relative size-[34px] flex-none rounded-full border border-gray-900/20" aria-hidden="true"></span>
								</button>
							</h3>
							<div class="collapse<?php echo $open ? ' is-open' : ''; ?>" id="<?php echo esc_attr( $panel_id ); ?>">
								<div>
									<div class="faq__answer max-w-[682px] pr-12 pb-6 text-base text-gray-500">
										<?php the_content(); ?>
									</div>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
