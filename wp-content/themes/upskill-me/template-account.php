<?php
/**
 * Template Name: Account screen
 *
 * LearnDash's account pages (Registration, Registration Success, Reset
 * Password) in the "Log in" page's frame: logo, the page title, then the page
 * content, i.e. LearnDash's form, restyled by
 * src/global/scss/plugins/learndash/_account.scss. Figma draws only "Log in"
 * (49325:21367 / 49375:115106); these follow it.
 *
 * @package upskill-me
 */

get_template_part( 'template-parts/account/document-start' );

while ( have_posts() ) :
	the_post();
	?>
	<h1 class="text-center text-[2.25rem] leading-[1.1] font-medium tracking-[-0.01em] text-gray-900"><?php the_title(); ?></h1>
	<div class="account-content">
		<?php the_content(); ?>
	</div>
	<p class="text-center text-base text-gray-500">
		<a class="font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Back to sign in', 'upskill-me' ); ?></a>
	</p>
	<?php
endwhile;

get_template_part( 'template-parts/account/document-end' );
