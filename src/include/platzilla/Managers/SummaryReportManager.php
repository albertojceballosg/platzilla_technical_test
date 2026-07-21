<?php
	require_once ('include/platzilla/Objects/MasterWeeklyReport.php');
	require_once ('include/platzilla/Objects/RailesAgreements.php');
	require_once ('include/platzilla/Objects/RailesPerformance.php');
	require_once ('include/platzilla/Objects/SummaryReport.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	class SummaryReportManager {
		
		/** @var SummaryReportManager|null  */
		private static $INSTANCES = null;
		
		/** @var PearDatabase */
		private $adb;
		
		/** @var boolean */
		private $isInstance;
		
		/** @var PearDatabase  */
		private $masterAdb;
		
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
			$pos       = strpos ($this->adb->dbName,'madre');
			if ($pos === false) {
				$this->isInstance = true;
				$this->masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
			} else {
				$this->isInstance = false;
				$this->masterAdb  = $adb;
			}
		}
		
		/**
		 * @param PearDatabase $targetAdb
		 *
		 * @return RailesAgreements[]|null
		 * @throws Exception
		 */
		private function fetchAgreements ($targetAdb, $reportId) {
			$result = $targetAdb->pquery ('SELECT * FROM vtiger_railes_agreements WHERE reportid = ?', array ($reportId));
			if ($targetAdb->num_rows ($result) > 0) {
				$agreements = array();
				while ($row = $targetAdb->fetchByAssoc ($result, -1, false)) {
					$agreements [] = RailesAgreements::getInstance ()
						->setAgreement ($row['agreement'])
						->setAgreementId ($row['agreementid'])
						->setDescription ($row['description'])
						->setExecution ($row['execution'])
						->setReportId ($row['summaryreportid'])
						->setSequence ($row['sequence'])
						->setTabName ($row['tab_name'])
						->setUsersInvolved ($row['users_involved']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($agreements)) ? $agreements : null;
		}
		
		/**
		 * @return RailesPerformance|null
		 * @throws Exception
		 */
		private function fetchPerformance ($performanceId) {
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_railes_performance WHERE performanceid = ?', array ($performanceId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row         = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$performance = $this->getPerformanceObject ($row);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($performance)) ? $performance : null;
		}
		
		/**
		 * @param array $row
		 *
		 * @return RailesPerformance|null
		 * @throws SummaryReportException
		 */
		private function getPerformanceObject ($row) {
			if (empty($row)) {
				return null;
			}
			return  RailesPerformance::getInstance ()
				->setDescription ($row['description'])
				->setIconPath ($row['iconpath'])
				->setIndexColor ($row['index_color'])
				->setPerformanceId ($row['performanceid'])
				->setPerformanceStatus ($row['performance_status'])
				->setPerformanceName ($row['name']);
		}
		
		/**
		 * @param SummaryReport $summaryReport
		 *
		 * @return void
		 */
		private function saveAgreements ($summaryReport) {
			if (empty ($summaryReport->getRailesAgreements ()) || !count($summaryReport->getRailesAgreements ())) {
				return;
			}
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($summaryReport->getInstanceCode ());
			$targetAdb->pquery ('DELETE FROM vtiger_railes_agreements WHERE summaryreportid = ?', array ($summaryReport->getReportId ()));
			foreach ($summaryReport->getRailesAgreements () as $railesAgreement) {
				if (!$railesAgreement instanceof RailesAgreements) {
					continue;
				}
				$targetAdb->pquery (
					'INSERT INTO vtiger_rails_agreements (summaryreportid, agreement, description, execution, tab_name, users_involved,sequence) VALUES (?,?,?,?,?,?,?)',
					array ($summaryReport->getReportId (), $railesAgreement->getAgreement (), $railesAgreement->getDescription (), $railesAgreement->getExecution (), $railesAgreement->getTabName (), $railesAgreement->getUsersInvolved (), $railesAgreement->getSequence ())
				);
			}
		}
		
		/**
		 * @param array $periodDate
		 * @param string|null $instanceCode
		 *
		 * @return SummaryReport|null
		 * @throws SummaryReportException
		 */
		public function fetchSummaryReport ($periodDate, $instanceCode = null) {
			if (empty ($periodDate)) {
				throw new SummaryReportException (SummaryReportException::ERROR_PERIOD_DATE_EMPTY);
			}
			if (!$this->isInstance && !empty ($instanceCode)) {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			} else {
				$targetAdb = $this->adb;
			}
			$dbMain   = $this->masterAdb->dbName;
			$dbTarget = $targetAdb->dbName;
			$result   = $targetAdb->pquery (
				"SELECT
			       		{$dbMain}.*,
			       		{$dbTarget}.*
					FROM {$dbMain}.vtiger_master_summary_report msr
					INNER JOIN  {$dbTarget}.vtiger_summary_report sr ON msr.summaryreportid = sr.summaryreportid
					WHERE msr.date_start = ? AND msr.due_date=?",
				array ($periodDate['date_start'], $periodDate['due_date'])
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$summaryReport = SummaryReport::getInstance ()
						->setAgentId ($row['agentid'])
						->setDateStart ($row['date_start'])
						->setDueDate ($row['due_date'])
						->setInstanceCode ($row['instance_code'])
						->setMasterReportId ($row['masterreportid'])
						->setPerformanceId ($row['performance_id'])
						->setPerformanceText ($row['summary_performance'])
						->setRailesAgreements ($this->fetchAgreements ($dbTarget, $row['summaryreportid']))
						->setRailesPerformance ($this->fetchPerformance ($row['performanceid']))
						->setReportId ($row['summaryreportid'])
						->setReportStatus ($row['report_status'])
						->setReportText ($row['summary_report'])
						->setReportTitle ($row['report_title'])
						->setUserId ($row['user_id']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($summaryReport)) ? $summaryReport : null;
		}
		
		/**
		 * @param string $index
		 *
		 * @return RailesPerformance|null
		 * @throws SummaryReportException
		 */
		public function getPerformanceById ($index) {
			if (empty ($index)) {
				return null;
			}
			
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_rails_performance WHERE performanceid = ?', array ($index));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row         = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$performance = $this->getPerformanceObject ($row);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($performance)) ? $performance : null;
		}
		
		/**
		 * @param MasterWeeklyReport $masterWeeklyReport
		 *
		 * @return MasterWeeklyReport
		 */
		public function saveMasterReport ($masterWeeklyReport) {
			if (empty($masterWeeklyReport->getId())) {
				$this->masterAdb->pquery (
					'INSERT INTO vtiger_master_summary_report (description, agentid, date_start, due_date, instance_code, instance_mail, master_status, master_report_status) VALUES (?,?,?,?,?,?,?, ?)',
					array($masterWeeklyReport->getDescription (), $masterWeeklyReport->getAgentId (),$masterWeeklyReport->getDateStart (), $masterWeeklyReport->getDueDate (), $masterWeeklyReport->getCodeInstance (), $masterWeeklyReport->getMailInstance (), $masterWeeklyReport->getStatus (), $masterWeeklyReport->getReportOfStatus ())
				);
				$masterWeeklyReport->setId ($this->masterAdb->getLastInsertID ());
			} else {
				$this->masterAdb->pquery (
					'UPDATE vtiger_master_summary_report SET description= ?, agentid = ?, date_start = ?, due_date = ?, instance_code = ?, instance_mail =?, master_status = ?, master_report_status=? WHERE masterreportid = ?',
					array($masterWeeklyReport->getDescription (), $masterWeeklyReport->getAgentId (),$masterWeeklyReport->getDateStart (), $masterWeeklyReport->getDueDate (), $masterWeeklyReport->getCodeInstance (), $masterWeeklyReport->getMailInstance (), $masterWeeklyReport->getStatus (), $masterWeeklyReport->getReportOfStatus (), $masterWeeklyReport->getId ())
				);
			}
			return $masterWeeklyReport;
		}
		
		/**
		 * @param SummaryReport $summaryReport
		 *
		 * @return void
		 * @throws SummaryReportException
		 */
		public function saveSummaryReport ($summaryReport) {
			if (!$summaryReport instanceof SummaryReport) {
				return;
			}
			$summaryReport->validate ();
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($summaryReport->getInstanceCode ());
			if ($summaryReport->getMasterReportId () == null) {
				$targetAdb->pquery(
					'INSERT INTO vtiger_rails_summary_report (userid, date_start, due_date, report_title, summary_report, summary_performance, performanceid, report_status) VALUES (?,?,?,?,?,?,?,?)',
					array ($summaryReport->getUserId (), $summaryReport->getDateStart (), $summaryReport->getDueDate (), $summaryReport->getReportTitle (), $summaryReport->getReportText (), $summaryReport->getPerformanceText (), $summaryReport->getPerformanceId (), $summaryReport->getReportStatus ())
				);
				$summaryReport->setReportId ($targetAdb->database->Insert_ID ());
			} else {
				$targetAdb->pquery(
					'UPDATE vtiger_rails_summary_report SET userid=?, date_start=?, due_date=?, report_title=?, summary_report=?, summary_performance=?, performanceid=?, report_status=? WHERE summaryreportid=?',
					array ($summaryReport->getUserId (), $summaryReport->getDateStart (), $summaryReport->getDueDate (), $summaryReport->getReportTitle (), $summaryReport->getReportText (), $summaryReport->getPerformanceText (), $summaryReport->getPerformanceId (), $summaryReport->getReportStatus (), $summaryReport->getReportId ())
				);
			}
			$this->saveAgreements ($summaryReport);
			$this->saveMasterReport ($summaryReport);
			
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return SummaryReportManager|null
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
