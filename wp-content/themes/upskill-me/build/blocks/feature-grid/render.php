<?php
/**
 * Feature Grid block.
 *
 * Figma: Why UpSkill Me "Why it works" 49325:15613; About "Why we exist"
 * 49325:14680.
 *
 * A split section header over three or four columns of icon cards. Three
 * columns use the roomy 30px card (Why), four the compact 16px card (About).
 * Every card is white and takes Figma's black textured look on hover
 * (src/global/scss/components/_feature-card.scss).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$cards = array_values(
	array_filter(
		(array) get_field( 'cards' ),
		static function ( $card ) {
			return ! empty( $card['title'] ) || ! empty( $card['label'] );
		}
	)
);

$columns    = 4 === (int) get_field( 'columns' ) ? 4 : 3;
$roomy      = 3 === $columns;
$numbered   = (bool) get_field( 'show_numbers' );
$attributes = upskill_block_attributes( $block, 'block-feature-grid section-y bg-white' );
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

		<?php if ( $cards ) : ?>
			<ul class="feature-grid__list mt-stack grid gap-4 sm:grid-cols-2 <?php echo 4 === $columns ? 'lg:grid-cols-4' : 'lg:grid-cols-3'; ?>">
				<?php foreach ( $cards as $index => $card ) : ?>
					<li class="feature-grid__item feature-card reveal group relative isolate flex flex-col overflow-hidden rounded-[22px] border border-line bg-white shadow-xs hover:border-transparent" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<span class="feature-grid__surface pointer-events-none absolute inset-0 -z-10 bg-gray-950 opacity-0 group-hover:opacity-100" aria-hidden="true">
							<img class="feature-grid__texture size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
						</span>
						<div class="feature-grid__body flex flex-1 flex-col <?php echo $roomy ? 'p-[30px]' : 'min-h-[240px] p-4'; ?>">
							<?php if ( ! empty( $card['icon'] ) ) : ?>
								<span class="feature-grid__icon grid size-11 place-items-center rounded-[13px] bg-[#F3F1FB] text-brand-700 group-hover:bg-white/7 group-hover:text-white" aria-hidden="true"><?php upskill_icon( $card['icon'], 'size-[21px]' ); ?></span>
							<?php endif; ?>
							<div class="feature-grid__content flex flex-col <?php echo empty( $card['icon'] ) ? '' : ( $roomy ? 'pt-5 lg:pt-10' : 'pt-11' ); ?>">
								<?php if ( $numbered ) : ?>
									<?php // Purple when it heads a label (About "Why we exist"), grey when it heads the title. ?>
									<span class="feature-grid__number <?php echo empty( $card['label'] ) ? 'text-sm text-gray-500' : 'text-[13px] font-semibold tracking-[0.1em] text-[#6b2fe3]'; ?>" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $card['label'] ) ) : ?>
									<span class="feature-grid__label pt-2 text-sm font-medium text-gray-500 uppercase group-hover:text-white"><?php echo esc_html( $card['label'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $card['title'] ) ) : ?>
									<h3 class="feature-grid__title <?php echo ! empty( $card['label'] ) ? 'pt-4' : ( $numbered ? 'pt-2.5' : '' ); ?> text-lg font-medium text-gray-950 group-hover:text-white"><?php echo esc_html( $card['title'] ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $card['text'] ) ) : ?>
									<p class="feature-grid__text pt-2.5 text-base text-gray-500 group-hover:text-gray-100"><?php echo esc_html( $card['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
