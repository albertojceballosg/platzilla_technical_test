<?php
	if (php_sapi_name () !== 'cli') {
		echo 'Sólo ejecutable desde la línea de comandos';
		exit ();
	}

	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/rssnews/lib/RssNewsInstaller.class.php');

	RssNewsInstaller::getInstance ()->uninstall (AdbManager::getInstance ()->getMasterAdb ());
	echo 'Módulo desinstalado correctamente' . PHP_EOL;
