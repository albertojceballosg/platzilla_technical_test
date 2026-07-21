<?php
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/boxscore/boxscore.php');

	global $adb;
	if(isset($_REQUEST ['recordop'])) {
		$recordId = vtlib_purify($_REQUEST['recordop']);
	} else{
		$recordId = null;
	}

	if ($recordId) {
		$bs = new box_score ();
		$bs->deleteCalculation ($recordId);
		echo 'delete_on';
	} else {
		echo 'no_redord';
	}
