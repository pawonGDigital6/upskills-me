<?php
/**
 * Training Needs block.
 *
 * Figma: industry page "Key training needs" 49325:14836.
 *
 * A split section header over a grey frame of numbered cards: title, text and
 * a bullet list of what each need covers. Each card takes Figma's black
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
			<div class="training-needs__frame mt-stack rounded-[32px] bg-gray-100 p-2 sm:p-4">
				<ul class="training-needs__list grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( $needs as $index => $need ) : ?>
						<?php $points = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $need['points'] ?? '' ) ) ) ); ?>
						<li class="training-needs__item feature-card reveal group relative isolate flex flex-col overflow-hidden rounded-[22px] border border-line bg-white shadow-xs hover:border-transparent" style="--i:<?php echo (int) min( $index, 5 ); ?>">
							<span class="feature-card__surface pointer-events-none absolute inset-0 -z-10 bg-gray-950 opacity-0 group-hover:opacity-100" aria-hidden="true">
								<img class="feature-card__texture size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
							</span>
							<div class="feature-card__body flex flex-1 flex-col p-[30px]">
								<div class="feature-card__content flex flex-col">
									<span class="feature-card__number mb-6 grid size-8 place-items-center rounded-md border border-gray-200 text-sm text-gray-500 group-hover:border-white/40 group-hover:text-white" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
									<h3 class="feature-card__title pt-2.5 text-lg font-medium text-gray-950 group-hover:text-white"><?php echo esc_html( $need['title'] ); ?></h3>
									<?php if ( ! empty( $need['text'] ) ) : ?>
										<p class="feature-card__text pt-2.5 text-base text-gray-500 group-hover:text-gray-100"><?php echo esc_html( $need['text'] ); ?></p>
									<?php endif; ?>
									<?php if ( $points ) : ?>
										<ul class="feature-card__points flex list-disc flex-col gap-1 pt-3 pl-5 text-sm text-gray-700 marker:text-gray-500 group-hover:text-gray-100 group-hover:marker:text-white">
											<?php foreach ( $points as $point ) : ?>
												<li class="feature-card__point"><?php echo esc_html( $point ); ?></li>
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
