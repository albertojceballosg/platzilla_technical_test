<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/BackgroundTaskManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/process_steps/Objects/StepsTypeInterface.php');
	require_once ('modules/Settings/lib/TableFieldHelper.class.php');
	
	class StepsType implements StepsTypeInterface {
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		/**
				 * taskToWork constructor.
				 * @param PearDatabase $adb
				 */
		public function __construct ($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return void
		 */
		private function delStepsType ($crmId) {
			if (empty($crmId)) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_step_type WHERE step_typeid=?', array ($crmId));
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return Module[]|null
		 */
		private function fetchAvailableModules ($adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModulesByType(Module::TYPE_USER, true);
			if (!empty($modules)) {
				$availableModules = array ();
				foreach ($modules as $module) {
					if (
						$module->getPresence () !== 0 ||
						in_array ($module->getName (), self::EXCLUDED_MODULES)
					) {
						continue;
					}
					$availableModules [] = $module;
				}
			}
			return (isset($availableModules)) ? $availableModules : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return BackgroundTask[]|null
		 */
		private function fetchBackGroundTask ($adb) {
			$backgroundTaskManager = BackgroundTaskManager::getInstance ($this->adb);
			$backgroundTask        = $backgroundTaskManager->fetchTasksClassifiedByScope ('','USER', true);
			if (!empty ($backgroundTask)) {
				$availableBackgroundTask = array ();
				foreach ($backgroundTask['USER'] as $key => $value) {
					if (
						//$value->getStatus () === 'DISABLED' ||
						$value->getTrigger () === 'EVENT' ||
						$value->getTrigger () === 'DAILY SCHEDULE'
					) {
						continue;
					}
					$availableBackgroundTask [] = $value;
				}
			}
			
			return (isset($availableBackgroundTask) && count ($availableBackgroundTask)) ? $availableBackgroundTask : null;
		}
		
		/**
		 * @param integer $crmId
		 * @return void
		 */
		private function updateProcessTablaField ($crmId) {
			$parameters = array (
				$_REQUEST ['step_name'],
				$_REQUEST ['step_state'],
				$_REQUEST ['type'],
				$_REQUEST['step_type'],
				$crmId,
			);
			$this->adb->pquery ("UPDATE vtiger_process_at_steps SET step_name=?, step_state=?, step_type=?, related_module=?  WHERE step_id=?", $parameters);
		}
		
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return stdClass|null
		 */
		public function getStepsTypeById ($crmId) {
			if (empty ($crmId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_step_type WHERE step_typeid=?', array ($crmId));
			if ($result && $this->adb->num_rows ($result)) {
				$row = $this->adb->fetchByAssoc ($result);
				$stepsType = new stdClass ();
				$stepsType->id           = $row ['step_typeid'];
				$stepsType->stepComments = $row ['step_comments'];
				$stepsType->stepType     = $row ['step_type'];
				$stepsType->stepModule   = $row ['step_type_module'];
				$stepsType->stepTask     = $row ['step_task'];
				$stepsType->stepView     = $row ['step_view'];
				$stepsType->viewType     = self::STEPS_TYPE [$row['step_type']];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($stepsType)) ? $stepsType : null;
		}
		
		/**
		 * @param integer $stepId
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		public function isModuleOfStep ($stepId, $moduleName) {
			if (empty ($stepId) || empty ($moduleName)) {
				return false;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_step_type WHERE step_typeid=? AND step_type_module=?', array ($stepId, $moduleName));
			if ($result && $this->adb->num_rows ($result)) {
				return true;
			}
			return false;
		}
		
		public function run ($crmId, $view = null, $currentUser) {
			$modStrings = return_module_language ('es_es','Calendar');
			list ($prefix, $crm, $suffix) = explode ('_', $this->adb->dbName);
			unset ($prefix, $crm);
			$stepType = null;
			if (!empty ($crmId)) {
				$stepType = $this->getStepsTypeById ($crmId);
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $this->adb);
			$smarty->assign ('AVAILABLE_MODULES', $this->fetchAvailableModules ($this->adb));
			$smarty->assign ('BACKGROUND_TASK', $this->fetchBackGroundTask ($this->adb));
			$smarty->assign ('STEPS', $stepType);
			$smarty->assign ('STEPS_TYPE', self::STEPS_TYPE);
			$smarty->assign ('VIEW', $view);
			$smarty->assign ('VIEW_END', self::STEPS_VIEW_END);
			return $smarty->fetch ("modules/process_steps/StepsTypesEditView.tpl");
		}
		
		/**
		 * @param integer $crmId
		 * @param integer $user
		 * @param string $mode
		 *
		 * @return void
		 */
		public function saveStep ($crmId, $userId, $mode) {
			if (empty ($userId) || empty ($crmId)) {
				throw new Exception ('Uoops! algo salio mal, intenta de nuevo');
			} else if (empty ($_REQUEST['type'])) {
				//throw new Exception ('No hay paso reportado');
				return;
			}
			if ($mode == 'edit') {
				$this->delStepsType ($crmId);
			}
			$parameters = array (
				$crmId,
				$_REQUEST['step_comments'],
				$_REQUEST['step_task'],
				$_REQUEST['type'],
				$_REQUEST['step_type'],
				$_REQUEST['step_view'],
			);
			
			$this->adb->pquery (
				'INSERT INTO vtiger_step_type (step_typeid, step_comments, step_task, step_type, step_type_module, step_view) VALUES (?, ?, ?, ?, ?, ?)',
				$parameters
			);
			$this->updateProcessTablaField($crmId);
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return StepsType
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
	}
