<?php
/**
 * Training library pagination: Previous / numbered pages / Next under the
 * cards. Figma: 49325:14592 (1440) "Previous 1 2 3 … 8 9 10 Next", 49347:88528
 * (390) "Previous 1 … 10 Next"; drawn as the Resource Library's pagination
 * (upskill_pagination()), with Previous and Next shown disabled at the ends.
 *
 * Desktop lists the first and last three pages and those beside the current
 * one; phones only the first, current and last, with an ellipsis for each gap.
 *
 * Links are relative (?…&pg=N#results) so they stay on the page showing the
 * library (/courses/, a term page, the Training Library block) and work without
 * JavaScript; js/components/library.js loads them in place (data-library-page).
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type array    $request upskill_library_request().
 *     @type WP_Query $results upskill_library_results().
 * }
 */

$upskill_request = $args['request'];
$upskill_pages   = (int) $args['results']->max_num_pages;

if ( $upskill_pages < 2 ) {
	return;
}

$upskill_page = min( $upskill_pages, max( 1, (int) $args['results']->get( 'paged' ) ) );
$upskill_link = static function ( $page ) use ( $upskill_request ) {
	$query = array_filter(
		array(
			'q'        => $upskill_request['q'],
			'industry' => $upskill_request['industry'],
			'category' => $upskill_request['category'],
			'level'    => $upskill_request['level'],
			'free'     => $upskill_request['free'] ? 1 : '',
			'sort'     => 'newest' === $upskill_request['sort'] ? '' : $upskill_request['sort'],
			'all'      => $upskill_request['all'] ? 1 : '',
			'pg'       => $page > 1 ? $page : '',
		)
	);

	// industry[]=a&industry[]=b, as the form and js/components/library.js write them.
	return '?' . preg_replace( '/%5B[0-9]+%5D=/', '%5B%5D=', http_build_query( $query, '', '&', PHP_QUERY_RFC3986 ) ) . '#results';
};

// Which pages each layout lists (as paginate_links() with end_size 3, mid_size 1).
$upskill_desktop = static function ( $n ) use ( $upskill_page, $upskill_pages ) {
	return $n <= 3 || $n > $upskill_pages - 3 || abs( $n - $upskill_page ) <= 1;
};
$upskill_mobile  = static function ( $n ) use ( $upskill_page, $upskill_pages ) {
	return 1 === $n || $upskill_pages === $n || $upskill_page === $n;
};

// Numbers and ellipses in order; 'only' hides an item at the other layout.
$upskill_items = array();
for ( $upskill_n = 1; $upskill_n <= $upskill_pages; $upskill_n++ ) {
	$upskill_gap_d = ! $upskill_desktop( $upskill_n ) && $upskill_desktop( $upskill_n - 1 );
	$upskill_gap_m = ! $upskill_mobile( $upskill_n ) && $upskill_mobile( $upskill_n - 1 );

	if ( $upskill_gap_d || $upskill_gap_m ) {
		$upskill_items[] = array( 'dots', $upskill_gap_d && $upskill_gap_m ? '' : ( $upskill_gap_d ? 'desktop' : 'mobile' ) );
	}
	if ( $upskill_desktop( $upskill_n ) ) {
		$upskill_items[] = array( $upskill_n, $upskill_mobile( $upskill_n ) ? '' : 'desktop' );
	}
}

$upskill_only = array(
	''        => '',
	'desktop' => 'max-sm:hidden',
	'mobile'  => 'sm:hidden',
);
$upskill_step = 'inline-flex items-center gap-2 text-sm font-medium';
?>
<nav class="pagination mt-3.5 flex items-center gap-3 border-t border-[#e9eaeb] pt-5" aria-label="<?php esc_attr_e( 'Training pages', 'upskill-me' ); ?>" data-library-pagination>
	<div class="flex flex-1">
		<?php if ( $upskill_page > 1 ) : ?>
			<a class="<?php echo esc_attr( $upskill_step ); ?> text-[#6744A8] hover:text-brand-700" href="<?php echo esc_url( $upskill_link( $upskill_page - 1 ) ); ?>" rel="prev" data-library-page="<?php echo esc_attr( $upskill_page - 1 ); ?>">
				<?php upskill_icon( 'arrow-right', 'size-4 -scale-x-100' ); ?>
				<?php esc_html_e( 'Previous', 'upskill-me' ); ?>
			</a>
		<?php else : ?>
			<span class="<?php echo esc_attr( $upskill_step ); ?> cursor-default text-[#a4a7ae]" aria-disabled="true">
				<?php upskill_icon( 'arrow-right', 'size-4 -scale-x-100' ); ?>
				<?php esc_html_e( 'Previous', 'upskill-me' ); ?>
			</span>
		<?php endif; ?>
	</div>
	<ul class="flex items-center gap-0.5" role="list">
		<?php foreach ( $upskill_items as $upskill_item ) : ?>
			<li class="<?php echo esc_attr( $upskill_only[ $upskill_item[1] ] ); ?>">
				<?php if ( 'dots' === $upskill_item[0] ) : ?>
					<span class="grid size-10 place-items-center text-sm text-[#717680]" aria-hidden="true">&hellip;</span>
				<?php elseif ( $upskill_item[0] === $upskill_page ) : ?>
					<span class="grid size-10 place-items-center rounded-lg bg-[#fafafa] text-sm text-brand-700" aria-current="page">
						<span class="screen-reader-text"><?php esc_html_e( 'Page', 'upskill-me' ); ?></span>
						<?php echo esc_html( number_format_i18n( $upskill_item[0] ) ); ?>
					</span>
				<?php else : ?>
					<a class="grid size-10 place-items-center rounded-lg text-sm text-[#717680] transition-colors duration-200 hover:bg-brand-25 hover:text-brand-700" href="<?php echo esc_url( $upskill_link( $upskill_item[0] ) ); ?>" data-library-page="<?php echo esc_attr( $upskill_item[0] ); ?>">
						<span class="screen-reader-text"><?php esc_html_e( 'Page', 'upskill-me' ); ?></span>
						<?php echo esc_html( number_format_i18n( $upskill_item[0] ) ); ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class="flex flex-1 justify-end">
		<?php if ( $upskill_page < $upskill_pages ) : ?>
			<a class="<?php echo esc_attr( $upskill_step ); ?> text-[#6744A8] hover:text-brand-700" href="<?php echo esc_url( $upskill_link( $upskill_page + 1 ) ); ?>" rel="next" data-library-page="<?php echo esc_attr( $upskill_page + 1 ); ?>">
				<?php esc_html_e( 'Next', 'upskill-me' ); ?>
				<?php upskill_icon( 'arrow-right', 'size-4' ); ?>
			</a>
		<?php else : ?>
			<span class="<?php echo esc_attr( $upskill_step ); ?> cursor-default text-[#a4a7ae]" aria-disabled="true">
				<?php esc_html_e( 'Next', 'upskill-me' ); ?>
				<?php upskill_icon( 'arrow-right', 'size-4' ); ?>
			</span>
		<?php endif; ?>
	</div>
</nav>
