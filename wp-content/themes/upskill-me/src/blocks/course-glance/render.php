<?php
/**
 * At a Glance block (course page).
 *
 * Figma: Product Detail "At a glance" 49325:20672 (1440).
 *
 * An eyebrow over one bordered strip of fact tiles: icon, title, line of text,
 * six across at desktop. A tile can take its title from the course
 * (upskill_course_fact(): session count, total duration, learning level,
 * certificate), so the facts stay true when sessions change; a tile whose
 * source has no value is left out.
 *
 * @param array $block   The block settings and attributes.
 * @param int   $post_id The post the block belongs to.
 *
 * @package upskill-me
 */

$course_id = function_exists( 'upskill_block_course_id' ) ? upskill_block_course_id( $post_id ?? 0 ) : 0;
$items     = array();

foreach ( (array) get_field( 'items' ) as $item ) {
	$source = (string) ( $item['source'] ?? '' );
	$title  = trim( (string) ( $item['title'] ?? '' ) );

	if ( $source ) {
		$value = $course_id ? upskill_course_fact( $course_id, $source ) : '';

		if ( '' === $value ) {
			continue;
		}

		if ( 'sessions' === $source ) {
			/* translators: %d: number of sessions. */
			$title = sprintf( _n( '%d Session', '%d Sessions', (int) $value, 'upskill-me' ), (int) $value );
		} elseif ( 'certificate' !== $source || '' === $title ) {
			$title = $value;
		}
	}

	if ( '' !== $title ) {
		$items[] = array(
			'icon'  => (string) ( $item['icon'] ?? '' ),
			'title' => $title,
			'text'  => (string) ( $item['text'] ?? '' ),
		);
	}
}

if ( ! $items ) {
	return;
}

$attributes = upskill_block_attributes( $block, 'block-course-glance bg-gray-25 pt-2 pb-12 lg:pb-20' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php upskill_eyebrow( (string) get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ), 'reveal' ); ?>
		<?php // One strip; the 1px gaps over the line colour draw the dividers however the tiles wrap. ?>
		<ul class="course-glance__list mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-[20px] border border-line bg-line shadow-xs sm:grid-cols-3 lg:mt-8 lg:grid-cols-6">
			<?php foreach ( $items as $index => $item ) : ?>
				<li class="course-glance__item reveal flex flex-col bg-white px-[22px] pt-[26px] pb-[22px]" style="--i:<?php echo (int) min( $index, 5 ); ?>">
					<?php if ( $item['icon'] ) : ?>
						<span class="course-glance__icon grid size-11 place-items-center rounded-[13px] bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( $item['icon'], 'size-5' ); ?></span>
					<?php endif; ?>
					<span class="course-glance__title pt-3.5 text-[24px] leading-[1.55] font-medium text-gray-950 lg:text-xl lg:leading-[1.5]"><?php echo esc_html( $item['title'] ); ?></span>
					<?php if ( $item['text'] ) : ?>
						<span class="course-glance__text pt-2 text-sm leading-[1.55] text-[#8b8b97] lg:pt-3.5 lg:text-base lg:leading-normal lg:text-gray-500"><?php echo esc_html( $item['text'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
