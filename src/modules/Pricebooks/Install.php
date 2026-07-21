<?php
	if (php_sapi_name () !== 'cli') {
		echo 'Sólo ejecutable desde la línea de comandos';
		exit ();
	}

	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set ('display_errors', 1);
	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));

	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/Pricebooks/lib/PricebooksInstaller.class.php');

	PricebooksInstaller::getInstance ()->install (AdbManager::getInstance ()->getMasterAdb ());
	echo 'Módulo instalado correctamente' . PHP_EOL;
