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
	return (
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ||
		! ( 'IntersectionObserver' in window )
	);
}

/**
 * Start watching every .reveal inside a root, revealing each as it arrives.
 *
 * Safe to call again for markup added after startup.
 *
 * @param {ParentNode} root Element or document to search. Defaults to document.
 */
export function observeReveals( root = document ) {
	const elements = root.querySelectorAll( '.reveal' );

	if ( ! elements.length ) {
		return;
	}

	if ( showImmediately() ) {
		elements.forEach( ( element ) => element.classList.add( 'is-in' ) );
		return;
	}

	if ( ! observer ) {
		observer = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-in' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.01 }
		);
	}

	elements.forEach( ( element ) => {
		if ( ! element.classList.contains( 'is-in' ) ) {
			observer.observe( element );
		}
	} );
}

/**
 * Wire the reveals present at startup.
 */
export function Reveal() {
	observeReveals( document );
}
