<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank;

use ApiBase;
use MediaWiki\Api\Hook\APIGetAllowedParamsHook;
use MediaWiki\Hook\OutputPageBodyAttributesHook;
use MediaWiki\Hook\ParserBeforeInternalParseHook;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MediaWiki\Title\Title;
use OutputPage;
use Override;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * @author Niklas Laxstrom
 * @license GPL-2.0-or-later
 */
class Hooks implements
	LoadExtensionSchemaUpdatesHook,
	OutputPageBodyAttributesHook,
	ParserBeforeInternalParseHook,
	APIGetAllowedParamsHook
{
	/** @inheritDoc */
	public function onLoadExtensionSchemaUpdates( $updater ): void {
		$dir = __DIR__;
		$updater->addExtensionUpdate( [ 'addTable', 'privatedata', "$dir/privatedata.sql", true ] );
	}

	/** @inheritDoc */
	public function onBeforePageDisplay( OutputPage $out ): void {
		$out->addModuleStyles( 'ext.termbank.styles' );
		$out->addModules( 'ext.termbank' );
		$out->addModuleStyles( 'ext.termbank.workgroups' );
	}

	/** @inheritDoc */
	public function onOutputPageBodyAttributes( $out, $skin, &$att ): void {
		$ns = $out->getTitle()->getNamespace();
		$action = $out->getRequest()->getText( 'action', 'view' );
		if ( $ns >= 1100 && $ns % 2 === 0 && $action === 'view' ) {
			$att['class'] .= ' ttp-termpage';
		}
	}

	/** @inheritDoc */
	public function onParserBeforeInternalParse( $parser, &$text, $stripState ): void {
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

	/** @inheritDoc */
	public function onAPIGetAllowedParams( $module, &$params, $flags ): void {
		// Termbank has over 50 content namespaces, which breaks the search box
		if ( $module->getModuleName() === 'opensearch' ) {
			$params['namespace'][ParamValidator::PARAM_ISMULTI_LIMIT1] = ApiBase::LIMIT_BIG1;
			$params['namespace'][ParamValidator::PARAM_ISMULTI_LIMIT2] = ApiBase::LIMIT_BIG1;
		}
	}
}
