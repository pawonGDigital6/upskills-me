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
 *     @type int     $number  Guide cards: the 01/02 badge on the photo.
 * }
 *
 * @package upskill-me
 */

$post_obj = $args['post'];
$variant = $args['variant'];
$index = isset($args['index']) ? (int) $args['index'] : 0;
$tag = isset($args['tag']) ? $args['tag'] : 'h3';
$url = get_permalink($post_obj);
$title = get_the_title($post_obj);
$excerpt = has_excerpt($post_obj) ? get_the_excerpt($post_obj) : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_obj)), 22);
$type = get_post_type($post_obj);
$thumb = get_post_thumbnail_id($post_obj);
$read = upskill_read_label($post_obj);
$stagger = 'style="--i:' . (int) min($index, 5) . '"';
$category = 'post' === $type ? upskill_primary_category($post_obj) : null;
$industry = 'case_study' === $type ? get_field('industry', $post_obj) : null;
$industry = $industry ? get_term((int) $industry, 'industry') : null;

$title_link = sprintf(
	'<a class="resource-card__link after:absolute after:inset-0 after:z-[1] after:rounded-[inherit] focus-visible:outline-none" href="%1$s">%2$s</a>',
	esc_url($url),
	esc_html($title)
);

$read_line = static function ($classes) use ($read) {
	printf(
		'<span class="resource-card__read inline-flex items-center gap-[7px] text-sm font-semibold %1$s" aria-hidden="true">%2$s%3$s</span>',
		esc_attr($classes),
		esc_html($read),
		upskill_get_icon('arrow-up-right', 'size-3 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5') // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file.
	);
};

$meta_row = static function () use ($post_obj, $type) {
	?>
	<p class="resource-card__meta flex flex-wrap items-center gap-2.5 text-sm">
		<span
			class="resource-card__type rounded-sm bg-brand-25 px-2.5 py-1 font-medium text-brand-700"><?php echo esc_html(upskill_post_type_label($post_obj)); ?></span>
		<?php if ('post' === $type): ?>
			<span class="resource-card__dot size-1 rounded-full bg-gray-200" aria-hidden="true"></span>
			<time class="resource-card__date text-gray-500"
				datetime="<?php echo esc_attr(get_the_date('c', $post_obj)); ?>"><?php echo esc_html(get_the_date('d F Y', $post_obj)); ?></time>
		<?php endif; ?>
	</p>
	<?php
};

if ('featured' === $variant):
	$links = isset($args['links']) ? array_filter((array) $args['links'], static fn($link) => !empty($link['url'])) : array();
	?>
	<article
		class="resource-card resource-card--featured reveal group relative grid overflow-hidden rounded-[20px] border border-line bg-white shadow-xs lg:min-h-[380px] lg:grid-cols-[671fr_688fr]">
		<?php // From lg the photo fills its column (lg:absolute) so the copy, not the photo's proportions, sets the card height. ?>
		<div class="resource-card__media relative grid grid-cols-1 grid-rows-1 overflow-hidden">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'resource-card__image col-start-1 row-start-1 aspect-[354/216] size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03] lg:absolute lg:inset-0 lg:aspect-auto',
					// 671x380 desktop (the 3:2 photo draws 671 wide); full width below lg.
					'sizes' => '(min-width: 1025px) 50vw, 92vw',
				)
			);
			?>
			<span
				class="resource-card__badge relative col-start-1 row-start-1 m-5 self-start items-center flex justify-self-start rounded-sm bg-brand-500 p-[6px_11px_3px] max-sm:px-2.5 max-sm:py-1 text-sm leading-[1.55] font-semibold tracking-[0.02em] text-white uppercase bg-red"><?php 'guide' === $type ? esc_html_e('Featured guide', 'upskill-me') : esc_html_e('Featured', 'upskill-me'); ?></span>
		</div>
		<?php // Phones (49374:104007 / 106465): 16px padding, 8px to the title, 20px to the copy, 42px to the links. ?>
		<div class="resource-card__body flex flex-col p-4 lg:p-9">
			<?php if ('case_study' !== $type): ?>
				<?php $meta_row(); ?>
			<?php else: ?>
				<p class="resource-card__type self-start rounded-sm bg-brand-25 px-2.5 py-1 text-sm font-medium text-brand-700">
					<?php
					/* translators: %s: resource type, e.g. "case study". */
					printf(esc_html__('Featured %s', 'upskill-me'), esc_html(strtolower(upskill_post_type_label($post_obj))));
					?>
				</p>
			<?php endif; ?>
			<<?php echo esc_attr($tag); ?> class="resource-card__title pt-2 text-h6 font-medium text-gray-950 lg:min-h-[calc(2.2em+16px)]
				lg:pt-4"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr($tag); ?>>
			<?php // Desktop: Figma keeps two title lines of room, then 19px to the copy (49325:16867, 49325:18137). ?>
			<div class="resource-card__excerpt flex flex-col gap-[25px] pt-5 text-base leading-[1.6] text-gray-500 lg:pt-[19px]">
				<?php upskill_paragraphs($excerpt); ?>
			</div>
			<div class="resource-card__links relative z-[2] mt-auto flex flex-wrap items-center gap-x-[22px] gap-y-2 pt-[42px] lg:pt-[18px]">
				<?php if ($links): ?>
					<?php foreach (array_values($links) as $i => $link): ?>
						<?php upskill_text_link($link, 0 === $i ? 'text-brand-700' : 'text-[#55555f]'); ?>
					<?php endforeach; ?>
				<?php else: ?>
					<?php $read_line('text-brand-700'); ?>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
elseif ('compact' === $variant):
	?>
	<article
		class="resource-card resource-card--compact reveal group relative flex flex-col overflow-hidden rounded-[18px] border border-brand-200 bg-white shadow-xs"
		<?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<div class="resource-card__media grid aspect-[264/147] grid-cols-1 grid-rows-1 overflow-hidden">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'resource-card__image col-start-1 row-start-1 size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
					'sizes' => '(min-width: 1025px) 364px, (min-width: 768px) 45vw, 92vw',
				)
			);
			?>
			<p class="resource-card__tags relative col-start-1 row-start-1 m-3 flex gap-[7px] self-start">
				<span
					class="resource-card__type flex items-center rounded-sm bg-signal px-[9px] py-1 text-2xs leading-none font-semibold tracking-[0.1em] text-gray-950 uppercase"><?php echo esc_html(upskill_post_type_label($post_obj)); ?></span>
				<?php if ($category): ?>
					<span
						class="resource-card__category rounded-sm bg-brand-25 px-2.5 py-[5px] text-2xs font-medium text-brand-700 leading-none flex items-center min-h-[24px]"><?php echo esc_html($category->name); ?></span>
				<?php endif; ?>
			</p>
		</div>
		<div class="resource-card__body flex flex-1 flex-col p-4 pb-[23px] max-sm:pr-6 wd:pt-10">
			<<?php echo esc_attr($tag); ?> class="resource-card__title text-lg font-medium
				text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr($tag); ?>> <?php if ($excerpt): ?>
				<p class="resource-card__excerpt line-clamp-4 pt-2 text-base text-gray-700"><?php echo esc_html($excerpt); ?></p>
			<?php endif; ?>
			<?php $read_line('mt-auto pt-[14px] text-brand-700'); ?>
		</div>
	</article>
	<?php
elseif ('case' === $variant):
	?>
	<article class="resource-card resource-card--case reveal group relative flex flex-col overflow-hidden rounded-[24px] border border-gray-900/10 bg-white"
		<?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<?php // 274px tall in both frames, so a squarer photo on phones (49374:106465). ?>
		<div class="resource-card__media aspect-[356/274] overflow-hidden sm:aspect-[440/274]">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'resource-card__image size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
					'sizes' => '(min-width: 1025px) 33vw, (min-width: 768px) 46vw, 92vw',
				)
			);
			?>
		</div>
		<div class="resource-card__body flex flex-1 flex-col items-start gap-3 p-6">
			<?php if ($industry instanceof WP_Term): ?>
				<p class="resource-card__category text-sm text-gray-500"><?php echo esc_html($industry->name); ?></p>
			<?php endif; ?>
			<<?php echo esc_attr($tag); ?> class="resource-card__title text-xl font-medium
				text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr($tag); ?>> <?php if ($excerpt): ?>
				<p class="resource-card__excerpt text-base text-gray-500"><?php echo esc_html($excerpt); ?></p>
			<?php endif; ?>
			<?php $read_line('mt-auto pt-4 text-brand-700'); ?>
		</div>
	</article>
	<?php
else:
	?>
	<article class="resource-card resource-card--standard reveal group relative flex flex-col overflow-hidden rounded-[20px] border border-brand-200 bg-white"
		<?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<div class="resource-card__media grid aspect-[358/216] grid-cols-1 grid-rows-1 overflow-hidden lg:aspect-[443/216]">
			<?php
			upskill_image(
				$thumb,
				'full',
				array(
					'class' => 'resource-card__image col-start-1 row-start-1 size-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]',
					// Guide renders (about 2:1) overfill the taller phone and tablet box: drawn ~113vw on phones, ~58vw on tablets.
					'sizes' => 'guide' === $type ? '(min-width: 1025px) 33vw, (min-width: 768px) 58vw, 113vw' : '(min-width: 1025px) 33vw, (min-width: 768px) 46vw, 92vw',
				)
			);
			?>
			<?php if ('guide' === $type && !empty($args['number'])): ?>
				<span
					class="resource-card__number relative col-start-1 row-start-1 m-3 grid h-7 min-w-7 place-items-center self-start justify-self-start rounded-lg bg-white/92 px-2 text-sm text-gray-700"><?php echo esc_html(str_pad((string) $args['number'], 2, '0', STR_PAD_LEFT)); ?></span>
			<?php endif; ?>
		</div>
		<div class="resource-card__body flex flex-1 flex-col p-4 <?php echo 'guide' === $type ? 'gap-11' : 'gap-8 lg:gap-[60px]'; ?>">
			<div class="resource-card__content flex flex-col">
				<?php if ('guide' === $type): ?>
					<p class="resource-card__type self-start rounded-sm bg-gray-50 px-2.5 py-1 text-sm text-gray-500">
						<?php echo esc_html(upskill_post_type_label($post_obj)); ?></p>
				<?php else: ?>
					<?php $meta_row(); ?>
				<?php endif; ?>
				<<?php echo esc_attr($tag); ?> class="resource-card__title py-2 text-h6 font-medium
					text-gray-950"><?php echo $title_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></<?php echo esc_attr($tag); ?>> <?php if ('guide' === $type && $excerpt): ?>
					<p class="resource-card__excerpt pt-4 text-base leading-[1.6] text-gray-500"><?php echo esc_html($excerpt); ?></p>
				<?php endif; ?>
			</div>
			<?php $read_line('mt-auto pt-[18px] text-brand-800'); ?>
		</div>
	</article>
	<?php
endif;
