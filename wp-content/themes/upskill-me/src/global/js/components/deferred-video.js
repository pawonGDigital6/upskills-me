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

	return (
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ||
		Boolean( connection && connection.saveData )
	);
}

/**
 * Attach the source and start one video.
 *
 * @param {HTMLVideoElement} video The video to start.
 */
function start( video ) {
	video.src = video.dataset.src;
	video.removeAttribute( 'data-src' );
	video.addEventListener( 'playing', () => video.classList.add( 'is-playing' ), { once: true } );

	// Rejects when the browser declines to autoplay, which is not an error.
	video.play()?.catch( () => {} );
}

export function DeferredVideo() {
	const videos = document.querySelectorAll( 'video[data-deferred-video][data-src]' );

	if ( ! videos.length || skipVideo() ) {
		return;
	}

	if ( ! ( 'IntersectionObserver' in window ) ) {
		videos.forEach( start );
		return;
	}

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					observer.unobserve( entry.target );
					start( entry.target );
				}
			} );
		},
		{ rootMargin: '300px 0px' }
	);

	videos.forEach( ( video ) => observer.observe( video ) );
}
