<?php
	require_once ('config.inc.php');
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');

	global $adb, $application_unique_key, $dbconfig, $platPrincipal;

	try {
		$applicationCode  = PlatzillaUtils::purify ($_POST, 'applicationcode');
		$companyName      = PlatzillaUtils::purify ($_POST, 'companyname');
		$firstName        = PlatzillaUtils::purify ($_POST, 'firstname');
		$lastName         = PlatzillaUtils::purify ($_POST, 'lastname');
		$password         = PlatzillaUtils::purify ($_POST, 'password');
		$repeatedPassword = PlatzillaUtils::purify ($_POST, 'repeatedpassword');
		$token            = PlatzillaUtils::purify ($_POST, 'id');

		if (empty ($token)) {
			throw new Exception ('No has suministrado el ID de la invitación');
		}

		$sourceInstanceCode = DataSharingUtils::fetchSourceInstanceCodeByToken ($token);
		if ($sourceInstanceCode === null) {
			// La instancia que indica ese token no está registrada. Lanzar excepción
			throw new Exception ('El enlace suministrado no es válido');
		} else if ($sourceInstanceCode === '') {
			// La solicitud se origina en la madre
			$sourceAdb = AdbManager::getInstance ()->getMasterAdb ();
		} else {
			// La solicitud se origina en una instancia existente
			$sourceAdb = AdbManager::getInstance ()->getSourceInstanceAdb ($sourceInstanceCode);
		}

		// Obtener la solicitud de la base de datos de la instancia
		$request = DataSharingUtils::fetchRequestByToken ($sourceAdb, $token, $sourceInstanceCode);
		if (empty ($request)) {
			throw new Exception ('El enlace suministrado no es válido');
		} else if ($request->getStatus () != DataSharingRequest::STATUS_SENT) {
			throw new Exception ('El enlace suministrado ya ha sido procesado');
		}

		$additionalArguments = array (
			'applicationcode'   => $applicationCode,
			'companyname'       => $companyName,
			'firstname'         => $firstName,
			'lastname'          => $lastName,
			'password'          => $password,
			'platform'          => $platPrincipal,
			'repeatedpassword'  => $repeatedPassword,
			'serverfornewusers' => $dbconfig ['db_serverForNewUsers'],
		);
		$processedRecordIds  = DataSharingManager::getInstance ($sourceAdb)->processRequest ($request, $additionalArguments);
		$targetInstanceCode  = $request->getTargetInstanceCode ();
		$targetAdb           = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceCode);
		NotificationHelper::updateParley ($processedRecordIds, $request->getModuleName (), $sourceAdb, $targetAdb);

		$moduleLabel = Translator::translate ($request->getModuleName (), $request->getModuleName ());
		if (array_key_exists ('platInstancia', $_SESSION)) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => "Se recibió la información de {$moduleLabel} que te invitaron a compartir",
			);
			header ("Location: index.php?module={$request->getModuleName ()}&action=index");
		} else {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MESSAGE', "Se recibió la información. Ahora puedes <a href=\"index.php?module={$request->getModuleName ()}&action=index\">entrar a tu cuenta</a> y verificar que todo está en orden");
			$smarty->assign ('TITLE', 'Listo!');
			$smarty->display ('modules/instancesdatasharing/Message.tpl');
		}
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MESSAGE', "Se ha presentado un error al procesar tu solicitud: {$e->getMessage ()}");
		$smarty->assign ('TITLE', '¡Lo sentimos!');
		$smarty->display ('modules/instancesdatasharing/Message.tpl');
	}
	exit ();
