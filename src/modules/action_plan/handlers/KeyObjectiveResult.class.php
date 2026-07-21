<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/business_objective/business_objective.php');
	class KeyObjectiveResult {
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		public function __construct($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		public function fetchKeyObjectiveResult ($crmId) {
			if (empty ($crmId)) {
				return null;
			}
			$results = $this->adb->pquery ('SELECT * FROM vtiger_action_plan2business_objective WHERE action_planid=?',
				array ($crmId)
			);
			$moduleName = 'business_objective';
			if ($this->adb->num_rows ($results) > 0) {
				$entity =  CRMEntity::getInstance ($moduleName);
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$entity->retrieve_entity_info ($row['business_objectiveid'], $moduleName);
					$keyResults = $entity->column_fields['kr_achieve_objective'];
					$summaryRow = json_decode ($keyResults['summaryrow'][0],true);
					$keyObjectiveResult [] = array (
						'objective_name'       => $entity->column_fields['objective_name'],
						'goal_progress'        => $summaryRow['goal_progress_pc'],
						'kr_achieve_objective' => $keyResults,
						'total_objetives'      => (count($keyResults['business_objectivetfid']) - 1),
					);
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($keyObjectiveResult)) ? $keyObjectiveResult : null;
		}
		
		/**
		 * @param $crmId
		 * @param string|null $view
		 * @param $currentUser
		 * @param $appFieldParameters
		 *
		 * @return string
		 */
		public function run ($crmId, $view, $currentUser, $appFieldParameters = null) {
			if (!empty ($view)) {
				return null;
			}
			$keyObjectiveResult = $this->fetchKeyObjectiveResult ($crmId);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $this->adb);
			$smarty->assign ('OKR', $keyObjectiveResult);
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ("modules/action_plan/KeyObjResultEditView.tpl");
		}
		
		/**
		 * @param integer $crmId
		 * @param string $mode
		 * @return void|null
		 */
		public function saveKeyObjectiveResult ($crmId, $mode) {
			if (empty ($crmId) || empty ($_REQUEST['app_okr'])) {
				return null;
			}
			if ($mode == 'edit') {
				$this->adb->pquery ('DELETE FROM vtiger_action_plan2business_objective WHERE action_planid=?', array ($crmId));
			}
			$okr            = $_REQUEST['app_okr'];
			$totalObjetives = count ($okr['objetive_id']);
			for ($k = 0; $k < $totalObjetives; $k++) {
				$objectiveId = $okr['objetive_id'][$k];
				$this->adb->pquery ('INSERT INTO vtiger_action_plan2business_objective (action_planid, business_objectiveid) VALUES (?,?)',
					array ($crmId, $objectiveId));
			}
		}
		
		
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
		
	}
