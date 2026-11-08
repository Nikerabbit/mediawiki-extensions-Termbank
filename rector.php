<?php

declare( strict_types = 1 );

use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;

return RectorConfig::configure()
	->withPaths( [
		__DIR__ . '/src',
		__DIR__ . '/maintenance',
		__DIR__ . '/Termbank.alias.php',
	] )
	->withPhpSets()
	->withComposerBased()
	->withSkip( [
		// Keep consecutive class member declarations together.
		NewlineBetweenClassLikeStmtsRector::class,
		// Hook interface array parameters must remain untyped.
		StrictArrayParamDimFetchRector::class => [ __DIR__ . '/src/Hooks.php' ],
	] )
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		codingStyle: true,
		typeDeclarations: true,
		privatization: true,
	);
