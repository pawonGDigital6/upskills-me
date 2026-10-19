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
	<div class="faq-help-card flex flex-col items-start sm:gap-6 gap-[11px] sm:rounded-card rounded-2xl border border-gray-100 sm:bg-gray-25 bg-[#FDFDFF] sm:p-4 p-[24px_26px_24px_24px] shadow-[0_1px_1px_rgb(10_13_18/5%)]">
		<div class="flex flex-col sm:gap-4 gap-3 text-lg">
			<p class="font-medium text-gray-950"><?php echo esc_html( $card['title'] ); ?></p>
			<?php if ( ! empty( $card['text'] ) ) : ?>
				<p class="text-gray-500 max-sm:text-base"><?php echo esc_html( $card['text'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		upskill_button(
			isset( $card['button'] ) ? $card['button'] : array(),
			array(
				'variant' => 'light',
				'icon'    => 'arrow-up-right',
				'class'   => 'min-h-[50px]',
			)
		);
		?>
	</div>
<?php endif; ?>
