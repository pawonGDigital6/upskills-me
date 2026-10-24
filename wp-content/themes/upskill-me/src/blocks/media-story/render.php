<?php
/**
 * Media Story block.
 *
 * Figma: About 49325:14636 + quote card 49325:14673 (1440) / 49374:47032 (390);
 * Contact "Stay connected" 49325:20138 / 49375:115528 (boxed, with links).
 *
 * A photograph with a quote card hanging over its lower edge, beside the
 * section header, story paragraphs and a short checklist or a list of social
 * links. "Boxed" sets the section as a white card on the grey page: inset on
 * phones, full width with rounded corners from desktop. Not boxed, it carries
 * on from the section above on phones (grey, no top padding: Figma draws the
 * About hero and story as one section) and is a white, rounded section from
 * desktop.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$image     = get_field( 'image' );
$quote     = get_field( 'quote' );
$checklist = array_filter( (array) get_field( 'checklist' ), static fn( $item ) => ! empty( $item['title'] ) );
$links     = array_filter( (array) get_field( 'links' ), static fn( $item ) => ! empty( $item['url'] ) );
$boxed     = (bool) get_field( 'boxed' );
// Points (Categories "Training for what matters" 49325:14601 / 49354:97376): a
// white rounded panel at every width, a 652x544 photo, lead-over-text points.
$points    = ! $boxed && 'points' === get_field( 'list_style' );
$networks  = array(
	'linkedin' => __( 'LinkedIn', 'upskill-me' ),
	'facebook' => __( 'Facebook', 'upskill-me' ),
	'youtube'  => __( 'YouTube', 'upskill-me' ),
);

if ( $points ) {
	$section = 'rounded-[24px] bg-white section-y';
} else {
	$section = $boxed ? 'bg-gray-25 py-[46px] lg:mb-20 lg:rounded-[24px] lg:bg-white lg:py-20' : 'bg-gray-25 pb-6 lg:rounded-[24px] lg:bg-white lg:section-y';
}
$attributes = upskill_block_attributes( $block, 'block-media-story ' . $section );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="flex flex-col lg:flex-row lg:gap-14 <?php echo $boxed ? 'lg:items-center gap-6 rounded-[24px] bg-white p-4 lg:rounded-none lg:bg-transparent lg:p-0' : ( $points ? 'gap-14 lg:items-start' : 'gap-10 lg:items-start' ); ?>">
			<?php if ( $image ) : ?>
				<?php // Phones: the quote card sits under the photo, overlapping it by 48px. Desktop: it shares the photo's cell and hangs 36px below it (the wrapper's padding). ?>
				<div class="grid grid-cols-1 lg:min-w-0 lg:flex-1 lg:grid-rows-1 lg:self-stretch <?php echo ! empty( $quote['text'] ) ? 'lg:pb-9' : ''; ?>">
					<?php
					upskill_image(
						$image,
						'full',
						array(
							'class' => 'col-start-1 row-start-1 size-full rounded-[24px] object-cover shadow-xs sm:aspect-[4/3] lg:aspect-auto ' . ( $boxed ? 'aspect-[326/421] lg:h-0 lg:min-h-full' : ( $points ? 'aspect-[356/544] lg:h-0 lg:min-h-full' : 'aspect-[358/280] lg:min-h-[560px]' ) ),
							// Boxed on phones: 326x421 draws the 3:2 photo about 630 wide.
							// Points on phones: 356x544 draws the 6:5 photo about 652 wide.
							'sizes' => $boxed ? '(min-width: 1025px) 46vw, (min-width: 640px) 92vw, 162vw' : ( $points ? '(min-width: 1025px) 46vw, (min-width: 768px) 92vw, 652px' : '(min-width: 1025px) 46vw, 92vw' ),
						)
					);
					?>
					<?php if ( ! empty( $quote['text'] ) ) : ?>
						<div class="reveal relative col-start-1 row-start-2 mx-3.5 -mt-12 rounded-[22px] border border-line bg-white p-[16px_26px_25px_22px] lg:pl-7 lg:pr-5 lg:pb-[26px] lg:pt-[20px] shadow-xs lg:row-start-1 lg:mt-0 lg:mr-auto lg:-mb-9 lg:ml-[55px] lg:max-w-[536px] lg:self-end">
							<span class="block h-6 text-[30px] leading-[30px] font-semibold text-brand-500" aria-hidden="true">&ldquo;</span>
							<blockquote class="pt-5 text-base font-medium text-gray-950"><?php echo esc_html( $quote['text'] ); ?></blockquote>
							<?php if ( ! empty( $quote['caption'] ) ) : ?>
								<p class="pt-3 text-sm text-gray-500"><?php echo esc_html( $quote['caption'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="flex flex-col gap-[18px] lg:min-w-0 lg:flex-1">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'button' => false,
					'reveal' => true,
						'class'  => 'block-media-story__header' . ( $points ? ' section-header--base' : '' ),
					)
				);
				?>

				<?php if ( $checklist && $points ) : ?>
					<ul class="flex flex-col gap-6 pt-1.5">
						<?php foreach ( $checklist as $item ) : ?>
							<li class="flex flex-col gap-2 text-base">
								<strong class="font-medium text-[#0d0d0d]"><?php echo esc_html( $item['title'] ); ?></strong>
								<?php if ( ! empty( $item['text'] ) ) : ?>
									<span class="text-gray-500"><?php echo esc_html( $item['text'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( $checklist ) : ?>
					<ul class="flex flex-col pt-2.5 lg:gap-[19px] sm:gap-4">
						<?php foreach ( $checklist as $item ) : ?>
							<li class="flex items-start gap-3.5">
								<span class="mt-0.5 grid size-6 flex-none place-items-center rounded-full bg-brand-100 text-[#6b2fe3]" aria-hidden="true"><?php upskill_icon( 'check', 'size-3' ); ?></span>
								<p class="text-base text-[#0e0e14]">
									<strong class="font-medium text-[#0e0e14]"><?php echo esc_html( $item['title'] ); ?></strong>
									<?php if ( ! empty( $item['text'] ) ) : ?>
										<?php echo esc_html( '- ' . $item['text'] ); ?>
									<?php endif; ?>
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $links ) : ?>
					<ul class="flex flex-col gap-2 pt-5 sm:max-w-[330px]">
						<?php foreach ( $links as $item ) : ?>
							<?php
							$network = isset( $networks[ $item['network'] ] ) ? $item['network'] : 'linkedin';
							$label   = ! empty( $item['text'] ) ? $item['text'] : __( 'Follow Upskillme', 'upskill-me' );
							?>
							<li>
								<a class="group flex items-center justify-between gap-3 rounded-xl border border-dashed border-gray-900/10 px-3.5 py-3 transition-colors hover:border-brand-700/40 hover:bg-brand-25" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener">
									<span class="flex items-center gap-2.5 text-base leading-[1.62] font-medium tracking-[-0.006em] text-gray-900">
										<?php upskill_icon( $network, 'size-4 flex-none' ); ?>
										<?php echo esc_html( $networks[ $network ] ); ?>
									</span>
									<span class="flex items-center gap-[7px] text-sm leading-[1.62] font-medium tracking-[-0.007em] text-[#6744a8]">
										<?php echo esc_html( $label ); ?>
										<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
									</span>
									<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'upskill-me' ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
