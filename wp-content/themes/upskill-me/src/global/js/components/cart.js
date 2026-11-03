/**
 * Cart (WooCommerce classic cart, inc/lms-store.php).
 *
 * The − / + buttons step a line's seats (WooCommerce's own quantity input),
 * and a changed seat count or "Cohort" select submits WooCommerce's own
 * "Update cart" button, so WooCommerce updates the cart (by AJAX, replacing
 * the cart and totals) exactly as it would by hand. Listeners sit on the
 * document because WooCommerce replaces the cart markup after each update.
 * The "Update cart" button itself is hidden once this runs (has-cart-js).
 */
let timer = 0;

function submitCart( delay ) {
	window.clearTimeout( timer );
	timer = window.setTimeout( () => {
		const button = document.querySelector( '.woocommerce-cart-form [name="update_cart"]' );

		if ( button ) {
			button.disabled = false;
			button.removeAttribute( 'aria-disabled' );
			button.click();
		}
	}, delay );
}

export function Cart() {
	if ( ! document.querySelector( '.woocommerce-cart-form' ) ) {
		return;
	}

	document.documentElement.classList.add( 'has-cart-js' );

	document.addEventListener( 'click', ( event ) => {
		const step = event.target.closest( '[data-seat-step]' );
		const input = step?.closest( '.cart-seats' )?.querySelector( 'input.qty' );

		if ( ! input ) {
			return;
		}

		const min = Number( input.min ) || 1;
		const max = Number( input.max ) || Infinity;
		const next = Math.min( max, Math.max( min, ( Number( input.value ) || min ) + Number( step.dataset.seatStep ) * ( Number( input.step ) || 1 ) ) );

		if ( next !== Number( input.value ) ) {
			input.value = String( next );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
	} );

	document.addEventListener( 'change', ( event ) => {
		if ( event.target.matches( '.woocommerce-cart-form input.qty' ) ) {
			// A short pause, so several clicks on − / + make one update.
			submitCart( 600 );
		} else if ( event.target.matches( '[data-cart-level]' ) ) {
			submitCart( 0 );
		}
	} );
}
