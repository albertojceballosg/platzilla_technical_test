<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifymanager/lib/NotifyManagerUtils.class.php');

	$notificationId = PlatzillaUtils::purify ($_POST, 'record');

	NotifyManagerUtils::disableNotification ($_SESSION ['platInstancia'], $notificationId);
	exit ();
