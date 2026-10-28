<?php
/**
 * Training Needs block.
 *
 * Figma: industry page "Key training needs" 49325:14836.
 *
 * A split section header over a grey frame of numbered cards: title, text and
 * a bullet list of what each need covers. Each card takes Figma's purple
 * textured look on hover (src/global/scss/components/_feature-card.scss).
 * Used in the industry page pattern, so {term} and {term_lower} in the copy
 * become the industry's name (template-parts/library/term-page.php).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$needs = array_values(
	array_filter(
		(array) get_field( 'needs' ),
		static function ( $need ) {
			return ! empty( $need['title'] );
		}
	)
);

$attributes = upskill_block_attributes( $block, 'block-training-needs section-y bg-brand-25' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'reveal' => true,
			)
		);
		?>

		<?php if ( $needs ) : ?>
			<div class="training-needs__frame mt-[46px] rounded-[32px] lg:mt-12 bg-gray-100 p-2 sm:p-4">
				<ul class="training-needs__list grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( $needs as $index => $need ) : ?>
						<?php $points = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $need['points'] ?? '' ) ) ) ); ?>
						<?php // Figma 49325:15015 / 49354:95613: 20px corners, 16 (phones) / 24 padding; the first card draws the hover look, purple texture with white text. ?>
						<li class="training-needs__item feature-card reveal group relative isolate flex flex-col overflow-hidden rounded-[20px] border border-gray-100 bg-white shadow-xs hover:border-transparent" style="--i:<?php echo (int) min( $index, 5 ); ?>">
							<span class="training-needs__surface pointer-events-none absolute inset-0 -z-10 bg-[#2f1a51] opacity-0 group-hover:opacity-100" aria-hidden="true">
								<img class="training-needs__texture size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'card-bg-grph-portr.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
							</span>
							<div class="training-needs__body flex flex-1 flex-col p-4 lg:p-6">
								<div class="training-needs__content flex flex-col">
									<?php // A 44px box, the title 93px below its top. ?>
									<span class="training-needs__number mb-[49px] grid size-11 place-items-center rounded-[2px] border border-gray-950/10 text-lg font-medium text-gray-500 group-hover:border-brand-100 group-hover:text-white" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
									<h3 class="training-needs__title text-lg leading-[1.5] font-medium text-gray-950 group-hover:text-white"><?php echo esc_html( $need['title'] ); ?></h3>
									<?php if ( ! empty( $need['text'] ) ) : ?>
										<?php // Figma #414651 (Untitled UI gray-600; no theme token). ?>
										<p class="training-needs__text pt-4 text-base text-[#414651] group-hover:text-white"><?php echo esc_html( $need['text'] ); ?></p>
									<?php endif; ?>
									<?php if ( $points ) : ?>
										<ul class="training-needs__points flex list-disc flex-col pt-2 pl-5 text-base text-[#414651] marker:text-[#414651] group-hover:text-white group-hover:marker:text-white">
											<?php foreach ( $points as $point ) : ?>
												<li class="training-needs__point"><?php echo esc_html( $point ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
