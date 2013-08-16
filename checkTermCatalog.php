<?php
/**
 * ...
 *
 * @author Antti Kanner
 * @copyright Copyright © 2011-2012, Niklas Laxström
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

class TBcheckCatalog extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'filecode', '.', true, true );
		$this->addOption( 'namespace', '.', true, true );
		}

	public function execute() {

			
		$file = $this->getOption( 'filecode' );
		$namespace = $this->getOption( 'namespace' );
		
		global $wgContLang;
		$namespaceId = $wgContLang->getNsIndex( $namespace );
		
		if ( $namespaceId == false) {
			echo "Namespace has no id";
			die();
		}

		$pages = $this->parseCSV( $file, 1 );
		$existingpages = array();
		$newpages = array();

		foreach ( $pages as $page ) {
			
			if ( !isset( $page ) ) continue;
			$title = Title::makeTitleSafe( $namespaceId, $page );
			if ( !$title ) {
				echo "Invalid title for $page\n";
				continue;
			}
		
			$wikipage = new WikiPage( $title );

			if ($wikipage->exists()) file_put_contents( "ex_".$file, "$page\n", FILE_APPEND );
			else file_put_contents( "new_".$file, "$page\n", FILE_APPEND );
		
		
		}

		

		
	}

	protected function parseCSV( $filename, $uniq = 0 ) {
		
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );

		return $rows;
	}

}
$maintClass = 'TBcheckCatalog';
require_once( DO_MAINTENANCE );

