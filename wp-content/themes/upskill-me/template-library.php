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
 * so results work without JavaScript; js/components/library.js updates the
 * results in place (REST) and opens the filter sheet on phones. The page's
 * footer CTA band follows as on every marketing page.
 *
 * @package upskill-me
 */

get_header();

$upskill_request = upskill_library_request();
$upskill_base    = get_permalink();
$upskill_suggest = (array) apply_filters( 'upskill_library_suggestions', array( __( 'Emergency training', 'upskill-me' ), __( 'Training for managers', 'upskill-me' ), __( 'Workplace safety', 'upskill-me' ), __( 'Leadership responsibilities', 'upskill-me' ) ) );

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

				<form class="w-full max-w-[480px]" role="search" action="<?php echo esc_url( $upskill_base ); ?>#results" method="get" data-library-search>
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
								<li><a class="block rounded-full border border-line bg-white px-3.5 py-2 text-sm text-gray-500 transition-colors hover:border-brand-300 hover:text-brand-700" href="<?php echo esc_url( add_query_arg( 'q', rawurlencode( $upskill_term ), $upskill_base ) . '#results' ); ?>" data-library-suggest="<?php echo esc_attr( $upskill_term ); ?>"><?php echo esc_html( $upskill_term ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/library/browser', null, array( 'request' => $upskill_request, 'base' => $upskill_base ) ); ?>
</main>
<?php
get_footer();
