<?php
/**
 * Template Name: Training library
 *
 * Search and browse the training catalogue. Figma: "Search" 49325:15693
 * (1440) / 49374:48811 (390); mobile filter sheet "Filters" 49355:39467.
 *
 * Search hero with suggestions, then "Refine your results" (Industry,
 * Category, Level, Free training) beside the result cards, a sort menu and
 * "Load more training". Every filter is a GET parameter (inc/lms-library.php),
 * so results work without JavaScript; js/components/library.js submits on
 * change and opens the filter sheet on phones. The page's footer CTA band
 * follows as on every marketing page.
 *
 * @package upskill-me
 */

get_header();

$upskill_request = upskill_library_request();
$upskill_results = upskill_library_results( $upskill_request );
$upskill_counts  = upskill_library_counts( $upskill_request );
$upskill_total   = (int) $upskill_results->found_posts;
$upskill_base    = get_permalink();
$upskill_active  = array_filter( array( $upskill_request['industry'], $upskill_request['category'], $upskill_request['level'], $upskill_request['free'] ) );
$upskill_suggest = (array) apply_filters( 'upskill_library_suggestions', array( __( 'Emergency training', 'upskill-me' ), __( 'Training for managers', 'upskill-me' ), __( 'Workplace safety', 'upskill-me' ), __( 'Leadership responsibilities', 'upskill-me' ) ) );

// The current filters as a query string (for links that change one thing).
$upskill_query = static function ( $changes = array() ) use ( $upskill_request, $upskill_base ) {
	$args = array_merge(
		array(
			'q'        => $upskill_request['q'],
			'industry' => $upskill_request['industry'],
			'category' => $upskill_request['category'],
			'level'    => $upskill_request['level'],
			'free'     => $upskill_request['free'] ? 1 : '',
			'sort'     => 'newest' === $upskill_request['sort'] ? '' : $upskill_request['sort'],
		),
		$changes
	);

	return add_query_arg( array_filter( $args ), $upskill_base );
};

$upskill_checkbox = 'peer size-[19px] flex-none cursor-pointer appearance-none rounded-sm border border-[#d8d2ea] bg-white transition-colors checked:border-brand-700 checked:bg-brand-700 disabled:cursor-default';
?>
<main id="primary" class="site-main bg-gray-25">
	<section class="container pt-6 pb-10 lg:pt-14 lg:pb-20" aria-labelledby="library-heading">
		<div class="mx-auto flex max-w-[792px] flex-col items-start gap-6 lg:items-center lg:text-center">
			<div class="flex flex-col items-start gap-5 lg:items-center">
				<?php upskill_eyebrow( __( 'Search training', 'upskill-me' ), 'search' ); ?>
				<h1 id="library-heading" class="heading text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]"><?php echo wp_kses( __( 'Find the training <br><em>you need.</em>', 'upskill-me' ), upskill_inline_kses() ); ?></h1>
			</div>
			<div class="flex w-full max-w-[666px] flex-col items-start gap-6 lg:items-center">
				<p class="text-base text-gray-500"><?php esc_html_e( 'Search across the Upskillme training library by topic, responsibility, skill or area of work.', 'upskill-me' ); ?></p>

				<form class="w-full max-w-[480px]" role="search" action="<?php echo esc_url( $upskill_base ); ?>#results" method="get">
					<label class="screen-reader-text" for="library-search"><?php esc_html_e( 'Search training', 'upskill-me' ); ?></label>
					<span class="grid grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1">
						<input class="h-12 w-full rounded-full border border-gray-900/20 bg-white py-3 pr-14 pl-11 font-secondary text-base text-gray-950 transition-[border-color,box-shadow] duration-200 placeholder:text-[#6b6b72] hover:border-gray-900/30 focus:border-brand-700 focus:shadow-[0_0_0_3px_rgb(109_66_175/14%)] focus:outline-none" type="search" name="q" id="library-search" value="<?php echo esc_attr( $upskill_request['q'] ); ?>" placeholder="<?php esc_attr_e( 'Search training, topics or skills', 'upskill-me' ); ?>">
						<?php upskill_icon( 'search', 'pointer-events-none ml-4 size-[18px] self-center justify-self-start text-gray-500' ); ?>
						<button class="btn btn--primary mr-[3px] size-[42px] justify-center self-center justify-self-end p-0" type="submit">
							<span class="screen-reader-text"><?php esc_html_e( 'Search', 'upskill-me' ); ?></span>
							<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
						</button>
					</span>
				</form>

				<?php if ( $upskill_suggest ) : ?>
					<div class="flex flex-col items-start gap-3 lg:w-max lg:flex-row lg:items-center">
						<span class="text-sm text-[#8b8b97]"><?php esc_html_e( 'Try:', 'upskill-me' ); ?></span>
						<ul class="flex flex-wrap gap-2 lg:flex-nowrap lg:gap-[9px]" role="list">
							<?php foreach ( $upskill_suggest as $upskill_term ) : ?>
								<li><a class="block rounded-full border border-line bg-white px-3.5 py-2 text-sm text-gray-500 transition-colors hover:border-brand-300 hover:text-brand-700" href="<?php echo esc_url( add_query_arg( 'q', rawurlencode( $upskill_term ), $upskill_base ) . '#results' ); ?>"><?php echo esc_html( $upskill_term ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<form id="results" class="container scroll-mt-28 pb-12 lg:grid lg:pt-20 lg:grid-cols-[278px_minmax(0,1fr)] lg:items-start lg:gap-10 lg:pb-20" action="<?php echo esc_url( $upskill_base ); ?>#results" method="get" data-library>
		<?php if ( '' !== $upskill_request['q'] ) : ?>
			<input type="hidden" name="q" value="<?php echo esc_attr( $upskill_request['q'] ); ?>">
		<?php endif; ?>

		<dialog id="library-filters" class="m-0 mt-auto max-h-[92svh] w-full max-w-none overflow-y-auto rounded-t-[20px] border-0 bg-white p-0 text-gray-950 backdrop:bg-gray-950/50 lg:static lg:mt-0 lg:block lg:max-h-none lg:overflow-visible lg:rounded-[22px] lg:border lg:border-line" aria-labelledby="library-filters-heading" data-library-filters>
			<div class="flex flex-col p-4 lg:p-4">
				<div class="flex items-center justify-between gap-3">
					<h2 id="library-filters-heading" class="text-lg font-medium text-[#0e0e14]"><?php esc_html_e( 'Refine your results', 'upskill-me' ); ?></h2>
					<div class="flex items-center gap-1">
						<a class="rounded-lg px-2 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50 max-lg:hidden" data-library-clear href="<?php echo esc_url( add_query_arg( array_filter( array( 'q' => $upskill_request['q'] ) ), $upskill_base ) . '#results' ); ?>"><?php esc_html_e( 'Clear all', 'upskill-me' ); ?></a>
						<button class="grid size-10 place-items-center rounded-full text-gray-900 hover:bg-gray-50 lg:hidden" type="button" data-library-close>
							<span class="screen-reader-text"><?php esc_html_e( 'Close filters', 'upskill-me' ); ?></span>
							<?php upskill_icon( 'close', 'size-5' ); ?>
						</button>
					</div>
				</div>
				<a class="self-start pt-2 text-base font-medium text-brand-700 lg:hidden" href="<?php echo esc_url( add_query_arg( array_filter( array( 'q' => $upskill_request['q'] ) ), $upskill_base ) . '#results' ); ?>"><?php esc_html_e( 'Clear all', 'upskill-me' ); ?></a>

				<?php
				$upskill_groups = array();
				foreach ( upskill_library_facets() as $upskill_key => $upskill_facet ) {
					$upskill_terms = get_terms( array( 'taxonomy' => $upskill_facet[0], 'hide_empty' => false ) );
					if ( $upskill_terms && ! is_wp_error( $upskill_terms ) ) {
						$upskill_groups[ $upskill_key ] = array( $upskill_facet[1], $upskill_terms );
					}
				}
				$upskill_groups['free'] = array( __( 'Availability', 'upskill-me' ), array() );
				?>
				<?php foreach ( $upskill_groups as $upskill_key => $upskill_group ) : ?>
					<details class="group/facet mt-4 border-line pt-4 not-first-of-type:border-t lg:mt-4" open>
						<summary class="flex cursor-pointer list-none items-center justify-between gap-3 pb-3 text-sm font-medium text-gray-500 uppercase [&::-webkit-details-marker]:hidden">
							<?php echo esc_html( $upskill_group[0] ); ?>
							<?php upskill_icon( 'chevron-down', 'size-3 transition-transform group-open/facet:rotate-180' ); ?>
						</summary>
						<ul class="flex flex-col gap-[9px] pb-1.5 lg:px-[13px]" role="list">
							<?php if ( 'free' === $upskill_key ) : ?>
								<?php $upskill_n = $upskill_counts['free']; ?>
								<li>
									<label class="flex cursor-pointer items-center gap-2.5 py-[3px] <?php echo ! $upskill_n && ! $upskill_request['free'] ? 'opacity-45' : ''; ?>">
										<span class="grid grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1">
											<input class="<?php echo esc_attr( $upskill_checkbox ); ?>" type="checkbox" name="free" value="1"<?php checked( $upskill_request['free'] ); ?><?php disabled( ! $upskill_n && ! $upskill_request['free'] ); ?>>
											<?php upskill_icon( 'check', 'pointer-events-none hidden size-3 place-self-center text-white peer-checked:block' ); ?>
										</span>
										<span class="flex-1 text-base text-gray-950"><?php esc_html_e( 'Free training', 'upskill-me' ); ?></span>
										<span class="font-secondary text-xs text-[#8b8b97]"><?php echo esc_html( number_format_i18n( $upskill_n ) ); ?></span>
									</label>
								</li>
							<?php else : ?>
								<?php foreach ( $upskill_group[1] as $upskill_term ) : ?>
									<?php
									$upskill_on = in_array( $upskill_term->slug, $upskill_request[ $upskill_key ], true );
									$upskill_n  = $upskill_counts[ $upskill_key ][ $upskill_term->slug ] ?? 0;
									?>
									<li>
										<label class="flex cursor-pointer items-center gap-2.5 py-[3px] <?php echo ! $upskill_n && ! $upskill_on ? 'opacity-45' : ''; ?>">
											<span class="grid grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1">
												<input class="<?php echo esc_attr( $upskill_checkbox ); ?>" type="checkbox" name="<?php echo esc_attr( $upskill_key ); ?>[]" value="<?php echo esc_attr( $upskill_term->slug ); ?>"<?php checked( $upskill_on ); ?><?php disabled( ! $upskill_n && ! $upskill_on ); ?>>
												<?php upskill_icon( 'check', 'pointer-events-none hidden size-3 place-self-center text-white peer-checked:block' ); ?>
											</span>
											<span class="flex-1 text-base text-gray-950"><?php echo esc_html( $upskill_term->name ); ?></span>
											<span class="font-secondary text-xs text-[#8b8b97]"><?php echo esc_html( number_format_i18n( $upskill_n ) ); ?></span>
										</label>
									</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</details>
				<?php endforeach; ?>

				<div class="sticky bottom-0 -mx-4 mt-4 border-t border-line bg-white p-4 lg:static lg:mx-0 lg:border-0 lg:p-0 [.js_&]:lg:hidden">
					<button class="btn btn--primary btn--md min-h-[50px] w-full justify-center" type="submit">
						<span class="btn__label">
							<?php
							/* translators: %d: number of results. */
							printf( esc_html( _n( 'Show %d result', 'Show %d results', $upskill_total, 'upskill-me' ) ), (int) $upskill_total );
							?>
						</span>
						<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
					</button>
				</div>
			</div>
		</dialog>

		<div class="flex min-w-0 flex-col">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
				<div>
					<h2 class="text-[2.25rem] leading-[1.1] font-medium tracking-[-0.01em] text-[#0e0e14]">
						<?php
						if ( '' !== $upskill_request['q'] ) {
							/* translators: %s: search words. */
							printf( esc_html__( 'Results for “%s”', 'upskill-me' ), esc_html( $upskill_request['q'] ) );
						} else {
							esc_html_e( 'Featured training', 'upskill-me' );
						}
						?>
					</h2>
					<p class="pt-1.5 text-base text-gray-500" aria-live="polite">
						<?php
						/* translators: %s: number of training sessions. */
						printf( esc_html( _n( '%s training session', '%s training sessions', $upskill_total, 'upskill-me' ) ), esc_html( number_format_i18n( $upskill_total ) ) );
						?>
					</p>
				</div>
				<div class="flex items-center gap-2.5">
					<label class="text-sm text-gray-500" for="library-sort"><?php esc_html_e( 'Sort by', 'upskill-me' ); ?></label>
					<span class="grid flex-1 grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1 lg:w-[203px] lg:flex-none">
						<select class="h-[46px] w-full cursor-pointer appearance-none rounded-full border border-line bg-white py-2 pr-11 pl-4 text-sm font-medium text-[#55555f] focus:border-brand-700 focus:outline-none" id="library-sort" name="sort" data-library-submit>
							<option value="newest"<?php selected( $upskill_request['sort'], 'newest' ); ?>><?php esc_html_e( 'Newest', 'upskill-me' ); ?></option>
							<option value="title"<?php selected( $upskill_request['sort'], 'title' ); ?>><?php esc_html_e( 'A to Z', 'upskill-me' ); ?></option>
						</select>
						<?php upskill_icon( 'chevron-down', 'pointer-events-none mr-4 size-4 self-center justify-self-end text-gray-700' ); ?>
					</span>
					<button class="relative grid size-[46px] flex-none place-items-center rounded-full border border-line bg-white text-gray-900 lg:hidden" type="button" aria-controls="library-filters" data-library-open>
						<span class="screen-reader-text"><?php esc_html_e( 'Filters', 'upskill-me' ); ?></span>
						<?php upskill_icon( 'sliders', 'size-5' ); ?>
						<?php if ( $upskill_active ) : ?>
							<span class="absolute top-2 right-2 size-2 rounded-full bg-brand-700" aria-hidden="true"></span>
						<?php endif; ?>
					</button>
				</div>
			</div>

			<div class="max-w-[620px] pt-6 lg:pt-[26px]">
				<h3 class="text-xl leading-[1.5] font-medium text-[#0e0e14]"><?php esc_html_e( 'Explore training across Upskillme.', 'upskill-me' ); ?></h3>
				<p class="pt-2 text-base text-[#55555f]"><?php esc_html_e( 'Find practical on-the-job training across industries, categories and areas of professional development.', 'upskill-me' ); ?></p>
			</div>

			<?php if ( $upskill_results->have_posts() ) : ?>
				<div class="mt-[22px] grid gap-4 sm:grid-cols-2 lp:grid-cols-3">
					<?php
					foreach ( $upskill_results->posts as $upskill_post ) {
						get_template_part( 'template-parts/cards/training', null, array( 'card' => upskill_library_card( $upskill_post->ID ) ) );
					}
					?>
				</div>
				<?php if ( $upskill_total > $upskill_request['show'] ) : ?>
					<div class="mt-6 flex justify-center">
						<?php // Figma: white pill, 16px label, pale purple nest with a down arrow. ?>
						<a class="btn btn--light min-h-12 border-line pl-5 text-base" href="<?php echo esc_url( $upskill_query( array( 'show' => $upskill_request['show'] + upskill_library_per_page() ) ) . '#results' ); ?>">
							<span class="btn__label">
								<?php
								/* translators: %d: number of results not shown yet. */
								printf( esc_html__( 'Load more training (%d)', 'upskill-me' ), (int) ( $upskill_total - $upskill_request['show'] ) );
								?>
							</span>
							<span class="btn__nest size-7 bg-brand-50 text-brand-700" aria-hidden="true"><?php upskill_icon( 'arrow-down', 'size-3' ); ?></span>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="mt-[22px] flex flex-col items-start gap-4 rounded-lg border border-gray-100 bg-white p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'No training matches these filters yet. Try fewer filters or another search.', 'upskill-me' ); ?></p>
					<a class="text-base font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( $upskill_base . '#results' ); ?>"><?php esc_html_e( 'Clear all filters', 'upskill-me' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</form>
</main>
<?php
get_footer();
