<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/platzilla/Objects/Application.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ApplicationManager {
		/** @var ApplicationManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param Application $application
		 */
		private function deleteUnusedModulesFromProfile ($application) {
			$modules = $application->getModules ();

			$processedModuleIds = array ();
			foreach ($modules as $module) {
				$processedModuleIds [] = $module->getId ();
			}

			$applicationId = $application->getId ();
			$questionMarks = str_repeat ('?, ', (count ($processedModuleIds) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT
					cat.*,
					t.name AS modulename
				FROM
					vtiger_configapps_tab cat
					INNER JOIN vtiger_tab t ON t.tabid=cat.tabid
				WHERE
					cat.config_applicationsid=? AND
					cat.tabid NOT IN ({$questionMarks})",
				array_merge (array ($applicationId), $processedModuleIds)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$pm      = ProfileManager::getInstance ($this->adb);
				$profile = $application->getProfile ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$pm->deleteProfilesByModuleName ($profile, $row ['modulename']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param integer $applicationId
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Module[]|null
		 */
		private function fetchApplicationModules ($applicationId, $headersOnly = false, $excludedModuleNames = null) {
			if (!empty ($excludedModuleNames)) {
				$questionMarks = str_repeat ('?, ', (count ($excludedModuleNames) - 1)) . '?';
				$joinClause    = "AND t.name NOT IN ({$questionMarks})";
				$arguments     = $excludedModuleNames;
			} else {
				$joinClause = '';
				$arguments  = array ();
			}

			$result = $this->adb->pquery (
				"SELECT t.*  FROM vtiger_configapps_tab cat INNER JOIN vtiger_tab t ON t.tabid=cat.tabid {$joinClause} WHERE config_applicationsid=?",
				array_merge ($arguments, array ($applicationId))
			);
			if ($this->adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$modules [] = ModuleManager::getInstance ($this->adb)->fetchModule ($row ['name'], $headersOnly);
				}
			} else {
				$modules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $modules;
		}

		/**
		 * @param string $applicationName
		 * @param integer $profileId
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Profile|null
		 */
		private function fetchProfile ($applicationName, $profileId, $headersOnly = false, $excludedModuleNames = null) {
			if (!empty ($profileId)) {
				return ProfileManager::getInstance ($this->adb)->fetchProfileById ($profileId, $headersOnly, $excludedModuleNames);
			} else {
				return ProfileManager::getInstance ($this->adb)->fetchProfile ($applicationName, $headersOnly, $excludedModuleNames);
			}
		}

		/**
		 * @param string $applicationCode
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Profile[]|null
		 */
		private function fetchSecondaryProfiles ($applicationCode, $headersOnly = false, $excludedModuleNames = null) {
			return ProfileManager::getInstance ($this->adb)->fetchSecondaryProfiles ($applicationCode, $headersOnly, $excludedModuleNames);
		}

		/**
		 * @param array $row
		 * @param boolean $moduleHeadersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Application
		 */
		private function fillApplication ($row, $moduleHeadersOnly, $excludedModuleNames) {
			return Application::getInstance ()
				->setCategoryId (intval ($row ['app_category']))
				->setCode ($row ['app_code'])
				->setDescription ($row ['app_descripcion'])
				->setId (intval ($row ['config_applicationsid']))
				->setModules ($this->fetchApplicationModules ($row ['config_applicationsid'], $moduleHeadersOnly, $excludedModuleNames))
				->setName ($row ['app_name'])
				->setPrice (doubleval ($row ['app_price']))
				->setProfile ($this->fetchProfile ($row ['app_name'], $row ['app_profile'], false, $excludedModuleNames))
				->setSecondaryProfiles ($this->fetchSecondaryProfiles ($row ['app_code'], false, $excludedModuleNames))
				->setStatus ($row ['app_status'])
				->setUrl ($row ['app_url']);
		}

		/**
		 * @param array $newModules
		 * @param array $oldModuleNames
		 * @param array $newApplicationModulesData
		 */
		private function resetModuleReferences ($newModules, $oldModuleNames, $newApplicationModulesData) {
			/** @var Module $newModule */
			foreach ($newModules as $newModule) {
				$fields = $newModule->getFields ();
				if (empty ($fields)) {
					continue;
				}

				foreach ($fields as $field) {
					if ($field->getUiType () != FieldInterface::UI_TYPE_MODULE_REFERENCE) {
						continue;
					}

					$references = $field->getModuleReferences ();
					if (empty ($references)) {
						continue;
					}

					$newReferences = array ();
					foreach ($references as $reference) {
						$referencedModuleName = $reference->getReferencedModuleName ();
						if (in_array ($referencedModuleName, $oldModuleNames)) {
							$reference->setReferencedModuleName ($newApplicationModulesData [ $referencedModuleName ]['newmodulename']);
						}
						$newReferences [] = $reference;
					}
					$field->setModuleReferences ($newReferences);
					FieldManager::getInstance ($this->adb)->saveField ($field);
				}
			}
		}

		/**
		 * @param Application $application
		 */
		private function saveApplicationModules ($application) {
			$modules = $application->getModules ();

			$applicationId      = intval ($application->getId ());
			$processedModuleIds = array ();
			foreach ($modules as $module) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($module->getName ()));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					continue;
				}

				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleId = intval ($row ['tabid']);
				DatabaseUtils::closeResult ($result);
				$result = null;

				$result = $this->adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid=?', array ($applicationId, $moduleId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_configapps_tab (config_applicationsid, tabid) VALUES (?, ?)',
						array ($applicationId, $moduleId)
					);
				}
				$processedModuleIds [] = $moduleId;
				DatabaseUtils::closeResult ($result);
				$result = null;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedModuleIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid NOT IN ({$questionMarks})",
				array_merge (array ($applicationId), $processedModuleIds)
			);
		}

		/**
		 * @param Application $application
		 *
		 * @return Profile
		 */
		private function saveProfile ($application) {
			$profile = $application->getProfile ();

			$pm = ProfileManager::getInstance ($this->adb);
			if (empty ($profile)) {
				return $pm->createDefaultProfile ($application->getName (), $application->getDescription ());
			} else {
				$this->deleteUnusedModulesFromProfile ($application);
				return $pm->saveProfile ($profile);
			}
		}

		/**
		 * @param Application $application
		 */
		private function saveSecondaryProfiles ($application) {
			$profiles = $application->getSecondaryProfiles ();
			if (empty ($profiles)) {
				return;
			}

			$pm = ProfileManager::getInstance ($this->adb);
			foreach ($profiles as $profile) {
				$pm->saveProfile ($profile);
			}
		}

		/**
		 * @param Application $application
		 * @param boolean $saveModules
		 */
		private function saveModules ($application, $saveModules = false) {
			if (!$saveModules) {
				return;
			}

			$mm      = ModuleManager::getInstance ($this->adb);
			$modules = $application->getModules ();
			foreach ($modules as $module) {
				$mm->saveModule ($module);
			}
		}

		/**
		 * @param Application $application
		 * @param boolean $validateModules
		 *
		 * @throws ApplicationException
		 */
		private function validate ($application, $validateModules = false) {
			if ((empty ($application)) || (!($application instanceof Application))) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY);
			}

			$application->validate ();
			$this->validateCategory ($application);
			$this->validateCode ($application);
			$this->validateProfile ($application);
			if ($validateModules) {
				$this->validateModules ($application);
			}
		}

		/**
		 * @param Application $application
		 *
		 * @throws ApplicationException
		 */
		private function validateCategory ($application) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_category_apps WHERE catappid=?', array ($application->getCategoryId ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new ApplicationException (ApplicationException::ERROR_APPLICATION_INVALID_CATEGORY_ID);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param Application $application
		 *
		 * @throws ApplicationException
		 */
		private function validateCode ($application) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ($application->getCode ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row           = $this->adb->fetchByAssoc ($result, -1, false);
				$applicationId = $application->getId ();
				if ((empty ($applicationId)) || ($row ['config_applicationsid'] != $applicationId)) {
					$e = new ApplicationException (ApplicationException::ERROR_APPLICATION_DUPLICATE_CODE);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param Application $application
		 */
		private function validateModules ($application) {
			$mm      = ModuleManager::getInstance ($this->adb);
			$modules = $application->getModules ();
			foreach ($modules as $module) {
				$mm->validateModule ($module);
			}
		}

		/**
		 * @param Application $application
		 *
		 * @throws ApplicationException
		 */
		private function validateProfile ($application) {
			$applicationId = $application->getId ();
			if (empty ($applicationId)) {
				return;
			}

			$profile   = $application->getProfile ();
			$profileId = isset ($profile) ? $profile->getId () : null;
			if ((empty ($profile)) || (empty ($profileId))) {
				throw new ApplicationException (ApplicationException::ERROR_APPLICATION_EMPTY_PROFILE);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profileid=?', array ($profileId));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new ApplicationException (ApplicationException::ERROR_APPLICATION_INVALID_PROFILE_ID);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param Application $application
		 * @param boolean $isInstance
		 */
		public function deleteApplication ($application, $isInstance = false) {
			$code = isset ($application) ? $application->getCode () : null;
			if ((empty ($application)) || (!($application instanceof Application)) || (empty ($code))) {
				return;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ($code));
			if ($this->adb->num_rows ($result) > 0) {
				$row           = $this->adb->fetchByAssoc ($result, -1, false);
				$applicationId = intval ($row ['config_applicationsid']);
				$pm            = ProfileManager::getInstance ($this->adb);
				$profile       = $pm->fetchProfile ($row ['app_name'], $row ['app_profile']);

				$this->adb->startTransaction ();
				ChartManager::getInstance ($this->adb)->deleteChartApplicationCode ($code);
				$this->adb->pquery ('DELETE FROM vtiger_configapps_tab WHERE config_applicationsid=?', array ($applicationId));
				$this->adb->pquery ('DELETE FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
				$pm->deleteProfile ($profile);
				if ($isInstance) {
					ModuleManager::getInstance ($this->adb)->updateModulesPresence ();
				}
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param Application $oldApplication
		 * @param string $newApplicationCode
		 * @param string $newApplicationName
		 * @param string $newApplicationDescription
		 * @param array $newApplicationModulesData
		 *
		 * @throws ApplicationException
		 * @throws Exception
		 */
		public function duplicateApplication ($oldApplication, $newApplicationCode, $newApplicationName, $newApplicationDescription, $newApplicationModulesData) {
			$this->validate ($oldApplication, true);

			$mm  = ModuleManager::getInstance ($this->adb);
			$mrm = ModuleRelationshipManager::getInstance ($this->adb);
			try {
				$this->adb->startTransaction ();
				$oldModuleNames = array_keys ($newApplicationModulesData);
				$newModules     = array ();
				foreach ($newApplicationModulesData as $oldModuleName => $newApplicationModuleData) {
					$oldModule     = $mm->fetchModule ($oldModuleName);
					$newModules [] = $mm->duplicateModule ($oldModule, $newApplicationModuleData ['newmodulename'], $newApplicationModuleData ['newmoduletitle'], $newApplicationModuleData ['newmenulabel']);
				}

				// Validar que los campos de tipo ModuleReference relacionados con la aplicación antigua apunten al nuevo módulo
				$this->resetModuleReferences ($newModules, $oldModuleNames, $newApplicationModulesData);

				foreach ($newApplicationModulesData as $oldModuleName => $newApplicationModuleData) {
					$relationships = $mrm->fetchRelationships ($oldModuleName);
					if (empty ($relationships)) {
						continue;
					}

					foreach ($relationships as $relationship) {
						$relationship->setModuleName ($newApplicationModuleData ['newmodulename']);
						if (in_array ($relationship->getRelatedModuleName (), $oldModuleNames)) {
							$relationship->setRelatedModuleName ($newApplicationModulesData [ $relationship->getRelatedModuleName () ]['newmodulename']);
						}
						$mrm->saveRelationship ($relationship);
					}
				}

				$oldApplicationProfile = ProfileManager::getInstance ($this->adb)->fetchProfile ($oldApplication->getName (), null);
				$newApplication        = $oldApplication->setProfile ($oldApplicationProfile)
					->duplicate (null, $newApplicationName, $newApplicationDescription)
					->setCode ($newApplicationCode)
					->setDescription ($newApplicationDescription)
					->setName ($newApplicationName)
					->setModules ($newModules);
				$this->saveApplication ($newApplication);
				$this->adb->completeTransaction ();
			} catch (Exception $e) {
				foreach ($newApplicationModulesData as $newApplicationModuleData) {
					$mrm->deleteRelationships ($newApplicationModuleData ['newmodulename']);
					$oldModule = $mm->fetchModule ($newApplicationModuleData ['newmodulename']);
					$mm->deleteModule ($oldModule);
				}
				throw $e;
			}
		}

		/**
		 * @param string $code
		 * @param boolean $moduleHeadersOnly
		 * @param string[]|null $excludedModuleNames
		 *
		 * @return Application|null
		 */
		public function fetchApplication ($code, $moduleHeadersOnly = false, $excludedModuleNames = null) {
			if (empty ($code)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ($code));
			if ($this->adb->num_rows ($result) > 0) {
				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$application = $this->fillApplication ($row, $moduleHeadersOnly, $excludedModuleNames);
			} else {
				$application = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $application;
		}

		/**
		 * @param integer $applicationId
		 * @param boolean $moduleHeadersOnly
		 * @param string[]|null $excludedModuleNames
		 *
		 * @return Application|null
		 */
		public function fetchApplicationById ($applicationId, $moduleHeadersOnly = false, $excludedModuleNames = null) {
			if (empty ($applicationId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ($this->adb->num_rows ($result) > 0) {
				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$application = $this->fillApplication ($row, $moduleHeadersOnly, $excludedModuleNames);
			} else {
				$application = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $application;
		}

		/**
		 * @param string $name
		 * @param boolean $moduleHeadersOnly
		 * @param string[]|null $excludedModuleNames
		 *
		 * @return Application|null
		 */
		public function fetchApplicationByName ($name, $moduleHeadersOnly = false, $excludedModuleNames = null) {
			if (empty ($name)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_name=?', array ($name));
			if ($this->adb->num_rows ($result) > 0) {
				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$application = $this->fillApplication ($row, $moduleHeadersOnly, $excludedModuleNames);
			} else {
				$application = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $application;
		}

		/**
		 * @return Application[]|null
		 */
		public function fetchApplicationHeaders () {
			$result = $this->adb->query ('SELECT * FROM vtiger_config_applications');
			if ($this->adb->num_rows ($result) > 0) {
				$applications = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applications [] = Application::getInstance ()
						->setCategoryId (intval ($row ['app_category']))
						->setCode ($row ['app_code'])
						->setDescription ($row ['app_descripcion'])
						->setId (intval ($row ['config_applicationsid']))
						->setModules (null)
						->setName ($row ['app_name'])
						->setPrice (doubleval ($row ['app_price']))
						->setProfile (null)
						->setSecondaryProfiles (null)
						->setStatus ($row ['app_status'])
						->setUrl ($row ['app_url']);
				}
			} else {
				$applications = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $applications;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Application[]|null
		 */
		public function fetchApplicationHeadersByModuleName ($moduleName) {
			$result = $this->adb->pquery (
				'SELECT
					ca.*
				FROM
					vtiger_config_applications ca
					INNER JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=ca.config_applicationsid
					INNER JOIN vtiger_tab t ON t.tabid=cat.tabid AND t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$applications = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applications [] = Application::getInstance ()
						->setCategoryId (intval ($row ['app_category']))
						->setCode ($row ['app_code'])
						->setDescription ($row ['app_descripcion'])
						->setId (intval ($row ['config_applicationsid']))
						->setModules (null)
						->setName ($row ['app_name'])
						->setPrice (doubleval ($row ['app_price']))
						->setProfile (null)
						->setSecondaryProfiles (null)
						->setStatus ($row ['app_status'])
						->setUrl ($row ['app_url']);
				}
			} else {
				$applications = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $applications;
		}

		/**
		 * @param boolean $moduleHeadersOnly
		 * @param string[]|null $excludedModuleNames
		 *
		 * @return Application[]|null
		 */
		public function fetchApplications ($moduleHeadersOnly = false, $excludedModuleNames = null) {
			$result = $this->adb->query ('SELECT * FROM vtiger_config_applications');
			if ($this->adb->num_rows ($result) > 0) {
				$applications = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applications [] = $this->fillApplication ($row, $moduleHeadersOnly, $excludedModuleNames);
				}
			} else {
				$applications = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $applications;
		}

		/**
		 * @param Application $application
		 * @param boolean $saveModules
		 * @param boolean $isInstance
		 *
		 * @return Application
		 * @throws ApplicationException
		 */
		public function saveApplication ($application, $saveModules = false, $isInstance = false) {
			$this->validate ($application, $saveModules);

			$applicationId    = $application->getId ();
			$isNewApplication = empty ($applicationId) ? true : false;

			$this->adb->startTransaction ();
			$this->saveModules ($application, $saveModules);
			if ($isNewApplication) {
				$profile = $this->saveProfile ($application);
				$this->adb->pquery (
					'INSERT INTO vtiger_config_applications (config_applicationsid, app_code, app_name, app_descripcion, app_status, app_date_act, app_profile, app_price, app_category, app_url, settings_blocks_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($applicationId, $application->getCode (), $application->getName (), $application->getDescription (), $application->getStatus (), date ('Y-m-d'), $profile->getId (), $application->getPrice (), $application->getCategoryId (), $application->getUrl (), null)
				);
				$application->setId ($this->adb->getLastInsertID ())
					->setProfile ($profile);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_config_applications SET app_code=?, app_name=?, app_descripcion=?, app_status=?, app_price=?, app_category=?, app_url=?, settings_blocks_id=? WHERE config_applicationsid=?',
					array ($application->getCode (), $application->getName (), $application->getDescription (), $application->getStatus (), $application->getPrice (), $application->getCategoryId (), $application->getUrl (), null, $applicationId)
				);
				$this->saveProfile ($application);
			}
			$this->saveSecondaryProfiles ($application);
			$this->saveApplicationModules ($application);
			if ($isInstance) {
				ModuleManager::getInstance ($this->adb)->updateModulesPresence ();
			}
			$this->adb->completeTransaction ();
			return $application;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ApplicationManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
