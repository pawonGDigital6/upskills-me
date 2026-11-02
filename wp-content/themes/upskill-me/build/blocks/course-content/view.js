/******/ (() => { // webpackBootstrap
/*!*******************************************!*\
  !*** ./src/blocks/course-content/view.js ***!
  \*******************************************/
/**
 * Course Content block: "Expand all", and the session named in the URL hash.
 *
 * Each session row is a shared Disclosure (src/global/js/components/disclosure.js),
 * so a row opens and closes on its own; this only drives them together. The
 * hero's session tabs link to #session-N: that row opens and scrolls into view.
 */

function setRow(button, open) {
  button.setAttribute('aria-expanded', String(open));
  document.getElementById(button.getAttribute('aria-controls'))?.classList.toggle('is-open', open);
}
function openFromHash() {
  const match = /^#session-\d+$/.test(window.location.hash) ? document.querySelector(window.location.hash) : null;
  const button = match?.querySelector('[data-disclosure]');
  if (button) {
    setRow(button, true);
    match.scrollIntoView({
      block: 'center'
    });
  }
}
document.querySelectorAll('[data-course-content]').forEach(box => {
  const toggle = box.querySelector('[data-course-expand]');
  const label = toggle?.querySelector('[data-course-expand-label]');
  toggle?.addEventListener('click', () => {
    const open = 'true' !== toggle.getAttribute('aria-pressed');
    box.querySelectorAll('[data-disclosure]').forEach(button => setRow(button, open));
    toggle.setAttribute('aria-pressed', String(open));
    if (label) {
      label.textContent = open ? toggle.dataset.labelCollapse : toggle.dataset.labelExpand;
    }
  });
});
openFromHash();
window.addEventListener('hashchange', openFromHash);
/******/ })()
;
//# sourceMappingURL=view.js.map