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

const SEPARATOR = '%';

class TBImportExternalDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'namespace', '.', true, true );
		$this->addOption( 'notes', '.', true, true );

	}

	public function execute() {
		$namespace = $this->getOption( 'namespace' );
		
		global $wgContLang;
		$namespaceId = $wgContLang->getNsIndex( $namespace );
		if ( $namespaceId == false) {
				echo "Ei nimiavaruuden nimikoodia";
				die();
			}

		$notes = $this->parseCSV( $this->getOption( 'notes' ), 1 );

		foreach ( $notes as $i => $fields ) {
			$käsite = $fields['käsite'];
			if ( !$käsite ) {
				continue;
			}
		
			$note = $fields['teksti'];
			$note = str_replace( '\n', "\n", $note);
			$title = Title::makeTitle( $namespaceId, $käsite );
			if ( !$title ) {
				echo "Invalid title for {$käsite['käsite']}\n";
				continue;
			}
			if (!$title -> exists()) {
				echo "Ei sivua: $title";
				continue;
			}

			$this->insert( $title, $note );

		}




	}

	protected function parseCSV( $filename, $uniq = 0 ) {
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );

		$headerRow = array_shift( $rows );
		$headers = str_getcsv( $headerRow, SEPARATOR );

		$output = array();
		foreach ( $rows as $row ) {
			$values = str_getcsv( $row, SEPARATOR );

			if ( count( $values ) !== count( $headers ) ) {
				echo "Apua2\n";
				var_dump( $headers, $values ); die();
			}

			if ( is_int($uniq) ) {
				$output[$values[$uniq]] = array_combine( $headers, $values );
			} else {
				$output[] = array_combine( $headers, $values );
			}
		}
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

