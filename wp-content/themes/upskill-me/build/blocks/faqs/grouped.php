<?php
/**
 * FAQs block — grouped layout (the FAQs page).
 *
 * Figma: FAQs 49325:17989 (1440) / 49374:109536 (390).
 *
 * The header and help card stay in view on the left while every chosen topic
 * lists its questions on the right. Each topic collapses as a whole, and
 * inside it one answer is open at a time (the first, to start). Phones also
 * get topic chips that jump to a topic and a search that filters questions
 * (view.js).
 *
 * Included from render.php, which provides $block, $attributes and $card.
 *
 * @package upskill-me
 */

$topics = upskill_selected_terms( get_field( 'faq_topics' ), 'faq_category' );

if ( ! $topics ) {
	$topics = get_terms(
		array(
			'taxonomy'   => 'faq_category',
			'hide_empty' => true,
		)
	);
	$topics = is_array( $topics ) ? $topics : array();
}

$groups = array();

foreach ( $topics as $topic ) {
	$posts = get_posts(
		array(
			'post_type'              => 'faq',
			'numberposts'            => -1,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'update_post_meta_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- one indexed term lookup per topic.
				array(
					'taxonomy' => 'faq_category',
					'terms'    => $topic->term_id,
				),
			),
		)
	);

	if ( $posts ) {
		$groups[] = array(
			'topic' => $topic,
			'posts' => $posts,
		);
	}
}

$uid = wp_unique_id( 'faq-' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> data-faq-groups>
	<div class="container">
		<div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-14">
			<?php // 514 of the 1360 content box, held in view while the topics scroll. ?>
			<div class="flex flex-col gap-6 lg:sticky lg:top-[calc(var(--upskill-header-height)-20px)] lg:w-[37.8%] lg:flex-none lg:gap-8">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'button' => false,
						'reveal' => true,
						'class'  => 'section-header--base',
					)
				);
				get_template_part( 'template-parts/faq-help-card', null, array( 'card' => $card ) );
				?>
			</div>

			<div class="min-w-0 flex-1">
				<?php if ( count( $groups ) > 1 ) : ?>
					<?php // Phones only, as drawn: jump to a topic, or search every question. ?>
					<div class="flex flex-col gap-4 pt-6 lg:hidden">
						<nav class="-mx-gutter overflow-x-auto px-gutter [scrollbar-width:none]" aria-label="<?php esc_attr_e( 'FAQ topics', 'upskill-me' ); ?>">
							<ul class="flex gap-2">
								<?php foreach ( $groups as $group ) : ?>
									<li class="flex-none">
										<a class="block rounded-full border border-gray-900/10 px-4 py-2 text-sm whitespace-nowrap text-[#55555a] transition-colors hover:border-brand-700 hover:text-brand-700" href="#<?php echo esc_attr( $uid . '-' . $group['topic']->slug ); ?>"><?php echo esc_html( $group['topic']->name ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
						<div class="relative">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid . '-search' ); ?>"><?php esc_html_e( 'Search FAQs', 'upskill-me' ); ?></label>
							<?php upskill_icon( 'search', 'pointer-events-none absolute top-1/2 left-4 size-[18px] -translate-y-1/2 text-[#6b6b72]' ); ?>
							<input class="h-12 w-full rounded-full border border-gray-900/10 bg-white pr-12 pl-11 text-base text-gray-950 placeholder:text-[#6b6b72] focus:border-brand-700 focus:outline-none" id="<?php echo esc_attr( $uid . '-search' ); ?>" type="search" placeholder="<?php esc_attr_e( 'Search FAQs', 'upskill-me' ); ?>" autocomplete="off" data-faq-search>
							<p class="pt-4 text-base text-gray-500" hidden data-faq-empty><?php esc_html_e( 'No questions match your search.', 'upskill-me' ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php foreach ( $groups as $group_index => $group ) : ?>
					<?php
					$topic    = $group['topic'];
					$group_id = $uid . '-' . $topic->slug;
					?>
					<div class="faq-group scroll-mt-[calc(var(--upskill-header-height)-20px)] <?php echo 0 === $group_index ? 'pt-8 lg:pt-12' : 'pt-8 lg:pt-14'; ?>" id="<?php echo esc_attr( $group_id ); ?>" data-faq-group>
						<h2>
							<button class="faq__question faq-group__toggle flex w-full items-center justify-between gap-3 pt-5 pb-6 text-left text-h6 font-medium lg:gap-6 lg:text-[36px] lg:leading-[1.1] text-gray-900" type="button" aria-expanded="true" aria-controls="<?php echo esc_attr( $group_id . '-list' ); ?>" data-disclosure>
								<?php echo esc_html( $topic->name ); ?>
								<span class="faq__toggle relative size-[34px] flex-none rounded-full border border-gray-900/20" aria-hidden="true"></span>
							</button>
						</h2>
						<div class="collapse is-open" id="<?php echo esc_attr( $group_id . '-list' ); ?>">
							<div>
								<div class="border-t border-gray-900/10" data-disclosure-group>
									<?php foreach ( $group['posts'] as $index => $faq ) : ?>
										<?php
										$open     = 0 === $index;
										$panel_id = $group_id . '-' . $faq->ID;
										?>
										<div class="faq border-b border-gray-900/10" data-faq-item>
											<h3>
												<button class="faq__question flex min-h-[77.5px] w-full items-center justify-between gap-3 py-5 text-left text-xl lg:gap-6 font-medium text-gray-950" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-disclosure>
													<?php echo esc_html( get_the_title( $faq ) ); ?>
													<span class="faq__toggle relative size-[34px] flex-none rounded-full border border-gray-900/20" aria-hidden="true"></span>
												</button>
											</h3>
											<div class="collapse<?php echo $open ? ' is-open' : ''; ?>" id="<?php echo esc_attr( $panel_id ); ?>">
												<div>
													<div class="faq__answer max-w-[682px] pb-4 lg:pr-12 text-base text-gray-500">
														<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $faq ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core content filter. ?>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
