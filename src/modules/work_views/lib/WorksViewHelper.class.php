<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/work_views/Objects/WorksView.php');
	
	abstract class WorksViewHelper {
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param integer $workViewId
		 *
		 * @throws Exception
		 */
		public static function deleteWorkView ($adb, $userId, $workViewId) {
			if (empty ($workViewId)) {
				throw new Exception ('No has suministrado el ID de la vista a eliminar');
			} else if (empty ($userId)) {
				throw new Exception ('No se encontró el propietario de la vista');
			}
			$adb->pquery ('DELETE FROM vtiger_project_works_view WHERE projectworksviewid=? AND form_user=?', array ($workViewId, $userId));
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer $userId
		 *
		 * @return WorksView[]|null
		 * @throws Exception
		 */
		public static function fetchWorksView ($adb, $userId) {
			if (empty($userId)) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_project_works_view WHERE form_user=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$jobViews = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$jobViews [] = WorksView::getInstance ()
						->setFormUser ($row ['form_user'])
						->setId ($row ['projectworksviewid'])
						->setView ($row ['view'])
						->setViewStatus ($row ['views_status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($jobViews)) ? $jobViews : null;
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer $userId
		 * @param integer $workViewId
		 *
		 * @return null|WorksView
		 * @throws Exception
		 */
		public static function fetchWorkViewById ($adb, $userId, $workViewId) {
			if (empty($userId)) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_project_works_view WHERE form_user=? AND projectworksviewid=?', array ($userId, $workViewId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$jobView = WorksView::getInstance ()
					->setFormUser ($row ['form_user'])
					->setId ($row ['projectworksviewid'])
					->setView ($row ['view'])
					->setViewStatus ($row ['views_status']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($jobView)) ? $jobView : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $view
		 *
		 * @return null|WorksView
		 * @throws Exception
		 */
		public static function fetchWorkViewByView ($adb, $userId, $view) {
			if (empty($userId)) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_project_works_view WHERE form_user=? AND view=?', array ($userId, $view));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$jobView = WorksView::getInstance ()
					->setFormUser ($row ['form_user'])
					->setId ($row ['projectworksviewid'])
					->setView ($row ['view'])
					->setViewStatus ($row ['views_status']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($jobView)) ? $jobView : null;
		}
		
		/**
		 * @param $adb
		 * @param WorksView $workView
		 *
		 * @throws Exception
		 */
		public static function saveWorkView ($adb, $workView) {
			$workView->validate();
			
			$jobView = self::fetchWorkViewByView ($adb, $workView->getFormUser (), $workView->getView ());
			if (!empty ($jobView)) {
				$workView->setId ($jobView->getId ());
			}
			
			if (empty ($workView->getId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_project_works_view (view, views_status, form_user) VALUES (?, ?, ?)',
					array ($workView->getView (), $workView->getViewStatus (), $workView->getFormUser ())
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_project_works_view SET view=?, views_status=?, form_user=? WHERE projectworksviewid=?',
					array ($workView->getView (), $workView->getViewStatus (), $workView->getFormUser (), $workView->getId ())
				);
			}
		}
		
	}
