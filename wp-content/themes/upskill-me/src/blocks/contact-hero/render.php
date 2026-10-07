<?php
/**
 * Contact Hero block.
 *
 * Figma: Contact 49325:20089 (1440) / 49375:115528 (390).
 *
 * The headline, intro, buttons and note sit on a darkened photo; the enquiry
 * form (Contact Form 7) floats in a white card on its right. Phones stack the
 * photo card above the form card.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$image = get_field( 'image' );
$form  = (int) get_field( 'form' );
$note  = get_field( 'note' );

$attributes = upskill_block_attributes( $block, 'block-contact-hero bg-gray-25 pt-[30px] pb-[46px] lg:pt-14 lg:pb-0' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php // From desktop the photo is the background of the whole card (the copy's layers position against it). ?>
		<div class="relative isolate grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,559px)] lg:gap-20 lg:overflow-hidden lg:rounded-[24px] lg:p-10">
			<div class="relative isolate flex flex-col justify-center overflow-hidden rounded-[24px] px-4 py-[120px] sm:px-10 sm:py-20 lg:static lg:overflow-visible lg:rounded-none lg:p-6">
				<?php if ( $image ) : ?>
					<?php
					upskill_image(
						$image,
						'full',
						array(
							'class'   => 'absolute inset-0 -z-20 size-full object-cover',
							'loading' => 'eager',
							// 1360x916 at desktop draws the 3:2 photo 1374 wide; 358x730 on phones about 1095.
							'sizes'   => '(min-width: 1025px) 96vw, (min-width: 640px) 120vw, 300vw',
						)
					);
					?>
				<?php endif; ?>
				<span class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgb(0_0_0/0%)_-15%,rgb(0_0_0/80%)_100%)]" aria-hidden="true"></span>

				<div class="flex flex-col items-start gap-3">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-semibold text-white' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="reveal mt-6 text-xl text-gray-100" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>

				<?php if ( get_field( 'primary_button' ) || get_field( 'secondary_button' ) ) : ?>
					<div class="reveal flex flex-col items-start gap-2 pt-8 sm:flex-row sm:flex-wrap" style="--i:2">
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
					<p class="flex items-center gap-2 pt-4 font-[Geist] text-[13px] leading-[1.62] tracking-[-0.007em] text-gray-100">
						<?php upskill_icon( 'check', 'size-3.5 flex-none' ); ?>
						<?php echo esc_html( $note ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="reveal relative rounded-[32px] bg-white px-4 py-6 shadow-xs lg:self-start lg:p-6 lg:shadow-[0_32px_70px_-20px_rgb(47_26_81/20%),0_4px_10px_rgb(26_26_26/3%)]" style="--i:2">
				<?php if ( get_field( 'form_title' ) ) : ?>
					<h2 class="text-[19.2px] leading-[1.3] font-medium tracking-[-0.016em] text-gray-900"><?php echo esc_html( get_field( 'form_title' ) ); ?></h2>
				<?php endif; ?>
				<?php if ( get_field( 'form_text' ) ) : ?>
					<p class="pt-1.5 font-[Geist] text-[15px] leading-[1.62] tracking-[-0.007em] text-[#55555a]"><?php echo esc_html( get_field( 'form_text' ) ); ?></p>
				<?php endif; ?>
				<?php if ( $form && 'wpcf7_contact_form' === get_post_type( $form ) ) : ?>
					<div class="pt-5">
						<?php echo do_shortcode( '[contact-form-7 id="' . $form . '" html_class="upskill-form"]' ); ?>
					</div>
				<?php endif; ?>
				<?php if ( get_field( 'form_note' ) ) : ?>
					<p class="flex items-center gap-2 pt-4 font-[Geist] text-xs leading-[1.62] tracking-[-0.008em] text-[#6b6b72]">
						<?php upskill_icon( 'check', 'size-[13px] flex-none text-brand-700' ); ?>
						<?php echo esc_html( get_field( 'form_note' ) ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
