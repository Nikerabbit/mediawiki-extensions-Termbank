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
		/*$this->addOption( 'expressions', '.', true, true );
		$this->addOption( 'concepts', ',', true, true );
		$this->addOption( 'relations', '-', true, true );
		$this->addOption( 'namespace', '.', true, true );
		$this->addOption( 'source', '.', true, true );*/
	}

	public function execute() {
		$full = $this->parseCSV( 'n.txt', false );

		$concepts = array();
		foreach ( $full as $row ) {
			$belongsTo = $row['käsite'];
			$row = array_filter( $row );
			if ( !isset( $concepts[$belongsTo] ) ) {
				$concept[$belongsTo][] = $row;
			}
		}

		############ Käsitesivut
		foreach ( $concept as $name => $expressions ) {
			$title = Title::makeTitle( NS_KASVITIEDE, $name );
			if ( !$title->exists() ) {
				echo $title->getPrefixedText() . " doesn't exist\n";
			}

			$page = TemplateParser::newFromTitle( $title );
			$parsed = $page->extractTemplates();
			foreach ( $parsed as $template ) {
				if ( $template['name'] !== 'Liittyvä nimitys' ) continue;
				foreach ( $expressions as $index => $exp ) {
					if ( $template['params']['kieli'] === $exp['kieli'] && 
					     trim( $template['params']['nimitys'] ) === "Nimitys:" . $exp['nimitys'] ) {
						echo "Found {$template['params']['nimitys']} {$template['params']['kieli']}\n";
						unset( $expressions[$index] );
					}
				}
			}

			foreach ( $expressions as $exp ) {
				echo "Addinng Nimitys:{$exp['nimitys']} {$exp['kieli']}\n";
				$parsed[] = array(
					'name' => 'Liittyvä nimitys',
					'params' => array(
						'kieli' => $exp['kieli'],
						'nimitys' => "Nimitys:" . $exp['nimitys'],
					)
				);
			}

			$text = $page->updateText( $parsed );
			if ( $text ) {
				echo "Going to save page " . $title->getPrefixedText() . "\n";
				# var_dump( $text );

				# yay readline( "Ok?" );
				$user = User::newFromName( 'Aineiston tuonti', false );
				$wikipage = new WikiPage( $title );
				$wikipage->doEdit( $text, 'Lisätään puuttuvia nimityksiä', 0, false, $user );
			}
		}

		############ Nimityssivut

		foreach ( $full as $exp ) {
			$title = Title::makeTitle( NS_NIMITYS, $exp['nimitys'] );
			if ( !$title->exists() ) {
				echo $title->getPrefixedText() . " doesn't exist\n";
			}

			$page = TemplateParser::newFromTitle( $title );
			$parsed = $page->extractTemplates();
			foreach ( $parsed as $template ) {
				if ( $template['name'] !== 'Nimitys' ) continue;
				if ( $template['params']['kieli'] === $exp['kieli'] && 
				     trim( $template['params']['nimitys'] ) === $exp['nimitys'] ) {
					continue 2;
				}
			}

			$exp = array_filter( $exp );
			$exp['lähdeaineisto'] = 'Kaarina_iso';

			$parsed[] = array(
				'name' => 'Nimitys',
				'params' => $exp,
			);

			$text = $page->updateText( $parsed );
			if ( $text ) {
				echo "Going to save page " . $title->getPrefixedText() . "\n";
				#var_dump( $text );

				#readline( "Ok?" );
				$user = User::newFromName( 'Aineiston tuonti', false );
				$wikipage = new WikiPage( $title );
				$wikipage->doEdit( $text, 'Lisätään puuttuvia nimityksiä', 0, false, $user );
			}
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

}

$maintClass = 'TBImportExternalDatabase';
require_once( DO_MAINTENANCE );

