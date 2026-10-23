<?php
/**
 * Outcome Columns block.
 *
 * Figma: Project Case Study Detail 49325:16815 (1440), "The impact".
 *
 * A split section header over the columns of one outlined box (label, rule,
 * large title, text); separate boxes on phones. Faint waves behind.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$columns = array_values(
	array_filter(
		(array) get_field( 'columns' ),
		static function ( $column ) {
			return ! empty( $column['title'] );
		}
	)
);

$attributes = upskill_block_attributes( $block, 'block-outcome-columns section-y relative isolate bg-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="outcome-columns__background absolute inset-0 -z-10 size-full object-cover opacity-[0.08]" src="<?php echo esc_url( upskill_theme_image( 'impact-waves.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'reveal' => true,
			)
		);
		?>

		<?php if ( $columns ) : ?>
			<ul class="outcome-columns__list mt-6 grid gap-4 lg:grid-cols-3 lg:gap-0 lg:overflow-hidden lg:rounded-[24px] lg:border lg:border-[#111827]/14">
				<?php foreach ( $columns as $index => $column ) : ?>
					<li class="outcome-columns__item reveal flex flex-col border border-[#111827]/14 px-6 py-12 lg:border-0 lg:px-8 lg:first:pl-6 lg:not-last:border-r lg:not-last:border-[#111827]/14" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<p class="outcome-columns__label flex items-center gap-2.5 text-sm font-medium text-[#111827] uppercase">
							<?php upskill_icon( $column['icon'] ? $column['icon'] : 'check', 'outcome-columns__icon size-4 flex-none text-gray-800' ); ?>
							<?php echo esc_html( $column['label'] ? $column['label'] : __( 'Outcome', 'upskill-me' ) ); ?>
						</p>
						<span class="outcome-columns__rule mt-5 h-0.5 w-10 rounded-[1px] bg-[#111827]" aria-hidden="true"></span>
						<h3 class="outcome-columns__title pt-5 text-[36px] leading-[1.1] font-medium tracking-[-0.01em] text-[#111827]"><?php echo esc_html( $column['title'] ); ?></h3>
						<?php if ( ! empty( $column['text'] ) ) : ?>
							<p class="outcome-columns__text max-w-[340px] pt-[30px] text-base leading-[1.6] tracking-[-0.006em] text-gray-500"><?php echo esc_html( $column['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
