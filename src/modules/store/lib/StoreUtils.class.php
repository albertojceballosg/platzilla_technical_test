<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');

	/**
	 * Class StoreUtils
	 *
	 * Clase abstracta (abstract class StoreUtils) que contiene las utilidades que brindan soporte a las funcionalidades del módulo store
	 */
	abstract class StoreUtils {

		/**
		 * Obtiene los modulos por el menu
		 *
		 * @param PearDatabase $adb
		 * @param string $menuLabel
		 *
		 * @return null|array
		 */
		private static function getModulesByMenu ($adb, $menuLabel) {
			$adbMaster = AdbManager::getInstance ()->getMasterAdb ();
			if (empty($menuLabel)) {
				return null;
			}

			$result = $adbMaster->pquery(
				'SELECT 
					t.tabid,
					t.name,
					t.tablabel,
					t.presence	
				FROM vtiger_parenttabrel ptr 
				INNER JOIN vtiger_tab t ON t.tabid = ptr.tabid
				LEFT JOIN  vtiger_parenttab pt ON pt.parenttabid = ptr.parenttabid
				WHERE 
					t.tabid IN (SELECT tabid FROM vtiger_configapps_tab WHERE 1) AND 
					pt.parenttab_label=?
				ORDER BY ptr.sequence',
				array ($menuLabel)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$moduleData = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['modulerel'] = self::getModulesRel ($adb, $row ['name']);
					$row ['presence']  = self::getPresence ($adb, $row ['name']);
					$moduleData [] = $row;
				}
				if ($result instanceof ADORecordSet) {
					$result->Close ();
					unset ($result);
				}
				return $moduleData;
			}
			return null;
		}

		/**
		 * Obtiene los modulos relacionados
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return null|string
		 */
		private static function getModulesRel ($adb, $moduleName) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT 
					t.tablabel
				FROM 
					`vtiger_fieldmodulerel` fm
					INNER JOIN `vtiger_tab` t ON t.name = fm.module
				WHERE 
				`relmodule`=?',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [] = trim ($row ['tablabel']);
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return join (';', $modules);
		}

		/**
		 * Obtiene la presencia del modulo
		 *
		 * @param $adb
		 * @param $moduleName
		 *
		 * @return null
		 */
		private static function getPresence ($adb, $moduleName) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $adb->pquery ('SELECT presence FROM vtiger_tab WHERE name=?', array($moduleName));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row   = $adb->fetchByAssoc($result, -1, false);
				$result->Close();
				unset ($result);
				return $row ['presence'];
			}
			return null;
		}

		/**
		 * Buscar las categorias de las aplicaciones
		 *
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchApplicationCategories ($adb) {
			$adbMaster = AdbManager::getInstance ()->getMasterAdb ();

			$result = $adbMaster->pquery (
				'SELECT 
					ca.*,
					pt.parenttabid,
					cas.app_code 
				 FROM 
				 	vtiger_category_apps ca 
				 INNER JOIN vtiger_parenttab pt ON pt.parenttab_label = ca.parenttab_label
				 LEFT JOIN vtiger_config_applications cas ON cas.app_category = ca.catappid
				 WHERE 
				 	pt.visible=? AND 
				 	pt.parenttab_label <>? 
				 	AND status=? 
				 ORDER BY pt.sequence',
				array (0, 'Revisión', 'Activa')
			);
			if (($result) && ($adbMaster->num_rows ($result) > 0)) {
				$categories = array ();
				while ($row = $adbMaster->fetchByAssoc ($result, -1, false)) {
					$row ['modules'] = self::getModulesByMenu ($adb, $row ['parenttab_label']);
					$categories [ $row ['catappid'] ] = $row;
				}
			} else {
				$categories = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return $categories;
		}

		/**
		 * Obtener las aplicaciones por su categoria
		 *
		 * @return Application[][]|null
		 */
		public static function fetchApplicationsByCategory () {
			$adb          = AdbManager::getInstance ()->getMasterAdb ();
			$applications = ApplicationManager::getInstance ($adb)->fetchApplicationHeaders ();
			if (empty ($applications)) {
				return null;
			}

			$applicationsByCategory = array ();
			foreach ($applications as $application) {
				if ($application->getStatus () != Application::STATUS_ACTIVE) {
					continue;
				}
				$applicationCode                                                     = $application->getCode ();
				$applicationCategory                                                 = $application->getCategoryId ();
				$applicationsByCategory [ $applicationCategory ][ $applicationCode ] = $application;
			}
			return $applicationsByCategory;
		}

		/**
		 * Obtener suscripciones de las aplicaciones por su codigo
		 *
		 * @param string $instanceCode
		 *
		 * @return ApplicationSubscription[]|null
		 */
		public static function fetchApplicationSubscriptionsByCode ($instanceCode) {
			$adb                      = AdbManager::getInstance ()->getMasterAdb ();
			$applicationSubscriptions = ApplicationSubscriptionManager::getInstance ($adb)->fetchSubscriptions ($instanceCode);
			if (empty ($applicationSubscriptions)) {
				return null;
			}

			$applicationSubscriptionsByCode = array ();
			foreach ($applicationSubscriptions as $applicationSubscription) {
				$applicationCode                                     = $applicationSubscription->getApplicationCode ();
				$applicationSubscriptionsByCode [ $applicationCode ] = $applicationSubscription;
			}
			return $applicationSubscriptionsByCode;
		}

		/**
		 * Obtiene el catalogo de las aplicaciones
		 *
		 * @return array|null
		 */
		public static function getCatalogApplications () {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery (
				'SELECT DISTINCT
					ca.*
				FROM
					vtiger_config_applications ca
					INNER JOIN vtiger_category_apps cat ON cat.catappid=ca.app_category
				WHERE
					ca.app_status=?
				ORDER BY
					cat.catappid',
				array (ApplicationInterface::STATUS_ACTIVE)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applications [] = $row;
			}
			return $applications;
		}

		/**
		 * Obtiene una aplicación del catálogo, incluyendo precios e impuestos calculados según la tarifa que le corresponda
		 *
		 * @param string $applicationId
		 *
		 * @return array|null
		 */
		public static function getCatalogApplicationById ($applicationId) {
			if (empty ($applicationId)) {
				return null;
			}
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery (
				'SELECT DISTINCT
					ca.*
				FROM
					vtiger_config_applications ca
				WHERE
					ca.config_applicationsid=? AND
					ca.app_status=?',
				array ($applicationId, ApplicationInterface::STATUS_ACTIVE)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		/**
		 * Verifica la instancia
		 *
		 * @param string $instanceCode
		 *
		 * @return boolean
		 */
		public static function isInstanceVerified ($instanceCode) {
			if (empty ($instanceCode)) {
				return true;
			}

			$adb      = AdbManager::getInstance ()->getMasterAdb ();
			$instance = PlatformManager::getInstance ($adb)->fetchInstance ($instanceCode, true);
			if (empty ($instance)) {
				return false;
			} else if ($instance->getStatus () == PlatformInstance::STATUS_VERIFIED) {
				return true;
			} else {
				$registrationDate = $instance->getRegistrationDate ();
				if (!empty ($registrationDate)) {
					$today    = date_create ()->setTime (0, 0, 0);
					$days = intval ($today->diff ($registrationDate)->format ('%a'));
				} else {
					$days = 2;
				}
				return $days < 2;
			}
		}

		/**
		 * @param integer $length
		 * @param boolean $isPin
		 *
		 * @return string
		 */
		public  static function randomPassword ($length, $isPin = false) {
			$alphabet    = (!$isPin) ? 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789' : '0123456789';
			$pass        = array ();
			$alphaLength = (strlen ($alphabet) - 1);
			for ($i = 0; $i < $length; $i++) {
				$n      = rand(0, $alphaLength);
				$pass[] = $alphabet [$n];
			}
			return implode ($pass);
		}

		/**
		 * Actualizar los modulos de la instancia
		 *
		 * @param PearDatabase $adb
		 * @param string $tabName
		 * @param string $appCode
		 * @param integer $presence
		 *
		 * @return string|null
		 */
		public static function updatePresenceModules ($adb, $tabName, $appCode, $presence) {
			$result = $adb->pquery ('SELECT presence, tabid FROM vtiger_tab WHERE name=?', array($tabName));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$tabId = $row ['tabid'];
				$adb->pquery ('UPDATE vtiger_tab SET presence=? WHERE tabid=?', array($presence, $tabId));
				$result->Close ();
				unset ($result);
				if ($presence == -1) {
					$result = $adb->pquery('SELECT tabid FROM vtiger_disabled_tab WHERE tabid=?', array ($tabId));
					if ((!$result) || ($adb->num_rows ($result) == 0)) {
						$adb->pquery('INSERT INTO vtiger_disabled_tab (tabid, app_code) VALUE (?, ?)',array ($tabId, $appCode));
					}
				} else {
					$adb->pquery ('DELETE FROM vtiger_disabled_tab WHERE tabid=?', array($tabId));
				}
				return 'ok';
			}
			return null;
		}

	}
