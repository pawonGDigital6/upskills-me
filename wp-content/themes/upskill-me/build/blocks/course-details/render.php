<?php
/**
 * Course Details block (course page).
 *
 * Figma: Product Detail "Course details" 49325:20856 (1440).
 *
 * The stacked section header beside a striped specification table. A row can
 * take its value from the course (upskill_course_fact(): category, title,
 * sessions, duration, learning level, certificate) instead of typed text; a
 * row whose source has no value is left out.
 *
 * @param array $block   The block settings and attributes.
 * @param int   $post_id The post the block belongs to.
 *
 * @package upskill-me
 */

$course_id = function_exists( 'upskill_block_course_id' ) ? upskill_block_course_id( $post_id ?? 0 ) : 0;
$rows      = array();

foreach ( (array) get_field( 'rows' ) as $row ) {
	$source = (string) ( $row['source'] ?? '' );
	$value  = trim( (string) ( $row['value'] ?? '' ) );

	if ( $source ) {
		$fact = $course_id ? upskill_course_fact( $course_id, $source ) : '';

		if ( '' === $fact ) {
			continue;
		}

		// A typed value still wins for Certificate ("Certificate of completion" by default).
		$value = 'certificate' === $source && '' !== $value ? $value : $fact;
	}

	if ( ! empty( $row['label'] ) && '' !== $value ) {
		$rows[] = array( $row['label'], $value );
	}
}

$attributes = upskill_block_attributes( $block, 'block-course-details section-y bg-gray-50' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,790px)] lg:items-start lg:gap-14">
		<?php
		upskill_section_header(
			array(
				'layout' => 'stack',
				'button' => false,
				'reveal' => true,
			)
		);
		?>
		<?php if ( $rows ) : ?>
			<dl class="course-details__table reveal overflow-hidden rounded-[20px] border border-line bg-white shadow-xs" style="--i:1">
				<?php foreach ( $rows as $row ) : ?>
					<div class="course-details__row grid grid-cols-2 gap-4 px-5 py-3.5 odd:bg-gray-25 lg:px-6">
						<dt class="course-details__label text-xs font-medium tracking-[0.08em] text-gray-500 uppercase"><?php echo esc_html( $row[0] ); ?></dt>
						<dd class="course-details__value text-sm font-medium text-gray-950"><?php echo esc_html( $row[1] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>
</section>
