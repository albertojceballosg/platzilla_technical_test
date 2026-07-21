<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	$token  = PlatzillaUtils::purify ($_GET, 'id');
	$smarty = new vtigerCRM_Smarty ();
	try {
		if (empty ($token)) {
			throw new Exception ('No has suministrado el ID de la invitación');
		}

		// Obtener el código de la instancia que origina la solicitud, y una conexión a su base de datos
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

		$sender = $request->getCreatedBy ();
		$recipientAddress = $request->getRecipientAddress ();
		// Obtener la instancia en la cual está registrada la dirección de correo del receptor de la solicitud
		$targetInstanceCode = DataSharingUtils::fetchUserInstanceCode ($request->getRecipientAddress ());
		if (empty ($targetInstanceCode)) {
			if (isset ($_SESSION ['platInstancia'])) {
				throw new Exception ('Por favor cierra todas las sesiones abiertas de Platzilla y refresca esta página');
			}
			// El receptor de la solicitud no tiene instancia asignada. Solicitar los datos necesarios para crearle una y el código de la aplicación que se le instalará
			$smarty->assign ('AVAILABLE_APPLICATIONS', DataSharingUtils::fetchAvailableApplicationsByModuleName ($request->getModuleName ()));
			$smarty->assign ('COMMENTS', $request->getComments ());
			$smarty->assign ('MODULE_LABEL', Translator::translate ($request->getModuleName (), $request->getModuleName ()));
			$smarty->assign ('SENDER_FULL_NAME', trim ("{$sender->getFirstName ()} {$sender->getLastName ()}"));
			$smarty->assign ('TOKEN', $token);
			$smarty->assign ('USER_NAME', $recipientAddress);
			$smarty->display ('modules/instancesdatasharing/RequestCreateInstance.tpl');
		} else {
			// El receptor de la solicitud tiene instancia asignada. Revisar si tiene el módulo instalado y activo
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceCode);
			if (!DataSharingUtils::isModuleActive ($targetAdb, $request->getModuleName ())) {
				if (isset ($_SESSION ['platInstancia'])) {
					throw new Exception ('Por favor cierra todas las sesiones abiertas de Platzilla y refresca esta página');
				}
				// El receptor de la solicitud no tiene instalada ninguna aplicación con ese módulo. Solicitar cuál aplicación quiere instalar
				$smarty->assign ('AVAILABLE_APPLICATIONS', DataSharingUtils::fetchAvailableApplicationsByModuleName ($request->getModuleName ()));
				$smarty->assign ('COMMENTS', $request->getComments ());
				$smarty->assign ('MODULE_LABEL', Translator::translate ($request->getModuleName (), $request->getModuleName ()));
				$smarty->assign ('MODULE_NAME', $request->getModuleName ());
				$smarty->assign ('RECORD_IDS', $request->getRecordIds ());
				$smarty->assign ('SENDER_FULL_NAME', trim ("{$sender->getFirstName ()} {$sender->getLastName ()}"));
				$smarty->assign ('SOURCE_INSTANCE_CODE', $sourceInstanceCode);
				$smarty->assign ('TARGET_INSTANCE_CODE', $targetInstanceCode);
				$smarty->assign ('TOKEN', $token);
				$smarty->display ('modules/instancesdatasharing/RequestInstallApplication.tpl');
			} else {
				if ((!empty ($_SESSION ['platInstancia'])) && ($_SESSION ['platInstancia'] != $targetInstanceCode)) {
					throw new Exception ('Por favor cierra todas las sesiones abiertas de Platzilla y empieza de nuevo');
				}
				// El receptor de la solicitud tiene instalada alguna aplicación con ese módulo. Solicitar confirmación para compartir los registros
				$smarty->assign ('COMMENTS', $request->getComments ());
				$smarty->assign ('MODULE_LABEL', Translator::translate ($request->getModuleName (), $request->getModuleName ()));
				$smarty->assign ('MODULE_NAME', $request->getModuleName ());
				$smarty->assign ('RECORD_IDS', $request->getRecordIds ());
				$smarty->assign ('SENDER_FULL_NAME', trim ("{$sender->getFirstName ()} {$sender->getLastName ()}"));
				$smarty->assign ('SOURCE_INSTANCE_CODE', $sourceInstanceCode);
				$smarty->assign ('TARGET_INSTANCE_CODE', $targetInstanceCode);
				$smarty->assign ('TOKEN', $token);
				$smarty->display ('modules/instancesdatasharing/RequestConfirmation.tpl');
			}
		}
	} catch (Exception $e) {
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TITLE', '¡Lo sentimos!');
		$smarty->display ('modules/instancesdatasharing/Message.tpl');
	}
	exit ();
