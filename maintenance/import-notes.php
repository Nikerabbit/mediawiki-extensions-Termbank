<?php

declare( strict_types = 1 );

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";
require_once __DIR__ . '/../src/Maintenance/ImportNotes.php';

$maintClass = MediaWiki\Extensions\Termbank\Maintenance\ImportNotes::class;
require_once RUN_MAINTENANCE_IF_MAIN;
