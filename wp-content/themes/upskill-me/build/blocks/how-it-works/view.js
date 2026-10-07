/******/ (() => { // webpackBootstrap
/*!*****************************************!*\
  !*** ./src/blocks/how-it-works/view.js ***!
  \*****************************************/
/**
 * How It Works — the step flight (from the designer's template).
 *
 * When a step list scrolls into view its steps cascade in, then the list walks
 * once through default -> active -> complete, one step every 900ms, ending with
 * every step complete. Each list runs on its own, so on mobile the second track
 * waits until it is reached. Reduced motion skips straight to the end state.
 */

const DWELL = 900;
const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Mark steps before `index` complete, `index` active and the rest default.
 *
 * @param {HTMLElement[]} steps Step elements.
 * @param {number}        index Active step; steps.length marks all complete.
 */
function setState(steps, index) {
  steps.forEach((step, i) => {
    step.dataset.state = i < index ? 'complete' : i === index ? 'active' : 'default';
  });
}

/**
 * Run one list: cascade in, then walk the steps.
 *
 * @param {HTMLElement} flight The step list.
 * @param {number}      offset Extra delay so side-by-side tracks do not tick in unison.
 */
function run(flight, offset) {
  const steps = Array.from(flight.querySelectorAll('[data-step]'));
  flight.classList.add('is-in');
  if (REDUCED) {
    setState(steps, steps.length);
    return;
  }

  // Wait for the cascade to land: first delay + stagger + entrance duration.
  const appear = 160 + (steps.length - 1) * 150 + 560;
  let index = 0;
  window.setTimeout(() => {
    setState(steps, 0);
    const timer = window.setInterval(() => {
      index += 1;
      setState(steps, index);
      if (index >= steps.length) {
        window.clearInterval(timer);
      }
    }, DWELL);
  }, appear + offset);
}
document.querySelectorAll('.block-how-it-works').forEach(block => {
  const flights = Array.from(block.querySelectorAll('[data-flight]'));
  if (!('IntersectionObserver' in window)) {
    flights.forEach((flight, i) => run(flight, i * 160));
    return;
  }
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        observer.unobserve(entry.target);
        run(entry.target, flights.indexOf(entry.target) * 160);
      }
    });
  }, {
    rootMargin: '0px 0px -14% 0px'
  });
  flights.forEach(flight => observer.observe(flight));
});
/******/ })()
;
//# sourceMappingURL=view.js.map