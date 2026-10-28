<?php
/**
 * Heading Hero block.
 *
 * Eyebrow, headline and intro, nothing else. Centred: Case Studies 49325:18138
 * (390: 49374:106465). Left on a pale lilac panel: FAQs 49325:17983.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$left = 'left' === get_field( 'align' );

// Inner pages leave 56px under the header before the hero section (Figma frames start every inner hero at y 176).
$attributes = upskill_block_attributes( $block, 'block-heading-hero overflow-hidden bg-gray-25 pt-7 pb-[46px] lg:pt-14 lg:pb-20' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php if ( $left ) : ?>
			<div class="heading-hero__copy flex flex-col gap-6 lg:bg-brand-25">
				<div class="flex flex-col items-start gap-3">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'heading-hero__heading text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="heading-hero__text reveal text-lead text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="heading-hero__copy flex flex-col items-start gap-7 sm:gap-6 lg:mx-auto lg:max-w-[792px] lg:items-center lg:text-center">
				<div class="flex flex-col items-start gap-3 sm:gap-5 lg:items-center">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'heading-hero__heading text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="heading-hero__text reveal max-w-[756px] text-base text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
