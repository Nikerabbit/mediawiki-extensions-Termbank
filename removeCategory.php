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

const IMPORTING_USER = "Aineiston poisto";


class TBRemoveCategory extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'category', '.', true, true );

	}

	public function execute() {

		$categoryname = $this->getOption( 'category' );
		$category = Category::newFromName( $categoryname );
		
		$this->removeContents( $categoryname );
	
	}

	protected function removeContents( $categoryname ) {

	$category = Category::newFromName($categoryname);

	$titles = $category-> getMembers();

	$pagecount = $category->getPageCount();

	
	foreach ($titles as $title) {

		$wikipage = WikiPage::factory( $title );
		echo "poistetaan: ".$title->getText()."\n";
		$wikipage->doDeleteArticleReal( "korjausajo" );
	
		}

	}

		
}

$maintClass = 'TBRemoveCategory';
require_once( DO_MAINTENANCE );


