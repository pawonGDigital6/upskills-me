<?php
/**
 * The template for displaying the footer.
 *
 * The "Get a head start" call to action band, then the footer. Figma: Homepage
 * "Frame 38" (49325:14551, 1440) and "Frame 114" (49343:29335, 390). The band
 * appears above the footer on every marketing page and is switched off per
 * page ("Hide footer call to action") on account and checkout screens.
 *
 * Everything here comes from Site Settings and menus, never from the post.
 *
 * @package upskill-me
 */

$upskill_band   = upskill_hide_footer_cta() ? array() : upskill_option( 'footer_cta', array() );
$upskill_social = upskill_social_links();
$upskill_legal  = upskill_menu_tree( 'footer-legal' );
?>
</div><!-- #page -->

<?php if ( ! empty( $upskill_band['heading'] ) ) : ?>
	<?php
	$upskill_video  = ! empty( $upskill_band['background_video']['url'] ) ? $upskill_band['background_video']['url'] : '';
	$upskill_poster = ! empty( $upskill_band['background_image'] ) ? $upskill_band['background_image'] : false;
	?>
	<section class="cta-band is-dark" aria-labelledby="cta-band-heading">
		<div class="cta-band__media" aria-hidden="true">
			<?php upskill_image( $upskill_poster, 'full', array( 'class' => 'cta-band__poster', 'alt' => '', 'loading' => 'lazy' ) ); ?>
			<?php if ( $upskill_video ) : ?>
				<?php // Loaded only once the band nears the viewport (deferred-video.js); hidden for reduced motion. ?>
				<video class="cta-band__video" muted loop playsinline preload="none" data-deferred-video data-src="<?php echo esc_url( $upskill_video ); ?>"></video>
			<?php endif; ?>
		</div>
		<div class="container cta-band__inner">
			<?php upskill_eyebrow( isset( $upskill_band['eyebrow'] ) ? $upskill_band['eyebrow'] : '', 'book-open', 'eyebrow--dark' ); ?>
			<h2 class="heading cta-band__heading text-h2" id="cta-band-heading"><?php echo wp_kses( $upskill_band['heading'], upskill_inline_kses() ); ?></h2>
			<?php if ( ! empty( $upskill_band['text'] ) ) : ?>
				<p class="cta-band__text"><?php echo esc_html( $upskill_band['text'] ); ?></p>
			<?php endif; ?>
			<div class="cta-band__actions">
				<?php
				upskill_button( isset( $upskill_band['primary_button'] ) ? $upskill_band['primary_button'] : array(), array( 'size' => 'lg' ) );
				upskill_button(
					isset( $upskill_band['secondary_button'] ) ? $upskill_band['secondary_button'] : array(),
					array(
						'variant' => 'outline',
						'size'    => 'lg',
						'icon'    => 'play',
					)
				);
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<footer class="site-footer" id="colophon">
	<div class="container">
		<div class="site-footer__grid">
			<div class="site-footer__brand">
				<a class="site-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php echo esc_url( upskill_theme_image( 'logo-white.webp' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="185" height="72" loading="lazy">
				</a>
				<?php if ( upskill_option( 'footer_text' ) ) : ?>
					<p class="site-footer__text"><?php echo esc_html( upskill_option( 'footer_text' ) ); ?></p>
				<?php endif; ?>
				<?php upskill_button( upskill_option( 'footer_button' ), array( 'variant' => 'glass', 'size' => 'lg' ) ); ?>
			</div>
			<?php
			upskill_render_footer_menu( 'footer-industries', 'site-footer__nav--industries' );
			upskill_render_footer_menu( 'footer-categories' );
			upskill_render_footer_menu( 'footer-resources' );
			upskill_render_footer_menu( 'footer-company' );
			upskill_render_footer_menu( 'footer-account' );
			?>
		</div>

		<div class="site-footer__bottom">
			<p class="site-footer__copyright">
				<?php
				$upskill_copyright = upskill_option( 'copyright' );
				echo esc_html( $upskill_copyright ? str_replace( '{year}', wp_date( 'Y' ), $upskill_copyright ) : '© ' . wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) );
				?>
			</p>
			<?php if ( $upskill_legal ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Legal', 'upskill-me' ); ?>">
					<ul class="site-footer__legal">
						<?php foreach ( $upskill_legal as $upskill_item ) : ?>
							<li><a<?php echo upskill_menu_item_link_atts( $upskill_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $upskill_item->title ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
			<?php if ( $upskill_social ) : ?>
				<ul class="site-footer__social">
					<?php foreach ( $upskill_social as $upskill_link ) : ?>
						<li>
							<a href="<?php echo esc_url( $upskill_link['url'] ); ?>" target="_blank" rel="noopener">
								<span class="screen-reader-text"><?php echo esc_html( $upskill_link['label'] ); ?></span>
								<?php upskill_icon( $upskill_link['network'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
