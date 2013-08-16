jQuery( function( $, undefined ) {
	var loader = 'Special:PrivateData/' + mw.config.get( 'wgPageName' );
	var $target = $( ".ttp-privatedata-placeholder" );
	if ( $target.length ) {
		$.get( mw.util.getUrl( loader ), function( data ) {
			$target.html( data );
		} );
	}
} );
