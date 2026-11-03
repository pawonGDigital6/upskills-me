<?php
/**
 * The ticked list under an order summary (Figma Cart 49325:15876 and Post
 * Checkout Flow 49325:20216): 14/21 #666, purple ticks, 10px apart.
 *
 * @package upskill-me
 *
 * @var array $args { @type string $context cart|order. }
 */

$upskill_points = upskill_store_reassurance( $args['context'] ?? 'cart' );
?>
<ul class="store-reassurance mt-5 flex flex-col gap-2.5 border-t border-gray-100 pt-5">
	<?php foreach ( $upskill_points as $upskill_point ) : ?>
		<li class="flex items-start gap-2.5 text-sm text-gray-500">
			<?php upskill_icon( 'tick', 'mt-0.5 h-[15px] w-3.5 flex-none text-brand-700' ); ?>
			<?php echo esc_html( $upskill_point ); ?>
		</li>
	<?php endforeach; ?>
</ul>
