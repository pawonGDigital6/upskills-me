<?php
/**
 * Impact Highlights block.
 *
 * Figma: Case Studies "The impact" 49325:18246.
 *
 * A dark textured section: a stacked header (20px intro) over translucent
 * cards of icon, title and text, three to a row at desktop.
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

$attributes = upskill_block_attributes( $block, 'block-impact-highlights section-y is-dark relative isolate bg-[#0e0e10] text-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="impact-highlights__background absolute inset-0 -z-10 size-full object-cover opacity-20" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'stack',
				'dark'   => true,
				'reveal' => true,
				'class'  => 'section-header--intro-xl',
			)
		);
		?>

		<?php if ( $highlights ) : ?>
			<ul class="impact-highlights__list mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $highlights as $index => $highlight ) : ?>
					<li class="impact-highlights__item reveal relative flex flex-col rounded-[24px] border border-white/14 bg-white/4 p-6" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<?php if ( ! empty( $highlight['icon'] ) ) : ?>
							<span class="impact-highlights__icon grid size-10 place-items-center rounded-[11px] bg-white/10 text-white" aria-hidden="true"><?php upskill_icon( $highlight['icon'], 'size-[19px]' ); ?></span>
						<?php endif; ?>
						<h3 class="impact-highlights__title pt-5 text-xl font-medium text-white"><?php echo esc_html( $highlight['title'] ); ?></h3>
						<?php if ( ! empty( $highlight['text'] ) ) : ?>
							<p class="impact-highlights__text pt-3 text-base text-gray-100"><?php echo esc_html( $highlight['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
