<?php
/**
 * Card Grid block.
 *
 * Figma: Why UpSkill Me 49325:15353, 15528, 15613; About 49325:14680, 14730,
 * 14758; Resources 49325:16433; Case Studies 49325:18246; Project Case Study
 * Detail 49325:16767 (rows), 49325:16815 (outcomes).
 *
 * A section header over a grid of cards (template-parts/cards/feature.php),
 * or — the "side" layout — the header, extra copy and an image or note in a
 * left column with the cards two-up on the right.
 *
 * Two styles are lists rather than cards: "rows" (icon, title and text across
 * one ruled line each, cards on phones; for dark sections) and "outcome" (the
 * columns of one outlined box, separate boxes on phones).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$cards = array_values(
	array_filter(
		(array) get_field( 'cards' ),
		static function ( $card ) {
			return ! empty( $card['title'] ) || ! empty( $card['label'] );
		}
	)
);

$style   = get_field( 'card_style' ) ? get_field( 'card_style' ) : 'stacked';
$layout  = get_field( 'layout' ) ? get_field( 'layout' ) : 'split';
$surface = get_field( 'surface' ) ? get_field( 'surface' ) : 'white';
$columns = (int) get_field( 'columns' );
$columns = in_array( $columns, array( 2, 3, 4 ), true ) ? $columns : 3;
$dark    = 'dark' === $surface;
$side    = 'side' === $layout;

// Glass cards are the dark-surface version of every card style.
if ( $dark && ! in_array( $style, array( 'rows', 'outcome' ), true ) ) {
	$style = 'glass';
}

$surfaces = array(
	'white' => 'bg-white',
	'tint'  => 'bg-brand-50',
	'pale'  => 'bg-brand-25',
	'dark'  => 'is-dark relative isolate bg-[#0e0e10] text-white',
	'waves' => 'relative isolate bg-white',
	'faint' => 'relative isolate bg-white',
);

$grid_columns = array(
	2 => 'sm:grid-cols-2',
	3 => 'sm:grid-cols-2 lg:grid-cols-3',
	4 => 'sm:grid-cols-2 lg:grid-cols-4',
);

$card_args = array(
	'style'  => $style,
	'number' => (bool) get_field( 'show_numbers' ),
	'large'  => (bool) get_field( 'large_titles' ),
	'roomy'  => (bool) get_field( 'roomy_cards' ),
);

$attributes = upskill_block_attributes( $block, 'block-card-grid section-y ' . ( isset( $surfaces[ $surface ] ) ? $surfaces[ $surface ] : $surfaces['white'] ) );
$image      = $side ? get_field( 'side_image' ) : false;
$callout    = $side ? get_field( 'callout' ) : array();

$render_cards = static function ( $classes ) use ( $cards, $card_args, $style ) {
	if ( ! $cards ) {
		return;
	}

	if ( 'rows' === $style ) {
		?>
		<?php // 44 | title | text across ruled rows at desktop (rules overlap by 1px); outlined cards on phones. ?>
		<ul class="mt-10 flex flex-col gap-4 lg:gap-0">
			<?php foreach ( $cards as $index => $card ) : ?>
				<li class="reveal flex flex-col gap-6 rounded-[24px] border border-gray-800 p-4 lg:-mt-px lg:grid lg:grid-cols-[44px_minmax(0,1fr)_minmax(0,1fr)] lg:items-start lg:gap-6 lg:rounded-none lg:border-x-0 lg:border-white/12 lg:px-0 lg:py-2" style="--i:<?php echo (int) min( $index, 5 ); ?>">
					<div class="flex flex-col gap-3 lg:contents">
						<span class="grid size-10 flex-none place-items-center rounded-full bg-white/8 text-signal" aria-hidden="true"><?php upskill_icon( $card['icon'] ? $card['icon'] : 'check', 'size-[18px]' ); ?></span>
						<h3 class="text-xl leading-[1.62] font-bold tracking-[-0.014em] text-white lg:pt-1.5"><?php echo esc_html( $card['title'] ); ?></h3>
					</div>
					<?php if ( ! empty( $card['text'] ) ) : ?>
						<p class="text-sm leading-[1.6] tracking-[-0.007em] text-white/65 lg:max-w-[432px]"><?php echo esc_html( $card['text'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return;
	}

	if ( 'outcome' === $style ) {
		?>
		<ul class="mt-6 grid gap-4 lg:grid-cols-3 lg:gap-0 lg:overflow-hidden lg:rounded-[24px] lg:border lg:border-[#111827]/14">
			<?php foreach ( $cards as $index => $card ) : ?>
				<li class="reveal flex flex-col border border-[#111827]/14 px-6 py-12 lg:border-0 lg:px-8 lg:first:pl-6 lg:not-last:border-r lg:not-last:border-[#111827]/14" style="--i:<?php echo (int) min( $index, 5 ); ?>">
					<p class="flex items-center gap-2.5 text-sm font-medium text-[#111827] uppercase">
						<?php upskill_icon( $card['icon'] ? $card['icon'] : 'check', 'size-4 flex-none text-gray-800' ); ?>
						<?php echo esc_html( $card['label'] ? $card['label'] : __( 'Outcome', 'upskill-me' ) ); ?>
					</p>
					<span class="mt-5 h-0.5 w-10 rounded-[1px] bg-[#111827]" aria-hidden="true"></span>
					<h3 class="pt-5 text-[36px] leading-[1.1] font-medium tracking-[-0.01em] text-[#111827]"><?php echo esc_html( $card['title'] ); ?></h3>
					<?php if ( ! empty( $card['text'] ) ) : ?>
						<p class="max-w-[340px] pt-5 text-base leading-[1.6] tracking-[-0.006em] text-gray-500"><?php echo esc_html( $card['text'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return;
	}
	?>
	<ul class="grid gap-4 <?php echo esc_attr( $classes ); ?>">
		<?php
		foreach ( $cards as $index => $card ) {
			get_template_part(
				'template-parts/cards/feature',
				null,
				array_merge(
					$card_args,
					array(
						'card'  => $card,
						'index' => $index,
					)
				)
			);
		}
		?>
	</ul>
	<?php
};
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<?php if ( $dark ) : ?>
		<img class="absolute inset-0 -z-10 size-full object-cover opacity-20" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
	<?php elseif ( 'waves' === $surface ) : ?>
		<img class="absolute inset-0 -z-10 size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'how-it-works-bg.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
	<?php elseif ( 'faint' === $surface ) : ?>
		<img class="absolute inset-0 -z-10 size-full object-cover opacity-[0.08]" src="<?php echo esc_url( upskill_theme_image( 'impact-waves.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
	<?php endif; ?>

	<div class="container">
		<?php if ( $side ) : ?>
			<div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:gap-[clamp(56px,6.1vw,88px)]">
				<div class="flex flex-col gap-8 lg:min-w-0 lg:flex-1 lg:self-stretch lg:gap-10">
					<?php
					upskill_section_header(
						array(
							'layout' => 'stack',
							'button' => false,
							'reveal' => true,
							'class'  => get_field( 'large_intro' ) ? '' : 'section-header--base',
						)
					);
					?>

					<?php if ( $image ) : ?>
						<?php
						upskill_image(
							$image,
							'full',
							array(
								'class' => 'reveal aspect-[358/240] w-full rounded-[24px] object-cover lg:aspect-[652/240]',
								// 652x240 at desktop draws the 3:2 photo 652 wide; the column is full width below lg.
								'sizes' => '(min-width: 1025px) 46vw, 92vw',
							)
						);
						?>
					<?php elseif ( ! empty( $callout['title'] ) ) : ?>
						<div class="reveal flex items-start gap-4 rounded-[20px] border border-line bg-white p-6 lg:max-w-[507px]" style="--i:2">
							<span class="grid size-11 flex-none place-items-center rounded-[13px] bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( $callout['icon'] ? $callout['icon'] : 'info', 'size-5' ); ?></span>
							<div class="flex flex-col gap-2">
								<p class="text-lg font-medium text-gray-950"><?php echo esc_html( $callout['title'] ); ?></p>
								<?php if ( ! empty( $callout['text'] ) ) : ?>
									<p class="text-base text-gray-500"><?php echo esc_html( $callout['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div class="lg:min-w-0 lg:flex-1 lg:self-stretch lg:pt-12">
					<?php $render_cards( 'sm:grid-cols-2' ); ?>
				</div>
			</div>
		<?php else : ?>
			<?php
			upskill_section_header(
				array(
					'layout' => in_array( $layout, array( 'split', 'center', 'stack' ), true ) ? $layout : 'split',
					'dark'   => $dark,
					'reveal' => true,
					'class'  => 'rows' === $style ? 'section-header--rows' : '',
				)
			);
			$render_cards( 'mt-stack ' . $grid_columns[ $columns ] . ( 'texture' === $style ? ' lg:items-end' : '' ) );
			?>
		<?php endif; ?>
	</div>
</section>
