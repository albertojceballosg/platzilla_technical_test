<?php
	require_once ('config.php');
	require_once ('include/logging.php');
	require_once ('include/logging.php');
	require_once ('include/ListView/ListView.php');
	require_once ('include/database/PearDatabase.php');

	/**
	 * This class is used to track all the operations done by the particular User while using vtiger crm.
	 * It is intended to be called when the check for audit trail is enabled.
	 **/
	class AuditTrail {

		public $log;
		public $db;

		public $auditid;
		public $userid;
		public $module;
		public $action;
		public $recordid;
		public $actiondate;

		public $module_name = 'Settings';
		public $table_name  = 'vtiger_audit_trial';

		public $object_name = 'AuditTrail';

		public $new_schema = true;

		public function __construct () {
			$this->log = LoggerManager::getLogger ('audit_trial');
			$this->db  = PearDatabase::getInstance ();
		}

		public $sortby_fields = array ('module', 'action', 'actiondate', 'recordid');

		public $list_fields = array (
			'Module'      => array ('vtiger_audit_trial' => 'module'),
			'Action'      => array ('vtiger_audit_trial' => 'action'),
			'Record'      => array ('vtiger_audit_trial' => 'recordid'),
			'Action Date' => array ('vtiger_audit_trial' => 'actiondate'),
		);

		public $list_fields_name = array (
			'Module'      => 'module',
			'Action'      => 'action',
			'Record'      => 'recordid',
			'Action Date' => 'actiondate',
		);

		public $default_order_by   = 'actiondate';
		public $default_sort_order = 'DESC';

		/**
		 * Function to get the Headers of Audit Trail Information like Module, Action, RecordID, ActionDate.
		 * Returns Header Values like Module, Action etc in an array format.
		 **/
		public function getAuditTrailHeader () {
			global $log;
			$log->debug ('Entering getAuditTrailHeader() method ...');
			global $app_strings;
			$headerArray = array ($app_strings['LBL_MODULE'], $app_strings['LBL_ACTION'], $app_strings['LBL_RECORD_ID'], $app_strings['LBL_ACTION_DATE']);
			$log->debug ('Exiting getAuditTrailHeader() method ...');
			return $headerArray;
		}

		/**
		 * Function to get the Audit Trail Information values of the actions performed by a particular User.
		 * Returns the audit trail entries in an array format.
		 *
		 * @param integer $userid User's ID
		 * @param array $navigationArray Array values to navigate through the number of entries.
		 * @param string $sorder
		 * @param string $orderby
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getAuditTrailEntries ($userid, $navigationArray, $sorder = '', $orderby = '') {
			global $log;
			$log->debug ('Entering getAuditTrailEntries(' . $userid . ') method ...');
			global $adb;

			if ($sorder != '' && $orderby != '') {
				$listQuery = "SELECT * FROM vtiger_audit_trial WHERE userid=? ORDER BY {$orderby} {$sorder}";
			} else {
				$listQuery = "SELECT * FROM vtiger_audit_trial WHERE userid=? ORDER BY {$this->default_order_by} {$this->default_sort_order}";
			}

			$result      = $adb->pquery ($listQuery, array ($userid));
			$entriesList = array ();
			if ($navigationArray['end_val'] != 0) {
				for ($i = $navigationArray['start']; $i <= $navigationArray['end_val']; $i++) {
					$entries = array ();
					$entries[]     = getTranslatedString ($adb->query_result ($result, ($i - 1), 'module'));
					$entries[]     = $adb->query_result ($result, ($i - 1), 'action');
					$entries[]     = $adb->query_result ($result, ($i - 1), 'recordid');
					$date          = $adb->query_result ($result, ($i - 1), 'actiondate');
					$entries[]     = $date;
					$entriesList[] = $entries;
				}
				$log->debug ('Exiting getAuditTrailEntries() method ...');
				return $entriesList;
			}
			return null;
		}

	}
