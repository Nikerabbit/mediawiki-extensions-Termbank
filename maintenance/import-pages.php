<?php
declare( strict_types = 1 );

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once __DIR__ . '/../src/Maintenance/ImportPages.php';

$maintClass = MediaWiki\Extensions\Termbank\Maintenance\ImportPages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
