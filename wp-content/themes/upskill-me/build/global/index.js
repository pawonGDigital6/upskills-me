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

/***/ "./src/global/js/components/article.js"
/*!*********************************************!*\
  !*** ./src/global/js/components/article.js ***!
  \*********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Article: () => (/* binding */ Article)
/* harmony export */ });
/**
 * Blog article and policy pages: the contents list's reading position, the
 * phone "See more" toggle on long contents lists, and the copy-link share
 * button.
 */

/**
 * Mark the contents link of the section being read: the last heading that has
 * passed the top third of the screen (the first one until any has). Worked out
 * from scroll position, so scrolling back up always lands on the right item.
 */
function contents() {
  const list = document.querySelector('[data-article-contents]');
  if (!list) {
    return;
  }
  const links = Array.from(list.querySelectorAll('a[href^="#"]'));
  const pairs = links.map(link => [link, document.getElementById(decodeURIComponent(link.hash.slice(1)))]).filter(([, heading]) => heading);
  if (!pairs.length) {
    return;
  }
  let frame = 0;
  const update = () => {
    const line = window.innerHeight / 3;
    let current = pairs[0][0];
    pairs.forEach(([link, heading]) => {
      if (heading.getBoundingClientRect().top <= line) {
        current = link;
      }
    });
    links.forEach(link => link.classList.toggle('is-active', link === current));
  };
  window.addEventListener('scroll', () => {
    window.cancelAnimationFrame(frame);
    frame = window.requestAnimationFrame(update);
  }, {
    passive: true
  });
  update();
}

/**
 * Long contents lists show their first items on phones; the toggle reveals
 * the rest (the list carries .is-expanded) and swaps its label.
 */
function contentsMore() {
  document.querySelectorAll('[data-contents-more]').forEach(button => {
    const list = document.getElementById(button.getAttribute('aria-controls'));
    const label = button.querySelector('[data-contents-more-label]');
    if (!list || !label) {
      return;
    }
    const more = label.textContent;
    button.addEventListener('click', () => {
      const expanded = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', String(expanded));
      list.classList.toggle('is-expanded', expanded);
      label.textContent = expanded ? button.dataset.lessLabel : more;
    });
  });
}

/**
 * Copy text with the Clipboard API, falling back to a selected textarea where
 * the API is missing or refused (non-HTTPS sites, older browsers).
 *
 * @param {string} text Text to copy.
 * @return {Promise<void>} Resolves once copied; rejects if it could not be.
 */
function copyText(text) {
  const fallback = () => {
    const field = document.createElement('textarea');
    field.value = text;
    field.setAttribute('readonly', '');
    field.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
    document.body.append(field);
    field.select();
    let copied = false;
    try {
      copied = document.execCommand('copy');
    } catch (error) {
      copied = false;
    }
    field.remove();
    return copied ? Promise.resolve() : Promise.reject(new Error('copy'));
  };
  if (navigator.clipboard && window.isSecureContext) {
    return navigator.clipboard.writeText(text).catch(fallback);
  }
  return fallback();
}

/**
 * Copy the article URL. The button swaps its icon for a tick and a tooltip
 * live region says "Link copied!" (or that it failed) for a few seconds.
 */
function copyLink() {
  document.querySelectorAll('[data-copy-link]').forEach(button => {
    const status = button.parentElement.querySelector('[data-copy-status]');
    let timer = 0;
    const show = (message, copied) => {
      window.clearTimeout(timer);
      button.toggleAttribute('data-copied', copied);
      if (status) {
        status.textContent = message;
        status.toggleAttribute('data-visible', true);
      }
      timer = window.setTimeout(() => {
        button.removeAttribute('data-copied');
        if (status) {
          status.removeAttribute('data-visible');
          // Emptied after the fade, so the same message is announced again next time.
          window.setTimeout(() => !status.hasAttribute('data-visible') && (status.textContent = ''), 200);
        }
      }, copied ? 2500 : 5000);
    };
    button.addEventListener('click', () => {
      copyText(button.dataset.copyLink || window.location.href).then(() => show(button.dataset.copiedLabel, true), () => show(button.dataset.copyFailedLabel, false));
    });
  });
}
function Article() {
  contents();
  contentsMore();
  copyLink();
}

/***/ },

/***/ "./src/global/js/components/buy-box.js"
/*!*********************************************!*\
  !*** ./src/global/js/components/buy-box.js ***!
  \*********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   BuyBox: () => (/* binding */ BuyBox)
/* harmony export */ });
/**
 * Course buy box (learndash/ld30/course.php).
 *
 * A plain form posting to WooCommerce's add-to-cart. Here: choosing a level
 * updates the shown price and the variation's attribute fields WooCommerce
 * checks, and the − / + buttons step the seat count.
 */
function BuyBox(root = document) {
  root.querySelectorAll('[data-buy-box]').forEach(form => {
    const price = form.querySelector('[data-buy-price]');
    const unit = form.querySelector('[data-buy-unit]');
    const seats = form.querySelector('input[name="quantity"]');
    const unitText = unit?.textContent.trim().replace(/·.*$/, '').trim();
    form.addEventListener('change', event => {
      const level = event.target.closest('input[name="variation_id"]');
      if (!level) {
        return;
      }
      if (price) {
        price.textContent = level.dataset.price;
      }
      if (unit && unitText) {
        unit.textContent = `${unitText} · ${level.dataset.label}`;
      }
      form.querySelectorAll('[data-buy-attribute]').forEach(field => field.remove());
      Object.entries(JSON.parse(level.dataset.attributes || '{}')).forEach(([name, value]) => {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        field.value = value;
        field.dataset.buyAttribute = '';
        form.append(field);
      });
    });
    form.querySelectorAll('[data-buy-step]').forEach(button => {
      button.addEventListener('click', () => {
        const next = Math.min(Number(seats.max) || 999, Math.max(Number(seats.min) || 1, (Number(seats.value) || 1) + Number(button.dataset.buyStep)));
        seats.value = String(next);
      });
    });
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

/***/ "./src/global/js/components/library.js"
/*!*********************************************!*\
  !*** ./src/global/js/components/library.js ***!
  \*********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Library: () => (/* binding */ Library)
/* harmony export */ });
/**
 * Training library (template-library.php).
 *
 * The filters are a plain GET form that works without JavaScript. Here the
 * results update in place from the library REST route (inc/lms-library.php,
 * address in the form's data-library):
 * - a ticked filter, "Sort by", the search, a suggestion, "Clear all" and
 *   "Load more training" fetch the new cards, labels and filter counts;
 * - the address bar follows (history), so results stay shareable and Back
 *   returns to the previous results;
 * - while loading the cards dim (aria-busy); a failed request shows a
 *   "Try again" message and keeps the current cards;
 * - on industry, category and level pages the page's own term is ticked by
 *   default (data-library-default): "Clear all" ticks it again, and unticking
 *   it adds all=1 so the address (and a reload) keeps it off;
 * - on phones the filters sit in a bottom sheet (<dialog>) opened by the
 *   filter button; its counts and "Show N results" update as choices change,
 *   and the button closes the sheet onto the results.
 */
function Library(root = document) {
  const form = root.querySelector('[data-library]');
  if (!form) {
    return;
  }
  const endpoint = form.dataset.library;
  const sheet = form.querySelector('[data-library-filters]');
  const results = form.querySelector('[data-library-results]');
  const error = form.querySelector('[data-library-error]');
  const search = root.querySelector('[data-library-search]');
  const searchInput = search?.querySelector('input[name="q"]');
  const isSheet = () => sheet?.open && sheet.matches(':modal');
  let controller = null;
  let retry = null;
  let current = window.location.search;

  // The current choices as query parameters (empty values left out).
  const params = (changes = {}) => {
    const data = new FormData(form);
    const query = new URLSearchParams();
    data.delete('q');
    data.delete('show');
    const words = (searchInput?.value ?? '').trim();
    if (words) {
      query.set('q', words);
    }
    for (const [key, value] of data) {
      if (value && !('sort' === key && 'newest' === value)) {
        query.append(key, value);
      }
    }
    if (form.querySelector('input[data-library-default]:not(:checked)')) {
      query.set('all', '1');
    }
    Object.entries(changes).forEach(([key, value]) => query.set(key, value));
    return query;
  };

  // Put the form back to a set of parameters (Back/Forward).
  const restore = query => {
    if (searchInput) {
      searchInput.value = query.get('q') ?? '';
    }
    form.querySelectorAll('input[type="checkbox"]').forEach(box => {
      box.checked = query.getAll(box.name).includes(box.value) || box.hasAttribute('data-library-default') && !query.has('all');
    });
    const sort = form.querySelector('select[name="sort"]');
    if (sort) {
      sort.value = query.get('sort') ?? 'newest';
    }
  };
  const render = data => {
    results.innerHTML = data.html;
    Object.entries(data.labels).forEach(([key, text]) => {
      form.querySelectorAll(`[data-library-label="${key}"]`).forEach(el => {
        el.textContent = text;
      });
    });

    // Filter counts: a choice with nothing to show is dimmed and disabled, unless ticked.
    form.querySelectorAll('input[type="checkbox"]').forEach(box => {
      const key = box.name.replace('[]', '');
      const n = 'free' === key ? data.counts.free : data.counts[key]?.[box.value] ?? 0;
      const label = box.closest('label');
      const count = label?.querySelector('[data-library-count]');
      if (count) {
        count.textContent = n.toLocaleString();
      }
      box.disabled = !n && !box.checked;
      label?.classList.toggle('opacity-45', !n && !box.checked);
    });
    form.querySelector('[data-library-dot]')?.toggleAttribute('hidden', !data.active);

    // The hidden search field keeps the no-JavaScript submit in step.
    form.querySelector('[data-library-q]')?.setAttribute('value', searchInput?.value.trim() ?? '');
  };

  /**
   * Fetch and show the results for a set of parameters.
   *
   * @param {URLSearchParams} query   Parameters.
   * @param {Object}          options history: 'push' | 'replace' | false; focusFrom: card index to focus after.
   */
  const update = async (query, {
    history = 'push',
    focusFrom = -1
  } = {}) => {
    controller?.abort();
    const request = new AbortController();
    controller = request;
    retry = () => update(query, {
      history,
      focusFrom
    });
    results.setAttribute('aria-busy', 'true');
    error.hidden = true;
    try {
      const url = new URL(endpoint, window.location.href);
      query.forEach((value, key) => url.searchParams.append(key, value));
      const response = await fetch(url, {
        signal: request.signal,
        headers: {
          Accept: 'application/json'
        }
      });
      if (!response.ok) {
        throw new Error(response.status);
      }
      render(await response.json());
      current = query.toString() ? `?${query}` : '';
      if (history) {
        const address = `${window.location.pathname}${current}#results`;
        window.history['push' === history ? 'pushState' : 'replaceState']({
          library: true
        }, '', address);
      }
      if (focusFrom >= 0) {
        results.querySelectorAll('article')[focusFrom]?.querySelector('a[href]')?.focus();
      }
    } catch (failure) {
      if ('AbortError' !== failure.name) {
        error.hidden = false;
      }
    } finally {
      if (!request.signal.aborted) {
        results.removeAttribute('aria-busy');
      }
    }
  };
  const showResults = () => form.scrollIntoView({
    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
  });
  form.querySelector('[data-library-open]')?.addEventListener('click', () => sheet?.showModal());
  form.querySelector('[data-library-close]')?.addEventListener('click', () => sheet?.close());

  // A tap on the backdrop (outside the sheet's box) closes it.
  sheet?.addEventListener('click', event => {
    if (event.target === sheet && isSheet()) {
      const box = sheet.getBoundingClientRect();
      const inside = event.clientX >= box.left && event.clientX <= box.right && event.clientY >= box.top && event.clientY <= box.bottom;
      if (!inside) {
        sheet.close();
      }
    }
  });

  // Ticks inside the phone sheet replace the history entry, so Back skips them.
  form.addEventListener('change', () => update(params(), {
    history: isSheet() ? 'replace' : 'push'
  }));

  // "Show N results" (phones): the results are already current.
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (isSheet()) {
      sheet.close();
      showResults();
    } else {
      update(params());
    }
  });
  search?.addEventListener('submit', event => {
    event.preventDefault();
    update(params());
    showResults();
  });
  root.querySelectorAll('[data-library-suggest]').forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      if (searchInput) {
        searchInput.value = link.dataset.librarySuggest;
      }
      update(params());
      showResults();
    });
  });

  // Links inside the form: "Clear all", "Clear all filters", "Load more training".
  form.addEventListener('click', event => {
    const reset = event.target.closest('[data-library-reset]');
    const more = event.target.closest('[data-library-more]');
    if (reset) {
      event.preventDefault();
      form.querySelectorAll('input[type="checkbox"]').forEach(box => {
        box.checked = box.hasAttribute('data-library-default');
      });
      if ('all' === reset.dataset.libraryReset && searchInput) {
        searchInput.value = '';
      }
      update(params());
    } else if (more) {
      event.preventDefault();
      update(params({
        show: more.dataset.libraryMore
      }), {
        history: 'replace',
        focusFrom: results.querySelectorAll('article').length
      });
    }
  });
  form.querySelector('[data-library-retry]')?.addEventListener('click', () => retry?.());
  window.addEventListener('popstate', () => {
    // Only a change of filters needs new results (not a jump to an anchor).
    if (window.location.search === current) {
      return;
    }
    const query = new URLSearchParams(window.location.search);
    restore(query);
    // From the form, so a page default ticked by restore() is part of the request.
    update(params(query.has('show') ? {
      show: query.get('show')
    } : {}), {
      history: false
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
const SLIDE = 320;

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
 * Slide a `hidden` panel open or shut by animating its height (what jQuery's
 * slideToggle does; jQuery is not loaded on the front end).
 *
 * The slide always starts from the panel's current height, so toggling again
 * mid-slide reverses smoothly. Once settled the inline height is cleared and a
 * closed panel gets `hidden` back, dropping its links out of the tab order.
 *
 * @param {HTMLElement} panel Panel element with a CSS height transition.
 * @param {boolean}     open  Target state.
 */
function slide(panel, open) {
  window.clearTimeout(panel.upskillTimer);
  const from = panel.hidden ? 0 : panel.getBoundingClientRect().height;
  panel.hidden = false;
  panel.style.height = '';
  const to = open ? panel.getBoundingClientRect().height : 0;
  panel.style.height = `${from}px`;
  panel.getBoundingClientRect(); // Commit the start height before the transition.
  panel.style.height = `${to}px`;
  panel.upskillTimer = window.setTimeout(() => {
    panel.style.height = '';
    panel.hidden = !open;
  }, SLIDE);
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

  // Parent rows expand their sub-menu in place.
  sheet.querySelectorAll('[data-drawer-sub]').forEach(button => {
    const panel = document.getElementById(button.getAttribute('aria-controls'));
    if (!panel) {
      return;
    }
    button.addEventListener('click', () => {
      const open = 'true' !== button.getAttribute('aria-expanded');
      button.setAttribute('aria-expanded', String(open));
      slide(panel, open);
    });
  });
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

/***/ "./src/global/js/components/password-toggle.js"
/*!*****************************************************!*\
  !*** ./src/global/js/components/password-toggle.js ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PasswordToggle: () => (/* binding */ PasswordToggle)
/* harmony export */ });
/**
 * Show / hide password (the eye button in the sign-in form).
 *
 * A `[data-password-toggle]` button controls the input named by
 * aria-controls and holds two icons: eye-off (hidden, the default as Figma
 * draws it) and eye (shown).
 */
function PasswordToggle(root = document) {
  root.querySelectorAll('[data-password-toggle]').forEach(button => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const label = button.querySelector('.screen-reader-text');
    const [hiddenIcon, shownIcon] = button.querySelectorAll('svg');
    if (!input) {
      return;
    }
    const showLabel = label?.textContent;
    button.addEventListener('click', () => {
      const show = 'password' === input.type;
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-pressed', String(show));
      hiddenIcon?.classList.toggle('hidden', show);
      shownIcon?.classList.toggle('hidden', !show);
      if (label) {
        label.textContent = show ? button.dataset.hideLabel || 'Hide password' : showLabel;
      }
    });
  });
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
/* harmony import */ var _components_article__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./components/article */ "./src/global/js/components/article.js");
/* harmony import */ var _components_password_toggle__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components/password-toggle */ "./src/global/js/components/password-toggle.js");
/* harmony import */ var _components_library__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./components/library */ "./src/global/js/components/library.js");
/* harmony import */ var _components_buy_box__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./components/buy-box */ "./src/global/js/components/buy-box.js");











_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default()(() => {
  (0,_components_announcement__WEBPACK_IMPORTED_MODULE_1__.Announcement)();
  (0,_components_navigation__WEBPACK_IMPORTED_MODULE_2__.Navigation)();
  (0,_components_search__WEBPACK_IMPORTED_MODULE_3__.Search)();
  (0,_components_disclosure__WEBPACK_IMPORTED_MODULE_4__.Disclosure)();
  (0,_components_reveal__WEBPACK_IMPORTED_MODULE_5__.Reveal)();
  (0,_components_deferred_video__WEBPACK_IMPORTED_MODULE_6__.DeferredVideo)();
  (0,_components_article__WEBPACK_IMPORTED_MODULE_7__.Article)();
  (0,_components_password_toggle__WEBPACK_IMPORTED_MODULE_8__.PasswordToggle)();
  (0,_components_library__WEBPACK_IMPORTED_MODULE_9__.Library)();
  (0,_components_buy_box__WEBPACK_IMPORTED_MODULE_10__.BuyBox)();
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