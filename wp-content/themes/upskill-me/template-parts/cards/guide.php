<?php
/**
 * Guide cards — Resources "Practical guides" (Figma 49325:16462).
 *
 * - feature: the tall dark card with the "Featured guide" chip.
 * - row:     thumbnail, title, line and a chevron, stacked beside it.
 *
 * @param array $args {
 *     @type WP_Post $post    The guide.
 *     @type string  $variant feature|row.
 *     @type int     $index   Position, for the reveal stagger.
 * }
 *
 * @package upskill-me
 */

$post_obj = $args['post'];
$index    = isset( $args['index'] ) ? (int) $args['index'] : 0;
$url      = get_permalink( $post_obj );
$excerpt  = has_excerpt( $post_obj ) ? get_the_excerpt( $post_obj ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_obj ) ), 14 );
$link     = sprintf(
	'<a class="after:absolute after:inset-0 after:z-[1] after:rounded-[inherit] focus-visible:outline-none" href="%1$s">%2$s</a>',
	esc_url( $url ),
	esc_html( get_the_title( $post_obj ) )
);
$arrow    = upskill_get_icon( 'arrow-up-right', 'size-3 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' );

if ( 'feature' === $args['variant'] ) :
	?>
	<article class="reveal group relative isolate flex min-h-[360px] flex-col overflow-hidden rounded-[18px] border border-line bg-gray-950 shadow-xs lg:min-h-[398px]" style="--i:<?php echo (int) $index; ?>">
		<img class="absolute inset-0 -z-10 size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
		<div class="flex flex-1 flex-col items-start justify-end p-6 lg:pb-16">
			<span class="rounded-sm bg-brand-500 px-2.5 py-1 text-sm leading-[1.55] font-semibold tracking-[0.02em] text-white uppercase"><?php esc_html_e( 'Featured guide', 'upskill-me' ); ?></span>
			<h3 class="pt-3.5 text-h6 font-medium text-white"><?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></h3>
			<?php if ( $excerpt ) : ?>
				<p class="max-w-[420px] pt-2.5 text-sm leading-[1.6] text-gray-100"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<span class="inline-flex items-center gap-[7px] pt-3.5 text-base font-medium text-white" aria-hidden="true"><?php echo esc_html( upskill_read_label( $post_obj ) ) . $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file. ?></span>
		</div>
	</article>
	<?php
else :
	?>
	<article class="reveal group relative flex items-start gap-4 rounded-[18px] border border-line bg-white p-3.5 shadow-xs" style="--i:<?php echo (int) $index; ?>">
		<div class="aspect-[132/104] w-[132px] flex-none overflow-hidden rounded-xl">
			<?php
			upskill_image(
				get_post_thumbnail_id( $post_obj ),
				'full',
				array(
					'class' => 'size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105',
					'sizes' => '132px',
				)
			);
			?>
		</div>
		<div class="flex min-w-0 flex-1 flex-col">
			<h3 class="text-lg font-medium text-gray-950"><?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></h3>
			<?php if ( $excerpt ) : ?>
				<p class="pt-1.5 text-sm leading-[1.6] text-[#55555f]"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<span class="inline-flex items-center gap-[7px] pt-2.5 text-base font-medium text-brand-700" aria-hidden="true"><?php echo esc_html( upskill_read_label( $post_obj ) ) . $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file. ?></span>
		</div>
		<span class="icon-link hidden size-10 flex-none border border-line bg-white text-gray-950 sml:grid group-hover:border-brand-700 group-hover:bg-brand-700 group-hover:text-white" aria-hidden="true"><?php upskill_icon( 'chevron-right', 'size-3.5' ); ?></span>
	</article>
	<?php
endif;
