<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/preloaded_tasks/Objects/AreaActivity.php');
	require_once ('modules/preloaded_tasks/Objects/PrecreatedTask.php');
	
	class PrecreatedTaskUtils {
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		public function __construct() {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		}
		
		/**
		 * @param string $codeArea
		 *
		 * @return boolean
		 */
		public function checkCodeAreaActivity ($codeArea) {
			$result    = $this->masterAdb->pquery('SELECT * FROM vtiger_area_activity WHERE codearea=?', array($codeArea));
			$isCreated = ($this->masterAdb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $isCreated;
		}
		
		/**
		 * @param integer $idTask
		 *
		 * @throws Exception
		 */
		public function deletePrecreatedTask ($idTask) {
			if (empty($idTask)) {
				throw new Exception ('Imposible eliminar la tarea');
			}
			$this->masterAdb->pquery ('DELETE FROM vtiger_activity2precreated WHERE precreatedid=?',array ($idTask));
		}
		
		/**
		 * @param null|string $status
		 *
		 * @return AreaActivity[]|null
		 * @throws Exception
		 */
		public function fetchAreaActivity ($status = null) {
			if (!empty ($status) && in_array ($status, array (PrecreatedTaskInterface::PRECRATED_TASK_ENABLED, PrecreatedTaskInterface::PRECRATED_TASK_DISABLED))) {
				$result = $this->masterAdb->pquery('SELECT * FROM vtiger_area_activity WHERE 1 AND status=? ORDER BY areaname ASC', array ($status));
			} else {
				$result = $this->masterAdb->query('SELECT * FROM vtiger_area_activity WHERE 1 ORDER BY areaname ASC');
			}
			
			if ($this->masterAdb->num_rows ($result) > 0) {
				$areaActivity = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$areaActivity [] = AreaActivity::getInstance ()
						->setId ($row['areaactivityid'])
						->setAreaName ($row['areaname'])
						->setCodeArea ($row['codearea'])
						->setStatus ($row['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($areaActivity)) ? $areaActivity : null;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return AreaActivity|null
		 * @throws Exception
		 */
		public function fetchAreaActivityById ($id) {
			$result = $this->masterAdb->pquery('SELECT * FROM vtiger_area_activity WHERE areaactivityid=?', array($id));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$areaActivity = AreaActivity::getInstance ()
						->setId ($row['areaactivityid'])
						->setAreaName ($row['areaname'])
						->setCodeArea ($row['codearea'])
						->setStatus ($row['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($areaActivity)) ? $areaActivity : null;
		}
		
		/**
		 * @param null|string $status
		 *
		 * @return PrecreatedTask[]|null
		 * @throws Exception
		 */
		public function fetchPreCreatedTask ($status = null) {
			if (!empty ($status) && in_array ($status, array (PrecreatedTaskInterface::PRECRATED_TASK_ENABLED, PrecreatedTaskInterface::PRECRATED_TASK_DISABLED))) {
				$where = "ap.status='{$status}' AND aa.status='{$status}'";
			} else {
				$where = 1;
			}
			$result = $this->masterAdb->query (
				"SELECT
						ap.*,
						t.tablabel,
						aa.areaname
					  FROM
					  	vtiger_activity2precreated ap
					  INNER JOIN vtiger_tab t ON t.name = ap.tabname
					  INNER JOIN vtiger_area_activity aa ON aa.codearea = ap.codearea
					  WHERE {$where}"
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$preCreateTasks = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$preCreateTasks[] = PrecreatedTask::getInstance ()
						->setId ($row['precreatedid'])
						->setAreaName ($row['areaname'])
						->setTabName ($row['tabname'])
						->setStatus ($row['status'])
						->setModuleName ($row['tablabel'])
						->setCodeArea ($row['codearea'])
						->setTaskDescription ($row['taskdescription'])
						->setTaskName ($row['taskname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($preCreateTasks)) ? $preCreateTasks : null;
		}
		
		/**
		 * @param $id
		 *
		 * @return null|PrecreatedTask
		 * @throws Exception
		 */
		public function fetchPreCreatedTaskById ($id) {
			if (empty($id)) {
				return null;
			}
			$result = $this->masterAdb->pquery (
				'SELECT
						ap.*,
						t.tablabel,
						aa.areaname
					  FROM
					  	vtiger_activity2precreated ap
					  INNER JOIN vtiger_tab t ON t.name = ap.tabname
					  INNER JOIN vtiger_area_activity aa ON aa.codearea = ap.codearea
					  WHERE precreatedid=?',
				array ($id)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$preCreateTask = PrecreatedTask::getInstance ()
						->setId ($row['precreatedid'])
						->setAreaName ($row['areaname'])
						->setTabName ($row['tabname'])
						->setStatus ($row['status'])
						->setModuleName ($row['tablabel'])
						->setCodeArea ($row['codearea'])
						->setTaskDescription ($row['taskdescription'])
						->setTaskName ($row['taskname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($preCreateTask)) ? $preCreateTask : null;
		}
		
		/**
		 * @param AreaActivity $areaActivity
		 *
		 * @return null
		 */
		public function saveAreaActivity ($areaActivity) {
			if (!$areaActivity instanceof AreaActivity) {
				return null;
			}
			
			if (empty ($areaActivity->getId ())) {
				$this->masterAdb->pquery (
					'INSERT INTO vtiger_area_activity (codearea, areaname, status) VALUES (?, ?, ?)',
					array ($areaActivity->getCodeArea (), $areaActivity->getAreaName (), $areaActivity->getStatus ())
				);
			} else {
				$this->masterAdb->pquery (
					'UPDATE vtiger_area_activity SET areaname=?, status=? WHERE areaactivityid=?',
					array ($areaActivity->getAreaName (), $areaActivity->getStatus (), $areaActivity->getId ())
				);
			}
			return $areaActivity;
		}
		
		/**
		 * @param PrecreatedTask $preCreateTasks
		 *
		 * @return null
		 */
		public function savePreCreatedTask ($preCreateTasks) {
			if (!$preCreateTasks instanceof PrecreatedTask) {
				return null;
			}
			
			if (empty ($preCreateTasks->getId ())) {
				$this->masterAdb->pquery (
					'INSERT INTO vtiger_activity2precreated (codearea, taskname, taskdescription, tabname, status) VALUES (?, ?, ?, ?, ?)',
					array ($preCreateTasks->getCodeArea (), $preCreateTasks->getTaskName (), $preCreateTasks->getTaskDescription (), $preCreateTasks->getTabName (), $preCreateTasks->getStatus ())
				);
			} else {
				$this->masterAdb->pquery (
					'UPDATE vtiger_activity2precreated SET codearea=?, taskname=?, taskdescription=?, tabname=?, status=? WHERE precreatedid=?',
					array ($preCreateTasks->getCodeArea (), $preCreateTasks->getTaskName (), $preCreateTasks->getTaskDescription (), $preCreateTasks->getTabName (), $preCreateTasks->getStatus (), $preCreateTasks->getId ())
				);
			}
			return $preCreateTasks;
		}
		
		/**
		 * @param string $status
		 * @param integer $id
		 *
		 * @throws Exception
		 */
		public function upDateAreaStatus ($status, $id) {
			if (
				!in_array ($status, array (PrecreatedTaskInterface::PRECRATED_TASK_DISABLED, PrecreatedTaskInterface::PRECRATED_TASK_ENABLED)) ||
				empty($id)
			) {
				throw new Exception ('Imposible cambiar estatus.');
			}
				$this->masterAdb->pquery ('UPDATE vtiger_area_activity SET status=? WHERE areaactivityid=?', array ($status, $id));
		}
		
		/**
		 * @param string $status
		 * @param integer $id
		 *
		 * @throws Exception
		 */
		public function upDatePreCreateTaskStatus ($status, $id) {
			if (
				!in_array ($status, array (PrecreatedTaskInterface::PRECRATED_TASK_DISABLED, PrecreatedTaskInterface::PRECRATED_TASK_ENABLED)) ||
				empty($id)
			) {
				throw new Exception ('Imposible cambiar estatus.');
			}
				$this->masterAdb->pquery ('UPDATE vtiger_activity2precreated SET status=? WHERE precreatedid=?', array ($status, $id));
		}
		
	}
