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
	const list = document.querySelector( '[data-article-contents]' );

	if ( ! list ) {
		return;
	}

	const links = Array.from( list.querySelectorAll( 'a[href^="#"]' ) );
	const pairs = links
		.map( ( link ) => [ link, document.getElementById( decodeURIComponent( link.hash.slice( 1 ) ) ) ] )
		.filter( ( [ , heading ] ) => heading );

	if ( ! pairs.length ) {
		return;
	}

	let frame = 0;

	const update = () => {
		const line = window.innerHeight / 3;
		let current = pairs[ 0 ][ 0 ];

		pairs.forEach( ( [ link, heading ] ) => {
			if ( heading.getBoundingClientRect().top <= line ) {
				current = link;
			}
		} );

		links.forEach( ( link ) => link.classList.toggle( 'is-active', link === current ) );
	};

	window.addEventListener(
		'scroll',
		() => {
			window.cancelAnimationFrame( frame );
			frame = window.requestAnimationFrame( update );
		},
		{ passive: true }
	);

	update();
}

/**
 * Long contents lists show their first items on phones; the toggle reveals
 * the rest (the list carries .is-expanded) and swaps its label.
 */
function contentsMore() {
	document.querySelectorAll( '[data-contents-more]' ).forEach( ( button ) => {
		const list = document.getElementById( button.getAttribute( 'aria-controls' ) );
		const label = button.querySelector( '[data-contents-more-label]' );

		if ( ! list || ! label ) {
			return;
		}

		const more = label.textContent;

		button.addEventListener( 'click', () => {
			const expanded = button.getAttribute( 'aria-expanded' ) !== 'true';

			button.setAttribute( 'aria-expanded', String( expanded ) );
			list.classList.toggle( 'is-expanded', expanded );
			label.textContent = expanded ? button.dataset.lessLabel : more;
		} );
	} );
}

/**
 * Copy text with the Clipboard API, falling back to a selected textarea where
 * the API is missing or refused (non-HTTPS sites, older browsers).
 *
 * @param {string} text Text to copy.
 * @return {Promise<void>} Resolves once copied; rejects if it could not be.
 */
function copyText( text ) {
	const fallback = () => {
		const field = document.createElement( 'textarea' );

		field.value = text;
		field.setAttribute( 'readonly', '' );
		field.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
		document.body.append( field );
		field.select();

		let copied = false;

		try {
			copied = document.execCommand( 'copy' );
		} catch ( error ) {
			copied = false;
		}

		field.remove();

		return copied ? Promise.resolve() : Promise.reject( new Error( 'copy' ) );
	};

	if ( navigator.clipboard && window.isSecureContext ) {
		return navigator.clipboard.writeText( text ).catch( fallback );
	}

	return fallback();
}

/**
 * Copy the article URL. The button swaps its icon for a tick and a tooltip
 * live region says "Link copied!" (or that it failed) for a few seconds.
 */
function copyLink() {
	document.querySelectorAll( '[data-copy-link]' ).forEach( ( button ) => {
		const status = button.parentElement.querySelector( '[data-copy-status]' );
		let timer = 0;

		const show = ( message, copied ) => {
			window.clearTimeout( timer );
			button.toggleAttribute( 'data-copied', copied );

			if ( status ) {
				status.textContent = message;
				status.toggleAttribute( 'data-visible', true );
			}

			timer = window.setTimeout(
				() => {
					button.removeAttribute( 'data-copied' );

					if ( status ) {
						status.removeAttribute( 'data-visible' );
						// Emptied after the fade, so the same message is announced again next time.
						window.setTimeout( () => ! status.hasAttribute( 'data-visible' ) && ( status.textContent = '' ), 200 );
					}
				},
				copied ? 2500 : 5000
			);
		};

		button.addEventListener( 'click', () => {
			copyText( button.dataset.copyLink || window.location.href ).then(
				() => show( button.dataset.copiedLabel, true ),
				() => show( button.dataset.copyFailedLabel, false )
			);
		} );
	} );
}

export function Article() {
	contents();
	contentsMore();
	copyLink();
}
