<?php
/**
 * Working groups can have colors and unique fields.
 * Both need some CSS to support the feature.
 *
 * @author Niklas Laxstrom
 * @license GPL-2.0-or-later
 * @file
 */

namespace MediaWiki\Extensions\Termbank;

use ResourceLoaderContext;
use ResourceLoaderModule;

/**
 * Generates CSS dynamically for defined working groups.
 */
class ResourceLoaderTermbankModule extends ResourceLoaderModule {
	public function getStyles( ResourceLoaderContext $context ): array {
		global $wgExtraNamespaces, $wgTermbankColors;
		$output = "\n/* Mui sinulle. */\n";
		$fields = [];
		foreach ( $wgExtraNamespaces as $index => $name ) {
			$lname = strtolower( $name );
			$s = ".ns-$index";
			if ( $index < 1100 || $index % 2 === 1 ) {
				continue;
			}

			$fields[] = "$s .field-$lname";

			if ( isset( $wgTermbankColors[$name] ) ) {
				$color = $wgTermbankColors[$name];
				$output .= <<<CSS
a$s,
a[title=$name],
$s h1,
.page-$name #firstHeading,
$s #firstHeading { color: $color; }

.aihealuelista a[title=$name] { color: $color !important; }

CSS;
			}
		}

		$output .= ".areafield { display: none; }\n";
		# Display working group specific fields per namespace
		$output .= implode( ",\n", $fields ) . " { display: table-row; }\n";
		return [ 'all' => $output ];
	}
}
