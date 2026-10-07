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
 * Copy the article URL; the button's accessible name confirms it.
 */
function copyLink() {
	document.querySelectorAll( '[data-copy-link]' ).forEach( ( button ) => {
		const label = button.querySelector( '.screen-reader-text' );
		const original = label ? label.textContent : '';

		button.addEventListener( 'click', () => {
			if ( ! navigator.clipboard ) {
				return;
			}

			navigator.clipboard.writeText( button.dataset.copyLink ).then( () => {
				if ( label ) {
					label.textContent = button.dataset.copiedLabel;
					window.setTimeout( () => ( label.textContent = original ), 2000 );
				}
				button.classList.add( 'is-copied' );
				window.setTimeout( () => button.classList.remove( 'is-copied' ), 2000 );
			} );
		} );
	} );
}

export function Article() {
	contents();
	contentsMore();
	copyLink();
}
