<?php
	require_once ('include/utils/PackageExporter.class.php');
	require_once ('include/utils/PackageImporter.class.php');
	require_once ('vtlib/Vtiger/Module.php');

	abstract class InstanceUtils {

		public static function exportModule ($moduleName) {
			$module = Vtiger_Module::getInstance ($moduleName);
			if (!$module) {
				return null;
			}
			$exporter = new PackageExporter ();
			$exporter->export ($module);
			return $exporter;
		}

		public static function importModule ($exportedModuleFilePath) {
			if (!file_exists ($exportedModuleFilePath)) {
				return;
			}
			$importer = new PackageImporter (dirname ($exportedModuleFilePath));
			$importer->import ($exportedModuleFilePath);
		}

		public static function importRelatedLists ($exportedModuleFilePath) {
			if (!file_exists ($exportedModuleFilePath)) {
				return null;
			}
			$importer = new PackageImporter (dirname ($exportedModuleFilePath));
			$importer->importRelatedLists ($exportedModuleFilePath);
			return $importer;
		}

		private static function createModule ($instanceName, $moduleName) {
			global $adb;
			$adb = AdbManager::getInstance ()->getMasterAdb ();
			InstanceUtils::exportModule ($moduleName);

			$tempFolder = sys_get_temp_dir () . '/vtlib';
			$adb        = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			InstanceUtils::importModule ("$tempFolder/$moduleName.zip");
			InstanceUtils::importRelatedLists ("$tempFolder/$moduleName.zip");

			// Esta operación debe ejecutarse desde la plataforma, devolver la variable $adb a su estado original
			$adb = AdbManager::getInstance ()->getMasterAdb ();
		}

		public static function saveModule ($instanceName, $moduleName) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE tablabel=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE tablabel=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				self::createModule ($instanceName, $moduleName);
			} else {
				self::updateModule ($instanceName, $moduleName);
			}


		}

	}
