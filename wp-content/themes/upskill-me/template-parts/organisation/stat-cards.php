<?php
/**
 * Organisation pages: a row of count cards (Figma Org dashboard 49325:16970,
 * Courses / Subscriptions 49325:17490). Label (caps), number, and on the
 * dashboard's first row a line of text and a link.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type array[] $items { label, value (int), text (optional), link (optional { url, title }) }
 *     @type string  $class Extra classes for the list.
 * }
 */

$upskill_items = $args['items'] ?? array();

if ( ! $upskill_items ) {
	return;
}
?>
<ul class="org-stats grid grid-cols-2 sm:gap-3 gap-2 lg:grid-cols-4 lg:gap-4 <?php echo esc_attr( $args['class'] ?? '' ); ?>" role="list">
	<?php foreach ( $upskill_items as $upskill_item ) : ?>
		<li class="org-stats__card flex flex-col rounded-2xl border border-gray-900/10 bg-white p-4 lg:p-5">
			<p class="org-stats__label text-xs leading-[1.6] font-medium text-[#6b6b72] uppercase lg:text-sm"><?php echo esc_html( $upskill_item['label'] ); ?></p>
			<p class="org-stats__value pt-3 pb-2 text-[2rem] leading-none font-medium tracking-[-0.01em] text-gray-950 lg:text-[2.25rem]"><?php echo esc_html( number_format_i18n( (int) $upskill_item['value'] ) ); ?></p>
			<?php if ( ! empty( $upskill_item['text'] ) ) : ?>
				<p class="org-stats__text text-sm leading-[1.5] text-gray-500"><?php echo esc_html( $upskill_item['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $upskill_item['link']['url'] ) ) : ?>
				<a class="org-stats__link group mt-auto inline-flex items-center gap-[7px] self-start pt-4 text-sm font-medium text-brand-700" href="<?php echo esc_url( $upskill_item['link']['url'] ); ?>">
					<?php echo esc_html( $upskill_item['link']['title'] ); ?>
					<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
				</a>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
