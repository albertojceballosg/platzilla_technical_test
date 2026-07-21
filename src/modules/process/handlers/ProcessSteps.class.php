<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/process_steps/Objects/StepsTypeInterface.php');
	require_once ('modules/process/Objects/ProcessStep.php');
	require_once ('modules/process/Objects/StepTypes.php');
	class ProcessSteps implements StepsTypeInterface {
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		/**
		 * @param PearDatabase $adb
		 */
		public function __construct ($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 *
		 * @return ProcessStep
		 */
		private function fetchStepTypes () {
			$results = $this->adb->query ('SELECT * FROM vtiger_process_at_steps WHERE 1');
			if ($this->adb->num_rows ($results) > 0) {
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$stepsTypes[] = StepTypes::getInstance ()
						->setStepComments ($row['step_comments'])
						->setStepModule ($row['step_type_module'])
						->setStepTask ($row['step_task'])
						->setStepType ($row['step_type'])
						->setStepTypeid ($row['step_typeid']);
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($stepsTypes)) ? $stepsTypes : null;
		}
		
		/**
		 * @param integer $processId
		 * @param integer $stepId
		 * @param integer $caseId
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		private function isActiveStep ($processId, $stepId, $caseId, $moduleName) {
			if (empty ($processId) || empty ($stepId) || empty ($caseId)) {
				return false;
			}
			$isActive = false;
			$results  = $this->adb->pquery (
				'SELECT
       					process_casesid
					FROM
					     vtiger_process_cases
					WHERE
					      process=? AND
					      process_step=? AND
					      case_number=? AND
					      module_name=?',
				array ($processId, $stepId, $caseId, $moduleName)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$isActive = true;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $isActive;
		}
		
		/**
		 * @param integer $processId
		 * @param integer $stepId
		 *
		 * @return void
		 */
		private function setRelatedList ($processId, $stepId) {
			if (empty($processId) || empty($stepId)) {
				return;
			}
			$results = $this->adb->pquery ('SELECT *  FROM vtiger_crmentityrel WHERE crmid=? AND relcrmid=?', array ($stepId, $processId));
			if ($this->adb->num_rows ($results) > 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$this->adb->pquery ('INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?,?,?,?)',
					array ($stepId, self::PROCESS_STEP_MODULE_NAME, $processId, self::PROCESS_MODULE_NAME)
				);
			}
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return ProcessStep[]|null
		 * @throws Exception
		 */
		public function fetchProcessAtSteps ($crmId) {
			if (empty ($crmId)) {
				return null;
			}
			
			$results = $this->adb->pquery ('SELECT * FROM vtiger_process_at_steps WHERE processtfid=? ORDER BY sequence ASC',
				array ($crmId)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$index = 1;
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$porcessAtSteps[] = ProcessStep::getInstance ()
						->setActionOnStep ($row['action_on_step'])
						->setActionOnTask ($row['action_on_task'])
						->setProcessId ($row ['processtfid'])
						->setRelatedTab ($row ['related_module'])
						->setSequence ((empty ($row ['sequence']) ? $index : $row ['sequence']))
						->setStepCode ($row ['step_code'])
						->setStepId ($row ['step_id'])
						->setStepName ($row ['step_name'])
						->setResponsibleRole ($row ['step_responsible_role'])
						->setStepState ($row ['step_state'])
						->setStepType ($row ['step_type']);
					$index++;
				}
				
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($porcessAtSteps)) ? $porcessAtSteps : null;
		}
		
		/**
		 * @param integer $processId
		 * @param string $module
		 * @param integer|null $stepId
		 *
		 * @return array|mixed|null
		 * @throws Exception
		 */
		public function getStepProcess ($processId, $module, $stepId) {
			if (empty ($processId) || empty ($module)) {
				return null;
			}
			$parameters  = array ($processId, $module);
			$whereStepId = '';
			if (!empty ($stepId)) {
				$whereStepId   = 'AND pas.step_id!=?';
				$parameters [] = $stepId;
			}
			
			$results = $this->adb->pquery (
				"SELECT
			       		pas.step_id AS step_codeid,
			       		pas.step_type,
			       		pas.step_name,
			            ps.estimated_tim AS estimated_time,
			            ps.error_rat AS error_rate
					FROM
						vtiger_process_at_steps pas
					INNER JOIN vtiger_process_steps ps ON pas.step_id=ps.process_stepsid
					INNER JOIN vtiger_crmentity crm ON ps.process_stepsid=crm.crmid AND crm.deleted=0
					WHERE
						pas.processtfid=? AND
						pas.related_module=?
						{$whereStepId}
					ORDER BY pas.sequence ASC
					LIMIT 1",
				$parameters
			);
			if ($this->adb->num_rows ($results) > 0) {
				$row = $this->adb->fetchByAssoc ($results);
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($row)) ? $row : null;
		}
		
		/**
		 * @param integer $processId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function getStepsByProcess ($processId, $caseId, $moduleName) {
			if (empty ($processId)) {
				return null;
			}
			$results = $this->adb->pquery (
				"SELECT
				        pt.*,
				        st.step_task,
				        st.step_comments,
       					st.step_view,
				        ps.step_description
					FROM vtiger_process_at_steps pt
					INNER JOIN vtiger_step_type st ON st.step_typeid = pt.step_id
					INNER JOIN vtiger_process_steps ps ON ps.process_stepsid = pt.step_id
					WHERE processtfid=?
					ORDER BY pt.sequence ASC",
				array ($processId)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$steps = array ();
				$activeFound = false;
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$dummy                  = explode ('-', $row ['action_on_step']);
					$dummy[0]               = (intval ($dummy[0]) - 1);
					$row ['action_on_step'] = $dummy;
					if (!empty($row ['action_on_task'])) {
						$dummy                  = explode ('-', $row ['action_on_task']);
						$dummy[0]               = (intval ($dummy[0]) - 1);
						$row ['action_on_task'] = $dummy;
					}
					if (!$activeFound) {
						$activeFound       = $this->isActiveStep ($processId, $row ['step_id'], $caseId, $moduleName);
						$row ['is_active'] = $activeFound;
					} else {
						$row ['is_active'] = false;
					}
					$steps []               = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($steps)) ? $steps : null;
		}
		
		/**
		 * @param integer $crmId
		 * @param string|null $view
		 * @param Users $currentUser
		 *
		 * @return false|string
		 * @throws SmartyException
		 */
		public function run ($crmId, $view = null, $currentUser) {
			$processAtSteps = null;
			if (!empty($crmId)) {
				$processAtSteps = $this->fetchProcessAtSteps ($crmId);
			}
			$template = (empty ($view)) ? 'ProcessStepsEditView' : 'ProcessStepsDetailView';
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $this->adb);
			$smarty->assign ('PROCESS_STEPS', $processAtSteps);
			$smarty->assign ('STEPS', $this->fetchStepTypes());
			$smarty->assign ('STEPS_TYPE', self::STEPS_TYPE);
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ("modules/process/{$template}.tpl");
		}
		
		/**
		 * @param integer $crmId
		 * @param string $mode
		 *
		 * @return void|null
		 */
		public function saveProcessSteps ($crmId, $mode) {
			if (empty ($crmId) || empty ($_REQUEST['app_process'])) {
				return null;
			}
			if ($mode == 'edit') {
				$this->adb->pquery ('DELETE FROM vtiger_process_at_steps WHERE processtfid=?', array ($crmId));
			}
			$processSteps = $_REQUEST['app_process'];
			$totalRows    = count ($processSteps['step_id']);
			$index        = 1;
			for ($k = 0; $k < $totalRows; $k++) {
				$actionOn        = $processSteps ['action_on']['step'][$k];
				$actionTask      = $processSteps ['action_on']['task'][$k];
				$stepCode        = $processSteps ['step_code'][$k];
				$stepId          = $processSteps ['step_id'][$k];
				$stepName        = $processSteps ['step_name'][$k];
				$responsibleRole = $processSteps ['step_responsible_role'][$k];
				$relatedModule   = $processSteps ['related_module'][$k];
				$stepState       = $processSteps ['step_state'][$k];
				$stepType        = $processSteps ['step_type'][$k];
				$this->adb->pquery ('INSERT INTO vtiger_process_at_steps (processtfid, action_on_step, action_on_task, step_code, step_id, step_name, step_responsible_role, related_module, step_state, step_type, sequence) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
					array ($crmId, $actionOn, $actionTask, $stepCode, $stepId, $stepName, $responsibleRole, $relatedModule, $stepState, $stepType, $index)
				);
				$index++;
				$this->setRelatedList ($crmId, $stepId);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @return ProcessSteps
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
	}
