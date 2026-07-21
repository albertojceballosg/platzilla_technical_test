<?php
	if (php_sapi_name () !== 'cli') {
		echo 'Sólo ejecutable desde la línea de comandos';
		exit ();
	}

	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set ('display_errors', 1);
	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));

	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/potenciales_clientes/lib/PotentialCustomersInstaller.class.php');

	PotentialCustomersInstaller::getInstance ()->uninstall (AdbManager::getInstance ()->getMasterAdb ());
	echo 'Módulo desinstalado correctamente' . PHP_EOL;
