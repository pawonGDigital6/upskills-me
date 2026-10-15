/**
 * Course buy box (learndash/ld30/course.php).
 *
 * A plain form posting to WooCommerce's add-to-cart. Here: choosing a level
 * updates the shown price and the variation's attribute fields WooCommerce
 * checks, and the − / + buttons step the seat count.
 */
export function BuyBox( root = document ) {
	root.querySelectorAll( '[data-buy-box]' ).forEach( ( form ) => {
		const price = form.querySelector( '[data-buy-price]' );
		const unit = form.querySelector( '[data-buy-unit]' );
		const seats = form.querySelector( 'input[name="quantity"]' );
		const unitText = unit?.textContent.trim().replace( /·.*$/, '' ).trim();

		form.addEventListener( 'change', ( event ) => {
			const level = event.target.closest( 'input[name="variation_id"]' );

			if ( ! level ) {
				return;
			}

			if ( price ) {
				price.textContent = level.dataset.price;
			}

			if ( unit && unitText ) {
				unit.textContent = `${ unitText } · ${ level.dataset.label }`;
			}

			form.querySelectorAll( '[data-buy-attribute]' ).forEach( ( field ) => field.remove() );

			Object.entries( JSON.parse( level.dataset.attributes || '{}' ) ).forEach( ( [ name, value ] ) => {
				const field = document.createElement( 'input' );

				field.type = 'hidden';
				field.name = name;
				field.value = value;
				field.dataset.buyAttribute = '';
				form.append( field );
			} );
		} );

		form.querySelectorAll( '[data-buy-step]' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const next = Math.min( Number( seats.max ) || 999, Math.max( Number( seats.min ) || 1, ( Number( seats.value ) || 1 ) + Number( button.dataset.buyStep ) ) );

				seats.value = String( next );
			} );
		} );
	} );
}
