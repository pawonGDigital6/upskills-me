<?php
/**
 * Single case study.
 *
 * Figma: Project Case Study Detail 49325:16718 (1440) / 49374:107273 (390).
 * The hero (type chip, title, summary, photo), then the case study's own
 * blocks (Statement for the overview and challenge, Card Grid rows for the
 * solution and outcome columns for the impact, Testimonials), then related
 * case studies.
 *
 * Held for LearnDash: "Industry Training Resources" (49325:16856), the
 * sessions for the case study's industry.
 *
 * @package upskill-me
 */

get_header();

while ( have_posts() ) :
	the_post();

	$upskill_title = get_field( 'display_title' );
	$upskill_photo = get_field( 'hero_image' ) ? (int) get_field( 'hero_image' ) : (int) get_post_thumbnail_id();
	?>
	<main id="primary" class="site-main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="bg-gray-25 pt-6 pb-6 lg:pt-[104px] lg:pb-20">
				<div class="container flex flex-col gap-6 lg:flex-row lg:items-center lg:gap-20">
					<div class="flex flex-col lg:min-w-0 lg:flex-1">
						<p class="reveal flex">
							<span class="rounded-sm bg-brand-200 px-2.5 py-1 text-sm font-medium text-brand-700"><?php echo esc_html( upskill_post_type_label( get_post() ) ); ?></span>
						</p>
						<?php upskill_heading( $upskill_title ? $upskill_title : esc_html( get_the_title() ), 'h1', 'pt-3 text-h3 font-medium text-gray-900 lg:pt-[18px] lg:text-gray-950 lg:max-w-[600px]' ); ?>
						<?php if ( has_excerpt() ) : ?>
							<p class="reveal pt-6 text-base text-[#6b6b72] lg:pt-4 lg:text-gray-500 lg:max-w-[600px]" style="--i:1"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $upskill_photo ) : ?>
						<div class="reveal overflow-hidden rounded-[24px] lg:min-w-0 lg:flex-1 lg:rounded-[20px]" style="--i:2">
							<?php
							upskill_image(
								$upskill_photo,
								'full',
								array(
									'class'   => 'aspect-square w-full object-cover sm:aspect-[16/10] lg:aspect-auto lg:h-[392px]',
									'loading' => 'eager',
									// 640x392 at desktop: the 3:2 photo draws 640 wide; square on phones draws it 1.5x the column.
									'sizes'   => '(min-width: 1025px) 46vw, (min-width: 640px) 92vw, 138vw',
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</header>

			<?php // The blocks are full-width sections of their own. ?>
			<?php the_content(); ?>
		</article>

		<?php get_template_part( 'template-parts/related-posts' ); ?>
	</main>
	<?php
endwhile;

get_footer();
