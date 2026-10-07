/**
 * FAQs (grouped layout) — the phone search.
 *
 * Filters questions as you type: matching questions stay, empty topics hide,
 * and topics with matches are opened. Clearing the field restores everything.
 */

document.querySelectorAll( '[data-faq-groups]' ).forEach( ( block ) => {
	const input = block.querySelector( '[data-faq-search]' );
	const empty = block.querySelector( '[data-faq-empty]' );

	if ( ! input ) {
		return;
	}

	const groups = Array.from( block.querySelectorAll( '[data-faq-group]' ) );

	input.addEventListener( 'input', () => {
		const term = input.value.trim().toLowerCase();
		let shown = 0;

		groups.forEach( ( group ) => {
			let matches = 0;

			group.querySelectorAll( '[data-faq-item]' ).forEach( ( item ) => {
				const hit = ! term || item.textContent.toLowerCase().includes( term );
				item.hidden = ! hit;
				matches += hit ? 1 : 0;
			} );

			group.hidden = ! matches;
			shown += matches;

			// A topic with matches is opened so they can be seen.
			if ( term && matches ) {
				const toggle = group.querySelector( '.faq-group__toggle' );
				const list = document.getElementById( toggle.getAttribute( 'aria-controls' ) );
				toggle.setAttribute( 'aria-expanded', 'true' );
				list?.classList.add( 'is-open' );
			}
		} );

		if ( empty ) {
			empty.hidden = shown > 0;
		}
	} );
} );
