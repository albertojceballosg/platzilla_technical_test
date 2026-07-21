<?php
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Objects/HowToUse.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	class HowToUseManager {

		/** @var HowToUseManager|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/** @var PearDatabase  */
		private $masterAdb;

		public function __construct (PearDatabase $adb) {
			$this->adb       = $adb;
			$pos             = strpos ($this->adb->dbName,'madre');
			$this->masterAdb = ($pos !== false) ? $adb : AdbManager::getInstance ()->getMasterAdb ();
		}

		/**
		 * @param HowToUse $howToUse
		 */
		private function clearDefaults ($howToUse) {
			$this->adb->pquery(
				'UPDATE vtiger_how_use SET isdefault=? WHERE tabname=?',
				array (0, $howToUse->getTabName ())
			);
		}

		/**
		 * @param HowUseView[] $howUseView
		 *
		 * @throws Exception
		 */
		private function codeUpdater (&$howUseView) {
			if (empty($howUseView)) {
				return;
			}

			$totalHowUseView = count ($howUseView);
			for ($i = 0; $i < $totalHowUseView; $i++) {
				$relatedIds   = $howUseView[ $i ]->getRelatedViews();
				$defaultId    = $howUseView[ $i ]->getRelatedId();
				$types        = array_keys ($relatedIds);
				$relatedType  = $types[ 0 ];
				$relatedViews = null;
				switch ($relatedType) {
					case 'REPORT':
						$relatedViews = $this->updaterReports (array_values ($relatedIds)[0], $defaultId);
						break;
					case 'CALENDAR':
						$relatedViews = $this->updaterCalendar (array_values ($relatedIds)[0], $defaultId);
						break;
					case 'GRAPHIC_VIEW':
						$relatedViews = $this->updateGraphics (array_values ($relatedIds)[0], $defaultId);
						break;
					case 'LIST_VIEW':
						$relatedViews = $this->updateListView (array_values ($relatedIds)[0], $defaultId);
						break;
					case 'KANBAN_VIEW':
						$relatedViews = $this->updateKanban (array_values ($relatedIds)[0], $defaultId);
						break;
					case 'BOX_SCORE':
						$relatedViews = $this->updateBoxSore (array_values ($relatedIds)[0], $defaultId);
						break;
					default:
						continue;
				}
				$howUseView[ $i ]->setRelatedViews (json_encode (array ($relatedType => $relatedViews ['relatedViews'])));
				$howUseView[ $i ]->setRelatedId ($relatedViews['defaultId']);
			}
		}

		/**
		 * @param HowToUse $howToUse
		 */
		private function deletePartnersViews ($howToUse) {
			$this->adb->pquery (
				'DELETE FROM vtiger_default_listview WHERE howuseid=? AND tabname=?',
				array ($howToUse->getId(), $howToUse->getTabName())
			);
			$this->adb->pquery (
				'DELETE FROM vtiger_how_use_views WHERE howuseid=?',
				array ($howToUse->getId())
			);
		}

		/**
		 * @param $howUseName
		 *
		 * @return HowToUse|null
		 * @throws Exception
		 */
		private function fetchHowToUseByName ($howUseName) {
			if (empty($howUseName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_how_use WHERE howusename=?', array ($howUseName));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$howToUse = HowToUse::getInstance()
					->setId ($row['howuseid'])
					->setHowUseName ($row['howusename'])
					->setName ($row['name'])
					->setDescription($row['description'])
					->setTabName ($row['tabname'])
					->setDefault(($row['isdefault']) ? true : false)
					->setStatus($row['status']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($howToUse)) ? $howToUse : null;
		}

		/**
		 * @return HowUseView[] |null
		 * @throws Exception
		 */
		private function fetchHowToUseViews () {
			$result = $this->adb->query ('SELECT * FROM vtiger_how_use_views WHERE 1');
			if ($this->adb->num_rows ($result) > 0) {
				$howUseView = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$howUseView [] = HowUseView::getInstance()
						->setHowUseId ($row ['howuseid'])
						->setHowUseName ($row ['howusename'])
						->setId ($row ['howuseviewid'])
						->setMasterView($this->fetchMasterView ($row ['viewid']))
						->setName ($row ['useviewname'])
						->setRelatedId ($row ['relatedid'])
						->setRelatedViews ($row ['views']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($howUseView)) ? $howUseView : null;
		}

		/**
		 * @param array $boxScoreIds
		 * @param integer $defaultId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function updateBoxSore ($boxScoreIds, $defaultId) {
			if (empty($boxScoreIds)) {
				return null;
			}
			$boxscore      = array ();
			$newDefaultId = 0;
			foreach ($boxScoreIds as $id) {
				$resultA  = $this->masterAdb->pquery('SELECT name FROM vtiger_box_score_data WHERE box_score_dataid=?', array ($id));
				if ($this->masterAdb->num_rows ($resultA) > 0) {
					$row = $this->masterAdb->fetchByAssoc ($resultA, -1, false);
					DatabaseUtils::closeResult ($resultA);
					$resultB = $this->adb->pquery('SELECT box_score_dataid FROM vtiger_box_score_data WHERE name=?', array ($row ['name']));
					if ($this->adb->num_rows($resultB) > 0) {
						$row = $this->masterAdb->fetchByAssoc ($resultB, -1, false);
						$newDefaultId = ($defaultId == $id) ? $row['box_score_dataid'] : $newDefaultId;
						$boxscore []   = $row['box_score_dataid'];
					}
					DatabaseUtils::closeResult ($resultB);
				}
			}
			return array ('relatedViews' => $boxscore, 'defaultId' => $newDefaultId);
		}

		/**
		 * @param array $calendarIds
		 * @param integer $defaultId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function updaterCalendar ($calendarIds, $defaultId) {
			if (empty($calendarIds)) {
				return null;
			}
			$calendars      = array ();
			$newDefaultId = 0;
			foreach ($calendarIds as $id) {
				$resultA  = $this->masterAdb->pquery('SELECT label, modulename, fromfieldname, tofieldname  FROM vtiger_calendarviews WHERE calendarviewid=?', array($id));
				if ($this->masterAdb->num_rows ($resultA) > 0) {
					$row = $this->masterAdb->fetchByAssoc ($resultA, -1, false);
					DatabaseUtils::closeResult ($resultA);
					$resultB = $this->adb->pquery(
						'SELECT 
								calendarviewid 
							  FROM 
							  	vtiger_calendarviews 
							  WHERE 
							  	label=? AND 
							  	modulename=? AND 
							  	fromfieldname=? AND 
							  	tofieldname=?',
						array($row ['label'], $row ['modulename'],$row ['fromfieldname'], $row ['tofieldname'])
					);
					if ($this->adb->num_rows($resultB) > 0) {
						$row = $this->masterAdb->fetchByAssoc ($resultB, -1, false);
						$newDefaultId = ($defaultId == $id) ? $row['calendarviewid'] : $newDefaultId;
						$calendars []   = $row['calendarviewid'];
					}
					DatabaseUtils::closeResult ($resultB);
				}
			}
			return array ('relatedViews' => $calendars, 'defaultId' => $newDefaultId);
		}

		/**
		 * @param array $graphicIds
		 * @param integer $defaultId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function updateGraphics ($graphicIds, $defaultId) {
			if (empty($graphicIds)) {
				return null;
			}
			$reports      = array ();
			$newDefaultId = 0;
			foreach ($graphicIds as $id) {
				$resultA  = $this->masterAdb->pquery('SELECT name FROM vtiger_graficos WHERE graficoid=?', array ($id));
				if ($this->masterAdb->num_rows ($resultA) > 0) {
					$row = $this->masterAdb->fetchByAssoc ($resultA, -1, false);
					DatabaseUtils::closeResult ($resultA);
					$resultB = $this->adb->pquery('SELECT graficoid FROM vtiger_graficos WHERE name=?', array ($row ['name']));
					if ($this->adb->num_rows($resultB) > 0) {
						$row = $this->masterAdb->fetchByAssoc ($resultB, -1, false);
						$newDefaultId = ($defaultId == $id) ? $row['graficoid'] : $newDefaultId;
						$reports []   = $row['graficoid'];
					}
					DatabaseUtils::closeResult ($resultB);
				}
			}
			return array ('relatedViews' => $reports, 'defaultId' => $newDefaultId);
		}

		/**
		 * @param array $kanbaIds
		 * @param integer $defaultId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function updateKanban ($kanbaIds, $defaultId) {
			if (empty($kanbaIds)) {
				return null;
			}
			$reports      = array ();
			$newDefaultId = 0;
			foreach ($kanbaIds as $id) {
				$resultA  = $this->masterAdb->pquery('SELECT kanbaname FROM vtiger_kanbanviews WHERE kanbanviewid=?', array ($id));
				if ($this->masterAdb->num_rows ($resultA) > 0) {
					$row = $this->masterAdb->fetchByAssoc ($resultA, -1, false);
					DatabaseUtils::closeResult ($resultA);
					$resultB = $this->adb->pquery('SELECT kanbanviewid FROM vtiger_kanbanviews WHERE kanbaname=?', array ($row ['kanbaname']));
					if ($this->adb->num_rows($resultB) > 0) {
						$row = $this->masterAdb->fetchByAssoc ($resultB, -1, false);
						$newDefaultId = ($defaultId == $id) ? $row['kanbanviewid'] : $newDefaultId;
						$reports []   = $row['kanbanviewid'];
					}
					DatabaseUtils::closeResult ($resultB);
				}
			}
			return array ('relatedViews' => $reports, 'defaultId' => $newDefaultId);
		}

		/**
		 * @param array $customViewIds
		 * @param integer $defaultId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function updateListView ($customViewIds, $defaultId) {
			if (empty($customViewIds)) {
				return null;
			}
			$reports      = array ();
			$newDefaultId = 0;
			foreach ($customViewIds as $id) {
				$resultA  = $this->masterAdb->pquery('SELECT viewname, entitytype FROM vtiger_customview WHERE cvid=?', array ($id));
				if ($this->masterAdb->num_rows ($resultA) > 0) {
					$row = $this->masterAdb->fetchByAssoc ($resultA, -1, false);
					DatabaseUtils::closeResult ($resultA);
					$resultB = $this->adb->pquery('SELECT cvid FROM vtiger_customview WHERE viewname=? AND entitytype=?', array ($row ['viewname'], $row['entitytype']));
					if ($this->adb->num_rows($resultB) > 0) {
						$row = $this->masterAdb->fetchByAssoc ($resultB, -1, false);
						$newDefaultId = ($defaultId == $id) ? $row['cvid'] : $newDefaultId;
						$reports []   = $row['cvid'];
					}
					DatabaseUtils::closeResult ($resultB);
				}
			}
			return array ('relatedViews' => $reports, 'defaultId' => $newDefaultId);
		}

		/**
		 * @param array $reportIds
		 * @param integer $defaultId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function updaterReports ($reportIds, $defaultId) {
			if (empty($reportIds)) {
				return null;
			}
			$reports      = array ();
			$newDefaultId = 0;
			foreach ($reportIds as $id) {
				$resultA  = $this->masterAdb->pquery('SELECT reportname, applicationcodes FROM vtiger_report WHERE reportid=?', array($id));
				if ($this->masterAdb->num_rows ($resultA) > 0) {
					$row = $this->masterAdb->fetchByAssoc ($resultA, -1, false);
					DatabaseUtils::closeResult ($resultA);
					$resultB = $this->adb->pquery('SELECT reportid FROM vtiger_report WHERE reportname=? AND applicationcodes=?', array($row ['reportname'], $row ['applicationcodes']));
					if ($this->adb->num_rows($resultB) > 0) {
						$row = $this->masterAdb->fetchByAssoc ($resultB, -1, false);
						$newDefaultId = ($defaultId == $id) ? $row['reportid'] : $newDefaultId;
						$reports []   = $row['reportid'];
					}
					DatabaseUtils::closeResult ($resultB);
				}
			}
			return array ('relatedViews' => $reports, 'defaultId' => $newDefaultId);
		}

		/**
		 * @param HowToUse $howToUse
		 *
		 * @throws HowToUseException
		 */
		private function validate($howToUse) {
			$howToUse->validate();
			if (!empty ($howToUse->getDefaultView()) && ($howToUse->getDefaultView() instanceof DefaultView)) {
				$howToUse->getDefaultView()->validate();
			} else if (!empty ($howToUse->getHowUseView())) {
				foreach ($howToUse->getHowUseView() as $howUseView) {
					if (empty ($howToUse) || (!$howUseView instanceof HowUseView)) {
						continue;
					}
					$howUseView->validate();
				}
			}
		}

		/**
		 * @param string $moduleName
		 *
		 * @return DefaultView[]|null
		 * @throws Exception
		 */
		public function fetchDefaultView ($moduleName) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_default_listview WHERE tabname=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$defaultViews = array();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$defaultViews [] = DefaultView::getInstance()
						->setHowUseId($row ['howuseid'])
						->setHowUseName ($row ['howusename'])
						->setId ($row ['defaultid'])
						->setMasterView ($this->fetchMasterView($row ['viewid']))
						->setModuleName($row ['tabname'])
						->setUserId ($row ['userid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($defaultViews)) ? $defaultViews : null;
		}

		/**
		 * @param integer $howUseId
		 *
		 * @return DefaultView|null
		 * @throws Exception
		 */
		public function fetchDefaultViewByModeId ($howUseId) {
			if (empty($howUseId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_default_listview WHERE howuseid=? AND userid=?', array ($howUseId, 1));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$defaultView = DefaultView::getInstance()
					->setHowUseId($row ['howuseid'])
					->setHowUseName ($row ['howusename'])
					->setId ($row ['defaultid'])
					->setMasterView ($this->fetchMasterView($row ['viewid']))
					->setModuleName($row ['tabname'])
					->setUserId ($row ['userid']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($defaultView)) ? $defaultView : null;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $headersOnly
		 *
		 * @return HowToUse[]|null
		 * @throws Exception
		 */
		public function fetchHowToUse ($moduleName, $headersOnly = false) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_how_use WHERE tabname=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$howToUse = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$howToUse[] = HowToUse::getInstance()
						->setDefaultView ((!$headersOnly) ? $this->fetchDefaultViewByModeId ($row['howuseid']) : null)
						->setDescription ($row ['description'])
						->setDefault (($row ['isdefault']) ? true : false)
						->setHowUseName ($row ['howusename'])
						->setHowUseView ((!$headersOnly) ? $this->fetchHowUseViews ($row ['howuseid']) : null)
						->setId ($row ['howuseid'])
						->setName ($row ['name'])
						->setStatus ($row ['status'])
						->setTabName ($row ['tabname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($howToUse)) ? $howToUse : null;
		}

		/**
		 * @param integer $howUseId
		 *
		 * @return HowUseView[]|null
		 * @throws Exception
		 */
		public function fetchHowUseViews ($howUseId) {
			if (empty($howUseId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_how_use_views WHERE howuseid=?', array ($howUseId));
			if ($this->adb->num_rows ($result) > 0) {
				$howUseView = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$howUseView [] = HowUseView::getInstance()
						->setHowUseId ($row ['howuseid'])
						->setHowUseName ($row ['howusename'])
						->setId ($row ['howuseviewid'])
						->setMasterView($this->fetchMasterView ($row ['viewid']))
						->setName ($row ['useviewname'])
						->setRelatedId ($row ['relatedid'])
						->setRelatedViews ($row ['views']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($howUseView)) ? $howUseView : null;
		}

		/**
		 * @param integer $masterViewId
		 *
		 * @return MasterView|null
		 * @throws Exception
		 */
		public function fetchMasterView ($masterViewId) {
			if (empty($masterViewId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_master_view WHERE viewid=?', array ($masterViewId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$masterView = MasterView::getInstance()
					->setId ($row ['viewid'])
					->setName ($row ['name'])
					->setTabView ($row ['tabview'])
					->setViewName ($row ['viewname']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($masterView)) ? $masterView : null;
		}

		/**
		 * @param HowToUse $howToUse
		 */
		public function saveDefaultView ($howToUse) {
			if (empty($howToUse->getDefaultView()) || !$howToUse->getDefaultView() instanceof DefaultView) {
				return;
			}
			$defaultView = $howToUse->getDefaultView();
			$this->adb->pquery (
				'INSERT INTO vtiger_default_listview (howuseid, howusename, tabname, viewid, userid) VALUES (?, ?, ?, ?, ?)',
				array ($howToUse->getId(), $howToUse->getHowUseName(), $defaultView->getModuleName(), $defaultView->getMasterView()->getId(), 1)
			);
		}

		/**
		 * @param HowToUse $howToUse
		 */
		public function saveHowUseView ($howToUse) {
			if (empty($howToUse->getHowUseView())) {
				return;
			}

			foreach ($howToUse->getHowUseView () as $howUseView) {
				if (empty($howUseView) || (!$howUseView instanceof HowUseView)) {
					continue;
				}
				$views = (!empty($howUseView->getRelatedViews())) ? json_encode ($howUseView->getRelatedViews()) : null;
				$this->adb->pquery (
					'INSERT INTO vtiger_how_use_views (howuseid, howusename, useviewname, views, viewid, relatedid) VALUES (?, ?, ?, ?, ?, ?)',
					array ($howToUse->getId(),  $howToUse->getHowUseName(), $howUseView->getName (), $views, $howUseView->getMasterView()->getId(), $howUseView->getRelatedId())
				);
			}
		}

		/**
		 * @param HowToUse $howToUse
		 *
		 * @return null
		 * @throws HowToUseException
		 */
		public function saveHowToUse($howToUse) {
			if (empty ($howToUse) || (!$howToUse instanceof HowToUse)) {
				return null;
			}
			$this->validate ($howToUse);
			$isDefault = ($howToUse->isDefault()) ? 1 : 0;
			if ($howToUse->isDefault()) {
				$this->clearDefaults ($howToUse);
			}
			if (empty($howToUse->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_how_use (howusename, name, description, tabname, isdefault, status) VALUES (?, ?, ?, ?, ?, ?)',
					array ($howToUse->getHowUseName() ,$howToUse->getName (), $howToUse->getDescription (), $howToUse->getTabName (), $isDefault, $howToUse->getStatus ())
				);
				$howToUse->setId($this->adb->getLastInsertID());
			} else {
				$this->adb->pquery(
					'UPDATE vtiger_how_use SET howusename=?, name=?, description=?, tabname=?, isdefault=?, status=? WHERE howuseid=?',
					array ($howToUse->getHowUseName(), $howToUse->getName(), $howToUse->getDescription(), $howToUse->getTabName (), $isDefault, $howToUse->getStatus (), $howToUse->getId())
				);
			}
			$this->deletePartnersViews ($howToUse);
			$this->saveDefaultView ($howToUse);
			$this->saveHowUseView ($howToUse);
		}

		/**
		 * @param Module $module
		 *
		 * @throws Exception
		 */
		public function saveHowUseModes ($module) {
			if (empty($module->getHowToUse())) {
				return;
			}
			foreach ($module->getHowToUse() as $source) {
				if (empty($source)) {
					continue;
				}

				$target = $this->fetchHowToUseByName($source->getHowUseName());
				if (empty ($target)) {
					$source->setId (null);
				} else {
					$source->setId ($target->getId());
				}
				$this->saveHowToUse ($source);
			}
		}

		/**
		 * @param Module $module
		 *
		 * @throws Exception
		 */
		public function updateHowToUsesViewsIds ($module) {
			if ($module->getName () != 'graficosgenerales') {
				return;
			}
			$allHowViews = $this->fetchHowToUseViews ();
			if (empty ($allHowViews)) {
				return;
			}

			$this->codeUpdater ($allHowViews);
			foreach ($allHowViews as $howUseView) {
				$views = (!empty($howUseView->getRelatedViews())) ? json_encode ($howUseView->getRelatedViews()) : null;
				$this->adb->pquery(
					'UPDATE vtiger_how_use_views SET views=?, relatedid=? WHERE howuseviewid=?',
					array ( $views, $howUseView->getRelatedId (), $howUseView->getId ())
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return mixed|HowToUseManager
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
