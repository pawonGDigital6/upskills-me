<?php
/**
 * Course Reviews block (course page).
 *
 * No Figma section: the hero's rating ("4.8 · 24 reviews", Figma 49325:20557)
 * links here ("Read reviews"). The split section header, a summary card (the
 * average, its stars and the count) and the newest approved reviews as cards
 * in the Testimonials block's style (lilac card, quote mark, amber stars,
 * title, text, name).
 *
 * The reviews are LearnDash Course Reviews (comments of type ld_review with
 * "rating" and "review_title" meta): learners who have started a course leave
 * them on LearnDash's own course page (Reviews tab); admins approve and edit
 * them under Comments. Hidden while the course has reviews switched off
 * (Course → Settings → Show reviews) or has none approved yet.
 *
 * @param array $block      The block settings and attributes.
 * @param int   $post_id    The post the block belongs to.
 * @param bool  $is_preview True in the editor.
 *
 * @package upskill-me
 */

$course_id = function_exists( 'upskill_block_course_id' ) ? upskill_block_course_id( $post_id ?? 0 ) : 0;
$summary   = $course_id ? upskill_course_reviews( $course_id ) : array( 'enabled' => false, 'count' => 0, 'average' => 0 );

if ( ! $summary['enabled'] || ! $summary['count'] ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="container py-6 text-sm text-gray-500">' . esc_html__( 'Course Reviews: shown once "Show reviews" is on in this course\'s Settings and at least one review is approved (Comments).', 'upskill-me' ) . '</p>';
	}
	return;
}

$reviews = get_comments(
	array(
		'post_id' => $course_id,
		'type'    => 'ld_review',
		'status'  => 'approve',
		'parent'  => 0,
		'number'  => 6,
		'orderby' => 'comment_date_gmt',
		'order'   => 'DESC',
	)
);

// The hero's "Read reviews" link targets #reviews.
if ( empty( $block['anchor'] ) ) {
	$block['anchor'] = 'reviews';
}

$stars = static function ( $rating, $classes ) {
	$rating = (int) round( min( 5, max( 0, (float) $rating ) ) );
	?>
	<span class="flex gap-1">
		<span class="screen-reader-text">
			<?php
			/* translators: %d: rating out of five. */
			printf( esc_html__( 'Rated %d out of 5', 'upskill-me' ), (int) $rating );
			?>
		</span>
		<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
			<?php upskill_icon( 'star', $classes . ' ' . ( $star <= $rating ? 'text-[#fbbf24]' : 'text-gray-200' ) ); ?>
		<?php endfor; ?>
	</span>
	<?php
};

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

		<div class="mt-10 grid gap-4 lg:mt-12 lg:grid-cols-3">
			<div class="course-reviews__summary reveal flex flex-col justify-center gap-3 rounded-[24px] bg-brand-700 p-6 text-white lg:self-start lg:p-10">
				<p class="text-[64px] leading-none font-medium"><?php echo esc_html( number_format_i18n( $summary['average'], 1 ) ); ?></p>
				<?php $stars( $summary['average'], 'size-6' ); ?>
				<p class="text-base text-white/80">
					<?php
					/* translators: %d: number of reviews. */
					echo esc_html( sprintf( _n( 'Based on %d review', 'Based on %d reviews', $summary['count'], 'upskill-me' ), $summary['count'] ) );
					?>
				</p>
			</div>

			<ul class="course-reviews__list grid gap-4 sm:grid-cols-2 lg:col-span-2">
				<?php foreach ( $reviews as $index => $review ) : ?>
					<?php $title = (string) get_comment_meta( $review->comment_ID, 'review_title', true ); ?>
					<li class="course-reviews__item reveal flex flex-col rounded-[24px] bg-brand-50 p-5 lg:p-6" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<div class="flex items-center justify-between gap-4">
							<?php upskill_icon( 'quote', 'h-[30px] w-8 text-brand-500' ); ?>
							<?php $stars( get_comment_meta( $review->comment_ID, 'rating', true ), 'size-4' ); ?>
						</div>
						<blockquote class="flex flex-col pt-6">
							<?php if ( $title ) : ?>
								<p class="text-lg font-medium text-gray-950"><?php echo esc_html( $title ); ?></p>
							<?php endif; ?>
							<div class="pt-2 text-base text-gray-700"><?php echo wp_kses_post( wpautop( $review->comment_content ) ); ?></div>
						</blockquote>
						<p class="mt-auto flex flex-wrap items-baseline justify-between gap-x-3 pt-6 text-sm">
							<span class="text-base font-medium text-gray-950"><?php echo esc_html( $review->comment_author ); ?></span>
							<span class="text-gray-500"><?php echo esc_html( mysql2date( get_option( 'date_format' ), $review->comment_date ) ); ?></span>
						</p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
