/**
 * Site header: sticky state, desktop mega panels and the mobile drawer.
 *
 * Everything enhances markup that already works: triggers are real buttons
 * with aria-expanded/aria-controls, and panels are plain `hidden` elements.
 */

const DESKTOP = window.matchMedia( '(min-width: 1200px)' );
const HOVER = window.matchMedia( '(hover: hover) and (pointer: fine)' );
const TRANSITION = 220;

/**
 * Show or hide a `hidden` panel with a CSS transition on `.is-open`.
 *
 * `hidden` cannot be transitioned, so it is removed first and the class added
 * on the next frame; on close the class goes first and `hidden` returns once
 * the fade has finished (unless the panel was reopened meanwhile).
 *
 * @param {HTMLElement} panel Panel element.
 * @param {boolean}     open  Target state.
 */
function setPanel( panel, open ) {
	window.clearTimeout( panel.upskillTimer );

	if ( open ) {
		panel.hidden = false;
		window.requestAnimationFrame( () => panel.classList.add( 'is-open' ) );
		return;
	}

	panel.classList.remove( 'is-open' );
	panel.upskillTimer = window.setTimeout( () => {
		if ( ! panel.classList.contains( 'is-open' ) ) {
			panel.hidden = true;
		}
	}, TRANSITION );
}

/**
 * Toggle the `.is-stuck` look once the announcement bar has scrolled away.
 *
 * @param {HTMLElement} header Site header.
 */
function stickyState( header ) {
	let ticking = false;

	const update = () => {
		ticking = false;
		header.classList.toggle( 'is-stuck', window.scrollY > 0 && header.getBoundingClientRect().top <= 0 );
	};

	window.addEventListener(
		'scroll',
		() => {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		},
		{ passive: true }
	);

	update();
}

/**
 * Desktop mega panels.
 *
 * @param {HTMLElement} nav The primary nav list.
 */
function megaMenu( nav ) {
	const items = Array.from( nav.querySelectorAll( '[data-mega-trigger]' ) ).map( ( trigger ) => ( {
		trigger,
		panel: document.getElementById( trigger.getAttribute( 'aria-controls' ) ),
		item: trigger.closest( 'li' ),
	} ) );

	if ( ! items.length ) {
		return;
	}

	let hoverTimer = 0;

	const close = ( entry, restoreFocus = false ) => {
		if ( 'true' !== entry.trigger.getAttribute( 'aria-expanded' ) ) {
			return;
		}

		entry.trigger.setAttribute( 'aria-expanded', 'false' );
		setPanel( entry.panel, false );

		if ( restoreFocus ) {
			entry.trigger.focus();
		}
	};

	const open = ( entry ) => {
		items.forEach( ( other ) => other !== entry && close( other ) );
		entry.trigger.setAttribute( 'aria-expanded', 'true' );
		setPanel( entry.panel, true );
	};

	items.forEach( ( entry ) => {
		if ( ! entry.panel ) {
			return;
		}

		entry.trigger.addEventListener( 'click', () => {
			if ( 'true' === entry.trigger.getAttribute( 'aria-expanded' ) ) {
				close( entry );
			} else {
				open( entry );
			}
		} );

		// Hover intent for mouse users: a short delay in each direction stops
		// panels flashing open as the pointer crosses the bar.
		entry.item.addEventListener( 'mouseenter', () => {
			if ( ! HOVER.matches ) {
				return;
			}

			window.clearTimeout( hoverTimer );
			hoverTimer = window.setTimeout( () => open( entry ), 80 );
		} );

		entry.item.addEventListener( 'mouseleave', () => {
			if ( ! HOVER.matches ) {
				return;
			}

			window.clearTimeout( hoverTimer );
			hoverTimer = window.setTimeout( () => close( entry ), 160 );
		} );

		// Tabbing out of the item closes its panel.
		entry.item.addEventListener( 'focusout', ( event ) => {
			if ( event.relatedTarget && ! entry.item.contains( event.relatedTarget ) ) {
				close( entry );
			}
		} );
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' !== event.key ) {
			return;
		}

		const active = items.find( ( entry ) => entry.item.contains( document.activeElement ) );

		items.forEach( ( entry ) => close( entry, entry === active ) );
	} );

	document.addEventListener( 'click', ( event ) => {
		if ( ! nav.contains( event.target ) ) {
			items.forEach( ( entry ) => close( entry ) );
		}
	} );
}

/**
 * The mobile drawer.
 *
 * @param {HTMLElement} header Site header.
 */
function drawer( header ) {
	const toggle = header.querySelector( '[data-drawer-toggle]' );
	const sheet = document.getElementById( 'site-drawer' );

	if ( ! toggle || ! sheet ) {
		return;
	}

	const label = toggle.querySelector( '[data-drawer-label]' );

	const setOpen = ( open ) => {
		if ( open ) {
			// The sheet starts under whatever is visible of the header — the
			// announcement bar may or may not still be on screen.
			sheet.style.setProperty( '--upskill-drawer-top', `${ Math.max( 0, header.getBoundingClientRect().bottom ) }px` );
		}

		toggle.setAttribute( 'aria-expanded', String( open ) );
		document.documentElement.classList.toggle( 'has-drawer', open );
		setPanel( sheet, open );

		if ( label ) {
			label.textContent = open ? label.dataset.closeLabel : label.dataset.openLabel;
		}
	};

	toggle.addEventListener( 'click', () => setOpen( 'true' !== toggle.getAttribute( 'aria-expanded' ) ) );

	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' === event.key && 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );

	DESKTOP.addEventListener( 'change', ( event ) => {
		if ( event.matches ) {
			setOpen( false );
		}
	} );
}

export function Navigation() {
	const header = document.querySelector( '[data-site-header]' );

	if ( ! header ) {
		return;
	}

	stickyState( header );
	drawer( header );

	const nav = header.querySelector( '[data-mega-nav]' );

	if ( nav ) {
		megaMenu( nav );
	}
}
