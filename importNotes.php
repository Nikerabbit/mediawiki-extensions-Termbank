<?php
/**
 * ...
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2011-2012, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\MediaWikiServices;

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../..';
require_once "$IP/maintenance/Maintenance.php";

const SEPARATOR = '\t';

class TermbankImportNotes extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Adds hidden notes only shown to experts';
		$this->addOption( 'notes', 'File containing the notes', true, true );
	}

	public function execute() {
		$contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();
		$notes = $this->parseCSV( $this->getOption( 'notes' ) );

		foreach ( $notes as $i => $fields ) {
			$käsite = $fields['käsite'];
			if ( !$käsite ) {
				continue;
			}
			$namespace = $fields['alue'];
			$namespaceId = $contentLanguage->getNsIndex( $namespace );
			if ( $namespaceId === false ) {
				echo "EIN3: Unknown namespace: $namespace\n";
			} else {
				$note = $fields['teksti'];
				$note = str_replace( '\n', "\n", $note );
				$title = Title::makeTitle( $namespaceId, $käsite );
				if ( !$title ) {
					echo "EIN1: Invalid title for {$käsite['käsite']}\n";
					continue;
				} elseif ( !$title->exists() ) {
					$name = $title->getPrefixedText();
					echo "EIN2: Page does not exists: $name\n";
					continue;
				} else {
					$this->insert( $title, $note );
				}
			}
		}
	}

	protected function parseCSV( $filename ) {
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );

		$headerRow = array_shift( $rows );
		$headers = str_getcsv( $headerRow, "\t" );

		$output = [];

		foreach ( $rows as $row ) {
			$outputRow = str_getcsv( $row, "\t" );
			$rowcount = count( $outputRow );
			$concept = $outputRow[1];
			if ( $rowcount != 3 ) {
				echo "$concept\n";
			}
			$output[] = array_combine( $headers, $outputRow );
		}
		return $output;
	}

	protected function insert( Title $title, $note ) {
		$dbw = wfGetDB( DB_MASTER );
		$fields = [ 'pd_page' => $title->getArticleId(), 'pd_text' => $note ];
		$dbw->replace( 'privatedata', [ [ 'pd_page' ] ], $fields, __METHOD__ );
	}
}

$maintClass = TermbankImportNotes::class;
require_once RUN_MAINTENANCE_IF_MAIN;
