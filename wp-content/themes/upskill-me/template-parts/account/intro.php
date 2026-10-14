<?php
/**
 * Account screens: the page's heading and supporting text.
 *
 * Written in the page editor as a Heading (H1; italic marks the purple phrase,
 * "Log in to *your account*") and a Paragraph. A page whose content has no
 * heading shows its title instead. Figma: 36/39.6 medium, -1% tracking;
 * supporting text 18/27, 12px below.
 *
 * @package upskill-me
 */

$upskill_content = get_the_content();
?>
<div class="flex flex-col gap-3 text-center [&_em]:not-italic [&_em]:text-brand-700 [&_h1]:text-[2.25rem] [&_h1]:leading-[1.1] [&_h1]:font-medium [&_h1]:tracking-[-0.01em] [&_h1]:text-gray-900 [&_p]:text-lg [&_p]:text-gray-700">
	<?php if ( ! has_block( 'core/heading' ) ) : ?>
		<h1><?php the_title(); ?></h1>
	<?php endif; ?>
	<?php
	if ( '' !== trim( $upskill_content ) ) {
		the_content();
	}
	?>
</div>
