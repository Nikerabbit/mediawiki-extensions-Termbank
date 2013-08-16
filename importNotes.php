<?php
/**
 * ...
 *
 * @author Niklas Laxstrom
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

const SEPARATOR = '\t';

class TBImportExternalDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Adds hidden notes only shown to experts';
		
		$this->addOption( 'notes', 'File containing the notes', true, true );

	}

	public function execute() {
		
		
		global $wgContLang;
		$notes = $this->parseCSV( $this->getOption( 'notes' ), 1 );

		foreach ( $notes as $i => $fields ) {
			$käsite = $fields['käsite'];
			if ( !$käsite ) {
				continue;
			}
			$namespace = $fields['alue'];		
			$namespaceId = $wgContLang->getNsIndex( $namespace );
			if ( $namespaceId === false ) {
				echo "EIN3: Unknown namespace: $namespace\n";
				
			}
			else {
			$note = $fields['teksti'];
			$note = str_replace( '\n', "\n", $note);
			$title = Title::makeTitle( $namespaceId, $käsite );
			if ( !$title ) {
				echo "EIN1: Invalid title for {$käsite['käsite']}\n";
				continue;
			}
			else if ( !$title->exists() ) {
				$name = $title->getPrefixedText();
				echo "EIN2: Page does not exists: $name\n";
				continue;
			}
			else {
			$this->insert( $title, $note );
			}
			}
		}
	}

	protected function parseCSV( $filename, $uniq = 0 ) {
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );

		$headerRow = array_shift( $rows );
		$headers = str_getcsv( $headerRow, "\t" );

		$output = array();
		
		foreach ($rows as $row ) {
			
		$outputrow = str_getcsv( $row, "\t" );
		$rowcount = count($outputrow);
		$concept = $outputrow[1];
		if ($rowcount != 3 ) echo "$concept\n";
		$output[] = array_combine( $headers, $outputrow );

		}		

		/*
		foreach ( $rows as $row ) {
			$values = str_getcsv( $row, SEPARATOR );
			
			$contents = $values;
			$content = $values[1];
			unset($contents[0]);
			unset($contents[1]);
			

			foreach ($contents as $element) {
			
			$content = $content+'%'+$element;

			}

			$result[0] = $values[0];
			$result[1] = $content;

			if ( is_int($uniq) ) {
				//echo '$uniq:'+$uniq;				
				$output[$values[$uniq]] = array_combine( $headers, $result );
			} else {
				$output[] = array_combine( $headers, $result );
			}
		}
		*/	
		return $output;
	}

	protected function insert( Title $title, $note ) {

		$dbw = wfGetDB( DB_MASTER );
		$fields = array( 'pd_page' => $title->getArticleId(), 'pd_text' => $note );
		$dbw->replace( 'privatedata', array( array( 'pd_page' ) ), $fields, __METHOD__ );

	}

}

$maintClass = 'TBImportExternalDatabase';
require_once( DO_MAINTENANCE );

