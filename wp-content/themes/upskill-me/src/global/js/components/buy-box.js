/**
 * Course buy box (learndash/ld30/course.php).
 *
 * A plain form posting to WooCommerce's add-to-cart; the − / + buttons step
 * the seats input beside them.
 *
 * Seat list (several levels, `data-buy-list`): each level has its own seats
 * (`upskill_seats[<level>]`, added as one cart line per level by
 * inc/lms-commerce.php). Here: a level with seats is highlighted, the total
 * and seat count follow the seats, and Add to Cart waits for at least one.
 */
export function BuyBox( root = document ) {
	root.querySelectorAll( '[data-buy-box]' ).forEach( ( form ) => {
		form.querySelectorAll( '[data-buy-step]' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const seats = button.parentElement.querySelector( '[data-buy-seats]' );

				if ( ! seats ) {
					return;
				}

				const min = Number( seats.min ) || 0;
				const max = Number( seats.max ) || 999;

				seats.value = String( Math.min( max, Math.max( min, ( Number( seats.value ) || 0 ) + Number( button.dataset.buyStep ) ) ) );
				seats.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			} );
		} );

		if ( form.hasAttribute( 'data-buy-list' ) ) {
			seatList( form );
		}
	} );
}

function seatList( form ) {
	const total = form.querySelector( '[data-buy-total]' );
	const count = form.querySelector( '[data-buy-count]' );
	const submit = form.querySelector( '[data-buy-submit]' );
	const decimals = Number( form.dataset.decimals ?? 2 );
	let money;

	try {
		money = new Intl.NumberFormat( document.documentElement.lang || 'en-AU', {
			style: 'currency',
			currency: form.dataset.currency || 'AUD',
			currencyDisplay: 'narrowSymbol',
			minimumFractionDigits: decimals,
			maximumFractionDigits: decimals,
		} );
	} catch {
		money = { format: ( value ) => value.toFixed( decimals ) };
	}

	const update = () => {
		let seats = 0;
		let sum = 0;

		form.querySelectorAll( '[data-buy-level]' ).forEach( ( level ) => {
			const input = level.querySelector( '[data-buy-seats]' );
			const value = Math.max( 0, Math.floor( Number( input.value ) || 0 ) );

			level.classList.toggle( 'is-active', value > 0 );
			seats += value;
			sum += value * ( Number( level.dataset.price ) || 0 );
		} );

		if ( total ) {
			total.textContent = money.format( sum );
		}

		if ( count && total ) {
			const label = 1 === seats ? total.dataset.labelOne : total.dataset.labelMany;

			count.textContent = ( label || '%s' ).replace( '%s', seats.toLocaleString() );
		}

		if ( submit ) {
			submit.disabled = 0 === seats;
		}
	};

	form.addEventListener( 'input', ( event ) => {
		if ( event.target.matches( '[data-buy-seats]' ) ) {
			update();
		}
	} );

	update();
}
