<?php
/**
 * Mission Pillars block.
 *
 * Figma: About "Our purpose" 49325:14730.
 *
 * A centred section header (with its optional button) over three numbered
 * pillars, each headed by a purple accent bar, on the faint waves background.
 * Each pillar takes Figma's black textured look on hover
 * (src/global/scss/components/_feature-card.scss).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$pillars = array_values(
	array_filter(
		(array) get_field( 'pillars' ),
		static function ( $pillar ) {
			return ! empty( $pillar['title'] );
		}
	)
);

$attributes = upskill_block_attributes( $block, 'block-mission-pillars section-y relative isolate bg-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="mission-pillars__background absolute inset-0 -z-10 size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'how-it-works-bg-2.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'center',
				'reveal' => true,
			)
		);
		?>

		<?php if ( $pillars ) : ?>
			<ul class="mission-pillars__list mt-12 grid gap-4 sm:grid-cols-2 lg:mt-15 lg:grid-cols-3">
				<?php foreach ( $pillars as $index => $pillar ) : ?>
					<li class="mission-pillars__item feature-card reveal group relative isolate flex flex-col overflow-hidden rounded-[22px] border border-line bg-white shadow-xs hover:border-transparent" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<span class="feature-card__surface pointer-events-none absolute inset-0 -z-10 bg-gray-950 opacity-0 group-hover:opacity-100" aria-hidden="true">
							<img class="feature-card__texture size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
						</span>
						<div class="feature-card__body flex flex-1 flex-col p-[30px]">
							<span class="feature-card__accent h-[3px] w-[34px] rounded-full bg-[#6b2fe3]" aria-hidden="true"></span>
							<div class="feature-card__content flex flex-col pt-[22px]">
								<span class="feature-card__number text-sm text-gray-500" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<h3 class="feature-card__title pt-2.5 text-h6 font-medium text-gray-950 group-hover:text-white"><?php echo esc_html( $pillar['title'] ); ?></h3>
								<?php if ( ! empty( $pillar['text'] ) ) : ?>
									<p class="feature-card__text pt-2.5 text-base text-gray-500 group-hover:text-gray-100 min-[1201px]:max-w-[326px]"><?php echo esc_html( $pillar['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
