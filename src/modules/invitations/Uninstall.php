<?php
	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/invitations/lib/InvitationsInstaller.class.php');

	InvitationsInstaller::getInstance ()->uninstall (AdbManager::getInstance ()->getMasterAdb ());
	echo '<html><head><meta charset="utf-8" /></head><body><h1>Módulo desinstalado correctamente</h1></body></html>';
