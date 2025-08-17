<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank;

use MediaWiki\Html\Html;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use Override;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class SpecialPrivateData extends SpecialPage {
	public function __construct(
		private readonly PermissionManager $permissionManager,
		private readonly IConnectionProvider $connectionProvider,
	) {
		parent::__construct( 'PrivateData' );
	}

	#[Override]
	public function isListed(): bool {
		return false;
	}

	#[Override]
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

		$db = $this->connectionProvider->getReplicaDatabase();
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
		return strtr(
			htmlspecialchars( $msg ),
			[
				' ' => '&#160;',
				"\n" => '<br />'
			]
		);
	}
}
