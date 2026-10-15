<?php
/**
 * An industry, category or level page: the term's hero, then the training
 * library fixed to that term. Figma: Manufacturing 49325:14836 (1440) /
 * 49347:88536 (390), "Admin" category 49325:15080 / 49354:97475.
 *
 * Used by taxonomy-industry.php, taxonomy-training_category.php and
 * taxonomy-cohort.php for terms without a designed landing page (a term with
 * one links there instead, inc/taxonomies.php). The marketing sections the
 * designs add below (categories, cohorts, FAQs) are Home modules and can be
 * placed on a landing page.
 *
 * @package upskill-me
 */

$upskill_term  = get_queried_object();
$upskill_facet = array_search( $upskill_term->taxonomy, wp_list_pluck( upskill_library_facets(), 0 ), true );
$upskill_label = upskill_library_facets()[ $upskill_facet ][1];
$upskill_base  = get_term_link( $upskill_term );

// The term is always part of the request; every other filter comes from the URL.
$upskill_source                   = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public, read-only filters.
$upskill_source[ $upskill_facet ] = array( $upskill_term->slug );
$upskill_request                  = upskill_library_request( $upskill_source );

$upskill_free = function_exists( 'upskill_library_free_course_ids' ) ? array_intersect( upskill_library_free_course_ids(), array_map( 'intval', (array) get_objects_in_term( $upskill_term->term_id, $upskill_term->taxonomy ) ) ) : array();
?>
<main id="primary" class="site-main bg-gray-25">
	<section class="container pt-6 pb-10 lg:pt-12 lg:pb-16" aria-labelledby="term-heading">
		<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
			<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
			<span aria-hidden="true">/</span>
			<a class="hover:text-brand-700" href="<?php echo esc_url( upskill_library_page_url() ); ?>"><?php esc_html_e( 'Training', 'upskill-me' ); ?></a>
			<span aria-hidden="true">/</span>
			<span class="text-gray-950" aria-current="page"><?php echo esc_html( $upskill_term->name ); ?></span>
		</nav>
		<div class="mt-8 flex max-w-[860px] flex-col items-start gap-5 lg:mt-12">
			<?php upskill_eyebrow( $upskill_label, 'industry' === $upskill_facet ? 'industry' : ( 'category' === $upskill_facet ? 'category' : 'cohorts' ) ); ?>
			<h1 id="term-heading" class="heading text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]">
				<?php
				/* translators: %s: industry, category or level name. */
				echo wp_kses( sprintf( __( '%s <em>training.</em>', 'upskill-me' ), esc_html( $upskill_term->name ) ), upskill_inline_kses() );
				?>
			</h1>
			<?php if ( $upskill_term->description ) : ?>
				<p class="text-lead text-gray-500"><?php echo esc_html( $upskill_term->description ); ?></p>
			<?php endif; ?>
			<?php if ( $upskill_free ) : ?>
				<a class="btn btn--light btn--md btn--play mt-2" href="<?php echo esc_url( add_query_arg( 'free', 1, $upskill_base ) . '#results' ); ?>">
					<span class="btn__label"><?php esc_html_e( 'Watch Free Training', 'upskill-me' ); ?></span>
					<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'play', 'btn__icon' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/library/browser', null, array( 'request' => $upskill_request, 'base' => $upskill_base, 'fixed' => array( $upskill_facet => $upskill_term->slug ) ) ); ?>
</main>
