<?php
/**
 * Template Name: Log in
 *
 * The sign-in page for learners and organisations. Figma: "Log in"
 * 49325:21367 (1440) / 49375:115106 (390).
 *
 * The logo, the page's own heading and text (page editor), then the sign-in
 * form and the "Sign up" link (shown while sign-up is open). Someone already
 * signed in sees where to continue instead. Routing: inc/lms-accounts.php.
 *
 * @package upskill-me
 */

get_template_part( 'template-parts/account/document-start' );

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/account/intro' );
endwhile;

if ( is_user_logged_in() ) :
	$upskill_user = wp_get_current_user();
	?>
	<div class="flex flex-col gap-6">
		<p class="rounded-lg border border-line bg-brand-50 px-3.5 py-3 text-center text-sm text-gray-800" role="status">
			<?php
			/* translators: %s: the user's display name. */
			printf( esc_html__( 'You are signed in as %s.', 'upskill-me' ), '<strong class="font-medium">' . esc_html( $upskill_user->display_name ) . '</strong>' );
			?>
		</p>
		<?php
		upskill_button(
			array(
				'url'   => upskill_login_destination( $upskill_user ),
				'title' => __( 'Continue', 'upskill-me' ),
			),
			array( 'class' => 'min-h-[50px] w-full justify-center' )
		);
		?>
		<a class="self-center text-base font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Sign out', 'upskill-me' ); ?></a>
	</div>
	<?php
else :
	get_template_part( 'template-parts/account/login-form' );

	if ( get_option( 'users_can_register' ) ) :
		?>
		<p class="text-center text-base text-gray-500">
			<?php esc_html_e( 'Don’t have an account?', 'upskill-me' ); ?>
			<a class="ml-1 font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Sign up', 'upskill-me' ); ?></a>
		</p>
		<?php
	endif;
endif;

get_template_part( 'template-parts/account/document-end' );
