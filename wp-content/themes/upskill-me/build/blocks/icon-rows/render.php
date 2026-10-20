<?php
/**
 * Icon Rows block.
 *
 * Figma: Project Case Study Detail 49325:16718 (1440) / 49374:107273 (390),
 * "The solution".
 *
 * A dark textured section: a stacked header over ruled rows of icon, title
 * and text at desktop, outlined cards on phones.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$rows = array_values(
	array_filter(
		(array) get_field( 'rows' ),
		static function ( $row ) {
			return ! empty( $row['title'] );
		}
	)
);

$attributes = upskill_block_attributes( $block, 'block-icon-rows section-y is-dark relative isolate bg-[#0e0e10] text-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="icon-rows__background absolute inset-0 -z-10 size-full object-cover opacity-20" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'stack',
				'dark'   => true,
				'reveal' => true,
				'class'  => 'section-header--rows',
			)
		);
		?>

		<?php if ( $rows ) : ?>
			<?php // 44 | title | text across ruled rows at desktop (rules overlap by 1px); outlined cards on phones. ?>
			<ul class="icon-rows__list mt-10 flex flex-col gap-4 lg:gap-0">
				<?php foreach ( $rows as $index => $row ) : ?>
					<li class="icon-rows__item reveal flex flex-col gap-6 rounded-[24px] border border-gray-800 p-4 lg:-mt-px lg:grid lg:grid-cols-[44px_minmax(0,1fr)_minmax(0,1fr)] lg:items-start lg:gap-6 lg:rounded-none lg:border-x-0 lg:border-white/12 lg:px-0 lg:py-2" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<div class="icon-rows__head flex flex-col gap-3 lg:contents">
							<span class="icon-rows__icon grid size-10 flex-none place-items-center rounded-full bg-white/8 text-signal" aria-hidden="true"><?php upskill_icon( $row['icon'] ? $row['icon'] : 'check', 'size-[18px]' ); ?></span>
							<h3 class="icon-rows__title text-xl leading-[1.62] font-bold tracking-[-0.014em] text-white lg:pt-1.5"><?php echo esc_html( $row['title'] ); ?></h3>
						</div>
						<?php if ( ! empty( $row['text'] ) ) : ?>
							<p class="icon-rows__text text-sm leading-[1.6] tracking-[-0.007em] text-white/65 lg:max-w-[432px]"><?php echo esc_html( $row['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
