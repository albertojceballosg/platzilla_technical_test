<?php
	require_once ('include/platzilla/Data/EntityComments.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class EntityCommentsUtils {

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $platformName
		 *
		 * @return EntityComments[]|null
		 * @throws Exception
		 */
		public static function fetchComments (PearDatabase $adb, $entityId, $platformName) {
			if (empty ($entityId)) {
				return null;
			}

			$result = $adb->pquery (
				"SELECT
					cc.*,
					u.id AS userid,
					TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS userfullname
				FROM
					vtiger_crmentity_comments cc
					INNER JOIN vtiger_crmentity crme ON crme.crmid=cc.crmid
					INNER JOIN vtiger_users u ON u.id=cc.writtenby
				WHERE
					cc.crmid=?",
				array ($entityId)
			);
			if ($adb->num_rows ($result) > 0) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$comments       = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$avatarUri   = "{$platformName}/user_images/Avatar_{$row ['userid']}.png";
					$comments [] = EntityComments::getInstance()
						->setCommentId ($row ['commentid'])
						->setCommentType ($row ['type'])
						->setCrmId ($row ['crmid'])
						->setStatement ($row ['statement'])
						->setUserAvatar ((file_exists ("{$rootFolderPath}/{$avatarUri}")) ? $avatarUri : null)
						->setUserName ($row ['userfullname'])
						->setWrittenBy ($row ['writtenby'])
						->setWrittenOn ($row ['writtenon']);
				}
			} else {
				$comments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $comments;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param EntityComments $note
		 *
		 * @return null
		 * @throws Exception
		 */
		public static function saveComment (PearDatabase $adb, $note) {
			if ((empty ($note)) || (!$note instanceof EntityComments)) {
				return null;
			}
			
			$adb->pquery (
				'INSERT INTO vtiger_crmentity_comments (crmid, statement, type, writtenby, writtenon) VALUES (?, ?, ?, ?, ?)',
				array ($note->getCrmId (), $note->getStatement (), $note->getCommentType (), $note->getWrittenBy (), $note->getWrittenOn ())
			);
		}

	}
