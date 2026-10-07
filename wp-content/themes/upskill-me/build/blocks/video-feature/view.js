/******/ (() => { // webpackBootstrap
/*!******************************************!*\
  !*** ./src/blocks/video-feature/view.js ***!
  \******************************************/
/**
 * Video Feature — start the video on demand.
 *
 * Only the poster loads with the page. Pressing play swaps the poster, button
 * and caption for a native player (controls on), so the MP4 is fetched only by
 * people who choose to watch.
 */

document.querySelectorAll('[data-video-card]').forEach(card => {
  const button = card.querySelector('[data-video-play]');
  if (!button) {
    return;
  }
  button.addEventListener('click', () => {
    const video = document.createElement('video');
    video.className = 'col-start-1 row-start-1 size-full bg-black object-contain';
    video.src = button.dataset.src;
    video.controls = true;
    video.playsInline = true;
    video.setAttribute('aria-label', button.textContent.trim());
    card.classList.add('is-playing');
    card.append(video);
    video.focus();
    video.play().catch(() => {});
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map