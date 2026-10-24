<?php
/**
 * Training Library block.
 *
 * Figma: Industry Hub 49325:14592, Categories 49325:14599 (390: 49347:88528):
 * a split section header over the training library, as on the industry and
 * category pages ("Explore by category / Find the training you need next.").
 *
 * The filters and results are template-parts/library/browser.php (LearnDash
 * courses; GET filters, updated in place by js/components/library.js), here
 * with nothing ticked. One per page: the form is #results.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

if ( ! function_exists( 'upskill_library_request' ) ) {
	return;
}

$attributes = upskill_block_attributes( $block, 'block-training-library bg-brand-25 pt-12 lg:pt-20' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'button' => false,
			)
		);
		?>
	</div>
	<?php
	get_template_part(
		'template-parts/library/browser',
		null,
		array(
			'request' => upskill_library_request(),
			'base'    => get_permalink(),
		)
	);
	?>
</section>
