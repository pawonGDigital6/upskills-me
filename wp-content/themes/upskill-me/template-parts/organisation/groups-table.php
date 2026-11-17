<?php
/**
 * Organisation pages: the coordinator's groups (inc/lms-org-groups.php), one
 * row each: people (seats used), completion, completed, in progress, Manage.
 * A table from md; on phones each row is a card of label / value lines, like
 * the training packages table.
 *
 * @package upskill-me
 *
 * @var array $args { @type array[] $teams upskill_org_team() rows. }
 */

$upskill_teams = $args['teams'] ?? array();
$upskill_th    = 'uppercase border-b border-gray-900/10 bg-gray-25 px-5 py-4 text-sm font-medium text-[#666666]';
$upskill_td    = 'px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:flex max-md:items-center max-md:justify-between max-md:gap-4 max-md:px-0 max-md:py-1.5 max-md:text-right max-md:before:shrink-0 max-md:before:text-left max-md:before:text-sm max-md:before:text-gray-500 max-md:before:content-[attr(data-label)]';

if ( ! $upskill_teams ) {
	return;
}
?>
<div class="mt-6 overflow-hidden rounded-lg border border-gray-900/10 bg-white">
	<table class="w-full border-collapse text-left text-base text-gray-950 max-md:block">
		<thead class="max-md:hidden">
			<tr>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Group', 'upskill-me' ); ?></th>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Seats used', 'upskill-me' ); ?></th>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Completion', 'upskill-me' ); ?></th>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'Completed', 'upskill-me' ); ?></th>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><?php esc_html_e( 'In progress', 'upskill-me' ); ?></th>
				<th class="<?php echo esc_attr( $upskill_th ); ?>" scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'upskill-me' ); ?></span></th>
			</tr>
		</thead>
		<tbody class="max-md:block">
			<?php foreach ( $upskill_teams as $upskill_team ) : ?>
				<tr class="max-md:block border-t border-gray-900/10 max-md:p-4 first:border-t-0">
					<th class="px-5 py-5 text-left align-middle font-normal md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-0 max-md:pb-3" scope="row">
						<span class="block font-medium"><?php echo esc_html( $upskill_team['title'] ); ?></span>
						<span class="block pt-1.5 text-sm text-[#666]">
							<?php
							/* translators: %s: number of people. */
							echo esc_html( sprintf( _n( '%s person', '%s people', $upskill_team['people'], 'upskill-me' ), number_format_i18n( $upskill_team['people'] ) ) );
							?>
						</span>
					</th>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Seats used', 'upskill-me' ); ?>"><?php echo esc_html( number_format_i18n( $upskill_team['people'] ) ); ?></td>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Completion', 'upskill-me' ); ?>">
						<span class="inline-flex items-center gap-2.5">
							<span class="block h-1.5 w-[133px] overflow-hidden rounded-full bg-[#ededef] max-md:w-20" role="presentation"><span class="block h-full rounded-full bg-brand-700" style="width:<?php echo (int) min( 100, $upskill_team['completion'] ); ?>%"></span></span>
							<?php echo esc_html( $upskill_team['completion'] . '%' ); ?>
						</span>
					</td>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'Completed', 'upskill-me' ); ?>"><?php echo esc_html( number_format_i18n( $upskill_team['completed'] ) ); ?></td>
					<td class="<?php echo esc_attr( $upskill_td ); ?>" data-label="<?php esc_attr_e( 'In progress', 'upskill-me' ); ?>"><?php echo esc_html( number_format_i18n( $upskill_team['in_progress'] ) ); ?></td>
					<td class="px-5 py-5 align-middle md:border-t md:border-gray-900/10 max-md:block max-md:px-0 max-md:pt-3 max-md:pb-0">
						<a class="group inline-flex items-center gap-[7px] text-sm font-medium whitespace-nowrap text-brand-700 hover:underline" href="<?php echo esc_url( upskill_org_groups_url( $upskill_team['id'] ) ); ?>">
							<?php esc_html_e( 'Manage', 'upskill-me' ); ?>
							<span class="screen-reader-text"><?php echo esc_html( $upskill_team['title'] ); ?></span>
							<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
