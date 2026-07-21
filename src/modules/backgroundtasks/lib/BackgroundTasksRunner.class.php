<?php
	require_once ('include/platzilla/Managers/BackgroundTaskManager.php');
	require_once ('include/platzilla/Objects/PlatformInstance.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('log4php/LoggerManager.php');

	class BackgroundTasksRunner {
		/** @var PearDatabase */
		private $adb;

		/** @var string */
		private $platform;

		public function __construct (PearDatabase $adb, $platform) {
			if (!is_dir (__DIR__ . "/../../../{$platform}/logs/backgroundtasks")) {
				mkdir (__DIR__ . "/../../../{$platform}/logs/backgroundtasks", 0777, true);
			}
			$this->adb      = $adb;
			$this->platform = $platform;
		}

		/**
		 * @param BackgroundTask $task
		 * @param string $lockFilePath
		 *
		 * @return boolean
		 */
		private function isTaskRunning ($task, $lockFilePath) {
			$frequency = $task->getFrequency ();

			if (file_exists ($lockFilePath)) {
				$lastExecutionDate = strtotime (file_get_contents ($lockFilePath));
				$now               = strtotime (date_create ()->format ('Y-m-d H:i:s'));
				$difference        = ($now - $lastExecutionDate);
			} else {
				$difference = $task->getFrequency ();
			}
			return ($difference < $frequency);
		}

		/**
		 * @param BackgroundTask $task
		 * @param CRMEntity|stdClass $sourceEntity
		 *
		 * @throws Exception
		 */
		private function runTask ($task, CRMEntity $sourceEntity = null) {
			if (empty ($task)) {
				return;
			}
			BackgroundTaskManager::getInstance ($this->adb)->runTask ($task, $this->platform, $sourceEntity);
		}

		/**
		 * @param string $event
		 * @param string $eventInstant
		 * @param CRMEntity|PlatformInstance $sourceEntity
		 *
		 * @throws Exception
		 */
		public function runEventTriggeredTasks ($event, $eventInstant, $sourceEntity) {
			if (empty ($sourceEntity)) {
				throw new Exception ('No se ha suministrado la entidad');
			}

			$btm   = BackgroundTaskManager::getInstance ($this->adb);
			$tasks = $btm->fetchEventTriggeredTasks (get_class ($sourceEntity), $event, $eventInstant, BackgroundTaskInterface::STATUS_ENABLED);
			if (empty ($tasks)) {
				return;
			}

			foreach ($tasks as $task) {
				$btm->runTask ($task, $this->platform, $sourceEntity);
			}
		}

		public function runScheduledTasks () {
			$btm   = BackgroundTaskManager::getInstance ($this->adb);
			$tasks = $btm->fetchRunnableScheduledTasks ();
			if (empty ($tasks)) {
				return;
			}

			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			foreach ($tasks as $task) {
				$lockFilePath = "{$rootFolderPath}/{$this->platform}/logs/backgroundtasks/{$task->getName ()}.lock";
				if ($this->isTaskRunning ($task, $lockFilePath)) {
					continue;
				}
				file_put_contents ($lockFilePath, date_create ()->format ('Y-m-d H:i:s'));
				$btm->runTask ($task, $this->platform);
				unlink ($lockFilePath);
			}
		}

		/**
		 * @param string $taskName
		 * @param integer $entityId
		 *
		 * @throws Exception
		 */
		public function runManuallyTriggeredTask ($taskName, $entityId = null) {
			$btm  = BackgroundTaskManager::getInstance ($this->adb);
			$task = $btm->fetchTaskByName ($taskName);
			if (empty ($task)) {
				throw new Exception ("No se encuentra registrada la tarea {$taskName}");
			} else if ($task->getStatus () != BackgroundTaskInterface::STATUS_ENABLED) {
				throw new Exception ("La tarea {$taskName} no está habilitada o no es ejecutable manualmente");
			} else if ($task->getTrigger () != BackgroundTaskInterface::TRIGGER_MANUAL) {
				throw new Exception ("La tarea {$taskName} no es ejecutable manualmente");
			}

			$moduleName = $task->getModuleName ();
			if (empty ($entityId)) {
				$sourceEntity = null;
			} else if ($moduleName == 'instances') {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE instanceid=?', array ($entityId));
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					throw new Exception ("No se encuentra registrada la instancia con el ID {$entityId}");
				}
				$row                   = $this->adb->fetchByAssoc ($result, -1, false);
				$row ['record_id']     = $entityId;
				$row ['record_module'] = 'instances';

				require_once ('modules/instances/instances.php');
				$sourceEntity                = instances::getInstance ();
				$sourceEntity->id            = $entityId;
				$sourceEntity->column_fields = $row;
			} else if ($moduleName == 'notifications') {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_notifications WHERE notificationid=? AND sendbyemail=? ', array ($entityId, 'ACTIVE'));
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					throw new Exception ("No se encuentra registrada la notificación con el ID {$entityId} o no esta activo el envío al panel de mensajes");
				}
				$row                   = $this->adb->fetchByAssoc ($result, -1, false);
				$row ['record_id']     = $entityId;
				$row ['record_module'] = 'notifications';
				$row ['contents']      = strip_tags (stripslashes ($row ['contents']),'<div><p><strong><a><img>' );
				$row ['contents']      = str_replace ('×<strong', '<strong', $row ['contents']);
				$row ['contents']      = str_replace ('themes/centaurus/img/platzillaman.png', '', $row ['contents']);
				require_once ('modules/notifications/notifications.php');
				$sourceEntity                = notifications::getInstance ();
				$sourceEntity->id            = $entityId;
				$sourceEntity->column_fields = $row;
			} else {
				$result = $this->adb->pquery ('SELECT crme.* FROM vtiger_crmentity crme WHERE crme.crmid=?', array ($entityId));
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					throw new Exception ("No se encuentra registrada la entidad con el ID {$entityId}");
				}

				$row          = $this->adb->fetchByAssoc ($result, -1, false);
				$sourceEntity = PlatformUtils::getCrmEntity ($this->adb, $row ['setype'], $entityId);
			}

			$this->runTask ($task, $sourceEntity);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $platform
		 *
		 * @return BackgroundTasksRunner
		 */
		public static function getInstance (PearDatabase $adb, $platform) {
			return new self ($adb, $platform);
		}

	}
