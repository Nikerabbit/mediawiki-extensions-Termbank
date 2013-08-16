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

class TBExportExternalDatabaseByList extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'list', '.', true, true );
		$this->addOption( 'outputfile', '.', true, true );

	}

	public function execute() {
		
		$filename = $this->getOption( 'list' );
		$fullfile = file_get_contents( $filename );
		$pagelist = explode( "\n", $fullfile );
		$outputfile = $this->getOption( 'outputfile' );
		
		foreach ( $pagelist as $page ) {
			
			$this->getContent( $page, $outputfile );

		}
			
	
	}

	protected function getContent( $pagename, $outputfile) {

		echo $pagename."\n";
		$title = Title::newFromText( $pagename );
		if ( $title->exists() ) {
			$page = new WikiPage( $title );
			$content = $page->getContent();
			$pagename = $title->getFullText();
			$native = "pagename=$pagename\n";
			$native .= $content->getNativeData();
			file_put_contents( $outputfile, "$native\n", FILE_APPEND );
		}
		else {
			echo "ei löydy:".$pagename."\n";
		}
		
	}
}
$maintClass = 'TBExportExternalDatabaseByList';
require_once( DO_MAINTENANCE );
