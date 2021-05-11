'use strict';
document.querySelectorAll( '.ttp-responsive-table' ).forEach( function ( table ) {
	var headings = table.querySelectorAll( 'th' );
	table.querySelectorAll( 'tr' ).forEach( function ( row ) {
		row.querySelectorAll( 'td' ).forEach( function ( cell, index ) {
			cell.dataset.cellLabel = headings[ index ].textContent + ': ';
		} );
	} );
} );
