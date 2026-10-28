<?php
/**
 * Video card transport bar: play/pause, mute, seek with elapsed and remaining
 * time, full screen. Figma "Play bar" (49325:14394, training card 49325:14592).
 *
 * Goes inside a [data-video-card] next to its <video data-video controls>.
 * Hidden until js/components/video-player.js swaps the native controls for it,
 * so the native controls remain the no-JS fallback. Styles:
 * src/global/scss/components/_video-card.scss.
 *
 * Used by the Free Sessions block and template-parts/cards/training.php.
 *
 * @package upskill-me
 *
 * @var array $args {
 *     @type string $class Extra classes for the bar (padding per card).
 * }
 */

$upskill_class = isset( $args['class'] ) ? (string) $args['class'] : '';
?>
<div class="video-card__bar relative flex items-center gap-0.5 self-end text-white <?php echo esc_attr( $upskill_class ); ?>" hidden data-video-bar>
	<button class="video-card__btn" type="button" data-video-play>
		<span class="screen-reader-text" data-label-play="<?php esc_attr_e( 'Play', 'upskill-me' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause', 'upskill-me' ); ?>"><?php esc_html_e( 'Play', 'upskill-me' ); ?></span>
		<?php upskill_icon( 'play', 'video-card__icon video-card__icon--play' ); ?>
		<?php upskill_icon( 'pause', 'video-card__icon video-card__icon--pause' ); ?>
	</button>
	<button class="video-card__btn" type="button" aria-pressed="false" data-video-mute>
		<span class="screen-reader-text"><?php esc_html_e( 'Mute', 'upskill-me' ); ?></span>
		<?php upskill_icon( 'volume', 'video-card__icon' ); ?>
	</button>
	<div class="flex min-w-0 flex-1 items-center gap-2 px-1">
		<span class="w-9 flex-none text-xs leading-[18px]" data-video-current>00:00</span>
		<input class="video-card__progress" type="range" min="0" max="100" step="0.1" value="0" aria-label="<?php esc_attr_e( 'Seek', 'upskill-me' ); ?>" data-video-progress>
		<span class="w-[42px] flex-none text-xs leading-[18px]" data-video-remaining></span>
	</div>
	<button class="video-card__btn" type="button" data-video-fullscreen>
		<span class="screen-reader-text"><?php esc_html_e( 'Full screen', 'upskill-me' ); ?></span>
		<?php upskill_icon( 'maximize', 'video-card__icon' ); ?>
	</button>
</div>
