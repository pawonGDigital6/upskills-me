<?php
/**
 * Template Name: Store screen
 *
 * WooCommerce's cart and checkout (classic shortcodes, which LearnDash Group
 * Registration's seat purchase relies on) inside the site frame: breadcrumb,
 * a heading as drawn (Figma Cart 49325:15876, Post Checkout Flow 49325:20216),
 * then WooCommerce's own markup, refined by src/global/scss/plugins/woocommerce/.
 *
 * @package upskill-me
 */

get_header();

$upskill_received = function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
$upskill_heading  = get_the_title();
$upskill_intro    = '';

if ( $upskill_received ) {
	$upskill_heading = __( 'Your training is ready.', 'upskill-me' );
	$upskill_intro   = __( 'Thanks for choosing Upskillme. Your order details are below.', 'upskill-me' );
} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
	$upskill_heading = __( 'Review your order.', 'upskill-me' );
	$upskill_intro   = __( 'Check your training and seats. You assign seats to your people after checkout - no learner details needed now.', 'upskill-me' );
} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
	$upskill_heading = __( 'Complete your order.', 'upskill-me' );
	$upskill_intro   = __( 'Your organisation account is created with your order, so you can assign seats straight away.', 'upskill-me' );
}
?>
	<main id="primary" class="site-main store-page bg-gray-25">
		<div class="container pt-6 pb-16 lg:pt-12 lg:pb-24">
			<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
				<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
				<span aria-hidden="true">/</span>
				<span class="text-gray-950" aria-current="page"><?php the_title(); ?></span>
			</nav>
			<h1 class="pt-6 text-h3 font-medium text-gray-950 lg:pt-8"><?php echo esc_html( $upskill_heading ); ?></h1>
			<?php if ( $upskill_intro ) : ?>
				<p class="max-w-[640px] pt-3 text-base text-gray-500"><?php echo esc_html( $upskill_intro ); ?></p>
			<?php endif; ?>
			<div class="store-content mt-8 lg:mt-12">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
	</main>
<?php
get_footer();
