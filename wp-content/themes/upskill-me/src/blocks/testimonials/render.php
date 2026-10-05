<?php
/**
 * ACF Block template.
 *
 * @param array $block The block settings and attributes.
 * 
 * @package acf-block-demo
 */

// Support custom "anchor" values.
$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'acf-block testimonials';

if ( ! empty( $block['className'] ) ) {
	$class_name .= ' ' . $block['className'];
}

if ( ! empty( $block['align'] ) ) {
	$class_name .= ' align' . $block['align'];
}

$class_name .= generate_block_settings_classnames()['class_name'];

// Block Style.
$style_attr  = '';
$block_style = generate_block_settings_classnames()['block_style'];

if ( $block_style !== '' ) {
	$style_attr = 'style="' . $block_style . '"';
}

?>

<section <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?>" <?php echo $style_attr; ?>>
	<div class="container">
		<div class="section-title">
			<h2>Testimonials</h2>
		</div>
        <div class="testimonial-slider swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <p>Slide 1</p>
                </div>
                <div class="swiper-slide">
                    <p>Slide 2</p>
                </div>
                <div class="swiper-slide">
                    <p>Slide 3</p>
                </div>
                <div class="swiper-slide">
                    <p>Slide 4</p>
                </div>
                <div class="swiper-slide">
                    <p>Slide 5</p>
                </div>
            </div>
        </div>
	</div>
</section>
