<?php

namespace MediaWiki\Extensions\Termbank;

use DatabaseUpdater;
use OutputPage;
use Parser;
use Skin;

class Hooks {
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__;
		$updater->addExtensionUpdate( [ 'addTable', 'privatedata', "$dir/privatedata.sql", true ] );
	}

	public static function onLinkBegin(
		$dummy,
		$target,
		&$html,
		&$customAttribs,
		$query,
		&$options,
		&$ret
	) {
		if ( $target && $target->getNamespace() >= 1100 && count( $query ) === 0 ) {
			if ( isset( $customAttribs['class'] ) ) {
				$customAttribs['class'] .= " ns-" . $target->getNamespace();
			} else {
				$customAttribs['class'] = " ns-" . $target->getNamespace();
			}
		}
	}

	public static function onBeforePageDisplay( OutputPage $out ) {
		$out->addModuleStyles( 'ext.termbank' );
		$out->addModules( 'ext.termbank.privatedata' );
		$out->addModules( 'ext.termbank.workgroups' );
	}

	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$att ) {
		$ns = $out->getTitle()->getNamespace();
		$action = $out->getRequest()->getText( 'action', 'view' );
		if ( $ns >= 1100 && $ns % 2 === 0 && $action === 'view' ) {
			$att['class'] .= ' ttp-termpage';
		}
	}

	public static function onParserBeforeInternalParse( Parser $parser, &$text ) {
		if (
			   $parser->getTitle()->getNsText() !== 'Nimitys'
			|| strpos( $text, '{{Nimityssivu}}' ) === false
		) {
			return;
		}

		$text .= <<<WIKITEXT
== Alaviite ==
{{int:ttp-page-concept-referthispage}}:<br>
''{{int:ttp-page-concept-wikiname}} {{CURRENTDAY}}.{{CURRENTMONTH}}.{{CURRENTYEAR}}: {{FULLPAGENAME}}.
({{int:ttp-page-concept-wikiaddress}}: <nowiki>https://www.tieteentermipankki.fi/wiki/</nowiki>{{FULLPAGENAME}}.)''
WIKITEXT;
	}
}
