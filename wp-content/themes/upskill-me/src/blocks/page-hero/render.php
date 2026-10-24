<?php
/**
 * Page Hero block.
 *
 * Figma: About 49325:14606, Resources 49325:16382, Blogs 49325:16868 (split);
 * Why UpSkill Me 49325:15293, Case Studies 49325:18138 (centred). Mobile
 * frames 49374:46982, 49374:101478, 49372:42258.
 *
 * Text (FAQs 49325:17983): eyebrow, headline and intro on a pale lilac panel.
 *
 * Split: headline, intro, two buttons and a note beside a photo (optionally
 * carrying link chips) or an illustration. Centred: headline and intro, with
 * optional floating badges either side and "the ladder" row underneath.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$layout   = in_array( get_field( 'layout' ), array( 'center', 'text' ), true ) ? get_field( 'layout' ) : 'split';
$center   = 'center' === $layout;
$media    = get_field( 'media' );
$is_photo = 'illustration' !== get_field( 'media_style' );
$chips    = $center ? array() : array_filter( (array) get_field( 'chips' ), static fn( $chip ) => ! empty( $chip['label'] ) );
$badges   = $center ? array_filter( (array) get_field( 'badges' ), static fn( $badge ) => ! empty( $badge['title'] ) ) : array();
$ladder   = $center ? get_field( 'ladder' ) : array();
$steps    = ! empty( $ladder['steps'] ) ? array_filter( (array) $ladder['steps'], static fn( $step ) => ! empty( $step['title'] ) ) : array();
$cards    = $center ? array_values( array_filter( (array) get_field( 'cards' ), static fn( $card ) => ! empty( $card['image'] ) ) ) : array();
$note     = get_field( 'note' );

// Inner pages leave 56px under the header before the hero section (Figma frames
// start every inner hero at y 176): split heroes add their own 48px on top.
// With photo cards (Industry Hub 49325:14554 / 49354:95609) the 390 frame has
// 46px above the copy and the cards end the section on desktop.
$spacing    = $cards ? 'pt-[46px] pb-[46px] lg:pt-14 lg:pb-0' : 'pt-7 pb-[46px] lg:pb-20 ' . ( 'split' === $layout ? 'lg:pt-[104px]' : 'lg:pt-14' );
$attributes = upskill_block_attributes( $block, 'block-page-hero overflow-hidden bg-gray-25 ' . $spacing );

/*
 * Chip spots over the photo, from the Resources frame (a 640-wide photo, 49325:16381):
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
		<?php if ( 'text' === $layout ) : ?>
			<div class="flex flex-col gap-6 lg:bg-brand-25">
				<div class="flex flex-col items-start gap-3">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="reveal text-lead text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>
			</div>

		<?php elseif ( $center ) : ?>
			<?php // The badges share the copy's grid cell so they float beside it without leaving the flow. ?>
			<div class="grid grid-cols-1 grid-rows-1">
				<div class="col-start-1 row-start-1 flex flex-col items-start sm:gap-6 <?php echo $cards ? 'gap-6' : 'gap-7'; ?> lg:mx-auto lg:max-w-[792px] lg:items-center lg:text-center">
					<div class="flex flex-col items-start sm:gap-5 gap-3 lg:items-center">
						<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
						<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]' ); ?>
					</div>
					<?php if ( get_field( 'intro' ) ) : ?>
						<p class="reveal <?php echo $cards ? 'max-w-[666px]' : 'max-w-[756px]'; ?> text-base text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
					<?php endif; ?>
					<?php upskill_button( get_field( 'primary_button' ) ); ?>
				</div>

				<?php foreach ( $badges as $badge ) : ?>
					<?php $right = isset( $badge['side'] ) && 'right' === $badge['side']; ?>
					<?php if ( ! empty( $badge['count'] ) || ! empty( $badge['photo'] ) ) : ?>
						<?php // Compact tag with a photo tile under it (Industry Hub 49325:14554): left at y 159 with the tile 96px in, right at y 142 with the tile under its start. Shown from 1200, where they clear the heading. ?>
						<div class="page-hero__tag reveal col-start-1 row-start-1 hidden flex-col items-start gap-3 self-start wd:flex <?php echo $right ? 'mt-[142px] justify-self-end' : 'mt-[159px] justify-self-start'; ?>" style="--i:<?php echo $right ? 2 : 1; ?>">
							<p class="flex items-center gap-[7px] rounded-lg border border-line bg-white py-2 pr-3.5 pl-2.5 text-xs leading-[1.62] tracking-[-0.1px] shadow-[0_4px_14px_-6px_rgb(47_26_81/10%),0_1px_2px_0_rgb(26_26_26/4%)]">
								<?php if ( ! $right ) : ?>
									<?php upskill_icon( $badge['icon'] ? $badge['icon'] : 'play', 'size-[15px] flex-none text-[#6744a8]' ); ?>
								<?php endif; ?>
								<?php if ( ! empty( $badge['count'] ) ) : ?>
									<strong class="font-semibold text-gray-900"><?php echo esc_html( $badge['count'] ); ?></strong>
								<?php endif; ?>
								<span class="font-secondary text-[#55555a]"><?php echo esc_html( $badge['title'] ); ?></span>
								<?php if ( $right ) : ?>
									<?php upskill_icon( $badge['icon'] ? $badge['icon'] : 'check', 'size-[15px] flex-none text-[#6744a8]' ); ?>
								<?php endif; ?>
							</p>
							<?php if ( ! empty( $badge['photo'] ) ) : ?>
								<?php upskill_image( $badge['photo'], 'full', array( 'class' => 'size-[54px] rounded-2xl border-[3px] border-white object-cover shadow-[0_14px_34px_-10px_rgb(47_26_81/14%),0_2px_4px_0_rgb(26_26_26/3%)] ' . ( $right ? '' : 'ml-24' ), 'alt' => '', 'loading' => 'eager', 'sizes' => '48px' ) ); ?>
							<?php endif; ?>
						</div>
						<?php continue; ?>
					<?php endif; ?>
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

			<?php if ( $cards ) : ?>
				<?php
				// Photo cards (Industry Hub 49325:14554, 390: 49354:95609): 354 / 620 / 354 at 1440,
				// 62px under the button. Phones: the wide card first and full width, the
				// other two side by side underneath.
				?>
				<ul class="page-hero__cards mt-[46px] grid grid-cols-2 gap-4 sm:grid-cols-[354fr_620fr_354fr] lg:mt-[62px]" role="list">
					<?php foreach ( $cards as $index => $card ) : ?>
						<?php $wide = 1 === $index; ?>
						<li class="page-hero__card reveal grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[17px] bg-gray-950 *:col-start-1 *:row-start-1 sm:rounded-[20px] <?php echo $wide ? 'order-first col-span-2 aspect-[358/418] sm:order-none sm:col-span-1 sm:aspect-[620/590]' : 'aspect-[170/200] sm:aspect-[354/590]'; ?>" style="--i:<?php echo (int) $index + 2; ?>">
							<?php
							upskill_image(
								$card['image'],
								'full',
								array(
									'class'   => 'size-full object-cover',
									'loading' => 'eager',
									// Drawn at 620 / 354 of 1360 (cover in a taller box draws them wider on phones).
									'sizes'   => $wide ? '(min-width: 1440px) 620px, (min-width: 768px) 46vw, 100vw' : '(min-width: 1440px) 354px, (min-width: 768px) 26vw, 50vw',
								)
							);
							?>
							<div class="bg-[linear-gradient(180deg,rgb(0_0_0/0%)_50%,rgb(0_0_0/80%)_100%)]" aria-hidden="true"></div>
							<?php if ( ! empty( $card['title'] ) || ! empty( $card['text'] ) ) : ?>
								<div class="flex flex-col justify-end">
									<p class="flex flex-col gap-2 border-t border-white/15 bg-white/10 px-3 py-4 text-white backdrop-blur-md sm:gap-1 sm:pr-0 sm:pl-6">
										<?php if ( ! empty( $card['title'] ) ) : ?>
											<span class="text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em]"><?php echo esc_html( $card['title'] ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $card['text'] ) ) : ?>
											<span class="text-sm"><?php echo esc_html( $card['text'] ); ?></span>
										<?php endif; ?>
									</p>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $steps ) : ?>
				<?php // 1200 of the 1360 box: the ladder sits 44px inside the 1288 frame Figma draws; Figma leaves 62px under the button (its 138px intro box + 48px gap). ?>
				<div class="page-hero__steps reveal mt-6 rounded-[24px] border border-line bg-white px-4 py-6 shadow-xs lg:mx-auto lg:mt-[62px] mc:flex lg:max-w-[1200px] lg:px-8 lg:pt-7 lg:pb-4">
					<div class="flex flex-col gap-2.5 mc:w-[210px] lg:flex-none mc:border-r mc:border-line lg:pr-7 max-mc:mb-8 max-sm:mb-4 max-mc:text-center max-sm:text-left">
						<?php if ( ! empty( $ladder['label'] ) ) : ?>
							<p class="text-sm font-medium text-brand-700 uppercase"><?php echo esc_html( $ladder['label'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $ladder['title'] ) ) : ?>
							<p class="text-lg font-medium text-gray-950 mc:max-w-[181px]"><?php echo esc_html( $ladder['title'] ); ?></p>
						<?php endif; ?>
					</div>
					<ol class="mt-0 mc:flex-1 pl-6 grid lg:auto-cols-fr lg:grid-flow-col sm:gap-6 lg:gap-0 gap-5 lg:pl-0 max-mc:grid-cols-2 max-sm:grid-cols-1">
						<?php foreach ( array_values( $steps ) as $number => $step ) : ?>
							<?php $last = count( $steps ) - 1 === $number; ?>
							<li class="relative flex flex-col lg:px-6 lg:py-0.5">
								<?php if ( $number > 0 ) : ?>
									<?php upskill_icon( 'chevron-right', 'absolute top-5 -left-1.5 hidden size-3 text-gray-200 lg:block' ); ?>
								<?php endif; ?>
								<span class="text-sm font-medium <?php echo $last ? 'text-[#6b2fe3]' : 'text-gray-500'; ?>"><?php echo esc_html( str_pad( (string) ( $number + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="pt-2 text-lg font-medium lg:whitespace-nowrap <?php echo $last ? 'text-[#6b2fe3]' : 'text-gray-950'; ?>"><?php echo esc_html( $step['title'] ); ?></span>
								<?php if ( ! empty( $step['text'] ) ) : ?>
									<span class="pt-[2px] text-base text-gray-500"><?php echo esc_html( $step['text'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<div class="flex flex-col gap-6 lg:flex-row lg:gap-20">
				<div class="flex flex-col justify-center lg:min-w-0 lg:flex-1">
					<div class="flex flex-col items-start gap-3">
						<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
						<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
					</div>
					<?php if ( get_field( 'intro' ) ) : ?>
						<p class="reveal mt-7 text-lead text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
					<?php endif; ?>

					<?php if ( get_field( 'primary_button' ) || get_field( 'secondary_button' ) ) : ?>
						<div class="reveal flex flex-wrap gap-2 sm:pt-14 pt-6" style="--i:2">
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
						<?php // Hidden on phones: the About, Blogs and Guides phone frames leave it out (Resources alone keeps it). ?>
						<p class="hidden items-center gap-2 pt-4 sm:flex font-secondary text-[13px] leading-[1.62] tracking-[-0.007em] text-[#6b6b72]">
							<?php upskill_icon( 'check', 'size-3.5 flex-none text-brand-700' ); ?>
							<?php echo esc_html( $note ); ?>
						</p>
					<?php endif; ?>
				</div>

				<?php if ( $media && $is_photo ) : ?>
					<?php // Photo and chips share one grid cell; the chips are hidden on the 390 frame, as drawn. ?>
					<div class="grid grid-cols-1 grid-rows-1 overflow-hidden sm:rounded-xl rounded-2xl lg:min-w-0 lg:flex-1">
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
							<<?php echo esc_attr( $tag ); ?> class="reveal col-start-1 row-start-1 hidden items-center gap-2.5 self-start rounded-full bg-white py-2 pr-[18px] pl-2 text-sm leading-none font-semibold text-[#0e0e14] drop-shadow-[0_14px_16px_rgb(20_12_48/14%)] transition-transform duration-300 hover:-translate-y-0.5 lg:flex <?php echo esc_attr( $spot ); ?>"<?php echo 'a' === $tag ? upskill_link_attributes( $chip['link'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> style="--i:<?php echo (int) $index + 1; ?>">
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
					<div class="flex items-center justify-center lg:min-w-0 lg:flex-1 lg:min-h-[460px] max-sm:pt-10">
						<?php
						upskill_image(
							$media,
							'full',
							array(
								'class'   => 'h-auto w-full',
								'style'   => 'max-width:' . $art_width . 'px',
								'loading' => 'eager',
								'sizes'   => '(min-width: ' . ( $art_width + 40 ) . 'px) ' . $art_width . 'px, 92vw',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
