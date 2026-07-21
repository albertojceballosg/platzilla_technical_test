<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	abstract class ActivityAjaxHelper {

		private static function arePlatformPermissionsArgumentsValid ($arguments) {
			if (!is_array ($arguments)) {
				return false;
			}
			if (!isset ($arguments ['plat_base'])) {
				return false;
			}
			if (!isset ($arguments ['nombreCodigo'])) {
				return false;
			}
			if (!isset ($arguments ['modulerel'])) {
				return false;
			}
			if (!isset ($arguments ['modules'])) {
				return false;
			}
			return true;
		}

		private static function getRelatedField (PearDatabase $adb, $platformName, $baseModule, $childModule, $childField) {
			$sql    = 'SELECT
							fieldbase
						FROM
							vtiger_relationsship_plat_fields pf
							INNER JOIN vtiger_relationsship_plat p ON pf.relationsship_platid=p.relationsship_platid AND p.plat=?
						WHERE
							modulebase=? AND
							modulechild=? AND
							fieldchild=?';
			$result = $adb->pquery ($sql, array ($platformName, $baseModule, $childModule, $childField));
			if (!$result) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['fieldbase'];
		}

		private static function getPlatformRelationshipId (PearDatabase $adb, $platformName) {
			$result = $adb->pquery ('SELECT relationsship_platid FROM vtiger_relationsship_plat WHERE plat=?', array ($platformName));
			if (!$result) {
				return -1;
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['relationsship_platid'];
		}

		private static function getModuleFields (PearDatabase $adb, $module, $childPlatform = null, $relatedModule = null) {
			$result = $adb->pquery ('SELECT fieldname, fieldlabel FROM vtiger_field f INNER JOIN vtiger_tab t ON f.tabid=t.tabid AND t.name=?', array ($module));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$field = array (
					'name'  => $row ['fieldname'],
					'label' => getTranslatedString ($row ['fieldlabel']),
				);
				if (($childPlatform) && ($relatedModule)) {
					$field ['related'] = self::getRelatedField ($adb, $childPlatform, $module, $relatedModule, $row ['fieldname']);
				}
				$fields [] = $field;
			}
			return $fields;
		}

		private static function recreatePlatformPermissions (PearDatabase $adb, $relationshipId, $module, $relatedModule, $view, $shouldReplicate, $update) {
			$adb->pquery ('DELETE FROM vtiger_relationsship_plat_fields WHERE relationsship_platid=? AND modulechild=?', array ($relationshipId, $relatedModule));
			$adb->pquery ('DELETE FROM vtiger_relationsship_plat_modules WHERE relationsship_platid=? AND module=?', array ($relationshipId, $relatedModule));
			$adb->pquery (
				'INSERT INTO vtiger_relationsship_plat_modules (relationsship_platid,module,module_base,view,replication,`update`) VALUES (?, ?, ?, ?, ?, ?)',
				array ($relationshipId, $relatedModule, $module, $view, $shouldReplicate, $update)
			);
		}

		private static function replicatePlatformFields (PearDatabase $adb, $relationshipId, $module, $relatedModule, $baseFields, $childFields) {
			$n = count ($childFields);
			for ($i = 0; $i < $n; $i++) {
				if ((isset ($baseFields [ $i ])) && (!empty ($baseFields [ $i ]))) {
					$adb->pquery (
						'INSERT INTO vtiger_relationsship_plat_fields (relationsship_platid, modulebase, fieldbase, modulechild, fieldchild) VALUES (?, ?, ?, ?, ?)',
						array ($relationshipId, $module, $baseFields [ $i ], $relatedModule, $childFields [ $i ])
					);
				}
			}
		}

		public static function createPlatformsRelationship ($basePlatform, $childPlatform, $username, $password) {
			if (!$basePlatform) {
				throw new Exception ('No se ha suministrado el nombre de la plataforma principal');
			}
			if (!$childPlatform) {
				throw new Exception ('No se ha suministrado el nombre de la plataforma hija');
			}
			if (!$username) {
				throw new Exception ('No se ha suministrado el nombre del usuario');
			}
			if (!$password) {
				throw new Exception ('No se ha suministrado la contraseña del usuario');
			}

			$basePlatform  = vtlib_purify ($basePlatform);
			$childPlatform = vtlib_purify ($childPlatform);
			$username      = vtlib_purify ($username);
			$password      = vtlib_purify ($password);

			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($childPlatform);
			$result = $adb->query ('SELECT * FROM vtiger_organizationdetails');
			if (!$result) {
				return;
			}
			$row              = $adb->fetchByAssoc ($result);
			$organizationName = $row ['organizationname'];
			if (empty ($organizationName)) {
				return;
			}
			$adb->pquery (
				'INSERT INTO vtiger_relationsship_plat (plat, name, user, pass) VALUES (?, ?, ?, ?)',
				array ($childPlatform, $organizationName, $username, en_cryption ($password))
			);
			$adb->disconnect ();
			unset ($adb);

			$adb = AdbManager::getInstance ()->getSourceInstanceAdb ($basePlatform);
			$adb->pquery (
				'INSERT INTO vtiger_relationsship_plat_plat VALUES (NULL, ?, ?)',
				array ($basePlatform, $childPlatform)
			);
			$adb->disconnect ();
			unset ($adb);
		}

		public static function deleteSystemVariable (PearDatabase $adb, $variableId) {
			$variableId = vtlib_purify ($variableId);
			if (!$variableId) {
				return;
			}
			$adb->pquery ('DELETE FROM vtiger_variables WHERE variableid=?', array ($variableId));
		}

		public static function getFieldMapping ($basePlatform, $childPlatform, $module, $relatedModule) {
			if ((!$basePlatform) || (!$childPlatform) || (!$module) || (!$relatedModule)) {
				return '';
			}

			$basePlatform  = vtlib_purify ($basePlatform);
			$childPlatform = vtlib_purify ($childPlatform);
			$module        = vtlib_purify ($module);
			$relatedModule = vtlib_purify ($relatedModule);

			$adb          = AdbManager::getInstance ()->getTargetInstanceAdb ($childPlatform);
			$sourceFields = self::getModuleFields ($adb, $relatedModule, $childPlatform, $relatedModule);
			$adb->disconnect ();
			unset ($adb);

			$adb          = AdbManager::getInstance ()->getSourceInstanceAdb ($basePlatform);
			$targetFields = self::getModuleFields ($adb, $module);
			$adb->disconnect ();
			unset ($adb);

			return array (
				'source' => $sourceFields,
				'target' => $targetFields,
			);
		}

		public static function getRelatedModulesPicklistOptions (PearDatabase $adb, $module) {
			$result = $adb->pquery (
				'SELECT
					t1.name,
					t1.tablabel
				FROM
					vtiger_tab t1
					INNER JOIN vtiger_relatedlists rl ON t1.tabid=rl.related_tabid
					INNER JOIN vtiger_tab t2 ON t2.tabid=rl.tabid AND t2.name=?',
				array ($module)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			$options = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$options [ $row ['name'] ] = getTranslatedString ($row ['label']);
			}
			return $options;
		}

		public static function getRelatedModuleFieldsPicklistOptions (PearDatabase $adb, $module) {
			$result = $adb->pquery (
				'SELECT f.fieldname, f.fieldlabel FROM vtiger_field f INNER JOIN vtiger_tab t WHERE t.name=?',
				array ($module)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			$options = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$options [ $row ['fieldname'] ] = getTranslatedString ($row ['fieldlabel']);
			}
			return $options;
		}

		public static function setModuleAsCombinable (PearDatabase $adb, $status, $relatedModule) {
			if ((!$status) || (!$relatedModule)) {
				return;
			}

			$status        = vtlib_purify ($status);
			$relatedModule = vtlib_purify ($relatedModule);
			$adb->pquery (
				'UPDATE vtiger_tab SET combinable=? WHERE name=?',
				array ($status == 'true' ? '1' : '0', $relatedModule)
			);
		}

		public static function setPlatformsPermissions ($arguments) {
			if (!self::arePlatformPermissionsArgumentsValid ($arguments)) {
				return;
			}

			$basePlatform    = vtlib_purify ($arguments ['plat_base']);
			$childPlatform   = vtlib_purify ($arguments ['nombreCodigo']);
			$module          = vtlib_purify ($arguments ['modules']);
			$relatedModule   = vtlib_purify ($arguments ['modulerel']);
			$update          = SettingsUtils::purify ($arguments, 'update');
			$view            = SettingsUtils::purify ($arguments, 'view');
			$shouldReplicate = SettingsUtils::purify ($arguments, 'replication');
			$baseFields      = SettingsUtils::purify ($arguments, 'campoPlatBase');
			$childFields     = SettingsUtils::purify ($arguments, 'campoPlatHija');

			$adb            = AdbManager::getInstance ()->getSourceInstanceAdb ($basePlatform);
			$relationshipId = self::getPlatformRelationshipId ($adb, $childPlatform);
			//Se borran las relaciones viejas y se recrean
			self::recreatePlatformPermissions ($adb, $relationshipId, $module, $relatedModule, $view, $shouldReplicate, $update);
			//Se crea la relacion de campos entre los modulos
			if ($shouldReplicate == 1) {
				self::replicatePlatformFields ($adb, $relationshipId, $module, $relatedModule, $baseFields, $childFields);
			}
			$adb->disconnect ();
			unset ($adb);

			$adb            = AdbManager::getInstance ()->getTargetInstanceAdb ($childPlatform);
			$relationshipId = self::getPlatformRelationshipId ($adb, $basePlatform);
			//Se borran las relaciones viejas y se recrean
			self::recreatePlatformPermissions ($adb, $relationshipId, $module, $relatedModule, $view, $shouldReplicate, $update);
			//Se crea la relacion de campos entre los modulos
			if ($shouldReplicate == 1) {
				self::replicatePlatformFields ($adb, $relationshipId, $module, $relatedModule, $baseFields, $childFields);
			}
			$adb->disconnect ();
			unset ($adb);
		}

	}
