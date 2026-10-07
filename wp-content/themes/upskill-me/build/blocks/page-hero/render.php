<?php
/**
 * Page Hero block.
 *
 * Figma: About 49325:14606, Resources 49325:16382, Blogs 49325:16868 (split);
 * Why UpSkill Me 49325:15293, Case Studies 49325:18138 (centred). Mobile
 * frames 49374:46982, 49374:101478, 49372:42258.
 *
 * Split: headline, intro, two buttons and a note beside a photo (optionally
 * carrying link chips) or an illustration. Centred: headline and intro, with
 * optional floating badges either side and "the ladder" row underneath.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$layout   = 'center' === get_field( 'layout' ) ? 'center' : 'split';
$center   = 'center' === $layout;
$media    = get_field( 'media' );
$is_photo = 'illustration' !== get_field( 'media_style' );
$chips    = $center ? array() : array_filter( (array) get_field( 'chips' ), static fn( $chip ) => ! empty( $chip['label'] ) );
$badges   = $center ? array_filter( (array) get_field( 'badges' ), static fn( $badge ) => ! empty( $badge['title'] ) ) : array();
$ladder   = $center ? get_field( 'ladder' ) : array();
$steps    = ! empty( $ladder['steps'] ) ? array_filter( (array) $ladder['steps'], static fn( $step ) => ! empty( $step['title'] ) ) : array();
$note     = get_field( 'note' );

$attributes = upskill_block_attributes( $block, 'block-page-hero overflow-hidden bg-gray-25 pt-7 pb-[46px] lg:pt-12 lg:pb-20' );

/*
 * Chip spots over the photo, from the Resources frame (a 680-wide photo):
 * left offset and top offset, both as a share of the photo's width so the
 * chips scale with it.
 */
$chip_spots = array(
	'upper-left'   => 'ml-[2.5%] mt-[25.9%]',
	'top-right'    => 'ml-[72.2%] mt-[11.5%]',
	'lower-left'   => 'ml-[20%] mt-[65.2%]',
	'bottom-right' => 'ml-[69.3%] mt-[69.7%]',
);
$chip_tones = array(
	'purple' => 'bg-brand-100 text-[#6b2fe3]',
	'blue'   => 'bg-[#e2edfa] text-[#2f6fb5]',
	'green'  => 'bg-[#e7f6d8] text-[#4d7c0f]',
);
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php if ( $center ) : ?>
			<?php // The badges share the copy's grid cell so they float beside it without leaving the flow. ?>
			<div class="grid grid-cols-1 grid-rows-1">
				<div class="col-start-1 row-start-1 flex flex-col items-start gap-6 lg:mx-auto lg:max-w-[792px] lg:items-center lg:text-center">
					<div class="flex flex-col items-start gap-5 lg:items-center">
						<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
						<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]' ); ?>
					</div>
					<?php if ( get_field( 'intro' ) ) : ?>
						<p class="max-w-[666px] text-base text-gray-500"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
					<?php endif; ?>
					<?php upskill_button( get_field( 'primary_button' ) ); ?>
				</div>

				<?php foreach ( $badges as $badge ) : ?>
					<?php $right = isset( $badge['side'] ) && 'right' === $badge['side']; ?>
					<p class="reveal col-start-1 row-start-1 hidden items-center gap-2.5 self-start rounded-full border border-line bg-white py-2 pr-4 pl-2 drop-shadow-[0_12px_16px_rgb(20_12_48/7%)] lg:flex <?php echo $right ? 'mt-[129px] justify-self-end' : 'mt-[236px] justify-self-start'; ?>" style="--i:<?php echo $right ? 2 : 1; ?>">
						<span class="grid size-[30px] place-items-center rounded-full <?php echo isset( $badge['tone'] ) && 'green' === $badge['tone'] ? 'bg-[#f0fbd4] text-[#3f6212]' : 'bg-brand-100 text-[#6b2fe3]'; ?>" aria-hidden="true">
							<?php upskill_icon( $badge['icon'] ? $badge['icon'] : 'check', 'size-[15px]' ); ?>
						</span>
						<span class="flex flex-col">
							<span class="text-[13px] leading-[1.2] font-semibold text-[#0e0e14]"><?php echo esc_html( $badge['title'] ); ?></span>
							<?php if ( ! empty( $badge['text'] ) ) : ?>
								<span class="text-[11px] leading-[1.2] text-[#8b8b97]"><?php echo esc_html( $badge['text'] ); ?></span>
							<?php endif; ?>
						</span>
					</p>
				<?php endforeach; ?>
			</div>

			<?php if ( $steps ) : ?>
				<?php // 1200 of the 1360 box: the ladder sits 44px inside the 1288 frame Figma draws. ?>
				<div class="mt-6 rounded-[24px] border border-line bg-white px-4 py-6 shadow-xs lg:mx-auto lg:mt-12 lg:flex lg:max-w-[1200px] lg:px-8 lg:py-7">
					<div class="flex flex-col gap-2.5 lg:w-[210px] lg:flex-none lg:border-r lg:border-line lg:pr-7">
						<?php if ( ! empty( $ladder['label'] ) ) : ?>
							<p class="text-sm font-medium text-brand-700 uppercase"><?php echo esc_html( $ladder['label'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $ladder['title'] ) ) : ?>
							<p class="text-lg font-medium text-gray-950 lg:max-w-[181px]"><?php echo esc_html( $ladder['title'] ); ?></p>
						<?php endif; ?>
					</div>
					<ol class="mt-6 flex flex-col gap-6 pl-6 lg:mt-0 lg:grid lg:flex-1 lg:auto-cols-fr lg:grid-flow-col lg:gap-0 lg:pl-0">
						<?php foreach ( array_values( $steps ) as $number => $step ) : ?>
							<?php $last = count( $steps ) - 1 === $number; ?>
							<li class="relative flex flex-col lg:px-6 lg:py-0.5">
								<?php if ( $number > 0 ) : ?>
									<?php upskill_icon( 'chevron-right', 'absolute top-5 -left-1.5 hidden size-3 text-gray-200 lg:block' ); ?>
								<?php endif; ?>
								<span class="text-sm font-medium <?php echo $last ? 'text-[#6b2fe3]' : 'text-gray-500'; ?>"><?php echo esc_html( str_pad( (string) ( $number + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="pt-2 text-lg font-medium lg:whitespace-nowrap <?php echo $last ? 'text-[#6b2fe3]' : 'text-gray-950'; ?>"><?php echo esc_html( $step['title'] ); ?></span>
								<?php if ( ! empty( $step['text'] ) ) : ?>
									<span class="pt-1 text-base text-gray-500"><?php echo esc_html( $step['text'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<div class="flex flex-col gap-10 lg:flex-row lg:gap-20">
				<div class="flex flex-col justify-center lg:min-w-0 lg:flex-1">
					<div class="flex flex-col items-start gap-3">
						<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
						<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
					</div>
					<?php if ( get_field( 'intro' ) ) : ?>
						<p class="mt-6 text-lead text-gray-500"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
					<?php endif; ?>

					<?php if ( get_field( 'primary_button' ) || get_field( 'secondary_button' ) ) : ?>
						<div class="reveal flex flex-wrap gap-2 pt-8">
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
						<p class="flex items-center gap-2 pt-4 font-[Geist] text-[13px] leading-[1.62] tracking-[-0.007em] text-[#6b6b72]">
							<?php upskill_icon( 'check', 'size-3.5 flex-none text-brand-700' ); ?>
							<?php echo esc_html( $note ); ?>
						</p>
					<?php endif; ?>
				</div>

				<?php if ( $media && $is_photo ) : ?>
					<?php // Photo and chips share one grid cell; the chips are hidden on the 390 frame, as drawn. ?>
					<div class="grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[20px] lg:min-w-0 lg:flex-1">
						<?php
						upskill_image(
							$media,
							'full',
							array(
								'class'   => 'col-start-1 row-start-1 aspect-square size-full object-cover sm:aspect-[4/3] lg:aspect-auto lg:min-h-[578px]',
								'loading' => 'eager',
								// The photo fills half the 1360 box (or the full column below lg); at 578px tall it can draw wider than its box.
								'sizes'   => '(min-width: 1025px) 46vw, 92vw',
							)
						);
						?>
						<?php foreach ( array_values( $chips ) as $index => $chip ) : ?>
							<?php
							$spot = isset( $chip_spots[ $chip['position'] ] ) ? $chip_spots[ $chip['position'] ] : $chip_spots['upper-left'];
							$tone = isset( $chip_tones[ $chip['tone'] ] ) ? $chip_tones[ $chip['tone'] ] : $chip_tones['purple'];
							$tag  = ! empty( $chip['link']['url'] ) ? 'a' : 'span';
							?>
							<<?php echo esc_attr( $tag ); ?> class="reveal col-start-1 row-start-1 hidden items-center gap-2.5 self-start justify-self-start rounded-full bg-white py-2 pr-[18px] pl-2 text-sm leading-[1.55] font-semibold text-[#0e0e14] drop-shadow-[0_14px_16px_rgb(20_12_48/14%)] transition-transform duration-300 hover:-translate-y-0.5 lg:flex <?php echo esc_attr( $spot ); ?>"<?php echo 'a' === $tag ? upskill_link_attributes( $chip['link'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> style="--i:<?php echo (int) $index + 1; ?>">
								<span class="grid size-[30px] place-items-center rounded-full <?php echo esc_attr( $tone ); ?>" aria-hidden="true">
									<?php upskill_icon( $chip['icon'] ? $chip['icon'] : 'book', 'size-[15px]' ); ?>
								</span>
								<?php echo esc_html( $chip['label'] ); ?>
							</<?php echo esc_attr( $tag ); ?>>
						<?php endforeach; ?>
					</div>
				<?php elseif ( $media ) : ?>
					<div class="flex items-center justify-center lg:min-w-0 lg:flex-1 lg:min-h-[460px]">
						<?php
						upskill_image(
							$media,
							'full',
							array(
								'class'   => 'h-auto w-full max-w-[556px]',
								'loading' => 'eager',
								'sizes'   => '(min-width: 600px) 556px, 92vw',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
