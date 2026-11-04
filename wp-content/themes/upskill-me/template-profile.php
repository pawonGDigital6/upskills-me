<?php
/**
 * Template Name: Learner profile
 *
 * The LearnDash Profile page (/profile/, the "LearnDash Profile" block):
 * no Figma, so it takes the learner dashboard's frame (template-learner-dashboard.php):
 * breadcrumb, eyebrow, display heading and intro, then the block's own output
 * (profile card, stats, "Your Courses" with search and progress), unchanged,
 * restyled by src/global/scss/plugins/learndash/_profile.scss.
 *
 * @package upskill-me
 */

get_header();

$upskill_dashboard = function_exists( 'upskill_learner_dashboard_url' ) ? upskill_learner_dashboard_url() : '';
?>
<main id="primary" class="site-main profile-page bg-gray-25">
	<section class="container pt-6 lg:pt-10" aria-labelledby="profile-heading">
		<nav class="flex flex-wrap items-center gap-2 text-xs leading-[1.62] tracking-[-0.01em] text-[#6b6b72]" aria-label="<?php esc_attr_e( 'Breadcrumb', 'upskill-me' ); ?>">
			<a class="hover:text-brand-700" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'upskill-me' ); ?></a>
			<?php if ( $upskill_dashboard ) : ?>
				<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
				<a class="hover:text-brand-700" href="<?php echo esc_url( $upskill_dashboard ); ?>"><?php esc_html_e( 'My Learning', 'upskill-me' ); ?></a>
			<?php endif; ?>
			<span class="text-[#c3c3c8]" aria-hidden="true">/</span>
			<span class="font-medium text-[#5d3a8f]" aria-current="page"><?php the_title(); ?></span>
		</nav>

		<div class="flex flex-col gap-6 pt-8 lg:pt-12">
			<div class="flex flex-col gap-3">
				<?php upskill_eyebrow( __( 'My profile', 'upskill-me' ), 'account-profile', 'self-start' ); ?>
				<h1 id="profile-heading" class="heading text-h1 font-medium text-gray-900 lg:font-semibold"><?php echo wp_kses( __( 'Your learning <em>record.</em>', 'upskill-me' ), array( 'em' => array() ) ); ?></h1>
			</div>
			<p class="text-lead text-gray-500"><?php esc_html_e( 'Your courses, progress, certificates and points in one place.', 'upskill-me' ); ?></p>
		</div>
	</section>

	<div class="profile-content container pt-8 pb-16 lg:pt-12 lg:pb-24">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
