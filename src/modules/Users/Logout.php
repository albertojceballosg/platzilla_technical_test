<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/Users/LoginHistory.php');

	global $current_user;

	$usip       = $_SERVER ['REMOTE_ADDR'];
	$outtime    = date ("Y/m/d H:i:s");
	$loghistory = new LoginHistory ();
	$loghistory->user_logout ($current_user->user_name, $usip, $outtime);

	if (isset ($_GET ['impersonationtoken'])) {
		$impersonationToken = vtlib_purify ($_GET ['impersonationtoken']);
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		PlatformManager::getInstance ($masterAdb)->deleteInstanceTemporaryAdmin ($impersonationToken);
	}
	unset ($_SESSION);
	session_destroy ();
	header ('Location: index.php?action=Login&module=Users');
