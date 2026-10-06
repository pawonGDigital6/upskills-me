<?php
/**
 * The template for displaying pages.
 *
 * A page composed from the theme's ACF blocks brings its own sections and is
 * rendered edge to edge. Anything else (legal copy typed into the editor) falls
 * through to the default content layout.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package upskill-me
 */

get_header();
?>

	<main id="primary" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();

			if ( upskill_is_block_composed() ) {
				the_content();
			} else {
				get_template_part( 'template-parts/content', 'page' );
			}
		endwhile;
		?>
	</main>

<?php
get_footer();
