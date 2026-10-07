/******/ (() => { // webpackBootstrap
/*!******************************************!*\
  !*** ./src/blocks/free-sessions/view.js ***!
  \******************************************/
/**
 * Free Sessions — the video card transport.
 *
 * Each card ships a native <video controls>, so it plays with no script. This
 * swaps those controls for the Figma transport bar: play/pause, mute, a seek
 * slider with elapsed and remaining time, and full screen. Starting one card
 * pauses any other that is playing.
 */

const cards = [];

/**
 * Format seconds as mm:ss.
 *
 * @param {number} seconds Seconds.
 * @return {string} Formatted time.
 */
function clock(seconds) {
  const safe = Number.isFinite(seconds) ? Math.max(0, Math.floor(seconds)) : 0;
  return `${String(Math.floor(safe / 60)).padStart(2, '0')}:${String(safe % 60).padStart(2, '0')}`;
}
function enhance(card) {
  const video = card.querySelector('[data-video]');
  const bar = card.querySelector('[data-video-bar]');
  if (!video || !bar) {
    return;
  }
  const play = bar.querySelector('[data-video-play]');
  const playLabel = play.querySelector('.screen-reader-text');
  const mute = bar.querySelector('[data-video-mute]');
  const progress = bar.querySelector('[data-video-progress]');
  const current = bar.querySelector('[data-video-current]');
  const remaining = bar.querySelector('[data-video-remaining]');
  const fullscreen = bar.querySelector('[data-video-fullscreen]');
  video.removeAttribute('controls');
  bar.hidden = false;
  cards.push(video);
  const toggle = () => {
    if (video.paused) {
      cards.forEach(other => other !== video && other.pause());
      video.play()?.catch(() => {});
    } else {
      video.pause();
    }
  };
  const sync = () => {
    const playing = !video.paused && !video.ended;
    card.classList.toggle('is-playing', playing);
    playLabel.textContent = playing ? playLabel.dataset.labelPause : playLabel.dataset.labelPlay;
  };
  const tick = () => {
    const duration = video.duration;
    current.textContent = clock(video.currentTime);
    remaining.textContent = Number.isFinite(duration) ? `-${clock(duration - video.currentTime)}` : '';
    progress.value = Number.isFinite(duration) && duration > 0 ? video.currentTime / duration * 100 : 0;
    progress.style.setProperty('--progress', `${progress.value}%`);
  };
  play.addEventListener('click', toggle);
  video.addEventListener('click', toggle);
  video.addEventListener('play', sync);
  video.addEventListener('pause', sync);
  video.addEventListener('ended', sync);
  video.addEventListener('timeupdate', tick);
  video.addEventListener('loadedmetadata', tick);
  mute.addEventListener('click', () => {
    video.muted = !video.muted;
    mute.setAttribute('aria-pressed', String(video.muted));
  });
  progress.addEventListener('input', () => {
    if (Number.isFinite(video.duration)) {
      video.currentTime = progress.value / 100 * video.duration;
    }
  });
  fullscreen.addEventListener('click', () => {
    if (card.requestFullscreen) {
      card.requestFullscreen().catch(() => {});
    } else if (video.webkitEnterFullscreen) {
      // iOS Safari only allows the video element itself to go full screen.
      video.webkitEnterFullscreen();
    }
  });
}
document.querySelectorAll('.block-free-sessions [data-video-card]').forEach(enhance);
/******/ })()
;
//# sourceMappingURL=view.js.map