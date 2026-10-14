<?php
/**
 * The sign-in form (Figma "Log in" 49325:21367 / 49375:115106).
 *
 * A standard WordPress login form posting to wp-login.php, written out here so
 * it can carry the design's markup. It applies the same hooks as
 * wp_login_form() (login_form_top / _middle / _bottom), through which
 * LearnDash adds its field: a failed sign-in then returns here with
 * ?login=failed instead of showing wp-login.php. Where the user lands is
 * decided in inc/lms-accounts.php.
 *
 * @package upskill-me
 */

$upskill_login_page = get_permalink();
$upskill_asked      = isset( $_GET['redirect_to'] ) ? wp_validate_redirect( wp_unslash( $_GET['redirect_to'] ), '' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only, validated.
$upskill_redirect   = $upskill_asked ? add_query_arg( 'redirect_to', rawurlencode( $upskill_asked ), $upskill_login_page ) : $upskill_login_page;
$upskill_args       = array(
	'echo'           => true,
	'redirect'       => $upskill_redirect,
	'form_id'        => 'loginform',
	'id_username'    => 'user_login',
	'id_password'    => 'user_pass',
	'id_remember'    => 'rememberme',
	'id_submit'      => 'wp-submit',
	'remember'       => true,
	'value_username' => '',
	'value_remember' => false,
);
$upskill_notice     = '';
$upskill_error      = false;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display-only flags set by WordPress/LearnDash redirects.
if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) {
	$upskill_notice = __( 'That email or password is not right. Please try again.', 'upskill-me' );
	$upskill_error  = true;
} elseif ( isset( $_GET['loggedout'] ) ) {
	$upskill_notice = __( 'You have signed out.', 'upskill-me' );
}
// phpcs:enable

$upskill_field = 'h-[50px] w-full min-w-0 rounded-lg border border-gray-900/20 bg-gray-25 py-3 pr-3 pl-11 text-base text-gray-950 transition-[border-color,background-color,box-shadow] duration-200 placeholder:text-[#999] hover:border-gray-900/30 focus:border-brand-700 focus:bg-white focus:shadow-[0_0_0_3px_rgb(109_66_175/14%)] focus:outline-none';
$upskill_disc  = 'pointer-events-none ml-3 grid size-6 place-items-center self-center justify-self-start rounded-full bg-gray-100 text-gray-800';
?>
<?php if ( $upskill_notice ) : ?>
	<p class="<?php echo esc_attr( $upskill_error ? 'border-[#fecdca] bg-[#fef3f2] text-[#b42318]' : 'border-line bg-brand-50 text-gray-800' ); ?> rounded-lg border px-3.5 py-3 text-sm" role="<?php echo esc_attr( $upskill_error ? 'alert' : 'status' ); ?>">
		<?php echo esc_html( $upskill_notice ); ?>
	</p>
<?php endif; ?>

<form name="loginform" id="loginform" class="flex flex-col gap-6" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
	<?php echo apply_filters( 'login_form_top', '', $upskill_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter, as in wp_login_form(). ?>

	<div class="flex flex-col gap-5">
		<p class="flex flex-col gap-1.5">
			<label class="text-sm font-medium text-gray-900" for="user_login"><?php esc_html_e( 'Email', 'upskill-me' ); ?></label>
			<span class="grid grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1">
				<input class="<?php echo esc_attr( $upskill_field ); ?>" type="text" name="log" id="user_login" autocomplete="username" autocapitalize="off" spellcheck="false" inputmode="email" placeholder="<?php esc_attr_e( 'Enter your email address', 'upskill-me' ); ?>" required>
				<span class="<?php echo esc_attr( $upskill_disc ); ?>" aria-hidden="true"><?php upskill_icon( 'field-mail', 'size-[18px]' ); ?></span>
			</span>
		</p>

		<p class="flex flex-col gap-1.5">
			<label class="text-sm font-medium text-gray-900" for="user_pass"><?php esc_html_e( 'Password', 'upskill-me' ); ?></label>
			<span class="grid grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1">
				<input class="<?php echo esc_attr( $upskill_field ); ?> pr-11" type="password" name="pwd" id="user_pass" autocomplete="current-password" spellcheck="false" placeholder="••••••••" required>
				<span class="<?php echo esc_attr( $upskill_disc ); ?>" aria-hidden="true"><?php upskill_icon( 'field-password', 'size-[18px]' ); ?></span>
				<button class="mr-2 grid size-8 place-items-center self-center justify-self-end rounded-full text-gray-800 transition-colors hover:bg-gray-100" type="button" data-password-toggle data-hide-label="<?php esc_attr_e( 'Hide password', 'upskill-me' ); ?>" aria-controls="user_pass" aria-pressed="false">
					<span class="screen-reader-text"><?php esc_html_e( 'Show password', 'upskill-me' ); ?></span>
					<?php upskill_icon( 'eye-off', 'size-4' ); ?>
					<?php upskill_icon( 'eye', 'hidden size-4' ); ?>
				</button>
			</span>
		</p>
	</div>

	<?php echo apply_filters( 'login_form_middle', '', $upskill_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter. ?>

	<div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
		<label class="flex cursor-pointer items-center gap-2 text-base text-gray-500" for="rememberme">
			<span class="grid grid-cols-1 grid-rows-1 *:col-start-1 *:row-start-1">
				<input class="peer size-4 cursor-pointer appearance-none rounded-[4px] border border-[#d5d7da] bg-white transition-colors checked:border-brand-700 checked:bg-brand-700" type="checkbox" name="rememberme" id="rememberme" value="forever">
				<?php upskill_icon( 'check', 'pointer-events-none hidden size-3 place-self-center text-white peer-checked:block' ); ?>
			</span>
			<?php esc_html_e( 'Remember me', 'upskill-me' ); ?>
		</label>
		<a class="text-base font-medium text-brand-700 underline-offset-4 hover:underline" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password', 'upskill-me' ); ?></a>
	</div>

	<input type="hidden" name="redirect_to" value="<?php echo esc_url( $upskill_redirect ); ?>">
	<button class="btn btn--primary btn--md min-h-[50px] w-full justify-center" type="submit" name="wp-submit" id="wp-submit">
		<span class="btn__label"><?php esc_html_e( 'Sign in', 'upskill-me' ); ?></span>
		<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
	</button>

	<?php echo apply_filters( 'login_form_bottom', '', $upskill_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter. ?>
</form>
