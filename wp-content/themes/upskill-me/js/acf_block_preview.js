/**
 * Show each ACF block's preview.png in the block inserter.
 *
 * The inserter renders a "no preview available" panel for blocks that use a
 * server-side render template. Each block ships a preview.png next to its
 * block.json (copied into build/blocks by the build), and its file name is
 * declared as the block's `previewImage` attribute, so the image can be
 * resolved from whichever block is being hovered.
 */
( function () {
	'use strict';

	var PREFIX = 'editor-block-list-item-acf-block-';

	document.addEventListener( 'mouseover', function ( event ) {
		var panel = document.querySelector( '.block-editor-inserter__preview-content-missing' );

		if ( ! panel || ! event.target.closest ) {
			return;
		}

		var item = event.target.closest( '.block-editor-block-types-list__item' );

		if ( ! item ) {
			return;
		}

		var match = Array.prototype.find.call( item.classList, function ( name ) {
			return 0 === name.indexOf( PREFIX );
		} );

		var reset = function () {
			panel.style.background = '';
			panel.style.backgroundSize = '';
			panel.style.fontSize = '';
		};

		if ( ! match || ! window.wp || ! window.wp.data ) {
			reset();
			return;
		}

		var slug = match.slice( PREFIX.length );
		var type = window.wp.data.select( 'core/blocks' ).getBlockType( 'acf-block/' + slug );
		var file = type && type.attributes && type.attributes.previewImage
			? type.attributes.previewImage.default
			: null;

		if ( ! file ) {
			reset();
			return;
		}

		panel.style.background =
			'url(' + upskillBlockPreview.templateUrl + '/build/blocks/' + slug + '/' + file + ') no-repeat center';
		panel.style.backgroundSize = 'contain';
		panel.style.fontSize = '0px';
	} );
}() );
