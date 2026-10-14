<?php
/**
 * Account screens: opening of the page (no site header, as Figma "Log in"
 * 49325:21367 / 49375:115106 draws it), up to and including the logo.
 *
 * Desktop: white page, the 360px column centred in the viewport between 96px
 * and 48px of padding. Phone: grey page, 46px / 16px padding.
 *
 * @package upskill-me
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'account-screen' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'upskill-me' ); ?></a>

<main id="primary" class="flex min-h-svh flex-col items-center justify-start bg-gray-25 px-4 py-[46px] sm:justify-center sm:bg-white sm:px-8 sm:pt-24 sm:pb-12">
	<div class="flex w-full max-w-[360px] flex-col gap-8">
		<a class="self-center" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( upskill_theme_image( 'logo.webp' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="192" height="44" loading="eager">
		</a>
