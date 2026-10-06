/**
 * Feature List With Media — light the feature crossing the middle of the
 * screen, so the list reads as a sequence while the photograph stays pinned.
 *
 * The first feature is lit in the markup; without IntersectionObserver it
 * simply stays that way.
 */
document.querySelectorAll( '.block-feature-media [data-features]' ).forEach( ( list ) => {
	const features = list.querySelectorAll( '[data-feature]' );

	if ( features.length < 2 || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					features.forEach( ( feature ) => feature.classList.toggle( 'is-active', feature === entry.target ) );
				}
			} );
		},
		// A thin band across the centre of the viewport.
		{ rootMargin: '-45% 0px -50% 0px' }
	);

	features.forEach( ( feature ) => observer.observe( feature ) );
} );
