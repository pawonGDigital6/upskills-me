/******/ (() => { // webpackBootstrap
/*!*****************************************!*\
  !*** ./src/blocks/testimonials/view.js ***!
  \*****************************************/
/**
 * Testimonials carousel.
 *
 * The track is a native scroll-snap row; this adds the arrow buttons and the
 * progress bars (every bar up to the current card is filled, as Figma draws).
 * With three or more cards the desktop row starts on the second, so a card
 * peeks in on both sides.
 */

const DESKTOP = window.matchMedia('(min-width: 1025px)');
document.querySelectorAll('[data-carousel]').forEach(carousel => {
  const track = carousel.querySelector('[data-carousel-track]');
  const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
  const bars = Array.from(carousel.querySelectorAll('[data-carousel-bar]'));
  const prev = carousel.querySelector('[data-carousel-prev]');
  const next = carousel.querySelector('[data-carousel-next]');
  if (!track || slides.length < 2) {
    return;
  }

  /**
   * The slide whose snap point is nearest the current scroll position.
   *
   * @return {number} Slide index.
   */
  const current = () => {
    const centre = DESKTOP.matches;
    const anchor = centre ? track.scrollLeft + track.clientWidth / 2 : track.scrollLeft;
    let best = 0;
    let distance = Infinity;
    slides.forEach((slide, index) => {
      const point = centre ? slide.offsetLeft + slide.offsetWidth / 2 : slide.offsetLeft - parseFloat(getComputedStyle(track).scrollPaddingInlineStart || 0);
      const gap = Math.abs(point - anchor);
      if (gap < distance) {
        distance = gap;
        best = index;
      }
    });
    return best;
  };
  const goTo = (index, behavior) => {
    const slide = slides[Math.max(0, Math.min(index, slides.length - 1))];
    const left = DESKTOP.matches ? slide.offsetLeft - (track.clientWidth - slide.offsetWidth) / 2 : slide.offsetLeft - parseFloat(getComputedStyle(track).scrollPaddingInlineStart || 0);
    track.scrollTo({
      left,
      behavior
    });
  };
  const update = () => {
    const index = current();
    bars.forEach((bar, i) => bar.classList.toggle('is-filled', i <= index));
    if (prev) {
      prev.disabled = 0 === index;
    }
    if (next) {
      next.disabled = slides.length - 1 === index;
    }
  };
  prev?.addEventListener('click', () => goTo(current() - 1));
  next?.addEventListener('click', () => goTo(current() + 1));
  let frame = 0;
  track.addEventListener('scroll', () => {
    window.cancelAnimationFrame(frame);
    frame = window.requestAnimationFrame(update);
  }, {
    passive: true
  });
  if (DESKTOP.matches && slides.length > 2) {
    goTo(1, 'instant');
  }
  update();
});
/******/ })()
;
//# sourceMappingURL=view.js.map