<?php
/**
 * The header for our theme.
 *
 * Announcement bar, then the sticky nav bar. Figma: Homepage "Frame 28"
 * (49325:14552, 1440) and "Frame 94" (49343:29334, 390). The mobile drawer is
 * 49475:48697 / 49475:48748.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package upskill-me
 */

$upskill_announcement = upskill_option( 'announcement', array() );
$upskill_cta          = upskill_option( 'header_cta' );
$upskill_account      = upskill_option( 'account_link' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'upskill-me' ); ?></a>

<?php if ( ! empty( $upskill_announcement['status'] ) && ! empty( $upskill_announcement['message'] ) ) : ?>
	<div class="announcement" data-announcement>
		<div class="container announcement__inner">
			<?php if ( ! empty( $upskill_announcement['tag'] ) ) : ?>
				<span class="tag tag--free announcement__tag"><?php echo esc_html( $upskill_announcement['tag'] ); ?></span>
			<?php endif; ?>
			<p class="announcement__message">
				<span class="announcement__text"><?php echo esc_html( $upskill_announcement['message'] ); ?></span>
				<?php if ( ! empty( $upskill_announcement['link']['url'] ) ) : ?>
					<a class="announcement__link"<?php echo upskill_link_attributes( $upskill_announcement['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
						<span class="announcement__link-label"><?php echo esc_html( $upskill_announcement['link']['title'] ); ?></span>
						<?php upskill_icon( 'arrow-up-right', 'announcement__icon' ); ?>
					</a>
				<?php endif; ?>
			</p>
			<button class="announcement__close" type="button" data-announcement-close>
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss announcement', 'upskill-me' ); ?></span>
				<?php upskill_icon( 'close' ); ?>
			</button>
		</div>
	</div>
<?php endif; ?>

<header class="site-header" id="masthead" data-site-header>
	<div class="container site-header__bar">
		<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( upskill_theme_image( 'logo.webp' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="192" height="44">
		</a>

		<nav class="site-header__nav" id="site-navigation" aria-label="<?php esc_attr_e( 'Primary', 'upskill-me' ); ?>">
			<?php upskill_render_primary_nav(); ?>
		</nav>

		<div class="site-header__actions">
			<button class="site-header__icon" type="button" aria-haspopup="dialog" aria-controls="site-search" data-search-open>
				<span class="screen-reader-text"><?php esc_html_e( 'Search training', 'upskill-me' ); ?></span>
				<?php upskill_icon( 'search' ); ?>
			</button>
			<?php if ( ! empty( $upskill_account['url'] ) ) : ?>
				<a class="site-header__icon"<?php echo upskill_link_attributes( $upskill_account ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
					<span class="screen-reader-text"><?php echo esc_html( $upskill_account['title'] ? $upskill_account['title'] : __( 'Your account', 'upskill-me' ) ); ?></span>
					<?php upskill_icon( 'user' ); ?>
				</a>
			<?php endif; ?>
			<?php upskill_button( $upskill_cta, array( 'size' => 'sm', 'class' => 'site-header__cta' ) ); ?>
			<button class="site-header__icon site-header__burger" type="button" aria-expanded="false" aria-controls="site-drawer" data-drawer-toggle>
				<span class="screen-reader-text" data-drawer-label data-open-label="<?php esc_attr_e( 'Open menu', 'upskill-me' ); ?>" data-close-label="<?php esc_attr_e( 'Close menu', 'upskill-me' ); ?>"><?php esc_html_e( 'Open menu', 'upskill-me' ); ?></span>
				<span class="site-header__burger-lines" aria-hidden="true"></span>
			</button>
		</div>
	</div>
	<?php upskill_render_mobile_nav(); ?>
</header>

<dialog class="search-dialog" id="site-search" aria-label="<?php esc_attr_e( 'Search training', 'upskill-me' ); ?>" data-search-dialog>
	<div class="search-dialog__panel">
		<form class="search-dialog__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="site-search-input"><?php esc_html_e( 'Search for training', 'upskill-me' ); ?></label>
			<?php upskill_icon( 'search', 'search-dialog__icon' ); ?>
			<input class="search-dialog__input" id="site-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search training, industries or topics', 'upskill-me' ); ?>" autocomplete="off" required>
			<button class="btn btn--primary btn--md" type="submit">
				<span class="btn__label"><?php esc_html_e( 'Search', 'upskill-me' ); ?></span>
				<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
			</button>
		</form>
		<form method="dialog">
			<button class="search-dialog__close" type="submit">
				<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'upskill-me' ); ?></span>
				<?php upskill_icon( 'close' ); ?>
			</button>
		</form>
	</div>
</dialog>

<div id="page" class="site">
