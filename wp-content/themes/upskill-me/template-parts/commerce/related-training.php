<?php
/**
 * Cart: "You might also need these." (Figma Cart 49325:15876).
 *
 * Three courses from the same training categories or industries as the
 * cart, not already in it, topped up with the newest courses that can be
 * bought; shown as the training library's cards (template-parts/cards/training.php).
 *
 * @package upskill-me
 */

if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() || ! function_exists( 'upskill_library_card' ) ) {
	return;
}

$upskill_in_cart = array();
$upskill_terms   = array(
	'training_category' => array(),
	'industry'          => array(),
);

foreach ( WC()->cart->get_cart() as $upskill_line ) {
	$upskill_course = function_exists( 'upskill_product_course' ) ? upskill_product_course( $upskill_line['data'] ) : 0;

	if ( ! $upskill_course ) {
		continue;
	}

	$upskill_in_cart[] = $upskill_course;

	foreach ( array_keys( $upskill_terms ) as $upskill_taxonomy ) {
		$upskill_terms[ $upskill_taxonomy ] = array_merge( $upskill_terms[ $upskill_taxonomy ], wp_get_post_terms( $upskill_course, $upskill_taxonomy, array( 'fields' => 'ids' ) ) );
	}
}

$upskill_tax_query = array( 'relation' => 'OR' );

foreach ( $upskill_terms as $upskill_taxonomy => $upskill_ids ) {
	if ( $upskill_ids ) {
		$upskill_tax_query[] = array(
			'taxonomy' => $upskill_taxonomy,
			'terms'    => array_unique( $upskill_ids ),
		);
	}
}

$upskill_query = array(
	'post_type'      => 'sfwd-courses',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'fields'         => 'ids',
	'post__not_in'   => $upskill_in_cart, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- the few courses in the cart.
	'no_found_rows'  => true,
);

$upskill_related = count( $upskill_tax_query ) > 1 ? get_posts( $upskill_query + array( 'tax_query' => $upskill_tax_query ) ) : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
$upskill_related = array_merge( $upskill_related, get_posts( $upskill_query ) );

// Only courses that can be bought, three of them.
$upskill_related = array_slice(
	array_values(
		array_filter(
			array_unique( $upskill_related ),
			static fn( $id ) => function_exists( 'upskill_course_product' ) && upskill_course_product( $id )
		)
	),
	0,
	3
);

if ( ! $upskill_related ) {
	return;
}
?>
<section class="store-related section-y bg-white" aria-labelledby="store-related-heading">
	<div class="container">
		<header class="section-header section-header--split">
			<div class="section-header__title">
				<?php upskill_eyebrow( __( 'Keep building your knowledge', 'upskill-me' ), 'search-list' ); ?>
				<h2 class="heading section-header__heading text-h3" id="store-related-heading"><?php echo wp_kses( __( 'You might also <em>need these.</em>', 'upskill-me' ), upskill_inline_kses() ); ?></h2>
			</div>
			<div class="section-header__aside">
				<p class="section-header__intro"><?php esc_html_e( 'Closely related sessions from the same subject areas already in your cart.', 'upskill-me' ); ?></p>
			</div>
		</header>
		<div class="mt-10 grid gap-4 sm:grid-cols-2 lg:mt-14 lg:grid-cols-3">
			<?php foreach ( $upskill_related as $upskill_id ) : ?>
				<?php get_template_part( 'template-parts/cards/training', null, array( 'card' => upskill_library_card( $upskill_id ) ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
