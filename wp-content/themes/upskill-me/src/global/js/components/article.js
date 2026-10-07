/**
 * Blog article: the "In this article" reading position and the copy-link
 * share button.
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
	copyLink();
}
