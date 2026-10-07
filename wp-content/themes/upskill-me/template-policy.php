<?php
/**
 * Template Name: Policy
 *
 * Privacy Policy, Terms and Conditions, Returns and Refunds.
 *
 * Figma: Privacy Policy 49325:18785 / 49375:119288, Terms and Conditions
 * 49325:19600 / 49375:120732, Returns & Refunds 49325:19167 / 49375:122135.
 *
 * The page's own editor content (core paragraphs, headings and lists) beside a
 * "Policy navigation" list built from its headings, with the reading position
 * marked as on a blog article. Phones show the first four headings and a
 * "See more" toggle.
 *
 * @package upskill-me
 */

get_header();

// Policies keep their hyphens exactly as written (texturize would turn " - " into an en dash).
remove_filter( 'the_content', 'wptexturize' );

while ( have_posts() ) :
	the_post();

	$upskill_title    = get_field( 'display_title' );
	$upskill_updated  = get_field( 'last_updated' );
	$upskill_contents = upskill_article_contents( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
	$upskill_shown    = 4;
	?>
	<main id="primary" class="site-main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="bg-gray-25 pt-6 pb-6 lg:pt-[76px] lg:pb-20">
				<div class="container flex flex-col gap-6">
					<?php upskill_heading( $upskill_title ? $upskill_title : esc_html( get_the_title() ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
					<p class="reveal text-base text-[#6b6b72] lg:text-gray-500" style="--i:1">
						<?php
						/* translators: %s: date. */
						printf( esc_html__( 'Last updated: %s', 'upskill-me' ), esc_html( $upskill_updated ? $upskill_updated : get_the_modified_date( 'j F Y' ) ) );
						?>
					</p>
				</div>
			</header>

			<div class="bg-white pt-8 pb-14 lg:py-20">
				<div class="container max-lg:px-5 lg:px-[120px]">
					<div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-24">
						<?php if ( $upskill_contents['items'] ) : ?>
							<nav class="policy-nav border-b border-[#efeff2] pb-7 lg:sticky lg:top-[calc(var(--upskill-header-height)-20px)] lg:w-[236px] lg:flex-none lg:border-0 lg:pb-0" aria-labelledby="policy-contents-title">
								<p class="text-xs leading-[1.55] font-semibold tracking-[0.14em] text-[#6b6b72] uppercase lg:text-sm lg:leading-[1.45] lg:font-medium lg:tracking-[0.08em] lg:text-gray-500" id="policy-contents-title"><?php esc_html_e( 'Policy navigation', 'upskill-me' ); ?></p>
								<ol class="policy-nav__list flex flex-col gap-px pt-6 lg:pt-11" id="policy-contents" data-article-contents>
									<?php foreach ( $upskill_contents['items'] as $upskill_index => $upskill_item ) : ?>
										<li<?php echo $upskill_index >= $upskill_shown ? ' class="policy-nav__extra"' : ''; ?>>
											<a class="article-contents__link block border-l-2 border-transparent px-[13px] py-[7px] text-sm text-[#6b6b72] transition-colors hover:bg-brand-25 hover:text-gray-950 lg:rounded-[10px] lg:px-3 lg:py-2 lg:font-medium lg:whitespace-nowrap lg:text-gray-500<?php echo 0 === $upskill_index ? ' is-active' : ''; ?>" href="#<?php echo esc_attr( $upskill_item['id'] ); ?>"><?php echo esc_html( $upskill_item['label'] ); ?></a>
										</li>
									<?php endforeach; ?>
								</ol>
								<?php if ( count( $upskill_contents['items'] ) > $upskill_shown ) : ?>
									<button class="group/link mt-4 inline-flex items-center gap-[7px] text-sm leading-[1.62] font-medium tracking-[-0.007em] text-[#6744a8] lg:hidden" type="button" aria-expanded="false" aria-controls="policy-contents" data-contents-more data-less-label="<?php esc_attr_e( 'See less', 'upskill-me' ); ?>">
										<span data-contents-more-label><?php esc_html_e( 'See more', 'upskill-me' ); ?></span>
										<?php upskill_icon( 'chevron-down', 'size-[11px] transition-transform duration-300 group-aria-expanded/link:rotate-180' ); ?>
									</button>
								<?php endif; ?>
							</nav>
						<?php endif; ?>

						<div class="entry-content article-body policy-body min-w-0 lg:max-w-[680px] lg:flex-1">
							<?php echo $upskill_contents['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?>
						</div>
					</div>
				</div>
			</div>
		</article>
	</main>
	<?php
endwhile;

get_footer();
