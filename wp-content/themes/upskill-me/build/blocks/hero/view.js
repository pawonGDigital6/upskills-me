/******/ (() => { // webpackBootstrap
/*!*********************************!*\
  !*** ./src/blocks/hero/view.js ***!
  \*********************************/
/**
 * Home Hero — showcase panels and the pathway tabs.
 *
 * Showcase: on desktop the panel under the pointer (or focus) opens and the
 * others fold to their vertical tabs. The first panel is open in the markup,
 * so without JavaScript the layout is already the designed one.
 *
 * Pathway: an ARIA tablist (arrow keys, Home/End) switching between the
 * industry and category term lists.
 */

function showcase(root) {
  const items = root.querySelectorAll('[data-showcase-item]');
  const activate = item => {
    items.forEach(other => other.classList.toggle('is-active', other === item));
  };
  items.forEach(item => {
    item.addEventListener('mouseenter', () => activate(item));
    item.addEventListener('focus', () => activate(item));
  });
}
function pathway(root) {
  const tabs = Array.from(root.querySelectorAll('[data-pathway-tab]'));
  const select = (tab, focus = false) => {
    tabs.forEach(other => {
      const selected = other === tab;
      other.setAttribute('aria-selected', String(selected));
      other.tabIndex = selected ? 0 : -1;
      document.getElementById(other.getAttribute('aria-controls')).hidden = !selected;
    });
    if (focus) {
      tab.focus();
    }
  };
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => select(tab));
    tab.addEventListener('keydown', event => {
      const keys = {
        ArrowRight: index + 1,
        ArrowLeft: index - 1,
        Home: 0,
        End: tabs.length - 1
      };
      if (!(event.key in keys)) {
        return;
      }
      event.preventDefault();
      select(tabs[(keys[event.key] + tabs.length) % tabs.length], true);
    });
  });
}
document.querySelectorAll('.block-hero').forEach(block => {
  block.querySelectorAll('[data-showcase]').forEach(showcase);
  block.querySelectorAll('[data-pathway]').forEach(pathway);
});
/******/ })()
;
//# sourceMappingURL=view.js.map