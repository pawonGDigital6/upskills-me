<?php
/**
 * Template Name: Store screen
 *
 * WooCommerce's cart and checkout (classic shortcodes, which LearnDash Group
 * Registration's seat purchase relies on), and Group Registration's groups
 * dashboard, inside the site frame: breadcrumb,
 * a heading as drawn (Figma Cart 49325:15876, Post Checkout Flow 49325:20216),
 * then the plugin's own markup, refined by src/global/scss/plugins/ (woocommerce/store,
 * learndash/group-registration) and inc/lms-store.php.
 *
 * Cart and checkout take the Figma cart header (10px breadcrumb, display
 * heading with its highlighted phrase); the cart adds "You might also need
 * these." under the cart. On the order-received page a valid order draws its
 * own header beside the order summary (template-parts/commerce/order-overview.php),
 * so only an unknown or failed order gets the heading here.
 *
 * @package upskill-me
 */

get_header();

$upskill_received = function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
$upskill_cart     = function_exists( 'is_cart' ) && is_cart();
$upskill_checkout = ! $upskill_received && function_exists( 'is_checkout' ) && is_checkout();
$upskill_order    = $upskill_received && function_exists( 'upskill_received_order' ) ? upskill_received_order() : null;
$upskill_header   = ! ( $upskill_order && ! $upskill_order->has_status( 'failed' ) );
$upskill_display  = $upskill_cart || $upskill_checkout || $upskill_received;
$upskill_heading  = esc_html( get_the_title() );
$upskill_intro    = '';
$upskill_crumbs   = array();

if ( function_exists( 'ldgr_get_groups_dashboard_page' ) && (int) ldgr_get_groups_dashboard_page() === get_queried_object_id() ) {
	// Figma "Enrol" 49325:17392.
	$upskill_heading = esc_html__( 'Manage your team.', 'upskill-me' );
	$upskill_intro   = __( 'Add your people to your training, see who is enrolled and follow their progress.', 'upskill-me' );
} elseif ( $upskill_received ) {
	$upskill_heading = __( 'Thank you for <em>your order.</em>', 'upskill-me' );
	$upskill_intro   = __( 'Your confirmation has been emailed to you, and your orders are always in your account.', 'upskill-me' );
} elseif ( $upskill_cart ) {
	$upskill_heading = __( 'Review <em>your order.</em>', 'upskill-me' );
	$upskill_intro   = __( 'Check your training and seats. You assign seats to your people after checkout - no learner details needed now.', 'upskill-me' );
} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
	// My Account is the billing area; the dashboards are /organisation/ and /my-learning/.
	$upskill_heading = esc_html__( 'Orders & billing.', 'upskill-me' );
	$upskill_intro   = __( 'Your orders, invoices and billing details. Your training is on your dashboard.', 'upskill-me' );
} elseif ( $upskill_checkout ) {
	$upskill_heading = __( 'Complete <em>your order.</em>', 'upskill-me' );
	$upskill_intro   = __( 'Your organisation account is created with your order, so you can assign seats straight away.', 'upskill-me' );
	$upskill_crumbs  = array( array( wc_get_cart_url(), __( 'Cart', 'upskill-me' ) ) );
}
?>
	<main id="primary" class="site-main store-page bg-gray-25">
		<?php // Figma: 80px under the header to the cart breadcrumb, 104px to the confirmation badge. ?>
		<div class="container pb-16 lg:pb-24 <?php echo ! $upskill_header ? 'pt-10 lg:pt-[104px]' : ( $upskill_display ? 'pt-6 lg:pt-20' : 'pt-6 lg:pt-12' ); ?>">
			<?php if ( $upskill_header ) : ?>
				<?php if ( $upskill_display ) : ?>
					<?php // Figma Cart: 10px, #666 links, #ccc separators, current page 500 #5d3a8f (no theme tokens). ?>
					<nav class="flex flex-wrap items-center gap-2 text-[10px] leading-[1.5] text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
						<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
						<?php foreach ( $upskill_crumbs as $upskill_crumb ) : ?>
							<span class="text-gray-200" aria-hidden="true">/</span>
							<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_crumb[0] ); ?>"><?php echo esc_html( $upskill_crumb[1] ); ?></a>
						<?php endforeach; ?>
						<span class="text-gray-200" aria-hidden="true">/</span>
						<span class="font-medium text-[#5d3a8f]" aria-current="page"><?php the_title(); ?></span>
					</nav>
					<?php upskill_heading( $upskill_heading, 'h1', 'pt-6 text-h1 font-semibold text-gray-900 lg:pt-9' ); ?>
					<?php if ( $upskill_intro ) : ?>
						<p class="max-w-[666px] pt-4 text-base text-gray-500 lg:pt-6"><?php echo esc_html( $upskill_intro ); ?></p>
					<?php endif; ?>
				<?php else : ?>
					<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
						<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
						<span aria-hidden="true">/</span>
						<span class="text-gray-950" aria-current="page"><?php the_title(); ?></span>
					</nav>
					<h1 class="pt-6 text-h3 font-medium text-gray-950 lg:pt-8"><?php echo wp_kses( $upskill_heading, upskill_inline_kses() ); ?></h1>
					<?php if ( $upskill_intro ) : ?>
						<p class="max-w-[640px] pt-3 text-base text-gray-500"><?php echo esc_html( $upskill_intro ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
			<div class="store-content <?php echo $upskill_header ? ( $upskill_display ? 'mt-8 lg:mt-[72px]' : 'mt-8 lg:mt-12' ) : ''; ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
		<?php
		if ( $upskill_cart ) {
			get_template_part( 'template-parts/commerce/related-training' );
		}
		?>
	</main>
<?php
get_footer();
