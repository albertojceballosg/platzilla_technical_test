<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	if (file_exists ($_SESSION ['plat'] . "/modules/{$currentModule}/{$currentModule}.php")) {
		checkFileAccessForInclusion ($_SESSION ['plat'] . "/modules/{$currentModule}/{$currentModule}.php");
		require_once ($_SESSION ['plat'] . "/modules/{$currentModule}/{$currentModule}.php");
	} else {
		checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
		require_once ("modules/{$currentModule}/{$currentModule}.php");
	}

	if(isset($_REQUEST['colorbase'])) {
		$baseColor = vtlib_purify($_REQUEST['colorbase']);
	} else {
		$baseColor = null;
	}

	if(isset($_REQUEST['colordegrade'])) {
		$degradeeColor = vtlib_purify($_REQUEST['colordegrade']);
	} else{
		$degradeeColor = null;
	}

	$bs      = new box_score ();
	$blockId = $bs->saveBlock ($baseColor, $degradeeColor);
	if ($blockId > 0) {
		echo 'success';
	} else {
		echo "error - {$blockId}";
	}
