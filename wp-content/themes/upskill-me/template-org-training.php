<?php
/**
 * Template Name: Organisation courses & seats
 *
 * The organisation's full training and team lists, under the organisation
 * dashboard. Figma: Courses / Subscriptions 49325:17490 (1440) /
 * 49374:98607 (390).
 *
 * One card per group the user leads (its course, seats, seat usage and
 * completion; View Course, Enrol), then everyone enrolled with their cohort
 * (the group's level), progress, status and last activity, 20 per page. Read
 * only (upskill_org_overview()); enrolling and managing people is Group
 * Registration's groups dashboard, buying is the course page. Only group
 * leaders and administrators reach it (upskill_org_dashboard_guard()).
 *
 * @package upskill-me
 */

get_header();

$upskill_data     = upskill_org_overview( get_current_user_id() );
$upskill_urls     = upskill_org_urls();
$upskill_groups   = $upskill_data['groups'];
$upskill_rows     = $upskill_data['rows'];
$upskill_totals   = $upskill_data['totals'];
$upskill_per_page = 20;
$upskill_pages    = max( 1, (int) ceil( count( $upskill_rows ) / $upskill_per_page ) );
$upskill_page     = min( $upskill_pages, max( 1, absint( $_GET['team-page'] ?? 1 ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only paging.
$upskill_shown    = array_slice( $upskill_rows, ( $upskill_page - 1 ) * $upskill_per_page, $upskill_per_page );
$upskill_enrol    = array(
	'url'   => $upskill_urls['manage'],
	'title' => __( 'Enrol Users', 'upskill-me' ),
);
$upskill_page_url = static fn( $page ) => ( $page > 1 ? add_query_arg( 'team-page', $page, $upskill_urls['training'] ) : remove_query_arg( 'team-page', $upskill_urls['training'] ) ) . '#org-team';
?>
<main id="primary" class="site-main org-page bg-gray-25">
	<section class="container pt-8 pb-12 lg:pt-14 lg:pb-20" aria-labelledby="org-training-heading">
		<div class="flex flex-col items-start gap-3">
			<?php upskill_eyebrow( __( 'Courses & seats', 'upskill-me' ), 'book-columns' ); ?>
			<?php upskill_heading( __( 'Manage your <em>team’s training.</em>', 'upskill-me' ), 'h1', 'heading text-h1 font-medium text-gray-950 lg:font-semibold' ); ?>
		</div>
		<p class="pt-5 text-base leading-[1.5] text-[#666] lg:pt-6 lg:text-lead"><?php esc_html_e( 'View your active training, monitor available seats and see how many people are enrolled.', 'upskill-me' ); ?></p>

		<?php get_template_part( 'template-parts/account/pending-orders' ); ?>
	</section>

	<section class="bg-white py-12 lg:py-16" aria-labelledby="org-training">
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
					'intro'   => __( 'Choose a purchased training package to begin assigning seats to your people.', 'upskill-me' ),
					'button'  => $upskill_groups ? $upskill_enrol : array(),
				)
			);
			?>

			<?php if ( $upskill_groups ) : ?>
				<ul class="mt-8 grid gap-5 md:grid-cols-2 lg:mt-12 xl:grid-cols-3" role="list">
					<?php foreach ( $upskill_groups as $upskill_group ) : ?>
						<?php $upskill_used_pc = $upskill_group['seats'] ? (int) round( 100 * $upskill_group['used'] / $upskill_group['seats'] ) : 0; ?>
						<li class="org-card flex flex-col rounded-[24px] border border-gray-900/10 bg-white p-6">
							<div class="flex items-start justify-between gap-4">
								<p class="pt-1 text-sm leading-[1.6] font-medium tracking-[0.1em] text-brand-700 uppercase"><?php echo esc_html( $upskill_group['category'] ? $upskill_group['category'] : __( 'Training', 'upskill-me' ) ); ?></p>
								<?php upskill_org_status_badge( 'active' ); ?>
							</div>
							<h3 class="pt-3 pb-2 text-lg leading-[1.5] font-medium text-gray-950"><?php echo esc_html( $upskill_group['title'] ); ?></h3>
							<?php if ( $upskill_group['summary'] ) : ?>
								<p class="text-sm leading-[1.5] text-[#666]"><?php echo esc_html( $upskill_group['summary'] ); ?></p>
							<?php endif; ?>
							<dl class="mt-5 grid grid-cols-3 gap-3 border-y border-[#e6e6e6] py-5">
								<?php
								foreach (
									array(
										array( __( 'Total seats', 'upskill-me' ), $upskill_group['unlimited'] ? '∞' : number_format_i18n( $upskill_group['seats'] ) ),
										array( __( 'Enrolled', 'upskill-me' ), number_format_i18n( $upskill_group['used'] ) ),
										array( __( 'Remaining', 'upskill-me' ), $upskill_group['unlimited'] ? '∞' : number_format_i18n( $upskill_group['remaining'] ) ),
									) as $upskill_fact
								) :
									?>
									<div>
										<dt class="text-sm text-[#666]"><?php echo esc_html( $upskill_fact[0] ); ?></dt>
										<dd class="pt-1 text-2xl leading-[1.1] font-medium tracking-[-0.01em] text-gray-950"><?php echo esc_html( $upskill_fact[1] ); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
							<dl class="flex flex-col gap-4 py-5">
								<?php
								foreach (
									array(
										array(
											__( 'Seat usage', 'upskill-me' ),
											$upskill_group['unlimited'] ? 0 : $upskill_used_pc,
											$upskill_group['unlimited']
												/* translators: %s: seats in use. */
												? sprintf( __( '%s in use', 'upskill-me' ), number_format_i18n( $upskill_group['used'] ) )
												/* translators: 1: seats in use, 2: seats bought. */
												: sprintf( __( '%1$s of %2$s', 'upskill-me' ), number_format_i18n( $upskill_group['used'] ), number_format_i18n( $upskill_group['seats'] ) ),
										),
										array( __( 'Completion', 'upskill-me' ), $upskill_group['completion'], $upskill_group['completion'] . '%' ),
									) as $upskill_bar
								) :
									?>
									<div>
										<dt class="text-base text-[#666]"><?php echo esc_html( $upskill_bar[0] ); ?></dt>
										<dd class="flex items-center gap-2.5 pt-2">
											<span class="block h-1.5 flex-1 overflow-hidden rounded-full bg-[#ededef]" role="presentation"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) min( 100, $upskill_bar[1] ); ?>%"></span></span>
											<span class="text-sm whitespace-nowrap text-gray-950"><?php echo esc_html( $upskill_bar[2] ); ?></span>
										</dd>
									</div>
								<?php endforeach; ?>
							</dl>
							<div class="mt-auto flex flex-wrap items-center justify-between gap-3">
								<?php if ( $upskill_group['course'] ) : ?>
									<a class="btn btn--light btn--sm" href="<?php echo esc_url( get_permalink( $upskill_group['course'] ) ); ?>"><span class="btn__label"><?php esc_html_e( 'View Course', 'upskill-me' ); ?></span><span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span></a>
								<?php endif; ?>
								<?php upskill_org_team_form( $upskill_group['id'], __( 'Enrol', 'upskill-me' ), 'link', $upskill_group['title'] ); ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="pt-4 text-sm text-[#6b6b72]">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: number of training packages. */
							_n( 'Showing <strong class="font-medium text-gray-950">%s</strong> active training package.', 'Showing all <strong class="font-medium text-gray-950">%s</strong> active training packages.', count( $upskill_groups ), 'upskill-me' ),
							number_format_i18n( count( $upskill_groups ) )
						),
						array( 'strong' => array( 'class' => array() ) )
					);
					?>
				</p>
			<?php else : ?>
				<div class="mt-8 flex flex-col items-start gap-4 rounded-2xl border border-gray-900/10 bg-gray-25 p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'Your organisation has no training yet. Choose training and the number of seats to get started.', 'upskill-me' ); ?></p>
					<?php
					upskill_button(
						array(
							'url'   => $upskill_urls['library'],
							'title' => __( 'Find Training', 'upskill-me' ),
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="py-12 lg:py-16" aria-labelledby="org-team">
		<div class="container">
			<?php
			get_template_part(
				'template-parts/organisation/section-head',
				null,
				array(
					'eyebrow' => __( 'Team enrolment', 'upskill-me' ),
					'icon'    => 'users',
					'heading' => __( 'See who’s learning.', 'upskill-me' ),
					'id'      => 'org-team',
					'intro'   => __( 'View the people assigned to your training and quickly understand their current progress.', 'upskill-me' ),
					'button'  => $upskill_groups ? $upskill_enrol : array(),
				)
			);

			get_template_part(
				'template-parts/organisation/stat-cards',
				null,
				array(
					'class' => 'mt-8 lg:mt-12',
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

			<?php if ( $upskill_shown ) : ?>
				<div class="mt-6">
					<?php
					get_template_part(
						'template-parts/organisation/people-table',
						null,
						array(
							'rows'    => $upskill_shown,
							'variant' => 'full',
						)
					);
					?>
				</div>
				<div class="flex flex-wrap items-center justify-between gap-4 pt-4">
					<p class="text-sm text-[#6b6b72]">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: first row shown, 2: last row shown, 3: all rows. */
								__( 'Showing %1$s–%2$s of %3$s enrolments.', 'upskill-me' ),
								number_format_i18n( ( $upskill_page - 1 ) * $upskill_per_page + 1 ),
								number_format_i18n( ( $upskill_page - 1 ) * $upskill_per_page + count( $upskill_shown ) ),
								number_format_i18n( count( $upskill_rows ) )
							)
						);
						?>
					</p>
					<?php if ( $upskill_pages > 1 ) : ?>
						<nav class="flex items-center gap-2" aria-label="<?php esc_attr_e( 'Team pages', 'upskill-me' ); ?>">
							<?php if ( $upskill_page > 1 ) : ?>
								<a class="btn btn--light btn--sm" href="<?php echo esc_url( $upskill_page_url( $upskill_page - 1 ) ); ?>" rel="prev"><span class="btn__label"><?php esc_html_e( 'Previous', 'upskill-me' ); ?></span></a>
							<?php endif; ?>
							<span class="px-2 text-sm text-gray-500">
								<?php
								/* translators: 1: current page, 2: number of pages. */
								echo esc_html( sprintf( __( 'Page %1$s of %2$s', 'upskill-me' ), number_format_i18n( $upskill_page ), number_format_i18n( $upskill_pages ) ) );
								?>
							</span>
							<?php if ( $upskill_page < $upskill_pages ) : ?>
								<a class="btn btn--light btn--sm" href="<?php echo esc_url( $upskill_page_url( $upskill_page + 1 ) ); ?>" rel="next"><span class="btn__label"><?php esc_html_e( 'Next', 'upskill-me' ); ?></span></a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="mt-6 flex flex-col items-start gap-4 rounded-2xl border border-gray-900/10 bg-white p-6">
					<p class="text-base text-gray-700"><?php esc_html_e( 'No one is enrolled yet. Add your people to start their training.', 'upskill-me' ); ?></p>
					<?php if ( $upskill_groups ) : ?>
						<?php upskill_button( $upskill_enrol ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
