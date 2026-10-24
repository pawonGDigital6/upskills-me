<?php
/**
 * Header account icon. Signed out it links to Log in; signed in it opens the
 * account menu (Figma 49398:107717): on hover with a mouse, on click or tap
 * everywhere (src/global/js/components/navigation.js). The rows reuse the
 * mega panel's row styles.
 *
 * @package upskill-me
 */

$upskill_link  = upskill_account_link();
$upskill_items = upskill_account_menu_items();

if ( empty( $upskill_link['url'] ) ) {
	return;
}

if ( ! $upskill_items ) : ?>
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
		<span class="screen-reader-text"><?php esc_html_e( 'Your account', 'upskill-me' ); ?></span>
		<?php upskill_icon( 'user' ); ?>
	</button>
	<div class="account-menu__panel" id="account-menu" hidden data-account-panel>
		<p class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: user's display name. */ __( 'Signed in as %s', 'upskill-me' ), wp_get_current_user()->display_name ) ); ?></p>
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
		<a class="account-menu__logout" href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log out', 'upskill-me' ); ?></a>
	</div>
</div>
