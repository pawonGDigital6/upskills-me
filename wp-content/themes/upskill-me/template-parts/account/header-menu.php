<?php
/**
 * Header account icon and menu (Figma 49398:107717), signed in or out: on
 * hover with a mouse, on click or tap everywhere
 * (src/global/js/components/navigation.js). Rows come from
 * upskill_account_menu_items(); signed-in menus end with Log out. The rows
 * reuse the mega panel's row styles. With no rows at all (no pages set up)
 * the icon falls back to a plain link.
 *
 * @package upskill-me
 */

$upskill_link  = upskill_account_link();
$upskill_items = upskill_account_menu_items();
$upskill_user  = wp_get_current_user();
$upskill_name  = $upskill_user->exists() ? ( $upskill_user->first_name ? $upskill_user->first_name : $upskill_user->display_name ) : '';

// Signed in: the person's initials replace the outline icon (first + last name, else the display name).
$upskill_initials = '';
$upskill_role     = '';

if ( $upskill_user->exists() ) {
	$upskill_parts    = array_filter( array( $upskill_user->first_name, $upskill_user->last_name ) );
	$upskill_parts    = $upskill_parts ? $upskill_parts : preg_split( '/[\s._@-]+/', (string) $upskill_user->display_name, -1, PREG_SPLIT_NO_EMPTY );
	$upskill_initials = mb_strtoupper( implode( '', array_map( static fn( $part ) => mb_substr( $part, 0, 1 ), array_slice( array_values( $upskill_parts ), 0, 2 ) ) ) );

	if ( user_can( $upskill_user, 'manage_options' ) ) {
		$upskill_role = __( 'Administrator', 'upskill-me' );
	} elseif ( function_exists( 'upskill_is_org_admin' ) && upskill_is_org_admin( $upskill_user->ID ) ) {
		$upskill_role = __( 'Organisation admin', 'upskill-me' );
	} elseif ( function_exists( 'upskill_is_learner' ) && upskill_is_learner( $upskill_user->ID ) ) {
		$upskill_role = __( 'Learner', 'upskill-me' );
	} else {
		$upskill_role = $upskill_user->user_email;
	}
}

if ( ! $upskill_items ) :
	if ( empty( $upskill_link['url'] ) ) {
		return;
	}
	?>
	<a class="site-header__icon"<?php echo upskill_link_attributes( $upskill_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
		<span class="screen-reader-text"><?php echo esc_html( $upskill_link['title'] ? $upskill_link['title'] : __( 'Your account', 'upskill-me' ) ); ?></span>
		<?php upskill_icon( 'user' ); ?>
	</a>
	<?php
	return;
endif;
?>
<div class="account-menu" data-account-menu>
	<button class="site-header__icon account-menu__trigger" type="button" aria-expanded="false" aria-controls="account-menu" data-account-trigger>
		<?php if ( $upskill_user->exists() ) : ?>
			<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: user's first name. */ __( 'Your account (signed in as %s)', 'upskill-me' ), $upskill_name ) ); ?></span>
			<?php // Signed in, whatever the role: the person's initials instead of the outline icon. ?>
			<span class="account-menu__avatar" aria-hidden="true"><?php echo esc_html( $upskill_initials ); ?></span>
		<?php else : ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Your account', 'upskill-me' ); ?></span>
			<?php upskill_icon( 'user' ); ?>
		<?php endif; ?>
	</button>
	<div class="account-menu__panel" id="account-menu" hidden data-account-panel>
		<?php if ( $upskill_user->exists() ) : ?>
			<?php // Who is signed in heads the menu, so it shows on hover (mouse) and on tap (touch) alike. ?>
			<div class="account-menu__profile">
				<span class="account-menu__avatar account-menu__avatar--lg" aria-hidden="true"><?php echo esc_html( $upskill_initials ); ?></span>
				<span class="min-w-0">
					<span class="block truncate text-base leading-[1.4] font-medium text-gray-950">
						<?php
						/* translators: %s: user's first name. */
						echo esc_html( sprintf( __( 'Hello, %s', 'upskill-me' ), $upskill_name ) );
						?>
					</span>
					<span class="block truncate text-sm leading-[1.4] text-gray-500"><?php echo esc_html( $upskill_role ); ?></span>
				</span>
			</div>
		<?php endif; ?>
		<ul class="mega__list account-menu__list">
			<?php foreach ( $upskill_items as $upskill_item ) : ?>
				<li>
					<a class="mega__row account-menu__row" href="<?php echo esc_url( $upskill_item['url'] ); ?>">
						<span class="mega__row-text flex justify-between gap-2">
							<span class="mega__row-title block text-xl leading-[1.5em] font-medium text-gray-950"><?php echo esc_html( $upskill_item['title'] ); ?></span>
							<span class="mega__go" aria-hidden="true"><?php upskill_icon( 'arrow-up-right' ); ?></span>
						</span>
						<span class="mega__row-desc block text-base leading-[1.50em] text-gray-500"><?php echo esc_html( $upskill_item['text'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( is_user_logged_in() ) : ?>
			<a class="account-menu__logout" href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log out', 'upskill-me' ); ?></a>
		<?php endif; ?>
	</div>
</div>
