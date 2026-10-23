<?php
/**
 * Single guide.
 *
 * Figma has no guide detail, so this is the default reading layout taken from
 * Blog Detail (49325:16550 / 49374:105324): the hero (type chip, title,
 * excerpt, featured image) and the editor content in the article body, with
 * "In this article" beside it once the guide has headings. The theme's ACF
 * blocks run full width between runs of text (upskill_contain_editor_text()).
 *
 * @package upskill-me
 */

get_header();

while ( have_posts() ) :
	the_post();

	// A guide laid out with full-width blocks has no single text column to list beside.
	$upskill_composed = upskill_is_block_composed();
	$upskill_contents = $upskill_composed ? array( 'items' => array() ) : upskill_article_contents( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
	?>
	<main id="primary" class="site-main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="bg-gray-25 pt-7 pb-[46px] lg:pt-[104px] lg:pb-20">
				<div class="container flex flex-col gap-6 sm:gap-10 lg:flex-row lg:items-center lg:gap-20">
					<div class="flex flex-col lg:min-w-0 lg:flex-1">
						<p class="reveal flex">
							<span class="rounded-sm bg-brand-200 px-2.5 py-1 text-sm font-medium text-brand-700"><?php echo esc_html( upskill_post_type_label( get_post() ) ); ?></span>
						</p>
						<?php upskill_heading( esc_html( get_the_title() ), 'h1', 'pt-[11px] text-h3 font-medium text-gray-950 sm:pt-[18px] lg:max-w-[600px]' ); ?>
						<?php if ( has_excerpt() ) : ?>
							<p class="reveal pt-7 text-base text-gray-500 sm:pt-4 lg:max-w-[600px]" style="--i:1"><?php echo esc_html( get_the_excerpt() ); ?></p>
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
									// As the blog hero: the 3:2 photo draws 680 wide at desktop.
									'sizes'   => '(min-width: 1025px) 48vw, 92vw',
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( $upskill_contents['items'] ) : ?>
				<div class="section-y bg-white">
					<div class="container wd:px-[120px]">
						<div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:gap-14">
							<aside class="article-aside rounded-[20px] border border-line bg-white p-4 lg:sticky lg:top-[calc(var(--upskill-header-height)-20px)] lg:w-[236px] lg:flex-none lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0">
								<nav aria-labelledby="article-contents-title">
									<p class="text-sm font-medium tracking-[0.08em] text-[#8b8b97] uppercase lg:text-gray-500" id="article-contents-title"><?php esc_html_e( 'In this article', 'upskill-me' ); ?></p>
									<ol class="flex flex-col gap-1.5 pt-3.5" data-article-contents>
										<?php foreach ( $upskill_contents['items'] as $upskill_index => $upskill_item ) : ?>
											<li>
												<a class="article-contents__link block border-l-2 border-transparent px-3 py-2 text-sm font-medium text-gray-950 transition-colors hover:bg-brand-25<?php echo 0 === $upskill_index ? ' is-active' : ''; ?>" href="#<?php echo esc_attr( $upskill_item['id'] ); ?>"><?php echo esc_html( $upskill_item['label'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ol>
								</nav>
							</aside>

							<div class="entry-content article-body min-w-0 lg:max-w-[680px] lg:flex-1">
								<?php echo $upskill_contents['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?>
							</div>
						</div>
					</div>
				</div>
			<?php elseif ( ! $upskill_composed ) : ?>
				<?php // No headings to list: the text sits centred at the article width. ?>
				<div class="section-y bg-white">
					<div class="container">
						<div class="entry-content article-body mx-auto lg:max-w-[680px]">
							<?php echo $upskill_contents['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?>
						</div>
					</div>
				</div>
			<?php else : ?>
				<?php
				echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output.
					'the_content', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
					upskill_contain_editor_text(
						get_the_content(),
						'<div class="section-y bg-white"><div class="container"><div class="entry-content article-body mx-auto lg:max-w-[680px]">',
						'</div></div></div>'
					)
				);
				?>
			<?php endif; ?>
		</article>
	</main>
	<?php
endwhile;

get_footer();
