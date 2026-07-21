<?php
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');

	abstract class ConfigApplicationsHelper {
		private static $UPLOAD_FOLDER = 'storage/appsimages';

		private static function createApplicationModules (PearDatabase $adb, $applicationId, $modules) {
			foreach ($modules as $module) {
				$adb->pquery ('INSERT INTO vtiger_configapps_tab (config_applicationsid, tabid) VALUES (?, ?)', array ($applicationId, $module ['id']));
			}
		}

		private static function createApplicationProfile (PearDatabase $adb, $applicationId, array $arguments) {
			$name        = $arguments ['name'];
			$description = $arguments ['description'];
			$profileId   = $adb->getUniqueID ('vtiger_profile');
			$adb->pquery ('INSERT INTO vtiger_profile (profileid, profilename, description) VALUES (?, ?, ?)', array ($profileId, $name, $description));
			$adb->pquery ('UPDATE vtiger_config_applications SET app_profile=? WHERE config_applicationsid=?', array ($profileId, $applicationId));
			self::createApplicationProfileDetails ($adb, $profileId, $arguments);
			return $profileId;
		}

		private static function createApplicationProfileDetails (PearDatabase $adb, $profileId, array $arguments) {
			$moduleIds = array ();
			$result    = $adb->query ('SELECT tabid FROM vtiger_tab WHERE customized IN (0, 2)');
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$moduleIds [] = $row ['tabid'];
				}
			}
			$modules = json_decode ($arguments ['modules'], true);
			foreach ($modules as $module) {
				$moduleIds [] = $module ['id'];
			}
			$moduleIds = array_unique ($moduleIds);
			$result    = $adb->query ('SELECT profileid FROM vtiger_profile ORDER BY profileid ASC LIMIT 1');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$row            = $adb->fetchByAssoc ($result, -1, false);
			$adminProfileId = $row ['profileid'];
			$questionMarks  = str_repeat ('?, ', (count ($moduleIds) - 1)) . '?';
			$adb->pquery ('INSERT INTO vtiger_profile2globalpermissions (profileid, globalactionid, globalactionpermission) VALUES (?, ?, ?)', array ($profileId, 1, 1));
			$adb->pquery ('INSERT INTO vtiger_profile2globalpermissions (profileid, globalactionid, globalactionpermission) VALUES (?, ?, ?)', array ($profileId, 2, 1));
			$adb->pquery (
				"INSERT INTO vtiger_profile2tab (profileid, tabid, permissions) SELECT ?, p2t.tabid, 1 FROM vtiger_profile2tab p2t INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.customized=1 WHERE p2t.profileid=? AND p2t.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2tab (profileid, tabid, permissions) SELECT ?, p2t.tabid, 0 FROM vtiger_profile2tab p2t INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid WHERE p2t.profileid=? AND (p2t.tabid IN ({$questionMarks}) OR t.customized IN (0, 2))",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions) SELECT ?, p2sp.tabid, p2sp.operation, 1 FROM vtiger_profile2standardpermissions p2sp INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.customized=1 WHERE p2sp.profileid=? AND p2sp.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions) SELECT ?, p2sp.tabid, p2sp.operation, 0 FROM vtiger_profile2standardpermissions p2sp INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid WHERE p2sp.profileid=? AND (p2sp.tabid IN ({$questionMarks}) OR t.customized IN (0, 2))",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) SELECT ?, p2u.tabid, p2u.activityid, 1 FROM vtiger_profile2utility p2u INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.customized=1 WHERE p2u.profileid=? AND p2u.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) SELECT ?, p2u.tabid, p2u.activityid, 0 FROM vtiger_profile2utility p2u INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid WHERE p2u.profileid=? AND (p2u.tabid IN ({$questionMarks}) OR t.customized IN (0, 2))",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly)
				SELECT
					?, p2f.tabid, p2f.fieldid, 1, 0
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid
					INNER JOIN vtiger_tab t ON t.tabid=p2f.tabid AND t.customized=1
				WHERE
					p2f.profileid=? AND
					t.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly)
				SELECT
					?, p2f.tabid, p2f.fieldid, 0, 0
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid
					INNER JOIN vtiger_tab t ON t.tabid=p2f.tabid
				WHERE
					p2f.profileid=? AND
					(t.tabid IN ({$questionMarks}) OR t.customized IN (0, 2))",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2customview (profileid, cvid, tabid, permissions) SELECT ?, p2cv.cvid, p2cv.tabid, 1 FROM vtiger_profile2customview p2cv INNER JOIN vtiger_tab t ON t.tabid=p2cv.tabid AND t.customized=1 WHERE p2cv.profileid=? AND p2cv.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
			$adb->pquery (
				"INSERT INTO vtiger_profile2customview (profileid, cvid, tabid, permissions) SELECT ?, p2cv.cvid, p2cv.tabid, 0 FROM vtiger_profile2customview p2cv INNER JOIN vtiger_tab t ON t.tabid=p2cv.tabid WHERE p2cv.profileid=? AND (p2cv.tabid IN ({$questionMarks}) OR t.customized IN (0, 2))",
				array_merge (array ($profileId, $adminProfileId), $moduleIds)
			);
		}

		private static function extractFileError ($file, $hiddenFileName) {
			$uploadFolder = self::getUploadFolder ();
			return (isset ($file ['error'])) && (($file ['error'] != UPLOAD_ERR_NO_FILE) || (!file_exists ("{$uploadFolder}/{$hiddenFileName}"))) ? $file ['error'] : UPLOAD_ERR_OK;
		}

		private static function extractFileName ($file, $hiddenFileName) {
			$uploadFolder = self::getUploadFolder ();
			if ((isset ($file ['error'])) && ($file ['error'] == UPLOAD_ERR_NO_FILE) && (file_exists ("{$uploadFolder}/{$hiddenFileName}"))) {
				return $hiddenFileName;
			}

			return ((isset ($file ['name'])) && (!empty ($file ['name']))) ? basename ($file ['name']) : null;
		}

		private static function extractFileType ($file, $hiddenFileName) {
			$uploadFolder = self::getUploadFolder ();
			if ((isset ($file ['error'])) && ($file ['error'] == UPLOAD_ERR_NO_FILE) && (file_exists ("{$uploadFolder}/{$hiddenFileName}"))) {
				return 'png';
			}

			if (!isset ($file ['type'])) {
				return null;
			}
			$explodedType = explode ('/', $file ['type']);
			return ($explodedType) && (is_array ($explodedType)) && (count ($explodedType) == 2) ? strtolower ($explodedType [1]) : null;
		}

		private static function extractFileSize ($file, $hiddenFileName) {
			$uploadFolder = self::getUploadFolder ();
			if ((isset ($file ['error'])) && ($file ['error'] == UPLOAD_ERR_NO_FILE) && (file_exists ("{$uploadFolder}/{$hiddenFileName}"))) {
				return 1;
			}
			return isset ($file ['size']) ? $file ['size'] : 0;
		}

		private static function getCategoryNames (PearDatabase $adb, $categoryIds) {
			if (($categoryIds) && (is_array ($categoryIds))) {
				$n             = count ($categoryIds);
				$questionMarks = array ();
				for ($i = 0; $i < $n; $i++) {
					$questionMarks [] = '?';
				}
				$questionMarks = join (',', $questionMarks);
				$sql           = "SELECT name FROM vtiger_category_apps WHERE catappid IN ($questionMarks)";
				$parameters    = $categoryIds;
			} else {
				$sql        = 'SELECT name FROM vtiger_category_apps';
				$parameters = array ($categoryIds);
			}
			$result = $adb->pquery ($sql, $parameters);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$categoryNames = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$categoryNames [] = $row ['name'];
			}
			return $categoryNames;
		}

		private static function getFreeModules (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				'SELECT
					t.*
				FROM
					vtiger_tab t
				WHERE
					t.isentitytype=1 AND
					t.presence IN (0, 2) AND
					t.tabid NOT IN (
						SELECT cat.tabid FROM vtiger_configapps_tab cat WHERE cat.config_applicationsid=?
					)',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}

			usort (
				$modules,
				function ($moduleA, $moduleB) {
					if ($moduleA ['tablabel'] < $moduleB ['tablabel']) {
						return -1;
					} else if ($moduleA ['tablabel'] == $moduleB ['tablabel']) {
						return 0;
					} else {
						return 1;
					}
				}
			);

			return $modules;
		}

		private static function getApplicationModuleIds (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				'SELECT
					t.tabid
				FROM
					vtiger_tab t
					INNER JOIN vtiger_configapps_tab cat ON cat.tabid=t.tabid
				WHERE
					t.isentitytype=1 AND
					t.presence IN (0, 2) AND
					cat.config_applicationsid=?',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row ['tabid'];
			}
			return $modules;
		}

		private static function getApplicationModules (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				'SELECT t.* FROM vtiger_tab t INNER JOIN vtiger_configapps_tab cat ON cat.tabid=t.tabid WHERE cat.config_applicationsid=? ORDER BY t.tabid',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			return $modules;
		}

		private static function getBlockFieldsByProfileId (PearDatabase $adb, $profileId, $moduleId, $blockId) {
			$result = $adb->pquery (
				'SELECT
					f.*,
					p2f.visible,
					p2f.readonly
				FROM
					vtiger_field f
					INNER JOIN vtiger_profile2field p2f ON p2f.fieldid=f.fieldid AND p2f.tabid=f.tabid AND p2f.profileid=?
				WHERE
					f.displaytype IN (1, 2, 4) AND
					f.tabid=? AND
					f.block=?
				ORDER BY
					f.sequence;',
				array ($profileId, $moduleId, $blockId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fields [] = $row;
			}
			return $fields;
		}

		private static function getModuleBlocksByProfileId (PearDatabase $adb, $profileId, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_blocks WHERE tabid=? ORDER BY sequence', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$blocks = array ();
			while ($row = $adb->fetch_array ($result)) {
				$row ['fields'] = self::getBlockFieldsByProfileId ($adb, $profileId, $moduleId, $row ['blockid']);
				$blocks []      = $row;
			}
			return $blocks;
		}

		private static function getModuleCustomViewsByProfileId (PearDatabase $adb, $profileId, $moduleId) {
			$result = $adb->pquery (
				'SELECT
					cv.*,
					p2cv.permissions,
					p2cv.setdefault AS profiledefault
				FROM
					vtiger_customview cv
					INNER JOIN vtiger_profile2customview p2cv ON p2cv.cvid=cv.cvid
				WHERE
					cv.status IN (0, 2, 3) AND
					p2cv.profileid=? AND
					p2cv.tabid=?
				ORDER BY
					p2cv.setdefault DESC,
					cv.setdefault DESC,
					cv.viewname',
				array ($profileId, $moduleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$permissions = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$permissions [] = $row;
			}
			return $permissions;
		}

		private static function getModuleId (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT tabid FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['tabid'];
		}

		private static function getModuleStandardPermissionsByProfileId (PearDatabase $adb, $profileId, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array ($profileId, $moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$permissions = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$permissions [] = array (
					'operation'   => $row ['operation'],
					'permissions' => $row ['permissions'],
				);
			}
			return $permissions;
		}

		private static function getModuleUtilityPermissionsByProfileId (PearDatabase $adb, $profileId, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array ($profileId, $moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$permissions = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$permissions [] = array (
					'activityid' => $row ['activityid'],
					'permission' => $row ['permission'],
				);
			}
			return $permissions;
		}

		private static function getUploadFolder () {
			return __DIR__ . '/../../../' . self::$UPLOAD_FOLDER;
		}

		private static function moveUploadedFile ($oldFileName, $newFileName) {
			if (!$oldFileName) {
				return;
			}

			$uploadFolder = self::getUploadFolder ();
			if (!is_dir ($uploadFolder)) {
				mkdir ($uploadFolder, 0777, true);
			}
			move_uploaded_file ($oldFileName, "{$uploadFolder}/{$newFileName}");
		}

		private static function updateApplicationProfile (PearDatabase $adb, $applicationId, array $arguments) {
			$result = $adb->pquery ('SELECT app_profile FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				self::createApplicationProfile ($adb, $applicationId, $arguments);
				return;
			}

			$row       = $adb->fetchByAssoc ($result, -1, false);
			$profileId = $row ['app_profile'];
			if (!$profileId) {
				self::createApplicationProfile ($adb, $applicationId, $arguments);
				return;
			}

			$result = $adb->pquery (
				'SELECT p2t.profileid FROM vtiger_profile2tab p2t INNER JOIN vtiger_profile2field p2f ON p2f.profileid=p2t.profileid AND p2f.tabid=p2t.tabid INNER JOIN vtiger_profile2standardpermissions p2sp ON p2sp.profileid=p2t.profileid AND p2sp.tabid=p2t.tabid INNER JOIN vtiger_profile2utility p2u ON p2u.profileid=p2t.profileid AND p2u.tabid=p2t.tabid INNER JOIN vtiger_profile2customview p2cv ON p2cv.profileid=p2t.profileid AND p2cv.tabid=p2t.tabid WHERE p2t.profileid=?',
				array ($profileId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$adb->pquery ('DELETE FROM vtiger_profile2globalpermissions WHERE profileid=?', array ($profileId));
				$adb->pquery ('DELETE FROM vtiger_profile2tab WHERE profileid=?', array ($profileId));
				$adb->pquery ('DELETE FROM vtiger_profile2standardpermissions WHERE profileid=?', array ($profileId));
				$adb->pquery ('DELETE FROM vtiger_profile2utility WHERE profileid=?', array ($profileId));
				$adb->pquery ('DELETE FROM vtiger_profile2field WHERE profileid=?', array ($profileId));
				$adb->pquery ('DELETE FROM vtiger_profile2customview WHERE profileid=?', array ($profileId));
				self::createApplicationProfileDetails ($adb, $profileId, $arguments);
				return;
			}

			$adb->pquery ('UPDATE vtiger_profile SET profilename=?, description=? WHERE profileid=?', array ($arguments ['name'], $arguments ['description'], $profileId));

			$modules   = json_decode ($arguments ['modules'], true);
			$moduleIds = array ();
			foreach ($modules as $module) {
				$moduleIds [] = $module ['id'];
			}

			$adb->pquery ('INSERT IGNORE INTO vtiger_profile2globalpermissions (profileid, globalactionid, globalactionpermission) VALUES (?, ?, ?)', array ($profileId, 1, 1));
			$adb->pquery ('INSERT IGNORE INTO vtiger_profile2globalpermissions (profileid, globalactionid, globalactionpermission) VALUES (?, ?, ?)', array ($profileId, 2, 1));

			$questionMarks = str_repeat ('?, ', (count ($moduleIds) - 1)) . '?';
			$adb->pquery (
				"UPDATE vtiger_profile2tab p2t INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.customized=1 SET p2t.permissions=0 WHERE p2t.permissions=1 AND p2t.profileid=? AND p2t.tabid IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2tab p2t INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.customized=1 SET p2t.permissions=1 WHERE p2t.permissions=0 AND p2t.profileid=? AND p2t.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2standardpermissions p2sp INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.customized=1 SET p2sp.permissions=0 WHERE p2sp.permissions=1 AND p2sp.profileid=? AND p2sp.tabid IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2standardpermissions p2sp INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.customized=1 SET p2sp.permissions=1 WHERE p2sp.permissions=0 AND p2sp.profileid=? AND p2sp.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2utility p2u INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.customized=1 SET p2u.permission=0 WHERE p2u.permission=1 AND p2u.profileid=? AND p2u.tabid IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2utility p2u INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.customized=1 SET permission=1 WHERE p2u.permission=0 AND p2u.profileid=? AND p2u.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE
						vtiger_profile2field p2f
						INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid
						INNER JOIN vtiger_tab t ON t.tabid=p2f.tabid
					SET
						p2f.visible=0
					WHERE
						p2f.visible=1 AND
						f.presence IN (0, 1, 2) AND
						t.customized=1 AND
						p2f.profileid=? AND
						t.tabid IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE
						vtiger_profile2field p2f
						INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid
						INNER JOIN vtiger_tab t ON t.tabid=p2f.tabid
					SET
						p2f.visible=1
					WHERE
						p2f.visible=0 AND
						f.presence IN (0, 1, 2) AND
						t.customized=1 AND
						p2f.profileid=? AND
						t.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2customview p2cv INNER JOIN vtiger_tab t ON t.tabid=p2cv.tabid AND t.customized=1 SET p2cv.permissions=0 WHERE p2cv.permissions=1 AND p2cv.profileid=? AND p2cv.tabid IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
			$adb->pquery (
				"UPDATE vtiger_profile2customview p2cv INNER JOIN vtiger_tab t ON t.tabid=p2cv.tabid AND t.customized=1 SET p2cv.permissions=1 WHERE p2cv.permissions=0 AND p2cv.profileid=? AND p2cv.tabid NOT IN ({$questionMarks})",
				array_merge (array ($profileId), $moduleIds)
			);
		}

		private static function updateUploadedFile ($fileData, $arguments) {
			if ($fileData ['tmp_name']) {
				self::moveUploadedFile ($fileData ['tmp_name'], "{$arguments ['code']}.{$fileData ['type']}");
			} else if (($fileData ['name']) && ($fileData ['name'] != "{$arguments ['code']}.{$fileData ['type']}")) {
				$uploadFolder = self::getUploadFolder ();
				if (!file_exists ("{$uploadFolder}/{$fileData ['name']}")) {
					return;
				}

				rename ("{$uploadFolder}/{$fileData ['name']}", "{$uploadFolder}/{$arguments ['code']}.{$fileData ['type']}");
			}
		}

		private static function validateFileData ($fileData) {
			if ((!$fileData) || ($fileData ['error'] == UPLOAD_ERR_NO_FILE) || (!$fileData ['name'])) {
				throw new Exception ('No se ha suministrado la imagen de la aplicación');
			} else if (($fileData ['size'] == 0) || ($fileData ['error'] == UPLOAD_ERR_FORM_SIZE)) {
				throw new Exception ('Tamaño de archivo inválido');
			} else if ($fileData ['type'] != 'png') {
				throw new Exception ('Tipo de archivo inválido');
			} else if ($fileData ['error'] == UPLOAD_ERR_PARTIAL) {
				throw new Exception ('La imagen suministrada no pudo ser procesada en su totalidad');
			}
		}

		private static function validateInactivatableApplication (PearDatabase $adb, $applicationId, $status) {
			if ($status != 'Inactiva') {
				return;
			}
			$result = $adb->pquery (
				'SELECT
					ia.*
				FROM
					vtiger_instanceapplications ia
					INNER JOIN vtiger_config_applications ca ON ca.app_code=ia.applicationcode AND ca.config_applicationsid=?
					INNER JOIN vtiger_instances i ON i.code=ia.instancecode',
				array ($applicationId)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				throw new Exception ('No puede ser inactivada una aplicación asociada a una instancia activa del sistema');
			}
		}

		private static function validateSelectedModules (PearDatabase $adb, $modules) {
			if (empty ($modules)) {
				throw new Exception ('No se han seleccionado módulos');
			}

			foreach ($modules as $module) {
				$result = $adb->pquery (
					'SELECT * FROM vtiger_tab WHERE tabid=? AND isentitytype=1 AND presence IN (0, 2)',
					array ($module ['id'])
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					throw new Exception ("El módulo {$module ['name']} no está registrado");
				}
			}
		}

		public static function createApplication (PearDatabase $adb, $arguments, $fileData) {
			self::moveUploadedFile ($fileData ['tmp_name'], "{$arguments ['code']}.{$fileData ['type']}");

			$adb->pquery (
				'INSERT INTO vtiger_config_applications (app_code, app_name, app_descripcion, app_status, app_date_act, app_price, app_category, app_url) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)',
				array ($arguments ['code'], $arguments ['name'], $arguments ['description'], $arguments ['status'], $arguments ['price'], join ('#', $arguments ['category']), $arguments ['url'])
			);
			$applicationId = $adb->getLastInsertID ();
			$modules       = json_decode ($arguments ['modules'], true);
			self::createApplicationModules ($adb, $applicationId, $modules);
			self::createApplicationProfile ($adb, $applicationId, $arguments);
		}

		public static function duplicateApplication (PearDatabase $adb, $oldApplicationId, $newApplicationCode, $newApplicationName, array $newModuleNames) {
			try {
				$oldApplication = self::getApplication ($adb, $oldApplicationId);
			} catch (Exception $e) {
				return;
			}

			$adb->pquery (
				'INSERT INTO vtiger_config_applications (app_code, app_name, app_descripcion, app_status, app_date_act, app_price, app_category, app_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
				array ($newApplicationCode, $newApplicationName, $oldApplication ['app_descripcion'], $oldApplication ['app_status'], date ('Y-m-d'), $oldApplication ['app_price'], $oldApplication ['app_category'], $oldApplication ['app_url'])
			);
			$newApplicationId = $adb->getLastInsertID ();

			$newModules = array ();
			foreach ($newModuleNames as $newModuleName) {
				$tabId         = self::getModuleId ($adb, $newModuleName);
				$newModules [] = array ('id' => $tabId);
				$adb->pquery (
					'INSERT INTO vtiger_configapps_tab (config_applicationsid, tabid) VALUES (?, ?)',
					array ($newApplicationId, $tabId)
				);
			}

			self::createApplicationProfile ($adb, $newApplicationId, array ('name' => $newApplicationName, 'description' => $oldApplication ['app_descripcion'], 'modules' => json_encode ($newModules)));

			$oldApplicationImagePath = self::getUploadFolder () . "/{$oldApplication ['app_code']}.png";
			$newApplicationImagePath = self::getUploadFolder () . "/{$newApplicationCode}.png";
			if (file_exists ($newApplicationImagePath)) {
				unlink ($newApplicationImagePath);
			}

			copy ($oldApplicationImagePath, $newApplicationImagePath);
		}

		public static function extractFileData ($file, $hiddenFileName) {
			if ((!$file) && (!$hiddenFileName)) {
				return null;
			}

			$fileData = array (
				'error'    => self::extractFileError ($file, $hiddenFileName),
				'name'     => self::extractFileName ($file, $hiddenFileName),
				'size'     => self::extractFileSize ($file, $hiddenFileName),
				'tmp_name' => $file ['tmp_name'],
				'type'     => self::extractFileType ($file, $hiddenFileName),
			);

			self::validateFileData ($fileData);

			return $fileData;
		}

		public static function fixApplicationProfile (PearDatabase $adb, $applicationData) {
			$name        = $applicationData ['app_name'];
			$description = $applicationData ['app_descripcion'];
			$moduleIds   = self::getApplicationModuleIds ($adb, $applicationData ['config_applicationsid']);
			$modules     = array ();
			foreach ($moduleIds as $moduleId) {
				$modules [] = array ('id' => $moduleId);
			}
			$adb->pquery ('DELETE FROM vtiger_profile2tab WHERE profileid IN (SELECT profileid FROM vtiger_profile WHERE profilename=? AND description=?)', array ($name, $description));
			$adb->pquery ('DELETE FROM vtiger_profile2standardpermissions WHERE profileid IN (SELECT profileid FROM vtiger_profile WHERE profilename=? AND description=?)', array ($name, $description));
			$adb->pquery ('DELETE FROM vtiger_profile2utility WHERE profileid IN (SELECT profileid FROM vtiger_profile WHERE profilename=? AND description=?)', array ($name, $description));
			$adb->pquery ('DELETE FROM vtiger_profile2field WHERE profileid IN (SELECT profileid FROM vtiger_profile WHERE profilename=? AND description=?)', array ($name, $description));
			$adb->pquery ('DELETE FROM vtiger_profile2customview WHERE profileid IN (SELECT profileid FROM vtiger_profile WHERE profilename=? AND description=?)', array ($name, $description));
			$adb->pquery ('DELETE FROM vtiger_profile WHERE profilename=? AND description=?', array ($name, $description));
			return self::createApplicationProfile ($adb, $applicationData ['config_applicationsid'], array ('name' => $name, 'description' => $description, 'modules' => json_encode ($modules)));
		}

		public static function getActiveApplicationCategories (PearDatabase $adb) {
			$result = $adb->query ("SELECT * FROM vtiger_category_apps WHERE status='Activa'");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$categories = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$categories [] = $row;
			}

			usort (
				$categories,
				function ($categoryA, $categoryB) {
					return strcmp ($categoryA ['name'], $categoryB ['name']);
				}
			);

			return $categories;
		}

		public static function getApplication (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				'SELECT
					ca.*
				FROM
					vtiger_config_applications ca
				WHERE
					ca.config_applicationsid=?',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ("La aplicación con el identificador {$applicationId} no se encuentra registrada");
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		public static function getApplicationById (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery ('SELECT a.* FROM vtiger_config_applications a WHERE a.config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row;
		}

		public static function getApplicationProfileData (PearDatabase $adb, $profileId, $includeProfileDataForAllModules = false) {
			$joinType = $includeProfileDataForAllModules ? 'LEFT' : 'INNER';
			$result   = $adb->pquery (
				"SELECT
					t.*,
					p2t.permissions,
					IF(cat.tabid IS NULL, 0, 1) AS isapplicationmodule
				FROM
					vtiger_tab t
					INNER JOIN vtiger_profile2tab p2t ON p2t.tabid=t.tabid
					{$joinType} JOIN vtiger_config_applications a ON a.app_profile=p2t.profileid
					{$joinType} JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=a.config_applicationsid AND cat.tabid=t.tabid
				WHERE
					p2t.profileid=?
				ORDER BY
					t.tablabel",
				array ($profileId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['blocks']              = self::getModuleBlocksByProfileId ($adb, $profileId, $row ['tabid']);
				$row ['customviews']         = self::getModuleCustomViewsByProfileId ($adb, $profileId, $row ['tabid']);
				$row ['standardpermissions'] = self::getModuleStandardPermissionsByProfileId ($adb, $profileId, $row ['tabid']);
				$row ['utilitypermissions']  = self::getModuleUtilityPermissionsByProfileId ($adb, $profileId, $row ['tabid']);
				$modules []                  = $row;
			}

			return array (
				'id'      => $profileId,
				'modules' => $modules,
			);
		}

		public static function getApplications (PearDatabase $adb, $includeProfileDataForAllModules = false) {
			$result = $adb->query ('SELECT * FROM vtiger_config_applications ORDER BY config_applicationsid');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$row ['modules'] = self::getApplicationModules ($adb, $row ['config_applicationsid']);
				$row ['profile'] = self::getApplicationProfileData ($adb, $row ['app_profile'], $includeProfileDataForAllModules);
				$applications [] = $row;
			}
			return $applications;
		}

		public static function getApplicationData (PearDatabase $adb, $applicationId, $imagesDirectoryPath) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$row = $adb->fetchByAssoc ($result);
			return array (
				'id'          => $row ['config_applicationsid'],
				'category'    => explode ('#', $row ['app_category']),
				'code'        => $row ['app_code'],
				'description' => $row ['app_descripcion'],
				'image'       => file_exists ("{$imagesDirectoryPath}/{$row ['app_code']}.png") ? 1 : 0,
				'modules'     => self::getApplicationModuleIds ($adb, $applicationId),
				'name'        => $row ['app_name'],
				'price'       => $row ['app_price'],
				'status'      => $row ['app_status'],
				'url'         => $row ['app_url'],
			);
		}

		public static function getApplicationsData (PearDatabase $adb, $imagesDirectoryPath) {
			$result = $adb->query ('SELECT * FROM vtiger_config_applications');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$applicationsData = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$applicationsData [] = array (
					'id'          => $row ['config_applicationsid'],
					'category'    => self::getCategoryNames ($adb, explode ('#', $row ['app_category'])),
					'code'        => $row ['app_code'],
					'image'       => file_exists ("{$imagesDirectoryPath}/{$row ['app_code']}.png") ? 1 : 0,
					'description' => $row ['app_descripcion'],
					'name'        => $row ['app_name'],
					'price'       => $row ['app_price'],
					'status'      => $row ['app_status'],
					'url'         => $row ['app_url'],
				);
			}
			return $applicationsData;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @return array|null
		 */
		public static function getInstanceApplications ($instanceCode) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->pquery (
				"SELECT
					ica.*
				FROM
					vtiger_instanceapplications ia
					INNER JOIN vtiger_instances i ON i.code=ia.instancecode
					INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
					INNER JOIN pg_crm_{$instanceCode}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
				WHERE
					ia.status IN (?, ?) AND
					i.code=?
				ORDER BY
					ica.config_applicationsid",
				array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $instanceCode)
			);
			if (($result) && ($masterAdb->num_rows ($result) > 0)) {
				$targetAdb          = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
				$activeApplications = array ();
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$row ['modules']                          = self::getApplicationModules ($targetAdb, $row ['config_applicationsid']);
					$activeApplications [ $row ['app_code'] ] = $row;
				}
			} else {
				$activeApplications = null;
			}
			return $activeApplications;
		}

		public static function getVisibleModules (PearDatabase $adb, $applicationId = null) {
			if ($applicationId) {
				return self::getFreeModules ($adb, $applicationId);
			}

			$result = $adb->query (
				'SELECT
					t.*
				FROM
					vtiger_tab t
				WHERE
					t.isentitytype=1 AND
					t.presence IN (0, 2) AND
					t.customized IN (1, 2)'
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			usort (
				$modules,
				function ($moduleA, $moduleB) {
					if ($moduleA ['tablabel'] < $moduleB ['tablabel']) {
						return -1;
					} else if ($moduleA ['tablabel'] == $moduleB ['tablabel']) {
						return 0;
					} else {
						return 1;
					}
				}
			);
			return $modules;
		}

		public static function updateApplication (PearDatabase $adb, $arguments, $fileData) {
			if ((!isset ($arguments ['id'])) || (empty ($arguments ['id']))) {
				throw new Exception ('No se ha suministrado el identificador de la aplicación');
			}

			self::validateInactivatableApplication ($adb, $arguments ['id'], $arguments ['status']);
			self::updateUploadedFile ($fileData, $arguments);

			$modules     = json_decode ($arguments ['modules'], true);
			$adb->pquery (
				'UPDATE vtiger_config_applications SET app_code=?, app_name=?, app_descripcion=?, app_status=?, app_date_act=NOW(), app_price=?, app_category=?, app_url=? WHERE config_applicationsid=?',
				array ($arguments ['code'], $arguments ['name'], $arguments ['description'], $arguments ['status'], $arguments ['price'], join ('#', $arguments ['category']), $arguments ['url'], $arguments ['id'])
			);

			$adb->pquery ('DELETE FROM vtiger_configapps_tab WHERE config_applicationsid=?', array ($arguments ['id']));
			self::createApplicationModules ($adb, $arguments ['id'], $modules);
			self::updateApplicationProfile ($adb, $arguments ['id'], $arguments);
		}

		public static function validateArguments (PearDatabase $adb, $arguments) {
			if (empty ($arguments ['code'])) {
				throw new Exception ('No se ha suministrado el código');
			} else if (empty ($arguments ['name'])) {
				throw new Exception ('No se ha suministrado el nombre');
			} else if (empty ($arguments ['url'])) {
				throw new Exception ('No se ha suministrado el URL');
			} else if (empty ($arguments ['description'])) {
				throw new Exception ('No se ha suministrado la descripción');
			} else if (empty ($arguments ['status'])) {
				throw new Exception ('No se ha suministrado el status');
			} else if (empty ($arguments ['category'])) {
				throw new Exception ('No se ha seleccionado una categoría');
			}

			self::validateSelectedModules ($adb, json_decode ($arguments ['modules'], true));
		}

	}
