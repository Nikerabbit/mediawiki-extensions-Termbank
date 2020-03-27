<?php
/**
 * ...
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

class SpecialPrivateData extends SpecialPage {
	function __construct() {
		parent::__construct( 'PrivateData' );
	}

	public function isListed() {
		return false;
	}

	public function execute( $parameters ) {
		$this->setHeaders();

		if ( $parameters === null ) {
			throw new PermissionsError();
		}

		$this->getOutput()->disable();

		$title = Title::newFromText( $parameters );
		if ( !$title || !$title->exists() ) {
			return;
		}

		global $wgNamespaceProtection;
		$namespace = $title->getNamespace();
		$user = $this->getUser();

		$services = MediaWikiServices::getInstance();
		if ( is_callable( [ $services, 'getPermissionManager' ] ) ) {
			if ( !$services->getPermissionManager()->userCan( 'edit', $user, $title ) ) {
				return;
			}
		} else {
			// BC <1.33
			if ( !$title->userCan( 'edit' ) ) {
				return;
			}
		}

		if ( isset( $wgNamespaceProtection[$namespace] ) ) {
			foreach ( $wgNamespaceProtection[$namespace] as $right ) {
				if ( !$user->isAllowed( $right ) ) {
					return;
				}
			}
		}

		$db = wfGetDB( DB_SLAVE );
		$table = 'privatedata';
		$fields = 'pd_text';
		$conds = [ 'pd_page' => $title->getArticleId() ];
		$res = $db->selectRow( $table, $fields, $conds, __METHOD__ );
		if ( $res ) {
			$msg = $this->msg( 'termbank-privatedata-note' )->parse();
			$text = "<em>$msg</em><hr />";
			$text .= self::convertWhiteSpaceToHTML( $res->pd_text );
			echo Html::rawElement( 'div', array( 'class' => 'ttp-privatedata' ), $text );
		}
		return;
	}

	public static function convertWhiteSpaceToHTML( $msg ) {
		$msg = htmlspecialchars( $msg );
		$msg = preg_replace( '/^ /m', '&#160;', $msg );
		$msg = preg_replace( '/ $/m', '&#160;', $msg );
		$msg = preg_replace( '/ /',  '&#160; ', $msg );
		$msg = str_replace( "\n", '<br />', $msg );

		return $msg;
	}
}
