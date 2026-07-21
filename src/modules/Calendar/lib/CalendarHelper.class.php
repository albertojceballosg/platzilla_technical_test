<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/RecurringType.php');

	abstract class CalendarHelper {
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $categoryName
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function checkTaskCategory ($adb, $userId, $categoryName) {
			$isCategory = false;
			$result = $adb->pquery ('SELECT categoryid FROM vtiger_activity_categories WHERE smownerid=? AND name=?', array ($userId, $categoryName));
			if ($adb->num_rows ($result) > 0) {
				$isCategory= true;
			}
			DatabaseUtils::closeResult ($result);
			return $isCategory;
		}

		public static function getActivityData (PearDatabase $adb, $activityId) {
			if (empty ($activityId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT activitytype FROM vtiger_activity WHERE activityid=?', array ($activityId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		public static function getActivityMode (array $activity) {
			$activityMode = null;
			if ($activity ['activitytype'] == 'Task') {
				$activityMode = 'Task';
			} else if ($activity ['activitytype'] != 'Emails') {
				$activityMode = 'Events';
			}
			return $activityMode;
		}

		public static function getInvitedUsers (PearDatabase $adb, $activityId) {
			if (empty ($activityId)) {
				return array ();
			}

			$result = $adb->pquery ('SELECT u.*, i.* FROM vtiger_invitees i LEFT JOIN vtiger_users u ON i.inviteeid=u.id WHERE activityid=?', array ($activityId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$invitedUsers = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$userId                   = $row ['inviteeid'];
				$fullName                 = trim ("{$row ['first_name']} {$row ['last_name']}");
				$invitedUsers [ $userId ] = $fullName;
			}
			return $invitedUsers;
		}

		public static function getRecurringData (PearDatabase $adb, $moduleName, $activityId) {
			if (empty ($activityId)) {
				return array ();
			}

			$result = $adb->pquery (
				'SELECT
					re.*,
					a.date_start,
					a.time_start,
					a.due_date,
					a.time_end
				FROM
					vtiger_recurringevents re
					INNER JOIN vtiger_activity a ON a.activityid=re.activityid
				WHERE
					re.activityid=?',
				array ($activityId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array (
					'recurringcheck' => getTranslatedString ('LBL_NO', $moduleName),
					'repeat_str'     => '',
				);
			}

			$recurringObject = RecurringType::fromDBRequest ($adb->query_result_rowdata ($result, 0));
			return $recurringObject->getDisplayRecurringInfo ();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $categoryName
		 * @param integer $userId
		 * @param integer $categoryId
		 *
		 * @return integer
		 */
		public static function saveTaskCategory ($adb, $categoryName, $userId, $categoryId) {
			if (!$categoryId) {
				$categoryId = $adb->getUniqueID ('vtiger_activity_categories');
			}
			$adb->pquery ('INSERT INTO vtiger_activity_categories (categoryid, smownerid, name, description) VALUES (?, ?, ?, ?)', array($categoryId, $userId, $categoryName, $categoryName));
			return $categoryId;
		}
		
	}
