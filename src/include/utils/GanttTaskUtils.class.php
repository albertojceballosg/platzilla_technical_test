<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	
	abstract  class GanttTaskUtils {
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 *
		 * @throws Exception
		 */
		public static function deleteGanttTask ($adb, $viewId) {
			if (empty($viewId)) {
				throw new Exception ('No has suministrado el ID de la vista a eliminar');
			}
			$adb->pquery ('DELETE FROM vtiger_gantt_tasks WHERE gantttasksid=?', array ($viewId));
		}
		
		public static function fetchAvailableModules ($adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModulesByType(Module::TYPE_USER, true);
			if (!empty ($modules)) {
				$availableModules = array ();
				foreach ($modules as $module) {
					if ($module->getPresence () !== 0) {
						continue;
					}
					$availableModules [] = $module;
				}
			}
			return (isset($availableModules)) ? $availableModules : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function fecthGanttById ($adb, $viewId) {
			if (empty($viewId)) {
				throw new Exception ('Kanba task no encontrado!');
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_gantt_tasks WHERE gantttasksid=?', array ($viewId));
			if ($adb->num_rows ($result)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($row)) ? $row : null;
		
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param string $moduleName
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function fetchGanttByModule ($adb, $moduleName) {
			if (empty($moduleName)) {
				throw new Exception ('Uoops! no ha seleccionado el modulo');
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_gantt_tasks WHERE tabname=? LIMIT 1', array ($moduleName));
			if ($adb->num_rows ($result)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($row)) ? $row : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchGantts ($adb) {
			$result  = $adb->query (
				'SELECT kt.*, t.tablabel
					FROM vtiger_gantt_tasks kt
					INNER JOIN vtiger_tab t ON t.name = kt.tabname
					WHERE 1
					ORDER BY t.tablabel'
			);
			if ($adb->num_rows ($result) > 0) {
				$kabanTasks = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$kabanTasks [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($kabanTasks)) ? $kabanTasks : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $data
		 *
		 * @throws Exception
		 */
		public static function  saveGanttTask ($adb, $data)	{
			$ganttTask = self::fetchGanttByModule ($adb, $data['tab_name']);
			
			if (empty ($ganttTask)) {
				$adb->pquery (
					'INSERT INTO vtiger_gantt_tasks (tabname, detail_view, list_view, form_user) VALUES (?, ?, ?, ?)',
					array ($data ['tab_name'], $data ['detail_view'], $data ['list_view'], $data ['user_id'])
				);
			} else {
				$data ['view_id'] = (!empty($data ['view_id'])) ? $data ['view_id'] : $ganttTask['gantttasksid'];
				$adb->pquery (
					'UPDATE vtiger_gantt_tasks SET tabname=?, detail_view=?, list_view=?, form_user=? WHERE gantttasksid=?',
					array ($data ['tab_name'], $data ['detail_view'], $data ['list_view'], $data ['user_id'], $data ['view_id'])
				);
			}
		}
		
	}
