<?php
	require_once ('include/platzilla/Data/ActivityFeedback.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	class ActivityFeedbackManager	{

		/** @var ActivityFeedbackManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}
		
		
		/**
		 * @param integer $activityId
		 *
		 * @return ActivityFeedback[]|null
		 * @throws Exception
		 */
		public function fetchActivityFeedbackByActivity ($activityId) {
			if (empty($activityId)) {
				return null;
			}
						
			// Primero intentar con la consulta original
			$result = $this->adb->pquery (
				'SELECT DISTINCT
						af.*,
						CONCAT(u.first_name, " ", u.last_name) AS username
					  FROM
						vtiger_activity_feedback af
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = af.activityid
					  INNER JOIN vtiger_activity a ON a.activityid = af.activityid
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = sa.crmid
					  INNER JOIN vtiger_users u ON u.id = af.userid
					  WHERE
					  	crm.crmid=?',
				array ($activityId)
			);
			
			// Si no hay resultados, intentar consulta alternativa directa por activityid
			if ($this->adb->num_rows($result) == 0) {
				$result = $this->adb->pquery (
					'SELECT DISTINCT
							af.*,
							CONCAT(u.first_name, " ", u.last_name) AS username
						  FROM
							vtiger_activity_feedback af
						  INNER JOIN vtiger_users u ON u.id = af.userid
						  WHERE
						  	af.activityid=?',
					array ($activityId)
				);
			}
			
			if ($this->adb->num_rows ($result) > 0) {
				$dummy  = explode('_', $this->adb->dbName);
				$dbName = $dummy[ 2 ];
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$activityFeedBacks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$avatarUri = "{$dbName}/user_images/Avatar_{$row ['userid']}.png";
					if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
						$avatarUri = 'themes/centaurus/img/photo.png';
					}
					$activityFeedBacks [] = ActivityFeedback::getInstance()
						->setId ($row ['activityfeedbackid'])
						->setActivityId ($row ['activityid'])
						->setFeedback ($row ['feedback'])
						->setFeedbackDate ($row ['feedbackdate'])
						->setTitle ($row ['title'])
						->setUserAvatar ($avatarUri)
						->setUserId ($row ['userid'])
						->setUserName ($row ['username']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityFeedBacks)) ? $activityFeedBacks : null;
		}
		
		/**
		 * @param integer $entityId
		 *
		 * @return ActivityFeedback[]|null
		 * @throws Exception
		 */
		public function fetchActivityFeedback ($entityId, $feedbackExcluded = array()) {
			if (empty($entityId)) {
				return null;
			}
			$whereFeedbacks = '';
			if (count ($feedbackExcluded)) {
				$whereFeedbacks = "af.activityfeedbackid NOT IN{$this->adb->sql_expr_datalist ($feedbackExcluded)} AND ";
			}
			
			$result = $this->adb->pquery (
				"SELECT DISTINCT
						af.*,
						CONCAT(u.first_name, '', u.last_name) AS username
					  FROM
						vtiger_activity_feedback af
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = af.activityid
					  INNER JOIN vtiger_activity a ON a.activityid = sa.activityid
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = sa.crmid  AND crm.deleted=0
					  INNER JOIN vtiger_users u ON u.id = af.userid
					  WHERE
					  {$whereFeedbacks}
					  	a.eventstatus !=? AND
					  	crm.crmid=?",
				array ('Held', $entityId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$dummy  = explode('_', $this->adb->dbName);
				$dbName = $dummy[ 2 ];
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$activityFeedBacks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$avatarUri = "{$dbName}/user_images/Avatar_{$row ['userid']}.png";
					if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
						$avatarUri = 'themes/centaurus/img/photo.png';
					}
					$activityFeedBacks [] = ActivityFeedback::getInstance()
						->setId ($row ['activityfeedbackid'])
						->setActivityId ($row ['activityid'])
						->setFeedback ($row ['feedback'])
						->setFeedbackDate ($row ['feedbackdate'])
						->setTitle ($row ['title'])
						->setUserAvatar ($avatarUri)
						->setUserId ($row ['userid'])
						->setUserName ($row ['username']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityFeedBacks)) ? $activityFeedBacks : null;
		}

		/**
		 * @param integer $feekBackId
		 *
		 * @return ActivityFeedback|null
		 * @throws Exception
		 */
		public function fetchActivityFeedbackById ($feekBackId) {
			if (empty($feekBackId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
						af.*,
						CONCAT(u.first_name, " ", u.last_name) AS username
					  FROM
						vtiger_activity_feedback af
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = af.activityid
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = sa.crmid AND crm.deleted=0
					  INNER JOIN vtiger_users u ON u.id = af.userid
					  WHERE af.activityfeedbackid=?',
				array ($feekBackId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$dummy  = explode('_', $this->adb->dbName);
				$dbName = $dummy[ 2 ];
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$avatarUri = "{$dbName}/user_images/Avatar_{$row ['userid']}.png";
				if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
					$avatarUri = 'themes/centaurus/img/photo.png';
				}
				$activityFeedBack = ActivityFeedback::getInstance()
					->setId ($row ['activityfeedbackid'])
					->setActivityId ($row ['activityid'])
					->setFeedback ($row ['feedback'])
					->setFeedbackDate ($row ['feedbackdate'])
					->setTitle ($row ['title'])
					->setUserAvatar ($avatarUri)
					->setUserId ($row ['userid'])
					->setUserName ($row ['username']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityFeedBack)) ? $activityFeedBack : null;
		}
		
		/**
		 * @param integer $reportId
		 *
		 * @return ActivityFeedback[]|null
		 * @throws Exception
		 */
		public function fetchFeedbackByReport ($reportId) {
			if (empty($reportId)) {
				return null;
			}
			
			$result = $this->adb->pquery ('SELECT DISTINCT activityfeedbackid FROM vtiger_activity_report2feedback WHERE activityreportid=?', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$activityFeedBacks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$activityFeedBacks [] = $this->fetchActivityFeedbackById ($row ['activityfeedbackid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityFeedBacks)) ? $activityFeedBacks : null;
		}
		
		/**
		 * @param integer $entityId
		 * @param integer $userId
		 *
		 * @return ActivityFeedback[]|null
		 * @throws Exception
		 */
		public function fetchActivityFeedbackByUser ($entityId, $userId) {
			if (empty($entityId) || empty($userId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
						ar.*,
						CONCAT(u.first_name, " ", u.last_name) AS username
					  FROM
						vtiger_activity_feedback af
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = ar.activityid
					  INNER JOIN vtiger_crmentity crm ON  cr.crmid = sa.crmid
					  INNER JOIN vtiger_users u ON u.id = af.userid
					  WHERE 
					  	crm.crmid=? AND 
					  	ra.userid=?',
				array ($entityId, $userId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$dummy  = explode('_', $this->adb->dbName);
				$dbName = $dummy[ 2 ];
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$activityFeedBacks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$avatarUri = "{$dbName}/user_images/Avatar_{$row ['userid']}.png";
					if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
						$avatarUri = 'themes/centaurus/img/photo.png';
					}
					$activityFeedBacks [] = ActivityFeedback::getInstance()
						->setId ($row ['activityreportid'])
						->setActivityId ($row ['activityid'])
						->setFeedback ($row ['feedback'])
						->setFeedbackDate ($row ['feedbackdate'])
						->setTitle ($row ['title'])
						->setTitle ($row ['title'])
						->setUserAvatar ($avatarUri)
						->setUserId ($row ['userid'])
						->setUserName ($row ['username']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityFeedBacks)) ? $activityFeedBacks : null;
		}

		
		/**
		 * @param ActivityFeedback $activityFeedBack
		 *
		 * @return ActivityFeedback;
		 * @throws Exception
		 */
		public function saveActivityFeedback ($activityFeedBack) {
			if (!$activityFeedBack instanceof ActivityFeedback) {
				throw new Exception ('Se ha presentado un error, por favor intente mas tarde');
			}

			if (empty ($activityFeedBack->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_activity_feedback (activityid, userid, feedback, title) VALUES (?, ?, ?, ?)',
					array ($activityFeedBack->getActivityId(), $activityFeedBack->getUserId (), $activityFeedBack->getFeedback (), $activityFeedBack->getTitle ())
				);
				$activityFeedBack->setId ($this->adb->getLastInsertID ());
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_activity_feedback SET activityid=?, userid=?, feedback=?, feedbackdate=?, title=? WHERE activityfeedbackid=?',
					array ($activityFeedBack->getActivityId (), $activityFeedBack->getUserId (), $activityFeedBack->getFeedback (), date('Y-m-d H:i:s'), $activityFeedBack->getTitle (), $activityFeedBack->getId ())
				);
			}
			return $activityFeedBack;
		}
		
		/**
		 * @param integer $actitvityId
		 * @param integer $reportId
		 * @param integer $feedbackId
		 *
		 * @throws Exception
		 */
		public function saveReportToFeedback ($actitvityId, $reportId, $feedbackId) {
			if (empty($actitvityId) || empty($reportId) || empty($feedbackId)) {
				throw new Exception ('Imposible relacionar el feedback con el reporte');
			}
			
			$this->adb->pquery (
				'INSERT INTO vtiger_activity_report2feedback (activityreportid, activityfeedbackid, activityid) VALUES (?, ?, ?)',
				array ($reportId, $feedbackId, $actitvityId)
			);
		}
		
		/**
		 * @param integer $feedbackId
		 *
		 * @throws Exception
		 */
		public function deleteReportFromFeedback ($feedbackId) {
			if (empty($feedbackId)) {
				throw new Exception ('Imposible eliminar la relación del feedback');
			}
			
			$this->adb->pquery (
				'DELETE FROM vtiger_activity_report2feedback WHERE activityfeedbackid = ?',
				array ($feedbackId)
			);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ActivityFeedbackManager|mixed
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
