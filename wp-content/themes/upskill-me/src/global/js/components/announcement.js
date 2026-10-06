/**
 * Dismissable announcement bar.
 *
 * Dismissal lasts for the browser session, so the free sessions offer comes
 * back on a later visit. Storage can throw (private mode, blocked cookies), in
 * which case the bar simply closes for this page view.
 */

const KEY = 'upskill-announcement-dismissed';

export function Announcement() {
	const bar = document.querySelector( '[data-announcement]' );

	if ( ! bar ) {
		return;
	}

	try {
		if ( window.sessionStorage.getItem( KEY ) ) {
			bar.hidden = true;
			return;
		}
	} catch ( error ) {}

	const close = bar.querySelector( '[data-announcement-close]' );

	if ( ! close ) {
		return;
	}

	close.addEventListener( 'click', () => {
		bar.hidden = true;

		try {
			window.sessionStorage.setItem( KEY, '1' );
		} catch ( error ) {}

		document.dispatchEvent( new CustomEvent( 'upskill:layout' ) );
	} );
}
