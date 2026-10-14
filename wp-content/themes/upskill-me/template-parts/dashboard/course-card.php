<?php
/**
 * Learner dashboard: a current course with its progress.
 *
 * Figma "User Account Dashboard" Article 49325:18382 (670 wide on 1440, full
 * width on 390): category and status, title, summary, progress bar, last
 * activity and "Continue Course".
 *
 * @package upskill-me
 *
 * @var array $args { @type array $course upskill_learner_course() row. }
 */

$upskill_course  = $args['course'];
$upskill_started = 'in_progress' === $upskill_course['status'];
?>
<article class="flex flex-col rounded-2xl border border-gray-900/10 bg-white p-6">
	<div class="flex items-start justify-between gap-4">
		<p class="min-h-[23px] text-sm leading-[1.62] font-medium tracking-[0.1em] text-brand-700 uppercase"><?php echo esc_html( $upskill_course['category'] ); ?></p>
		<p class="inline-flex flex-none items-center gap-1.5 rounded-full border px-[11px] py-1 text-sm leading-[1.62] font-medium tracking-[-0.007em] <?php echo esc_attr( $upskill_started ? 'border-[#f0dfb8] bg-[#fff6e5] text-[#7a5410]' : 'border-gray-900/10 bg-gray-25 text-gray-700' ); ?>">
			<?php upskill_icon( 'status-progress', 'size-[11px]' ); ?>
			<?php echo esc_html( $upskill_started ? __( 'In Progress', 'upskill-me' ) : __( 'Not Started', 'upskill-me' ) ); ?>
		</p>
	</div>

	<h3 class="pt-3 pb-2 text-xl leading-[1.5] font-medium text-gray-950 lg:leading-[1.3] lg:tracking-[-0.018em]">
		<a class="transition-colors hover:text-brand-700" href="<?php echo esc_url( $upskill_course['url'] ); ?>"><?php echo esc_html( $upskill_course['title'] ); ?></a>
	</h3>
	<?php if ( $upskill_course['summary'] ) : ?>
		<p class="text-sm leading-[1.55] tracking-[-0.007em] text-[#55555a]"><?php echo esc_html( $upskill_course['summary'] ); ?></p>
	<?php endif; ?>

	<div class="mt-auto pt-5">
		<div class="border-t border-gray-900/10 pt-5">
			<div class="flex items-end justify-between gap-4">
				<span class="text-sm leading-[1.62] tracking-[-0.007em] text-[#6b6b72]"><?php esc_html_e( 'Progress', 'upskill-me' ); ?></span>
				<span class="text-xl leading-[1.62] font-semibold tracking-[-0.005em] text-gray-900">
					<?php
					/* translators: %d: percentage of the course completed. */
					printf( esc_html__( '%d%% Complete', 'upskill-me' ), (int) $upskill_course['percent'] );
					?>
				</span>
			</div>
			<div class="mt-2.5 h-1.5 overflow-hidden rounded-[3px] bg-[#ededef]" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $upskill_course['percent'] ); ?>" aria-label="<?php esc_attr_e( 'Course progress', 'upskill-me' ); ?>">
				<div class="h-full rounded-[3px] bg-brand-700" style="width: <?php echo esc_attr( $upskill_course['percent'] ); ?>%"></div>
			</div>
		</div>

		<div class="flex flex-col items-start gap-4 pt-5 lg:flex-row lg:items-center lg:justify-between">
			<p class="font-secondary text-sm leading-[1.62] tracking-[-0.007em] text-[#6b6b72]">
				<?php if ( $upskill_course['activity'] ) : ?>
					<?php esc_html_e( 'Last activity', 'upskill-me' ); ?>
					<strong class="font-medium text-gray-900"><?php echo esc_html( wp_date( 'd M Y', $upskill_course['activity'] ) ); ?></strong>
				<?php else : ?>
					<?php esc_html_e( 'Not started yet', 'upskill-me' ); ?>
				<?php endif; ?>
			</p>
			<?php
			upskill_button(
				array(
					'url'   => $upskill_course['continue_url'],
					'title' => $upskill_started ? __( 'Continue Course', 'upskill-me' ) : __( 'Start Course', 'upskill-me' ),
				),
				array(
					'size'  => 'sm',
					'class' => 'pl-4 text-[0.78125rem] lg:text-sm',
				)
			);
			?>
		</div>
	</div>
</article>
