/**
 * Show / hide password (the eye button in the sign-in form).
 *
 * A `[data-password-toggle]` button controls the input named by
 * aria-controls and holds two icons: eye-off (hidden, the default as Figma
 * draws it) and eye (shown).
 */
export function PasswordToggle( root = document ) {
	root.querySelectorAll( '[data-password-toggle]' ).forEach( ( button ) => {
		const input = document.getElementById( button.getAttribute( 'aria-controls' ) );
		const label = button.querySelector( '.screen-reader-text' );
		const [ hiddenIcon, shownIcon ] = button.querySelectorAll( 'svg' );

		if ( ! input ) {
			return;
		}

		const showLabel = label?.textContent;

		button.addEventListener( 'click', () => {
			const show = 'password' === input.type;

			input.type = show ? 'text' : 'password';
			button.setAttribute( 'aria-pressed', String( show ) );
			hiddenIcon?.classList.toggle( 'hidden', show );
			shownIcon?.classList.toggle( 'hidden', ! show );

			if ( label ) {
				label.textContent = show ? button.dataset.hideLabel || 'Hide password' : showLabel;
			}
		} );
	} );
}
