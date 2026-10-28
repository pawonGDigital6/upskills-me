<?php
/**
 * Expertise Highlights block.
 *
 * Figma: About "Real-world experience" 49325:14758 (1440) / 49374:47139 (390).
 *
 * The section header (20px intro) and a photo in the left column; four
 * numbered icon cards two-up on the right. Each card takes Figma's black
 * textured look on hover (src/global/scss/components/_feature-card.scss).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$highlights = array_values(
	array_filter(
		(array) get_field( 'highlights' ),
		static function ( $highlight ) {
			return ! empty( $highlight['title'] );
		}
	)
);

$image      = get_field( 'image' );
$attributes = upskill_block_attributes( $block, 'block-expertise-highlights section-y bg-brand-50' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="expertise-highlights__layout flex flex-col gap-10 lg:flex-row lg:items-center lg:gap-14">
			<div class="expertise-highlights__aside flex flex-col gap-10 lg:min-w-0 lg:flex-1 lg:self-stretch">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'button' => false,
						'reveal' => true,
						'class'  => 'section-header--intro-xl',
					)
				);
				?>

				<?php
				if ( $image ) {
					upskill_image(
						$image,
						'full',
						array(
							'class' => 'expertise-highlights__image reveal aspect-[358/240] w-full rounded-[24px] object-cover lg:aspect-[652/240]',
							// 652x240 at desktop draws the 3:2 photo 652 wide; the column is full width below lg.
							'sizes' => '(min-width: 1025px) 46vw, 92vw',
						)
					);
				}
				?>
			</div>

			<?php if ( $highlights ) : ?>
				<div class="expertise-highlights__cards lg:min-w-0 lg:flex-1 lg:self-stretch lg:pt-10">
					<ul class="expertise-highlights__list grid gap-4 sm:grid-cols-2">
						<?php foreach ( $highlights as $index => $highlight ) : ?>
							<li class="expertise-highlights__item feature-card reveal group relative isolate flex flex-col overflow-hidden rounded-[22px] border border-line bg-white shadow-xs hover:border-transparent" style="--i:<?php echo (int) min( $index, 5 ); ?>">
								<span class="expertise-highlights__surface pointer-events-none absolute inset-0 -z-10 bg-gray-950 opacity-0 group-hover:opacity-100" aria-hidden="true">
									<img class="expertise-highlights__texture size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
								</span>
								<?php // Phones: 30px padding, 18px titles, 24px under the icon (Figma 49374:47139). ?>
								<div class="expertise-highlights__body flex min-h-[273px] flex-1 flex-col p-[30px] lg:p-4">
									<?php if ( ! empty( $highlight['icon'] ) ) : ?>
										<span class="expertise-highlights__icon grid size-11 place-items-center rounded-[13px] bg-[#F3F1FB] text-brand-700 group-hover:bg-white/7 group-hover:text-white" aria-hidden="true"><?php upskill_icon( $highlight['icon'], 'size-[21px]' ); ?></span>
									<?php endif; ?>
									<div class="expertise-highlights__content flex flex-col <?php echo empty( $highlight['icon'] ) ? '' : 'pt-6 lg:pt-12.5'; ?>">
										<span class="expertise-highlights__number text-xs font-semibold tracking-[0.1em] text-[#8b8b97]" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
										<h3 class="expertise-highlights__title pt-2.5 text-lg font-medium text-gray-950 group-hover:text-white lg:text-h6"><?php echo esc_html( $highlight['title'] ); ?></h3>
										<?php if ( ! empty( $highlight['text'] ) ) : ?>
											<p class="expertise-highlights__text pt-2 text-base text-gray-500 group-hover:text-gray-100"><?php echo esc_html( $highlight['text'] ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
