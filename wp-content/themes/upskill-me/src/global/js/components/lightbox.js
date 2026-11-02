/**
 * Image lightbox (course page session previews).
 *
 * A [data-lightbox] wrapper holds the trigger buttons ([data-lightbox-item],
 * each carrying data-src, data-srcset and data-caption) and one native
 * <dialog data-lightbox-dialog>. A trigger opens the dialog on its image;
 * previous / next (buttons, arrow keys, swipe) step through the set. Esc, the
 * close button or a click on the backdrop closes it, and focus returns to the
 * trigger (native <dialog> behaviour).
 */
export function Lightbox( root = document ) {
	root.querySelectorAll( '[data-lightbox]' ).forEach( ( gallery ) => {
		const dialog = gallery.querySelector( '[data-lightbox-dialog]' );
		const items = [ ...gallery.querySelectorAll( '[data-lightbox-item]' ) ];

		if ( ! dialog || ! items.length || typeof dialog.showModal !== 'function' ) {
			return;
		}

		const image = dialog.querySelector( '[data-lightbox-image]' );
		const caption = dialog.querySelector( '[data-lightbox-caption]' );
		const counter = dialog.querySelector( '[data-lightbox-counter]' );
		let current = 0;
		let touchX = null;

		const show = ( index ) => {
			current = ( index + items.length ) % items.length;
			const item = items[ current ];

			image.src = item.dataset.src;
			image.srcset = item.dataset.srcset || '';
			image.alt = item.dataset.caption || '';

			if ( caption ) {
				caption.textContent = item.dataset.caption || '';
			}

			if ( counter ) {
				counter.textContent = `${ current + 1 } / ${ items.length }`;
			}
		};

		items.forEach( ( item, index ) => {
			item.addEventListener( 'click', () => {
				show( index );
				dialog.showModal();
			} );
		} );

		dialog.querySelector( '[data-lightbox-prev]' )?.addEventListener( 'click', () => show( current - 1 ) );
		dialog.querySelector( '[data-lightbox-next]' )?.addEventListener( 'click', () => show( current + 1 ) );
		dialog.querySelector( '[data-lightbox-close]' )?.addEventListener( 'click', () => dialog.close() );

		// A click on the backdrop lands on the dialog element itself.
		dialog.addEventListener( 'click', ( event ) => {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );

		dialog.addEventListener( 'keydown', ( event ) => {
			if ( 'ArrowLeft' === event.key ) {
				show( current - 1 );
			} else if ( 'ArrowRight' === event.key ) {
				show( current + 1 );
			}
		} );

		dialog.addEventListener( 'touchstart', ( event ) => {
			touchX = event.touches[ 0 ].clientX;
		}, { passive: true } );

		dialog.addEventListener( 'touchend', ( event ) => {
			if ( null === touchX ) {
				return;
			}

			const distance = event.changedTouches[ 0 ].clientX - touchX;
			touchX = null;

			if ( Math.abs( distance ) > 40 ) {
				show( current + ( distance < 0 ? 1 : -1 ) );
			}
		} );
	} );
}
