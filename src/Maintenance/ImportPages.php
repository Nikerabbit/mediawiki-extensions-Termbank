<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank\Maintenance;

/**
 * ...
 *
 * @author Antti Kanner
 * @copyright Copyright © 2011-2024, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use ContentHandler;
use Maintenance;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use UtfNormal\Validator;

class ImportPages extends Maintenance {
	private const string IMPORTING_USER = 'Aineiston tuonti';

	public function __construct() {
		parent::__construct();
		$this->addOption( 'filecode', '.', true, true );
		$this->addOption( 'overwrite', '.', true, true );
		$this->addOption( 'checked', '.', true, true );
		$this->addOption( 'extend', '.', true, true );
	}

	public function execute(): void {
		$overwrite = $this->getOption( 'overwrite' );
		$checked = $this->getOption( 'checked' );
		$extend = $this->getOption( 'extend' );

		$file = $this->getOption( 'filecode' );
		$pages = $this->parseCSV( $file );

		foreach ( $pages as $page ) {
			$namespace = $page['namespace'] ?? '';
			$pagename = $page['pagename'] ?? '';
			$content = $page['content'] ?? '';
			$content = Validator::cleanUp( $content );

			$title = Title::newFromText( Validator::cleanUp( "$namespace:$pagename" ) );
			if ( !$title || $title->inNamespace( NS_MAIN ) ) {
				echo "Invalid title for $pagename\n";
				continue;
			}

			$this->insert( $title, $content, $overwrite, $checked, $extend );
		}
	}

	/** Eats a filename, returns a list of dicts(ns, title, content) */
	protected function parseCSV( string $filename ): array {
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );
		$output = [];

		foreach ( $rows as $row ) {
			$headers = [ "namespace", "pagename", "content" ];
			$values = str_getcsv( $row, "\t" );

			if ( count( $values ) !== 3 ) {
				echo "Row length not matching to headers\n";
				var_dump( $values );
				die();
			}

			$output[] = array_combine( $headers, $values );
		}

		print_r( $output );
		return $output;
	}

	protected function insert(
		Title $title,
		string $content,
		string $overwrite,
		string $checked,
		string $extend,
	): void {
		$content = preg_replace( '/}}{{/', "}}\n{{", $content );
		$content = preg_replace( '/\|/', "\n|", $content );
		$content = preg_replace( '/<putki>/', "|", $content );

		if ( $checked === 'y' ) {
			$content = preg_replace( '/{{Käsite\|/', "{{Käsite|tarkistettu=y|", $content );
		}
		if ( $checked === 'n' ) {
			$content = preg_replace( '/{{Käsite\|/', "{{Käsite|tarkistettu=N|", $content );
		}

		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();

		$user = $userFactory->newFromName( self::IMPORTING_USER );
		if ( !$user ) {
			$this->fatalError( "Invalid user name: " . self::IMPORTING_USER );
		}

		$content = Validator::cleanUp( $content );
		$contentObj = ContentHandler::makeContent( $content, $title );
		$page = $wikiPageFactory->newFromTitle( $title );

		echo "Importing $title";

		if ( $page->exists() ) {
			echo " --> Wiki already has page: $title\n";
			if ( $overwrite === 'y' ) {
				echo " --> Replacing\n";

				if ( $extend === 'y' ) {
					if ( strlen( $content ) > strlen( $page->getUserText() ) ) {
						$page->newPageUpdater( $user )
							->setContent( SlotRecord::MAIN, $contentObj )
							->saveRevision( CommentStoreComment::newUnsavedComment(
								self::IMPORTING_USER
							) );
					} else {
						echo "--> not extended";
					}
				} else {
					$page->newPageUpdater( $user )
						->setContent( SlotRecord::MAIN, $contentObj )
						->saveRevision( CommentStoreComment::newUnsavedComment(
							self::IMPORTING_USER
						) );
				}
			}
			// else: not replaced
		} else {
			echo " --> Saved\n";
			$page->newPageUpdater( $user )
				->setContent( SlotRecord::MAIN, $contentObj )
				->saveRevision( CommentStoreComment::newUnsavedComment( self::IMPORTING_USER ) );
		}
	}
}
