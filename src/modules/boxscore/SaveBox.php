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

	if(isset($_REQUEST['box_score'])) {
		$boxScore = vtlib_purify ($_REQUEST ['box_score']);
	} else{
		$boxScore = null;
	}

	if(isset($_REQUEST['boxscoreid'])) {
		$boxScoreId = vtlib_purify($_REQUEST['boxscoreid']);
	} else{
		$boxScoreId = null;
	}

	if(isset($_REQUEST['fecha_desde'])) {
		$from = vtlib_purify($_REQUEST['fecha_desde']);
	} else{
		$from = null;
	}

	if(isset($_REQUEST['monthsearch'])) {
		$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
	} else{
		$monthSearch = null;
	}

	if(isset($_REQUEST['submit'])) {
		$submit = vtlib_purify($_REQUEST['submit']);
	} else{
		$submit = null;
	}

	if(isset($_REQUEST['fecha_hasta'])) {
		$to = vtlib_purify($_REQUEST['fecha_hasta']);
	} else{
		$to = null;
	}

	if(isset($_REQUEST['submit-boxscore'])) {
		$update = vtlib_purify($_REQUEST['submit-boxscore']);
	} else{
		$update = null;
	}


	$bs = new box_score ();
	if ($update) {
		$bs->update ($_REQUEST);
	}
	if (($submit) && (!empty ($boxScore))) {
		$bs->add ($_REQUEST);
	}
	if (($from) && ($to)) {
		$addUrl = "&fecha_desde={$from}&fecha_hasta={$to}";
	} else {
		$addUrl = '';
	}
	header ("Location: index.php?module=boxscore&action=DetailView&record={$boxScoreId}&monthsearch={$monthSearch}{$addUrl}");
