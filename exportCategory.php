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

class TBExportExternalDatabaseByCategory extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'category', '.', true, true );
	}
	public function execute() {

	$categoryname = $this -> getOption( 'category' );

	
	
	$this->getTitles( $categoryname );
	
	}

	protected function getTitles( $categoryname ) {

	$category = Category::newFromName($categoryname);

	$titles = $category-> getMembers();

	$pagecount = $category->getPageCount();

	echo "$pagecount";
	file_put_contents("lista.txt", "");
	foreach ($titles as $title) {

		$this->getContent($title);
	
		}

	}


	protected function getContent($title) {

	$page = new WikiPage( $title );
	$content = $page->getContent();
	$pagename = $title->getFullText();
	
	$native = "pagename=$pagename\n";
	$native .= $content->getNativeData();
	file_put_contents( "lista.txt", "$native\n", FILE_APPEND );
	}
}
$maintClass = 'TBExportExternalDatabaseByCategory';
require_once( DO_MAINTENANCE );
