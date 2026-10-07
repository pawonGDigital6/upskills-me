<?php
/**
 * Location Map block.
 *
 * Figma: Contact 49325:20173 (1440) / 49375:115528 (390).
 *
 * A centred section header over a Google map of the office. The map loads
 * lazily in an iframe (no API key); a link opens the same spot in Google Maps.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$place = trim( (string) get_field( 'place' ) );
$zoom  = (int) get_field( 'zoom' );
$zoom  = $zoom >= 3 && $zoom <= 21 ? $zoom : 16;

$attributes = upskill_block_attributes( $block, 'block-location-map section-y bg-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'center',
				'button' => false,
				'reveal' => true,
				'class'  => 'lg:mx-auto lg:max-w-[642px]',
			)
		);
		?>

		<?php if ( $place ) : ?>
			<?php
			$embed = add_query_arg(
				array(
					'q'      => rawurlencode( $place ),
					'z'      => $zoom,
					'hl'     => 'en-AU',
					'output' => 'embed',
				),
				'https://maps.google.com/maps'
			);
			$open  = add_query_arg(
				array(
					'api'   => 1,
					'query' => rawurlencode( $place ),
				),
				'https://www.google.com/maps/search/'
			);
			?>
			<?php // 1360x462 as drawn; taller below 1100px so the map stays usable, and 356x461 on phones. ?>
			<div class="reveal mt-12 overflow-hidden rounded-[32px] border border-gray-200 bg-gray-100 shadow-xs" style="--i:1">
				<iframe class="block aspect-[356/461] w-full sm:aspect-[16/10] min-[1100px]:aspect-[1360/462]" src="<?php echo esc_url( $embed ); ?>" title="<?php echo esc_attr( get_field( 'map_title' ) ? get_field( 'map_title' ) : __( 'Map showing our location', 'upskill-me' ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
			</div>
			<p class="pt-4 lg:text-center">
				<?php
				upskill_text_link(
					array(
						'title'  => __( 'Open in Google Maps', 'upskill-me' ),
						'url'    => $open,
						'target' => '_blank',
					),
					'text-brand-700'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</section>
