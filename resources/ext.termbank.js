'use strict';
document.querySelectorAll( '.ttp-responsive-table' ).forEach( ( table ) => {
	const headings = table.querySelectorAll( 'th' );
	table.querySelectorAll( 'tr' ).forEach( ( row ) => {
		row.querySelectorAll( 'td' ).forEach( ( cell, index ) => {
			cell.dataset.cellLabel = headings[ index ].textContent + ': ';
		} );
	} );
} );
