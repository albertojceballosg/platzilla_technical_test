<?php
	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set ('display_errors', 1);
	set_time_limit (0);
	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));
	require_once ('include/utils/InstanceCreator.class.php');

	try {
		$now = date_create ();
		$instanceID = InstanceCreator::getCreator ()->createStockInstance ();
		echo "Instancia $instanceID ha sido creada en {$now->diff (date_create ())->format ('%I min %S seg')}" . PHP_EOL;
	} catch (Exception $e) {
		echo $e->getMessage () . PHP_EOL;
		echo $e->getTraceAsString ();
	}
