<?php
/**
 * ...
 *
 * @author Antti Kanner
 * @copyright Copyright © 2011-2012, Antti Kanner
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../..";
}
require_once( "$IP/maintenance/Maintenance.php" );

class TBExportCheckExpList extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'list', '.', true, true );
	}
	public function execute() {

	$file = $this->getOption( 'list' );

	$titles = $this->parseCSV( $file );
	
	$hits = array();
	$misses = array();
	
	foreach ($titles as $title) {
		
		if ($title->isKnown()) array_push( $hits, $title );
		else array_push( $misses, $title );
		}

	foreach ($hits as $hit) {
		file_put_contents( $file."_hit.txt", $hit->getText()."\n", FILE_APPEND );
	}
	
	foreach ($misses as $miss) {
		file_put_contents( $file."_miss.txt", $miss->getText()."\n", FILE_APPEND );
	}

	

}


	protected function parseCSV( $file ) {
		
		$data = file_get_contents( $file );
		
		$rows = str_getcsv( $data, "\n" );
		
		$output = array();
		
		foreach ($rows as $row ) {
			
			$title = Title::newFromText ('Nimitys:'.$row);
			array_push( $output, $title );
			
			
		}
		
		return $output;
		
	}
		
}
$maintClass = 'TBExportCheckExpList';
require_once( DO_MAINTENANCE );
