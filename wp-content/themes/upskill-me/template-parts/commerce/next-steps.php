<?php
/**
 * "What happens next" after a purchase (Figma Post Checkout Flow 49325:20216).
 *
 * Shown under WooCommerce's order confirmation. A group purchase makes the
 * buyer a group leader (Group Registration), so their card ("Your purchase")
 * is setting up the team; the other card is for learning yourself. Numbered
 * steps: filled purple on the highlighted card, lilac on the other, joined by
 * a short rule.
 *
 * @package upskill-me
 *
 * @var array $args { @type WC_Order $order The order. }
 */

$upskill_order  = $args['order'];
$upskill_leader = function_exists( 'learndash_is_group_leader_user' ) && learndash_is_group_leader_user( get_current_user_id() );
$upskill_team   = function_exists( 'upskill_groups_dashboard_url' ) ? upskill_groups_dashboard_url() : home_url( '/groups-dashboard/' );
$upskill_learn  = function_exists( 'upskill_learner_dashboard_url' ) ? upskill_learner_dashboard_url() : home_url( '/my-learning/' );
$upskill_paid   = $upskill_order->is_paid();

$upskill_paths = array(
	array(
		'tag'   => $upskill_leader ? __( 'Your purchase', 'upskill-me' ) : __( 'Managing a team?', 'upskill-me' ),
		'title' => __( 'Set up your team', 'upskill-me' ),
		'text'  => __( 'Assign your purchased seats to your people and select the appropriate cohort for each learner.', 'upskill-me' ),
		'steps' => array( __( 'Open your organisation account', 'upskill-me' ), __( 'Assign seats to your people', 'upskill-me' ), __( 'Choose the appropriate cohort', 'upskill-me' ) ),
		'link'  => array( 'url' => $upskill_team, 'title' => __( 'Enrol Your Team', 'upskill-me' ) ),
		'main'  => $upskill_leader,
	),
	array(
		'tag'   => $upskill_leader ? __( 'Learning yourself?', 'upskill-me' ) : __( 'Your purchase', 'upskill-me' ),
		'title' => __( 'Start your training', 'upskill-me' ),
		'text'  => __( 'Go to your account to access your selected training and begin learning at your own pace.', 'upskill-me' ),
		'steps' => array( __( 'Open your account', 'upskill-me' ), __( 'Select your training', 'upskill-me' ), __( 'Start learning', 'upskill-me' ) ),
		'link'  => array( 'url' => $upskill_learn, 'title' => __( 'Continue to Your Account', 'upskill-me' ) ),
		'main'  => ! $upskill_leader,
	),
);
?>
<section class="order-next mt-16" aria-labelledby="order-next-heading">
	<?php upskill_eyebrow( __( 'What happens next', 'upskill-me' ), 'trend-up' ); ?>
	<h2 class="pt-4 text-2xl leading-[1.1] font-medium tracking-[-0.01em] text-gray-900" id="order-next-heading"><?php esc_html_e( 'You’re ready to get started.', 'upskill-me' ); ?></h2>
	<p class="pt-5 text-base text-gray-500">
		<?php
		echo esc_html(
			$upskill_paid
				? __( 'Your next step depends on whether you’re completing training yourself or managing training for your team.', 'upskill-me' )
				: __( 'Your seats are reserved and become available as soon as your payment is confirmed. We’ll email you when they are ready.', 'upskill-me' )
		);
		?>
	</p>
	<div class="mt-6 grid gap-5 md:grid-cols-2">
		<?php foreach ( $upskill_paths as $upskill_path ) : ?>
			<?php // Figma: highlighted card #6744a8 border; the other a 10% rule. ?>
			<div class="flex flex-col items-start rounded-[24px] border bg-white p-6 <?php echo $upskill_path['main'] ? 'border-[#6744a8] shadow-card' : 'border-gray-900/10'; ?>">
				<?php if ( $upskill_path['main'] ) : ?>
					<?php upskill_eyebrow( $upskill_path['tag'] ); ?>
				<?php else : ?>
					<span class="rounded-full bg-gray-25 px-[11px] py-1 text-xs leading-[1.62] tracking-[-0.01em] text-[#55555a]"><?php echo esc_html( $upskill_path['tag'] ); ?></span>
				<?php endif; ?>
				<h3 class="pt-4 text-xl font-medium text-gray-950"><?php echo esc_html( $upskill_path['title'] ); ?></h3>
				<p class="pt-2 text-base text-gray-500"><?php echo esc_html( $upskill_path['text'] ); ?></p>
				<ol class="mt-6 flex flex-col">
					<?php foreach ( $upskill_path['steps'] as $upskill_i => $upskill_step ) : ?>
						<li class="flex gap-4">
							<span class="flex flex-none flex-col items-center">
								<span class="grid size-[26px] place-items-center rounded-full text-[10px] leading-[1.62] font-medium tracking-[-0.01em] <?php echo $upskill_path['main'] ? 'bg-[#6744a8] text-white' : 'bg-[#f1edfa] text-[#5a3d8c]'; ?>"><?php echo esc_html( str_pad( (string) ( $upskill_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<?php if ( $upskill_i < count( $upskill_path['steps'] ) - 1 ) : ?>
									<span class="h-[18px] w-px bg-gray-900/10" aria-hidden="true"></span>
								<?php endif; ?>
							</span>
							<span class="text-base text-gray-950"><?php echo esc_html( $upskill_step ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
				<div class="mt-auto pt-6">
					<?php upskill_button( $upskill_path['link'], array( 'variant' => $upskill_path['main'] ? 'primary' : 'light', 'class' => 'min-h-[50px]' ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
