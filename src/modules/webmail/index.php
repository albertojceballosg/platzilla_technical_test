<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/RoundCubeAutoLogin.class.php');

	global $adb, $currentModule, $current_user, $site_URL;

	require_once ('config.inc.php');

	$mailAction = PlatzillaUtils::purify ($_REQUEST, '_action');
	$mailbox    = PlatzillaUtils::purify ($_REQUEST, '_mbox');
	$messageUid = PlatzillaUtils::purify ($_REQUEST, '_uid');
	$task       = PlatzillaUtils::purify ($_REQUEST, '_task');
	$to       = PlatzillaUtils::purify ($_REQUEST, '_to');

	try {
		$result = $adb->pquery (
			'SELECT
				ue.*,
				ep.*
			FROM
				vtiger_webmail_users ue
				INNER JOIN vtiger_webmail_providers ep ON ep.name=ue.provider
			WHERE
				ue.userid=?',
			array ($current_user->id)
		);

		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			throw new Exception ('No tienes cuentas de correo asociadas a Platzilla');
		}

		$row       = $adb->fetchByAssoc ($result, -1, false);
		$siteUrl   = rtrim ($site_URL, '/');
		$arguments = array (
			'user'     => array (
				'email'    => $row ['email'],
				'fullname' => $row ['fullname'],
				'password' => $row ['password'],
				'username' => $row ['username'],
			),
			'incoming' => array (
				'protocol'             => $row ['incomingprotocol'],
				'hostname'             => $row ['incominghostname'],
				'port'                 => $row ['incomingport'],
				'securitytype'         => $row ['incomingsecuritytype'],
				'authenticationmethod' => $row ['incomingauthenticationmethod'],
			),
			'outgoing' => array (
				'protocol'             => $row ['outgoingprotocol'],
				'hostname'             => $row ['outgoinghostname'],
				'port'                 => $row ['outgoingport'],
				'securitytype'         => $row ['outgoingsecuritytype'],
				'authenticationmethod' => $row ['outgoingauthenticationmethod'],
			),
		);
		$rc        = new RoundCubeAutoLogin ("{$siteUrl}/{$currentModule}");
		$cookies   = $rc->login ($arguments);
		foreach ($cookies as $name => $value) {
			setcookie ($name, $value, 0, '/', '');
		}

		$queryString = array ();
		if (!empty ($task)) {
			$queryString [] = "_task={$task}";
		}
		if (!empty ($mailAction)) {
			$queryString [] = "_action={$mailAction}";
		}
		if ($mailAction == 'show') {
			if (!empty ($mailbox)) {
				$queryString [] = "_mbox={$mailbox}";
			}
			if (!empty ($messageUid)) {
				$queryString [] = "_uid={$messageUid}";
			}
		} else if ($mailAction == 'compose') {
			if (!empty ($to)) {
				$queryString [] = "_to={$to}";
			}
		}
		$queryString = !empty ($queryString) ? join ('&', $queryString) : '';
		$rc->redirect ($queryString);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ('Location: index.php?module=Home&action=index');
	}
