<?php
/**
 * Organisation pages: the team's progress, one row per person and course
 * (upskill_org_overview() rows). A table from md; on phones each row is a
 * card of label / value lines (Figma 49374:53079, 49374:98607).
 *
 * - "dashboard" (Org dashboard 49325:16970): team member (cohort under the
 *   name), training, progress, status, last activity, View.
 * - "full" (Courses / Subscriptions 49325:17490): name (email under it),
 *   cohort, course, progress, status, last activity, View User, View Course.
 *
 * "View" / "View User" open the person's team in Group Registration's groups
 * dashboard (its only per-team screen: enrolled people, report, remove);
 * "View Course" the course page (sessions, with the team bar).
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type array[] $rows    Rows to show (already sliced or paged).
 *     @type string  $variant dashboard|full.
 * }
 */

$upskill_rows    = $args['rows'] ?? array();
$upskill_full    = 'full' === ( $args['variant'] ?? 'dashboard' );
$upskill_th      = 'uppercase border-b border-gray-900/10 bg-gray-25 px-5 py-4 text-sm font-medium text-[#6b6b72]';
$upskill_td      = 'px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:flex max-md:items-center max-md:justify-between max-md:gap-4 max-md:px-0 max-md:py-1.5 max-md:text-right max-md:before:shrink-0 max-md:before:text-left max-md:before:text-sm max-md:before:text-gray-500 max-md:before:content-[attr(data-label)]';
$upskill_heading = array(
	'name'     => $upskill_full ? __( 'Name', 'upskill-me' ) : __( 'Team member', 'upskill-me' ),
	'cohort'   => __( 'Cohort', 'upskill-me' ),
	'course'   => $upskill_full ? __( 'Course', 'upskill-me' ) : __( 'Training', 'upskill-me' ),
	'progress' => __( 'Progress', 'upskill-me' ),
	'status'   => __( 'Status', 'upskill-me' ),
	'activity' => __( 'Last activity', 'upskill-me' ),
);

if ( ! $upskill_rows ) {
	return;
}
?>
<div class="org-people md:overflow-auto rounded-lg border border-gray-900/10 bg-white">
	<table class="border-collapse text-left text-base text-gray-950 max-md:block w-full">
		<thead class="max-md:hidden">
			<tr>
				<?php foreach ( $upskill_heading as $upskill_key => $upskill_label ) : ?>
					<?php
					if ( 'cohort' === $upskill_key && ! $upskill_full ) {
						continue;
					}
					?>
					<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php echo esc_html( $upskill_label ); ?></th>
				<?php endforeach; ?>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'upskill-me' ); ?></span></th>
			</tr>
		</thead>
		<tbody class="max-md:block">
			<?php foreach ( $upskill_rows as $upskill_row ) : ?>
				<tr class="org-people__row max-md:block max-md:border-t max-md:border-gray-900/10 max-md:p-4 max-md:first:border-t-0">
					<th class="px-5 py-5 text-left align-middle font-normal md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-0 max-md:pb-3" scope="row">
						<span class="block font-medium"><?php echo esc_html( $upskill_row['name'] ); ?></span>
						<span class="block pt-1.5 text-sm text-gray-500"><?php echo esc_html( $upskill_full ? $upskill_row['email'] : $upskill_row['level'] ); ?></span>
					</th>
					<?php if ( $upskill_full ) : ?>
						<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php echo esc_attr( $upskill_heading['cohort'] ); ?>"><?php echo esc_html( $upskill_row['level'] ? $upskill_row['level'] : '—' ); ?></td>
					<?php endif; ?>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php echo esc_attr( $upskill_heading['course'] ); ?>"><?php echo esc_html( get_the_title( $upskill_row['course'] ) ); ?></td>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php echo esc_attr( $upskill_heading['progress'] ); ?>">
						<span class="inline-flex items-center gap-2.5 text-sm text-[#55555a]">
							<span class="block h-1.5 w-20 overflow-hidden rounded-full bg-[#ededef]" role="presentation"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) $upskill_row['percent']; ?>%"></span></span>
							<?php echo esc_html( $upskill_row['percent'] . '%' ); ?>
						</span>
					</td>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php echo esc_attr( $upskill_heading['status'] ); ?>"><?php upskill_org_status_badge( $upskill_row['status'] ); ?></td>
					<td class="<?php echo esc_attr( $upskill_td ); ?> whitespace-nowrap" data-label="<?php echo esc_attr( $upskill_heading['activity'] ); ?>"><?php echo $upskill_row['activity'] ? esc_html( wp_date( 'd M Y', $upskill_row['activity'] ) ) : '—'; ?></td>
					<td class="px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:flex max-md:items-center max-md:justify-between max-md:px-0 max-md:pt-3 max-md:pb-0">
						<span class="flex flex-wrap items-center gap-x-5 gap-y-2 max-md:w-full max-md:justify-between">
							<?php upskill_org_team_form( $upskill_row['group'], $upskill_full ? __( 'View User', 'upskill-me' ) : __( 'View', 'upskill-me' ), 'link', $upskill_row['name'] ); ?>
							<?php if ( $upskill_full ) : ?>
								<a class="group inline-flex items-center gap-[7px] text-sm font-medium whitespace-nowrap text-brand-700 hover:underline" href="<?php echo esc_url( get_permalink( $upskill_row['course'] ) ); ?>">
									<?php esc_html_e( 'View Course', 'upskill-me' ); ?>
									<span class="screen-reader-text"><?php echo esc_html( get_the_title( $upskill_row['course'] ) ); ?></span>
									<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
								</a>
							<?php endif; ?>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
