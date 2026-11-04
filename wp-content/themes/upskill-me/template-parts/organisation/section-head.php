<?php
/**
 * Organisation pages: a section's header (Figma: eyebrow and heading on the
 * left, a 400px intro and optional button on the right from lg; stacked on
 * phones).
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type string $eyebrow Eyebrow text ('' for none).
 *     @type string $icon    Eyebrow icon.
 *     @type string $heading Heading (may hold <em>).
 *     @type string $id      Heading id (for aria-labelledby and links).
 *     @type string $intro   Intro text ('' for none).
 *     @type array  $button  Optional { url, title } (primary pill under the intro).
 * }
 */

$upskill_args = wp_parse_args(
	$args,
	array(
		'eyebrow' => '',
		'icon'    => '',
		'heading' => '',
		'id'      => '',
		'intro'   => '',
		'button'  => array(),
	)
);
?>
<div class="org-head flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between lg:gap-10">
	<div class="flex flex-col items-start gap-3 lg:gap-4">
		<?php
		if ( $upskill_args['eyebrow'] ) {
			upskill_eyebrow( $upskill_args['eyebrow'], $upskill_args['icon'] );
		}
		?>
		<h2 id="<?php echo esc_attr( $upskill_args['id'] ); ?>" class="heading scroll-mt-28 text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em] text-gray-950"><?php echo wp_kses( $upskill_args['heading'], array( 'em' => array(), 'br' => array() ) ); ?></h2>
	</div>
	<?php if ( $upskill_args['intro'] || ! empty( $upskill_args['button']['url'] ) ) : ?>
		<div class="flex flex-col items-start gap-4 lg:w-[400px] lg:flex-none">
			<?php if ( $upskill_args['intro'] ) : ?>
				<p class="text-base leading-[1.5] text-[#6b6b72] lg:text-lead"><?php echo esc_html( $upskill_args['intro'] ); ?></p>
			<?php endif; ?>
			<?php
			if ( ! empty( $upskill_args['button']['url'] ) ) {
				upskill_button( $upskill_args['button'] );
			}
			?>
		</div>
	<?php endif; ?>
</div>
