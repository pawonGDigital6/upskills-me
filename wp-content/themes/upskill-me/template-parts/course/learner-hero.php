<?php
/**
 * Course page, learner (or organisation admin) with access: the heading above
 * LearnDash's course view. No Figma; it follows the product page hero
 * (learndash/ld30/course.php): the same breadcrumb (Home / Courses /
 * category / course), the category eyebrow and the display title with its
 * highlighted phrase, plus the session count and running time.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type int $course_id Course ID.
 * }
 */

$upskill_course   = (int) ( $args['course_id'] ?? 0 );
$upskill_category = get_the_terms( $upskill_course, 'training_category' );
$upskill_category = is_array( $upskill_category ) ? $upskill_category[0] : null;
$upskill_term_url = $upskill_category ? get_term_link( $upskill_category ) : '';
$upskill_heading  = trim( (string) get_field( 'display_title', $upskill_course ) );
$upskill_library  = function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : home_url( '/' );
$upskill_sessions = upskill_course_sessions( $upskill_course );
$upskill_count    = count( $upskill_sessions );
$upskill_duration = function_exists( 'upskill_course_duration' ) ? upskill_course_duration( $upskill_sessions ) : '';
$upskill_meta     = array();

if ( $upskill_count ) {
	/* translators: %d: number of sessions. */
	$upskill_meta[] = sprintf( _n( '%d session', '%d sessions', $upskill_count, 'upskill-me' ), $upskill_count );
}

if ( $upskill_duration ) {
	$upskill_meta[] = $upskill_duration;
}
?>
<section class="course-learner-hero bg-gray-25 pt-6 pb-6 lg:pt-10 lg:pb-8" aria-labelledby="course-heading">
	<div class="container">
		<?php // The product page's breadcrumb (course.php). ?>
		<nav class="flex flex-wrap items-center gap-2 text-xs leading-[1.62] tracking-[-0.01em] text-[#6b6b72]" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
			<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
			<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
			<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_library ); ?>"><?php esc_html_e( 'Courses', 'upskill-me' ); ?></a>
			<?php if ( $upskill_category && ! is_wp_error( $upskill_term_url ) ) : ?>
				<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
				<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_term_url ); ?>"><?php echo esc_html( $upskill_category->name ); ?></a>
			<?php endif; ?>
			<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
			<span class="font-medium text-[#5d3a8f]" aria-current="page"><?php echo esc_html( get_the_title( $upskill_course ) ); ?></span>
		</nav>

		<div class="flex flex-col items-start gap-3 pt-8 lg:gap-4 lg:pt-12">
			<?php upskill_eyebrow( trim( ( $upskill_category ? $upskill_category->name . ' · ' : '' ) . __( 'On The Job Training', 'upskill-me' ) ), 'monitor', 'self-start' ); ?>
			<h1 id="course-heading" class="heading text-h3 font-medium text-gray-950"><?php echo wp_kses( $upskill_heading ? $upskill_heading : esc_html( get_the_title( $upskill_course ) ), upskill_inline_kses() ); ?></h1>
			<?php if ( $upskill_meta ) : ?>
				<p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-base text-gray-500">
					<?php echo esc_html( implode( ' · ', $upskill_meta ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>
