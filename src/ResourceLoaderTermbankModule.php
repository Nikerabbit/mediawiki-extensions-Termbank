<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Termbank;

use ResourceLoaderContext;
use ResourceLoaderModule;

/**
 * Generates CSS dynamically for defined working groups.
 * @author Niklas Laxstrom
 * @license GPL-2.0-or-later
 */
class ResourceLoaderTermbankModule extends ResourceLoaderModule {
	public function getStyles( ResourceLoaderContext $context ): array {
		global $wgExtraNamespaces;
		$output = "\n/* Mui sinulle. */\n";
		$fields = [];
		foreach ( $wgExtraNamespaces as $index => $name ) {
			$lname = strtolower( $name );
			$s = ".ns-$index";
			if ( $index < 1100 || $index % 2 === 1 ) {
				continue;
			}

			$fields[] = "$s .field-$lname";
		}

		$output .= ".areafield { display: none; }\n";
		# Display working group specific fields per namespace
		$output .= implode( ",\n", $fields ) . " { display: table-row; }\n";
		return [ 'all' => $output ];
	}

	/** @inheritDoc */
	public function getTargets(): array {
		return [ 'desktop', 'mobile' ];
	}

	/** @inheritDoc */
	public function getType(): string {
		return ResourceLoaderModule::LOAD_STYLES;
	}
}
