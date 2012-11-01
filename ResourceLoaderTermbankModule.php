<?php
/**
 * Working groups can have colors and unique fields.
 * Both need some CSS to support the feature.
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2012, Niklas Laxström
 * @license Public domain
 * @file
 */

/**
 * Generates CSS dynamically for defined working groups.
 */
class ResourceLoaderTermbankModule extends ResourceLoaderModule {
	/// Same for all users.
	protected $origin = self::ORIGIN_CORE_SITEWIDE;

	/**
	 * @param $context ResourceLoaderContext
	 * @return array|int|Mixed
	 */
	public function getModifiedTime( ResourceLoaderContext $context ) {
		global $wgCacheEpoch;
		return $wgCacheEpoch;
	}

	/**
	 * Load at top to avoid flash of the page.
	 */
	public function getPosition() {
		return 'top';
	}
	
	/**
	 * @param $context ResourceLoaderContext
	 * @return array
	 */
	public function getStyles( ResourceLoaderContext $context ) {

		global $wgExtraNamespaces, $wgTermbankColors;
		$output = "\n/* Mui sinulle. */\n";
		$fields = array();
		foreach ( $wgExtraNamespaces as $index => $name ) {
			$lname = strtolower( $name );
			$s = ".ns-$index";
			if ( $index < 1100 || $index % 2 === 1 ) continue;

			$fields[] = "$s .field-$lname";
			
			if ( isset( $wgTermbankColors[$name] ) ) {
				$color = $wgTermbankColors[$name];
				$output .= "a$s, a[title=$name], $s h1, $s #firstHeading { color: $color; }\n";
			}
		}

		$output .= ".areafield { display: none; }\n";
		$output .= implode( ', ', $fields ) . " { display: table-row; }\n";
		return array( 'all' => $output );

	}
}