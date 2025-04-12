<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank;

use ApiBase;
use DatabaseUpdater;
use MediaWiki\Title\Title;
use OutputPage;
use Parser;
use Skin;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * @author Niklas Laxstrom
 * @license GPL-2.0-or-later
 */
class Hooks {
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ): void {
		$dir = __DIR__;
		$updater->addExtensionUpdate( [ 'addTable', 'privatedata', "$dir/privatedata.sql", true ] );
	}

	public static function onBeforePageDisplay( OutputPage $out ): void {
		$out->addModuleStyles( 'ext.termbank.styles' );
		$out->addModules( 'ext.termbank' );
		$out->addModuleStyles( 'ext.termbank.workgroups' );
	}

	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$att ): void {
		$ns = $out->getTitle()->getNamespace();
		$action = $out->getRequest()->getText( 'action', 'view' );
		if ( $ns >= 1100 && $ns % 2 === 0 && $action === 'view' ) {
			$att['class'] .= ' ttp-termpage';
		}
	}

	public static function onParserBeforeInternalParse( Parser $parser, &$text ): void {
		$title = Title::castFromPageReference( $parser->getPage() );

		if (
			( $title && $title->getNsText() !== 'Nimitys' )
			|| !str_contains( $text, '{{Nimityssivu}}' )
		) {
			return;
		}

		$text .= <<<WIKITEXT
<div class="navigation-not-searchable">
== Alaviite ==
{{int:ttp-page-concept-referthispage}}:<br>
''{{int:ttp-page-concept-wikiname}} {{CURRENTDAY}}.{{CURRENTMONTH}}.{{CURRENTYEAR}}: {{FULLPAGENAME}}.
({{int:ttp-page-concept-wikiaddress}}: <nowiki>https://www.tieteentermipankki.fi/wiki/</nowiki>{{FULLPAGENAME}}.)''
</div>
WIKITEXT;
	}

	public static function onAPIGetAllowedParams( ApiBase $module, array &$params ): void {
		// Termbank has over 50 content namespaces, which breaks the search box
		if ( $module->getModuleName() === 'opensearch' ) {
			$params['namespace'][ParamValidator::PARAM_ISMULTI_LIMIT1] = ApiBase::LIMIT_BIG1;
			$params['namespace'][ParamValidator::PARAM_ISMULTI_LIMIT2] = ApiBase::LIMIT_BIG1;
		}
	}
}
