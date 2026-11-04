<?php
/**
 * Template Name: Organisation dashboard
 *
 * The organisation admin's (group leader's) home, where they land after
 * signing in. Figma: Organisation Account Dashboard 49325:16970 (1440) /
 * 49374:53079 (390).
 *
 * At a glance: four counts with links, the first three training packages
 * (table), the team's progress (totals, a completion bar and the four most
 * recent learners), shortcuts and account settings. The full lists are on
 * the "Courses & seats" page (template-org-training.php). Read only
 * (upskill_org_overview()); enrolling and managing people is Group
 * Registration's groups dashboard, account and billing details WooCommerce's
 * My Account. Only group leaders and administrators reach it.
 *
 * @package upskill-me
 */

get_header();

$upskill_data   = upskill_org_overview( get_current_user_id() );
$upskill_urls   = upskill_org_urls();
$upskill_groups = $upskill_data['groups'];
$upskill_rows   = $upskill_data['rows'];
$upskill_totals = $upskill_data['totals'];
$upskill_rated  = static fn( $count ) => $upskill_totals['assigned'] ? (int) round( 100 * $count / $upskill_totals['assigned'] ) : 0;
$upskill_share  = array(
	'completed'   => $upskill_rated( $upskill_totals['completed'] ),
	'in_progress' => $upskill_rated( $upskill_totals['in_progress'] ),
);
$upskill_share['not_started'] = $upskill_totals['assigned'] ? max( 0, 100 - $upskill_share['completed'] - $upskill_share['in_progress'] ) : 0;
$upskill_account = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-account' ) : get_edit_profile_url();
$upskill_th      = 'uppercase border-b border-gray-900/10 bg-gray-25 px-5 py-4 text-sm font-medium text-[#666666]';
$upskill_td      = 'px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:flex max-md:items-center max-md:justify-between max-md:gap-4 max-md:px-0 max-md:py-1.5 max-md:text-right max-md:before:shrink-0 max-md:before:text-left max-md:before:text-sm max-md:before:text-gray-500 max-md:before:content-[attr(data-label)]';

$upskill_actions = array(
	array(
		'icon'    => 'users',
		'title'   => __( 'Enrol Your Team', 'upskill-me' ),
		'text'    => __( 'Assign training seats to your people.', 'upskill-me' ),
		'link'    => array(
			'url'   => $upskill_urls['manage'],
			'title' => __( 'Enrol Team Members', 'upskill-me' ),
		),
		'primary' => true,
	),
	array(
		'icon'    => 'book-columns',
		'title'   => __( 'View Courses', 'upskill-me' ),
		'text'    => __( 'See the training available to your organisation.', 'upskill-me' ),
		'link'    => array(
			'url'   => $upskill_urls['training'],
			'title' => __( 'View Courses', 'upskill-me' ),
		),
		'primary' => false,
	),
	array(
		'icon'    => 'chart',
		'title'   => __( 'View Results', 'upskill-me' ),
		'text'    => __( 'Track completion and team progress.', 'upskill-me' ),
		'link'    => array(
			'url'   => $upskill_urls['team'],
			'title' => __( 'View Results', 'upskill-me' ),
		),
		'primary' => false,
	),
);

// WooCommerce My Account: billing details (organisation name and address) and account details. LearnDash and
// WooCommerce have no notification preferences a user can change, so Figma's "Notifications" row is left out.
$upskill_settings = array(
	array( 'building', __( 'Organisation Details', 'upskill-me' ), __( 'Update your organisation name and billing address.', 'upskill-me' ), __( 'Edit', 'upskill-me' ), function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'edit-address', 'billing', wc_get_page_permalink( 'myaccount' ) ) : '' ),
	array( 'account-profile', __( 'Account Details', 'upskill-me' ), __( 'Manage your name, email address and account information.', 'upskill-me' ), __( 'Edit', 'upskill-me' ), $upskill_account ),
	array( 'account-password', __( 'Password & Security', 'upskill-me' ), __( 'Manage your password and account security.', 'upskill-me' ), __( 'Manage', 'upskill-me' ), $upskill_account . '#password_current' ),
);
?>
<main id="primary" class="site-main org-page bg-gray-25">
	<section class="container pt-8 pb-12 lg:pt-14 lg:pb-12" aria-labelledby="org-heading">
		<div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between lg:gap-10">
			<div class="flex flex-col items-start gap-3 lg:gap-4">
				<?php upskill_eyebrow( __( 'Account overview', 'upskill-me' ), 'overview' ); ?>
				<h1 id="org-heading" class="heading text-h1 font-medium text-gray-950 lg:font-semibold"><?php echo wp_kses( __( 'Your training at <em>a glance.</em>', 'upskill-me' ), array( 'em' => array() ) ); ?></h1>
			</div>
			<p class="text-base leading-[1.5] text-[#6b6b72] lg:w-[400px] lg:flex-none lg:text-lead"><?php esc_html_e( 'See your training, team enrolments and progress at a glance.', 'upskill-me' ); ?></p>
		</div>

		<?php get_template_part( 'template-parts/account/pending-orders' ); ?>

		<?php
		get_template_part(
			'template-parts/organisation/stat-cards',
			null,
			array(
				'class' => 'mt-8 lg:mt-6',
				'items' => array(
					array(
						'label' => __( 'Active training', 'upskill-me' ),
						'value' => $upskill_totals['groups'],
						'text'  => __( 'Training packages currently available to your organisation.', 'upskill-me' ),
						'link'  => array(
							'url'   => $upskill_urls['training'],
							'title' => __( 'View Training', 'upskill-me' ),
						),
					),
					array(
						'label' => __( 'Team members', 'upskill-me' ),
						'value' => $upskill_totals['members'],
						'text'  => __( 'People currently enrolled in your training.', 'upskill-me' ),
						'link'  => array(
							'url'   => $upskill_urls['manage'],
							'title' => __( 'Manage Team', 'upskill-me' ),
						),
					),
					array(
						'label' => __( 'In progress', 'upskill-me' ),
						'value' => $upskill_totals['in_progress'],
						'text'  => __( 'Courses your team is working through.', 'upskill-me' ),
						'link'  => array(
							'url'   => $upskill_urls['training'] . '#org-training',
							'title' => __( 'View Courses', 'upskill-me' ),
						),
					),
					array(
						'label' => __( 'Completed', 'upskill-me' ),
						'value' => $upskill_totals['completed'],
						'text'  => __( 'Courses completed by your team.', 'upskill-me' ),
						'link'  => array(
							'url'   => $upskill_urls['team'],
							'title' => __( 'View Results', 'upskill-me' ),
						),
					),
				),
			)
		);
		?>
	</section>

	<section class="bg-white py-12" aria-labelledby="org-training">
		<div class="container">
			<?php
			get_template_part(
				'template-parts/organisation/section-head',
				null,
				array(
					'eyebrow' => __( 'Active training', 'upskill-me' ),
					'icon'    => 'video-book',
					'heading' => __( 'Your training packages.', 'upskill-me' ),
					'id'      => 'org-training',
					'intro'   => __( 'Manage the training currently available to your organisation and see how your team is using each package.', 'upskill-me' ),
				)
			);
			?>

			<?php if ( $upskill_groups ) : ?>
				<div class="org-packages mt-6 overflow-hidden rounded-lg border border-gray-900/10 bg-white">
					<table class="w-full border-collapse text-left text-base text-gray-950 max-md:block">
						<thead class="max-md:hidden">
							<tr>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Training', 'upskill-me' ); ?></th>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Seats', 'upskill-me' ); ?></th>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Used', 'upskill-me' ); ?></th>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Remaining', 'upskill-me' ); ?></th>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Completion', 'upskill-me' ); ?></th>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Status', 'upskill-me' ); ?></th>
								<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'upskill-me' ); ?></span></th>
							</tr>
						</thead>
						<tbody class="max-md:block">
							<?php foreach ( array_slice( $upskill_groups, 0, 3 ) as $upskill_group ) : ?>
								<tr class="max-md:block border-t border-gray-900/10 max-md:p-4 first:border-t-0">
									<th class="px-5 py-5 text-left align-middle font-normal md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-0 max-md:pb-3" scope="row">
										<span class="block font-medium"><?php echo esc_html( $upskill_group['title'] ); ?></span>
										<span class="block pt-1.5 text-sm text-[#666]"><?php echo esc_html( implode( ' · ', array_filter( array( $upskill_group['category'], $upskill_group['level'] ) ) ) ); ?></span>
									</th>
									<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Seats', 'upskill-me' ); ?>"><?php echo $upskill_group['unlimited'] ? '∞' : esc_html( number_format_i18n( $upskill_group['seats'] ) ); ?></td>
									<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Used', 'upskill-me' ); ?>"><?php echo esc_html( number_format_i18n( $upskill_group['used'] ) ); ?></td>
									<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Remaining', 'upskill-me' ); ?>"><?php echo $upskill_group['unlimited'] ? '∞' : esc_html( number_format_i18n( $upskill_group['remaining'] ) ); ?></td>
									<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Completion', 'upskill-me' ); ?>">
										<span class="inline-flex items-center gap-2.5">
											<span class="block h-1.5 w-[133px] overflow-hidden rounded-full bg-[#ededef] max-md:w-20" role="presentation"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) min( 100, $upskill_group['completion'] ); ?>%"></span></span>
											<?php echo esc_html( $upskill_group['completion'] . '%' ); ?>
										</span>
									</td>
									<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Status', 'upskill-me' ); ?>"><?php upskill_org_status_badge( 'active' ); ?></td>
									<td class="px-5 py-5 max-sm:pt-0 align-middle md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-3 max-md:pb-0">
										<?php if ( $upskill_group['course'] ) : ?>
											<a class="group inline-flex items-center gap-[7px] text-sm font-medium whitespace-nowrap text-brand-700 hover:underline" href="<?php echo esc_url( get_permalink( $upskill_group['course'] ) ); ?>">
												<?php esc_html_e( 'View Details', 'upskill-me' ); ?>
												<span class="screen-reader-text"><?php echo esc_html( $upskill_group['title'] ); ?></span>
												<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
											</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<p class="pt-4 text-sm text-[#6b6b72]">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: shown, 2: all. */
							__( 'Showing %1$s of %2$s active training packages.', 'upskill-me' ),
							number_format_i18n( min( 3, count( $upskill_groups ) ) ),
							number_format_i18n( count( $upskill_groups ) )
						)
					);
					?>
				</p>
			<?php else : ?>
				<div class="mt-6 flex flex-col items-start gap-4 rounded-2xl border border-gray-900/10 bg-gray-25 p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'Your organisation has no training yet. Choose training and the number of seats to get started.', 'upskill-me' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="flex flex-wrap gap-3 pt-6">
				<?php
				if ( $upskill_groups ) {
					upskill_button(
						array(
							'url'   => $upskill_urls['training'],
							'title' => __( 'View All Courses', 'upskill-me' ),
						),
						array( 'variant' => 'light' )
					);
					upskill_button(
						array(
							'url'   => $upskill_urls['manage'],
							'title' => __( 'Enrol Team Members', 'upskill-me' ),
						),
						array( 'variant' => 'light' )
					);
				} else {
					upskill_button(
						array(
							'url'   => $upskill_urls['library'],
							'title' => __( 'Find Training', 'upskill-me' ),
						)
					);
				}
				?>
			</div>
		</div>
	</section>

	<section class="py-12" aria-labelledby="org-progress">
		<div class="container">
			<?php
			get_template_part(
				'template-parts/organisation/section-head',
				null,
				array(
					'eyebrow' => __( 'Course completion', 'upskill-me' ),
					'icon'    => 'course-completion',
					'heading' => __( 'Track your team’s progress.', 'upskill-me' ),
					'id'      => 'org-progress',
					'intro'   => __( 'See how your people are progressing across the training available to your organisation.', 'upskill-me' ),
				)
			);

			get_template_part(
				'template-parts/organisation/stat-cards',
				null,
				array(
					'class' => 'mt-6',
					'items' => array(
						array(
							'label' => __( 'Total assigned', 'upskill-me' ),
							'value' => $upskill_totals['assigned'],
						),
						array(
							'label' => __( 'In progress', 'upskill-me' ),
							'value' => $upskill_totals['in_progress'],
						),
						array(
							'label' => __( 'Completed', 'upskill-me' ),
							'value' => $upskill_totals['completed'],
						),
						array(
							'label' => __( 'Not started', 'upskill-me' ),
							'value' => $upskill_totals['not_started'],
						),
					),
				)
			);
			?>

			<?php if ( $upskill_rows ) : ?>
				<?php // Completed, in progress, not started as one bar (Figma 49325:16970). ?>
				<div class="pt-8">
					<p class="screen-reader-text">
						<?php
						/* translators: 1-3: percentages completed, in progress, not started. */
						echo esc_html( sprintf( __( 'Completion: %1$s%% completed, %2$s%% in progress, %3$s%% not started.', 'upskill-me' ), $upskill_share['completed'], $upskill_share['in_progress'], $upskill_share['not_started'] ) );
						?>
					</p>
					<span class="flex h-2.5 overflow-hidden rounded-[5px] bg-[#ededef]" aria-hidden="true">
						<span class="block h-full bg-brand-700" style="width:<?php echo (int) $upskill_share['completed']; ?>%"></span>
						<span class="block h-full bg-[#d0c3ef]" style="width:<?php echo (int) $upskill_share['in_progress']; ?>%"></span>
					</span>
					<ul class="flex flex-wrap gap-x-5 gap-y-1 pt-4 text-sm text-[#55555a] lg:text-base" aria-hidden="true">
						<?php
						foreach (
							array(
								array( 'bg-brand-700', __( 'Completed', 'upskill-me' ), 'completed' ),
								array( 'bg-[#d0c3ef]', __( 'In progress', 'upskill-me' ), 'in_progress' ),
								array( 'bg-[#ededef]', __( 'Not started', 'upskill-me' ), 'not_started' ),
							) as $upskill_key
						) :
							?>
							<li class="flex items-center gap-2">
								<span class="size-2.5 rounded-[3px] <?php echo esc_attr( $upskill_key[0] ); ?>"></span>
								<?php
								/* translators: 1: status, 2: percentage, 3: count. */
								echo esc_html( sprintf( __( '%1$s · %2$s%% (%3$s)', 'upskill-me' ), $upskill_key[1], $upskill_share[ $upskill_key[2] ], number_format_i18n( $upskill_totals[ $upskill_key[2] ] ) ) );
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="mt-8">
					<?php
					get_template_part(
						'template-parts/organisation/people-table',
						null,
						array(
							'rows'    => array_slice( $upskill_rows, 0, 4 ),
							'variant' => 'dashboard',
						)
					);
					?>
				</div>
				<div class="pt-6">
					<?php
					upskill_button(
						array(
							'url'   => $upskill_urls['team'],
							'title' => __( 'View All Results', 'upskill-me' ),
						),
						array( 'variant' => 'light' )
					);
					?>
				</div>
			<?php else : ?>
				<div class="mt-6 flex flex-col items-start gap-4 rounded-2xl border border-gray-900/10 bg-white p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'No one is enrolled yet. Add your people to start their training.', 'upskill-me' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="bg-brand-25 py-12" aria-labelledby="org-actions">
		<div class="container">
			<h2 id="org-actions" class="heading text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950"><?php esc_html_e( 'What would you like to do?', 'upskill-me' ); ?></h2>
			<ul class="mt-6 grid gap-4 md:grid-cols-3" role="list">
				<?php foreach ( $upskill_actions as $upskill_action ) : ?>
					<li class="flex flex-col items-start rounded-[24px] border bg-white p-6 <?php echo $upskill_action['primary'] ? 'border-brand-700' : 'border-gray-900/10'; ?>">
						<span class="grid size-10 place-items-center rounded-[11px] <?php echo $upskill_action['primary'] ? 'bg-brand-700 text-white' : 'bg-[#f1edfa] text-brand-700'; ?>" aria-hidden="true"><?php upskill_icon( $upskill_action['icon'], 'size-[19px]' ); ?></span>
						<h3 class="pt-4 text-xl leading-[1.5] font-medium text-gray-950"><?php echo esc_html( $upskill_action['title'] ); ?></h3>
						<p class="pt-1.5 text-base leading-[1.5] text-[#666]"><?php echo esc_html( $upskill_action['text'] ); ?></p>
						<?php upskill_button( $upskill_action['link'], array( 'variant' => $upskill_action['primary'] ? 'primary' : 'light', 'class' => 'mt-5' ) ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="bg-white py-12" aria-labelledby="org-account">
		<div class="container">
			<?php
			get_template_part(
				'template-parts/organisation/section-head',
				null,
				array(
					'eyebrow' => __( 'Account settings', 'upskill-me' ),
					'icon'    => 'account-settings',
					'heading' => __( 'Manage your account.', 'upskill-me' ),
					'id'      => 'org-account',
					'intro'   => __( 'Update your organisation details and account information.', 'upskill-me' ),
				)
			);
			?>
			<ul class="mt-6 overflow-hidden rounded-2xl border border-gray-900/10 bg-white" role="list">
				<?php foreach ( $upskill_settings as $upskill_row ) : ?>
					<?php
					if ( ! $upskill_row[4] ) {
						continue;
					}
					?>
					<li class="border-b border-gray-900/10 last:border-b-0">
						<a class="group flex items-center gap-5 p-5 transition-colors hover:bg-brand-25" href="<?php echo esc_url( $upskill_row[4] ); ?>">
							<span class="grid size-9 flex-none place-items-center rounded-[10px] bg-gray-25 text-gray-800" aria-hidden="true"><?php upskill_icon( $upskill_row[0], 'size-[17px]' ); ?></span>
							<span class="flex min-w-0 flex-1 flex-col">
								<span class="text-base font-medium text-gray-950"><?php echo esc_html( $upskill_row[1] ); ?></span>
								<span class="pt-0.5 text-sm text-[#666]"><?php echo esc_html( $upskill_row[2] ); ?></span>
							</span>
							<span class="inline-flex flex-none items-center gap-[7px] text-sm font-medium text-brand-700">
								<?php echo esc_html( $upskill_row[3] ); ?>
								<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
</main>
<?php
get_footer();
