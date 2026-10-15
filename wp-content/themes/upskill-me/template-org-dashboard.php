<?php
/**
 * Template Name: Organisation dashboard
 *
 * The group leader's overview. Figma: Org dashboard 49325:16970 (1440) /
 * 49374:53079 (390), with the seat cards of "Courses/Subs" 49325:17490.
 *
 * Four counts, the organisation's training (one row per group: seats, used,
 * remaining, completion) and the team's progress. Read-only, from LearnDash and
 * Group Registration (inc/lms-organisation.php); "Enrol" and "Manage team" open
 * Group Registration's own groups dashboard on that group. Only group leaders
 * and administrators reach it.
 *
 * @package upskill-me
 */

get_header();

$upskill_user    = wp_get_current_user();
$upskill_groups  = array_map( 'upskill_org_group_summary', upskill_org_groups( $upskill_user->ID ) );
$upskill_rows    = array_merge( array(), ...array_map( static fn( $g ) => $g['rows'], $upskill_groups ?: array( array( 'rows' => array() ) ) ) );
$upskill_members = array_unique( array_merge( array(), ...array_map( static fn( $g ) => $g['members'], $upskill_groups ?: array( array( 'members' => array() ) ) ) ) );
$upskill_status  = array_count_values( wp_list_pluck( $upskill_rows, 'status' ) );
$upskill_manage  = upskill_groups_dashboard_url();
$upskill_name    = $upskill_user->first_name ? $upskill_user->first_name : $upskill_user->display_name;
$upskill_h2      = 'heading text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950';
$upskill_labels  = array(
	'completed'   => array( __( 'Completed', 'upskill-me' ), 'bg-[#e7f6d8] text-[#3f6212]' ),
	'in_progress' => array( __( 'In progress', 'upskill-me' ), 'bg-brand-100 text-brand-700' ),
	'not_started' => array( __( 'Not started', 'upskill-me' ), 'bg-gray-50 text-gray-500' ),
);

usort( $upskill_rows, static fn( $a, $b ) => $b['activity'] <=> $a['activity'] );

$upskill_counts = array(
	array( __( 'Active training', 'upskill-me' ), count( $upskill_groups ), __( 'Training packages currently available to your organisation.', 'upskill-me' ) ),
	array( __( 'Team members', 'upskill-me' ), count( $upskill_members ), __( 'People enrolled in your training.', 'upskill-me' ) ),
	array( __( 'In progress', 'upskill-me' ), $upskill_status['in_progress'] ?? 0, __( 'Courses your team is working through.', 'upskill-me' ) ),
	array( __( 'Completed', 'upskill-me' ), $upskill_status['completed'] ?? 0, __( 'Courses completed by your team.', 'upskill-me' ) ),
);

// "Enrol" / "Manage team" for one group: Group Registration reads the group from a POSTed wdm_group_id.
$upskill_group_button = static function ( $group_id, $label, $primary = false ) use ( $upskill_manage ) {
	?>
	<form action="<?php echo esc_url( $upskill_manage ); ?>" method="post">
		<input type="hidden" name="wdm_group_id" value="<?php echo esc_attr( $group_id ); ?>">
		<button class="btn <?php echo $primary ? 'btn--primary' : 'btn--light'; ?> btn--sm" type="submit"><span class="btn__label"><?php echo esc_html( $label ); ?></span><span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span></button>
	</form>
	<?php
};
?>
<main id="primary" class="site-main bg-gray-25">
	<section class="container pt-6 pb-12 lg:pt-14 lg:pb-16" aria-labelledby="org-heading">
		<div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
			<div class="flex flex-col items-start gap-4">
				<?php upskill_eyebrow( __( 'Account overview', 'upskill-me' ), 'building' ); ?>
				<h1 id="org-heading" class="text-h3 font-medium text-gray-950">
					<?php
					/* translators: %s: first name. */
					echo esc_html( sprintf( __( 'Welcome back, %s.', 'upskill-me' ), $upskill_name ) );
					?>
				</h1>
			</div>
			<p class="max-w-[400px] text-base text-gray-500"><?php esc_html_e( 'See your training, team enrolments and progress at a glance.', 'upskill-me' ); ?></p>
		</div>

		<ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:mt-12 lg:grid-cols-4">
			<?php foreach ( $upskill_counts as $upskill_count ) : ?>
				<li class="flex flex-col rounded-[22px] border border-line bg-white p-6">
					<span class="text-sm font-medium text-gray-500"><?php echo esc_html( $upskill_count[0] ); ?></span>
					<span class="pt-2 text-[2.5rem] leading-none font-medium text-gray-950"><?php echo esc_html( number_format_i18n( $upskill_count[1] ) ); ?></span>
					<span class="pt-3 text-sm text-gray-500"><?php echo esc_html( $upskill_count[2] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="bg-white py-12 lg:py-20" aria-labelledby="org-training">
		<div class="container">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
				<div class="flex flex-col items-start gap-4">
					<?php upskill_eyebrow( __( 'Active training', 'upskill-me' ), 'video-book' ); ?>
					<h2 id="org-training" class="<?php echo esc_attr( $upskill_h2 ); ?>"><?php esc_html_e( 'Your training and seats.', 'upskill-me' ); ?></h2>
				</div>
				<?php upskill_button( array( 'url' => function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : home_url( '/courses/' ), 'title' => __( 'Buy More Training', 'upskill-me' ) ), array( 'variant' => 'light', 'class' => 'self-start' ) ); ?>
			</div>

			<?php if ( $upskill_groups ) : ?>
				<ul class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( $upskill_groups as $upskill_group ) : ?>
						<?php
						$upskill_course   = $upskill_group['courses'] ? $upskill_group['courses'][0] : 0;
						$upskill_category = $upskill_course ? get_the_terms( $upskill_course, 'training_category' ) : null;
						$upskill_used_pc  = $upskill_group['seats'] ? (int) round( 100 * $upskill_group['used'] / $upskill_group['seats'] ) : 0;
						?>
						<li class="flex flex-col rounded-[22px] border border-line bg-white p-6">
							<div class="flex items-start justify-between gap-3">
								<p class="text-sm text-gray-500"><?php echo esc_html( is_array( $upskill_category ) ? $upskill_category[0]->name : __( 'Training', 'upskill-me' ) ); ?></p>
								<span class="rounded-full bg-[#e7f6d8] px-2.5 py-0.5 text-xs font-medium text-[#3f6212]"><?php esc_html_e( 'Active', 'upskill-me' ); ?></span>
							</div>
							<h3 class="pt-2 text-lg font-medium text-gray-950"><?php echo esc_html( $upskill_group['title'] ); ?></h3>
							<?php if ( count( $upskill_group['courses'] ) > 1 ) : ?>
								<p class="pt-1 text-sm text-gray-500">
									<?php
									/* translators: %d: number of courses. */
									echo esc_html( sprintf( _n( '%d course', '%d courses', count( $upskill_group['courses'] ), 'upskill-me' ), count( $upskill_group['courses'] ) ) );
									?>
								</p>
							<?php endif; ?>
							<dl class="mt-5 grid grid-cols-3 gap-2 border-y border-line py-4 text-center">
								<div><dt class="text-xs text-gray-500"><?php esc_html_e( 'Total seats', 'upskill-me' ); ?></dt><dd class="pt-1 text-xl font-medium text-gray-950"><?php echo $upskill_group['unlimited'] ? '∞' : esc_html( $upskill_group['seats'] ); ?></dd></div>
								<div><dt class="text-xs text-gray-500"><?php esc_html_e( 'Enrolled', 'upskill-me' ); ?></dt><dd class="pt-1 text-xl font-medium text-gray-950"><?php echo esc_html( $upskill_group['used'] ); ?></dd></div>
								<div><dt class="text-xs text-gray-500"><?php esc_html_e( 'Remaining', 'upskill-me' ); ?></dt><dd class="pt-1 text-xl font-medium text-gray-950"><?php echo $upskill_group['unlimited'] ? '∞' : esc_html( $upskill_group['remaining'] ); ?></dd></div>
							</dl>
							<div class="flex flex-col gap-3 pt-4">
								<?php foreach ( array( array( __( 'Seat usage', 'upskill-me' ), $upskill_used_pc, sprintf( /* translators: 1: used, 2: total. */ __( '%1$d of %2$d', 'upskill-me' ), $upskill_group['used'], $upskill_group['seats'] ) ), array( __( 'Completion', 'upskill-me' ), $upskill_group['completion'], $upskill_group['completion'] . '%' ) ) as $upskill_bar ) : ?>
									<div>
										<p class="flex justify-between text-sm"><span class="text-gray-500"><?php echo esc_html( $upskill_bar[0] ); ?></span><span class="font-medium text-gray-950"><?php echo esc_html( $upskill_bar[2] ); ?></span></p>
										<span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-gray-50" role="presentation"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) min( 100, $upskill_bar[1] ); ?>%"></span></span>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="mt-auto flex flex-wrap gap-2 pt-6">
								<?php $upskill_group_button( $upskill_group['id'], __( 'Enrol', 'upskill-me' ), true ); ?>
								<?php if ( $upskill_course ) : ?>
									<a class="btn btn--light btn--sm" href="<?php echo esc_url( get_permalink( $upskill_course ) ); ?>"><span class="btn__label"><?php esc_html_e( 'View Course', 'upskill-me' ); ?></span><span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span></a>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="mt-8 flex flex-col items-start gap-4 rounded-[22px] border border-line bg-gray-25 p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'Your organisation has no training yet. Choose training and seats to get started.', 'upskill-me' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="py-12 lg:py-20" aria-labelledby="org-team">
		<div class="container">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
				<div class="flex flex-col items-start gap-4">
					<?php upskill_eyebrow( __( 'Team enrolment', 'upskill-me' ), 'users' ); ?>
					<h2 id="org-team" class="<?php echo esc_attr( $upskill_h2 ); ?>"><?php esc_html_e( 'See who’s learning.', 'upskill-me' ); ?></h2>
				</div>
				<?php upskill_button( array( 'url' => $upskill_manage, 'title' => __( 'Manage Team', 'upskill-me' ) ), array( 'class' => 'self-start' ) ); ?>
			</div>

			<?php if ( $upskill_rows ) : ?>
				<div class="mt-8 overflow-x-auto rounded-[22px] border border-line bg-white">
					<table class="w-full min-w-[720px] text-left text-sm">
						<thead class="text-gray-500">
							<tr>
								<th class="px-5 py-4 font-medium" scope="col"><?php esc_html_e( 'Name', 'upskill-me' ); ?></th>
								<th class="px-5 py-4 font-medium" scope="col"><?php esc_html_e( 'Course', 'upskill-me' ); ?></th>
								<th class="px-5 py-4 font-medium" scope="col"><?php esc_html_e( 'Progress', 'upskill-me' ); ?></th>
								<th class="px-5 py-4 font-medium" scope="col"><?php esc_html_e( 'Status', 'upskill-me' ); ?></th>
								<th class="px-5 py-4 font-medium" scope="col"><?php esc_html_e( 'Last activity', 'upskill-me' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $upskill_rows, 0, 50 ) as $upskill_row ) : ?>
								<?php $upskill_member = get_userdata( $upskill_row['user'] ); ?>
								<tr class="border-t border-line">
									<td class="px-5 py-4">
										<span class="block font-medium text-gray-950"><?php echo esc_html( $upskill_member ? $upskill_member->display_name : '' ); ?></span>
										<span class="text-gray-500"><?php echo esc_html( $upskill_member ? $upskill_member->user_email : '' ); ?></span>
									</td>
									<td class="px-5 py-4 text-gray-950"><?php echo esc_html( get_the_title( $upskill_row['course'] ) ); ?></td>
									<td class="px-5 py-4">
										<span class="flex items-center gap-2"><span class="block h-1.5 w-20 overflow-hidden rounded-full bg-gray-50"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) $upskill_row['percent']; ?>%"></span></span><?php echo esc_html( $upskill_row['percent'] . '%' ); ?></span>
									</td>
									<td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium <?php echo esc_attr( $upskill_labels[ $upskill_row['status'] ][1] ); ?>"><?php echo esc_html( $upskill_labels[ $upskill_row['status'] ][0] ); ?></span></td>
									<td class="px-5 py-4 text-gray-500"><?php echo $upskill_row['activity'] ? esc_html( wp_date( 'd M Y', $upskill_row['activity'] ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="mt-8 flex flex-col items-start gap-4 rounded-[22px] border border-line bg-white p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'No one is enrolled yet. Add your people to start their training.', 'upskill-me' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
