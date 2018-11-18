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

const IMPORTING_USER = 'Aineiston tuonti';

class TBImportExternalDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'filecode', '.', true, true );
		$this->addOption( 'overwrite', '.', true, true );
		$this->addOption( 'checked', '.', true, true );
		$this->addOption( 'extend', '.', true, true );
	}

	public function execute() {
		$overwrite = $this->getOption( 'overwrite' );
		$checked = $this->getOption( 'checked' );
		$extend = $this->getOption( 'extend' );
		
		if ( $this->getOption( 'checked' ) === 'y' || $this->getOption( 'checked' ) === 'n') {
			$checked == $this->getOption( 'checked' );
		} else {
			echo "Invalid value in checked (y/n)";
			die();
		}
		
		if ( $this->getOption( 'overwrite' ) === 'y' || $this->getOption( 'overwrite' ) === 'n') {
			$overwrite == $this->getOption( 'overwrite' );
		} else {
			echo "Invalid value in overwrite (y/n)";
			die();
		}
		
		
		$file = $this->getOption( 'filecode' );
		$pages = $this->parseCSV( $file );

		foreach ( $pages as $page ) {
			$namespace = $page['namespace'];
			$pagename = $page['pagename'];
			$content = $page['content'];
			$content = UtfNormal\Validator::cleanUp( $content );

			$title = Title::newFromText( UtfNormal\Validator::cleanUp( "$namespace:$pagename" ) );
			if ( !$title || $title->inNamespace( NS_MAIN ) ) {
				echo "Invalid title for $pagename\n";
				continue;
			}
			
			$this->insert( $title, $content, $namespace, $overwrite, $checked, $extend );
		}
	}

	// Eats a filename, returns a list of dicts(ns, title, content)
	protected function parseCSV( $filename ) {
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );
			$output = array();

		foreach ( $rows as $row ) {
			$headers = array("namespace", "pagename", "content");
			$values = str_getcsv( $row, "\t" );

			if ( count( $values ) !== 3 ) {
				echo "Row length not matching to headers\n";
				var_dump( $values );
				die();
			}

			$output[] = array_combine( $headers, $values );
		}

		print_r($output);
		return $output;
	}

	protected function insert( Title $title, $content, $namespace, $overwrite, $checked, $extend ) {
		$templateSwitch = "}}{{";
		$callSwitch = "|";

		$content = preg_replace( '/}}{{/', "}}\n{{", $content );
		$content = preg_replace( '/\|/', "\n|", $content );
		$content = preg_replace( '/<putki>/', "|", $content );

		if ($checked == 'y' ) $content = preg_replace( '/{{Käsite\|/', "{{Käsite|tarkistettu=y|", $content );
		if ($checked == 'n' ) $content = preg_replace( '/{{Käsite\|/', "{{Käsite|tarkistettu=N|", $content );

		$user = User::newFromName( IMPORTING_USER, false );
		$page = new WikiPage( $title );
		$contentObj = ContentHandler::makeContent( $content, $title );
		$existingPages = [ 'pagename' ];
		echo "Importing $title";
		
		if ( $page->exists() ) {
			$existingPages[] = $title;
			echo " --> Wiki already has page: $title\n";
			if ( $overwrite == 'y' ) {
				echo " --> Replacing\n";
				
				if ( $extend == 'y' ) {
					if (strlen($content) > strlen($page->getRawText())) {
						$page->doEditContent( $contentObj, IMPORTING_USER, 0, false, $user );
					} else {
						echo "--> not extended";
					}
				
				} else {
					$page->doEditContent( $contentObj, IMPORTING_USER, 0, false, $user );
				}
			} else {
				//echo " --> Not replaced\n";
			}
		} else {
			echo " --> Saved\n";
			$page->doEditContent( $contentObj, IMPORTING_USER, 0, false, $user );
		}
	}
}

$maintClass = 'TBImportExternalDatabase';
require_once( RUN_MAINTENANCE_IF_MAIN );
