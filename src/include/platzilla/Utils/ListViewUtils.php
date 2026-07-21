<?php
	require_once ('include/utils/AdbManager.class.php');

	/**
	 * Class ListViewUtils
	 */
	abstract class ListViewUtils {

		/**
		 * @param PearDatabase $adb
		 * @param array $createdViews
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchAvailableGeneralView ($adb, $createdViews) {
			if (!is_array($createdViews) || empty($createdViews)) {
				return array();
			}
			$masterAdb        = AdbManager::getInstance()->getMasterAdb();
			$availableButtons = $adb->sql_expr_datalist ($createdViews);
			return $masterAdb->run_query_allrecords("SELECT * FROM vtiger_master_view WHERE tabview IN {$availableButtons}");
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $userId
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getDefaultListViewByUser ($adb, $moduleName, $userId) {
			if (empty ($moduleName) || empty($userId)) {
				return null;
			}
			$masterAdb = AdbManager::getInstance()->getMasterAdb();
			$tabView   = null;
			$result = $adb->pquery('SELECT viewid FROM vtiger_default_listview WHERE tabname=? AND userid=?', array ($moduleName, $userId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$result = $masterAdb->pquery('SELECT tabview FROM vtiger_master_view WHERE viewid=?', array ($row['viewid']));
				if ($adb->num_rows ($result) > 0) {
					$row = $adb->fetchByAssoc ($result, -1, false);
					$tabView = $row['tabview'];
				}
			}
			return ($tabView != 1) ? $tabView : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $userId
		 * @param integer $tabView
		 *
		 * @throws Exception
		 */
		public static function saveGeneralView ($adb, $moduleName, $userId, $tabView) {
			if(
				empty ($moduleName) ||
				empty ($userId) ||
				empty ($tabView) ||
				!is_scalar ($moduleName) ||
				!is_numeric ($userId) ||
				!is_numeric ($tabView)
			) {
				throw new Exception ('Imposible actualizar la vista');
			}
			$results = $adb->pquery('SELECT defaultid FROM vtiger_default_listview WHERE tabname=? AND userid=?', array($moduleName, $userId));
			if ( $adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc($results, -1, false);
				$adb->pquery('UPDATE vtiger_default_listview SET viewid=? WHERE defaultid=?', array ($tabView, $row ['defaultid']));
			} else {
				$adb->pquery('INSERT INTO vtiger_default_listview (tabname, viewid, userid) VALUES (?, ?, ?)', array ($moduleName, $tabView, $userId));
			}
		}
		
	}
