/**
 * Course hero gallery (learndash/ld30/course.php), the WooCommerce product
 * gallery pattern on Swiper.
 *
 * The main slider holds the preview video and the session preview images; the
 * thumbnail strip under it only changes the main slide (Swiper Thumbs).
 * Clicking an image slide opens it full size in a popup (a native <dialog>,
 * stepping through the image slides); the video slide plays in place with the
 * shared transport bar (js/components/video-player.js). Leaving a slide pauses
 * its video.
 *
 * Loaded on demand from main.js, only where [data-course-gallery] exists.
 */
import Swiper from 'swiper';
import { Thumbs, A11y } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/thumbs';

function popup( gallery ) {
	const dialog = gallery.querySelector( '[data-gallery-popup]' );
	const zooms = [ ...gallery.querySelectorAll( '[data-gallery-zoom]' ) ];

	if ( ! dialog || ! zooms.length || typeof dialog.showModal !== 'function' ) {
		return;
	}

	const image = dialog.querySelector( '[data-gallery-popup-image]' );
	const caption = dialog.querySelector( '[data-gallery-popup-caption]' );
	let current = 0;

	const show = ( index ) => {
		current = ( index + zooms.length ) % zooms.length;
		const zoom = zooms[ current ];

		image.src = zoom.dataset.src;
		image.srcset = zoom.dataset.srcset || '';
		image.alt = zoom.dataset.caption || '';
		caption.textContent = zoom.dataset.caption || '';
	};

	zooms.forEach( ( zoom, index ) => {
		zoom.addEventListener( 'click', () => {
			show( index );
			dialog.showModal();
		} );
	} );

	dialog.querySelector( '[data-gallery-popup-prev]' )?.toggleAttribute( 'hidden', zooms.length < 2 );
	dialog.querySelector( '[data-gallery-popup-next]' )?.toggleAttribute( 'hidden', zooms.length < 2 );
	dialog.querySelector( '[data-gallery-popup-prev]' )?.addEventListener( 'click', () => show( current - 1 ) );
	dialog.querySelector( '[data-gallery-popup-next]' )?.addEventListener( 'click', () => show( current + 1 ) );
	dialog.querySelector( '[data-gallery-popup-close]' )?.addEventListener( 'click', () => dialog.close() );

	// A click on the backdrop lands on the dialog element itself.
	dialog.addEventListener( 'click', ( event ) => event.target === dialog && dialog.close() );
	dialog.addEventListener( 'keydown', ( event ) => {
		if ( zooms.length > 1 && 'ArrowLeft' === event.key ) {
			show( current - 1 );
		} else if ( zooms.length > 1 && 'ArrowRight' === event.key ) {
			show( current + 1 );
		}
	} );
}

export function CourseGallery( root = document ) {
	root.querySelectorAll( '[data-course-gallery]' ).forEach( ( gallery ) => {
		const main = gallery.querySelector( '[data-gallery-main]' );
		const strip = gallery.querySelector( '[data-gallery-thumbs]' );

		if ( ! main ) {
			return;
		}

		const thumbs = strip
			? new Swiper( strip, {
				modules: [ A11y ],
				slidesPerView: 4,
				spaceBetween: 8,
				watchSlidesProgress: true,
				breakpoints: { 1025: { spaceBetween: 12 } },
			} )
			: null;

		new Swiper( main, {
			modules: [ Thumbs, A11y ],
			spaceBetween: 12,
			// The video's transport bar takes the pointer, not a swipe.
			noSwipingSelector: '[data-video-bar], input',
			thumbs: thumbs ? { swiper: thumbs } : undefined,
			on: {
				slideChange: () => main.querySelectorAll( 'video' ).forEach( ( video ) => video.pause() ),
			},
		} );

		popup( gallery );
	} );
}
