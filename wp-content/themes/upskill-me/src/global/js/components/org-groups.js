/**
 * Organisation Groups page (template-org-groups.php).
 *
 * - Forms with `data-org-confirm` (remove a person, delete a group) ask first,
 *   so nobody is removed by accident.
 * - "Add people": the search box filters the list by name or email.
 */
export function OrgGroups( root = document ) {
	root.querySelectorAll( 'form[data-org-confirm]' ).forEach( ( form ) => {
		form.addEventListener( 'submit', ( event ) => {
			// eslint-disable-next-line no-alert
			if ( ! window.confirm( form.dataset.orgConfirm ) ) {
				event.preventDefault();
			}
		} );
	} );

	root.querySelectorAll( '[data-org-people]' ).forEach( ( form ) => {
		const search = form.querySelector( '[data-org-people-search]' );

		search?.addEventListener( 'input', () => {
			const term = search.value.trim().toLowerCase();

			form.querySelectorAll( '[data-org-person]' ).forEach( ( person ) => {
				person.hidden = '' !== term && ! person.dataset.orgPerson.includes( term );
			} );
		} );

		// Enter in the search box filters instead of submitting the form.
		search?.addEventListener( 'keydown', ( event ) => {
			if ( 'Enter' === event.key ) {
				event.preventDefault();
			}
		} );
	} );
}
