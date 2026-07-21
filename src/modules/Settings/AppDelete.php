<?php
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_user;

	// Si no es un usuario administrador de la plataforma madre, no puede realizar cambios de Module Manager
	if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
		header ('Location: index.php');
		exit ();
	}

	try {
		$applicationId = SettingsUtils::purify ($_REQUEST, 'record');
		if (!$applicationId) {
			throw new Exception ('No se ha suministrado el identificador de la aplicación a eliminar');
		} else if ($applicationId == 1) {
			throw new Exception ('La aplicación no puede ser eliminada porque contiene los módulos personalizados de clientes');
		}

		$am = ApplicationManager::getInstance ($adb);
		$application = $am->fetchApplicationById ($applicationId);
		if (empty ($application)) {
			throw new Exception ("No se encuentra registrada la aplicación con el ID suministrado");
		}

		$result = $adb->pquery (
			'SELECT
				*
			FROM
				vtiger_instanceapplications ia
				INNER JOIN vtiger_config_applications ca ON ca.app_code=ia.applicationcode AND ca.config_applicationsid=?
				INNER JOIN vtiger_instances i ON i.code=ia.instancecode',
			array ($applicationId)
		);
		if (($result) && ($adb->num_rows ($result) > 0)) {
			throw new Exception ('No puede ser eliminada una aplicación asociada a una instancia activa del sistema');
		}

		$am->deleteApplication ($application);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => "La aplicación {$application->getName ()} ha sido eliminada",
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=ConfigApps&parenttab=Settings');
	exit ();
