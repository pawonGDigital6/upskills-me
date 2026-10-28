<?php
/**
 * Resources Hero block.
 *
 * Figma: Resources 49325:16382 (390: 49374:101478). Eyebrow, headline, intro,
 * two buttons and a note beside a photo carrying up to four link chips
 * (chips from 1025, as on the 390 frame they are left out).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$photo = get_field( 'photo' );
$chips = array_slice( array_values( array_filter( (array) get_field( 'chips' ), static fn( $chip ) => ! empty( $chip['label'] ) ) ), 0, 4 );
$note  = get_field( 'note' );

// Inner pages leave 56px under the header (Figma frames start every inner hero at y 176), plus the hero's own 48px.
$attributes = upskill_block_attributes( $block, 'block-resources-hero overflow-hidden bg-gray-25 pt-7 pb-[46px] lg:pt-[104px] lg:pb-20' );

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
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="flex flex-col gap-6 lg:flex-row lg:gap-20">
			<div class="resources-hero__copy flex flex-col justify-center lg:min-w-0 lg:flex-1">
				<div class="flex flex-col items-start gap-3">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'resources-hero__heading text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="resources-hero__text reveal mt-7 text-lead text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>

				<?php if ( get_field( 'primary_button' ) || get_field( 'secondary_button' ) ) : ?>
					<div class="resources-hero__buttons reveal flex flex-wrap gap-2 pt-6 sm:pt-14" style="--i:2">
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
					<p class="resources-hero__note hidden items-center gap-2 pt-4 font-secondary text-[13px] leading-[1.62] tracking-[-0.007em] text-[#6b6b72] sm:flex">
						<?php upskill_icon( 'check', 'size-3.5 flex-none text-brand-700' ); ?>
						<?php echo esc_html( $note ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $photo ) : ?>
				<?php // Photo and chips share one grid cell. ?>
				<div class="resources-hero__media grid grid-cols-1 grid-rows-1 overflow-hidden rounded-2xl sm:rounded-xl lg:min-w-0 lg:flex-1">
					<?php
					upskill_image(
						$photo,
						'full',
						array(
							'class'   => 'resources-hero__photo col-start-1 row-start-1 aspect-square size-full object-cover sm:aspect-[4/3] lg:aspect-auto lg:min-h-[578px]',
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
						<<?php echo esc_attr( $tag ); ?> class="resources-hero__chip reveal col-start-1 row-start-1 hidden items-center gap-2.5 self-start rounded-full bg-white py-2 pr-[18px] pl-2 text-sm leading-none font-semibold text-[#0e0e14] drop-shadow-[0_14px_16px_rgb(20_12_48/14%)] transition-transform duration-300 hover:-translate-y-0.5 lg:flex <?php echo esc_attr( $spot ); ?>"<?php echo 'a' === $tag ? upskill_link_attributes( $chip['link'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> style="--i:<?php echo (int) $index + 1; ?>">
							<span class="grid size-[30px] place-items-center rounded-full <?php echo esc_attr( $tone ); ?>" aria-hidden="true">
								<?php upskill_icon( $chip['icon'] ? $chip['icon'] : 'book', 'size-[15px]' ); ?>
							</span>
							<?php echo esc_html( $chip['label'] ); ?>
						</<?php echo esc_attr( $tag ); ?>>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
