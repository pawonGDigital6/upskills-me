/**
 * Disclosure buttons (FAQ answers, drawer sub-menus).
 *
 * A `[data-disclosure]` button controls the element named by aria-controls,
 * which carries `.collapse`. The panel is open in the markup when the button
 * starts with aria-expanded="true" (the first FAQ). A `data-disclosure-group`
 * ancestor makes its disclosures single-open, like the Figma accordion.
 */
export function Disclosure( root = document ) {
	root.querySelectorAll( '[data-disclosure]' ).forEach( ( button ) => {
		const panel = document.getElementById( button.getAttribute( 'aria-controls' ) );

		if ( ! panel ) {
			return;
		}

		button.addEventListener( 'click', () => {
			const open = 'true' !== button.getAttribute( 'aria-expanded' );
			const group = button.closest( '[data-disclosure-group]' );

			if ( open && group ) {
				group.querySelectorAll( '[data-disclosure][aria-expanded="true"]' ).forEach( ( other ) => {
					if ( other !== button ) {
						other.setAttribute( 'aria-expanded', 'false' );
						document.getElementById( other.getAttribute( 'aria-controls' ) )?.classList.remove( 'is-open' );
					}
				} );
			}

			button.setAttribute( 'aria-expanded', String( open ) );
			panel.classList.toggle( 'is-open', open );
		} );
	} );
}
