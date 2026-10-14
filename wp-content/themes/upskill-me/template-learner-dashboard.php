<?php
/**
 * Template Name: Learner dashboard
 *
 * "My learning": the signed-in learner's courses and account links. Figma:
 * "User Account Dashboard" 49325:18382 (1440) / 49374:112689 (390).
 *
 * Welcome, three counts, current courses with progress, completed courses
 * and account settings. All course data is LearnDash's
 * (inc/lms-learner.php); visitors are sent to the Log in page.
 *
 * @package upskill-me
 */

get_header();

$upskill_user    = wp_get_current_user();
$upskill_courses = upskill_learner_courses( $upskill_user->ID );
$upskill_name    = $upskill_user->first_name ? $upskill_user->first_name : $upskill_user->display_name;
$upskill_shown   = 3; // Completed courses listed before "View All Completed Courses".
$upskill_all_url = upskill_learndash_profile_url(); // LearnDash's profile lists every course.
$upskill_profile = get_edit_profile_url( $upskill_user->ID );

// Section headings: 24px on phones to 36px at 1440 (Figma).
$upskill_h2 = 'heading text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950';

$upskill_counts = array(
	array(
		'label'  => __( 'In progress', 'upskill-me' ),
		'value'  => $upskill_courses['started'],
		'text'   => __( 'Courses you have started and not yet finished.', 'upskill-me' ),
		'anchor' => '#current-courses',
	),
	array(
		'label'  => __( 'Completed', 'upskill-me' ),
		'value'  => count( $upskill_courses['completed'] ),
		'text'   => __( 'Courses you have finished so far.', 'upskill-me' ),
		'anchor' => '#completed-courses',
	),
	array(
		'label'  => __( 'Total courses', 'upskill-me' ),
		'value'  => $upskill_courses['total'],
		'text'   => __( 'All the training currently assigned to you.', 'upskill-me' ),
		'anchor' => '#current-courses',
	),
);

$upskill_settings = array(
	array(
		'icon'  => 'account-profile',
		'title' => __( 'Profile', 'upskill-me' ),
		'text'  => __( 'Update your name and account information.', 'upskill-me' ),
		'label' => __( 'Edit', 'upskill-me' ),
		'url'   => $upskill_profile,
	),
	array(
		'icon'  => 'account-password',
		'title' => __( 'Password & Security', 'upskill-me' ),
		'text'  => __( 'Manage your password and account security.', 'upskill-me' ),
		'label' => __( 'Manage', 'upskill-me' ),
		'url'   => $upskill_profile . '#password',
	),
);
?>
<main id="primary" class="site-main bg-gray-25">
	<section class="container pt-6 pb-6 lg:pt-14 lg:pb-12" aria-labelledby="dashboard-heading">
		<div class="flex flex-col gap-6">
			<div class="flex flex-col gap-3">
				<?php upskill_eyebrow( __( 'My learning', 'upskill-me' ), 'my-learning', 'self-start' ); ?>
				<h1 id="dashboard-heading" class="heading text-h1 font-medium text-gray-900 lg:font-semibold">
					<?php
					/* translators: %s: the learner's first name. */
					printf( esc_html__( 'Welcome back, %s', 'upskill-me' ), '<br><em>' . esc_html( $upskill_name ) . '.</em>' );
					?>
				</h1>
			</div>
			<p class="text-lead text-gray-500"><?php esc_html_e( 'Continue your training, pick up where you left off and keep building your knowledge.', 'upskill-me' ); ?></p>
		</div>

		<ul class="mt-6 grid grid-cols-2 gap-2 lg:mt-20 lg:grid-cols-3 lg:gap-4" role="list">
			<?php foreach ( $upskill_counts as $upskill_i => $upskill_count ) : ?>
				<li class="<?php echo 2 === $upskill_i ? 'col-span-2 lg:col-span-1' : ''; ?> flex flex-col rounded-lg border border-gray-900/10 bg-white px-3 py-5 shadow-card lg:p-5">
					<p class="text-sm font-medium text-gray-500 uppercase"><?php echo esc_html( $upskill_count['label'] ); ?></p>
					<p class="pt-3 pb-2 text-[2.25rem] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950"><?php echo esc_html( number_format_i18n( $upskill_count['value'] ) ); ?></p>
					<p class="text-sm text-gray-500 lg:min-h-[43px]"><?php echo esc_html( $upskill_count['text'] ); ?></p>
					<a class="group mt-auto inline-flex items-center gap-[7px] self-start pt-4 text-sm font-medium tracking-[-0.007em] text-brand-700" href="<?php echo esc_attr( $upskill_count['anchor'] ); ?>">
						<?php esc_html_e( 'Continue Learning', 'upskill-me' ); ?>
						<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section id="current-courses" class="container scroll-mt-28 py-[46px] lg:py-12" aria-labelledby="current-courses-heading">
		<div class="flex flex-col gap-3 lg:gap-4">
			<?php upskill_eyebrow( __( 'Current Courses', 'upskill-me' ), 'current-courses', 'self-start' ); ?>
			<h2 id="current-courses-heading" class="<?php echo esc_attr( $upskill_h2 ); ?>"><?php esc_html_e( 'Keep learning.', 'upskill-me' ); ?></h2>
		</div>

		<?php if ( $upskill_courses['current'] ) : ?>
			<div class="mt-6 grid gap-5 lg:grid-cols-2">
				<?php
				foreach ( $upskill_courses['current'] as $upskill_course ) {
					get_template_part( 'template-parts/dashboard/course-card', null, array( 'course' => $upskill_course ) );
				}
				?>
			</div>
		<?php else : ?>
			<div class="mt-6 flex flex-col items-start gap-5 rounded-2xl border border-gray-900/10 bg-white p-6">
				<p class="text-base text-gray-700">
					<?php echo $upskill_courses['completed'] ? esc_html__( 'You have finished all your training. New courses will appear here when they are assigned to you.', 'upskill-me' ) : esc_html__( 'You have no training yet. Courses appear here once they are assigned to you or bought.', 'upskill-me' ); ?>
				</p>
				<?php
				upskill_button(
					array(
						'url'   => home_url( '/courses/' ),
						'title' => __( 'Find Training', 'upskill-me' ),
					),
					array( 'class' => 'text-sm' )
				);
				?>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( $upskill_courses['completed'] ) : ?>
		<section id="completed-courses" class="scroll-mt-28 bg-brand-50 py-12" aria-labelledby="completed-courses-heading">
			<div class="container">
				<div class="flex flex-col gap-3 lg:gap-4">
					<?php upskill_eyebrow( __( 'Course completion', 'upskill-me' ), 'course-completion', 'self-start' ); ?>
					<h2 id="completed-courses-heading" class="<?php echo esc_attr( $upskill_h2 ); ?>"><?php esc_html_e( 'Your learning progress.', 'upskill-me' ); ?></h2>
				</div>
				<div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
					<?php
					foreach ( array_slice( $upskill_courses['completed'], 0, $upskill_shown ) as $upskill_course ) {
						get_template_part( 'template-parts/dashboard/completed-card', null, array( 'course' => $upskill_course ) );
					}
					?>
				</div>
				<?php if ( count( $upskill_courses['completed'] ) > $upskill_shown && $upskill_all_url ) : ?>
					<div class="mt-6">
						<?php
						upskill_button(
							array(
								'url'   => $upskill_all_url,
								'title' => __( 'View All Completed Courses', 'upskill-me' ),
							),
							array(
								'variant' => 'light',
								'class'   => 'min-h-12 border-gray-900/20 bg-transparent text-sm shadow-none',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="container py-12" aria-labelledby="account-heading">
		<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between sm:gap-10">
			<div class="flex flex-col gap-3 lg:gap-4">
				<?php upskill_eyebrow( __( 'Account settings', 'upskill-me' ), 'account-settings', 'self-start' ); ?>
				<h2 id="account-heading" class="<?php echo esc_attr( $upskill_h2 ); ?>"><?php esc_html_e( 'Manage your account.', 'upskill-me' ); ?></h2>
			</div>
			<?php
			upskill_button(
				array(
					'url'   => $upskill_profile,
					'title' => __( 'Manage Settings', 'upskill-me' ),
				),
				array( 'class' => 'min-h-12 self-start text-sm sm:self-auto' )
			);
			?>
		</div>

		<ul class="mt-6 overflow-hidden rounded-lg border border-gray-900/10 bg-white" role="list">
			<?php foreach ( $upskill_settings as $upskill_row ) : ?>
				<li class="border-b border-gray-900/10 last:border-b-0">
					<a class="group flex items-center gap-5 p-5 transition-colors hover:bg-brand-25" href="<?php echo esc_url( $upskill_row['url'] ); ?>">
						<span class="grid size-9 flex-none place-items-center rounded-[10px] bg-gray-25 text-gray-800" aria-hidden="true"><?php upskill_icon( $upskill_row['icon'], 'size-[17px]' ); ?></span>
						<span class="flex min-w-0 flex-1 flex-col">
							<span class="text-base font-medium tracking-[-0.006em] text-gray-900"><?php echo esc_html( $upskill_row['title'] ); ?></span>
							<span class="pt-0.5 text-sm tracking-[-0.007em] text-[#6b6b72]"><?php echo esc_html( $upskill_row['text'] ); ?></span>
						</span>
						<span class="inline-flex flex-none items-center gap-[7px] text-sm font-medium tracking-[-0.007em] text-brand-700">
							<?php echo esc_html( $upskill_row['label'] ); ?>
							<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
</main>
<?php
get_footer();
