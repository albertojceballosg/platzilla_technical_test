<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');

	global $adb;

	$applicationId = PlatzillaUtils::purify ($_POST, 'record');
	$cvIds         = PlatzillaUtils::purify ($_POST, 'cvids');
	$fieldIds      = PlatzillaUtils::purify ($_POST, 'fieldids');

	try {

		if (empty ($applicationId)) {
			throw new Exception ('No has suministrado el ID de la aplicación');
		}

		$application = ConfigApplicationsHelper::getApplicationById ($adb, $applicationId);
		if (empty ($application)) {
			throw new Exception ('La aplicación suministrada no está registrada');
		}

		$profileId = $application ['app_profile'];
		if (empty ($profileId)) {
			throw new Exception ('La aplicación suministrada no tiene perfil asociado');
		}

		$profileData = ConfigApplicationsHelper::getApplicationProfileData ($adb, $profileId);
		if (!$profileData) {
			throw new Exception ('La aplicación suministrada no tiene información de perfil');
		}
		foreach ($profileData ['modules'] as $profileModule) {
			$moduleId = $profileModule ['tabid'];
			if ((!isset ($cvIds [ $moduleId ]['defaultcvid'])) || (empty ($cvIds [ $moduleId ]['defaultcvid']))) {
				throw new Exception ("No has seleccionado vistas personalizadas para el módulo {$profileModule ['name']}");
			}
			if ((!isset ($cvIds [ $moduleId ]['cvids'])) || (empty ($cvIds [ $moduleId ]['cvids']))) {
				throw new Exception ("No has seleccionado vistas personalizadas para el módulo {$profileModule ['name']}");
			}
		}

		foreach ($profileData ['modules'] as $profileModule) {
			$moduleId = $profileModule ['tabid'];
			if ((isset ($fieldIds [ $moduleId ])) && (!empty ($fieldIds [ $moduleId ]))) {
				$questionMarks = str_repeat ('?, ', count ($fieldIds [ $moduleId ]) - 1) . '?';
				$adb->pquery (
					"UPDATE
							vtiger_profile2field p2f
							INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.tabid=p2f.tabid
						SET
							p2f.visible=1
						WHERE
							SUBSTRING_INDEX(SUBSTRING_INDEX(f.typeofdata, '~', 2), '~', -1)<>'M' AND
							p2f.profileid=? AND
							f.tabid=? AND
							f.fieldid NOT IN ({$questionMarks})",
					array_merge (array ($profileId, $moduleId), $fieldIds [ $moduleId ])
				);
				$adb->pquery (
					"UPDATE
							vtiger_profile2field p2f
							INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.tabid=p2f.tabid
						SET
							p2f.visible=0
						WHERE
							SUBSTRING_INDEX(SUBSTRING_INDEX(f.typeofdata, '~', 2), '~', -1)<>'M' AND
							p2f.profileid=? AND
							f.tabid=? AND
							f.fieldid IN ({$questionMarks})",
					array_merge (array ($profileId, $moduleId), $fieldIds [ $moduleId ])
				);
			} else {
				$adb->pquery (
					"UPDATE
							vtiger_profile2field p2f
							INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.tabid=p2f.tabid
						SET
							p2f.visible=1
						WHERE
							SUBSTRING_INDEX(SUBSTRING_INDEX(f.typeofdata, '~', 2), '~', -1)<>'M' AND
							p2f.profileid=? AND
							f.tabid=?",
					array ($profileId, $moduleId)
				);
			}

			$questionMarks = str_repeat ('?, ', count ($cvIds [ $moduleId ]['cvids']) - 1) . '?';
			$adb->pquery (
				"UPDATE
					vtiger_profile2customview p2cv
					INNER JOIN vtiger_customview cv ON cv.cvid=p2cv.cvid
				SET
					p2cv.permissions=1,
					p2cv.setdefault=0

				WHERE
					p2cv.profileid=? AND
					p2cv.tabid=? AND
					p2cv.cvid NOT IN ({$questionMarks})",
				array_merge (array ($profileId, $moduleId), $cvIds [ $moduleId ]['cvids'])
			);

			$adb->pquery (
				"UPDATE
					vtiger_profile2customview p2cv
					INNER JOIN vtiger_customview cv ON cv.cvid=p2cv.cvid
				SET
					p2cv.permissions=0,
					p2cv.setdefault=IF(p2cv.cvid=?, 1, 0),
					cv.status=0
				WHERE
					p2cv.profileid=? AND
					p2cv.tabid=? AND
					p2cv.cvid IN ({$questionMarks})",
				array_merge (array ($cvIds [ $moduleId ]['defaultcvid'], $profileId, $moduleId), $cvIds [ $moduleId ]['cvids'])
			);
		}
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se actualizó la configuración de visibilidad de campos de la aplicación',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module=Settings&action=EditApplicationProfile&record={$applicationId}&parenttab=Settings");