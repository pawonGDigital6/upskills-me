<?php
/**
 * Ladder Hero block.
 *
 * Figma: Why UpSkill Me 49325:15293 (390: 49372:42258). Centred eyebrow,
 * headline, intro and button, a badge either side (desktop) and "the ladder"
 * row of numbered steps underneath.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$badges = array_slice( array_values( array_filter( (array) get_field( 'badges' ), static fn( $badge ) => ! empty( $badge['title'] ) ) ), 0, 2 );
$ladder = (array) get_field( 'ladder' );
$steps  = ! empty( $ladder['steps'] ) ? array_values( array_filter( (array) $ladder['steps'], static fn( $step ) => ! empty( $step['title'] ) ) ) : array();

// Inner pages leave 56px under the header before the hero section (Figma frames start every inner hero at y 176).
$attributes = upskill_block_attributes( $block, 'block-ladder-hero overflow-hidden bg-gray-25 pt-7 pb-[46px] lg:pt-14 lg:pb-20' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php // The badges share the copy's grid cell so they float beside it without leaving the flow. ?>
		<div class="grid grid-cols-1 grid-rows-1">
			<div class="ladder-hero__copy col-start-1 row-start-1 flex flex-col items-start gap-7 sm:gap-6 lg:mx-auto lg:max-w-[792px] lg:items-center lg:text-center">
				<div class="flex flex-col items-start gap-3 sm:gap-5 lg:items-center">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'ladder-hero__heading text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="ladder-hero__text reveal max-w-[756px] text-base text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>
				<?php upskill_button( get_field( 'button' ) ); ?>
			</div>

			<?php foreach ( $badges as $index => $badge ) : ?>
				<?php
				// First badge left (purple), second right (green), as drawn.
				$right = 1 === $index;
				?>
				<p class="ladder-hero__badge reveal col-start-1 row-start-1 hidden items-center gap-2.5 self-start rounded-full border border-line bg-white py-2 pr-4 pl-2 drop-shadow-[0_12px_16px_rgb(20_12_48/7%)] lg:flex <?php echo $right ? 'mt-[129px] justify-self-end' : 'mt-[236px] justify-self-start'; ?>" style="--i:<?php echo $right ? 2 : 1; ?>">
					<span class="grid size-[30px] place-items-center rounded-full <?php echo $right ? 'bg-[#f0fbd4] text-[#3f6212]' : 'bg-brand-100 text-[#6b2fe3]'; ?>" aria-hidden="true">
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
			<?php // 1200 of the 1360 box: the ladder sits 44px inside the 1288 frame Figma draws; Figma leaves 62px under the button (its 138px intro box + 48px gap). ?>
			<div class="ladder-hero__steps reveal mt-6 rounded-[24px] border border-line bg-white px-4 py-6 shadow-xs lg:mx-auto lg:mt-[62px] mc:flex lg:max-w-[1200px] lg:px-8 lg:pt-7 lg:pb-4">
				<div class="flex flex-col gap-2.5 mc:w-[210px] lg:flex-none mc:border-r mc:border-line lg:pr-7 max-mc:mb-8 max-sm:mb-4 max-mc:text-center max-sm:text-left">
					<?php if ( ! empty( $ladder['label'] ) ) : ?>
						<p class="text-sm font-medium text-brand-700 uppercase"><?php echo esc_html( $ladder['label'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $ladder['title'] ) ) : ?>
						<p class="text-lg font-medium text-gray-950 mc:max-w-[181px]"><?php echo esc_html( $ladder['title'] ); ?></p>
					<?php endif; ?>
				</div>
				<ol class="mt-0 mc:flex-1 pl-6 grid lg:auto-cols-fr lg:grid-flow-col sm:gap-6 lg:gap-0 gap-5 lg:pl-0 max-mc:grid-cols-2 max-sm:grid-cols-1">
					<?php foreach ( $steps as $number => $step ) : ?>
						<?php $last = count( $steps ) - 1 === $number; ?>
						<li class="ladder-hero__step relative flex flex-col lg:px-6 lg:py-0.5">
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
	</div>
</section>
