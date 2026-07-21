<?php
	global $currentModule;
	$plat = isset ($_SESSION ['plat']) ? $_SESSION ['plat'] : '';

	if (file_exists ("{$plat}/modules/{$currentModule}/ListView.php")) {
		checkFileAccessForInclusion ("{$plat}/modules/{$currentModule}/ListView.php");
		require_once ("{$plat}/modules/{$currentModule}/ListView.php");
	} else {
		checkFileAccessForInclusion ("modules/{$currentModule}/ListView.php");
		require_once ("modules/{$currentModule}/ListView.php");
	}
