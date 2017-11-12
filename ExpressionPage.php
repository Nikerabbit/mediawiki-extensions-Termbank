<?php

	if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
	}




/**
 * @param $input string
 * @param $args array
 * @param $parser Parser
 * @param $frame Frame 
 * @return string
 */
const LANGUAGE = 0;
const POS = 1;
const FORM = 2;
const DERIV = 3;
const GENUS = 4;
const ORIGIN = 5;

class ExpressionPage {
	public static function listConcepts( $input, $args, $parser, $frame ) {
		$input = $parser->recursiveTagParse($input, $frame);

		$expression = $input;
		$expressiontitle = Title::newFromText( $expression );

		$store = smwfGetStore();
		$expressionproperty = SMWDIProperty::newFromUserLabel( 'Nimitys' );

		$return = '';

		if ( $expressiontitle === null ) {
			return "Ei tuloksia.";
		}

		$expressionpage = SMWDIWikiPage::newFromTitle($expressiontitle);

		$values = $store->getPropertyValues( $expressionpage, $expressionproperty );
		$pages = $store->getPropertySubjects( $expressionproperty, $expressionpage );

// getPropertySubjects palauttaa myös SIO:t
// Tässä tapauksessa kaikki palautettavat sivut ovat SIO:ita, sillä vain Liittyvällä nimityksellä on ominaisuus Nimitys.

		$expressionsubs = array();
		$hits = array();

		foreach ( $pages as $page ) {

// koska SIO:iden nimi on mallia: Namespace:Pagename#idcodemachinereadablenonsense, se pitää purkaa jotta sitä voidaan edelleen käyttää käsitesivun nimenä.
		
			$title = $page->getTitle();
			$titletext = $title->getFullText();
			$list = explode( "#", $titletext );
			$fullpagename = $list[0];
			$namespace = $title->getNsText();
			$pagename = $title->getText();
			//$pagenameaslist = explode( ":", $fullpagename );			
			//$pagename = $pagenameaslist[1];
			if ( $namespace === "Nimitys" ) {
				$expressionsubs[] = $page;			
			} else {
				$hits[] = $namespace.":".$pagename;
			}
		}

		$readmore = '<span>Lue koko artikkeli <img src="/favicon.png"></img></span>';

		$singled = array_unique( $hits );
		global $wgTermbankColors;
		foreach ( $singled as $hit ) {
			$pagetitle = Title::newFromText( $hit );
			$page = SMWDIWikiPage::newFromTitle( $pagetitle );
			$definitionproperty = SMWDIProperty::newFromUserLabel( "Määritelmä" );
			$definitionlangproperty = SMWDIProperty::newFromUserLabel( "Määritelmä_lang" );
			$definitionvalues = $store->getPropertyValues( $page, $definitionproperty );
			$definitionlangvalues = $store->getPropertyValues( $page, $definitionlangproperty );
			$definition = "";
			if ( count( $definitionvalues ) > 0 ) {
				$definition = $definitionvalues[0]->getString()."<br/>";
				$definition = preg_replace( "/\[\[[^\|]*\|/", "", $definition );
				$definition = preg_replace( "/\]\]/", "", $definition );
				$definition = preg_replace("/<ref.*<\/ref>/", "", $definition);
				
			}

			if ( count( $definitionlangvalues ) > 0 ) {
				$definition .= $definitionlangvalues[0]->getString()."<br/>";
				$definition = preg_replace( "/\[\[[^\|]*\|/", "", $definition );
				$definition = preg_replace( "/\]\]/", "", $definition );
				$definition = preg_replace("/<ref.*<\/ref>/", "", $definition);
			}
			
			$pagenamearray = explode(":", $hit);
			$pagename = $pagenamearray[1];
			$namespace = $pagenamearray[0];
			if (array_key_exists($namespace, $wgTermbankColors)) $color = $wgTermbankColors[$namespace];
			else $color = "#0645ad";
				$return .= '<p style="margin-top:2.0em;"><b>'.$pagename.'</b><span style="color:'.$color.'";> ('.$namespace.') </span><i>'.$definition.'</i> <a href="/wiki/'.$namespace.':'.$pagename.'">'.$readmore.'</a></p>';
			
		}

		$return .= '<div style="margin-top:4.0em;">Tarkastele kieliopillista koodausta <span id="exp-click" style="color:white !important">nuolialas</span></div>
<div id="exp-box">';

		$subtables = array();

		foreach ( $expressionsubs as $sub ) {
			$langproperty = SMWDIProperty::newFromUserLabel( "Kieli" );
			$genproperty = SMWDIProperty::newFromUserLabel( "Suku" );
			$posproperty = SMWDIProperty::newFromUserLabel( "Sanaluokka" );
			$formproperty = SMWDIProperty::newFromUserLabel( "Sanamuoto" );
			$derproperty = SMWDIProperty::newFromUserLabel( "Johdostyyppi" );
			$origproperty = SMWDIProperty::newFromUserLabel( "Alkuperä" );
			
			$langs = $store->getPropertyValues( $sub, $langproperty );
			$gens = $store->getPropertyValues( $sub, $genproperty );
			$pos = $store->getPropertyValues( $sub, $posproperty );
			$form = $store->getPropertyValues( $sub, $formproperty );
			$der = $store->getPropertyValues( $sub, $derproperty );
			$orig = $store->getPropertyValues( $sub, $origproperty );
			
			$table = [];
/*
			$table[LANGUAGE] = ExpressionPage::valueArrayToString( $langs );
			$table[POS] = ExpressionPage::valueArrayToString( $pos );
			$table[FORM] = ExpressionPage::valueArrayToString( $form );
			$table[DERIV] = ExpressionPage::valueArrayToString( $der );
			$table[GENUS] = ExpressionPage::valueArrayToString( $gens );
			$table[ORIGIN] = ExpressionPage::valueArrayToString( $orig );
*/
			$table[LANGUAGE] = implode($langs );
			$table[POS] = implode( $pos );
			$table[FORM] = implode( $form );
			$table[DERIV] = implode( $der );
			$table[GENUS] = implode( $gens );
			$table[ORIGIN] = implode( $orig );

			$subtables[] = $table;

		}

		$names = [ "Kieli", "Sanaluokka", "Termityyppi", "Johdostyyppi", "Suku", "Alkuperä" ];

		$return .= '<table class="wikitable">';
		foreach ( range( 0, 5 ) as $index) {
			$return .= '<tr><th>'.$names[$index].'</th>';

			foreach ( $subtables as $table ) {
				$return .= '<td>'.$table[$index].'</td>';
			}

			$return .= '</tr>';
		}
		
		$return .= "</table></div>";

		return $return;
	}

	static function valueArrayToString( $blob ) {
		$return = "";
		if ( count( $blob ) === 0 || !is_object($blob) ) {
			return "";
		} elseif ( $blob[0]->getDIType() === 2 ) {
			$return = $blob[0];
		} elseif ( $blob[0]->getDIType() === 9 ) {
			$return = $blob[0]->getTitle()->getBaseText();
		}

		return $return;
	}
}
