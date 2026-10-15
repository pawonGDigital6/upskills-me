<?php
/**
 * LearnDash LD30: a course page.
 *
 * Override of sfwd-lms/themes/ld30/templates/course.php (LearnDash 4.21.4 template).
 * Figma: Product Detail - admin 49325:20483 (1440) / 49354:101532 (390).
 *
 * Learners who already have access get LearnDash's own course page (status,
 * progress, session list, certificate): the plugin template is included
 * unchanged. Everyone else gets the product page as drawn: the course and its
 * buy box (the linked WooCommerce product's levels, seats = quantity, posted to
 * WooCommerce's add-to-cart), then the facts, the course text, the session list
 * and the course details. Free (Sample) sessions link straight to the player.
 * LearnDash's before/after hooks still run.
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
	include LEARNDASH_LMS_PLUGIN_DIR . 'themes/ld30/templates/course.php';
	return;
}

$upskill_product  = upskill_course_product( $course_id );
$upskill_levels   = $upskill_product ? upskill_product_levels( $upskill_product ) : array();
$upskill_sessions = array_values( array_map( 'intval', (array) learndash_course_get_steps_by_type( $course_id, learndash_get_post_type_slug( 'lesson' ) ) ) );
$upskill_count    = count( $upskill_sessions );
$upskill_duration = upskill_course_duration( $upskill_sessions );
$upskill_category = get_the_terms( $course_id, 'training_category' );
$upskill_category = is_array( $upskill_category ) ? $upskill_category[0] : null;
$upskill_industry = get_the_terms( $course_id, 'industry' );
$upskill_industry = is_array( $upskill_industry ) ? $upskill_industry[0] : null;
$upskill_level    = get_the_terms( $course_id, 'cohort' );
$upskill_level    = is_array( $upskill_level ) ? implode( ', ', wp_list_pluck( $upskill_level, 'name' ) ) : '';
$upskill_format   = $upskill_sessions ? (string) get_field( 'format', $upskill_sessions[0] ) : '';
$upskill_cert     = (bool) learndash_get_setting( $course_id, 'certificate' );
$upskill_outcomes = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_field( 'outcomes', $course_id ) ) ) );
$upskill_areas    = array_filter( (array) get_field( 'learn_areas', $course_id ), static fn( $row ) => ! empty( $row['title'] ) );
$upskill_summary  = function_exists( 'upskill_learner_course_summary' ) ? upskill_learner_course_summary( $course_id ) : '';
$upskill_first    = $upskill_levels ? $upskill_levels[0] : null;
$upskill_library  = function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : home_url( '/' );

$upskill_facts = array_filter(
	array(
		array( 'video-book', $upskill_count ? sprintf( /* translators: %d: number of sessions. */ _n( '%d video session', '%d video sessions', $upskill_count, 'upskill-me' ), $upskill_count ) : '', __( 'Focused video-based learning', 'upskill-me' ) ),
		array( 'clock', $upskill_duration ? sprintf( /* translators: %s: duration. */ __( '%s total', 'upskill-me' ), $upskill_duration ) : '', __( 'Total learning time', 'upskill-me' ) ),
		array( 'monitor', __( 'On demand', 'upskill-me' ), __( 'Learn when you need it', 'upskill-me' ) ),
		array( 'workplace', __( 'On The Job', 'upskill-me' ), __( 'Practical workplace knowledge', 'upskill-me' ) ),
		$upskill_cert ? array( 'award', __( 'Certificate', 'upskill-me' ), __( 'Certificate of completion', 'upskill-me' ) ) : array(),
	),
	static fn( $fact ) => ! empty( $fact[1] )
);

$upskill_details = array_filter(
	array(
		__( 'Category', 'upskill-me' )   => $upskill_category ? $upskill_category->name : '',
		__( 'Course', 'upskill-me' )     => get_the_title( $course_id ),
		__( 'Format', 'upskill-me' )     => $upskill_format ? ucfirst( $upskill_format ) : __( 'Video sessions', 'upskill-me' ),
		__( 'Sessions', 'upskill-me' )   => $upskill_count ? (string) $upskill_count : '',
		__( 'Duration', 'upskill-me' )   => $upskill_duration,
		__( 'Level', 'upskill-me' )      => $upskill_level,
		__( 'Access', 'upskill-me' )     => __( 'On demand', 'upskill-me' ),
		__( 'Completion', 'upskill-me' ) => $upskill_cert ? __( 'Certificate of completion', 'upskill-me' ) : '',
	)
);
?>
<div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?> course-product">
	<?php
	/** This action is documented in the LearnDash course template. */
	do_action( 'learndash-course-before', get_the_ID(), $course_id, $user_id );
	?>

	<section class="bg-gray-25 pt-6 pb-12 lg:pt-12 lg:pb-20">
		<div class="container">
			<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
				<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
				<span aria-hidden="true">/</span>
				<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_library ); ?>"><?php esc_html_e( 'Training', 'upskill-me' ); ?></a>
				<?php if ( $upskill_category ) : ?>
					<span aria-hidden="true">/</span>
					<a class="hover:text-brand-700" href="<?php echo esc_url( add_query_arg( 'category[]', $upskill_category->slug, $upskill_library ) ); ?>"><?php echo esc_html( $upskill_category->name ); ?></a>
				<?php endif; ?>
				<span aria-hidden="true">/</span>
				<span class="text-gray-950" aria-current="page"><?php echo esc_html( get_the_title( $course_id ) ); ?></span>
			</nav>

			<div class="mt-8 grid gap-8 lg:mt-14 lg:grid-cols-[minmax(0,1fr)_minmax(0,636px)] lg:items-start lg:gap-16">
				<div class="flex flex-col gap-4">
					<div class="overflow-hidden rounded-[24px] bg-gray-950">
						<?php if ( has_post_thumbnail( $course_id ) ) : ?>
							<?php upskill_image( get_post_thumbnail_id( $course_id ), 'full', array( 'class' => 'aspect-[16/10] w-full object-cover', 'loading' => 'eager', 'sizes' => '(min-width: 1025px) 46vw, 92vw' ) ); ?>
						<?php else : ?>
							<div class="grid aspect-[16/10] place-items-center"><img class="w-2/5 max-w-[200px] opacity-80" src="<?php echo esc_url( upskill_theme_image( 'logo-white.webp' ) ); ?>" alt="" width="192" height="44"></div>
						<?php endif; ?>
					</div>
					<?php if ( $upskill_sessions ) : ?>
						<ol class="grid grid-cols-2 gap-2 sm:grid-cols-4" aria-label="<?php esc_attr_e( 'Sessions', 'upskill-me' ); ?>">
							<?php foreach ( array_slice( $upskill_sessions, 0, 4 ) as $upskill_i => $upskill_session ) : ?>
								<li class="flex items-center justify-between gap-2 rounded-xl border border-line bg-white px-3 py-2.5 text-sm">
									<span class="font-medium text-brand-700"><?php echo esc_html( str_pad( (string) ( $upskill_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
									<span class="text-gray-500"><?php echo esc_html( (string) get_field( 'duration', $upskill_session ) ); ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>

				<div class="flex flex-col rounded-[24px] border border-line bg-white p-5 shadow-xs lg:p-8">
					<p class="text-sm font-medium text-brand-700">
						<?php echo esc_html( trim( ( $upskill_category ? $upskill_category->name . ' · ' : '' ) . __( 'On The Job Training', 'upskill-me' ) ) ); ?>
					</p>
					<h1 class="pt-3 text-h3 font-medium text-gray-950"><?php echo esc_html( get_the_title( $course_id ) ); ?></h1>
					<?php if ( $upskill_summary ) : ?>
						<p class="pt-4 text-base text-gray-500"><?php echo esc_html( $upskill_summary ); ?></p>
					<?php endif; ?>

					<?php if ( $upskill_levels ) : ?>
						<form class="mt-6 flex flex-col" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" data-buy-box>
							<p class="flex items-baseline gap-2">
								<span class="text-h3 font-medium text-gray-950" data-buy-price><?php echo wp_kses_post( $upskill_first['price_html'] ); ?></span>
								<span class="text-sm text-gray-500" data-buy-unit>
									<?php
									/* translators: %s: level name. */
									echo esc_html( sprintf( __( 'per learner · %s', 'upskill-me' ), $upskill_first['label'] ) );
									?>
								</span>
							</p>

							<?php if ( count( $upskill_levels ) > 1 ) : ?>
								<fieldset class="mt-6">
									<legend class="text-base font-medium text-gray-950"><?php esc_html_e( 'Choose your training level', 'upskill-me' ); ?></legend>
									<div class="mt-3 grid gap-2 sm:grid-cols-3">
										<?php foreach ( $upskill_levels as $upskill_i => $upskill_lvl ) : ?>
											<?php $upskill_save = $upskill_lvl['bundle'] ? array_sum( wp_list_pluck( array_filter( $upskill_levels, static fn( $l ) => ! $l['bundle'] ), 'price' ) ) - $upskill_lvl['price'] : 0; ?>
											<label class="buy-level relative flex cursor-pointer flex-col gap-1 rounded-2xl border border-line bg-white p-4 transition-colors hover:border-brand-300 has-checked:border-brand-700 has-checked:bg-brand-25 <?php echo $upskill_lvl['bundle'] ? 'sm:col-span-3 sm:flex-row sm:items-center sm:justify-between' : ''; ?>">
												<input class="sr-only" type="radio" name="variation_id" value="<?php echo esc_attr( $upskill_lvl['id'] ); ?>" data-price="<?php echo esc_attr( wp_strip_all_tags( $upskill_lvl['price_html'] ) ); ?>" data-label="<?php echo esc_attr( $upskill_lvl['label'] ); ?>" data-attributes="<?php echo esc_attr( wp_json_encode( $upskill_lvl['attributes'] ) ); ?>"<?php checked( 0, $upskill_i ); ?>>
												<span class="text-sm font-medium text-gray-950">
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
												<span class="text-lg font-medium text-brand-700"><?php echo wp_kses_post( $upskill_lvl['price_html'] ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</fieldset>
							<?php endif; ?>

							<div class="mt-6 flex flex-wrap items-center gap-3">
								<label class="sr-only" for="buy-seats"><?php esc_html_e( 'Seats', 'upskill-me' ); ?></label>
								<span class="flex h-[50px] items-center rounded-full border border-line bg-white">
									<button class="grid size-[50px] place-items-center rounded-full text-xl text-gray-700 hover:text-brand-700" type="button" data-buy-step="-1"><span class="sr-only"><?php esc_html_e( 'Fewer seats', 'upskill-me' ); ?></span><span aria-hidden="true">−</span></button>
									<input class="w-12 appearance-none bg-transparent text-center text-base font-medium text-gray-950 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none" id="buy-seats" type="number" name="quantity" value="1" min="1" max="999" inputmode="numeric">
									<button class="grid size-[50px] place-items-center rounded-full text-xl text-gray-700 hover:text-brand-700" type="button" data-buy-step="1"><span class="sr-only"><?php esc_html_e( 'More seats', 'upskill-me' ); ?></span><span aria-hidden="true">+</span></button>
								</span>
								<button class="btn btn--primary btn--md min-h-[50px] flex-1 justify-center" type="submit" name="add-to-cart" value="<?php echo esc_attr( $upskill_product->get_id() ); ?>">
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
							<p class="pt-4 text-sm text-gray-500"><?php esc_html_e( 'One seat per learner. Assign seats to your people after checkout.', 'upskill-me' ); ?></p>
							<p class="flex items-center gap-2 pt-2 text-sm text-gray-500"><?php upskill_icon( 'shield', 'size-4 text-brand-700' ); ?><?php esc_html_e( 'Secure checkout · On demand access', 'upskill-me' ); ?></p>
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

	<?php if ( $upskill_facts ) : ?>
		<section class="border-y border-line bg-white py-10" aria-labelledby="course-glance">
			<div class="container">
				<h2 class="sr-only" id="course-glance"><?php esc_html_e( 'At a glance', 'upskill-me' ); ?></h2>
				<ul class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-5">
					<?php foreach ( $upskill_facts as $upskill_fact ) : ?>
						<li class="flex items-start gap-3">
							<span class="grid size-11 flex-none place-items-center rounded-[13px] bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( $upskill_fact[0], 'size-5' ); ?></span>
							<span class="flex flex-col">
								<span class="text-base font-medium text-gray-950"><?php echo esc_html( $upskill_fact[1] ); ?></span>
								<span class="text-sm text-gray-500"><?php echo esc_html( $upskill_fact[2] ); ?></span>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( trim( wp_strip_all_tags( (string) $content ) ) || $upskill_outcomes ) : ?>
		<section class="section-y bg-white" aria-labelledby="course-about">
			<div class="container grid gap-10 lg:grid-cols-2 lg:gap-14">
				<div class="flex flex-col items-start gap-5">
					<?php upskill_eyebrow( __( 'About this training', 'upskill-me' ), 'book-open' ); ?>
					<h2 class="text-h3 font-medium text-gray-950" id="course-about"><?php echo esc_html( get_the_title( $course_id ) ); ?></h2>
					<div class="course-product__content flex flex-col gap-4 text-base text-gray-500"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash passes filtered post content. ?></div>
				</div>
				<?php if ( $upskill_outcomes ) : ?>
					<div class="rounded-[24px] border border-line bg-gray-25 p-6 lg:p-8">
						<p class="text-lg font-medium text-gray-950"><?php esc_html_e( 'This course is designed to help people:', 'upskill-me' ); ?></p>
						<ul class="mt-5 flex flex-col gap-3">
							<?php foreach ( $upskill_outcomes as $upskill_outcome ) : ?>
								<li class="flex items-start gap-3 text-base text-gray-950">
									<span class="mt-0.5 grid size-6 flex-none place-items-center rounded-full bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( 'check', 'size-3' ); ?></span>
									<?php echo esc_html( $upskill_outcome ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $upskill_areas ) : ?>
		<section class="section-y bg-brand-25" aria-labelledby="course-learn">
			<div class="container">
				<div class="flex flex-col items-start gap-5">
					<?php upskill_eyebrow( __( "What you'll learn", 'upskill-me' ), 'target' ); ?>
					<h2 class="text-h3 font-medium text-gray-950" id="course-learn"><?php esc_html_e( 'Knowledge you can use on the job.', 'upskill-me' ); ?></h2>
				</div>
				<ol class="mt-stack grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( array_values( $upskill_areas ) as $upskill_i => $upskill_area ) : ?>
						<li class="flex flex-col rounded-[22px] border border-line bg-white p-6">
							<span class="text-sm font-semibold tracking-[0.1em] text-[#8b8b97]"><?php echo esc_html( str_pad( (string) ( $upskill_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3 class="pt-2.5 text-lg font-medium text-gray-950"><?php echo esc_html( $upskill_area['title'] ); ?></h3>
							<?php if ( ! empty( $upskill_area['text'] ) ) : ?>
								<p class="pt-2 text-base text-gray-500"><?php echo esc_html( $upskill_area['text'] ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $upskill_sessions ) : ?>
		<section class="section-y bg-white" aria-labelledby="course-content">
			<div class="container">
				<div class="flex flex-col items-start gap-5">
					<?php upskill_eyebrow( __( 'Course content', 'upskill-me' ), 'video-book' ); ?>
					<h2 class="text-h3 font-medium text-gray-950" id="course-content"><?php esc_html_e( 'Explore the training.', 'upskill-me' ); ?></h2>
					<p class="text-sm text-gray-500">
						<?php
						echo esc_html(
							trim(
								/* translators: %d: number of sessions. */
								sprintf( _n( '%d session', '%d sessions', $upskill_count, 'upskill-me' ), $upskill_count ) . ( $upskill_duration ? ' · ' . $upskill_duration : '' )
							)
						);
						?>
					</p>
				</div>
				<ol class="mt-8 flex flex-col overflow-hidden rounded-[22px] border border-line">
					<?php foreach ( $upskill_sessions as $upskill_i => $upskill_session ) : ?>
						<?php
						$upskill_free  = 'on' === learndash_get_setting( $upskill_session, 'sample_lesson' );
						$upskill_title = preg_replace( '/^\s*session\s*:\s*/i', '', get_the_title( $upskill_session ) );
						$upskill_text  = has_excerpt( $upskill_session ) ? get_the_excerpt( $upskill_session ) : '';
						?>
						<li class="flex flex-col gap-2 border-line bg-white px-5 py-5 not-first:border-t lg:flex-row lg:items-start lg:gap-6 lg:px-8">
							<span class="w-8 flex-none text-sm font-medium text-gray-500"><?php echo esc_html( str_pad( (string) ( $upskill_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div class="flex min-w-0 flex-1 flex-col gap-1.5">
								<h3 class="text-lg font-medium text-gray-950"><?php echo esc_html( $upskill_title ); ?></h3>
								<?php if ( $upskill_text ) : ?>
									<p class="text-base text-gray-500"><?php echo esc_html( $upskill_text ); ?></p>
								<?php endif; ?>
								<?php if ( $upskill_free ) : ?>
									<a class="inline-flex items-center gap-2 self-start pt-1 text-sm font-semibold text-brand-700 hover:underline" href="<?php echo esc_url( learndash_get_step_permalink( $upskill_session, $course_id ) ); ?>"><?php upskill_icon( 'play', 'size-3' ); ?><?php esc_html_e( 'Watch Free Training', 'upskill-me' ); ?></a>
								<?php endif; ?>
							</div>
							<span class="flex-none text-sm text-gray-500">
								<?php if ( $upskill_free ) : ?>
									<span class="mr-2 rounded-sm bg-signal px-2 py-0.5 text-xs font-semibold text-gray-950 uppercase"><?php esc_html_e( 'Free', 'upskill-me' ); ?></span>
								<?php endif; ?>
								<?php echo esc_html( (string) get_field( 'duration', $upskill_session ) ); ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<section class="section-y bg-gray-25" aria-labelledby="course-details">
		<div class="container grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,766px)] lg:gap-14">
			<div class="flex flex-col items-start gap-5">
				<?php upskill_eyebrow( __( 'Course details', 'upskill-me' ), 'list-settings' ); ?>
				<h2 class="text-h3 font-medium text-gray-950" id="course-details"><?php esc_html_e( 'Everything specified.', 'upskill-me' ); ?></h2>
				<p class="text-base text-gray-500"><?php esc_html_e( 'A quick overview of the course format, content, learning level, access, duration and completion.', 'upskill-me' ); ?></p>
			</div>
			<dl class="overflow-hidden rounded-[22px] border border-line bg-white">
				<?php foreach ( $upskill_details as $upskill_term => $upskill_value ) : ?>
					<div class="grid grid-cols-2 gap-4 border-line px-5 py-4 not-first:border-t lg:px-8">
						<dt class="text-base text-gray-500"><?php echo esc_html( $upskill_term ); ?></dt>
						<dd class="text-base font-medium text-gray-950"><?php echo esc_html( $upskill_value ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>

	<?php
	/** This action is documented in the LearnDash course template. */
	do_action( 'learndash-course-after', get_the_ID(), $course_id, $user_id );
	?>
</div>
