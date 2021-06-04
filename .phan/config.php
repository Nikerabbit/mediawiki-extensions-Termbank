<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['file_list'] = array_merge(
	$cfg['file_list'],
	/** @phpcs-require-sorted-array */
	[
		'importNotes.php',
		'importPages.php',
	]
);

return $cfg;
