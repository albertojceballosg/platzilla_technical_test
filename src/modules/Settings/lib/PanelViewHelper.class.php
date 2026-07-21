<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	abstract class PanelViewHelper {
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchAvailableModules ($adb, $userId, $preference = array()) {
			$result = $adb->query (
				'SELECT
       					tabid,
       					name,
       					tablabel
					FROM
					    vtiger_tab
					WHERE
					    presence != -1 AND
					    tabsequence != -1 AND
					    isentitytype != 0 AND
					    isvisibleinadmin = 1
					ORDER BY
					    tablabel ASC'
			);
			
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$theStatus      = self::getStatusModule ($adb, $row ['name'], $userId);
					$row ['status'] = (!empty($theStatus)) ?  $theStatus : 'SHOW';
					$modules []     = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($modules) && count ($preference)) {
				if (isset ($preference['HIDDEN'])) {
					foreach ($modules as &$module) {
						if (in_array ($module['name'], $preference['HIDDEN'])) {
							$module['status'] = 'HIDDEN';
						} else if (in_array ('ALL', $preference['HIDDEN'])) {
							$module['status'] = 'HIDDEN';
						} else {
							continue;
						}
					}
				}
				if (isset ($preference['SHOW'])) {
					foreach ($modules as &$module) {
						if (in_array ($module['name'], $preference['SHOW'])) {
							$module['status'] = 'SHOW';
						} else if (in_array ('ALL', $preference['SHOW'])) {
							$module['status'] = 'SHOW';
						} else {
							continue;
						}
					}
				}
			}
			return isset ($modules) ? $modules : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $userId
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function getStatusModule ($adb, $moduleName, $userId) {
			if (empty ($moduleName)) {
				return null;
			}
			if (empty ($userId)) {
				$whereUser = '';
				$params    = array ($moduleName);
			} else {
				$whereUser = ' AND userId = ?';
				$params    = array ($moduleName, $userId);
			}
			$result = $adb->pquery ("SELECT status FROM vtiger_views_task WHERE tab_name=? {$whereUser}", $params);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$status = $row ['status'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return isset ($status) ? $status : null;
		}
		
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tabName
		 * @param string $status
		 * @param integer $userId
		 *
		 * @return void
		 * @throws Exception
		 */
		public static function setStatusModule ($adb, $tabName, $status, $userId) {
			if (empty ($tabName) || empty ($status)) {
				throw new Exception ('tabName or status is empty');
			}
			$theStatus      = self::getStatusModule ($adb, $tabName, $userId);
			if (!empty($theStatus)) {
				$adb->pquery ('UPDATE vtiger_views_task SET status=? WHERE tab_name=? AND userId=?', array ($status, $tabName, $userId));
			} else {
				$adb->pquery ('INSERT INTO vtiger_views_task (tab_name, status, userId) VALUES (?, ?, ?)', array ($tabName, $status, $userId));
			}
		}
	
	}
