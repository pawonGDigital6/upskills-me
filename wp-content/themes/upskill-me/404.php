<?php
/**
 * 404 page.
 *
 * Figma: 404 Error 49325:19580 (1440) / 49375:123322 (390). The illustration,
 * "Page not found", a line of copy and the way back. No call-to-action band
 * above the footer (inc/options.php).
 *
 * @package upskill-me
 */

get_header();

$upskill_courses = get_page_by_path( 'courses' );
?>

	<main id="primary" class="site-main">
		<section class="error-404 not-found bg-gray-25 pt-6 pb-6 lg:pt-[136px] lg:pb-20">
			<div class="container flex flex-col items-start lg:items-center lg:text-center">
				<?php // 769x296 as drawn (356x137 on phones); the theme carries a 2x and a 1x file. ?>
				<img class="h-auto w-full max-w-[769px] self-center" src="<?php echo esc_url( upskill_theme_image( '404-illustration.webp' ) ); ?>" srcset="<?php echo esc_url( upskill_theme_image( '404-illustration-768.webp' ) ); ?> 768w, <?php echo esc_url( upskill_theme_image( '404-illustration.webp' ) ); ?> 1536w" sizes="(min-width: 801px) 769px, calc(100vw - 32px)" width="1536" height="592" alt="" decoding="async" fetchpriority="high">

				<h1 class="heading pt-5 text-h3 font-medium text-gray-900 lg:text-h1 lg:font-semibold"><?php echo wp_kses( __( 'Page <em>not found</em>', 'upskill-me' ), upskill_inline_kses() ); ?></h1>
				<p class="reveal pt-6 text-lg text-gray-500 lg:text-xl lg:font-medium" style="--i:1"><?php esc_html_e( 'Looks like this page took a different learning path. Let’s get you back on track.', 'upskill-me' ); ?></p>

				<div class="reveal flex flex-wrap gap-2 pt-8" style="--i:2">
					<?php
					upskill_button(
						array(
							'url'   => home_url( '/' ),
							'title' => __( 'Go To Homepage', 'upskill-me' ),
						),
						array( 'class' => 'min-h-[50px]' )
					);
					upskill_button(
						array(
							'url'   => $upskill_courses ? get_permalink( $upskill_courses ) : home_url( '/' ),
							'title' => __( 'Browse Courses', 'upskill-me' ),
						),
						array(
							'variant' => 'light',
							'class'   => 'min-h-[50px]',
						)
					);
					?>
				</div>
			</div>
		</section>
	</main>

<?php
get_footer();
