<?php
/**
 * Working groups can have colors and unique fields.
 * Both need some CSS to support the feature.
 *
 * @author Niklas Laxstrom
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
				$output .= <<<CSS
a$s,
a[title=$name],
$s h1,
.page-$name #firstHeading,
$s #firstHeading { color: $color; }

CSS;
			}
		}

		$output .= ".areafield { display: none; }\n";
		# Display working group specific fields per namespace
		$output .= implode( ",\n", $fields ) . " { display: table-row; }\n";
		return array( 'all' => $output );

	}
}
