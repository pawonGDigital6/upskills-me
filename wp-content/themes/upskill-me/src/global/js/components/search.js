/**
 * Header search dialog.
 *
 * A native <dialog>: showModal() traps focus, Escape closes it and focus
 * returns to the opener, all without extra code.
 */
export function Search() {
	const dialog = document.querySelector( '[data-search-dialog]' );

	if ( ! dialog || 'function' !== typeof dialog.showModal ) {
		return;
	}

	document.querySelectorAll( '[data-search-open]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			dialog.showModal();
			dialog.querySelector( 'input[type="search"]' )?.focus();
		} );
	} );

	// A click on the backdrop lands on the dialog element itself.
	dialog.addEventListener( 'click', ( event ) => {
		if ( event.target === dialog ) {
			dialog.close();
		}
	} );
}
