<?php
/**
 * LearnDash LD30: a course page.
 *
 * Override of sfwd-lms/themes/ld30/templates/course.php (LearnDash 4.21.4 template).
 * Figma: Product Detail - admin 49325:20483 (1440) / 49354:101532 (390).
 *
 * Learners who already have access get LearnDash's own course page (status,
 * progress, session list, certificate, reviews tab): the plugin template is
 * included unchanged, with the course summary as its text (the course's
 * blocks are the sales page, not a learner's course description).
 *
 * Everyone else gets the product page. This template draws the hero: the
 * course preview and session tabs, and the buy box (the linked WooCommerce
 * product's levels, seats = quantity, posted to WooCommerce's add-to-cart).
 * Everything below it is the course's own block content (At a Glance, About
 * This Training, What You'll Learn, Course Details, Course Content, Reviews,
 * the shared "Course page sections" pattern), which new courses start with
 * (inc/lms-course.php). LearnDash's before/after hooks still run.
 *
 * Variables from LearnDash: $course_id, $user_id, $has_access, $content,
 * $lessons, $logged_in (and the rest documented in the plugin template).
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $has_access || ! function_exists( 'upskill_course_product' ) ) {
	if ( $has_access && function_exists( 'upskill_learner_course_summary' ) ) {
		$content = wpautop( esc_html( has_excerpt( $course_id ) ? get_the_excerpt( $course_id ) : upskill_learner_course_summary( $course_id ) ) );
	}
	include LEARNDASH_LMS_PLUGIN_DIR . 'themes/ld30/templates/course.php';
	return;
}

$upskill_product  = upskill_course_product( $course_id );
$upskill_levels   = $upskill_product ? upskill_product_levels( $upskill_product ) : array();
$upskill_sessions = upskill_course_sessions( $course_id );
$upskill_category = get_the_terms( $course_id, 'training_category' );
$upskill_category = is_array( $upskill_category ) ? $upskill_category[0] : null;
$upskill_summary  = function_exists( 'upskill_learner_course_summary' ) ? upskill_learner_course_summary( $course_id ) : '';
$upskill_heading  = trim( (string) get_field( 'display_title', $course_id ) );
$upskill_reviews  = upskill_course_reviews( $course_id );
$upskill_preview  = upskill_course_preview_session( $course_id );
$upskill_image    = get_post_thumbnail_id( $course_id );
$upskill_first    = $upskill_levels ? $upskill_levels[0] : null;
$upskill_library  = function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : home_url( '/' );
$upskill_term_url = $upskill_category ? get_term_link( $upskill_category ) : '';
?>
<?php // Not wrapped in LearnDash's .learndash-wrapper: its CSS (e.g. box-shadow: none !important on every link) would restyle the theme blocks below. ?>
<div class="course-product">
	<?php
	/** This action is documented in the LearnDash course template. */
	do_action( 'learndash-course-before', get_the_ID(), $course_id, $user_id );
	?>

	<section class="course-hero bg-gray-25 pt-6 pb-16 lg:pt-10 lg:pb-[72px]">
		<div class="container">
			<nav class="flex flex-wrap items-center gap-2 text-xs leading-[1.62] tracking-[-0.01em] text-[#6b6b72]" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
				<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
				<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
				<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_library ); ?>"><?php esc_html_e( 'Courses', 'upskill-me' ); ?></a>
				<?php if ( $upskill_category && ! is_wp_error( $upskill_term_url ) ) : ?>
					<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
					<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_term_url ); ?>"><?php echo esc_html( $upskill_category->name ); ?></a>
				<?php endif; ?>
				<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
				<span class="font-medium text-[#5d3a8f]" aria-current="page"><?php echo esc_html( get_the_title( $course_id ) ); ?></span>
			</nav>

			<div class="mt-6 grid gap-8 lg:mt-8 lg:grid-cols-[minmax(0,659fr)_minmax(0,664fr)] lg:items-start lg:gap-10">
				<?php // min-w-0: a grid item sizes to its content by default, and Swiper sizes to its container, so without it they grow each other. ?>
				<div class="course-hero__media flex min-w-0 flex-col gap-3">
					<?php
					// Gallery items: the preview video (first free session), then each other session's preview image
					// (its featured image); without a preview video the course image leads.
					$upskill_gallery = array();

					if ( $upskill_preview ) {
						$upskill_gallery[] = array(
							'type'    => 'video',
							'session' => $upskill_preview['id'],
							'image'   => get_post_thumbnail_id( $upskill_preview['id'] ) ? get_post_thumbnail_id( $upskill_preview['id'] ) : $upskill_image,
						);
					} elseif ( $upskill_image ) {
						$upskill_gallery[] = array(
							'type'    => 'image',
							'session' => 0,
							'image'   => $upskill_image,
						);
					}

					foreach ( $upskill_sessions as $upskill_session ) {
						if ( ( ! $upskill_preview || $upskill_preview['id'] !== $upskill_session ) && get_post_thumbnail_id( $upskill_session ) ) {
							$upskill_gallery[] = array(
								'type'    => 'image',
								'session' => $upskill_session,
								'image'   => get_post_thumbnail_id( $upskill_session ),
							);
						}
					}

					// "01 · Session title" (the course title for the course image).
					$upskill_caption = static function ( $item ) use ( $upskill_sessions, $course_id ) {
						if ( ! $item['session'] ) {
							return get_the_title( $course_id );
						}

						return str_pad( (string) ( array_search( $item['session'], $upskill_sessions, true ) + 1 ), 2, '0', STR_PAD_LEFT ) . ' · ' . upskill_session_title( $item['session'] );
					};
					?>
					<?php if ( $upskill_gallery ) : ?>
						<?php
						// WooCommerce-style gallery on Swiper (js/components/course-gallery.js): the thumbnails only change
						// the main slide; clicking an image slide opens it in the popup; the video slide plays in place
						// with the shared transport bar (template-parts/video/controls.php).
						?>
						<div class="course-hero__gallery flex flex-col gap-3" data-course-gallery>
							<div class="swiper w-full overflow-hidden rounded-[24px] bg-gray-950" data-gallery-main>
								<div class="swiper-wrapper">
									<?php foreach ( $upskill_gallery as $upskill_index => $upskill_item ) : ?>
										<div class="swiper-slide">
											<?php if ( 'video' === $upskill_item['type'] ) : ?>
												<div class="video-card grid aspect-[659/444] grid-cols-1 grid-rows-1 overflow-hidden bg-gray-950 *:col-start-1 *:row-start-1" data-video-card>
													<video class="size-full object-cover" preload="none" playsinline controls data-video<?php echo $upskill_image ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $upskill_image, 'full' ) ) . '"' : ''; ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: session title. */ __( 'Free preview: %s', 'upskill-me' ), upskill_session_title( $upskill_item['session'] ) ) ); ?>">
														<source src="<?php echo esc_url( $upskill_preview['video'] ); ?>" type="video/mp4">
													</video>
													<div class="video-card__scrim pointer-events-none<?php echo $upskill_image ? '' : ' video-card__scrim--tint'; ?>" aria-hidden="true"></div>
													<?php get_template_part( 'template-parts/video/controls', null, array( 'class' => 'z-10 px-3 pb-2' ) ); ?>
												</div>
											<?php else : ?>
												<button class="block w-full cursor-zoom-in" type="button" data-gallery-zoom data-src="<?php echo esc_url( (string) wp_get_attachment_image_url( $upskill_item['image'], 'full' ) ); ?>" data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $upskill_item['image'], 'full' ) ); ?>" data-caption="<?php echo esc_attr( $upskill_caption( $upskill_item ) ); ?>">
													<span class="sr-only"><?php echo esc_html( sprintf( /* translators: %s: image caption. */ __( 'Enlarge: %s', 'upskill-me' ), $upskill_caption( $upskill_item ) ) ); ?></span>
													<?php upskill_image( $upskill_item['image'], 'full', array( 'class' => 'aspect-[659/444] w-full object-contain', 'alt' => '', 'loading' => 0 === $upskill_index ? 'eager' : 'lazy', 'sizes' => '(min-width: 1440px) 659px, (min-width: 1025px) 46vw, 92vw' ) ); ?>
												</button>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<?php if ( count( $upskill_gallery ) > 1 ) : ?>
								<div class="swiper w-full" data-gallery-thumbs>
									<div class="swiper-wrapper">
										<?php foreach ( $upskill_gallery as $upskill_item ) : ?>
											<?php $upskill_number = $upskill_item['session'] ? str_pad( (string) ( array_search( $upskill_item['session'], $upskill_sessions, true ) + 1 ), 2, '0', STR_PAD_LEFT ) : ''; ?>
											<div class="swiper-slide grid aspect-[156/87] w-[156px]! cursor-pointer grid-cols-1 grid-rows-1 overflow-hidden rounded-xl border-2 border-transparent bg-gray-950 text-white *:col-start-1 *:row-start-1 hover:border-brand-500 [&.swiper-slide-thumb-active]:border-brand-700">
												<?php if ( $upskill_item['image'] ) : ?>
													<?php upskill_image( $upskill_item['image'], 'full', array( 'class' => 'size-full object-cover', 'alt' => '', 'sizes' => '(min-width: 1440px) 156px, (min-width: 1025px) 11vw, 23vw' ) ); ?>
												<?php endif; ?>
												<?php if ( 'video' === $upskill_item['type'] ) : ?>
													<span class="grid size-7 place-items-center self-center justify-self-center rounded-full bg-gray-950/60" aria-hidden="true"><?php upskill_icon( 'play', 'size-3' ); ?></span>
												<?php endif; ?>
												<span class="flex items-end justify-between gap-1 self-end bg-linear-to-t from-gray-950/70 to-transparent px-2 pt-4 pb-1.5">
													<span class="text-sm font-medium"><?php echo esc_html( $upskill_number ); ?></span>
													<span class="text-[10px] leading-[1.5]"><?php echo esc_html( $upskill_item['session'] ? (string) get_field( 'duration', $upskill_item['session'] ) : '' ); ?></span>
												</span>
												<span class="sr-only"><?php echo esc_html( $upskill_caption( $upskill_item ) ); ?></span>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<dialog class="m-auto max-h-none w-[min(1100px,calc(100vw-32px))] max-w-none overflow-visible bg-transparent p-0 text-white backdrop:bg-gray-950/85" aria-label="<?php esc_attr_e( 'Image preview', 'upskill-me' ); ?>" data-gallery-popup>
								<figure class="flex flex-col gap-3">
									<img class="w-full rounded-2xl bg-gray-950" src="" alt="" sizes="(min-width: 1140px) 1100px, calc(100vw - 32px)" data-gallery-popup-image>
									<figcaption class="text-sm" data-gallery-popup-caption></figcaption>
								</figure>
								<div class="mt-4 flex justify-center gap-3">
									<button class="grid size-11 place-items-center rounded-full bg-white/15 hover:bg-white/25" type="button" data-gallery-popup-prev><span class="sr-only"><?php esc_html_e( 'Previous image', 'upskill-me' ); ?></span><?php upskill_icon( 'chevron-right', 'size-4 rotate-180' ); ?></button>
									<button class="grid size-11 place-items-center rounded-full bg-white/15 hover:bg-white/25" type="button" data-gallery-popup-close><span class="sr-only"><?php esc_html_e( 'Close', 'upskill-me' ); ?></span><?php upskill_icon( 'close', 'size-4' ); ?></button>
									<button class="grid size-11 place-items-center rounded-full bg-white/15 hover:bg-white/25" type="button" data-gallery-popup-next><span class="sr-only"><?php esc_html_e( 'Next image', 'upskill-me' ); ?></span><?php upskill_icon( 'chevron-right', 'size-4' ); ?></button>
								</div>
							</dialog>
						</div>
					<?php else : ?>
						<div class="grid aspect-[659/444] place-items-center rounded-[24px] bg-gray-950"><img class="w-2/5 max-w-[200px] opacity-80" src="<?php echo esc_url( upskill_theme_image( 'logo-white.webp' ) ); ?>" alt="" width="192" height="44"></div>
					<?php endif; ?>

					<?php // Share copies the course link (the copy-link script from the blog, js/components/article.js). ?>
					<div class="relative mt-2 flex">
						<button class="inline-flex h-[42px] items-center rounded-full border border-line bg-white px-5 text-sm font-medium text-gray-950 hover:border-brand-700 hover:text-brand-700 data-copied:border-brand-700 data-copied:text-brand-700 leading-none" type="button" data-copy-link="<?php echo esc_url( get_permalink( $course_id ) ); ?>" data-copied-label="<?php esc_attr_e( 'Link copied!', 'upskill-me' ); ?>" data-copy-failed-label="<?php esc_attr_e( 'Couldn’t copy. Copy the address bar instead.', 'upskill-me' ); ?>"><?php esc_html_e( 'Share', 'upskill-me' ); ?></button>
						<span class="pointer-events-none absolute bottom-full left-0 mb-2 w-max max-w-[220px] rounded-md bg-gray-950 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-xs transition-opacity duration-200 data-visible:opacity-100 z-4" role="status" data-copy-status></span>
					</div>
				</div>

				<div class="course-hero__panel flex flex-col rounded-[24px] border border-line bg-white p-5 shadow-xs lg:p-6">
					<?php upskill_eyebrow( trim( ( $upskill_category ? $upskill_category->name . ' · ' : '' ) . __( 'On The Job Training', 'upskill-me' ) ), 'monitor', 'self-start' ); ?>
					<?php upskill_heading( $upskill_heading ? $upskill_heading : esc_html( get_the_title( $course_id ) ), 'h1', 'pt-3 text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950' ); ?>
					<?php if ( $upskill_summary ) : ?>
						<p class="pt-3 text-base leading-[1.6] text-gray-500"><?php echo esc_html( $upskill_summary ); ?></p>
					<?php endif; ?>

					<?php if ( $upskill_reviews['count'] ) : ?>
						<p class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-4 text-sm text-gray-500">
							<?php // LearnDash Course Reviews average, linked to the Course Reviews block (#reviews). ?>
							<span class="flex gap-0.5 text-[#f79009] -mt-1" aria-hidden="true">
								<?php for ( $upskill_star = 1; $upskill_star <= 5; $upskill_star++ ) : ?>
									<?php upskill_icon( $upskill_star <= round( $upskill_reviews['average'] ) ? 'star' : 'star-outline', 'size-3.5' ); ?>
								<?php endfor; ?>
							</span>
							<span>
								<span class="font-bold text-gray-950"><?php echo esc_html( number_format_i18n( $upskill_reviews['average'], 1 ) ); ?></span>
								<?php
								/* translators: %d: number of reviews. */
								echo esc_html( ' · ' . sprintf( _n( '%d review', '%d reviews', $upskill_reviews['count'], 'upskill-me' ), $upskill_reviews['count'] ) );
								?>
							</span>
							<a class="font-medium text-brand-700 hover:underline" href="#reviews"><?php esc_html_e( 'Read reviews', 'upskill-me' ); ?></a>
						</p>
					<?php endif; ?>

					<?php if ( $upskill_levels ) : ?>
						<form class="mt-5 flex flex-col" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" data-buy-box>
							<p class="flex items-baseline gap-2">
								<span class="text-[36px] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950" data-buy-price><?php echo wp_kses_post( $upskill_first['price_html'] ); ?></span>
								<span class="text-sm text-gray-500" data-buy-unit>
									<?php
									/* translators: %s: level name. */
									echo esc_html( sprintf( __( 'per learner, ex GST · %s', 'upskill-me' ), $upskill_first['label'] ) );
									?>
								</span>
							</p>

							<?php if ( count( $upskill_levels ) > 1 ) : ?>
								<fieldset class="mt-6">
									<legend class="float-left text-xl leading-[1.3] font-medium tracking-[-0.02em] text-gray-950"><?php esc_html_e( 'Choose your training level', 'upskill-me' ); ?></legend>
									<span class="clear-left block pt-1 text-sm text-[#8b8b97] sm:clear-none sm:float-right sm:pt-1"><?php esc_html_e( 'Choose what you need', 'upskill-me' ); ?></span>
									<?php // Three levels sit in one row (Figma); any other count pairs up so no card is left alone on a row. ?>
									<div class="clear-both grid gap-2 pt-6 <?php echo 0 === count( array_filter( $upskill_levels, static fn( $l ) => ! $l['bundle'] ) ) % 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2'; ?>">
										<?php foreach ( $upskill_levels as $upskill_i => $upskill_lvl ) : ?>
											<?php $upskill_save = $upskill_lvl['bundle'] ? array_sum( wp_list_pluck( array_filter( $upskill_levels, static fn( $l ) => ! $l['bundle'] ), 'price' ) ) - $upskill_lvl['price'] : 0; ?>
											<label class="buy-level relative flex cursor-pointer items-center justify-between gap-1 rounded-[14px] sm:flex-col sm:items-start sm:justify-start border border-line bg-white p-4 py-3.5 transition-colors hover:border-brand-300 has-checked:border-brand-700 has-checked:bg-brand-25 <?php echo $upskill_lvl['bundle'] ? 'sm:col-span-3 sm:flex-row sm:items-center sm:justify-between' : ''; ?>">
												<input class="sr-only" type="radio" name="variation_id" value="<?php echo esc_attr( $upskill_lvl['id'] ); ?>" data-price="<?php echo esc_attr( wp_strip_all_tags( $upskill_lvl['price_html'] ) ); ?>" data-label="<?php echo esc_attr( $upskill_lvl['label'] ); ?>" data-text="<?php echo esc_attr( $upskill_lvl['text'] ); ?>" data-attributes="<?php echo esc_attr( wp_json_encode( $upskill_lvl['attributes'] ) ); ?>"<?php checked( 0, $upskill_i ); ?>>
												<span class="text-sm font-medium text-[#55555f] [.buy-level:has(:checked)_&]:text-[#6b2fe3]">
													<?php echo esc_html( $upskill_lvl['label'] ); ?>
													<?php if ( $upskill_save > 0 ) : ?>
														<span class="ml-2 rounded-sm bg-signal px-2 py-0.5 text-xs font-semibold text-gray-950">
															<?php
															/* translators: %s: saving. */
															echo wp_kses_post( sprintf( __( 'Save %s', 'upskill-me' ), wc_price( $upskill_save ) ) );
															?>
														</span>
													<?php endif; ?>
												</span>
												<span class="text-xl leading-[1.55] font-medium tracking-[-0.02em] text-gray-950"><?php echo wp_kses_post( $upskill_lvl['price_html'] ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</fieldset>
							<?php endif; ?>

							<?php // The chosen level's line (its WooCommerce variation description); buy-box.js swaps it with the level. ?>
							<p class="pt-4 text-sm leading-[1.45] text-[#55555f]" data-buy-text<?php echo $upskill_first['text'] ? '' : ' hidden'; ?>><?php echo esc_html( $upskill_first['text'] ); ?></p>

							<div class="mt-6 flex flex-wrap items-center gap-3">
								<label class="sr-only" for="buy-seats"><?php esc_html_e( 'Seats', 'upskill-me' ); ?></label>
								<span class="flex justify-between h-[50px] md:w-[190px] px-[6px] items-center rounded-full border border-line bg-white shadow-[0_1px_2px_0_rgba(26,26,26,0.04),0_4px_14px_-6px_rgba(47,26,81,0.10)]">
									<button class="grid size-[50px] place-items-center rounded-full text-xl text-gray-700 hover:text-brand-700" type="button" data-buy-step="-1"><span class="sr-only"><?php esc_html_e( 'Fewer seats', 'upskill-me' ); ?></span><span aria-hidden="true">−</span></button>
									<input class="w-12 appearance-none bg-transparent text-center text-base font-medium text-gray-950 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none" id="buy-seats" type="number" name="quantity" value="1" min="1" max="999" inputmode="numeric">
									<button class="grid size-[50px] place-items-center rounded-full text-xl text-gray-700 hover:text-brand-700" type="button" data-buy-step="1"><span class="sr-only"><?php esc_html_e( 'More seats', 'upskill-me' ); ?></span><span aria-hidden="true">+</span></button>
								</span>
								<button class="btn btn--primary btn--md min-h-[50px] flex-1 justify-center max-sm:basis-full" type="submit" name="add-to-cart" value="<?php echo esc_attr( $upskill_product->get_id() ); ?>">
									<span class="btn__label"><?php esc_html_e( 'Add to Cart', 'upskill-me' ); ?></span>
									<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
								</button>
							</div>
							<?php // WooCommerce reads the variation's attributes too; the script keeps these in step with the chosen level. ?>
							<?php foreach ( $upskill_first['attributes'] as $upskill_key => $upskill_value ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $upskill_key ); ?>" value="<?php echo esc_attr( $upskill_value ); ?>" data-buy-attribute>
							<?php endforeach; ?>
							<?php // Group Registration names the group the seats go into; the buyer can rename it in their dashboard. ?>
							<input type="hidden" name="ldgr_group_name" value="<?php echo esc_attr( get_the_title( $course_id ) ); ?>">
							<?php if ( ! $upskill_product->is_type( 'variable' ) ) : ?>
								<input type="hidden" name="variation_id" value="">
							<?php endif; ?>
							<p class="pt-4 text-sm text-[#8b8b97]"><?php esc_html_e( 'One seat per learner.', 'upskill-me' ); ?></p>
							<p class="flex gap-2 pt-2 text-sm text-[#8b8b97]"><?php upskill_icon( 'shield', 'size-4 text-brand-700' ); ?><?php esc_html_e( 'Secure checkout · On demand access', 'upskill-me' ); ?></p>
						</form>
					<?php else : ?>
						<div class="mt-6 rounded-2xl bg-brand-25 p-5">
							<p class="text-base text-gray-700"><?php esc_html_e( 'This training is available through an organisation package. Talk to our team about access.', 'upskill-me' ); ?></p>
							<?php upskill_button( array( 'url' => home_url( '/contact/' ), 'title' => __( 'Talk to Our Team', 'upskill-me' ) ), array( 'class' => 'mt-4' ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! $logged_in ) : ?>
						<p class="pt-4 text-sm text-gray-500">
							<?php esc_html_e( 'Already enrolled?', 'upskill-me' ); ?>
							<a class="font-medium text-brand-700 hover:underline" href="<?php echo esc_url( wp_login_url( get_permalink( $course_id ) ) ); ?>"><?php esc_html_e( 'Log in', 'upskill-me' ); ?></a>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash passes filtered post content (the course's blocks). ?>

	<?php
	/** This action is documented in the LearnDash course template. */
	do_action( 'learndash-course-after', get_the_ID(), $course_id, $user_id );
	?>
</div>
