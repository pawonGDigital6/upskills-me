<?php
/**
 * Industry Resources block.
 *
 * Figma: Project Case Study Detail 49325:16856 (1440) / 49374:108325 (390),
 * "Industry Training Resources"; the same component sits on Industry Hub
 * (49325:14593) and Categories.
 *
 * A centred section header over a featured card (photo, two-tone title,
 * "Read More"), three training sessions as library cards, and a lilac panel
 * with a button and a cut-out photo (desktop only, as drawn).
 *
 * The sessions are the LearnDash courses chosen in the block, or else the
 * newest for the page's industry (a case study's Industry field, or the
 * industry page being viewed), topped up with the newest of the rest.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$featured_image = (int) get_field( 'featured_image' );
$featured_title = (string) get_field( 'featured_title' );
$featured_link  = get_field( 'featured_link' );
$panel_heading  = (string) get_field( 'panel_heading' );
$panel_text     = (string) get_field( 'panel_text' );
$panel_image    = (int) get_field( 'panel_image' );

// Sessions: the chosen courses, else the page's industry.
$sessions = array();

if ( function_exists( 'upskill_library_card' ) ) {
	$sessions = array_slice( array_filter( array_map( 'intval', (array) get_field( 'sessions' ) ) ), 0, 3 );

	if ( ! $sessions ) {
		$industry = 0;

		if ( is_tax( 'industry' ) ) {
			$industry = get_queried_object_id();
		} elseif ( 'case_study' === get_post_type() ) {
			$industry = (int) get_field( 'industry', get_the_ID() );
		}

		$sessions = upskill_library_industry_courses( $industry );
	}
}

$attributes = upskill_block_attributes( $block, 'block-industry-resources section-y bg-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'center',
				'reveal' => true,
			)
		);
		?>

		<?php // 443 + 906 at desktop (Figma 49325:16856); one column below. ?>
		<div class="industry-resources__grid mt-10 grid gap-4 lg:mt-[54px] lg:grid-cols-[443fr_906fr]">
			<?php if ( $featured_title ) : ?>
				<article class="industry-resources__feature reveal flex flex-col overflow-hidden rounded-lg border border-gray-100 bg-white shadow-card">
					<?php if ( $featured_image ) : ?>
						<div class="industry-resources__feature-media flex bg-[#ededef] p-2 lg:flex-1">
							<?php
							upskill_image(
								$featured_image,
								'full',
								array(
									// Fills the card's free height at desktop (425 x 294 as drawn).
									'class' => 'industry-resources__feature-image aspect-[326/290] w-full rounded-lg object-cover sm:aspect-[16/9] lg:aspect-auto lg:h-0 lg:min-h-full',
									'sizes' => '(min-width: 1440px) 425px, (min-width: 1025px) 30vw, calc(100vw - 48px)',
								)
							);
							?>
						</div>
					<?php endif; ?>
					<div class="industry-resources__feature-body flex flex-col p-4 pb-6">
						<h3 class="industry-resources__feature-title text-h6 font-medium text-gray-900 [&_em]:text-[#999] [&_em]:not-italic"><?php echo wp_kses( $featured_title, upskill_inline_kses() ); ?></h3>
						<?php if ( ! empty( $featured_link['url'] ) ) : ?>
							<a class="industry-resources__feature-link group mt-auto inline-flex items-center gap-2 self-start pt-8 text-[13px] leading-[1.62] font-medium tracking-[-0.008em] text-brand-700" href="<?php echo esc_url( $featured_link['url'] ); ?>"<?php echo empty( $featured_link['target'] ) ? '' : ' target="' . esc_attr( $featured_link['target'] ) . '" rel="noopener"'; ?>>
								<?php echo esc_html( $featured_link['title'] ? $featured_link['title'] : __( 'Read More', 'upskill-me' ) ); ?>
								<span class="screen-reader-text"><?php echo esc_html( wp_strip_all_tags( $featured_title ) ); ?></span>
								<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</article>
			<?php endif; ?>

			<div class="industry-resources__aside flex flex-col gap-4<?php echo $featured_title ? '' : ' lg:col-span-2'; ?>">
				<?php if ( $sessions ) : ?>
					<ul class="industry-resources__sessions grid gap-4 sm:grid-cols-3" role="list">
						<?php foreach ( $sessions as $index => $course_id ) : ?>
							<li class="industry-resources__session reveal flex flex-col *:flex-1" style="--i:<?php echo (int) $index + 1; ?>">
								<?php
								get_template_part(
									'template-parts/cards/training',
									null,
									array(
										'card'    => upskill_library_card( $course_id ),
										'compact' => true,
									)
								);
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $panel_heading || $panel_text ) : ?>
					<?php // The photo hangs from the top right and is cut off by the panel's lower edge. ?>
					<div class="industry-resources__panel reveal flex overflow-hidden rounded-2xl border border-[#d2c2f0] bg-brand-100 p-6 shadow-card" style="--i:4">
						<div class="industry-resources__panel-body flex min-w-0 flex-1 flex-col items-start gap-2">
							<?php if ( $panel_heading ) : ?>
								<h3 class="industry-resources__panel-title text-h6 font-medium text-gray-900"><?php echo esc_html( $panel_heading ); ?></h3>
							<?php endif; ?>
							<?php if ( $panel_text ) : ?>
								<p class="industry-resources__panel-text max-w-[547px] text-base text-gray-500"><?php echo esc_html( $panel_text ); ?></p>
							<?php endif; ?>
							<?php upskill_button( get_field( 'panel_button' ), array( 'class' => 'industry-resources__panel-button mt-4' ) ); ?>
						</div>
						<?php if ( $panel_image ) : ?>
							<div class="industry-resources__panel-media -mt-6 -mr-2.5 hidden h-0 w-[217px] flex-none lg:block">
								<?php
								upskill_image(
									$panel_image,
									'full',
									array(
										'class' => 'industry-resources__panel-image w-full',
										'alt'   => '',
										'sizes' => '217px',
									)
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
