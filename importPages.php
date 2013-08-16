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

const SEPARATOR = '%';
const NIMIAVARUUS = 'Kielitiede';

const CONCEPT_TEMPLATE		= 'Käsite';
const EXPRESSION_TEMPLATE	= 'Nimitys';
const REF_EXPRESSION_TEMPLATE	= 'Liittyvä nimitys';
const RELATED_CONCEPT_TEMPLATE	= 'Lähikäsite';

const IMPORTING_USER = "Aineiston tuonti";

const CONCEPT_FIELD	= 'käsite';
const EXPRESSION_FIELD	= 'nimitys';

const SOURCE = 'lähdeaineisto';

const LANGUAGE	  = 'kieli';
const EQUIVALENCE = 'käännösvastaavuus';
const STATUS      = 'käyttösuositus';
const ORIGIN      = 'alkuperä';
const NOTE        = 'käyttöhuomautus';
const ETYMOLOGY   = 'etymologia';

const RELATED_CONCEPT = 'lähikäsite';
const RELATION        = 'käsitesuhde';

class TBImportExternalDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'filecode', '.', true, true );
		$this->addOption( 'source', '.', true, true );
		$this->addOption( 'overwrite', '.', true, true );
		$this->addOption( 'checked', '.', true, true );
		$this->addOption( 'extend', '.', true, true );
	}

	public function execute() {

		$overwrite = $this->getOption( 'overwrite' );
		$checked = $this->getOption( 'checked' );
		$extend = $this->getOption( 'extend' );
		$source = $this->getOption( 'source' );
		
		if ( $this->getOption( 'checked' ) === 'y' OR $this->getOption( 'checked' ) === 'n') {
				$checked == $this->getOption( 'checked' );
		}
		else {
				echo "Invalid value in checked (y/n)";
				die();
		}
		
		if ( $this->getOption( 'overwrite' ) === 'y' OR $this->getOption( 'overwrite' ) === 'n') {
				$overwrite == $this->getOption( 'overwrite' );
		}
		else {
				echo "Invalid value in overwrite (y/n)";
				die();
		}	
		
		
		$file = $this->getOption( 'filecode' );
  
		$pages = $this->parseCSV( $file );

		foreach ( $pages as $page ) {
			
			$namespace = $page['namespace'];
			$pagename = $page['pagename'];
      $content = $page['content'];

			global $wgContLang;
			$namespaceId = $wgContLang->getNsIndex( $namespace );
		
			if ( $namespaceId == false) {
				echo "Namespace has no id";
				
			}			
			else {
			if ( !isset( $pagename ) ) continue;
			$title = Title::makeTitleSafe( $namespaceId, $pagename );
			if ( !$title ) {
				echo "Invalid title for $pagename\n";
				continue;
			}
			
			$this->insert( $title, $content, $namespace, $overwrite, $checked, $extend );
		}
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

		//echo $namespace, $content;

		$user = User::newFromName( IMPORTING_USER, false );
		$page = new WikiPage( $title );
		$existingPages = array("pagename");
		echo "Importing $title";
		
		if ($page->exists() == true) {
		array_push($existingPages, $title);
		echo " --> Wiki already has page: $title\n";
			if ($overwrite == 'y' ) {
				echo " --> Replacing\n";
				
				if ($extend == 'y' ) {
				
					if (strlen($content) > strlen($page->getRawText())) {
						$page->doEdit( $content, IMPORTING_USER, 0, false, $user );	
					}
					else echo "--> not extended";
				
				}
				
				else {
					$page->doEdit( $content, IMPORTING_USER, 0, false, $user );		
				}
			}
			
			
			else {
				//echo " --> Not replaced\n";
			}
		}
		else {
			echo "-->Saved\n";
			$page->doEdit( $content, IMPORTING_USER, 0, false, $user );	
		}
		//file_put_contents('out_log.txt', print_r($existingPages, true));	
}
		
}

$maintClass = 'TBImportExternalDatabase';
require_once( DO_MAINTENANCE );

