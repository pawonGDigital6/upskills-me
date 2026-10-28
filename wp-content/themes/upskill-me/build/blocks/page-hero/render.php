<?php
/**
 * Page Hero block.
 *
 * Figma: About 49325:14606, Resources 49325:16382, Blogs 49325:16868, Guides
 * (390: 49374:46982, 49374:101478). Eyebrow, headline, intro, two buttons and
 * a note beside a photo (cropped to fill) or an illustration (shown whole).
 * "Show chips" puts up to four link chips over the photo (Resources).
 *
 * Other heroes have their own blocks: heading-hero (Case Studies, FAQs),
 * ladder-hero (Why UpSkill Me) and hub-hero (Industries, Categories).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$media    = get_field( 'media' );
$is_photo = 'illustration' !== get_field( 'media_style' );
$note     = get_field( 'note' );
$chips    = $is_photo && get_field( 'show_chips' ) ? array_slice( array_values( array_filter( (array) get_field( 'chips' ), static fn( $chip ) => ! empty( $chip['label'] ) ) ), 0, 4 ) : array();

/*
 * Chip spots over the photo, from the 640-wide Resources photo (49325:16381):
 * side offset and top offset, both as a share of the photo's width so the
 * chips scale with it. Right-hand chips are measured from the right edge, so a
 * narrower photo (1025-1200) never clips them.
 */
$chip_spots = array(
	'upper-left'   => 'justify-self-start ml-[2.7%] mt-[27.5%]',
	'top-right'    => 'justify-self-end mr-[5.6%] mt-[12.2%]',
	'lower-left'   => 'justify-self-start ml-[21.3%] mt-[69.4%]',
	'bottom-right' => 'justify-self-end mr-[10.5%] mt-[74.1%]',
);
$chip_tones = array(
	'purple' => 'bg-brand-100 text-[#6b2fe3]',
	'blue'   => 'bg-[#e2edfa] text-[#2f6fb5]',
	'green'  => 'bg-[#e7f6d8] text-[#4d7c0f]',
);

// Inner pages leave 56px under the header before the hero section (Figma frames
// start every inner hero at y 176), plus the hero's own 48px on desktop.
$attributes = upskill_block_attributes( $block, 'block-page-hero overflow-hidden bg-gray-25 pt-7 pb-[46px] lg:pt-[104px] lg:pb-20' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="flex flex-col gap-6 lg:flex-row lg:gap-20">
			<div class="page-hero__copy flex flex-col justify-center lg:min-w-0 lg:flex-1">
				<div class="flex flex-col items-start gap-3">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'page-hero__heading text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="page-hero__text reveal mt-7 text-lead text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>

				<?php if ( get_field( 'primary_button' ) || get_field( 'secondary_button' ) ) : ?>
					<div class="page-hero__buttons reveal flex flex-wrap gap-2 pt-6 sm:pt-14" style="--i:2">
						<?php
						upskill_button( get_field( 'primary_button' ), array( 'class' => 'min-h-[50px]' ) );
						upskill_button(
							get_field( 'secondary_button' ),
							array(
								'variant' => 'light',
								'icon'    => 'play',
								'class'   => 'min-h-[50px]',
							)
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( $note ) : ?>
					<?php // Hidden on phones: the About, Blogs and Guides phone frames leave it out. ?>
					<p class="page-hero__note hidden items-center gap-2 pt-4 font-secondary text-[13px] leading-[1.62] tracking-[-0.007em] text-[#6b6b72] sm:flex">
						<?php upskill_icon( 'check', 'size-3.5 flex-none text-brand-700' ); ?>
						<?php echo esc_html( $note ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $media && $is_photo ) : ?>
				<?php // The photo fills the media column; chips share its grid cell (from lg; the 390 frame leaves them out). ?>
				<div class="page-hero__media grid grid-cols-1 grid-rows-1 overflow-hidden rounded-2xl sm:rounded-xl lg:min-w-0 lg:flex-1">
					<?php
					upskill_image(
						$media,
						'full',
						array(
							'class'   => 'page-hero__photo col-start-1 row-start-1 aspect-square size-full object-cover sm:aspect-[4/3] lg:aspect-auto lg:min-h-[578px]',
							'loading' => 'eager',
							// The photo fills half the 1360 box (or the full column below lg); at 578px tall it can draw wider than its box.
							'sizes'   => '(min-width: 1025px) 46vw, 92vw',
						)
					);
					?>
					<?php foreach ( $chips as $index => $chip ) : ?>
						<?php
						$spot = isset( $chip_spots[ $chip['position'] ] ) ? $chip_spots[ $chip['position'] ] : $chip_spots['upper-left'];
						$tone = isset( $chip_tones[ $chip['tone'] ] ) ? $chip_tones[ $chip['tone'] ] : $chip_tones['purple'];
						$tag  = ! empty( $chip['link']['url'] ) ? 'a' : 'span';
						?>
						<<?php echo esc_attr( $tag ); ?> class="page-hero__chip reveal col-start-1 row-start-1 hidden items-center gap-2.5 self-start rounded-full bg-white py-2 pr-[18px] pl-2 text-sm leading-none font-semibold text-[#0e0e14] drop-shadow-[0_14px_16px_rgb(20_12_48/14%)] transition-transform duration-300 hover:-translate-y-0.5 lg:flex <?php echo esc_attr( $spot ); ?>"<?php echo 'a' === $tag ? upskill_link_attributes( $chip['link'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> style="--i:<?php echo (int) $index + 1; ?>">
							<span class="grid size-[30px] place-items-center rounded-full <?php echo esc_attr( $tone ); ?>" aria-hidden="true">
								<?php upskill_icon( $chip['icon'] ? $chip['icon'] : 'book', 'size-[15px]' ); ?>
							</span>
							<?php echo esc_html( $chip['label'] ); ?>
						</<?php echo esc_attr( $tag ); ?>>
					<?php endforeach; ?>
				</div>
			<?php elseif ( $media ) : ?>
				<?php
				// Illustrations are 2x exports of the Figma group: show them at half their width (Blogs 556, Guides 385).
				$art_meta  = wp_get_attachment_metadata( (int) $media );
				$art_width = ! empty( $art_meta['width'] ) ? (int) round( $art_meta['width'] / 2 ) : 556;
				?>
				<div class="page-hero__media flex items-center justify-center lg:min-w-0 lg:flex-1 lg:min-h-[460px] max-sm:pt-10">
					<?php
					upskill_image(
						$media,
						'full',
						array(
							'class'   => 'page-hero__illustration h-auto w-full',
							'style'   => 'max-width:' . $art_width . 'px',
							'loading' => 'eager',
							'sizes'   => '(min-width: ' . ( $art_width + 40 ) . 'px) ' . $art_width . 'px, 92vw',
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
