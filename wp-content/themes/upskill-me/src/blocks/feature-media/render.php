<?php
/**
 * Feature List With Media block.
 *
 * Figma: Homepage section 49325:14505 (1440) / Section 49196:7753 (390) — "Why
 * UpSkill Me". A dark section: heading and a list of features on the left, a
 * framed photograph with a stat card on the right that stays in view while the
 * list scrolls past. The feature nearest the middle of the screen is lit.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-feature-media is-dark relative isolate section-y bg-gray-950 text-white' );
$features   = array_filter(
	(array) get_field( 'features' ),
	static function ( $feature ) {
		return ! empty( $feature['title'] );
	}
);
$image      = get_field( 'image' );
$stat       = get_field( 'stat' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="absolute inset-0 -z-10 size-full object-cover opacity-20" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php // 537 + 104 + 630 = 1271, centred in the 1360 content box. ?>
		<div class="mx-auto grid max-w-[1271px] gap-12 lg:grid-cols-[537fr_630fr] lg:gap-[clamp(48px,7.2vw,104px)]">
			<div>
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'dark'   => true,
						'button' => false,
					)
				);
				?>

				<?php if ( $features ) : ?>
					<ul class="mt-10 flex flex-col gap-2 lg:mt-14" data-features>
						<?php foreach ( array_values( $features ) as $index => $feature ) : ?>
							<li class="feature flex gap-4 rounded-2xl border border-white/14 bg-white/4 p-5<?php echo 0 === $index ? ' is-active' : ''; ?>" data-feature>
								<span class="mt-[7.6px] size-[11px] flex-none rotate-45 bg-brand-400" aria-hidden="true"></span>
								<span class="flex flex-col gap-[5px]">
									<span class="text-h6 font-medium text-white"><?php echo esc_html( $feature['title'] ); ?></span>
									<?php if ( ! empty( $feature['text'] ) ) : ?>
										<span class="text-base text-brand-100"><?php echo esc_html( $feature['text'] ); ?></span>
									<?php endif; ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<?php if ( $image ) : ?>
				<div class="flex flex-col items-start gap-10 self-start lg:sticky lg:gap-[85px] lg:top-[calc(var(--upskill-header-height)-44px+24px)] lg:pt-40">
					<div class="relative w-full pb-6 lg:pb-0">
						<div class="rounded-[28px] border border-white/14 bg-[linear-gradient(164.6deg,rgb(179_157_227/14%)_8.5%,rgb(192_174_232/8.5%)_50%,rgb(255_255_255/3%)_91.5%)] p-2 lg:rounded-[40px]">
							<?php
							upskill_image(
								$image,
								'large',
								array(
									'class' => 'aspect-[354/470] w-full lg:aspect-[614/460] rounded-[22px] bg-[#151519] object-cover shadow-float lg:rounded-[32px]',
									'sizes' => '(min-width: 1025px) 614px, 92vw',
								)
							);
							?>
						</div>
						<?php if ( ! empty( $stat['number'] ) ) : ?>
							<p class="absolute bottom-0 left-4 flex max-w-[260px] flex-col gap-1.5 rounded-2xl bg-brand-200 px-6 py-5 text-brand-700 shadow-[0_4px_10px_rgb(26_26_26/3%),0_32px_70px_rgb(47_26_81/22%)] lg:top-[77%] lg:-right-2 lg:bottom-auto lg:left-auto lg:w-[260px]">
								<span class="text-[40px] leading-none font-semibold tracking-[-0.03em]"><?php echo esc_html( $stat['number'] ); ?></span>
								<?php if ( ! empty( $stat['text'] ) ) : ?>
									<span class="font-[Geist] text-xs leading-[1.35] tracking-[-0.008em]"><?php echo esc_html( $stat['text'] ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
					<?php
					upskill_button(
						get_field( 'button' ),
						array(
							'variant' => 'glass',
							'size'    => 'lg',
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
