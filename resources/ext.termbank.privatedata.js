( function () {
	'use strict';
	var loader, $target;

	loader = 'Special:PrivateData/' + mw.config.get( 'wgPageName' );
	// eslint-disable-next-line no-jquery/no-global-selector
	$target = $( '.ttp-privatedata-placeholder' );

	if ( $target.length ) {
		$.get( mw.util.getUrl( loader ), function ( data ) {
			$target.html( data );
		} );
	}
}() );
