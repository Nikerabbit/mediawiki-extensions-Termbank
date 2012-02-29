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
const NIMIAVARUUS = 'Kielitiede';

class TBImportExternalDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'expressions', '.', true, true );
		$this->addOption( 'concepts', ',', true, true );
		$this->addOption( 'relations', '-', true, true );
		$this->addOption( 'namespace', '.', true, true );
		$this->addOption( 'source', '.', true, true );
	}

	public function execute() {

		$namespace = $this->getOption( 'namespace' );

		$source = $this->getOption( 'source' );
		global $wgContLang;
		$namespaceId = $wgContLang->getNsIndex( $namespace );
		if ( $namespaceId == false) {
				echo "Ei nimiavaruuden nimikoodia";
				die();
			}

		$concepts = $this->parseCSV( $this->getOption( 'concepts' ), 1 );
		$expressions = $this->parseCSV( $this->getOption( 'expressions' ), false );
		$relations = $this->parseCSV( $this->getOption( 'relations' ), false );

		foreach ( $expressions as $fields ) {
			$käsite = $fields['käsite'];
			if ( !$käsite ) {
				continue;
			}
			$liittyvä = array(
				'nimitys' => 'Nimitys:' . $fields['nimitys'],
				'kieli'   => $fields['kieli'],
				'käännösvastaavuus' => $fields['käännösvastaavuus'],
				'käyttösuositus' => $fields['käyttösuositus'],
				'alkuperä' => $fields['alkuperä']
			);

			$liittyvä = array_filter( $liittyvä );
			if ( !isset( $concepts[$käsite] ) ) {
				echo "Nimitys viittaa tuntemattomaan käsitteeseen: $käsite\n";
			}
			$concepts[$käsite]['_Liittyvä nimitys'][] = $liittyvä;
		}

		foreach ( $relations as $fields ) {
			$subject = $fields['käsite'];
			$liittyvä = array(
				'käsite' => "$namespace:" . $fields['lähikäsite'],
				'käsitesuhde'   => $fields['käsitesuhde'],
			);

			$liittyvä = array_filter( $liittyvä );
			if ( isset( $concepts[$subject] ) ) {
				$concepts[$subject]['_Lähikäsite'][] = $liittyvä;
			}
		}

		foreach ( $expressions as $i => $fields ) {
			unset( $fields['käsite'] );
			unset( $fields['etymologia'] );
			unset( $fields['käännösvastaavuus'] );
			unset( $fields['käyttösuositus'] );
			$expressions[$i] = array_filter( $fields );
		}

		foreach ( $concepts as $i => $concept ) {
			unset( $concept['-'] );
			if ( !isset( $concept['lähteet'] ) ) { echo "Apua1\n"; var_dump( $i, $concept ); die(); }
			$lähteet = array_map( 'trim', explode( ',', $concept['lähteet'] ) );
			$lähteet = array_flip( $lähteet );
			unset( $lähteet['Väre'] );
			unset( $lähteet['Piirainen'] );
			ksort( $lähteet );
			$concept['lähteet'] = implode( ', ', array_keys( $lähteet ) );
			$concept = array_filter( $concept );
			$concepts[$i] = $concept;
		}

		foreach ( $concepts as $concept ) {
			if ( !isset( $concept['käsite'] ) ) continue;
			$title = Title::makeTitleSafe( $namespaceId, $concept['käsite'] );
			if ( !$title ) {
				echo "Invalid title for {$concept['käsite']}\n";
				continue;
			}
			$concept['lähdeaineisto'] = $source;
			$this->insert( $title, 'Käsite', $concept, $namespace );
		}

		foreach ( $expressions as $exp ) {
			if ( !isset( $exp['nimitys'] ) ) continue;
			$title = Title::makeTitleSafe( NS_NIMITYS, $exp['nimitys'] );
			if ( !$title ) {
				echo "Invalid title for {$exp['nimitys']}\n";
				continue;
			}
			$exp['lähdeaineisto'] = $source;
			$this->insert( $title, 'Nimitys', $exp, $namespace );
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

	protected function insert( Title $title, $mainTemplate, $fields, $namespace ) {
		$content = "{{" . "$mainTemplate\n";
		foreach ( $fields as $key => $value ) {
			if ( $key[0] === '_' ) continue;
			$value = preg_replace( '/\[([^\]]+?)\|([^\]]+?)\]/', "[[$namespace:$1|$2]]", $value );
			$value = preg_replace( '/\[([^|\]]+?)\]/', "[[$namespace:$1|$1]]", $value );
			$content .= "|$key=$value\n";
		}
		$content .= "}}\n";

		foreach ( $fields as $key => $value ) {
			if ( $key[0] !== '_' ) continue;
			$key = ltrim( $key, '_' );
			foreach ( $value as $subitem ) {
				$content .= "{{" . "$key\n";
				foreach ( $subitem as $subkey => $subvalue ) {
					$content .= "|$subkey=$subvalue\n";
				}
				$content .= "}}\n";
			}
		}

		$user = User::newFromName( 'Aineiston tuonti', false );
		$page = new WikiPage( $title );
		$page->doEdit( $content, 'Aineiston tuonti', 0, false, $user );
	}

}

$maintClass = 'TBImportExternalDatabase';
require_once( DO_MAINTENANCE );

