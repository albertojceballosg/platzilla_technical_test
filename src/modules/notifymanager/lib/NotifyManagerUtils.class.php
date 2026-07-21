<?php
	require_once ('include/utils/AdbManager.class.php');

	abstract class NotifyManagerUtils {

		public static function getNotifications ($instanceName, $moduleName, $action) {
			if (empty ($instanceName)) {
				return null;
			}

			$instanceDatabaseName = "pg_crm_{$instanceName}";
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->pquery (
				"SELECT DISTINCT
					mnm.*
				FROM
					vtiger_notifymanager mnm
					LEFT JOIN {$instanceDatabaseName}.vtiger_notifymanager inm ON inm.notifyid=mnm.notifyid
				WHERE
					mnm.active=1 AND
					(inm.active IS NULL OR inm.active=1) AND
					mnm.action=?",
				array ($action)
			);
			if ((!$result) || ($masterAdb->num_rows ($result) == 0)) {
				return null;
			}

			$notifications = array ();
			while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
				$moduleNames = explode ('#', $row ['module']);
				if (!in_array ($moduleName, $moduleNames)) {
					continue;
				}
				$notifications [] = $row;
			}

			return count ($notifications) > 0 ? $notifications : null;
		}

		public static function disableNotification ($instanceName, $notificationId) {
			if ((empty ($instanceName)) || (empty ($notificationId))) {
				return;
			}

			$instanceAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$result = $instanceAdb->pquery ('SELECT * FROM vtiger_notifymanager WHERE notifyid=?', array ($notificationId));
			if ((!$result) || ($instanceAdb->num_rows ($result) == 0)) {
				$instanceDatabaseName = "pg_crm_{$instanceName}";
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$masterAdb->pquery (
					"INSERT INTO {$instanceDatabaseName}.vtiger_notifymanager (notifyid, module, action, title, description, design, active)
					SELECT
						mnm.notifyid,
						mnm.module,
						mnm.action,
						mnm.title,
						mnm.description,
						mnm.design,
						0 AS active
					FROM
						vtiger_notifymanager mnm
					WHERE
						mnm.notifyid=?",
					array ($notificationId)
				);
			} else {
				$instanceAdb->pquery ('UPDATE vtiger_notifymanager SET active=0 WHERE notifyid=?', array ($notificationId));
			}
		}

	}