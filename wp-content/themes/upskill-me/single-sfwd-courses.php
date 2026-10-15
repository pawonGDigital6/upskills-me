<?php
/**
 * Single course (LearnDash course).
 *
 * The layout is LearnDash's course template, overridden in
 * learndash/ld30/course.php (the product page for visitors, LearnDash's own
 * course page for enrolled learners), rendered through the_content() so
 * LearnDash's access checks stay in charge.
 *
 * @package upskill-me
 */

get_header();
?>

	<main id="primary" class="site-main course-page">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</main>

<?php
get_footer();
