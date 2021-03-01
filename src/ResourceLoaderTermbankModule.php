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
			$displayName = strtr( $name, '_', ' ' );
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
body$s h1,
body$s #firstHeading,
.page-$name #firstHeading,
.aihealuelista a[title="$displayName"] { color: $color; }


CSS;
			}
		}

		$output .= ".areafield { display: none; }\n";
		# Display working group specific fields per namespace
		$output .= implode( ",\n", $fields ) . " { display: table-row; }\n";
		return [ 'all' => $output ];
	}

	/** @inheritDoc */
	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}
}
