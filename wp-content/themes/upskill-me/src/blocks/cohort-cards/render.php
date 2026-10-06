<?php
/**
 * Cohort Cards block.
 *
 * Figma: Homepage section 49325:14506 (1440) / Section 49196:7754 (390).
 *
 * One card per selected Level (cohort) term. The photograph and the one-line
 * promise ("Do the task correctly, every time.") are fields on the term, so a
 * level reads the same wherever it is presented.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-cohort-cards section-y' );
$terms      = upskill_selected_terms( get_field( 'cohorts' ), 'cohort' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php upskill_section_header(); ?>

		<?php if ( $terms ) : ?>
			<ul class="mt-stack grid gap-4 md:grid-cols-3">
				<?php foreach ( $terms as $index => $term ) : ?>
					<?php $tagline = get_field( 'tagline', $term ); ?>
					<li class="reveal group flex flex-col overflow-hidden rounded-card border border-gray-900/10 bg-white lg:min-h-[746px]" style="--i:<?php echo (int) min( $index, 3 ); ?>">
						<div class="aspect-[358/340] overflow-hidden bg-[#ededef] md:aspect-[443/534]">
							<?php
							upskill_image(
								get_field( 'card_image', $term ),
								'upskill-card',
								array(
									'class' => 'size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
									'sizes' => '(min-width: 768px) 33vw, 92vw',
								)
							);
							?>
						</div>
						<div class="flex flex-1 flex-col items-start gap-10 p-5">
							<div class="flex flex-col items-start gap-3">
								<span class="rounded-sm bg-brand-100 px-[9px] py-1 text-sm text-brand-700"><?php echo esc_html( $term->name ); ?></span>
								<?php if ( $tagline ) : ?>
									<h3 class="text-h6 font-medium text-gray-950"><?php echo esc_html( $tagline ); ?></h3>
								<?php endif; ?>
							</div>
							<?php
							upskill_button(
								array(
									'url'   => upskill_term_url( $term ),
									/* translators: %s: level name, e.g. Manager. */
									'title' => sprintf( __( 'Explore %s', 'upskill-me' ), $term->name ),
								),
								array( 'class' => 'mt-auto min-h-[50px]' )
							);
							?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
