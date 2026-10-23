<?php
/**
 * Training library card.
 *
 * Figma: Search 49325:15693, "Article" 337 wide (1440) / 358 (390). A grey
 * frame holding the video with its Free and session-code tags, then category,
 * title, summary and "View Session".
 *
 * A free (Sample) session plays in the card; any other shows its featured
 * image, or the video's first frame, and opens its course.
 *
 * @package upskill-me
 *
 * Compact (Industry Resources, Figma 49325:16856): the picture, title and
 * "View Session" only; Figma hides the tags, category and summary there.
 *
 * @var array $args {
 *     @type array $card    upskill_library_card() row.
 *     @type bool  $compact Picture, title and link only.
 * }
 */

$upskill_card    = $args['card'];
$upskill_compact = ! empty( $args['compact'] );
if ( $upskill_compact ) {
	$upskill_card = array_merge(
		$upskill_card,
		array(
			'code'     => '',
			'category' => '',
			'summary'  => '',
		)
	);
}
$upskill_title = wp_unique_id( 'training-card-' );
?>
<article class="flex flex-col overflow-hidden rounded-lg border border-gray-100 bg-white shadow-card" aria-labelledby="<?php echo esc_attr( $upskill_title ); ?>">
	<div class="bg-[#ededef] p-2">
		<div class="grid aspect-[323/174] grid-cols-1 grid-rows-1 overflow-hidden rounded-lg bg-gray-950 *:col-start-1 *:row-start-1">
			<?php if ( $upskill_card['image'] && ! ( $upskill_card['free'] && $upskill_card['video'] ) ) : ?>
				<?php upskill_image( $upskill_card['image'], 'large', array( 'class' => 'size-full object-cover', 'alt' => '', 'sizes' => '(min-width: 1025px) 323px, (min-width: 768px) 50vw, calc(100vw - 48px)' ) ); ?>
			<?php elseif ( $upskill_card['video'] ) : ?>
				<?php if ( $upskill_card['free'] ) : ?>
					<video class="size-full object-cover" preload="metadata" playsinline controls<?php echo $upskill_card['image'] ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $upskill_card['image'], 'large' ) ) . '"' : ''; ?> aria-label="<?php echo esc_attr( $upskill_card['title'] ); ?>">
						<source src="<?php echo esc_url( $upskill_card['video'] ); ?>" type="video/mp4">
					</video>
				<?php else : ?>
					<?php // First frame as the picture; the session itself stays behind the course. ?>
					<video class="pointer-events-none size-full object-cover" preload="metadata" muted playsinline tabindex="-1" aria-hidden="true">
						<source src="<?php echo esc_url( $upskill_card['video'] . '#t=1' ); ?>" type="video/mp4">
					</video>
				<?php endif; ?>
			<?php else : ?>
				<?php // No picture or video yet: the brand mark, so the card never shows an empty box. ?>
				<img class="w-2/5 max-w-[160px] place-self-center opacity-80" src="<?php echo esc_url( upskill_theme_image( 'logo-white.webp' ) ); ?>" alt="" width="192" height="44" loading="lazy" decoding="async">
			<?php endif; ?>

			<div class="pointer-events-none relative z-10 flex items-start gap-[7px] self-start justify-self-start p-2">
				<?php if ( $upskill_card['free'] && ! $upskill_compact ) : ?>
					<span class="tag tag--free h-6"><?php esc_html_e( 'Free', 'upskill-me' ); ?></span>
				<?php endif; ?>
				<?php if ( $upskill_card['code'] ) : ?>
					<span class="tag tag--code h-6"><?php echo esc_html( $upskill_card['code'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="flex flex-1 flex-col p-4">
		<?php if ( $upskill_card['category'] ) : ?>
			<p class="text-2xs leading-[1.62] font-medium tracking-[0.12em] text-brand-700 uppercase"><?php echo esc_html( $upskill_card['category'] ); ?></p>
		<?php endif; ?>
		<h3 id="<?php echo esc_attr( $upskill_title ); ?>" class="pt-2 text-lg leading-[1.5] font-medium text-gray-900"><?php echo esc_html( $upskill_card['title'] ); ?></h3>
		<?php if ( $upskill_card['summary'] ) : ?>
			<p class="pt-2 text-base text-[#55555a]"><?php echo esc_html( $upskill_card['summary'] ); ?></p>
		<?php endif; ?>
		<a class="group mt-auto inline-flex items-center gap-2 self-start pt-4 text-[13px] leading-[1.62] font-medium tracking-[-0.008em] text-brand-700" href="<?php echo esc_url( $upskill_card['url'] ); ?>">
			<?php esc_html_e( 'View Session', 'upskill-me' ); ?>
			<span class="screen-reader-text"><?php echo esc_html( $upskill_card['title'] ); ?></span>
			<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
		</a>
	</div>
</article>
