<?php
/**
 * Template Name: Organisation groups
 *
 * The coordinator's own groups (call 13 Nov, item 5; inc/lms-org-groups.php),
 * under the organisation dashboard. No Figma: built from the dashboard's own
 * parts (section heads, stat cards, tables, badges).
 *
 * - List: "Create group" and one row per group (people, seats used,
 *   completion, completed, in progress).
 * - One group (?group=ID): its figures, its people (levels, progress) with
 *   Remove, "Add people" (anyone the coordinator has enrolled, any level or
 *   purchase), Rename and Delete.
 *
 * Seats and enrolment are unchanged (Group Registration's groups dashboard);
 * a group only gathers people already enrolled. Only group leaders and
 * administrators reach it (upskill_org_dashboard_guard()).
 *
 * @package upskill-me
 */

get_header();

$upskill_user    = get_current_user_id();
$upskill_urls    = upskill_org_urls();
$upskill_people  = upskill_org_people( $upskill_user );
$upskill_team_id = isset( $_GET['group'] ) ? absint( $_GET['group'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view.
$upskill_team    = $upskill_team_id ? upskill_org_team( $upskill_team_id, $upskill_user ) : null;
$upskill_notice  = isset( $_GET['notice'] ) ? upskill_org_team_notice( sanitize_key( wp_unslash( $_GET['notice'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
$upskill_bad     = isset( $_GET['notice'] ) && in_array( $_GET['notice'], array( 'name', 'none' ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$upskill_action  = admin_url( 'admin-post.php' );
$upskill_th      = 'uppercase border-b border-gray-900/10 bg-gray-25 px-5 py-4 text-sm font-medium text-[#666666]';
$upskill_td      = 'px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:flex max-md:items-center max-md:justify-between max-md:gap-4 max-md:px-0 max-md:py-1.5 max-md:text-right max-md:before:shrink-0 max-md:before:text-left max-md:before:text-sm max-md:before:text-gray-500 max-md:before:content-[attr(data-label)]';
$upskill_field   = 'h-[50px] w-full min-w-0 rounded-[8px] border border-gray-900/20 bg-white px-4 text-base text-gray-950 placeholder:text-[#999] focus:border-brand-700 focus:shadow-[0_0_0_3px_rgb(109_66_175/14%)] focus:outline-none';
$upskill_bar     = static function ( $percent ) {
	?>
	<span class="inline-flex items-center gap-2.5">
		<span class="block h-1.5 w-[133px] overflow-hidden rounded-full bg-[#ededef] max-md:w-20" role="presentation"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) min( 100, $percent ); ?>%"></span></span>
		<?php echo esc_html( $percent . '%' ); ?>
	</span>
	<?php
};
?>
<main id="primary" class="site-main org-page bg-gray-25">
	<section class="container pt-8 pb-10 lg:pt-14 lg:pb-12" aria-labelledby="org-groups-heading">
		<?php if ( $upskill_urls['dashboard'] ) : ?>
			<a class="inline-flex items-center gap-2 pb-5 text-sm font-medium text-brand-700 hover:underline" href="<?php echo esc_url( $upskill_team ? $upskill_urls['groups'] : $upskill_urls['dashboard'] ); ?>">
				<span class="rotate-180" aria-hidden="true"><?php upskill_icon( 'arrow-right', 'size-3' ); ?></span>
				<?php echo esc_html( $upskill_team ? __( 'All groups', 'upskill-me' ) : __( 'Back to dashboard', 'upskill-me' ) ); ?>
			</a>
		<?php endif; ?>
		<div class="flex flex-col items-start gap-3">
			<?php upskill_eyebrow( $upskill_team ? __( 'Group', 'upskill-me' ) : __( 'Groups', 'upskill-me' ), 'users' ); ?>
			<h1 id="org-groups-heading" class="heading text-h1 font-medium text-gray-950 lg:font-semibold">
				<?php
				if ( $upskill_team ) {
					echo esc_html( $upskill_team['title'] );
				} else {
					echo wp_kses( __( 'Organise your <em>people.</em>', 'upskill-me' ), array( 'em' => array() ) );
				}
				?>
			</h1>
		</div>
		<p class="pt-5 text-base leading-[1.5] text-[#666] lg:pt-6 lg:text-lead">
			<?php
			echo esc_html(
				$upskill_team
					? __( 'See this group’s progress, and add or remove people. Their training and seats stay as they are.', 'upskill-me' )
					: __( 'Make a group for each branch, site or team, and add the people you have enrolled, at any level. Seats from one purchase can be shared across groups.', 'upskill-me' )
			);
			?>
		</p>

		<?php if ( $upskill_notice ) : ?>
			<p class="mt-6 rounded-lg border px-3.5 py-3 text-sm <?php echo esc_attr( $upskill_bad ? 'border-[#fecdca] bg-[#fef3f2] text-[#b42318]' : 'border-line bg-brand-50 text-gray-800' ); ?>" role="<?php echo esc_attr( $upskill_bad ? 'alert' : 'status' ); ?>"><?php echo esc_html( $upskill_notice ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( $upskill_team ) : ?>
		<?php // ---------------- One group ---------------- ?>
		<section class="container pb-12" aria-label="<?php esc_attr_e( 'Group figures', 'upskill-me' ); ?>">
			<?php
			get_template_part(
				'template-parts/organisation/stat-cards',
				null,
				array(
					'items' => array(
						array(
							'label' => __( 'People', 'upskill-me' ),
							'value' => $upskill_team['people'],
							'text'  => __( 'Seats this group is using.', 'upskill-me' ),
						),
						array(
							'label' => __( 'In progress', 'upskill-me' ),
							'value' => $upskill_team['in_progress'],
							'text'  => __( 'Courses its people are working through.', 'upskill-me' ),
						),
						array(
							'label' => __( 'Completed', 'upskill-me' ),
							'value' => $upskill_team['completed'],
							'text'  => __( 'Courses its people have completed.', 'upskill-me' ),
						),
						array(
							'label' => __( 'Not started', 'upskill-me' ),
							'value' => $upskill_team['not_started'],
							'text'  => __( 'Courses its people haven’t started yet.', 'upskill-me' ),
						),
					),
				)
			);
			?>
		</section>

		<section class="bg-white py-12" aria-labelledby="org-group-people">
			<div class="container">
				<?php
				get_template_part(
					'template-parts/organisation/section-head',
					null,
					array(
						'eyebrow' => __( 'People', 'upskill-me' ),
						'icon'    => 'users',
						'heading' => __( 'People in this group.', 'upskill-me' ),
						'id'      => 'org-group-people',
						'intro'   => __( 'Their courses and progress. Removing someone from the group keeps their training and seat.', 'upskill-me' ),
					)
				);
				?>

				<?php if ( $upskill_team['members'] ) : ?>
					<div class="mt-6 overflow-hidden rounded-lg border border-gray-900/10 bg-white">
						<table class="w-full border-collapse text-left text-base text-gray-950 max-md:block">
							<thead class="max-md:hidden">
								<tr>
									<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Name', 'upskill-me' ); ?></th>
									<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Level', 'upskill-me' ); ?></th>
									<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Courses', 'upskill-me' ); ?></th>
									<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Progress', 'upskill-me' ); ?></th>
									<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Status', 'upskill-me' ); ?></th>
									<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'upskill-me' ); ?></span></th>
								</tr>
							</thead>
							<tbody class="max-md:block">
								<?php foreach ( $upskill_team['members'] as $upskill_member ) : ?>
									<?php
									$upskill_person = $upskill_people[ $upskill_member ];
									$upskill_mine   = array_values( array_filter( $upskill_team['rows'], static fn( $row ) => (int) $row['user'] === $upskill_member ) );
									$upskill_pc     = $upskill_mine ? (int) round( array_sum( wp_list_pluck( $upskill_mine, 'percent' ) ) / count( $upskill_mine ) ) : 0;
									$upskill_states = wp_list_pluck( $upskill_mine, 'status' );
									$upskill_state  = $upskill_states && ! array_diff( $upskill_states, array( 'completed' ) ) ? 'completed' : ( array_diff( $upskill_states, array( 'not_started' ) ) ? 'in_progress' : 'not_started' );
									?>
									<tr class="max-md:block border-t border-gray-900/10 max-md:p-4 first:border-t-0">
										<th class="px-5 py-5 text-left align-middle font-normal md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-0 max-md:pb-3" scope="row">
											<span class="block font-medium"><?php echo esc_html( $upskill_person['name'] ); ?></span>
											<span class="block pt-1 text-sm text-[#666] break-all"><?php echo esc_html( $upskill_person['email'] ); ?></span>
										</th>
										<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Level', 'upskill-me' ); ?>"><?php echo esc_html( $upskill_person['levels'] ? implode( ', ', $upskill_person['levels'] ) : '—' ); ?></td>
										<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Courses', 'upskill-me' ); ?>"><?php echo esc_html( number_format_i18n( count( $upskill_mine ) ) ); ?></td>
										<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Progress', 'upskill-me' ); ?>"><?php $upskill_bar( $upskill_pc ); ?></td>
										<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Status', 'upskill-me' ); ?>"><?php upskill_org_status_badge( $upskill_state ); ?></td>
										<td class="px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-3 max-md:pb-0">
											<form action="<?php echo esc_url( $upskill_action ); ?>" method="post" data-org-confirm="<?php echo esc_attr( sprintf( /* translators: %s: person's name. */ __( 'Remove %s from this group? They keep their training and seat.', 'upskill-me' ), $upskill_person['name'] ) ); ?>">
												<?php upskill_org_team_fields( 'remove', $upskill_team['id'] ); ?>
												<input type="hidden" name="person" value="<?php echo esc_attr( $upskill_member ); ?>">
												<button class="cursor-pointer text-sm font-medium whitespace-nowrap text-[#b42318] hover:underline" type="submit"><?php esc_html_e( 'Remove', 'upskill-me' ); ?><span class="screen-reader-text"> <?php echo esc_html( $upskill_person['name'] ); ?></span></button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<div class="mt-6 rounded-2xl border border-gray-900/10 bg-gray-25 p-6">
						<p class="text-base text-gray-700"><?php esc_html_e( 'No one is in this group yet. Add people below.', 'upskill-me' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="py-12" aria-labelledby="org-group-add">
			<div class="container grid gap-10 lg:grid-cols-[minmax(0,1fr)_400px]">
				<div>
					<?php
					get_template_part(
						'template-parts/organisation/section-head',
						null,
						array(
							'eyebrow' => __( 'Add people', 'upskill-me' ),
							'icon'    => 'users',
							'heading' => __( 'Add to this group.', 'upskill-me' ),
							'id'      => 'org-group-add',
						)
					);
					$upskill_free = array_diff_key( $upskill_people, array_flip( $upskill_team['members'] ) );
					?>
					<?php if ( $upskill_free ) : ?>
						<form class="mt-6 flex flex-col gap-4" action="<?php echo esc_url( $upskill_action ); ?>" method="post" data-org-people>
							<?php upskill_org_team_fields( 'add', $upskill_team['id'] ); ?>
							<label class="sr-only" for="org-people-search"><?php esc_html_e( 'Search people', 'upskill-me' ); ?></label>
							<input class="<?php echo esc_attr( $upskill_field ); ?>" id="org-people-search" type="search" placeholder="<?php esc_attr_e( 'Search by name or email', 'upskill-me' ); ?>" data-org-people-search>
							<fieldset>
								<legend class="sr-only"><?php esc_html_e( 'People you have enrolled', 'upskill-me' ); ?></legend>
								<ul class="max-h-[360px] overflow-y-auto rounded-lg border border-gray-900/10 bg-white" role="list">
									<?php foreach ( $upskill_free as $upskill_person ) : ?>
										<li class="border-b border-gray-900/10 last:border-b-0" data-org-person="<?php echo esc_attr( strtolower( $upskill_person['name'] . ' ' . $upskill_person['email'] ) ); ?>">
											<label class="flex cursor-pointer items-center gap-3 px-4 py-3 hover:bg-brand-25">
												<input class="size-4 flex-none accent-brand-700" type="checkbox" name="people[]" value="<?php echo esc_attr( $upskill_person['id'] ); ?>">
												<span class="flex min-w-0 flex-1 flex-col">
													<span class="text-base font-medium text-gray-950"><?php echo esc_html( $upskill_person['name'] ); ?></span>
													<span class="text-sm text-[#666] break-all"><?php echo esc_html( $upskill_person['email'] ); ?></span>
												</span>
												<span class="flex-none text-sm text-[#666]"><?php echo esc_html( implode( ', ', $upskill_person['levels'] ) ); ?></span>
											</label>
										</li>
									<?php endforeach; ?>
								</ul>
							</fieldset>
							<button class="btn btn--primary btn--md self-start" type="submit">
								<span class="btn__label"><?php esc_html_e( 'Add to Group', 'upskill-me' ); ?></span>
								<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
							</button>
						</form>
					<?php else : ?>
						<div class="mt-6 flex flex-col items-start gap-4 rounded-2xl border border-gray-900/10 bg-white p-6">
							<p class="text-base text-gray-700"><?php echo esc_html( $upskill_people ? __( 'Everyone you have enrolled is already in this group.', 'upskill-me' ) : __( 'Enrol people in your training first, then add them here.', 'upskill-me' ) ); ?></p>
							<?php
							if ( $upskill_urls['manage'] ) {
								upskill_button(
									array(
										'url'   => $upskill_urls['manage'],
										'title' => __( 'Enrol Team Members', 'upskill-me' ),
									),
									array( 'variant' => 'light' )
								);
							}
							?>
						</div>
					<?php endif; ?>
				</div>

				<div class="flex flex-col gap-6 rounded-[24px] border border-gray-900/10 bg-white p-6 lg:self-start">
					<form class="flex flex-col gap-3" action="<?php echo esc_url( $upskill_action ); ?>" method="post">
						<?php upskill_org_team_fields( 'rename', $upskill_team['id'] ); ?>
						<label class="text-sm font-medium text-gray-900" for="org-group-name"><?php esc_html_e( 'Group name', 'upskill-me' ); ?></label>
						<input class="<?php echo esc_attr( $upskill_field ); ?>" id="org-group-name" type="text" name="name" value="<?php echo esc_attr( $upskill_team['title'] ); ?>" maxlength="100" required>
						<button class="btn btn--light btn--sm self-start" type="submit"><span class="btn__label"><?php esc_html_e( 'Rename', 'upskill-me' ); ?></span></button>
					</form>
					<form class="border-t border-gray-900/10 pt-6" action="<?php echo esc_url( $upskill_action ); ?>" method="post" data-org-confirm="<?php esc_attr_e( 'Delete this group? Its people keep their training and seats.', 'upskill-me' ); ?>">
						<?php upskill_org_team_fields( 'delete', $upskill_team['id'] ); ?>
						<p class="text-sm text-[#666]"><?php esc_html_e( 'Deleting the group keeps its people’s training and seats.', 'upskill-me' ); ?></p>
						<button class="mt-3 cursor-pointer text-sm font-medium text-[#b42318] hover:underline" type="submit"><?php esc_html_e( 'Delete group', 'upskill-me' ); ?></button>
					</form>
				</div>
			</div>
		</section>
	<?php else : ?>
		<?php
		// ---------------- All groups ----------------
		$upskill_teams = array_filter( array_map( static fn( $id ) => upskill_org_team( $id, $upskill_user ), upskill_org_team_ids( $upskill_user ) ) );
		?>
		<section class="container pb-12" aria-labelledby="org-group-create">
			<form class="flex flex-col gap-3 rounded-[24px] border border-gray-900/10 bg-white p-6 md:flex-row md:items-end" action="<?php echo esc_url( $upskill_action ); ?>" method="post">
				<?php upskill_org_team_fields( 'create' ); ?>
				<div class="flex flex-1 flex-col gap-1.5">
					<label id="org-group-create" class="text-sm font-medium text-gray-900" for="org-new-group"><?php esc_html_e( 'Create a group', 'upskill-me' ); ?></label>
					<input class="<?php echo esc_attr( $upskill_field ); ?>" id="org-new-group" type="text" name="name" maxlength="100" placeholder="<?php esc_attr_e( 'e.g. Chermside Branch, Cleaners, Sales Team', 'upskill-me' ); ?>" required>
				</div>
				<button class="btn btn--primary btn--md min-h-[50px] justify-center" type="submit">
					<span class="btn__label"><?php esc_html_e( 'Create Group', 'upskill-me' ); ?></span>
					<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
				</button>
			</form>
		</section>

		<section class="bg-white py-12" aria-labelledby="org-groups-list">
			<div class="container">
				<?php
				get_template_part(
					'template-parts/organisation/section-head',
					null,
					array(
						'eyebrow' => __( 'Your groups', 'upskill-me' ),
						'icon'    => 'users',
						'heading' => __( 'Progress by group.', 'upskill-me' ),
						'id'      => 'org-groups-list',
						'intro'   => __( 'Each group’s people, seats used and progress. Open a group to add or remove people.', 'upskill-me' ),
					)
				);
				?>
				<?php if ( $upskill_teams ) : ?>
					<?php get_template_part( 'template-parts/organisation/groups-table', null, array( 'teams' => $upskill_teams ) ); ?>
				<?php else : ?>
					<div class="mt-6 rounded-2xl border border-gray-900/10 bg-gray-25 p-6">
						<p class="text-base text-gray-700"><?php esc_html_e( 'No groups yet. Create one above, then add your people to it.', 'upskill-me' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
