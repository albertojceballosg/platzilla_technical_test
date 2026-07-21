<?php
	require_once ('include/platzilla/Data/ProfilesHowToUseManager.php');
	require_once ('include/platzilla/Managers/HowToUseManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');

	/**
	 * Class HowToUseHelper
	 */
	abstract class HowToUseHelper {
		
		/**
		 * @param PearDatabase $adb
		 * @param array $buttons
		 *
		 * @return array
		 * @throws Exception
		 */
		private static function getActiveButtons ($adb, $buttons) {
			if(empty($buttons)) {
				return array ();
			}
			$statusButtons = array ();
			$masterViews = self::fetchMasterViews ($adb);
			foreach ($masterViews as $masterView) {
				if (in_array($masterView->getTabView(), $buttons)) {
					$statusButtons[ $masterView->getTabView() ] = 1;
				} else {
					$statusButtons[ $masterView->getTabView() ] = 0;
				}
			}
			return $statusButtons;
		}

		/**
		 * @param HowToUse $howToUse
		 * @param string|null $tabName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function getTabAndView ($howToUse, $tabName) {
			if (!$howToUse instanceof HowToUse) {
				return null;
			}

			if (!empty ($howToUse->getDefaultView()) && !empty($howToUse->getDefaultView ()->getMasterView ())) {
				$results             = array ();
				$masterDefView       = $howToUse->getDefaultView ()->getMasterView ();
				$results ['tab']     = $masterDefView->getTabView ();
				$results ['tabName'] = (empty($tabName)) ? $masterDefView->getViewName () : $tabName;
				foreach ($howToUse->getHowUseView() as $defaultView) {
					if (!empty ($defaultView->getMasterView ()) && !empty($defaultView->getMasterView ())) {
						$masterView = $defaultView->getMasterView ();
						if ($masterView->getViewName () == $results ['tabName']) {
							$results ['viewId']      = $defaultView->getRelatedId();
							$relatedViewsId          = $defaultView->getRelatedViews();
							$results ['relatedView'] = $relatedViewsId[ $results ['tabName'] ];
						}
						$results ['buttons'][]   = $masterView->getTabView();
					}
				}
			}
			return (isset($results)) ? $results : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $howUseId
		 *
		 * @throws Exception
		 */
		public static function deleteHowToUse ($adb, $howUseId) {
			if (empty($howUseId)) {
				throw new Exception ('Modo de uso no encontrado!');
			}
			$adb->pquery (
				'DELETE FROM vtiger_default_listview WHERE howuseid=?',
				array ($howUseId)
			);
			$adb->pquery (
				'DELETE FROM vtiger_how_use_views WHERE howuseid=?',
				array ($howUseId)
			);
			$adb->pquery (
				'DELETE FROM vtiger_how_use WHERE howuseid=?',
				array ($howUseId)
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $phaseId
		 *
		 * @throws Exception
		 */
		public static function deletePhase ($adb, $phaseId) {
			if (empty($phaseId)) {
				throw new Exception ('Perfil de uso no encontrado!');
			}
			$adb->pquery (
				'DELETE FROM vtiger_company_phases WHERE companyphasesid=?',
				array ($phaseId)
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $profileId
		 *
		 * @throws Exception
		 */
		public static function deleteProfileHowToUse ($adb, $profileId) {
			if (empty($profileId)) {
				throw new Exception ('Perfil de uso no encontrado!');
			}
			$adb->pquery (
				'DELETE FROM vtiger_how_use_profile WHERE howuseprofileid=?',
				array ($profileId)
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $sectorId
		 *
		 * @throws Exception
		 */
		public static function deleteSector ($adb, $sectorId) {
			if (empty($sectorId)) {
				throw new Exception ('Sector no encontrado!');
			}
			$adb->pquery (
				'DELETE FROM vtiger_company_sector WHERE companysectorid=?',
				array ($sectorId)
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $typeId
		 *
		 * @throws Exception
		 */
		public static function deleteType ($adb, $typeId) {
			if (empty($typeId)) {
				throw new Exception ('Sector no encontrado!');
			}
			$adb->pquery (
				'DELETE FROM vtiger_company_types WHERE companytypeid=?',
				array ($typeId)
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param boolean $headersOnly
		 *
		 * @return HowToUse[]|null
		 * @throws Exception
		 */
		public static function fetchAllHowToUse ($adb, $moduleName, $headersOnly = false) {
			$where    = (!empty($moduleName)) ? " AND status='ENABLED' AND tabname= '{$moduleName}' " : '';
			$howToUse = HowToUseManager::getInstance($adb);

			$result  = $adb->query ("SELECT * FROM vtiger_how_use WHERE 1 {$where}  ORDER BY tabname ASC");
			if ($adb->num_rows ($result) > 0) {
				$resultArray = array();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$resultArray [] = HowToUse::getInstance()
						->setDefaultView ((!$headersOnly) ? $howToUse->fetchDefaultViewByAdmin ($row['tabname']) : null)
						->setDescription ($row ['description'])
						->setDefault (($row ['isdefault']) ? true : false)
						->setHowUseView ((!$headersOnly) ? $howToUse->fetchHowUseViews ($row ['howuseid']) : null)
						->setId ($row ['howuseid'])
						->setHowUseName ($row ['howusename'])
						->setName ($row ['name'])
						->setStatus ($row ['status'])
						->setTabName ($row ['tabname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($resultArray)) ? $resultArray : null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return MasterView[]|null
		 * @throws Exception
		 */
		public static function fetchMasterViews ($adb) {
			$pos       = strpos ($adb->dbName, 'cleancap');
			$masterAdb = ($pos !== false) ? $adb : AdbManager::getInstance ()->getMasterAdb ();

			$result = $masterAdb->query ('SELECT * FROM vtiger_master_view WHERE 1');
			if ($masterAdb->num_rows ($result) > 0) {
				$masterView = array ();
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$masterView[] = MasterView::getInstance()
						->setId($row ['viewid'])
						->setName($row ['name'])
						->setTabView($row ['tabview'])
						->setViewName($row ['viewname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($masterView)) ? $masterView : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $viewName
		 *
		 * @return MasterView|null
		 * @throws Exception
		 */
		public static function fetchMasterViewsByName ($adb, $viewName) {
			$pos       = strpos ($adb->dbName, 'cleancap');
			$masterAdb = ($pos !== false) ? $adb : AdbManager::getInstance ()->getMasterAdb ();

			$result = $masterAdb->pquery ('SELECT * FROM vtiger_master_view WHERE viewname=?', array($viewName));
			if ($masterAdb->num_rows ($result) > 0) {
				$row = $masterAdb->fetchByAssoc ($result, -1, false);
				$masterView = MasterView::getInstance()
					->setId ($row ['viewid'])
					->setName ($row ['name'])
					->setTabView ($row ['tabview'])
					->setViewName ($row ['viewname']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($masterView)) ? $masterView : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $howUseId
		 *
		 * @return DefaultView|null
		 * @throws Exception
		 */
		public static function fetchDefaultViewByMode ($adb, $howUseId) {
			if (empty($howUseId)) {
				return null;
			}
			$howToUse = HowToUseManager::getInstance ($adb);
			$result   = $adb->pquery ('SELECT * FROM vtiger_default_listview WHERE howuseid=?', array ($howUseId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$defaultView = DefaultView::getInstance()
					->setHowUseId($row ['howuseid'])
					->setId ($row ['defaultid'])
					->setMasterView ($howToUse->fetchMasterView($row ['viewid']))
					->setModuleName($row ['tabname'])
					->setUserId ($row ['userid']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($defaultView)) ? $defaultView : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer|null $idModeSelected
		 * @param string|null $tabName
		 * @param boolean $headersOnly
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getDefaultMode ($adb, $moduleName, $idModeSelected, $tabName = null, $headersOnly = false) {
			if (empty($moduleName)) {
				return null;
			} else if (empty($idModeSelected)) {
				$where = 'isdefault=?';
				$idModeSelected = 1;
			} else {
				$where = 'howuseid=?';
			}

			$howToUse = HowToUseManager::getInstance ($adb);
			$result = $adb->pquery ("SELECT * FROM vtiger_how_use WHERE tabname=? AND {$where} AND status=?", array($moduleName, $idModeSelected, 'ENABLED'));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$howToUseObject = HowToUse::getInstance()
					->setDefaultView ((!$headersOnly) ? $howToUse->fetchDefaultViewByModeId ($row['howuseid']) : null)
					->setDescription ($row ['description'])
					->setDefault (($row ['isdefault']) ? true : false)
					->setHowUseView ((!$headersOnly) ? $howToUse->fetchHowUseViews ($row ['howuseid']) : null)
					->setId ($row ['howuseid'])
					->setName ($row ['name'])
					->setStatus ($row ['status'])
					->setTabName ($row ['tabname']);

				$resultArray                   = self::getTabAndView ($howToUseObject, $tabName);
				$resultArray ['statusButtons'] = self::getActiveButtons($adb, $resultArray['buttons']);
				$resultArray ['howUseId']      = $howToUseObject->getId ();
			} else {
				$resultArray ['howUseId']      = null;
				$resultArray ['statusButtons'] = self::getActiveButtons ($adb, array ('list'));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($resultArray)) ? $resultArray : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $howUseId
		 * @param boolean $headersOnly
		 *
		 * @throws Exception
		 * @return HowToUse|null
		 */
		public static function getHowToUseById ($adb, $howUseId, $headersOnly = false) {
			if (empty($howUseId)) {
				return null;
			}
			$howToUse = HowToUseManager::getInstance ($adb);
			$result   = $adb->pquery ('SELECT * FROM vtiger_how_use WHERE howuseid=?', array ($howUseId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$howToUse = HowToUse::getInstance()
					->setDefaultView ((!$headersOnly) ? self::fetchDefaultViewByMode ($adb, $row ['howuseid']) : null)
					->setDescription ($row ['description'])
					->setDefault (($row ['isdefault']) ? true : false)
					->setHowUseView ((!$headersOnly) ? $howToUse->fetchHowUseViews ($row ['howuseid']) : null)
					->setId ($row ['howuseid'])
					->setHowUseName ($row ['howusename'])
					->setName ($row ['name'])
					->setStatus ($row ['status'])
					->setTabName ($row ['tabname']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($howToUse)) ? $howToUse : null;
		}

		/**
		 * @param string $label
		 * @param integer $length
		 *
		 * @return string
		 */
		public static function getHowToUseName ($label, $length = 15) {
			$label = str_replace (
				array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$label
			);
			$label = str_replace (
				array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$label
			);
			$label = str_replace (
				array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$label
			);
			$label = str_replace (
				array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$label
			);
			$label = str_replace (
				array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$label
			);
			$label = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$label
			);
			$label = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'',
				$label
			);
			$label    = substr (trim (strtoupper ($label)), 0, $length);
			$randomId = rand (1000, 9999);
			return $label . $randomId;
		}

		/**
		 * @param $adb
		 *
		 * @return Module[]
		 */
		public static function getAvailableModules ($adb) {
			$excludedModuleNames = array (
				'Dashboard',
				'Home',
				'Documents',
				'Calendar',
				'Events',
				'Reports',
				'Users',
				'ConfigEditor',
				'Import',
				'Integration',
				'ModTracker',
				'RecycleBin',
				'Tooltip',
				'emailmanager',
				'notifications',
				'todotasks',
				'emailsreceived',
				'emailssent',
				'instances',
				'PlatformPerformance',
				'OAuth2Manager',
				'News',
				'panelusuarios',
				'graficosgenerales',
				'store',
				'admin_widgets',
				'reportmanager',
				'backgroundtasks',
				'instancesdatasharing',
				'Courses',
				'Walkthrough',
				'notification_center',
				'Taxes',
				'Products',
				'Pricebooks',
				'webmail',
				'calculated_fields',
				'historymanager',
				'indicatorspanel',
				'systemalerts',
				'etapas_proyecto',
				'grid_view',
				'operating_modes',
				'materials',
				'how_use',
			);
			$allModules = ModuleManager::getInstance($adb)->fetchModules(true,$excludedModuleNames);
			$availableModules = array();
			foreach ($allModules as $module) {
				if ($module->getPresence() == -1) {
					continue;
				}
				$availableModules [] = $module;
			}
			return $availableModules;
		}

		/**
		 * @param PearDatabase $adb
		 * @param CompanyPhase $companyPhase
		 *
		 * @throws Exception
		 * @throws ProfilesHowToUseException
		 */
		public static function saveCompanyPhase ($adb, $companyPhase) {
			if (empty ($companyPhase) || (!$companyPhase instanceof CompanyPhase)) {
				throw new Exception('Datos de face incompletos o en formato equivocado');
			}
			$companyPhase->validate();
			if (empty($companyPhase->getId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_company_phases (name, description) VALUES (?, ?)',
					array ($companyPhase->getName (), $companyPhase->getDescription ())
				);
				$companyPhase->setId($adb->getLastInsertID());
			} else {
				$adb->pquery(
					'UPDATE vtiger_company_phases SET name=?, description=? WHERE companyphasesid=?',
					array ($companyPhase->getName(), $companyPhase->getDescription(), $companyPhase->getId())
				);
			}
		}

		/**
		 * @param $adb
		 * @param $companySector
		 *
		 * @throws Exception
		 * @throws ProfilesHowToUseException
		 */
		public static function saveCompanySector ($adb, $companySector) {
			if (empty ($companySector) || (!$companySector instanceof CompanySector)) {
				throw new Exception('Datos del sector incompletos o en formato equivocado');
			}
			$companySector->validate();
			if (empty($companySector->getId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_company_sector (name, description) VALUES (?, ?)',
					array ( $companySector->getName (), $companySector->getDescription ())
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_company_sector SET name=?, description=? WHERE companysectorid=?',
					array ($companySector->getName (), $companySector->getDescription (), $companySector->getId ())
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param CompanyType $companyType
		 *
		 * @throws Exception
		 * @throws ProfilesHowToUseException
		 */
		public static function saveCompanyType ($adb, $companyType) {
			if (empty ($companyType) || (!$companyType instanceof CompanyType)) {
				throw new Exception('Imposible guardar el Tipo de empresa, datos del tipo de empresa incompletos o en formato equivocado');
			}
			$companyType->validate();
			if (empty ($companyType->getId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_company_types (name, description) VALUES (?, ?)',
					array ($companyType->getName (), $companyType->getDescription ())
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_company_types SET name=?, description=? WHERE companytypeid=?',
					array ($companyType->getName (), $companyType->getDescription (), $companyType->getId ())
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param ProfilesHowToUse $howToUseProfile
		 * @param array $howUseNames
		 *
		 * @throws Exception
		 */
		public static function saveHowToUseProfile ($adb, $howToUseProfile, $howUseNames) {
			if (empty ($howToUseProfile) || (!$howToUseProfile instanceof ProfilesHowToUse)) {
				throw new Exception('Datos del perfil incompletos o en formato equivocado');
			}

			$howUseNames = (is_array ($howUseNames)) ? json_encode ($howUseNames) : null;
			$typeIds     = json_encode ($howToUseProfile->getCompanyType());
			$sectorIds   = json_encode ($howToUseProfile->getCompanySector());
			$phaseIds    = json_encode ($howToUseProfile->getCompanyPhase ());
			$codeProfile = self::getHowToUseName ($howToUseProfile->getName(), 6);
			if (empty ($howToUseProfile->getId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_how_use_profile (profilecode, howusenames, companytypeid, companysectorid, companyphasesid, name, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array ($howToUseProfile->getCode (), $howUseNames, $typeIds, $sectorIds, $phaseIds, $howToUseProfile->getName(), $howToUseProfile->getDescription (), $howToUseProfile->getStatus ())
				);
				$id          = $adb->getLastInsertID();
				$codeProfile = "{$codeProfile}-{$id}";
				$adb->pquery ('UPDATE vtiger_how_use_profile SET profilecode=? WHERE howuseprofileid=?', 	array ($codeProfile, $id));
			} else {
				$adb->pquery (
					'UPDATE vtiger_how_use_profile SET howusenames=?, companytypeid=?, companysectorid=?, companyphasesid=?, name=?, description=?, status=? WHERE howuseprofileid=?',
					array ($howUseNames, $typeIds, $sectorIds, $phaseIds, $howToUseProfile->getName(), $howToUseProfile->getDescription (), $howToUseProfile->getStatus (), $howToUseProfile->getId ())
				);
			}
		}

	}
