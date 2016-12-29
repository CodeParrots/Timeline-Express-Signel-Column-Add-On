/**
 * Timeline Express Single Column Scripts
 * @since 1.0
 */
jQuery( document ).ready( function() {
	/**
	 * If a .single-column container is found on the page
	 * add the appropriate class to the parent container (for styling purposes)
	 */
	if ( jQuery( '.timeline-express' ).find( '.single-column' ).length > 0 ) {
		jQuery( '.timeline-express' ).find( '.single-column' ).parents( 'section.timeline-express' ).addClass( 'single-column' );
	}
});
