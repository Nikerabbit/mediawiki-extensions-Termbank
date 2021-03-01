<?php

namespace MediaWiki\Extensions\Termbank;

use ApiBase;
use DatabaseUpdater;
use OutputPage;
use Parser;
use Skin;

class Hooks {
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__;
		$updater->addExtensionUpdate( [ 'addTable', 'privatedata', "$dir/privatedata.sql", true ] );
	}

	public static function onHtmlPageLinkRendererEnd(
		$linkRenderer, $target, $isKnown, &$text, &$attribs, &$ret
	) {
		global $wgExtraNamespaces;

		if ( $query || $target->isExternal() ) {
			return;
		}

		$topicPages = array_flip( $wgExtraNamespaces );

		if ( $target->getNamespace() >= 1100 ) {
			$attribs['class'] .= ' ns-' . $target->getNamespace();
		} elseif ( $target->inNamespace( NS_MAIN ) && isset( $topicPages[$target->getDBkey()] ) ) {
			$attribs['class'] .= ' ns-' . $topicPages[$target->getDBkey()];
		}
	}

	public static function onBeforePageDisplay( OutputPage $out ) {
		$out->addModuleStyles( 'ext.termbank.styles' );
		$out->addModules( 'ext.termbank' );
		$out->addModuleStyles( 'ext.termbank.workgroups' );
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

	public static function onAPIGetAllowedParams( ApiBase $module, array &$params ) {
		// Termbank has over 50 content namespaces, which breaks the search box
		if ( $module->getModuleName() === 'opensearch' ) {
			$params['namespace'][ApiBase::PARAM_ISMULTI_LIMIT1] = ApiBase::LIMIT_BIG1;
			$params['namespace'][ApiBase::PARAM_ISMULTI_LIMIT2] = ApiBase::LIMIT_BIG1;
		}
	}
}
