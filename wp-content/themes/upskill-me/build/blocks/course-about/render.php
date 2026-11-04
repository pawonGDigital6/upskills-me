<?php
/**
 * About This Training block (course page).
 *
 * Figma: Product Detail "About this training" 49325:20737 (1440).
 *
 * The stacked section header (eyebrow, heading, the course text as its intro)
 * beside a white card with the outcomes checklist ("This course is designed to
 * help people:"), over the silk texture.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$list = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_field( 'list' ) ) ) );

if ( ! $list && ! get_field( 'heading' ) && ! get_field( 'intro' ) ) {
	return;
}

$attributes = upskill_block_attributes( $block, 'block-course-about grid grid-cols-1 grid-rows-1 overflow-hidden bg-gray-25 *:col-start-1 *:row-start-1' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<?php // Figma's silk photograph fills the section (theme image, cropped from the Figma original). ?>
	<img class="course-about__texture size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'texture-silk.webp' ) ); ?>" alt="" width="1024" height="384" loading="lazy" decoding="async">
	<div class="container section-y relative grid gap-10 lg:grid-cols-[1fr_50.9%] lg:items-start lg:gap-16">
		<?php
		upskill_section_header(
			array(
				'layout' => 'stack',
				'button' => false,
				'reveal' => true,
			)
		);
		?>
		<?php if ( $list ) : ?>
			<div class="course-about__card reveal rounded-[24px] bg-white p-6 shadow-xs" style="--i:1">
				<?php if ( get_field( 'list_title' ) ) : ?>
					<h3 class="course-about__list-title text-[20px] font-medium text-gray-950"><?php echo esc_html( (string) get_field( 'list_title' ) ); ?></h3>
				<?php endif; ?>
				<ul class="course-about__list mt-6 flex flex-col gap-3 lg:pl-7">
					<?php foreach ( $list as $item ) : ?>
						<li class="course-about__item flex items-start gap-3 text-base text-gray-500">
							<span class="mt-0.5 grid size-5 flex-none place-items-center rounded-full bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( 'check', 'size-2.5' ); ?></span>
							<?php echo esc_html( $item ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
