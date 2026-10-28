/**
 * Training library (template-library.php).
 *
 * The filters are a plain GET form that works without JavaScript. Here the
 * results update in place from the library REST route (inc/lms-library.php,
 * address in the form's data-library):
 * - a ticked filter, "Sort by", the search, a suggestion and "Clear all"
 *   fetch the new cards, labels and filter counts, always from page one;
 * - a page link (Previous, a number, Next) fetches that page (pg), scrolls back
 *   to the top of the results and focuses its first card;
 * - free sessions in the new cards get the video transport (VideoPlayer);
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
import { VideoPlayer } from './video-player';

export function Library( root = document ) {
	const form = root.querySelector( '[data-library]' );

	if ( ! form ) {
		return;
	}

	const endpoint = form.dataset.library;
	const sheet = form.querySelector( '[data-library-filters]' );
	const results = form.querySelector( '[data-library-results]' );
	const error = form.querySelector( '[data-library-error]' );
	const search = root.querySelector( '[data-library-search]' );
	const searchInput = search?.querySelector( 'input[name="q"]' );
	const isSheet = () => sheet?.open && sheet.matches( ':modal' );
	let controller = null;
	let retry = null;
	let current = window.location.search;

	// The current choices as query parameters (empty values left out).
	const params = ( changes = {} ) => {
		const data = new FormData( form );
		const query = new URLSearchParams();

		data.delete( 'q' );
		data.delete( 'pg' );

		const words = ( searchInput?.value ?? '' ).trim();

		if ( words ) {
			query.set( 'q', words );
		}

		for ( const [ key, value ] of data ) {
			if ( value && ! ( 'sort' === key && 'newest' === value ) ) {
				query.append( key, value );
			}
		}

		if ( form.querySelector( 'input[data-library-default]:not(:checked)' ) ) {
			query.set( 'all', '1' );
		}

		Object.entries( changes ).forEach( ( [ key, value ] ) => query.set( key, value ) );

		return query;
	};

	// Put the form back to a set of parameters (Back/Forward).
	const restore = ( query ) => {
		if ( searchInput ) {
			searchInput.value = query.get( 'q' ) ?? '';
		}

		form.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( box ) => {
			box.checked = query.getAll( box.name ).includes( box.value ) || ( box.hasAttribute( 'data-library-default' ) && ! query.has( 'all' ) );
		} );

		const sort = form.querySelector( 'select[name="sort"]' );

		if ( sort ) {
			sort.value = query.get( 'sort' ) ?? 'newest';
		}
	};

	const render = ( data ) => {
		results.innerHTML = data.html;
		VideoPlayer( results );

		Object.entries( data.labels ).forEach( ( [ key, text ] ) => {
			form.querySelectorAll( `[data-library-label="${ key }"]` ).forEach( ( el ) => {
				el.textContent = text;
			} );
		} );

		// Filter counts: a choice with nothing to show is dimmed and disabled, unless ticked.
		form.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( box ) => {
			const key = box.name.replace( '[]', '' );
			const n = 'free' === key ? data.counts.free : data.counts[ key ]?.[ box.value ] ?? 0;
			const label = box.closest( 'label' );
			const count = label?.querySelector( '[data-library-count]' );

			if ( count ) {
				count.textContent = n.toLocaleString();
			}

			box.disabled = ! n && ! box.checked;
			label?.classList.toggle( 'opacity-45', ! n && ! box.checked );
		} );

		form.querySelector( '[data-library-dot]' )?.toggleAttribute( 'hidden', ! data.active );

		// The hidden search field keeps the no-JavaScript submit in step.
		form.querySelector( '[data-library-q]' )?.setAttribute( 'value', searchInput?.value.trim() ?? '' );
	};

	/**
	 * Fetch and show the results for a set of parameters.
	 *
	 * @param {URLSearchParams} query   Parameters.
	 * @param {Object}          options history: 'push' | 'replace' | false; turn: a new page, so scroll to its top and focus its first card.
	 */
	const update = async ( query, { history = 'push', turn = false } = {} ) => {
		controller?.abort();
		const request = new AbortController();
		controller = request;
		retry = () => update( query, { history, turn } );

		results.setAttribute( 'aria-busy', 'true' );
		error.hidden = true;

		try {
			const url = new URL( endpoint, window.location.href );

			query.forEach( ( value, key ) => url.searchParams.append( key, value ) );

			const response = await fetch( url, { signal: request.signal, headers: { Accept: 'application/json' } } );

			if ( ! response.ok ) {
				throw new Error( response.status );
			}

			render( await response.json() );

			current = query.toString() ? `?${ query }` : '';

			if ( history ) {
				const address = `${ window.location.pathname }${ current }#results`;

				window.history[ 'push' === history ? 'pushState' : 'replaceState' ]( { library: true }, '', address );
			}

			if ( turn ) {
				showResults();
				results.querySelector( 'article a[href]' )?.focus( { preventScroll: true } );
			}
		} catch ( failure ) {
			if ( 'AbortError' !== failure.name ) {
				error.hidden = false;
			}
		} finally {
			if ( ! request.signal.aborted ) {
				results.removeAttribute( 'aria-busy' );
			}
		}
	};

	const showResults = () => form.scrollIntoView( { behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );

	form.querySelector( '[data-library-open]' )?.addEventListener( 'click', () => sheet?.showModal() );
	form.querySelector( '[data-library-close]' )?.addEventListener( 'click', () => sheet?.close() );

	// A tap on the backdrop (outside the sheet's box) closes it.
	sheet?.addEventListener( 'click', ( event ) => {
		if ( event.target === sheet && isSheet() ) {
			const box = sheet.getBoundingClientRect();
			const inside = event.clientX >= box.left && event.clientX <= box.right && event.clientY >= box.top && event.clientY <= box.bottom;

			if ( ! inside ) {
				sheet.close();
			}
		}
	} );

	// Ticks inside the phone sheet replace the history entry, so Back skips them.
	form.addEventListener( 'change', () => update( params(), { history: isSheet() ? 'replace' : 'push' } ) );

	// "Show N results" (phones): the results are already current.
	form.addEventListener( 'submit', ( event ) => {
		event.preventDefault();

		if ( isSheet() ) {
			sheet.close();
			showResults();
		} else {
			update( params() );
		}
	} );

	search?.addEventListener( 'submit', ( event ) => {
		event.preventDefault();
		update( params() );
		showResults();
	} );

	root.querySelectorAll( '[data-library-suggest]' ).forEach( ( link ) => {
		link.addEventListener( 'click', ( event ) => {
			event.preventDefault();

			if ( searchInput ) {
				searchInput.value = link.dataset.librarySuggest;
			}

			update( params() );
			showResults();
		} );
	} );

	// Links inside the form: "Clear all", "Clear all filters", the page links.
	form.addEventListener( 'click', ( event ) => {
		const reset = event.target.closest( '[data-library-reset]' );
		const page = event.target.closest( '[data-library-page]' );

		if ( reset ) {
			event.preventDefault();

			form.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( box ) => {
				box.checked = box.hasAttribute( 'data-library-default' );
			} );

			if ( 'all' === reset.dataset.libraryReset && searchInput ) {
				searchInput.value = '';
			}

			update( params() );
		} else if ( page ) {
			event.preventDefault();
			// Page one has no pg, so its address matches the unpaged results.
			update( params( '1' === page.dataset.libraryPage ? {} : { pg: page.dataset.libraryPage } ), { turn: true } );
		}
	} );

	form.querySelector( '[data-library-retry]' )?.addEventListener( 'click', () => retry?.() );

	window.addEventListener( 'popstate', () => {
		// Only a change of filters needs new results (not a jump to an anchor).
		if ( window.location.search === current ) {
			return;
		}

		const query = new URLSearchParams( window.location.search );

		restore( query );
		// From the form, so a page default ticked by restore() is part of the request.
		update( params( query.has( 'pg' ) ? { pg: query.get( 'pg' ) } : {} ), { history: false } );
	} );
}
