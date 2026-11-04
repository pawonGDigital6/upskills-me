<?php
/**
 * Course page, organisation admin with the course: a bar above LearnDash's
 * course view (sessions and progress) saying their organisation has this
 * training, with each of their groups' seats, "Manage Team" (Group
 * Registration's groups dashboard on that group, which reads a POSTed
 * wdm_group_id, as on the organisation dashboard) and "Buy More Seats" (the
 * course's buy box in add-seats mode: ?add-seats=<group>, see
 * learndash/ld30/course.php).
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type array[] $groups    upskill_org_course_groups() rows.
 *     @type int     $course_id Course ID.
 * }
 */

$upskill_groups = $args['groups'] ?? array();
$upskill_course = (int) ( $args['course_id'] ?? 0 );
$upskill_manage = function_exists( 'upskill_groups_dashboard_url' ) ? upskill_groups_dashboard_url() : '';

if ( ! $upskill_groups ) {
	return;
}
?>
<section class="course-org-bar border-b border-line bg-brand-25" aria-label="<?php esc_attr_e( 'Your organisation’s training', 'upskill-me' ); ?>">
	<div class="container flex flex-col gap-4 py-5 lg:py-6">
		<p class="text-base font-medium text-gray-950"><?php esc_html_e( 'Your organisation has this training.', 'upskill-me' ); ?></p>
		<ul class="flex flex-col gap-3" role="list">
			<?php foreach ( $upskill_groups as $upskill_group ) : ?>
				<li class="flex flex-col gap-3 rounded-lg border border-line bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
					<p class="text-sm text-gray-500">
						<span class="block font-medium text-gray-950"><?php echo esc_html( $upskill_group['title'] ); ?></span>
						<?php
						if ( $upskill_group['unlimited'] ) {
							esc_html_e( 'Unlimited seats', 'upskill-me' );
						} else {
							/* translators: 1: seats in use, 2: seats bought. */
							echo esc_html( sprintf( __( '%1$s of %2$s seats in use', 'upskill-me' ), number_format_i18n( $upskill_group['used'] ), number_format_i18n( $upskill_group['seats'] ) ) );
						}
						?>
					</p>
					<div class="flex flex-wrap gap-2">
						<?php if ( $upskill_manage ) : ?>
							<form action="<?php echo esc_url( $upskill_manage ); ?>" method="post">
								<input type="hidden" name="wdm_group_id" value="<?php echo esc_attr( $upskill_group['id'] ); ?>">
								<button class="btn btn--light btn--sm" type="submit"><span class="btn__label"><?php esc_html_e( 'Manage Team', 'upskill-me' ); ?></span><span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span></button>
							</form>
						<?php endif; ?>
						<?php if ( ! $upskill_group['unlimited'] && $upskill_group['product_id'] ) : ?>
							<a class="btn btn--primary btn--sm" href="<?php echo esc_url( add_query_arg( 'add-seats', $upskill_group['id'], get_permalink( $upskill_course ) ) . '#buy' ); ?>"><span class="btn__label"><?php esc_html_e( 'Buy More Seats', 'upskill-me' ); ?></span><span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span></a>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
