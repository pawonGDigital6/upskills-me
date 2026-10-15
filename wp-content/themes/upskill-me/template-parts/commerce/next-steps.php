<?php
/**
 * "What happens next" after a purchase (Figma Post Checkout Flow 49325:20216).
 *
 * Shown under WooCommerce's order confirmation. A group purchase makes the
 * buyer a group leader (Group Registration), so they set up their team; anyone
 * else goes to their learning.
 *
 * @package upskill-me
 *
 * @var array $args { @type WC_Order $order The order. }
 */

$upskill_order  = $args['order'];
$upskill_leader = function_exists( 'learndash_is_group_leader_user' ) && learndash_is_group_leader_user( get_current_user_id() );
$upskill_team   = function_exists( 'upskill_groups_dashboard_url' ) ? upskill_groups_dashboard_url() : home_url( '/groups-dashboard/' );
$upskill_learn  = function_exists( 'upskill_learner_dashboard_url' ) ? upskill_learner_dashboard_url() : home_url( '/my-learning/' );
$upskill_paid   = $upskill_order->has_status( array( 'completed', 'processing' ) );

$upskill_paths = array(
	array(
		'title' => __( 'Set up your team', 'upskill-me' ),
		'text'  => __( 'Assign your purchased seats to your people from your organisation account.', 'upskill-me' ),
		'steps' => array( __( 'Open your organisation account', 'upskill-me' ), __( 'Assign seats to your people', 'upskill-me' ), __( 'Track their progress', 'upskill-me' ) ),
		'link'  => array( 'url' => $upskill_team, 'title' => __( 'Enrol Your Team', 'upskill-me' ) ),
		'main'  => $upskill_leader,
	),
	array(
		'title' => __( 'Learning yourself?', 'upskill-me' ),
		'text'  => __( 'Go to your account to start your training and learn at your own pace.', 'upskill-me' ),
		'steps' => array( __( 'Open your account', 'upskill-me' ), __( 'Select your training', 'upskill-me' ), __( 'Start learning', 'upskill-me' ) ),
		'link'  => array( 'url' => $upskill_learn, 'title' => __( 'Continue to Your Account', 'upskill-me' ) ),
		'main'  => ! $upskill_leader,
	),
);
?>
<section class="order-next mt-12" aria-labelledby="order-next-heading">
	<div class="flex flex-col items-start gap-4">
		<?php upskill_eyebrow( __( 'What happens next', 'upskill-me' ), 'flowchart' ); ?>
		<h2 class="text-h3 font-medium text-gray-950" id="order-next-heading"><?php esc_html_e( 'You’re ready to get started.', 'upskill-me' ); ?></h2>
		<p class="text-base text-gray-500">
			<?php
			echo esc_html(
				$upskill_paid
					? __( 'Your next step depends on whether you’re completing training yourself or managing training for your team.', 'upskill-me' )
					: __( 'Your seats are reserved and become available as soon as your payment is confirmed. We’ll email you when they are ready.', 'upskill-me' )
			);
			?>
		</p>
	</div>
	<div class="mt-8 grid gap-4 md:grid-cols-2">
		<?php foreach ( $upskill_paths as $upskill_path ) : ?>
			<div class="flex flex-col rounded-[22px] border p-6 lg:p-8 <?php echo $upskill_path['main'] ? 'border-brand-300 bg-brand-25' : 'border-line bg-white'; ?>">
				<h3 class="text-xl font-medium text-gray-950"><?php echo esc_html( $upskill_path['title'] ); ?></h3>
				<p class="pt-2 text-base text-gray-500"><?php echo esc_html( $upskill_path['text'] ); ?></p>
				<ol class="mt-5 flex flex-col gap-3">
					<?php foreach ( $upskill_path['steps'] as $upskill_i => $upskill_step ) : ?>
						<li class="flex items-center gap-3 text-base text-gray-950">
							<span class="grid size-7 flex-none place-items-center rounded-full bg-white text-sm font-medium text-brand-700 ring-1 ring-line"><?php echo esc_html( str_pad( (string) ( $upskill_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<?php echo esc_html( $upskill_step ); ?>
						</li>
					<?php endforeach; ?>
				</ol>
				<?php upskill_button( $upskill_path['link'], array( 'variant' => $upskill_path['main'] ? 'primary' : 'light', 'class' => 'mt-6 self-start' ) ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
