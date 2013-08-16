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

class TBExportExternalDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'pagename', '.', true, true );
		$this->addOption( 'namespace', '.', true, true );
	}
	public function execute() {

	$pagename = $this -> getOption( 'pagename' );
	$namespace = $this -> getOption( 'namespace' );
	
	global $wgContLang;
	$namespaceId = $wgContLang->getNsIndex( $namespace );
	if ( $namespaceId == false) {
			echo "Ei nimiavaruuden nimikoodia";
			die();
		}

	$this->getContent( $pagename, $namespaceId, $namespace );
	
	}

	protected function getContent($pagename, $namespaceId, $namespace) {
	$title = Title::makeTitleSafe( $namespaceId, $pagename );
	$page = new WikiPage( $title );
	$content = $page->getContent();
	$native = "pagename=$namespace:$pagename\n";
	$native .= $content->getNativeData();
	echo "$native";
	file_put_contents( $pagename, $native );
	}
}
$maintClass = 'TBExportExternalDatabase';
require_once( DO_MAINTENANCE );
