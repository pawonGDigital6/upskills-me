<?php
/**
 * Course Reviews block (course page).
 *
 * No Figma section: the hero's rating ("4.8 · 24 reviews", Figma 49325:20557)
 * links here ("Read reviews"). The split section header over LearnDash Course
 * Reviews' own output: the average and the approved reviews. Hidden while the
 * course has reviews switched off (Course → Settings → Show reviews).
 *
 * @param array $block   The block settings and attributes.
 * @param int   $post_id The post the block belongs to.
 *
 * @package upskill-me
 */

$course_id = function_exists( 'upskill_block_course_id' ) ? upskill_block_course_id( $post_id ?? 0 ) : 0;

if ( ! $course_id || ! function_exists( 'learndash_course_reviews_locate_template' ) || ! upskill_course_reviews( $course_id )['enabled'] ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="container py-6 text-sm text-gray-500">' . esc_html__( 'Course Reviews: switch on "Show reviews" in this course\'s Settings to show this section.', 'upskill-me' ) . '</p>';
	}
	return;
}

// The hero's "Read reviews" link targets #reviews.
if ( empty( $block['anchor'] ) ) {
	$block['anchor'] = 'reviews';
}

$attributes = upskill_block_attributes( $block, 'block-course-reviews section-y scroll-mt-24 bg-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'button' => false,
				'reveal' => true,
			)
		);
		?>
		<div class="course-reviews__body mt-10 lg:mt-12">
			<?php
			// LearnDash's average and review list. Its review form is left to LearnDash's own
			// course page (Reviews tab): only learners who have started the course can review,
			// and they get that page instead of this one.
			?>
			<div class="learndash-course-reviews-container">
				<?php do_action( 'learndash_course_reviews_average_review', $course_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LearnDash hook. ?>
				<?php do_action( 'learndash_course_reviews_review_list', $course_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LearnDash hook. ?>
			</div>
		</div>
	</div>
</section>
