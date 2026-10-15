<?php
/**
 * Training library: the filters and results ("Refine your results", sort,
 * cards, "Load more"). Used by template-library.php (/courses/) and by the
 * industry, category and level pages (template-parts/library/term-page.php),
 * which pass a fixed filter: that filter's list is hidden and kept as a hidden
 * field, so every other filter, "Clear all" and the in-place updates
 * (js/components/library.js) stay inside the term.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type array  $request upskill_library_request().
 *     @type string $base    Address the form submits to.
 *     @type array  $fixed   Optional [ facet key => term slug ] kept on every request.
 * }
 */

$upskill_request = $args['request'];
$upskill_base    = $args['base'];
$upskill_fixed   = isset( $args['fixed'] ) ? (array) $args['fixed'] : array();
$upskill_results = upskill_library_results( $upskill_request );
$upskill_counts  = upskill_library_counts( $upskill_request );
$upskill_total   = (int) $upskill_results->found_posts;
$upskill_labels  = upskill_library_labels( $upskill_request, $upskill_total );
$upskill_active  = array_filter( array( $upskill_request['free'] ) + array_diff_key( array( 'industry' => $upskill_request['industry'], 'category' => $upskill_request['category'], 'level' => $upskill_request['level'] ), $upskill_fixed ) );

$upskill_checkbox = 'peer size-[19px] flex-none cursor-pointer appearance-none rounded-sm border border-[#d8d2ea] bg-white transition-colors checked:border-brand-700 checked:bg-brand-700 disabled:cursor-default';
?>
	<form id="results" class="container scroll-mt-28 pb-12 lg:grid lg:pt-20 lg:grid-cols-[278px_minmax(0,1fr)] lg:items-start lg:gap-10 lg:pb-20" action="<?php echo esc_url( $upskill_base ); ?>#results" method="get" data-library="<?php echo esc_url( rest_url( 'upskill/v1/library' ) ); ?>">
		<?php foreach ( $upskill_fixed as $upskill_key => $upskill_slug ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $upskill_key ); ?>[]" value="<?php echo esc_attr( $upskill_slug ); ?>">
		<?php endforeach; ?>
		<?php if ( '' !== $upskill_request['q'] ) : ?>
			<input type="hidden" name="q" value="<?php echo esc_attr( $upskill_request['q'] ); ?>" data-library-q>
		<?php endif; ?>

		<dialog id="library-filters" class="m-0 mt-auto max-h-[92svh] w-full max-w-none overflow-y-auto rounded-t-[20px] border-0 bg-white p-0 text-gray-950 backdrop:bg-gray-950/50 lg:static lg:mt-0 lg:block lg:max-h-none lg:overflow-visible lg:rounded-[22px] lg:border lg:border-line" aria-labelledby="library-filters-heading" data-library-filters>
			<div class="flex flex-col p-4 lg:p-4">
				<div class="flex items-center justify-between gap-3">
					<h2 id="library-filters-heading" class="text-lg font-medium text-[#0e0e14]"><?php esc_html_e( 'Refine your results', 'upskill-me' ); ?></h2>
					<div class="flex items-center gap-1">
						<a class="rounded-lg px-2 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50 max-lg:hidden" data-library-reset="filters" href="<?php echo esc_url( add_query_arg( array_filter( array( 'q' => $upskill_request['q'] ) ), $upskill_base ) . '#results' ); ?>"><?php esc_html_e( 'Clear all', 'upskill-me' ); ?></a>
						<button class="grid size-10 place-items-center rounded-full text-gray-900 hover:bg-gray-50 lg:hidden" type="button" data-library-close>
							<span class="screen-reader-text"><?php esc_html_e( 'Close filters', 'upskill-me' ); ?></span>
							<?php upskill_icon( 'close', 'size-5' ); ?>
						</button>
					</div>
				</div>
				<a class="self-start pt-2 text-base font-medium text-brand-700 lg:hidden" data-library-reset="filters" href="<?php echo esc_url( add_query_arg( array_filter( array( 'q' => $upskill_request['q'] ) ), $upskill_base ) . '#results' ); ?>"><?php esc_html_e( 'Clear all', 'upskill-me' ); ?></a>

				<?php
				$upskill_groups = array();
				foreach ( array_diff_key( upskill_library_facets(), $upskill_fixed ) as $upskill_key => $upskill_facet ) {
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
										<span class="font-secondary text-xs text-[#8b8b97]" data-library-count><?php echo esc_html( number_format_i18n( $upskill_n ) ); ?></span>
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
											<span class="font-secondary text-xs text-[#8b8b97]" data-library-count><?php echo esc_html( number_format_i18n( $upskill_n ) ); ?></span>
										</label>
									</li>
								<?php endforeach; ?>
							<?php endif; ?>
						</ul>
					</details>
				<?php endforeach; ?>

				<div class="sticky bottom-0 -mx-4 mt-4 border-t border-line bg-white p-4 lg:static lg:mx-0 lg:border-0 lg:p-0 [.js_&]:lg:hidden">
					<button class="btn btn--primary btn--md min-h-[50px] w-full justify-center" type="submit">
						<span class="btn__label" data-library-label="show"><?php echo esc_html( $upskill_labels['show'] ); ?></span>
						<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
					</button>
				</div>
			</div>
		</dialog>

		<div class="flex min-w-0 flex-col">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
				<div>
					<h2 class="text-[2.25rem] leading-[1.1] font-medium tracking-[-0.01em] text-[#0e0e14]" data-library-label="heading"><?php echo esc_html( $upskill_labels['heading'] ); ?></h2>
					<p class="pt-1.5 text-base text-gray-500" aria-live="polite" data-library-label="total"><?php echo esc_html( $upskill_labels['total'] ); ?></p>
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
						<span class="absolute top-2 right-2 size-2 rounded-full bg-brand-700" aria-hidden="true" data-library-dot<?php echo $upskill_active ? '' : ' hidden'; ?>></span>
					</button>
				</div>
			</div>

			<div class="max-w-[620px] pt-6 lg:pt-[26px]">
				<h3 class="text-xl leading-[1.5] font-medium text-[#0e0e14]"><?php esc_html_e( 'Explore training across Upskillme.', 'upskill-me' ); ?></h3>
				<p class="pt-2 text-base text-[#55555f]"><?php esc_html_e( 'Find practical on-the-job training across industries, categories and areas of professional development.', 'upskill-me' ); ?></p>
			</div>

			<?php // While an update loads the cards dim (aria-busy); a failed update shows the message below them. ?>
			<div class="transition-opacity duration-200 aria-busy:opacity-50" data-library-results>
				<?php get_template_part( 'template-parts/library/results', null, array( 'request' => $upskill_request, 'results' => $upskill_results, 'total' => $upskill_total ) ); ?>
			</div>
			<div class="mt-4 flex flex-col items-start gap-3 rounded-lg border border-gray-100 bg-white p-6" role="alert" hidden data-library-error>
				<p class="text-base text-gray-700"><?php esc_html_e( 'We couldn’t update the results. Check your connection and try again.', 'upskill-me' ); ?></p>
				<button class="text-base font-medium text-brand-700 underline-offset-4 hover:underline" type="button" data-library-retry><?php esc_html_e( 'Try again', 'upskill-me' ); ?></button>
			</div>
		</div>
	</form>
