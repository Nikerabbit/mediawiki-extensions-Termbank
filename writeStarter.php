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

class TBWriteStarter extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'category', '.', true, true );
		$this->addOption( 'string', '.', true, true );

	}

	public function execute() {
		
		$categoryname = $this->getOption( 'category' );
		$string = $this->getOption( 'string' );
		echo 'category:'.$categoryname;
		$category = Category::newFromName($categoryname);

		$titles = $category-> getMembers();

		echo 'pages'.count( $titles );

		foreach ($titles as $title) {
		
		$this->getWriteContent( $title, $string );
	
		}

	}

	protected function getWriteContent($title, $string) {

	$page = new WikiPage( $title );
	$content = $page->getContent();
	$pagename = $title->getFullText();
	$length = strlen( $string );
	$textcontent = $content->getNativeData();
	if ( substr( $textcontent, 0, $length ) === $string ) {
		echo $pagename." has already";
	}
	else {
	$newcontent = $string."\n".$textcontent;

	$page->doEdit( $newcontent, 'Huoltotöitä' );

	}
}
}
$maintClass = 'TBWriteStarter';
require_once( DO_MAINTENANCE );
