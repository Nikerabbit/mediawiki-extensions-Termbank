<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * An extension that provides facilities to tieteentermipankki.fi
 *
 * @file
 * @ingroup Extensions
 *
 * @author Siebrand Mazeland
 * @copyright Copyright © 2011
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */


/**
 * Extension credits properties.
 */
$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'Termbank',
	'version'        => '2001-12-18',
	'author'         => 'Niklas Laxström',
	//'descriptionmsg' => 'termbank-desc',
	//'url'            => 'http://tieteentermipankki.fi',
);

$dir = dirname( __FILE__ ) . '/';


$wgExtensionMessagesFiles['Termbank'] = $dir . 'Termbank.i18n.php';
$wgExtensionMessagesFiles['Termbank-alias'] = $dir . 'Termbank.alias.php';
$wgAutoloadClasses['SpecialPrivateData'] = "$dir/SpecialPrivateData.php";
$wgSpecialPages['PrivateData'] = 'SpecialPrivateData';
$wgSpecialPageGroups['PrivateData'] = 'wiki';

$resourcePaths = array(
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'Termbank'
);

// Client-side resource modules
$wgResourceModules['ext.termbank.privatedata'] = array(
	'styles' => 'resources/ext.termbank.privatedata.css',
	'scripts' => 'resources/ext.termbank.privatedata.js',
	'dependencies' => 'mediawiki.util',
) + $resourcePaths;


$wgHooks['LoadExtensionSchemaUpdates'][] = 'efTermbankSchema';
function efTermbankSchema( $updater ) {
	$dir = dirname( __FILE__ ) . '/';
	$updater->addExtensionUpdate( array( 'addTable', 'privatedata', "$dir/privatedata.sql", true ) );
	return true;
}

$wgHooks['BeforePageDisplay'][] = 'efTermbankModules';
function efTermbankModules( $out, $skin ) {
	$out->addModules( 'ext.termbank.privatedata' );
	return true;
}
