<?php

class TermbankHooks {
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__;
		$updater->addExtensionUpdate( [ 'addTable', 'privatedata', "$dir/privatedata.sql", true ] );
	}

	public static function onLinkBegin( $dummy, $target, &$html, &$customAttribs, &$query, &$options, &$ret ) {
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

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setHook( 'expression', 'ExpressionPage::listConcepts' );
	}

	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$att ) {
		$ns = $out->getTitle()->getNamespace();
		$action = $out->getRequest()->getText( 'action', 'view' );
		if ( $ns >= 1100 && $ns % 2 === 0 && $action === 'view' ) {
			$att['class'] .= ' ttp-termpage';
		}
	}
}
