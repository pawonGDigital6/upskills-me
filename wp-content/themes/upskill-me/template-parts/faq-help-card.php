<?php
/**
 * FAQ help card: "Got questions? We've got answers." beside the questions.
 * Shared by the FAQs block's list and grouped layouts.
 *
 * @param array $args { @type array $card title, text, button. }
 *
 * @package upskill-me
 */

$card = isset( $args['card'] ) ? $args['card'] : array();
?>
<?php if ( ! empty( $card['title'] ) ) : ?>
	<div class="flex flex-col items-start gap-6 rounded-card border border-gray-100 bg-gray-25 p-4 shadow-[0_1px_1px_rgb(10_13_18/5%)]">
		<div class="flex flex-col gap-4 text-lg">
			<p class="font-medium text-gray-950"><?php echo esc_html( $card['title'] ); ?></p>
			<?php if ( ! empty( $card['text'] ) ) : ?>
				<p class="text-gray-500"><?php echo esc_html( $card['text'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		upskill_button(
			isset( $card['button'] ) ? $card['button'] : array(),
			array(
				'variant' => 'light',
				'icon'    => 'play',
				'class'   => 'min-h-[50px]',
			)
		);
		?>
	</div>
<?php endif; ?>
