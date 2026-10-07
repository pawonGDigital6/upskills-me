/**
 * Blog article: the "In this article" reading position and the copy-link
 * share button.
 */

/**
 * Mark the contents link of the section being read.
 */
function contents() {
	const list = document.querySelector( '[data-article-contents]' );

	if ( ! list || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	const links = Array.from( list.querySelectorAll( 'a[href^="#"]' ) );
	const headings = links
		.map( ( link ) => document.getElementById( decodeURIComponent( link.hash.slice( 1 ) ) ) )
		.filter( Boolean );

	const activate = ( id ) => {
		links.forEach( ( link ) => link.classList.toggle( 'is-active', link.hash === '#' + id ) );
	};

	// A heading becomes current once it passes the top third of the screen.
	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					activate( entry.target.id );
				}
			} );
		},
		{ rootMargin: '0px 0px -66% 0px' }
	);

	headings.forEach( ( heading ) => observer.observe( heading ) );
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
