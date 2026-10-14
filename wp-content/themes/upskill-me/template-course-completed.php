<?php
/**
 * Template Name: Course completed
 *
 * The page LearnDash sends a learner to after finishing a course (LearnDash
 * LMS → Courses → Settings → "Global Course Completion Page"; the course is
 * passed as ?course=ID, see inc/lms-learner.php). Figma: "Course Completed"
 * 49325:18711 (1440) / 49375:114243 (390).
 *
 * Shows the course only if the signed-in learner has completed it; without a
 * valid course it shows their most recent completion. "Print Certificate"
 * appears when LearnDash has a certificate for the course. "What's next?"
 * suggests up to three courses the learner does not have yet, same category
 * first, as library cards (template-parts/cards/training.php).
 *
 * @package upskill-me
 */

get_header();

$upskill_user   = wp_get_current_user();
$upskill_course = upskill_completed_course_for( $upskill_user->ID, isset( $_GET['course'] ) ? absint( $_GET['course'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, checked against the user's own completions.
$upskill_name   = $upskill_user->first_name ? $upskill_user->first_name : $upskill_user->display_name;
$upskill_back   = upskill_learner_dashboard_url();
?>
<main id="primary" class="site-main bg-gray-25">
	<?php if ( ! $upskill_course ) : ?>
		<section class="container flex flex-col items-start gap-6 py-16 lg:items-center lg:py-24 lg:text-center">
			<h1 class="heading text-h4 font-medium text-gray-900"><?php esc_html_e( 'No completed course to show yet.', 'upskill-me' ); ?></h1>
			<p class="text-lead text-[#6b6b72]"><?php esc_html_e( 'When you finish a course, its summary appears here.', 'upskill-me' ); ?></p>
			<?php
			upskill_button(
				array(
					'url'   => $upskill_back ? $upskill_back : home_url( '/' ),
					'title' => __( 'Back to my courses', 'upskill-me' ),
				),
				array( 'class' => 'min-h-[50px]' )
			);
			?>
		</section>
	<?php else : ?>
		<?php
		$upskill_meta    = array_filter( array( $upskill_course['category'], $upskill_course['level'] ) );
		$upskill_details = array(
			__( 'Course', 'upskill-me' )       => $upskill_course['title'],
			__( 'Category', 'upskill-me' )     => $upskill_course['category'] ? $upskill_course['category'] : '—',
			__( 'Status', 'upskill-me' )       => __( 'Completed', 'upskill-me' ),
			__( 'Completed on', 'upskill-me' ) => $upskill_course['completed_on'] ? wp_date( 'j F Y', $upskill_course['completed_on'] ) : '—',
		);
		?>
		<section class="container pt-6 pb-[92px] lg:pt-[136px] lg:pb-20" aria-labelledby="completed-heading">
			<div class="flex flex-col items-start lg:items-center lg:text-center">
				<span class="grid size-[72px] place-items-center rounded-full border-4 border-brand-100" aria-hidden="true">
					<span class="grid size-16 place-items-center rounded-full border border-brand-200 bg-brand-50 text-brand-700 shadow-card"><?php upskill_icon( 'completed-badge', 'size-[30px]' ); ?></span>
				</span>
				<h1 id="completed-heading" class="heading pt-5 text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]">
					<?php
					/* translators: %s: the learner's first name. */
					printf( esc_html__( 'Well done, %s', 'upskill-me' ), '<em>' . esc_html( $upskill_name ) . '.</em>' );
					?>
				</h1>
				<p class="pt-4 text-base text-[#6b6b72] lg:pt-11 lg:text-xl lg:leading-[1.55] lg:tracking-[-0.005em]"><?php echo esc_html( $upskill_course['title'] ); ?></p>
				<?php if ( $upskill_meta ) : ?>
					<p class="pt-4 text-base text-[#6b6b72] lg:pt-1.5 lg:text-sm lg:leading-[1.62] lg:tracking-[-0.007em]"><?php echo esc_html( implode( ' · ', $upskill_meta ) ); ?></p>
				<?php endif; ?>
				<p class="mt-6 inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-[3px] text-sm leading-[1.62] tracking-[-0.007em] text-brand-800 lg:mt-5">
					<?php upskill_icon( 'completed-check', 'size-[11px]' ); ?>
					<?php esc_html_e( 'Course Completed', 'upskill-me' ); ?>
				</p>
				<div class="flex flex-col items-start gap-2 pt-6 lg:flex-row lg:pt-14">
					<?php
					upskill_button(
						array(
							'url'   => $upskill_back ? $upskill_back : home_url( '/' ),
							'title' => __( 'Back to my courses', 'upskill-me' ),
						),
						array( 'class' => 'min-h-[50px]' )
					);

					if ( $upskill_course['certificate'] ) {
						upskill_button(
							array(
								'url'    => $upskill_course['certificate'],
								'title'  => __( 'Print Certificate', 'upskill-me' ),
								'target' => '_blank',
							),
							array(
								'variant' => 'light',
								'icon'    => 'printer',
								'class'   => 'min-h-[50px]',
							)
						);
					}
					?>
				</div>
			</div>
		</section>

		<section class="container pb-12 lg:pt-16 lg:pb-20" aria-labelledby="summary-heading">
			<div class="flex flex-col items-start gap-4 lg:items-center lg:text-center">
				<?php upskill_eyebrow( __( 'Training completed.', 'upskill-me' ), 'course-completion' ); ?>
				<h2 id="summary-heading" class="heading text-2xl leading-[1.1] font-medium tracking-[-0.01em] text-gray-950 lg:text-[2.25rem]"><?php esc_html_e( 'Your learning progress.', 'upskill-me' ); ?></h2>
				<p class="font-secondary text-[0.9375rem] leading-[1.62] tracking-[-0.007em] text-[#55555a] lg:hidden"><?php esc_html_e( 'Your selected training and cohort details are shown below.', 'upskill-me' ); ?></p>
			</div>
			<dl class="mt-6 grid grid-cols-2 overflow-hidden rounded-lg border border-gray-100 bg-white shadow-card lg:mt-8 lg:grid-cols-4 lg:rounded-2xl">
				<?php foreach ( array_keys( $upskill_details ) as $upskill_i => $upskill_label ) : ?>
					<div class="flex flex-col gap-1.5 p-5 lg:gap-4 <?php echo esc_attr( ( $upskill_i % 2 ? 'border-l ' : '' ) . ( $upskill_i > 1 ? 'border-t lg:border-t-0 ' : '' ) . ( 2 === $upskill_i ? 'lg:border-l ' : '' ) ); ?>border-gray-900/10 lg:pb-8">
						<dt class="text-sm text-gray-500 lg:font-medium lg:uppercase"><?php echo esc_html( $upskill_label ); ?></dt>
						<dd class="text-base font-medium text-gray-900 lg:pt-[5px] lg:text-2xl lg:leading-[1.1] lg:tracking-[-0.01em]"><?php echo esc_html( $upskill_details[ $upskill_label ] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>

		<?php $upskill_next = function_exists( 'upskill_library_next' ) ? upskill_library_next( $upskill_course['id'], $upskill_user->ID ) : array(); ?>
		<?php if ( $upskill_next ) : ?>
			<section class="bg-white py-12 lg:py-20" aria-labelledby="next-heading">
				<div class="container">
					<header class="section-header section-header--split">
						<div class="section-header__title">
							<?php upskill_eyebrow( __( 'Keep building your knowledge', 'upskill-me' ), 'search-list' ); ?>
							<h2 id="next-heading" class="heading section-header__heading text-h3"><?php echo wp_kses( __( 'What’s <em>next?</em>', 'upskill-me' ), upskill_inline_kses() ); ?></h2>
						</div>
						<div class="section-header__aside">
							<p class="section-header__intro"><?php esc_html_e( 'Continue your learning with another practical On The Job training session.', 'upskill-me' ); ?></p>
						</div>
					</header>
					<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:mt-12 lg:grid-cols-3">
						<?php
						foreach ( $upskill_next as $upskill_id ) {
							get_template_part( 'template-parts/cards/training', null, array( 'card' => upskill_library_card( $upskill_id ) ) );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</main>
<?php
get_footer();
