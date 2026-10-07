<?php
/**
 * Resource card — a blog post, case study or guide.
 *
 * Variants, from the Figma frames:
 * - card:     blog listing card (Blogs 49325:16914, Blog Detail 49325:16710).
 * - case:     case study card (Case Studies 49325:18182, Why 49325:15680).
 * - compact:  narrow card with type chips on the photo (Resources 49325:16442 / 16528).
 * - featured: wide photo + copy card (Blogs 49325:16914, Case Studies 49325:18149).
 *
 * The whole card is the link: the title's anchor stretches over it, and the
 * "Read Article" line repeats it visually (aria-hidden) rather than adding a
 * second tab stop.
 *
 * @param array $args {
 *     @type WP_Post $post    The resource.
 *     @type string  $variant card|case|compact|featured.
 *     @type int     $index   Position, for the reveal stagger.
 *     @type string  $tag     Title heading tag.
 *     @type array   $links   Featured only: extra ACF links shown after "Read".
 * }
 *
 * @package upskill-me
 */

$post_obj = $args['post'];
$variant  = $args['variant'];
$index    = isset( $args['index'] ) ? (int) $args['index'] : 0;
$tag      = isset( $args['tag'] ) ? $args['tag'] : 'h3';
$url      = get_permalink( $post_obj );
$title    = get_the_title( $post_obj );
$excerpt  = has_excerpt( $post_obj ) ? get_the_excerpt( $post_obj ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_obj ) ), 22 );
$type     = get_post_type( $post_obj );
$thumb    = get_post_thumbnail_id( $post_obj );
$read     = upskill_read_label( $post_obj );
$stagger  = 'style="--i:' . (int) min( $index, 5 ) . '"';
$category = 'post' === $type ? upskill_primary_category( $post_obj ) : null;
$industry = 'case_study' === $type ? get_field( 'industry', $post_obj ) : null;
$industry = $industry ? get_term( (int) $industry, 'industry' ) : null;

$title_link = sprintf(
	'<a class="after:absolute after:inset-0 after:z-[1] after:rounded-[inherit] focus-visible:outline-none" href="%1$s">%2$s</a>',
	esc_url( $url ),
	esc_html( $title )
);

$read_line = static function ( $classes ) use ( $read ) {
	printf(
		'<span class="inline-flex items-center gap-[7px] text-sm font-semibold %1$s" aria-hidden="true">%2$s%3$s</span>',
		esc_attr( $classes ),
		esc_html( $read ),
		upskill_get_icon( 'arrow-up-right', 'size-3 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file.
	);
};

$meta_row = static function () use ( $post_obj, $type ) {
	?>
	<p class="flex flex-wrap items-center gap-2.5 text-sm">
		<span class="rounded-sm bg-brand-25 px-2.5 py-1 font-medium text-brand-700"><?php echo esc_html( upskill_post_type_label( $post_obj ) ); ?></span>
		<?php if ( 'post' === $type ) : ?>
			<span class="size-1 rounded-full bg-gray-200" aria-hidden="true"></span>
			<time class="text-gray-500" datetime="<?php echo esc_attr( get_the_date( 'c', $post_obj ) ); ?>"><?php echo esc_html( get_the_date( 'd F Y', $post_obj ) ); ?></time>
		<?php endif; ?>
	</p>
	<?php
};

if ( 'featured' === $variant ) :
	$links = isset( $args['links'] ) ? array_filter( (array) $args['links'], static fn( $link ) => ! empty( $link['url'] ) ) : array();
	?>
	<article class="reveal group relative grid overflow-hidden rounded-[20px] border border-line bg-white shadow-xs lg:min-h-[380px] lg:grid-cols-[671fr_527fr]">
		<?php // From lg the photo fills its column (lg:absolute) so the copy, not the photo's proportions, sets the card height. ?>
		<div class="relative grid grid-cols-1 grid-rows-1 overflow-hidden">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'col-start-1 row-start-1 aspect-[358/240] size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03] lg:absolute lg:inset-0 lg:aspect-auto',
					// 671x380 desktop (the 3:2 photo draws 671 wide); full width below lg.
					'sizes' => '(min-width: 1025px) 50vw, 92vw',
				)
			);
			?>
			<span class="relative col-start-1 row-start-1 m-5 self-start justify-self-start rounded-sm bg-brand-500 px-2.5 py-1 text-sm leading-[1.55] font-semibold tracking-[0.02em] text-white uppercase"><?php esc_html_e( 'Featured', 'upskill-me' ); ?></span>
		</div>
		<div class="flex flex-col p-6 lg:p-9">
			<?php if ( 'post' === $type ) : ?>
				<?php $meta_row(); ?>
			<?php else : ?>
				<p class="self-start rounded-sm bg-brand-25 px-2.5 py-1 text-sm font-medium text-brand-700">
					<?php
					/* translators: %s: resource type, e.g. "case study". */
					printf( esc_html__( 'Featured %s', 'upskill-me' ), esc_html( strtolower( upskill_post_type_label( $post_obj ) ) ) );
					?>
				</p>
			<?php endif; ?>
			<<?php echo esc_attr( $tag ); ?> class="pt-4 text-h6 font-medium text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr( $tag ); ?>>
			<div class="flex flex-col gap-[25px] pt-3 text-base leading-[1.6] text-gray-500">
				<?php upskill_paragraphs( $excerpt ); ?>
			</div>
			<div class="relative z-[2] mt-auto flex flex-wrap items-center gap-x-[22px] gap-y-2 pt-[18px]">
				<?php if ( $links ) : ?>
					<?php foreach ( array_values( $links ) as $i => $link ) : ?>
						<?php upskill_text_link( $link, 0 === $i ? 'text-brand-700' : 'text-[#55555f]' ); ?>
					<?php endforeach; ?>
				<?php else : ?>
					<?php $read_line( 'text-brand-700' ); ?>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
elseif ( 'compact' === $variant ) :
	?>
	<article class="reveal group relative flex flex-col overflow-hidden rounded-[18px] border border-brand-200 bg-white shadow-xs" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<div class="grid aspect-[264/147] grid-cols-1 grid-rows-1 overflow-hidden">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'col-start-1 row-start-1 size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
					'sizes' => '(min-width: 1025px) 264px, (min-width: 768px) 45vw, 92vw',
				)
			);
			?>
			<p class="relative col-start-1 row-start-1 m-3 flex gap-[7px] self-start">
				<span class="rounded-sm bg-signal px-[9px] py-1 text-2xs leading-[1.62] font-semibold tracking-[0.1em] text-gray-950 uppercase"><?php echo esc_html( upskill_post_type_label( $post_obj ) ); ?></span>
				<?php if ( $category ) : ?>
					<span class="rounded-sm bg-brand-25 px-2.5 py-[5px] text-2xs font-medium text-brand-700"><?php echo esc_html( $category->name ); ?></span>
				<?php endif; ?>
			</p>
		</div>
		<div class="flex flex-1 flex-col p-4 pb-[23px]">
			<<?php echo esc_attr( $tag ); ?> class="text-lg font-medium text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr( $tag ); ?>>
			<?php if ( $excerpt ) : ?>
				<p class="line-clamp-4 pt-2 text-base text-gray-700"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<?php $read_line( 'mt-auto pt-[14px] text-brand-700' ); ?>
		</div>
	</article>
	<?php
elseif ( 'case' === $variant ) :
	?>
	<article class="reveal group relative flex flex-col overflow-hidden rounded-[24px] border border-gray-900/10 bg-white" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<div class="aspect-[440/274] overflow-hidden">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
					'sizes' => '(min-width: 1025px) 33vw, (min-width: 768px) 46vw, 92vw',
				)
			);
			?>
		</div>
		<div class="flex flex-1 flex-col items-start gap-3 p-6">
			<?php if ( $industry instanceof WP_Term ) : ?>
				<p class="text-sm text-gray-500"><?php echo esc_html( $industry->name ); ?></p>
			<?php endif; ?>
			<<?php echo esc_attr( $tag ); ?> class="text-xl font-medium text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr( $tag ); ?>>
			<?php if ( $excerpt ) : ?>
				<p class="text-base text-gray-500"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<?php $read_line( 'mt-auto pt-4 text-brand-700' ); ?>
		</div>
	</article>
	<?php
else :
	?>
	<article class="reveal group relative flex flex-col overflow-hidden rounded-[20px] border border-brand-200 bg-white" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<div class="aspect-[443/216] overflow-hidden">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
					'sizes' => '(min-width: 1025px) 33vw, (min-width: 768px) 46vw, 92vw',
				)
			);
			?>
		</div>
		<div class="flex flex-1 flex-col gap-3 p-4 lg:gap-[60px]">
			<div class="flex flex-col">
				<?php $meta_row(); ?>
				<<?php echo esc_attr( $tag ); ?> class="py-2 text-h6 font-medium text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr( $tag ); ?>>
			</div>
			<?php $read_line( 'mt-auto pt-[18px] text-brand-800' ); ?>
		</div>
	</article>
	<?php
endif;
