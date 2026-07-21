<?php
	require_once ('include/platzilla/Utils/VtigerUtilsException.php');

	/**
	 * El objetivo de esta clase es servir de wrapper entre vTiger y Platzilla. Esta clase debe desaparecer en un futuro no tan lejano!
	 */
	abstract class VtigerUtils {

		private static function getAdbConnection ($globalAdb, $localAdb) {
			if (!empty ($globalAdb->database)) {
				return $globalAdb;
			} else {
				return $localAdb;
			}
		}

		public static function firePreUninstallEvent (PearDatabase $adbConnection, $moduleName) {
			global $adb;
			$adb = self::getAdbConnection ($adb, $adbConnection);

			require_once ('vtlib/Vtiger/Module.php');
			Vtiger_Module::fireEvent ($moduleName, Vtiger_Module::EVENT_MODULE_PREUNINSTALL);
		}

		public static function firePostInstallEvent (PearDatabase $adbConnection, $moduleName) {
			global $adb;
			$adb = self::getAdbConnection ($adb, $adbConnection);

			require_once ('vtlib/Vtiger/Module.php');
			Vtiger_Module::fireEvent ($moduleName, Vtiger_Module::EVENT_MODULE_POSTINSTALL);
		}

		public static function parseModuleFile (PearDatabase $adbConnection, $moduleName) {
			if (empty ($moduleName)) {
				throw new VtigerUtilsException (VtigerUtilsException::ERROR_ENTITY_EMPTY_MODULE_NAME);
			}

			// TODO: quitar cuando se repare el módulo Calendar
			$moduleFilePath = in_array ($moduleName, array ('Calendar', 'Events')) ? __DIR__ . '/../../../modules/Calendar/Activity.php' : __DIR__ . "/../../../modules/{$moduleName}/{$moduleName}.php";
			if (file_exists ($moduleFilePath)) {
				global $adb;
				$adb = self::getAdbConnection ($adb, $adbConnection);

				require_once ($moduleFilePath);
				$moduleEntity = in_array ($moduleName, array ('Calendar', 'Events')) ? new Activity () : new $moduleName ();
				$mainTable    = array (
					'name'     => isset ($moduleEntity->table_name) ? $moduleEntity->table_name : "vtiger_{$moduleName}",
					'idcolumn' => isset ($moduleEntity->table_index) ? $moduleEntity->table_index : "{$moduleName}id",
				);

				if (isset ($moduleEntity->tab_name) && (isset ($moduleEntity->tab_name_index))) {
					$extraTables = array ();
					foreach ($moduleEntity->tab_name as $tableName) {
						if (in_array ($tableName, array ('vtiger_crmentity', $moduleEntity->table_name))) {
							continue;
						}
						$extraTables [] = array (
							'name'     => $tableName,
							'idcolumn' => $moduleEntity->tab_name_index [ $tableName ],
						);
					}
				} else {
					$extraTables = array (
						array ('name' => "vtiger_{$moduleName}cf", 'idcolumn' => "{$moduleName}id"),
					);
				}
			} else {
				$mainTable   = array (
					'name'     => "vtiger_{$moduleName}",
					'idcolumn' => "{$moduleName}id",
				);
				$extraTables = array (
					array ('name' => "vtiger_{$moduleName}cf", 'idcolumn' => "{$moduleName}id"),
				);
			}

			return array (
				'maintable'   => $mainTable,
				'extratables' => $extraTables,
			);
		}

		public static function setUpWebServices (PearDatabase $adb, $moduleName) {
			$handlerPath  = 'include/Webservices/VtigerModuleOperation.php';
			$handlerClass = 'VtigerModuleOperation';
			$result       = $adb->pquery ('SELECT id FROM vtiger_ws_entity WHERE name=? AND handler_path=? AND handler_class=?', array ($moduleName, $handlerPath, $handlerClass));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$entityId = $adb->getUniqueID ('vtiger_ws_entity');
				$adb->pquery (
					'INSERT INTO vtiger_ws_entity (id, name, handler_path, handler_class, ismodule) VALUES (?, ?, ?, ?, ?)',
					array ($entityId, $moduleName, $handlerPath, $handlerClass, 1)
				);
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			$result = null;
		}

		public static function tearDownWebServices (PearDatabase $adb, $moduleName) {
			$adb->pquery ('DELETE FROM vtiger_ws_entity WHERE name=?', array ($moduleName));
		}

	}
