<?php

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../..";
}
require_once( "$IP/maintenance/Maintenance.php" );
class WishPage extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = '...';
		$this->addOption( 'page', '.', true, true );
		
		
	}

	public function execute() {
	
	$page = $this->getOption( 'page' );
	echo $page;
	$title = Title::newFromText( $page );
	$store = smwfGetStore();
	$pagedata = $store->getSemanticData(SMWDIWikiPage::newFromTitle($title));
	$wished = WishPage::getSemanticValue( $pagedata, 'Toivottu', '0' );
	if ($wished === "" ) {
		$page = new WikiPage( $title );
		$content = $page->getContent();
		$text = $content->getNativeData();
		$text = str_replace( "{{Käsite", "{{Käsite\n|Toivottu=1", $text );
			
	}
	else {
	echo $wished;
	$oldline = 'Toivottu='.$wished;
	echo $oldline;	
	$wished++;
	echo $wished;
	$page = new WikiPage( $title );
	$content = $page->getContent();
	$newline = "Toivottu=".$wished;
	echo $newline;
	$text = $content->getNativeData();
	echo $text;
	$text = str_replace( $oldline, $newline, $text );
	echo $text;
	}
	$page->doEdit($text, "", EDIT_SUPPRESS_RC );
	$page->doPurge();
	$user = User::newFromName( "Vinkkaaja" );
	$revision = Revision::newFromTitle( $title );
	$page->doEditUpdates( $revision, $user );
	}

	static function getSemanticValue($pagedata, $key, $key2=null, $retain=false) {
		$pageproperties = $pagedata->getProperties();
		$returnstring = "";
		if (array_key_exists($key, $pageproperties)) {
			if ($retain === false) {
				$propertyvalues = $pagedata->getPropertyValues($pageproperties[$key]);
				foreach ($propertyvalues as $tempkey=>$propertyvalue) {			
					if ($propertyvalue->getDIType() === 1) $propertyvalues[$tempkey] = $propertyvalue->getNumber();
					if ($propertyvalue->getDIType() === 2) $propertyvalues[$tempkey] = $propertyvalue->getString();
					if ($propertyvalue->getDIType() === 9) $propertyvalues[$tempkey] = $propertyvalue->getTitle()->__toString();
					if ($propertyvalue->getDIType() === 4) $propertyvalues[$tempkey] = $propertyvalue->getBoolean();
				}
			}
			if ($key2 !== null) $returnstring = $propertyvalues[$key2];
			else $returnstring = $propertyvalues;
		}
		return $returnstring;
	}

}
$maintClass = 'WishPage';
require_once( DO_MAINTENANCE );
