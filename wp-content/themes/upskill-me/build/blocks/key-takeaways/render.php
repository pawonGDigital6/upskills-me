<?php
/**
 * Key Takeaways block (article content).
 *
 * Figma: Blog Detail 49325:16672 — the lilac panel near the end of an article
 * with a short checklist of what to remember.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$items = array_filter( (array) get_field( 'items' ), static fn( $item ) => ! empty( $item['text'] ) );

if ( ! $items && ! get_field( 'title' ) ) {
	return;
}

$attributes = upskill_block_attributes( $block, 'block-key-takeaways my-10 rounded-[20px] border border-[#e2ddf3] bg-brand-25 p-5 sm:p-7' );
?>
<aside<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<?php if ( get_field( 'label' ) ) : ?>
		<p class="text-sm font-medium tracking-[0.08em] text-[#6b2fe3] uppercase lg:font-normal lg:tracking-normal lg:text-brand-700 lg:normal-case"><?php echo esc_html( get_field( 'label' ) ); ?></p>
	<?php endif; ?>
	<?php if ( get_field( 'title' ) ) : ?>
		<?php // The article's contents list names this panel by its label ("Key takeaways"). ?>
		<h2 class="pt-2.5 text-xl font-medium text-[#0e0e14]" data-toc-label="<?php echo esc_attr( get_field( 'label' ) ); ?>"><?php echo esc_html( get_field( 'title' ) ); ?></h2>
	<?php endif; ?>
	<?php if ( $items ) : ?>
		<ul class="flex flex-col gap-3.5 pt-4">
			<?php foreach ( $items as $item ) : ?>
				<li class="flex items-start gap-3 text-base text-gray-950">
					<span class="mt-1 grid size-[22px] flex-none place-items-center rounded-full bg-brand-700 text-white" aria-hidden="true"><?php upskill_icon( 'check', 'size-[11px]' ); ?></span>
					<?php echo esc_html( $item['text'] ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</aside>
