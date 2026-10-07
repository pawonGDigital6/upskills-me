<?php
/**
 * "Keep exploring" — related posts under a blog article.
 *
 * Figma: Blog Detail 49325:16710. Posts sharing the article's category come
 * first, topped up with the latest posts.
 *
 * @package upskill-me
 */

$current  = get_the_ID();
$category = upskill_primary_category( $current );
$related  = array();

if ( $category ) {
	$related = get_posts(
		array(
			'numberposts' => 3,
			'category'    => $category->term_id,
			'exclude'     => array( $current ),
		)
	);
}

if ( count( $related ) < 3 ) {
	$related = array_merge(
		$related,
		get_posts(
			array(
				'numberposts' => 3 - count( $related ),
				'exclude'     => array_merge( array( $current ), wp_list_pluck( $related, 'ID' ) ),
			)
		)
	);
}

if ( ! $related ) {
	return;
}
?>
<section class="section-y bg-white" aria-labelledby="related-posts-title">
	<div class="container">
		<header class="section-header section-header--split">
			<div class="section-header__title">
				<?php upskill_eyebrow( __( 'Keep exploring', 'upskill-me' ), 'resources', 'reveal' ); ?>
				<h2 class="heading section-header__heading text-h3" id="related-posts-title"><?php echo wp_kses( __( 'Keep building <em>your knowledge.</em>', 'upskill-me' ), upskill_inline_kses() ); ?></h2>
			</div>
			<div class="section-header__aside reveal" style="--i:1">
				<p class="section-header__intro"><?php esc_html_e( 'Related industries, categories and recommended training to explore next.', 'upskill-me' ); ?></p>
				<?php
				upskill_button(
					array(
						'url'   => upskill_listing_page_url( 'post' ),
						'title' => __( 'See All Blogs', 'upskill-me' ),
					)
				);
				?>
			</div>
		</header>

		<div class="mt-stack grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			foreach ( $related as $index => $item ) {
				get_template_part(
					'template-parts/cards/resource',
					null,
					array(
						'post'    => $item,
						'variant' => 'card',
						'index'   => $index,
					)
				);
			}
			?>
		</div>
	</div>
</section>
