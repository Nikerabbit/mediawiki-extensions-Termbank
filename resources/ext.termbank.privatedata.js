( function () {
	'use strict';

	const loader = 'Special:PrivateData/' + mw.config.get( 'wgPageName' );
	// eslint-disable-next-line no-jquery/no-global-selector
	const $target = $( '.ttp-privatedata-placeholder' );

	if ( $target.length ) {
		$.get( mw.util.getUrl( loader ), function ( data ) {
			$target.html( data );
		} );
	}
}() );
