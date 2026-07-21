<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/InstanceUtils.class.php');

	class InstanceUpdater {
		private static $UPDATER = null;

		private function addModule ($instanceName, $moduleName) {
			global $adb;
			$adb    = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE tablabel=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$adb = AdbManager::getInstance ()->getMasterAdb ();
				InstanceUtils::exportModule ($moduleName);

				$tempFolder = sys_get_temp_dir () . '/vtlib';
				$adb        = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
				InstanceUtils::importModule ("$tempFolder/$moduleName.zip");
				InstanceUtils::importRelatedLists ("$tempFolder/$moduleName.zip");
			}

			// Esta operación debe ejecutarse desde la plataforma, devolver la variable $adb a su estado original
			$adb = AdbManager::getInstance ()->getMasterAdb ();
		}

		public function createApplication ($instanceName, $applicationId) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$application = $adb->fetchByAssoc ($result);

			$result = $adb->pquery (
				'SELECT cat.tabid, t.tablabel FROM vtiger_configapps_tab cat INNER JOIN vtiger_tab t ON t.tabid=cat.tabid WHERE config_applicationsid=?',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$applicationModules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applicationModules [] = $row;
			}

			$adb = AdbManager::getInstance ()->getSourceInstanceAdb ($instanceName);
			$adb->pquery (
				'INSERT INTO vtiger_config_applications (app_code, app_name, app_descripcion, app_status, app_price, app_category, app_url) VALUES (?, ?, ?, ?, ?, ?, ?)',
				array ($application ['app_code'], $application ['app_name'], $application ['app_descripcion'], $application ['app_status'], $application ['app_price'], $application ['app_category'], $application ['app_url'])
			);
			$applicationId = $adb->getLastInsertID ();

			foreach ($applicationModules as $applicationModule) {
				$this->addModule ($instanceName, $applicationModule ['tablabel']);
				$adb->pquery (
					'INSERT INTO vtiger_configapps_tab (config_applicationsid, tabid)
					SELECT ?, tabid FROM vtiger_tab WHERE tablabel=?',
					array ($applicationId, $applicationModule ['tablabel'])
				);
			}
		}

		public function updateApplication ($instanceName, $applicationId) {
			$adb = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$application = $adb->fetchByAssoc ($result);

			$result = $adb->pquery (
				'SELECT cat.tabid, t.tablabel FROM vtiger_configapps_tab cat INNER JOIN vtiger_tab t ON t.tabid=cat.tabid WHERE config_applicationsid=?',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$applicationModules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applicationModules [] = $row;
			}

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$result = $adb->pquery (
				'SELECT * FROM vtiger_config_applications WHERE app_code=?',
				array ($application ['code'])
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$adb->pquery (
					'INSERT INTO vtiger_config_applications SET app_code=?, app_name=?, app_descripcion=?, app_status=?, app_category=?, app_url=? WHERE config_applicationsid=?',
					array ($application ['app_code'], $application ['app_name'], $application ['app_descripcion'], $application ['app_status'], $application ['app_category'], $application ['app_url'], $applicationId)
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_config_applications SET app_code=?, app_name=?, app_descripcion=?, app_status=?, app_category=?, app_url=? WHERE config_applicationsid=?',
					array ($application ['app_code'], $application ['app_name'], $application ['app_descripcion'], $application ['app_status'], $application ['app_category'], $application ['app_url'], $applicationId)
				);
			}


			$adb->pquery ('DELETE FROM vtiger_configapps_tab WHERE config_applicationsid=?', array ($applicationId));

			foreach ($applicationModules as $applicationModule) {
				$this->addModule ($instanceName, $applicationModule ['tablabel']);
				$adb->pquery (
					'INSERT INTO vtiger_configapps_tab (config_applicationsid, tabid)
					SELECT ?, tabid FROM vtiger_tab WHERE tablabel=?',
					array ($applicationId, $applicationModule ['tablabel'])
				);
				$adb->pquery ('UPDATE vtiger_tab SET presence=0 WHERE tablabel=?', array ($applicationModule ['tablabel']));
			}
		}

		public function updateInstanceModules ($instanceName) {

		}

		public static function getUpdater () {
			if (self::$UPDATER == null) {
				self::$UPDATER = new InstanceUpdater ();
			}
			return self::$UPDATER;
		}
	}