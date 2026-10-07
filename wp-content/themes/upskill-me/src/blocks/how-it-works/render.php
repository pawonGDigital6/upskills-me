<?php
/**
 * How It Works block.
 *
 * Figma: Homepage section 49325:14466 (1440) / Section 49196:7751 (390).
 *
 * Two step tracks side by side inside one framed card: the dark track ("For
 * your organisation") and the light one ("For each learner"). The step under
 * the pointer or focus is highlighted, as the first one is by default.
 *
 * Optional image (Why UpSkill Me 49325:15612, About 49325:14834): a single
 * track with a photograph filling the second column of the frame.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-how-it-works section-y bg-brand-50' );
$tracks     = array_filter(
	(array) get_field( 'tracks' ),
	static function ( $track ) {
		return ! empty( $track['steps'] );
	}
);
$image      = get_field( 'image' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php upskill_section_header(); ?>

		<?php if ( $tracks ) : ?>
			<div class="relative isolate mt-6 flex flex-col overflow-hidden lg:mt-stack rounded-card border border-gray-100 bg-white shadow-[0_1px_1px_rgb(10_13_18/5%)] lg:flex-row lg:gap-6 lg:p-6">
				<img class="absolute inset-0 -z-10 size-full object-cover opacity-60" src="<?php echo esc_url( upskill_theme_image( 'how-it-works-bg.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

				<?php foreach ( $tracks as $track ) : ?>
					<?php $dark = isset( $track['theme'] ) && 'dark' === $track['theme']; ?>
					<div class="relative isolate flex flex-1 flex-col overflow-hidden rounded-lg px-3.5 py-6 lg:px-6<?php echo $image ? ' lg:py-6' : ' lg:py-12'; ?><?php echo $dark ? ' is-dark bg-brand-950' : ''; ?>">
						<img class="pointer-events-none absolute -z-10 <?php echo $dark ? 'inset-0 size-full object-cover opacity-40' : 'right-0 bottom-0 w-[45.5%] max-w-[282px] object-contain'; ?>" src="<?php echo esc_url( upskill_theme_image( $dark ? 'how-it-works-dark.webp' : 'how-it-works-mark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

						<?php if ( ! empty( $track['label'] ) ) : ?>
							<h3 class="pl-2 text-sm font-medium uppercase <?php echo $dark ? 'text-brand-100' : 'text-brand-700'; ?>"><?php echo esc_html( $track['label'] ); ?></h3>
						<?php endif; ?>

						<?php // The step flight (view.js): steps cascade in, then walk default -> active -> complete once. Step 1 starts active, as Figma draws it. ?>
						<ol class="mt-6" data-flight>
							<?php foreach ( $track['steps'] as $number => $step ) : ?>
								<?php $last = $number === count( $track['steps'] ) - 1; ?>
								<li class="step flex gap-4 pl-2" data-step data-state="<?php echo 0 === $number ? 'active' : 'default'; ?>" style="--i:<?php echo (int) $number; ?>">
									<span class="flex w-12 flex-none flex-col items-center gap-1" aria-hidden="true">
										<span class="step__dot relative grid size-8 place-items-center rounded-full border border-gray-100 bg-gray-25 text-xs font-medium text-gray-950">
											<span class="step__num"><?php echo esc_html( str_pad( (string) ( $number + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
											<?php upskill_icon( 'check', 'step__check absolute inset-0 m-auto size-[13px]' ); ?>
										</span>
										<?php if ( ! $last ) : ?>
											<span class="step__line relative min-h-[45px] w-0 flex-1 border-l border-dashed border-gray-200"><span class="step__fill"></span></span>
										<?php endif; ?>
									</span>
									<span class="step__body flex flex-1 flex-col gap-1 <?php echo $last ? '' : 'pb-4'; ?>">
										<span class="step__title <?php echo $image ? 'text-[23.2px]' : 'text-[20px] lg:text-[23.2px]'; ?> leading-[1.24] font-medium tracking-[-0.016em] <?php echo $dark ? 'text-white' : 'text-gray-900'; ?>"><?php echo esc_html( $step['title'] ); ?></span>
										<?php if ( ! empty( $step['text'] ) ) : ?>
											<span class="<?php echo $image ? 'text-base leading-normal' : 'text-[15px] leading-[1.6] lg:text-base'; ?> <?php echo $dark ? 'text-gray-100' : 'text-gray-500'; ?>"><?php echo esc_html( $step['text'] ); ?></span>
										<?php endif; ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ol>

						<?php if ( ! empty( $track['primary_button']['url'] ) || ! empty( $track['secondary_button']['url'] ) ) : ?>
							<div class="mt-auto flex flex-wrap gap-2 pt-8">
								<?php
								upskill_button( $track['primary_button'], array( 'class' => 'min-h-[50px]' ) );
								upskill_button(
									$track['secondary_button'],
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
				<?php endforeach; ?>

				<?php if ( $image ) : ?>
					<div class="relative mx-4 mb-4 overflow-hidden rounded-lg lg:m-0 lg:flex-1">
						<?php
						upskill_image(
							$image,
							'full',
							array(
								'class' => 'aspect-[324/545] size-full object-cover sm:aspect-[4/3] lg:aspect-auto lg:min-h-[542px]',
								// Half the 1312 frame on desktop (~640x542); on phones a 324x545 portrait inset in the frame (Figma 49374:45131), where the photo draws ~165vw wide.
								'sizes' => '(min-width: 1025px) 46vw, (min-width: 576px) 92vw, 165vw',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
