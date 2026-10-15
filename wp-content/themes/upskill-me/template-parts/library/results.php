<?php
/**
 * Training library results: the cards and "Load more training", or the empty
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
	<?php if ( $upskill_total > $upskill_request['show'] ) : ?>
		<div class="mt-6 flex justify-center">
			<?php // Figma: white pill, 16px label, pale purple nest with a down arrow. ?>
			<a class="btn btn--light min-h-12 border-line pl-5 text-base" href="<?php echo esc_url( upskill_library_url( $upskill_request, array( 'show' => $upskill_request['show'] + upskill_library_per_page() ) ) . '#results' ); ?>" data-library-more="<?php echo esc_attr( $upskill_request['show'] + upskill_library_per_page() ); ?>">
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
		<a class="text-base font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( upskill_library_page_url() . '#results' ); ?>" data-library-reset="all"><?php esc_html_e( 'Clear all filters', 'upskill-me' ); ?></a>
	</div>
<?php endif; ?>
