<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');

	global $current_language;

	$token            = PlatzillaUtils::purify ($_POST, 'token');
	$email            = PlatzillaUtils::purify ($_POST, 'email');
	$password         = PlatzillaUtils::purify ($_POST, 'password');
	$repeatedPassword = PlatzillaUtils::purify ($_POST, 'repeatpassword');

	try {
		if (empty ($email)) {
			throw new Exception ('E0001');
		}
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$result    = $masterAdb->pquery ('SELECT * FROM vtiger_instances WHERE administrator=?', array ($email));
		if ((!$result) || ($masterAdb->num_rows ($result) == 0)) {
			throw new Exception ('E0002');
		}
		$instance = $masterAdb->fetchByAssoc ($result, -1, false);

		if (empty ($token)) {
			BackgroundTasksRunner::getInstance ($masterAdb, $_SESSION ['plat'])->runManuallyTriggeredTask ('[SYS] - Enviar credenciales de acceso', $instance ['instanceid']);
			$status = 'S0001';
		} else {
			if (empty ($password)) {
				throw new Exception ('E0003');
			}
			if ((empty ($repeatedPassword)) || ($password != $repeatedPassword)) {
				throw new Exception ('E0004');
			}

			$userHash          = strtolower (md5 ($password));
			$cryptType         = version_compare (PHP_VERSION, '5.3.0') >= 0 ? 'PHP5.3MD5' : 'MD5';
			$dummy             = substr ($email, 0, 2);
			$salt              = $cryptType == 'PHP5.3MD5' ? '$1$' . str_pad ($dummy, 9, '0') : "$1${$salt}$";
			$encryptedPassword = crypt ($password, $salt);
			$targetAdb         = AdbManager::getInstance ()->getTargetInstanceAdb ($instance ['code']);
			$targetAdb->connect (false);
			$result = $targetAdb->pquery (
				'UPDATE vtiger_users SET user_password=?, confirm_password=?, user_hash=?, crypt_type=? WHERE user_name=?',
				array ($encryptedPassword, $encryptedPassword, $userHash, $cryptType, $email)
			);
			if (!$result) {
				throw new Exception ('E0005');
			}

			$status = 'S0002';
		}
	} catch (Exception $e) {
		$status = $e->getMessage ();
	}
	header ("Location: reset-password-message.php?status={$status}");
	exit ();
