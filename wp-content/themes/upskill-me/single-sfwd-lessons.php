<?php
/**
 * Single session (LearnDash lesson).
 *
 * Figma: User Course Flow 49325:18592 (1440) / 49374:113832 (390). The layout
 * itself is LearnDash's lesson template, overridden in learndash/ld30/lesson.php,
 * which LearnDash renders through the_content(). That keeps LearnDash's access
 * checks, video progression and completion logic in charge.
 *
 * @package upskill-me
 */

get_header();
?>

	<main id="primary" class="site-main session-page">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</main>

<?php
get_footer();
