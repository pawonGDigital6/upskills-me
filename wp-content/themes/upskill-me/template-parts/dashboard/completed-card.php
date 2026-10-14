<?php
/**
 * Learner dashboard: a completed course.
 *
 * Figma "User Account Dashboard", Course completion Article (443 wide on
 * 1440): tick disc, category, title, completion date and "View Course".
 *
 * @package upskill-me
 *
 * @var array $args { @type array $course upskill_learner_course() row. }
 */

$upskill_course = $args['course'];
?>
<article class="flex gap-4 rounded-lg border border-gray-900/10 bg-white p-5">
	<span class="mt-px grid size-[34px] flex-none place-items-center rounded-full bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( 'completed-check', 'size-[15px]' ); ?></span>
	<div class="flex min-w-0 flex-col">
		<?php if ( $upskill_course['category'] ) : ?>
			<p class="pt-0.5 text-sm leading-[1.62] font-medium tracking-[0.1em] text-brand-700 uppercase"><?php echo esc_html( $upskill_course['category'] ); ?></p>
		<?php endif; ?>
		<h3 class="pt-1 text-base leading-[1.62] font-medium tracking-[-0.014em] text-gray-900"><?php echo esc_html( $upskill_course['title'] ); ?></h3>
		<p class="flex flex-wrap items-center gap-x-1 gap-y-3 pt-2 font-secondary text-sm leading-[1.62] tracking-[-0.007em] text-[#6b6b72]">
			<span>
				<?php esc_html_e( 'Completed', 'upskill-me' ); ?>
				<strong class="font-medium text-gray-900"><?php echo esc_html( $upskill_course['completed_on'] ? wp_date( 'j F Y', $upskill_course['completed_on'] ) : '' ); ?></strong>
			</span>
			<a class="group inline-flex items-center gap-1.5 font-sans font-medium text-brand-700" href="<?php echo esc_url( $upskill_course['url'] ); ?>">
				<?php esc_html_e( 'View Course', 'upskill-me' ); ?>
				<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
			</a>
		</p>
	</div>
</article>
