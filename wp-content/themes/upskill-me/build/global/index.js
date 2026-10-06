/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/global/js/components/announcement.js"
/*!**************************************************!*\
  !*** ./src/global/js/components/announcement.js ***!
  \**************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Announcement: () => (/* binding */ Announcement)
/* harmony export */ });
/**
 * Dismissable announcement bar.
 *
 * Dismissal lasts for the browser session, so the free sessions offer comes
 * back on a later visit. Storage can throw (private mode, blocked cookies), in
 * which case the bar simply closes for this page view.
 */

const KEY = 'upskill-announcement-dismissed';
function Announcement() {
  const bar = document.querySelector('[data-announcement]');
  if (!bar) {
    return;
  }
  try {
    if (window.sessionStorage.getItem(KEY)) {
      bar.hidden = true;
      return;
    }
  } catch (error) {}
  const close = bar.querySelector('[data-announcement-close]');
  if (!close) {
    return;
  }
  close.addEventListener('click', () => {
    bar.hidden = true;
    try {
      window.sessionStorage.setItem(KEY, '1');
    } catch (error) {}
    document.dispatchEvent(new CustomEvent('upskill:layout'));
  });
}

/***/ },

/***/ "./src/global/js/components/deferred-video.js"
/*!****************************************************!*\
  !*** ./src/global/js/components/deferred-video.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DeferredVideo: () => (/* binding */ DeferredVideo)
/* harmony export */ });
/**
 * Deferred background video.
 *
 * A <video data-deferred-video data-src="…"> downloads nothing while the page
 * loads. Once it nears the viewport its source is attached and it plays; the
 * still drawn beneath it covers the gap, and stays the only thing shown for a
 * reader who asked for reduced motion or less data.
 */

/**
 * Should the clip be skipped altogether?
 *
 * @return {boolean} True for reduced motion or Save-Data.
 */
function skipVideo() {
  const connection = navigator.connection;
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches || Boolean(connection && connection.saveData);
}

/**
 * Attach the source and start one video.
 *
 * @param {HTMLVideoElement} video The video to start.
 */
function start(video) {
  video.src = video.dataset.src;
  video.removeAttribute('data-src');
  video.addEventListener('playing', () => video.classList.add('is-playing'), {
    once: true
  });

  // Rejects when the browser declines to autoplay, which is not an error.
  video.play()?.catch(() => {});
}
function DeferredVideo() {
  const videos = document.querySelectorAll('video[data-deferred-video][data-src]');
  if (!videos.length || skipVideo()) {
    return;
  }
  if (!('IntersectionObserver' in window)) {
    videos.forEach(start);
    return;
  }
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        observer.unobserve(entry.target);
        start(entry.target);
      }
    });
  }, {
    rootMargin: '300px 0px'
  });
  videos.forEach(video => observer.observe(video));
}

/***/ },

/***/ "./src/global/js/components/disclosure.js"
/*!************************************************!*\
  !*** ./src/global/js/components/disclosure.js ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Disclosure: () => (/* binding */ Disclosure)
/* harmony export */ });
/**
 * Disclosure buttons (FAQ answers, drawer sub-menus).
 *
 * A `[data-disclosure]` button controls the element named by aria-controls,
 * which carries `.collapse`. The panel is open in the markup when the button
 * starts with aria-expanded="true" (the first FAQ). A `data-disclosure-group`
 * ancestor makes its disclosures single-open, like the Figma accordion.
 */
function Disclosure(root = document) {
  root.querySelectorAll('[data-disclosure]').forEach(button => {
    const panel = document.getElementById(button.getAttribute('aria-controls'));
    if (!panel) {
      return;
    }
    button.addEventListener('click', () => {
      const open = 'true' !== button.getAttribute('aria-expanded');
      const group = button.closest('[data-disclosure-group]');
      if (open && group) {
        group.querySelectorAll('[data-disclosure][aria-expanded="true"]').forEach(other => {
          if (other !== button) {
            other.setAttribute('aria-expanded', 'false');
            document.getElementById(other.getAttribute('aria-controls'))?.classList.remove('is-open');
          }
        });
      }
      button.setAttribute('aria-expanded', String(open));
      panel.classList.toggle('is-open', open);
    });
  });
}

/***/ },

/***/ "./src/global/js/components/navigation.js"
/*!************************************************!*\
  !*** ./src/global/js/components/navigation.js ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Navigation: () => (/* binding */ Navigation)
/* harmony export */ });
/**
 * Site header: sticky state, desktop mega panels and the mobile drawer.
 *
 * Everything enhances markup that already works: triggers are real buttons
 * with aria-expanded/aria-controls, and panels are plain `hidden` elements.
 */

const DESKTOP = window.matchMedia('(min-width: 1200px)');
const HOVER = window.matchMedia('(hover: hover) and (pointer: fine)');
const TRANSITION = 220;

/**
 * Show or hide a `hidden` panel with a CSS transition on `.is-open`.
 *
 * `hidden` cannot be transitioned, so it is removed first and the class added
 * on the next frame; on close the class goes first and `hidden` returns once
 * the fade has finished (unless the panel was reopened meanwhile).
 *
 * @param {HTMLElement} panel Panel element.
 * @param {boolean}     open  Target state.
 */
function setPanel(panel, open) {
  window.clearTimeout(panel.upskillTimer);
  if (open) {
    panel.hidden = false;
    window.requestAnimationFrame(() => panel.classList.add('is-open'));
    return;
  }
  panel.classList.remove('is-open');
  panel.upskillTimer = window.setTimeout(() => {
    if (!panel.classList.contains('is-open')) {
      panel.hidden = true;
    }
  }, TRANSITION);
}

/**
 * Toggle the `.is-stuck` look once the announcement bar has scrolled away.
 *
 * @param {HTMLElement} header Site header.
 */
function stickyState(header) {
  let ticking = false;
  const update = () => {
    ticking = false;
    header.classList.toggle('is-stuck', window.scrollY > 0 && header.getBoundingClientRect().top <= 0);
  };
  window.addEventListener('scroll', () => {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }, {
    passive: true
  });
  update();
}

/**
 * Desktop mega panels.
 *
 * @param {HTMLElement} nav The primary nav list.
 */
function megaMenu(nav) {
  const items = Array.from(nav.querySelectorAll('[data-mega-trigger]')).map(trigger => ({
    trigger,
    panel: document.getElementById(trigger.getAttribute('aria-controls')),
    item: trigger.closest('li')
  }));
  if (!items.length) {
    return;
  }
  let hoverTimer = 0;
  const close = (entry, restoreFocus = false) => {
    if ('true' !== entry.trigger.getAttribute('aria-expanded')) {
      return;
    }
    entry.trigger.setAttribute('aria-expanded', 'false');
    setPanel(entry.panel, false);
    if (restoreFocus) {
      entry.trigger.focus();
    }
  };
  const open = entry => {
    items.forEach(other => other !== entry && close(other));
    entry.trigger.setAttribute('aria-expanded', 'true');
    setPanel(entry.panel, true);
  };
  items.forEach(entry => {
    if (!entry.panel) {
      return;
    }
    entry.trigger.addEventListener('click', () => {
      if ('true' === entry.trigger.getAttribute('aria-expanded')) {
        close(entry);
      } else {
        open(entry);
      }
    });

    // Hover intent for mouse users: a short delay in each direction stops
    // panels flashing open as the pointer crosses the bar.
    entry.item.addEventListener('mouseenter', () => {
      if (!HOVER.matches) {
        return;
      }
      window.clearTimeout(hoverTimer);
      hoverTimer = window.setTimeout(() => open(entry), 80);
    });
    entry.item.addEventListener('mouseleave', () => {
      if (!HOVER.matches) {
        return;
      }
      window.clearTimeout(hoverTimer);
      hoverTimer = window.setTimeout(() => close(entry), 160);
    });

    // Tabbing out of the item closes its panel.
    entry.item.addEventListener('focusout', event => {
      if (event.relatedTarget && !entry.item.contains(event.relatedTarget)) {
        close(entry);
      }
    });
  });
  document.addEventListener('keydown', event => {
    if ('Escape' !== event.key) {
      return;
    }
    const active = items.find(entry => entry.item.contains(document.activeElement));
    items.forEach(entry => close(entry, entry === active));
  });
  document.addEventListener('click', event => {
    if (!nav.contains(event.target)) {
      items.forEach(entry => close(entry));
    }
  });
}

/**
 * The mobile drawer.
 *
 * @param {HTMLElement} header Site header.
 */
function drawer(header) {
  const toggle = header.querySelector('[data-drawer-toggle]');
  const sheet = document.getElementById('site-drawer');
  if (!toggle || !sheet) {
    return;
  }
  const label = toggle.querySelector('[data-drawer-label]');
  const setOpen = open => {
    if (open) {
      // The sheet starts under whatever is visible of the header — the
      // announcement bar may or may not still be on screen.
      sheet.style.setProperty('--upskill-drawer-top', `${Math.max(0, header.getBoundingClientRect().bottom)}px`);
    }
    toggle.setAttribute('aria-expanded', String(open));
    document.documentElement.classList.toggle('has-drawer', open);
    setPanel(sheet, open);
    if (label) {
      label.textContent = open ? label.dataset.closeLabel : label.dataset.openLabel;
    }
  };
  toggle.addEventListener('click', () => setOpen('true' !== toggle.getAttribute('aria-expanded')));
  document.addEventListener('keydown', event => {
    if ('Escape' === event.key && 'true' === toggle.getAttribute('aria-expanded')) {
      setOpen(false);
      toggle.focus();
    }
  });
  DESKTOP.addEventListener('change', event => {
    if (event.matches) {
      setOpen(false);
    }
  });
}
function Navigation() {
  const header = document.querySelector('[data-site-header]');
  if (!header) {
    return;
  }
  stickyState(header);
  drawer(header);
  const nav = header.querySelector('[data-mega-nav]');
  if (nav) {
    megaMenu(nav);
  }
}

/***/ },

/***/ "./src/global/js/components/reveal.js"
/*!********************************************!*\
  !*** ./src/global/js/components/reveal.js ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Reveal: () => (/* binding */ Reveal),
/* harmony export */   observeReveals: () => (/* binding */ observeReveals)
/* harmony export */ });
/**
 * Scroll reveal.
 *
 * Elements marked .reveal fade and lift into place once they enter the
 * viewport. The hidden state lives behind `.js .reveal`, so with the script
 * absent — or once the observer has run — everything is simply visible.
 *
 * Honours prefers-reduced-motion by revealing everything immediately; the CSS
 * also removes the transition for those users.
 */

/**
 * One observer for the whole document, created on first use.
 *
 * It is shared rather than made per call because markup that arrives later —
 * a filtered listing swapping its results, say — has to join the same pass;
 * a second observer would double up on anything already being watched.
 *
 * @type {IntersectionObserver|null}
 */
let observer = null;

/**
 * Is the reader asking for no motion, or is IntersectionObserver missing?
 *
 * @return {boolean} True when everything should simply be shown.
 */
function showImmediately() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window);
}

/**
 * Start watching every .reveal inside a root, revealing each as it arrives.
 *
 * Safe to call again for markup added after startup.
 *
 * @param {ParentNode} root Element or document to search. Defaults to document.
 */
function observeReveals(root = document) {
  const elements = root.querySelectorAll('.reveal');
  if (!elements.length) {
    return;
  }
  if (showImmediately()) {
    elements.forEach(element => element.classList.add('is-in'));
    return;
  }
  if (!observer) {
    observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          observer.unobserve(entry.target);
        }
      });
    }, {
      rootMargin: '0px 0px -8% 0px',
      threshold: 0.01
    });
  }
  elements.forEach(element => {
    if (!element.classList.contains('is-in')) {
      observer.observe(element);
    }
  });
}

/**
 * Wire the reveals present at startup.
 */
function Reveal() {
  observeReveals(document);
}

/***/ },

/***/ "./src/global/js/components/search.js"
/*!********************************************!*\
  !*** ./src/global/js/components/search.js ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Search: () => (/* binding */ Search)
/* harmony export */ });
/**
 * Header search dialog.
 *
 * A native <dialog>: showModal() traps focus, Escape closes it and focus
 * returns to the opener, all without extra code.
 */
function Search() {
  const dialog = document.querySelector('[data-search-dialog]');
  if (!dialog || 'function' !== typeof dialog.showModal) {
    return;
  }
  document.querySelectorAll('[data-search-open]').forEach(button => {
    button.addEventListener('click', () => {
      dialog.showModal();
      dialog.querySelector('input[type="search"]')?.focus();
    });
  });

  // A click on the backdrop lands on the dialog element itself.
  dialog.addEventListener('click', event => {
    if (event.target === dialog) {
      dialog.close();
    }
  });
}

/***/ },

/***/ "./src/global/js/main.js"
/*!*******************************!*\
  !*** ./src/global/js/main.js ***!
  \*******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/dom-ready */ "@wordpress/dom-ready");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_announcement__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/announcement */ "./src/global/js/components/announcement.js");
/* harmony import */ var _components_navigation__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/navigation */ "./src/global/js/components/navigation.js");
/* harmony import */ var _components_search__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/search */ "./src/global/js/components/search.js");
/* harmony import */ var _components_disclosure__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/disclosure */ "./src/global/js/components/disclosure.js");
/* harmony import */ var _components_reveal__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/reveal */ "./src/global/js/components/reveal.js");
/* harmony import */ var _components_deferred_video__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/deferred-video */ "./src/global/js/components/deferred-video.js");







_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default()(() => {
  (0,_components_announcement__WEBPACK_IMPORTED_MODULE_1__.Announcement)();
  (0,_components_navigation__WEBPACK_IMPORTED_MODULE_2__.Navigation)();
  (0,_components_search__WEBPACK_IMPORTED_MODULE_3__.Search)();
  (0,_components_disclosure__WEBPACK_IMPORTED_MODULE_4__.Disclosure)();
  (0,_components_reveal__WEBPACK_IMPORTED_MODULE_5__.Reveal)();
  (0,_components_deferred_video__WEBPACK_IMPORTED_MODULE_6__.DeferredVideo)();
});

/***/ },

/***/ "./src/global/tailwind/tailwind.css"
/*!******************************************!*\
  !*** ./src/global/tailwind/tailwind.css ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/global/scss/main.scss"
/*!***********************************!*\
  !*** ./src/global/scss/main.scss ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "@wordpress/dom-ready"
/*!**********************************!*\
  !*** external ["wp","domReady"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["domReady"];

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = (module) => {
/******/ 		const getter = module && module.__esModule ?
/******/ 			() => (module['default']) :
/******/ 			() => (module);
/******/ 		__webpack_require__.d(getter, { a: getter });
/******/ 		return getter;
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	// define getter/value functions for harmony exports
/******/ 	__webpack_require__.d = (exports, definition) => {
/******/ 		for(var key in definition) {
/******/ 			if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 				Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 			}
/******/ 		}
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop));
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*****************************!*\
  !*** ./src/global/index.js ***!
  \*****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _js_main__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./js/main */ "./src/global/js/main.js");
/* harmony import */ var _tailwind_tailwind_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./tailwind/tailwind.css */ "./src/global/tailwind/tailwind.css");
/* harmony import */ var _scss_main_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./scss/main.scss */ "./src/global/scss/main.scss");


// Tailwind first, then the SCSS: the SCSS only styles chrome Tailwind cannot
// reach (state-driven header/menu/drawer, plugin markup) or deliberately
// overrides a utility, and source order is clearer than !important.


})();

/******/ })()
;
//# sourceMappingURL=index.js.map