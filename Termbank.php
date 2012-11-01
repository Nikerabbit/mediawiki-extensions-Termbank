<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * An extension that provides facilities to tieteentermipankki.fi
 *
 * @file
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */


/**
 * Extension credits properties.
 */
$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'Termbank',
	'version'        => '2011-12-18',
	'author'         => 'Niklas Laxström',
	//'descriptionmsg' => 'termbank-desc',
	//'url'            => 'http://tieteentermipankki.fi',
);

$dir = dirname( __FILE__ ) . '/';


$wgExtensionMessagesFiles['Termbank'] = $dir . 'Termbank.i18n.php';
$wgExtensionMessagesFiles['Termbank-alias'] = $dir . 'Termbank.alias.php';
$wgAutoloadClasses['SpecialPrivateData'] = "$dir/SpecialPrivateData.php";
$wgAutoloadClasses['TemplateParser'] = "$dir/TemplateParser.php";
$wgAutoloadClasses['ResourceLoaderTermbankModule'] = "$dir/ResourceLoaderTermbankModule.php";
$wgSpecialPages['PrivateData'] = 'SpecialPrivateData';
$wgSpecialPageGroups['PrivateData'] = 'wiki';

$resourcePaths = array(
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'Termbank'
);

// Client-side resource modules
$wgResourceModules['ext.termbank'] = array(
	'styles' => 'resources/ext.termbank.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.termbank.privatedata'] = array(
	'styles' => 'resources/ext.termbank.privatedata.css',
	'scripts' => 'resources/ext.termbank.privatedata.js',
	'dependencies' => 'mediawiki.util',
) + $resourcePaths;

$wgResourceModules['ext.termbank.workgroups'] = array(
	'class' => 'ResourceLoaderTermbankModule',
);


$wgHooks['LoadExtensionSchemaUpdates'][] = 'efTermbankSchema';
function efTermbankSchema( $updater ) {
	$dir = dirname( __FILE__ ) . '/';
	$updater->addExtensionUpdate( array( 'addTable', 'privatedata', "$dir/privatedata.sql", true ) );
	return true;
}

$wgHooks['BeforePageDisplay'][] = 'efTermbankModules';
function efTermbankModules( $out, $skin ) {
	$out->addModules( 'ext.termbank' );
	$out->addModules( 'ext.termbank.privatedata' );
	return true;
}

$wgHooks['LinkBegin'][] = 'efTermbankLinkAnnotator';
function  efTermbankLinkAnnotator( $dummy, $target, &$html, &$customAttribs, &$query, &$options, &$ret ) {
	if ( $target && $target->getNamespace() >= 1100 && count( $query ) === 0 ) {
		if ( isset( $customAttribs['class'] ) ) {
			$customAttribs['class'] .= " ns-" . $target->getNamespace();
		} else {
			$customAttribs['class'] = " ns-" . $target->getNamespace();
		}
	}
	return true;
}

$wgHooks['BeforePageDisplay'][] = 'efTermbankCSSGenerator';
function efTermbankCSSGenerator( $out, $skin ) {
	$out->addModules( 'ext.termbank.workgroups' );
	return true;
}

