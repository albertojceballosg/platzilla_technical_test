<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/process_steps/Objects/StepsTypeInterface.php');
	class SummaryProcessCase implements StepsTypeInterface {
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		/** @var array */
		private $quality = array (
			'valuation' => 'Bueno',
			'reason'    =>  '<b>Calidad:</b> Valoración de calidad asignada automáticamente',
		);
		
		/**
		 * @param $adb
		 */
		public function __construct ($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $caseId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function fetchDocByCase  ($adb, $caseId) {
			if (empty ($caseId)) {
				return null;
			}
			$results = $this->adb->pquery (
				'SELECT
       				att.name,
       				att.description,
       				crm.createdtime
				FROM
					vtiger_attachments att
				INNER JOIN vtiger_process_cases2document pcd ON pcd.attachmentsid = att.attachmentsid
				INNER JOIN vtiger_crmentity crm ON crm.crmid = att.attachmentsid
				WHERE pcd.process_casesid=?',
				array ($caseId)
			);
			if ($this->adb->num_rows ($results) > 0) {
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$documents[] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($documents)) ? $documents : null;
		}
		
		private function getActionOnStep (&$row) {
			if (empty ($row['process']['processid']) || empty($row['step']['process_stepsid'])) {
				return;
			}
			$results = $this->adb->pquery (
				'SELECT action_on_step  FROM vtiger_process_at_steps WHERE processtfid=? AND step_id=? AND step_type=?',
				array ($row['process']['processid'], $row['step']['process_stepsid'], 'ASSISTED')
			);
			if ($this->adb->num_rows ($results) > 0) {
				$arrayResult    = $this->adb->fetchByAssoc ($results);
				$actionOnStep   = explode ('-', $arrayResult['action_on_step']);
				$row ['crm_id'] = $this->getModuleStepId ($actionOnStep[1], $row['case_number']);
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
		}
		
		/**
		 * @param array $parameters
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function getCaseDetails ($parameters) {
			if (empty ($parameters)) {
				return null;
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			$results = $this->adb->pquery (
				'SELECT
		       		pc.*,
       				crm.case_number AS crm_id
				FROM vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON crm.crmid = pc.process_casesid
				WHERE
					crm.deleted=0 AND
					pc.case_number=? AND
					pc.process=?
				 ORDER BY pc.start_date, pc.process_step',
				$parameters
			);
			if ($this->adb->num_rows ($results) > 0) {
				$summaryTime = 0;
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$row ['process']           = ProcessCasesUtils::getProcessById ($this->adb, $row['process']);
					$row ['step']              = ProcessCasesUtils::getStepProcessId ($this->adb, $row['process_step']);
					$row ['quality_valuation'] = (empty ($row ['quality_valuation'])) ? $this->quality['valuation'] : $row ['quality_valuation'];
					$row ['reason_valuation']  = (empty ($row ['reason_valuation'])) ? $this->quality['reason'] : "<b>Calidad:</b> {$row ['reason_valuation']}";
					$row ['documents']         = $this->fetchDocByCase ($this->adb, $row['process_casesid']);
					$this->getActionOnStep ($row);
					$summaryTime  += floatval ($row ['step_exec_time']);
					$row ['step_exec_time'] = $numberingHelper->setNumberFormat ($row ['step_exec_time']);
					$caseDetails[] = $row;
				}
				$caseDetails ['summary_time'] = $numberingHelper->setNumberFormat ($summaryTime);
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($caseDetails)) ? $caseDetails : null;
		}
		
		/**
		 * @param integer $processStepId
		 * @param string $caseNumber
		 *
		 * @return integer|null
		 * @throws Exception
		 */
		private function getModuleStepId ($processStepId, $caseNumber) {
			if (empty($processStepId) || empty($caseNumber)) {
				return null;
			}
			$results = $this->adb->pquery (
				'SELECT
			       		crm.case_number AS crm_id
					FROM vtiger_process_cases pc
					INNER JOIN vtiger_crmentity crm ON crm.crmid = pc.process_casesid
					WHERE
						crm.deleted=0 AND
						pc.case_number=? AND
						pc.process_step=?
					LIMIT 1',
				array ($caseNumber, $processStepId)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$arrayResult = $this->adb->fetchByAssoc ($results);
				$crmId       = $arrayResult ['crm_id'];
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return isset ($crmId) ? $crmId : null;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return array|mixed|null
		 * @throws Exception
		 */
		private function getParameters ($crmId) {
			if (empty($crmId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
       					case_number,
       					process
					FROM
						vtiger_process_cases
					WHERE
					      process_casesid=?',
				array ($crmId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$parameters = $this->adb->fetchByAssoc ($result);
			}
			DatabaseUtils::closeResult ($result);
			$results = null;
			return (isset ($parameters)) ? $parameters : null;
		}
		
		/**
		 * @param integer $crmId
		 * @param string|null $view
		 * @param Users $currentUser
		 *
		 * @return string|array|void
		 * @throws SmartyException
		 */
		public function run ($crmId, $view = null, $currentUser) {
			if (empty ($crmId) || empty ($view)) {
				return;
			}
			
			$parameters  = $this->getParameters ($crmId);
			$caseDetails = $this->getCaseDetails (array_values ($parameters));
			if (empty ($caseDetails)) {
				return;
			}
			if ($view == 'HomeView') {
				return array (
					'parameters'  => $parameters,
					'caseDetails' => $caseDetails,
				);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $this->adb);
			$smarty->assign ('CASE_DETAILS', $caseDetails);
			$smarty->assign ('CASE_NUMBER', $parameters ['case_number']);
			$smarty->assign ('STEPS_TYPE', self::STEPS_TYPE);
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ("modules/process_case/SummaryProcessCase.tpl");
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return SummaryProcessCase
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
	}
