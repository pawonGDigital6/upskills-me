/******/ (() => { // webpackBootstrap
/*!*****************************************!*\
  !*** ./src/blocks/testimonials/view.js ***!
  \*****************************************/
/**
 * Testimonials carousel.
 *
 * The track is a native scroll-snap row (touch swiping is its own scrolling);
 * this adds the arrow buttons, the progress bars (every bar up to the current
 * card is filled, as Figma draws, and each bar is a button to its card) and
 * mouse dragging. With three or more cards the desktop row starts on the
 * second, so a card peeks in on both sides.
 */

const DESKTOP = window.matchMedia('(min-width: 1025px)');

// How far (px) a mouse drag has to travel to count as a swipe rather than a click.
const DRAG_THRESHOLD = 40;
document.querySelectorAll('[data-carousel]').forEach(carousel => {
  const track = carousel.querySelector('[data-carousel-track]');
  const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
  const bars = Array.from(carousel.querySelectorAll('[data-carousel-bar]'));
  const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
  const prev = carousel.querySelector('[data-carousel-prev]');
  const next = carousel.querySelector('[data-carousel-next]');
  if (!track || slides.length < 2) {
    return;
  }
  const snapPadding = () => parseFloat(getComputedStyle(track).scrollPaddingInlineStart) || 0;

  /**
   * The scroll position that brings a slide to its snap point: centred at
   * desktop, against the gutter below it.
   *
   * @param {HTMLElement} slide Slide.
   * @return {number} scrollLeft.
   */
  const target = slide => DESKTOP.matches ? slide.offsetLeft - (track.clientWidth - slide.offsetWidth) / 2 : slide.offsetLeft - snapPadding();

  /**
   * The slide whose snap point is nearest the current scroll position. At the
   * end of the row it is the last slide, whose snap point the track may not
   * be able to reach (two cards fit at tablet widths).
   *
   * @return {number} Slide index.
   */
  const current = () => {
    if (track.scrollLeft > 0 && track.scrollLeft >= track.scrollWidth - track.clientWidth - 2) {
      return slides.length - 1;
    }
    let best = 0;
    let distance = Infinity;
    slides.forEach((slide, index) => {
      const gap = Math.abs(target(slide) - track.scrollLeft);
      if (gap < distance) {
        distance = gap;
        best = index;
      }
    });
    return best;
  };
  const goTo = (index, behavior) => {
    const slide = slides[Math.max(0, Math.min(index, slides.length - 1))];

    // Clamped, so the last slide's target is the end of the row and reads back as current.
    track.scrollTo({
      left: Math.min(target(slide), track.scrollWidth - track.clientWidth),
      behavior
    });
  };
  const update = () => {
    const index = current();
    bars.forEach((bar, i) => bar.classList.toggle('is-filled', i <= index));
    dots.forEach((dot, i) => i === index ? dot.setAttribute('aria-current', 'true') : dot.removeAttribute('aria-current'));
    if (prev) {
      prev.disabled = 0 === index;
    }
    if (next) {
      next.disabled = slides.length - 1 === index;
    }
  };
  prev?.addEventListener('click', () => goTo(current() - 1));
  next?.addEventListener('click', () => goTo(current() + 1));
  dots.forEach((dot, index) => dot.addEventListener('click', () => goTo(index)));
  let frame = 0;
  track.addEventListener('scroll', () => {
    window.cancelAnimationFrame(frame);
    frame = window.requestAnimationFrame(update);
  }, {
    passive: true
  });

  /*
   * Mouse dragging. Touch and pen already scroll the track natively, so only
   * a mouse is handled: snapping is paused while the row follows the pointer,
   * then the drag settles on the next or previous card (or back on the same
   * one for a short drag).
   */
  let drag = null;
  track.addEventListener('pointerdown', event => {
    if ('mouse' !== event.pointerType || 0 !== event.button) {
      return;
    }
    drag = {
      x: event.clientX,
      left: track.scrollLeft,
      index: current(),
      moved: false
    };
  });
  track.addEventListener('pointermove', event => {
    if (!drag) {
      return;
    }
    const distance = event.clientX - drag.x;
    if (!drag.moved && Math.abs(distance) < 5) {
      return;
    }
    if (!drag.moved) {
      drag.moved = true;
      track.setPointerCapture(event.pointerId);
      track.classList.add('is-dragging');
    }
    track.scrollLeft = drag.left - distance;
  });
  const release = event => {
    if (!drag) {
      return;
    }
    const {
      moved,
      index,
      x
    } = drag;
    const distance = event.clientX - x;
    drag = null;
    if (!moved) {
      return;
    }
    track.classList.remove('is-dragging');
    if (track.hasPointerCapture(event.pointerId)) {
      track.releasePointerCapture(event.pointerId);
    }
    if (Math.abs(distance) < DRAG_THRESHOLD) {
      goTo(index);
    } else {
      goTo(distance < 0 ? index + 1 : index - 1);
    }

    // The click that ends a drag must not follow a link under the pointer.
    const swallow = click => click.preventDefault();
    track.addEventListener('click', swallow, {
      capture: true,
      once: true
    });
    window.setTimeout(() => track.removeEventListener('click', swallow, {
      capture: true
    }), 0);
  };
  track.addEventListener('pointerup', release);
  track.addEventListener('pointercancel', release);

  // Text selection and native image dragging would fight the drag.
  track.addEventListener('dragstart', event => event.preventDefault());
  if (DESKTOP.matches && slides.length > 2) {
    goTo(1, 'instant');
  }
  update();
});
/******/ })()
;
//# sourceMappingURL=view.js.map