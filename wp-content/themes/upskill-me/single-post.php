<?php
/**
 * Single blog post.
 *
 * Figma: Blog Detail 49325:16550 (1440) / 49374:105324 (390).
 * Hero (meta, title, excerpt, photo), the article with its "In this article"
 * list and share links, then related posts.
 *
 * @package upskill-me
 */

get_header();

while ( have_posts() ) :
	the_post();

	$display_title = get_field( 'display_title' );
	$contents      = upskill_article_contents( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
	$permalink     = get_permalink();
	$share_title   = rawurlencode( get_the_title() );
	$share_url     = rawurlencode( $permalink );
	?>
	<main id="primary" class="site-main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="bg-gray-25 pt-7 pb-[46px] lg:pt-12 lg:pb-20">
				<div class="container flex flex-col gap-10 lg:flex-row lg:items-center lg:gap-20">
					<div class="flex flex-col lg:min-w-0 lg:flex-1">
						<p class="reveal flex flex-wrap items-center gap-2.5 text-sm text-gray-500">
							<span class="rounded-sm bg-brand-200 px-2.5 py-1 font-medium text-brand-700"><?php echo esc_html( upskill_post_type_label( get_post() ) ); ?></span>
							<span class="size-1 rounded-full bg-[#cfcadb]" aria-hidden="true"></span>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd F Y' ) ); ?></time>
							<span class="size-1 rounded-full bg-[#cfcadb]" aria-hidden="true"></span>
							<span>
								<?php
								/* translators: %d: minutes. */
								printf( esc_html( _n( '%d minute read', '%d minute read', upskill_reading_time( get_post() ), 'upskill-me' ) ), (int) upskill_reading_time( get_post() ) );
								?>
							</span>
						</p>
						<?php upskill_heading( $display_title ? $display_title : esc_html( get_the_title() ), 'h1', 'pt-[18px] text-h3 font-medium text-gray-950 lg:max-w-[600px]' ); ?>
						<?php if ( has_excerpt() ) : ?>
							<p class="reveal pt-4 text-base text-gray-500 lg:max-w-[600px]" style="--i:1"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="reveal overflow-hidden rounded-[20px] lg:min-w-0 lg:flex-1" style="--i:2">
							<?php
							the_post_thumbnail(
								'full',
								array(
									'class'   => 'aspect-square w-full object-cover sm:aspect-[16/10] lg:aspect-auto lg:h-[392px]',
									'loading' => 'eager',
									// 680x392 at desktop: the 3:2 photo draws 680 wide.
									'sizes'   => '(min-width: 1025px) 48vw, 92vw',
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</header>

			<div class="section-y bg-white">
				<div class="container lg:px-[120px]">
					<div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:gap-14">
						<aside class="article-aside rounded-[20px] border border-line bg-white p-4 lg:sticky lg:top-[calc(var(--upskill-header-height)-20px)] lg:w-[236px] lg:flex-none lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0">
							<?php if ( $contents['items'] ) : ?>
								<nav aria-labelledby="article-contents-title">
									<p class="text-sm font-medium tracking-[0.08em] text-[#8b8b97] uppercase lg:text-gray-500" id="article-contents-title"><?php esc_html_e( 'In this article', 'upskill-me' ); ?></p>
									<ol class="flex flex-col gap-1.5 pt-3.5" data-article-contents>
										<?php foreach ( $contents['items'] as $index => $item ) : ?>
											<li>
												<a class="article-contents__link block rounded-[10px] border-l-2 border-transparent px-3 py-2 text-sm font-medium text-gray-950 transition-colors hover:bg-brand-25<?php echo 0 === $index ? ' is-active' : ''; ?>" href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ol>
								</nav>
							<?php endif; ?>
							<ul class="flex gap-2 pt-6" aria-label="<?php esc_attr_e( 'Share this article', 'upskill-me' ); ?>">
								<li>
									<a class="icon-link size-10 border border-line bg-white text-gray-800 hover:border-brand-700 hover:text-brand-700" href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url ); ?>" target="_blank" rel="noopener">
										<span class="screen-reader-text"><?php esc_html_e( 'Share on LinkedIn', 'upskill-me' ); ?></span>
										<?php upskill_icon( 'linkedin', 'size-3.5' ); ?>
									</a>
								</li>
								<li>
									<a class="icon-link size-10 border border-line bg-white text-gray-800 hover:border-brand-700 hover:text-brand-700" href="<?php echo esc_url( 'mailto:?subject=' . $share_title . '&body=' . $share_url ); ?>">
										<span class="screen-reader-text"><?php esc_html_e( 'Share by email', 'upskill-me' ); ?></span>
										<?php upskill_icon( 'mail', 'size-3.5' ); ?>
									</a>
								</li>
								<li>
									<button class="icon-link size-10 border border-line bg-white text-gray-800 hover:border-brand-700 hover:text-brand-700" type="button" data-copy-link="<?php echo esc_url( $permalink ); ?>" data-copied-label="<?php esc_attr_e( 'Link copied', 'upskill-me' ); ?>">
										<span class="screen-reader-text"><?php esc_html_e( 'Copy link', 'upskill-me' ); ?></span>
										<?php upskill_icon( 'link', 'size-3.5' ); ?>
									</button>
								</li>
							</ul>
						</aside>

						<div class="entry-content article-body min-w-0 lg:max-w-[680px] lg:flex-1">
							<?php echo $contents['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?>
						</div>
					</div>
				</div>
			</div>
		</article>

		<?php get_template_part( 'template-parts/related-posts' ); ?>
	</main>
	<?php
endwhile;

get_footer();
