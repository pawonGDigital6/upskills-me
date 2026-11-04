/**
 * Session page progress strip (learndash/ld30/lesson.php, `[data-session-tabs]`).
 *
 * On phones the strip scrolls sideways (scss/plugins/learndash/_session.scss).
 * Bring the current session into view so a learner on session 7 of 8 sees it
 * without swiping. Only the strip scrolls (scrollLeft), never the page; does
 * nothing when the strip fits.
 */
export function SessionTabs( root = document ) {
	root.querySelectorAll( '[data-session-tabs]' ).forEach( ( strip ) => {
		const current = strip.querySelector( '[aria-current="step"]' )?.closest( 'li' );

		if ( ! current || strip.scrollWidth <= strip.clientWidth ) {
			return;
		}

		// Keep the previous session's edge visible as a hint that the strip scrolls.
		const peek = current.previousElementSibling ? 24 : 0;
		strip.scrollLeft = Math.max( 0, current.offsetLeft - strip.offsetLeft - peek );
	} );
}
