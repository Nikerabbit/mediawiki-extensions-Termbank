<?php
/**
 * ...
 *
 * @author Antti Kanner
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../..";
}
require_once "$IP/maintenance/Maintenance.php";

class TBDeleteBotList extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'list', '.', true, true );

	}

	public function execute() {
		$filename = $this->getOption( 'list' );
		$fullfile = file_get_contents( $filename );
		$botlist = explode( "\n", $fullfile );
		$user = User::newFromId( 1 );

		foreach ( $botlist as $bot ) {

			$botusertitle = Title::newFromText( $bot );
			$botuser_text = is_object( $botusertitle ) ? $botusertitle->getText() : '';
			$botuser = User::newFromName( $botuser_text );
			echo $bot;
			if ( is_object( $botuser ) ) {
			$botuserID = $botuser->idForName();

			TBDeleteBotList::deleteUser( $botuser, $botuserID, $botuser_text );
			}
			if ( $bot !== "" ) {
			$botuserpagetitle = Title::newFromText( "Käyttäjä:".$bot );
			echo "pagename: ".$botuserpagetitle->getFullText();
			$botpage = WikiPage::factory( $botuserpagetitle );
			if ( $botpage->exists() ) echo "Sivu löytyy ";
			else echo "Sivua ei löydy";
                        if ( version_compare( MW_VERSION, '1.35', '<' ) ) {
				$botpage->doDeleteArticleReal( "Vääränlainen käyttäjätunnus" );
                        } else {
				$botpage->doDeleteArticleReal( "Vääränlainen käyttäjätunnus", $user );
                        }
			if ( $botpage->exists() ) echo "---Sivu löytyy vieläkin\n";
			else echo "---Sivua ei löydy\n";
			}
		}

	}

	private function deleteUser( $objOldUser, $olduserID, $olduser_text ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'user_groups',
			array( 'ug_user' => $olduserID )
		);
		$dbw->delete(
			'user',
			array( 'user_id' => $olduserID )
		);

		wfRunHooks( 'DeleteAccount', array( &$objOldUser ) );

		$users = $dbw->selectField(
			'user',
			'COUNT(*)',
			array()
		);
		$dbw->update( 'site_stats',
			array( 'ss_users' => $users ),
			array( 'ss_row_id' => 1 )
		);
		return true;
	}
}
$maintClass = TBDeleteBotList::class;
require_once( DO_MAINTENANCE );
