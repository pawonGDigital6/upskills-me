/**
 * Training library (template-library.php).
 *
 * The filters are a plain GET form that works without JavaScript. Here:
 * - on desktop a ticked filter applies at once (the form submits);
 * - on phones the filters sit in a bottom sheet (<dialog>) opened by the
 *   filter button; choices apply with its "Show results" button;
 * - "Sort by" applies at once everywhere.
 */
export function Library( root = document ) {
	const form = root.querySelector( '[data-library]' );

	if ( ! form ) {
		return;
	}

	const sheet = form.querySelector( '[data-library-filters]' );
	const isSheet = () => sheet?.open && sheet.matches( ':modal' );

	form.querySelector( '[data-library-open]' )?.addEventListener( 'click', () => sheet?.showModal() );
	form.querySelector( '[data-library-close]' )?.addEventListener( 'click', () => sheet?.close() );

	// A tap on the backdrop (outside the sheet's box) closes it.
	sheet?.addEventListener( 'click', ( event ) => {
		if ( event.target === sheet && isSheet() ) {
			const box = sheet.getBoundingClientRect();
			const inside = event.clientX >= box.left && event.clientX <= box.right && event.clientY >= box.top && event.clientY <= box.bottom;

			if ( ! inside ) {
				sheet.close();
			}
		}
	} );

	form.addEventListener( 'change', ( event ) => {
		const always = event.target.matches( '[data-library-submit]' );

		if ( always || ! isSheet() ) {
			form.requestSubmit();
		}
	} );
}
