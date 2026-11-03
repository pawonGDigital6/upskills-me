<?php
/**
 * Order received: "Need help?" (Figma Post Checkout Flow 49325:20216): two
 * cards, to the FAQs and to Contact.
 *
 * @package upskill-me
 */

$upskill_cards = array(
	array(
		'icon'  => 'help-circle',
		'title' => __( 'Need help with your training?', 'upskill-me' ),
		'text'  => __( 'Find answers to common questions about accessing and completing your training.', 'upskill-me' ),
		'link'  => array(
			'url'   => home_url( '/faqs/' ),
			'title' => __( 'Visit FAQs', 'upskill-me' ),
		),
	),
	array(
		'icon'  => 'chat',
		'title' => __( 'Need help with your account?', 'upskill-me' ),
		'text'  => __( 'Get help with your account, enrolment or organisation training.', 'upskill-me' ),
		'link'  => array(
			'url'   => home_url( '/contact/' ),
			'title' => __( 'Contact Support', 'upskill-me' ),
		),
	),
);
?>
<section class="order-help mt-16" aria-labelledby="order-help-heading">
	<?php upskill_eyebrow( __( 'Need help?', 'upskill-me' ), 'help-ring' ); ?>
	<h2 class="pt-4 text-2xl leading-[1.1] font-medium tracking-[-0.01em] text-gray-900" id="order-help-heading"><?php esc_html_e( 'We’re here if you need us.', 'upskill-me' ); ?></h2>
	<p class="pt-5 text-[15px] leading-[1.62] tracking-[-0.01em] text-[#55555a]"><?php esc_html_e( 'Have a question about your training, account or next steps? Find the information you need or get in touch with the Upskillme team.', 'upskill-me' ); ?></p>
	<div class="mt-6 grid gap-4 md:grid-cols-2">
		<?php foreach ( $upskill_cards as $upskill_card ) : ?>
			<div class="flex flex-col rounded-2xl border border-gray-900/10 bg-white p-6">
				<span class="grid size-10 place-items-center rounded-[11px] bg-[#f1edfa] text-brand-700" aria-hidden="true"><?php upskill_icon( $upskill_card['icon'], 'size-[19px]' ); ?></span>
				<h3 class="pt-4 text-xl font-medium text-gray-950"><?php echo esc_html( $upskill_card['title'] ); ?></h3>
				<p class="pt-2 text-base text-gray-500"><?php echo esc_html( $upskill_card['text'] ); ?></p>
				<?php upskill_text_link( $upskill_card['link'], 'mt-auto pt-6 text-[13px] font-medium text-[#6744a8]' ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
