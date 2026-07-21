<?php
	require_once ('include/platzilla/Data/ProfilesHowToUse.php');
	require_once ('include/platzilla/Managers/HowToUseManager.php');
	class ProfilesHowToUseManager {

		/** @var ProfilesHowToUseManager|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/** @var PearDatabase  */
		private $masterAdb;

		public function __construct (PearDatabase $adb) {
			$this->adb       = $adb;
			$pos             = strpos ($this->adb->dbName, 'cleancap');
			$this->masterAdb = ($pos !== false) ? $adb : AdbManager::getInstance ()->getMasterAdb ();
		}

		/**
		 * @param string $howUseNames
		 * @param string|null $moduleName
		 *
		 * @return HowToUse[]|null
		 * @throws Exception
		 */
		private function fetchHowToUseByNames ($howUseNames, $moduleName = null) {
			if (empty ($howUseNames)) {
				return null;
			}
			$howToUseArray = json_decode ($howUseNames, true);
			$howToUse      = array();
			foreach ($howToUseArray as $module => $name) {
				if (!empty ($moduleName)) {
					if ($module != $moduleName) {
						continue;
					}
				}
				$result = $this->adb->pquery('SELECT * FROM vtiger_how_use WHERE tabname=? AND howusename=?', array($module, $name));
				if ($this->adb->num_rows($result) > 0) {
					while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
						$howToUse[] = HowToUse::getInstance()
							->setId($row['howuseid'])
							->setHowUseName($row['howusename'])
							->setName($row['name'])
							->setDescription($row['description'])
							->setTabName($row['tabname'])
							->setDefault(($row['isdefault']) ? true : false)
							->setStatus($row['status']);
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (count ($howToUse)) ? $howToUse : null;
		}

		/**
		 * @return CompanyPhase[]|null
		 * @throws Exception
		 */
		public function fetchCompanyPhases () {
			$result = $this->masterAdb->query ('SELECT * FROM vtiger_company_phases WHERE 1');
			if ($this->masterAdb->num_rows ($result) > 0) {
				$companyPhases = array();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$companyPhases [] = CompanyPhase::getInstance()
						->setDescription ($row ['description'])
						->setId ($row ['companyphasesid'])
						->setName($row ['name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companyPhases)) ? $companyPhases : null;
		}

		/**
		 * @param integer $companyPhaseId
		 *
		 * @return CompanyPhase|null
		 * @throws Exception
		 */
		public function fetchCompanyPhaseById ($companyPhaseId) {
			if (empty ($companyPhaseId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_company_phases WHERE companyphasesid=?', array ($companyPhaseId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$companyPhase = CompanyPhase::getInstance()
					->setDescription ($row ['description'])
					->setId ($row ['companyphasesid'])
					->setName ($row ['name']);

			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companyPhase)) ? $companyPhase : null;
		}

		/**
		 * @return CompanySector[]|null
		 * @throws Exception
		 */
		public function fetchCompanySector () {
			$result = $this->masterAdb->query ('SELECT * FROM vtiger_company_sector WHERE 1');
			if ($this->masterAdb->num_rows ($result) > 0) {
				$companySectors = array();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$companySectors [] = CompanySector::getInstance()
						->setDescription ($row ['description'])
						->setId ($row ['companysectorid'])
						->setName($row ['name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companySectors)) ? $companySectors : null;
		}

		/**
		 * @param integer $companySectorId
		 *
		 * @return CompanySector|null
		 * @throws Exception
		 */
		public function fetchCompanySectorById ($companySectorId) {
			if (empty($companySectorId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_company_sector WHERE companysectorid=?', array ($companySectorId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$companySector = CompanySector::getInstance()
					->setDescription ($row ['description'])
					->setId ($row ['companysectorid'])
					->setName($row ['name']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companySector)) ? $companySector : null;
		}

		/**
		 * @return CompanyType[]|null
		 * @throws Exception
		 */
		public function fetchCompanyTypes () {
			$result = $this->masterAdb->query ('SELECT * FROM vtiger_company_types  WHERE 1');
			if ($this->masterAdb->num_rows ($result) > 0) {
				$companyTypes = array();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$companyTypes [] = CompanyType::getInstance()
						->setDescription ($row ['description'])
						->setId ($row ['companytypeid'])
						->setName($row ['name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companyTypes)) ? $companyTypes : null;
		}

		/**
		 * @param integer $companyTypeId
		 *
		 * @return CompanyType|null
		 * @throws Exception
		 */
		public function fetchCompanyTypeById ($companyTypeId) {
			if (empty($companyTypeId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_company_types WHERE companytypeid=?',
				array ($companyTypeId)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$companyType = CompanyType::getInstance()
					->setDescription ($row ['description'])
					->setId ($row ['companytypeid'])
					->setName ($row ['name']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companyType)) ? $companyType : null;
		}

		/**
		 * @param integer $sectorId
		 *
		 * @return CompanyType|null
		 * @throws Exception
		 */
		public function fetchCompanyTypesBySector ($sectorId) {
			if (empty($sectorId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT 
						ct.*,
						cs.name AS sector
					  FROM 
					  	vtiger_company_types ct 
					  INNER JOIN vtiger_company_sector cs ON cs.companysectorid = ct.companysectorid
					  WHERE companysectorid=?',
				array ($sectorId)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$companyTypes = array();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$companyTypes [] = CompanyType::getInstance()
						->setCompanySectorId ($row ['companysectorid'])
						->setCompanySectorName ($row ['sector'])
						->setDescription ($row ['description'])
						->setId ($row ['companytypeid'])
						->setName($row ['name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($companyTypes)) ? $companyTypes : null;
		}

		/**
		 * @param array $profileVariables
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchProfilesHowToUseData ($profileVariables) {
			$result = $this->masterAdb->pquery (
				'SELECT * FROM 	vtiger_how_use_profile  WHERE status=?',
				array('ENABLED')
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$profilesHowToUse = array();
				while ($row = $this->masterAdb->fetchByAssoc($result, -1, false)) {
					$typeIds   = json_decode ($row ['companytypeid']);
					$sectorIds = json_decode ($row ['companysectorid']);
					$phaseIds  = json_decode ($row ['companyphasesid']);
					if (
						(in_array ($profileVariables ['typeId'], $typeIds)) &&
						(in_array ($profileVariables ['sectorId'], $sectorIds)) &&
						(in_array ($profileVariables ['phaseId'], $phaseIds))
					) {
						$profilesHowToUse [] = array (
							'profileId'   => $row ['howuseprofileid'],
							'profileName' => $row ['name'],
							'description' => $row ['description'],
							'code'        => $row ['profilecode'],
						);
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($profilesHowToUse)) ? $profilesHowToUse : null;
		}

		/**
		 * @param boolean $headOnly
		 *
		 * @return ProfilesHowToUse[]|null
		 * @throws Exception
		 */
		public function fetchProfilesHowToUse ($headOnly = false) {
			$result = $this->masterAdb->query ('SELECT * FROM vtiger_how_use_profile WHERE 1');
			if ($this->masterAdb->num_rows ($result) > 0) {
				$profilesHowToUse = array();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$profilesHowToUse [] = ProfilesHowToUse::getInstance()
						->setCode ($row ['profilecode'])
						->setCompanyPhase ((!$headOnly) ? json_decode ($row ['companyphasesid']) : null)
						->setCompanySector ((!$headOnly) ? json_decode ($row ['companysectorid']) : null)
						->setCompanyType ((!$headOnly) ? json_decode ($row ['companytypeid']) : null)
						->setHowToUse ((!$headOnly) ? $this->fetchHowToUseByNames ($row ['howusenames']) : null)
						->setDescription ($row ['description'])
						->setId ($row ['howuseprofileid'])
						->setName ($row ['name'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($profilesHowToUse)) ? $profilesHowToUse : null;
		}

		/**
		 * @param string|null $profileCode
		 * @param string|null $moduleName
		 *
		 * @return null|ProfilesHowToUse
		 * @throws Exception
		 */
		public function fetchProfilesHowToUseByCode ($profileCode = null, $moduleName = null) {
			if (empty($profileCode)) {
				$profileCode = $this->getProfileCode ();
				if (empty($profileCode)) {
					return null;
				}
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_how_use_profile WHERE status=? AND profilecode=? ', array ('ENABLED', $profileCode));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$profileHowToUse = ProfilesHowToUse::getInstance()
					->setCode ($row ['profilecode'])
					->setCompanyPhase (json_decode ($row ['companyphasesid']))
					->setCompanySector (json_decode ($row ['companysectorid']))
					->setCompanyType (json_decode ($row ['companytypeid']))
					->setHowToUse ($this->fetchHowToUseByNames ($row ['howusenames'], $moduleName))
					->setDescription ($row ['description'])
					->setId ($row ['howuseprofileid'])
					->setName ($row ['name'])
					->setStatus ($row ['status']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($profileHowToUse)) ? $profileHowToUse : null;
		}

		/**
		 * @param integer $profileId
		 *
		 * @return null|ProfilesHowToUse
		 * @throws Exception
		 */
		public function fetchProfilesHowToUseById ($profileId) {
			if (empty($profileId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_how_use_profile WHERE howuseprofileid=?', array ($profileId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$profileHowToUse = ProfilesHowToUse::getInstance()
					->setCode ($row ['profilecode'])
					->setCompanyPhase (json_decode ($row ['companyphasesid']))
					->setCompanySector (json_decode ($row ['companysectorid']))
					->setCompanyType (json_decode ($row ['companytypeid']))
					->setHowToUse ($this->fetchHowToUseByNames($row ['howusenames']))
					->setDescription ($row ['description'])
					->setId ($row ['howuseprofileid'])
					->setName ($row ['name'])
					->setStatus ($row ['status']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($profileHowToUse)) ? $profileHowToUse : null;
		}

		/**
		 * @return string|null
		 *
		 * @throws Exception
		 */
		public function getProfileCode () {
			$instanceCode = end (explode ('_',$this->adb->dbName));
			$result       = $this->masterAdb->pquery ('SELECT profilecode FROM vtiger_instances WHERE code=?', array ($instanceCode));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row         = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$profileCode = $row ['profilecode'];
			}

			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($profileCode)) ? $profileCode : null;
		}

		/**
		 * @param ProfilesHowToUse $profile
		 *
		 * @throws Exception
		 */
		public function setActiveProfile ($profile) {
			if (empty ($profile) || (!$profile instanceof ProfilesHowToUse)) {
				throw new Exception('Perfil no disponible');
			}
			$instanceCode = end (explode ('_',$this->adb->dbName));
			$this->masterAdb->pquery ('UPDATE vtiger_instances SET profilecode=? WHERE code=?', array ($profile->getCode(), $instanceCode));
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return mixed|ProfilesHowToUseManager
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
