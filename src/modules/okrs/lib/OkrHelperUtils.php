<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/platzilla/Data/CompanyType.php');
	require_once ('modules/okrs/Objects/OkrsObjectives.php');
	class OkrHelperUtils {
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		public function __construct() {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		}
		
		/**
		 * @param integer $objectiveId
		 *
		 * @throws Exception
		 */
		public function deleteObjective ($objectiveId) {
			if (empty($objectiveId)) {
				throw new Exception ('Objectivo no encontrado!');
			}
			$this->masterAdb->pquery (
				'DELETE FROM vtiger_okr_key_results WHERE objectiveid=?',
				array ($objectiveId)
			);
			$this->masterAdb->pquery (
				'DELETE FROM vtiger_okr_objectives WHERE objectivesid=?',
				array ($objectiveId)
			);
		}
		
		/**
		 * @param integer $keyResultId
		 *
		 * @throws Exception
		 */
		public function deleteKeyResult ($keyResultId) {
			if (empty($keyResultId)) {
				throw new Exception ('Resultado clave no encontrado!');
			}
			$this->masterAdb->pquery (
				'DELETE FROM vtiger_okr_key_results WHERE keyresultsid=?',
				array ($keyResultId)
			);
		}
		
		/**
		 * @param null|string $status
		 *
		 * @return array|KeyResults []
		 * @throws Exception
		 */
		public function fetchKeyResults ($status = null) {
			if (empty($status)) {
				$where = 'WHERE 1';
			} else {
				$where = "WHERE status= '{$status}'";
			}
			
			$result = $this->masterAdb->query ("SELECT * FROM vtiger_okr_key_results {$where}");
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$keyResults [] = KeyResults::getInstance ()
						->setId ($row ['keyresultsid'])
						->setObjectiveId ($row ['objectiveid'])
						->setCompanyArea ($row['companyarea'])
						->setDescription ($row ['description'])
						->setGoalValue (intval ($row ['goal_value']))
						->setValueActual ($row ['value_actual'])
						->setFrequency ($row ['frequency'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($keyResults)) ? $keyResults : array();
		}
		
		/**
		 * @param integer $objectiveId
		 *
		 * @return array|KeyResults[]
		 * @throws Exception
		 */
		public function fetchKeyResultByObjective ($objectiveId) {
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_okr_key_results WHERE objectiveid=?', array ($objectiveId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$keyResults [] = KeyResults::getInstance ()
						->setId ($row ['keyresultsid'])
						->setObjectiveId ($row ['objectiveid'])
						->setCompanyArea ($row['companyarea'])
						->setDescription ($row ['description'])
						->setGoalValue (intval ($row ['goal_value']))
						->setValueActual ($row ['value_actual'])
						->setFrequency ($row ['frequency'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($keyResults)) ? $keyResults : array();
		}
		
		/**
		 * @param null|string $status
		 * @param boolean $headersOnly
		 *
		 * @return null|OkrsObjectives[]
		 * @throws Exception
		 */
		public function fetchObjectives ($status = null, $headersOnly = false) {
			if (empty($status)) {
				$where = 'WHERE 1';
			} else {
				$where = "WHERE status= '{$status}'";
			}
			
			$result = $this->masterAdb->pquery ("SELECT * FROM vtiger_okr_objectives {$where}");
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$objectives [] = OkrsObjectives::getInstance ()
						->setId ($row ['objectivesid'])
						->setCompanyArea ($row ['companyarea'])
						->setCompanyPhases (!empty ($row ['companyphase']) ? explode (';', $row ['companyphase']) : array())
						->setCompanyTypes (!empty ($row ['companytype']) ? explode (';', $row ['companytype']) : array())
						->setKeyResults ((!$headersOnly) ? $this->fetchKeyResultByObjective ($row ['objectivesid']) : null)
						->setToDo ($row ['todo'])
						->setHowToDo ($row ['howtodo'])
						->setIsOnBoarding ($row ['onboarding'])
						->setFrequency ($row ['frequency'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($objectives)) ? $objectives : null;
		}
		
		/**
		 * @param string $objectiveId
		 * @param boolean $headersOnly
		 *
		 * @return null|OkrsObjectives
		 * @throws Exception
		 */
		public function getObjectiveById ($objectiveId, $headersOnly = false) {
			if (empty($objectiveId)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_OBJECTIVE_ID);
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_okr_objectives WHERE objectivesid=?', array ($objectiveId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$objectives = OkrsObjectives::getInstance ()
						->setId ($row ['objectivesid'])
						->setCompanyArea ($row ['companyarea'])
						->setCompanyPhases (!empty ($row ['companyphase']) ? explode (';', $row ['companyphase']) : array())
						->setCompanyTypes (!empty ($row ['companytype']) ? explode (';', $row ['companytype']) : array())
						->setKeyResults ((!$headersOnly) ? $this->fetchKeyResultByObjective ($row ['objectivesid']) : null)
						->setToDo ($row ['todo'])
						->setHowToDo ($row ['howtodo'])
						->setIsOnBoarding ($row ['onboarding'])
						->setFrequency ($row ['frequency'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($objectives)) ? $objectives : null;
		}
		
		/**
		 * @param string $area
		 *
		 * @return null|OkrsObjectives[]
		 * @throws Exception
		 */
		public function getObjectivesByArea ($area) {
			if (empty($area)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_COMPANY_AREA);
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_okr_objectives WHERE companyarea=?', array ($area));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$objectives [] = OkrsObjectives::getInstance ()
						->setId ($row ['objectivesid'])
						->setCompanyArea ($row ['companyarea'])
						->setCompanyPhases (!empty ($row ['companyphase']) ? explode (';', $row ['companyphase']) : array())
						->setCompanyTypes (!empty ($row ['companytype']) ? explode (';', $row ['companytype']) : array())
						->setKeyResults ($this->fetchKeyResultByObjective ($row ['objectivesid']))
						->setToDo ($row ['todo'])
						->setHowToDo ($row ['howtodo'])
						->setIsOnBoarding ($row ['onboarding'])
						->setFrequency ($row ['frequency'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($objectives)) ? $objectives : null;
		}
		
		/**
		 * @param array $data
		 * @param string $orderBy
		 *
		 * @return null|OkrsObjectives[]
		 * @throws Exception
		 */
		public function getObjectivesByWhere ($data, $orderBy = '') {
			$whereClause = 'WHERE 1 ';
			$where       = '';
			if (is_array ($data)) {
				foreach ($data as $key => $value) {
					if (is_numeric ($value)) {
						$where .= " AND ({$key} = {$value})";
					} else {
						$where .= " AND ({$key} LIKE '%{$value}%')";
					}
				}
			}
			$whereClause .= $where;
			$result = $this->masterAdb->query ("SELECT * FROM vtiger_okr_objectives {$whereClause} {$orderBy}");
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$objectives [] = OkrsObjectives::getInstance ()
						->setId ($row ['objectivesid'])
						->setCompanyArea ($row ['companyarea'])
						->setCompanyPhases (!empty ($row ['companyphase']) ? explode (';', $row ['companyphase']) : array())
						->setCompanyTypes (!empty ($row ['companytype']) ? explode (';', $row ['companytype']) : array())
						->setKeyResults ($this->fetchKeyResultByObjective ($row ['objectivesid']))
						->setToDo ($row ['todo'])
						->setHowToDo ($row ['howtodo'])
						->setIsOnBoarding ($row ['onboarding'])
						->setFrequency ($row ['frequency'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($objectives)) ? $objectives : null;
		}
		
		/**
		 * @param $keyResultId
		 * @return array|KeyResults
		 * @throws Exception
		 */
		public function getKeyResultById ($keyResultId) {
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_okr_key_results WHERE keyresultsid=?', array ($keyResultId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$keyResult = KeyResults::getInstance ()
						->setId ($row ['keyresultsid'])
						->setObjectiveId ($row ['objectiveid'])
						->setDescription ($row ['description'])
						->setGoalValue (intval ($row ['goal_value']))
						->setValueActual ($row ['value_actual'])
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($keyResult)) ? $keyResult : array();
		}
		/**
		 * @param KeyResults[] $keyResults
		 *
		 * @return null
		 * @throws OkrsException
		 */
		public function saveKeyResult ($keyResults) {
			if (empty ($keyResults)) {
				return null;
			}
			
			foreach ($keyResults as $keyResult) {
				if (empty ($keyResult) || (!$keyResult instanceof KeyResults)) {
					continue;
				}
				$keyResult->validate ();
				if (empty($keyResult->getId ())) {
					$this->masterAdb->pquery (
						'INSERT INTO vtiger_okr_key_results (objectiveid, companyarea, description, goal_value, value_actual, frequency, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array($keyResult->getObjectiveId (), $keyResult->getCompanyArea (), $keyResult->getDescription (), $keyResult->getGoalValue (), $keyResult->getValueActual (), $keyResult->getFrequency (), $keyResult->getStatus ())
					);
					$keyResult->setId ($this->masterAdb->getLastInsertID ());
				} else {
					$this->masterAdb->pquery (
						'UPDATE vtiger_okr_key_results SET objectiveid=?, companyarea=?, description=?, goal_value=?, value_actual=?, frequency=?, status=? WHERE keyresultsid=?',
						array($keyResult->getObjectiveId (), $keyResult->getCompanyArea (), $keyResult->getDescription (), $keyResult->getGoalValue (), $keyResult->getValueActual (), $keyResult->getFrequency (), $keyResult->getStatus (), $keyResult->getId ())
					);
				}
			}
		}
		
		/**
		 * @param OkrsObjectives $objective
		 *
		 * @return null
		 * @throws OkrsException
		 */
		public function saveObjective ($objective) {
			if (empty ($objective) || (!$objective instanceof OkrsObjectives)) {
				return null;
			}
			$objective->validate ();
			$companyPhases = (!empty($objective->getCompanyPhases ())) ? implode (';', $objective->getCompanyPhases ()) : null;
			$companyTypes  = (!empty($objective->getCompanyTypes ())) ? implode (';', $objective->getCompanyTypes ()) : null;
			if (empty($objective->getId ())) {
				$this->masterAdb->pquery (
					'INSERT INTO vtiger_okr_objectives (companyarea, companyphase, companytype, todo, howtodo, frequency, status, onboarding) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array($objective->getCompanyArea (), $companyPhases, $companyTypes, $objective->getToDo (), $objective->getHowToDo (), $objective->getFrequency (), $objective->getStatus (), $objective->isOnBoarding ())
				);
				$objective->setId ($this->masterAdb->getLastInsertID ());
			} else {
				$this->masterAdb->pquery (
					'UPDATE vtiger_okr_objectives SET companyarea=?, companyphase=?, companytype=?, todo=?, howtodo=?, frequency=?, status=?, onboarding=? WHERE objectivesid=?',
					array($objective->getCompanyArea (), $companyPhases, $companyTypes, $objective->getToDo (), $objective->getHowToDo (), $objective->getFrequency (), $objective->getStatus (), $objective->isOnBoarding (), $objective->getId ())
				);
			}
			$this->saveKeyResult ($objective->getKeyResults ());
			return $objective;
		}
		
		/**
		 * @return OkrHelperUtils
		 */
		public static function getInstance () {
			return new self ();
		}
	}
