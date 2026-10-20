<?php
/**
 * Card Grid block.
 *
 * Figma: Why UpSkill Me 49325:15528, 15613; About 49325:14680, 14730, 14758;
 * Case Studies 49325:18246; industry "Key training needs" 49325:14836.
 *
 * A section header over a grid of cards (template-parts/cards/feature.php),
 * or — the "side" layout — the header, extra copy and an image or note in a
 * left column with the cards two-up on the right.
 *
 * Textured cards, resource links, icon rows and outcome columns are their own
 * blocks (textured-cards, resource-links, icon-rows, outcome-columns).
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
if ( $dark ) {
	$style = 'glass';
}

$surfaces = array(
	'white' => 'bg-white',
	'tint'  => 'bg-brand-50',
	'pale'  => 'bg-brand-25',
	'dark'  => 'is-dark relative isolate bg-[#0e0e10] text-white',
	'waves' => 'relative isolate bg-white',
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
	'side'   => $side,
	'framed' => (bool) get_field( 'framed' ),
);

$attributes = upskill_block_attributes( $block, 'block-card-grid section-y ' . ( isset( $surfaces[ $surface ] ) ? $surfaces[ $surface ] : $surfaces['white'] ) );
$image      = $side ? get_field( 'side_image' ) : false;
$callout    = $side ? get_field( 'callout' ) : array();

$render_cards = static function ( $classes ) use ( $cards, $card_args ) {
	if ( ! $cards ) {
		return;
	}

	?>
	<ul class="stack-lists grid gap-4 <?php echo esc_attr( $classes ); ?>">
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
		<img class="absolute inset-0 -z-10 size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'how-it-works-bg-2.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
	<?php endif; ?>

	<div class="container">
		<?php if ( $side ) : ?>
			<?php $principle = 'principle' === $style; // Why (principle cards): 88px apart, cards 48px down. About: 56px, 40px down. ?>
			<div class="flex flex-col gap-10 lg:flex-row lg:items-center <?php echo $principle ? 'lg:gap-[clamp(56px,6.1vw,88px)]' : 'lg:gap-14'; ?>">
				<div class="flex flex-col lg:min-w-0 lg:flex-1 lg:self-stretch <?php echo $principle ? 'gap-8 lg:gap-10' : 'gap-10'; ?>">
					<?php
					upskill_section_header(
						array(
							'layout' => 'stack',
							'button' => false,
							'reveal' => true,
							'class'  => get_field( 'large_intro' ) ? 'section-header--intro-xl' : 'section-header--base',
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
				<div class="lg:min-w-0 lg:flex-1 lg:self-stretch <?php echo $principle ? 'lg:pt-12' : 'lg:pt-10'; ?>">
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
					// Stacked headers (Case Studies "The impact") draw a 20/30 intro on phones too.
					'class'  => 'stack' === $layout ? 'section-header--intro-xl' : '',
				)
			);
			if ( $card_args['framed'] ) {
				// Industry "Key training needs" (Figma 49325:14836): the cards in a grey frame.
				echo '<div class="mt-stack rounded-[32px] bg-gray-100 p-2 sm:p-4">';
				$render_cards( $grid_columns[ $columns ] );
				echo '</div>';
			} else {
				$render_cards( 'mt-stack ' . $grid_columns[ $columns ] );
			}
			?>
		<?php endif; ?>
	</div>
</section>
