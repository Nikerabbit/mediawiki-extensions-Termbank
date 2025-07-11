<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank;

use Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use SpecialPage;
use Title;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class SpecialPrivateData extends SpecialPage {
	private readonly PermissionManager $permissionManager;
	private readonly ILoadBalancer $loadBalancer;

	public function __construct() {
		parent::__construct( 'PrivateData' );

		$services = MediaWikiServices::getInstance();
		$this->permissionManager = $services->getPermissionManager();
		$this->loadBalancer = $services->getDBLoadBalancer();
	}

	/** @inheritDoc */
	public function isListed(): bool {
		return false;
	}

	/** @inheritDoc */
	public function execute( $parameters ): void {
		$this->setHeaders();

		if ( $parameters === null ) {
			return;
		}

		$this->getOutput()->disable();

		$title = Title::newFromText( $parameters );
		if ( !$title || !$title->exists() ) {
			return;
		}

		$user = $this->getUser();

		if ( !$this->permissionManager->userCan(
			'edit',
			$user,
			$title,
			PermissionManager::RIGOR_FULL
		) ) {
			return;
		}

		$db = $this->loadBalancer->getConnectionRef( DB_REPLICA );
		$table = 'privatedata';
		$fields = 'pd_text';
		$conds = [ 'pd_page' => $title->getArticleId() ];
		$res = $db->selectRow( $table, $fields, $conds, __METHOD__ );
		if ( $res ) {
			$msg = $this->msg( 'termbank-privatedata-note' )->parse();
			$text = "<em>$msg</em><hr />";
			$text .= self::convertWhiteSpaceToHTML( $res->pd_text );
			echo Html::rawElement( 'div', [ 'class' => 'ttp-privatedata' ], $text );
		}
	}

	private static function convertWhiteSpaceToHTML( string $msg ): string {
		$msg = htmlspecialchars( $msg );
		$msg = preg_replace( '/^ /m', '&#160;', $msg );
		$msg = preg_replace( '/ $/m', '&#160;', $msg );
		$msg = preg_replace( '/ /', '&#160; ', $msg );
		$msg = str_replace( "\n", '<br />', $msg );

		return $msg;
	}
}
