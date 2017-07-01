/**
 * Timeline Express Single Column Scripts
 *
 * @author Code Parrots <https://www.codeparrots.com>
 * @since 1.0.0
 */
( function( $ ) {

	var singleColumn = {

		init: function() {

			if ( ! $( '.timeline-express' ).find( '.single-column' ).length ) {

				return;

			}

			$( '.timeline-express' ).find( '.single-column' ).parents( 'section.timeline-express' ).addClass( 'single-column' );

		}

	};

	$( document ).ready( singleColumn.init );

} )( jQuery );
