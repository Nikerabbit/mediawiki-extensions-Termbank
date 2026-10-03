<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank\Maintenance;

/**
 * ...
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2011-2012, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Override;

class ImportNotes extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Adds hidden notes only shown to experts' );
		$this->addOption( 'notes', 'File containing the notes', true, true );
	}

	#[Override]
	public function execute(): void {
		$contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();
		$notes = $this->parseCSV( $this->getOption( 'notes' ) );

		foreach ( $notes as $fields ) {
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
				$title = Title::makeTitleSafe( $namespaceId, $käsite );
				if ( !$title ) {
					echo "EIN1: Invalid title for {$käsite}\n";
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

	protected function parseCSV( string $filename ): array {
		$data = file_get_contents( $filename );
		if ( $data === false || trim( $data ) === '' ) {
			$this->fatalError( "Cannot read a non-empty import file: $filename" );
		}
		$rows = str_getcsv( $data, "\n", '"', "\\" );

		$headerRow = array_shift( $rows );
		if ( $headerRow === null ) {
			$this->fatalError( 'Missing import headers' );
		}
		$headers = str_getcsv( $headerRow, "\t", '"', "\\" );
		if ( count( $headers ) !== 3 || array_diff( [ 'käsite', 'alue', 'teksti' ], $headers ) ) {
			$this->fatalError( 'Expected headers: käsite, alue, teksti' );
		}

		$output = [];

		foreach ( $rows as $row ) {
			if ( $row === null || $row === '' ) {
				continue;
			}
			$outputRow = str_getcsv( $row, "\t", '"', "\\" );
			if ( count( $outputRow ) !== 3 ) {
				$this->fatalError( 'Expected three fields in each notes row' );
			}
			$output[] = array_combine( $headers, $outputRow );
		}
		return $output;
	}

	protected function insert( Title $title, string $note ): void {
		$dbw = MediaWikiServices::getInstance()->getConnectionProvider()->getPrimaryDatabase();
		$dbw->newReplaceQueryBuilder()
			->replaceInto( 'privatedata' )
			->row( [
				'pd_page' => $title->getArticleId(),
				'pd_text' => $note
			] )
			->uniqueIndexFields( [ 'pd_page' ] )
			->caller( __METHOD__ )
			->execute();
	}
}
