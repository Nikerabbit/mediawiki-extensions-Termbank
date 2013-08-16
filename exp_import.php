<?php

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
		$this->addOption( 'expressions', '.', true, true );
		$this->addOption( 'source', '.', true, true )
	}

	public function execute() {
	
		$expressions = $this->parseCSV( $this->getOption( 'expressions' ), false );
	
		foreach ( $expressions as $exp ) {
			if ( !isset( $exp['nimitys'] ) ) continue;
			$title = Title::makeTitleSafe( NS_NIMITYS, $exp['nimitys'] );
			if ( !$title ) {
				echo "Invalid title for {$exp['nimitys']}\n";
				continue;
			}
			$exp['lähdeaineisto'] = $source;
			$this->insert( $title, $exp );
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

	protected function insert( Title $title, $fields ) {
	$content = "{{" . "Nimitys\n";
		foreach ( $fields as $key => $value ) {
			if ( $key[0] === '_' ) continue;
			$content .= "|$key=$value\n";
		}
		$content .= "}}\n";

		$user = User::newFromName( 'Aineiston tuonti', false );
		$page = new WikiPage( $title );
		$page->doEdit( $content, 'Aineiston tuonti', 0, false, $user );

	}

$maintClass = 'TBImportExternalDatabase';
require_once( DO_MAINTENANCE );

