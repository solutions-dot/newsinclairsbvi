/**
 * wp-admin only: repeater row cloning (FAQs, nutshell rows) and the
 * media-library image picker (Home / About page meta boxes). Vanilla JS,
 * no build step, no jQuery dependency.
 */
( function () {
	'use strict';

	function renumberRows( repeater ) {
		var label = repeater.getAttribute( 'data-row-label' ) || 'Row';
		var rows = repeater.querySelectorAll( ':scope > .sbvi-repeater-rows > .sbvi-repeater-row' );
		rows.forEach( function ( row, index ) {
			var title = row.querySelector( '.sbvi-repeater-row-title' );
			if ( title ) {
				title.textContent = label + ' ' + ( index + 1 );
			}
		} );
	}

	function initRepeaters() {
		document.querySelectorAll( '.sbvi-repeater' ).forEach( function ( repeater ) {
			var rowsWrap = repeater.querySelector( '.sbvi-repeater-rows' );
			var template = repeater.querySelector( 'template.sbvi-repeater-template' );
			var addBtn = repeater.querySelector( '.sbvi-add-row' );

			if ( addBtn && template && rowsWrap ) {
				addBtn.addEventListener( 'click', function () {
					var index = 'new' + Date.now();
					var html = template.innerHTML.split( '__INDEX__' ).join( index );
					var wrapper = document.createElement( 'div' );
					wrapper.innerHTML = html.trim();
					var newRow = wrapper.firstElementChild;
					rowsWrap.appendChild( newRow );
					renumberRows( repeater );
					var firstField = newRow.querySelector( 'input, textarea, select' );
					if ( firstField ) {
						firstField.focus();
					}
				} );
			}

			repeater.addEventListener( 'click', function ( e ) {
				var row = e.target.closest( '.sbvi-repeater-row' );
				if ( ! row ) {
					return;
				}

				if ( e.target.closest( '.sbvi-remove-row' ) ) {
					e.preventDefault();
					row.remove();
					renumberRows( repeater );
				} else if ( e.target.closest( '.sbvi-move-up' ) ) {
					e.preventDefault();
					var prev = row.previousElementSibling;
					if ( prev ) {
						row.parentNode.insertBefore( row, prev );
						renumberRows( repeater );
					}
				} else if ( e.target.closest( '.sbvi-move-down' ) ) {
					e.preventDefault();
					var next = row.nextElementSibling;
					if ( next ) {
						row.parentNode.insertBefore( next, row );
						renumberRows( repeater );
					}
				}
			} );
		} );
	}

	function initImagePickers() {
		if ( typeof wp === 'undefined' || ! wp.media ) {
			return;
		}

		document.querySelectorAll( '.sbvi-image-picker' ).forEach( function ( picker ) {
			var selectBtn = picker.querySelector( '.sbvi-image-picker-select' );
			var removeBtn = picker.querySelector( '.sbvi-image-picker-remove' );
			var input = picker.querySelector( '.sbvi-image-picker-input' );
			var preview = picker.querySelector( '.sbvi-image-picker-preview' );
			var frame = null;

			if ( ! selectBtn || ! input || ! preview ) {
				return;
			}

			selectBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title: selectBtn.getAttribute( 'data-title' ) || 'Select Image',
					multiple: false,
					library: { type: 'image' },
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					input.value = attachment.id;
					var url = ( attachment.sizes && attachment.sizes.medium ) ? attachment.sizes.medium.url : attachment.url;
					preview.innerHTML = '<img src="' + url + '" alt="">';
					if ( removeBtn ) {
						removeBtn.style.display = '';
					}
				} );

				frame.open();
			} );

			if ( removeBtn ) {
				removeBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					input.value = '';
					preview.innerHTML = '';
					removeBtn.style.display = 'none';
				} );
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initRepeaters();
			initImagePickers();
		} );
	} else {
		initRepeaters();
		initImagePickers();
	}
} )();
