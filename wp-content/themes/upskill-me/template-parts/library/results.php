<?php
/**
 * Training library results: a page of cards and the pagination, or the empty
 * message. Rendered by template-library.php and by the library REST route
 * (inc/lms-library.php), so a filtered page and an in-place update match.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type array    $request upskill_library_request().
 *     @type WP_Query $results upskill_library_results().
 *     @type int      $total   Number of matching courses.
 * }
 */

$upskill_request = $args['request'];
$upskill_results = $args['results'];
$upskill_total   = (int) $args['total'];
?>
<?php if ( $upskill_results->have_posts() ) : ?>
	<div class="mt-[22px] grid gap-4 sm:grid-cols-2 lp:grid-cols-3">
		<?php
		foreach ( $upskill_results->posts as $upskill_post ) {
			get_template_part( 'template-parts/cards/training', null, array( 'card' => upskill_library_card( $upskill_post->ID ) ) );
		}
		?>
	</div>
	<?php get_template_part( 'template-parts/library/pagination', null, array( 'request' => $upskill_request, 'results' => $upskill_results ) ); ?>
<?php else : ?>
	<div class="mt-[22px] flex flex-col items-start gap-4 rounded-lg border border-gray-100 bg-white p-6">
		<p class="text-base text-gray-700"><?php esc_html_e( 'No training matches these filters yet. Try fewer filters or another search.', 'upskill-me' ); ?></p>
		<a class="text-base font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( upskill_library_page_url() . '#results' ); ?>" data-library-reset="all"><?php esc_html_e( 'Clear all filters', 'upskill-me' ); ?></a>
	</div>
<?php endif; ?>
