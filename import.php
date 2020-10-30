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

const SEPARATOR = '%';
const NIMIAVARUUS = 'Kielitiede';

const CONCEPT_TEMPLATE = 'Käsite';
const EXPRESSION_TEMPLATE = 'Nimitys';
const REF_EXPRESSION_TEMPLATE = 'Liittyvä nimitys';
const RELATED_CONCEPT_TEMPLATE = 'Lähikäsite';
const _REF_EXPRESSION_TEMPLATE = '_Liittyvä nimitys';
const _RELATED_CONCEPT_TEMPLATE = '_Lähikäsite';

const IMPORTING_USER = "Aineiston tuonti";

const CONCEPT_FIELD = 'käsite';
const EXPRESSION_FIELD = 'nimitys';

const SOURCE = 'lähdeaineisto';

const LANGUAGE = 'kieli';
const EQUIVALENCE = 'käännösvastaavuus';
const STATUS = 'käyttösuositus';
const ORIGIN = 'alkuperä';
const NOTE = 'käyttöhuomautus';
const ETYMOLOGY = 'etymologia';

const RELATED_CONCEPT = 'lähikäsite';
const RELATION = 'käsitesuhde';

class TermbankImport extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'filecode', '.', true, true );
		$this->addOption( 'namespace', '.', true, true );
		$this->addOption( 'source', '.', true, true );
		$this->addOption( 'overwrite', '.', true, true );
		$this->addOption( 'checked', '.', true, true );
	}

	public function execute() {
		$overwrite = $this->getOption( 'overwrite' );
		$namespace = $this->getOption( 'namespace' );
		$checked = $this->getOption( 'checked' );
		$source = $this->getOption( 'source' );

		$contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();
		$namespaceId = $contentLanguage->getNsIndex( $namespace );

		if ( $namespaceId === false ) {
			echo "Namespace has no id";
			die();
		}

		$confile = $this->getOption( 'filecode' );
		$confile .= "_con.csv";
		$expfile = $this->getOption( 'filecode' );
		$expfile .= "_exp.csv";
		$relfile = $this->getOption( 'filecode' );
		$relfile .= "_rel.csv";

		echo "$confile, $expfile, $relfile";

		$concepts = $this->parseCSV( $confile, 1 );
		$expressions = $this->parseCSV( $expfile, false );
		$relations = $this->parseCSV( $relfile, false );

		foreach ( $expressions as $fields ) {
			$concept = $fields[CONCEPT_FIELD];
			if ( !$concept ) {
				continue;
			}
			$referring = [
				EXPRESSION_FIELD => $fields[EXPRESSION_FIELD],
				LANGUAGE => $fields[LANGUAGE],
				EQUIVALENCE => $fields[EQUIVALENCE],
				STATUS => $fields[STATUS],
				ORIGIN => $fields[ORIGIN],
				NOTE => $fields[NOTE],
			];

			$referring = array_filter( $referring );
			if ( !isset( $concepts[$concept] ) ) {
				echo "Expression refers to an unidentified concept: $concept\n";
			}

			$concepts[$concept][_REF_EXPRESSION_TEMPLATE][] = $referring;
		}

		foreach ( $relations as $fields ) {
			$subject = $fields[CONCEPT_FIELD];
			$referring = [
				CONCEPT_FIELD => $fields[RELATED_CONCEPT],
				RELATION => $fields[RELATION],
			];

			$referring = array_filter( $referring );
			if ( isset( $concepts[$subject] ) ) {
				$concepts[$subject][_RELATED_CONCEPT][] = $referring;
			}
		}

		foreach ( $expressions as $i => $fields ) {
			unset( $fields[CONCEPT_FIELD] );
			unset( $fields[ETYMOLOGY] );
			unset( $fields[EQUIVALENCE] );
			unset( $fields[NOTE] );
			$expressions[$i] = array_filter( $fields );
		}

		foreach ( $concepts as $i => $concept ) {
			unset( $concept['-'] );
			if ( !isset( $concept['lähteet'] ) ) {
				echo "Apua1\n";
				var_dump( $i, $concept );
				die();
			}
			$lahteet = array_map( 'trim', explode( ',', $concept['lähteet'] ) );
			$lahteet = array_flip( $lahteet );
			unset( $lahteet['Väre'] );
			unset( $lahteet['Piirainen'] );
			ksort( $lahteet );
			$concept['lähteet'] = implode( ', ', array_keys( $lahteet ) );
			$concept = array_filter( $concept );
			$concepts[$i] = $concept;
		}

		foreach ( $concepts as $concept ) {
			if ( !isset( $concept[CONCEPT_FIELD] ) ) {
				continue;
			}
			$title = Title::makeTitleSafe( $namespaceId, $concept[CONCEPT_FIELD] );
			if ( !$title ) {
				echo "Invalid title for $concept[CONCEPT_FIELD]\n";
				continue;
			}
			$concept[SOURCE] = $source;
			$this->insert( $title, CONCEPT_TEMPLATE, $concept, $namespace, $overwrite, $checked );
		}

		foreach ( $expressions as $exp ) {
			if ( !isset( $exp[EXPRESSION_FIELD] ) ) {
				continue;
			}
			$title = Title::makeTitleSafe( NS_NIMITYS, $exp[EXPRESSION_FIELD] );
			if ( !$title ) {
				echo "Invalid title for {$exp['nimitys']}\n";
				continue;
			}
			$exp[SOURCE] = $source;
			$this->insert( $title, EXPRESSION_TEMPLATE, $exp, $namespace, $overwrite, $checked );
		}
	}

	protected function parseCSV( $filename, $uniq = 0 ) {
		$data = file_get_contents( $filename );
		$rows = str_getcsv( $data, "\n" );

		$headerRow = array_shift( $rows );
		$headers = str_getcsv( $headerRow, SEPARATOR );

		$output = [];
		foreach ( $rows as $row ) {
			$values = str_getcsv( $row, SEPARATOR );

			if ( count( $values ) !== count( $headers ) ) {
				echo "Row length not matching to headers\n";
				var_dump( $headers, $values );
				die();
			}

			if ( is_int( $uniq ) ) {
				$output[$values[$uniq]] = array_combine( $headers, $values );
			} else {
				$output[] = array_combine( $headers, $values );
			}
		}
		return $output;
	}

	protected function insert(
		Title $title,
		$mainTemplate,
		$fields,
		$namespace,
		$overwrite,
		$checked
	) {
		$content = "{{" . "$mainTemplate\n";
		if ( $mainTemplate === CONCEPT_TEMPLATE ) {
			$content .= "|tarkistettu=$checked\n";
		}

		foreach ( $fields as $key => $value ) {
			if ( $key[0] === '_' ) {
				continue;
			}
			$value = preg_replace( '/\[([^\]]+?)\|([^\]]+?)\]/', "[[$namespace:$1|$2]]", $value );
			$value = preg_replace( '/\[([^|\]]+?)\]/', "[[$namespace:$1|$1]]", $value );
			$content .= "|$key=$value\n";
		}
		$content .= "}}\n";

		foreach ( $fields as $key => $value ) {
			if ( $key[0] !== '_' ) {
				continue;
			}
			$key = ltrim( $key, '_' );
			foreach ( $value as $subitem ) {
				$content .= "{{" . "$key\n";
				if ( $key == 'Liittyvä nimitys' ) {
					$content .= "|otsikossa=Y\n";
				}
				foreach ( $subitem as $subkey => $subvalue ) {
					$content .= "|$subkey=$subvalue\n";
				}
				$content .= "}}\n";
			}
		}

		$user = User::newFromName( IMPORTING_USER, false );
		$page = new WikiPage( $title );
		echo "Importing $title";

		if ( $page->exists() == true ) {
			echo " --> Wiki already has page: $title";
			if ( $overwrite == 'y' ) {
				echo " --> Replacing\n$content\n";
				$page->doEdit( $content, IMPORTING_USER, 0, false, $user );
			}
			if ( $overwrite == 'n' ) {
				echo " --> Not replaced\n";
			}
		} else {
			echo "-->Saved\n";
			$page->doEdit( $content, IMPORTING_USER, 0, false, $user );
		}
	}
}

$maintClass = TermbankImport::class;
require_once RUN_MAINTENANCE_IF_MAIN;
