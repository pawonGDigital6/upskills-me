<?php
/**
 * An industry, category or level page. Figma: Manufacturing 49325:14836 (1440)
 * / 49347:88536 (390), "Admin" category 49325:15080 / 49354:97475.
 *
 * 1. Photo hero: the term, heading, intro, Find Training Packages and Watch
 *    Free Training (term fields "Training Term: Page").
 * 2. "Explore by category" / "Find your training": the training library with
 *    all four filter lists, the page's own term ticked by default
 *    (template-parts/library/browser.php, AJAX in js/components/library.js).
 * 3. The page sections under it: a synced pattern of the existing blocks
 *    (card grid, free sessions, industry cards, category list, Why Upskillme,
 *    testimonials, FAQs), "Industry page sections" / "Category page sections" /
 *    "Level page sections" or the term's own choice. {term} and {term_lower}
 *    in the pattern become the term's name. The blocks themselves are unchanged.
 *
 * Used by taxonomy-industry.php, taxonomy-training_category.php and
 * taxonomy-cohort.php for terms without a designed landing page.
 *
 * @package upskill-me
 */

$upskill_term  = get_queried_object();
$upskill_facet = array_search( $upskill_term->taxonomy, wp_list_pluck( upskill_library_facets(), 0 ), true );
$upskill_base  = get_term_link( $upskill_term );
$upskill_name  = $upskill_term->name;
$upskill_lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $upskill_name ) : strtolower( $upskill_name );

// The page's term is ticked unless the visitor unticked it (all=1) or chose other values in its list.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public, read-only filters.
$upskill_source = $_GET;
if ( empty( $upskill_source['all'] ) && empty( $upskill_source[ $upskill_facet ] ) ) {
	$upskill_source[ $upskill_facet ] = array( $upskill_term->slug );
}
// phpcs:enable
$upskill_request = upskill_library_request( $upskill_source );

$upskill_photo = (int) get_field( 'hero_image', $upskill_term );
$upskill_photo = $upskill_photo ? $upskill_photo : (int) get_field( 'card_image', $upskill_term );
$upskill_title = (string) get_field( 'hero_heading', $upskill_term );
/* translators: %s: industry, category or level name. */
$upskill_title = '' !== $upskill_title ? $upskill_title : sprintf( __( '%s <em>training.</em>', 'upskill-me' ), esc_html( $upskill_name ) );
$upskill_intro = (string) get_field( 'hero_intro', $upskill_term );
$upskill_intro = '' !== $upskill_intro ? $upskill_intro : $upskill_term->description;
$upskill_free  = function_exists( 'upskill_library_free_course_ids' ) ? array_intersect( upskill_library_free_course_ids(), array_map( 'intval', (array) get_objects_in_term( $upskill_term->term_id, $upskill_term->taxonomy ) ) ) : array();
$upskill_any   = function_exists( 'upskill_library_free_course_ids' ) ? count( upskill_library_free_course_ids() ) : 0;

// Section header above the library, as drawn per page type.
$upskill_header = 'industry' === $upskill_facet
	? array(
		'eyebrow' => __( 'Explore by category', 'upskill-me' ),
		'icon'    => 'category',
		'heading' => __( 'Find the training <em>you need next.</em>', 'upskill-me' ),
		/* translators: %s: industry name in lower case. */
		'intro'   => sprintf( __( 'Search or filter to find the right session for %s, then keep exploring related training.', 'upskill-me' ), $upskill_lower ),
	)
	: array(
		'eyebrow' => __( 'Find your training', 'upskill-me' ),
		'icon'    => 'search',
		/* translators: %s: category or level name in lower case. */
		'heading' => sprintf( __( 'Find the %s training <em>you need.</em>', 'upskill-me' ), esc_html( $upskill_lower ) ),
		'intro'   => __( 'Search the training library or explore sessions designed around the knowledge and responsibilities that matter in your role.', 'upskill-me' ),
	);

// The page sections: the term's own synced pattern, or the one for its kind of page.
$upskill_pattern = (int) get_field( 'page_sections', $upskill_term );
if ( ! $upskill_pattern ) {
	$upskill_found   = get_posts(
		array(
			'post_type'   => 'wp_block',
			'post_status' => 'publish',
			'name'        => array( 'industry' => 'industry-page-sections', 'category' => 'category-page-sections', 'level' => 'level-page-sections' )[ $upskill_facet ],
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	$upskill_pattern = $upskill_found ? (int) $upskill_found[0] : 0;
}
?>
<main id="primary" class="site-main bg-gray-25">
	<?php // Figma 49325:14838 / 49354:95610: 30px over and 46px under the photo on phones; on desktop the library follows straight on. ?>
	<section class="term-page-hero container pt-[30px] pb-[46px] lg:pt-16 lg:pb-0" aria-labelledby="term-heading">
		<?php // Photo and copy share one grid cell; Figma's black 0–80% top-to-bottom gradient keeps the white text readable. ?>
		<div class="grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[24px] bg-gray-950 *:col-start-1 *:row-start-1">
			<?php if ( $upskill_photo ) : ?>
				<?php // Phones crop the photo as Figma does, from 78% across. Drawn by height there (730 × the photo's ratio). ?>
				<?php upskill_image( $upskill_photo, 'full', array( 'class' => 'size-full object-cover max-lg:object-[78%_50%]', 'alt' => '', 'loading' => 'eager', 'sizes' => '(min-width: 1440px) 1360px, (min-width: 1025px) calc(100vw - 80px), 1360px' ) ); ?>
			<?php endif; ?>
			<div class="bg-[linear-gradient(180deg,rgb(0_0_0/0%)_-15%,rgb(0_0_0/80%)_100%)]" aria-hidden="true"></div>
			<div class="relative flex min-h-[730px] flex-col justify-center gap-6 px-4 py-17.5 text-white sm:px-8 sm:py-8 lg:px-16 lg:py-13 lg:max-w-[784px]">
				<div class="flex flex-col items-start gap-3">
					<p class="eyebrow"><?php upskill_icon( 'free-video', 'eyebrow__icon' ); ?><span><?php echo esc_html( $upskill_name ); ?></span></p>
					<h1 id="term-heading" class="heading text-h1 font-medium text-white max-xs:text-[2.25rem] lg:font-semibold lg:tracking-[-0.01em] [&_em]:text-white">
						<?php echo wp_kses( $upskill_title, upskill_inline_kses() ); ?>
					</h1>
				</div>
				<?php if ( $upskill_intro ) : ?>
					<p class="text-xl text-gray-100 pt-[6px]"><?php echo esc_html( $upskill_intro ); ?></p>
				<?php endif; ?>
				<?php // Figma: the buttons sit 32 (phones, stacked 16 apart) / 56 (desktop) under the intro. ?>
				<div class="mt-2 flex flex-col items-start gap-4 sm:flex-row sm:flex-wrap sm:gap-2 lg:mt-8">
					<?php upskill_button( array( 'url' => $upskill_base . '#results', 'title' => __( 'Find Training Packages', 'upskill-me' ) ), array( 'class' => 'min-h-[50px]' ) ); ?>
					<?php if ( $upskill_any ) : ?>
						<?php upskill_button( array( 'url' => add_query_arg( 'free', 1, $upskill_free ? $upskill_base : upskill_library_page_url() ) . '#results', 'title' => __( 'Watch Free Training', 'upskill-me' ) ), array( 'variant' => 'light', 'icon' => 'play', 'class' => 'min-h-[50px]' ) ); ?>
					<?php endif; ?>
				</div>
				<?php if ( $upskill_any ) : ?>
					<?php // As drawn: the free sessions on offer across Upskillme (Watch Free Training opens this page's own when it has some). ?>
					<p class="-mt-2 flex items-center gap-2 font-secondary text-[13px] leading-[1.62] tracking-[-0.007em] text-gray-100">
						<?php upskill_icon( 'check', 'size-3.5 flex-none text-[#6744a8]' ); ?>
						<?php
						/* translators: %d: number of free sessions. */
						echo esc_html( sprintf( _n( '%d complete session, free to watch', '%d complete sessions, free to watch', $upskill_any, 'upskill-me' ), $upskill_any ) );
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="bg-brand-50 pt-12 lg:pt-20 course-filter-module" aria-labelledby="term-library-heading">
		<div class="container">
			<div class="section-header section-header--split">
				<div class="section-header__title">
					<?php upskill_eyebrow( $upskill_header['eyebrow'], $upskill_header['icon'] ); ?>
					<h2 id="term-library-heading" class="section-header__heading heading text-h2 font-medium"><?php echo wp_kses( $upskill_header['heading'], upskill_inline_kses() ); ?></h2>
				</div>
				<div class="section-header__aside">
					<p class="section-header__intro"><?php echo esc_html( $upskill_header['intro'] ); ?></p>
				</div>
			</div>
		</div>
		<?php get_template_part( 'template-parts/library/browser', null, array( 'request' => $upskill_request, 'base' => $upskill_base, 'defaults' => array( $upskill_facet => $upskill_term->slug ) ) ); ?>
	</section>

	<?php
	if ( $upskill_pattern && 'publish' === get_post_status( $upskill_pattern ) ) {
		$upskill_sections = do_blocks( (string) get_post_field( 'post_content', $upskill_pattern ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered blocks; the term name is escaped.
		echo str_replace( array( '{term}', '{term_lower}' ), array( esc_html( $upskill_name ), esc_html( $upskill_lower ) ), $upskill_sections );
	}
	?>
</main>
