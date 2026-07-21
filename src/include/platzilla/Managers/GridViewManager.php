<?php
	require_once ('include/platzilla/Objects/BoxContent.php');
	require_once ('include/platzilla/Objects/GridView.php');
	require_once ('include/platzilla/Objects/GridViewBox.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');

	class GridViewManager {

		/** @var GridViewManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param GridViewBox[] $gridViewBoxes
		 */
		private function saveGridViewBox ($gridViewBoxes) {
			if (empty ($gridViewBoxes) || !count ($gridViewBoxes)) {
				return;
			}
			foreach ($gridViewBoxes as $gridViewBox) {
				if (! $gridViewBox instanceof GridViewBox) {
					continue;
				}
				$this->adb->pquery (
					'INSERT INTO vtiger_grid_view_box (gridviewname, presence, boxtype, sequence) VALUES (?, ?, ?, ?)',
					array ($gridViewBox->getGridViewName (), $gridViewBox->getPresence (), $gridViewBox->getBoxType (), $gridViewBox->getSequence())
				);
			}
		}

		/**
		 * @param GridView $gridView
		 *
		 * @throws GridViewException
		 */
		private function validate ($gridView) {
			foreach ($gridView->getGridViewBox () as $gridViewBox) {
				$gridViewBox->validate();
			}
		}

		/**
		 * @param GridView $gridView
		 *
		 * @return null|GridView
		 */
		public function deleteGridView ($gridView) {
			if (empty($gridView) || !$gridView instanceof GridView) {
				return null;
			}

			$this->adb->pquery ('DELETE FROM vtiger_grid_view_box WHERE gridviewname=?',array ($gridView->getGridViewName ()));
			$this->adb->pquery ('DELETE FROM vtiger_grid_view WHERE gridviewname=?',array ($gridView->getGridViewName ()));
			return $gridView;
		}

		/**
		 * @return BoxContent[]|null
		 * @throws Exception
		 */
		public function fetchAvailableBoxContent () {
			if ($this->adb->dbName != 'pg_crm_audicleancap' && $this->adb->dbName != 'pg_crm_madre') {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$result = $masterAdb->query ('SELECT * FROM vtiger_box_content WHERE 1');
				unset ($masterAdb);
			} else {
				$result = $this->adb->query ('SELECT * FROM vtiger_box_content WHERE 1');
			}

			if ($this->adb->num_rows ($result) > 0) {
				$boxContent = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$boxContent [] = BoxContent::getInstance()
						->setAction($row ['action'])
						->setAttributes($row ['attributes'])
						->setId($row ['boxcontentid'])
						->setLabel($row ['label'])
						->setName($row ['name'])
						->setScript($row ['script']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($boxContent)) ? $boxContent : null;
		}

		/**
		 * @param string $boxName
		 *
		 * @return BoxContent|null
		 * @throws Exception
		 */
		public function fetchBoxContent ($boxName) {
			if (empty ($boxName)) {
				return null;
			}
			if ($this->adb->dbName != 'pg_crm_audicleancap' && $this->adb->dbName != 'pg_crm_madre') {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$result = $masterAdb->pquery ('SELECT * FROM vtiger_box_content WHERE name=?', array ($boxName));
				unset ($masterAdb);
			} else {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_box_content WHERE name=?', array ($boxName));
			}

			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$boxContent = BoxContent::getInstance()
					->setAction ($row ['action'])
					->setAttributes ($row ['attributes'])
					->setId ($row ['boxcontentid'])
					->setLabel ($row ['label'])
					->setName ($row ['name'])
					->setScript ($row ['script']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($boxContent)) ? $boxContent : null;
		}

		/**
		 * @param string $gridViewName
		 * @param boolean $headerOnly
		 *
		 * @return GridViewBox[]|null
		 * @throws Exception
		 */
		public function fetchGridViewBox ($gridViewName, $headerOnly = false) {
			if (empty ($gridViewName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_grid_view_box WHERE presence=? AND gridviewname=?', array (1, $gridViewName));
			if ($this->adb->num_rows ($result) > 0) {
				$gridViewBoxes = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$gridViewBoxes [] = GridViewBox::getInstance()
						->setBoxContenet ((!$headerOnly) ? $this->fetchBoxContent ($row ['boxtype']) : null)
						->setBoxType ($row ['boxtype'])
						->setGridViewName ($row ['gridviewname'])
						->setId ($row ['boxid'])
						->setPresence ($row ['presence'])
						->setSequence ($row ['sequence']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($gridViewBoxes)) ? $gridViewBoxes : null;
		}

		/**
		 * @param string|integer $gridViewCod
		 * @param boolean $headerOnly
		 *
		 * @return GridView|null
		 * @throws Exception
		 */
		public function fetchGridView ($gridViewCod, $headerOnly = false) {
			if (empty ($gridViewCod)) {
				return null;
			} else if (is_numeric ($gridViewCod)) {
				$where = "gv.gridviewid= {$gridViewCod}";
			} else {
				$where = "gv.gridviewname= '{$gridViewCod}'";
			}

			$result = $this->adb->query ("SELECT gv.*, t.tablabel FROM vtiger_grid_view gv INNER JOIN vtiger_tab t ON t.name = gv.tabname WHERE {$where}");
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$gridView = GridView::getInstance()
					->setCreateDate ($row ['createdate'])
					->setGridViewBox ((!$headerOnly) ? $this->fetchGridViewBox ($row ['gridviewname']) : null)
					->setGridViewName ($row ['gridviewname'])
					->setId ($row ['gridviewid'])
					->setLabel ($row ['gridviewlabel'])
					->setLocked ($row ['locked'])
					->setModuleName ($row ['tablabel'])
					->setPosition ($row ['position'])
					->setStatus ($row ['gridviewstatus'])
					->setTabName($row ['tabname']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($gridView)) ? $gridView : null;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $headerOnly
		 *
		 * @return GridView[]|null
		 * @throws Exception
		 */
		public function fetchGridViews ($moduleName, $headerOnly = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
					gv.*,
					t.tablabel
				FROM 
					vtiger_grid_view gv 
				INNER JOIN vtiger_tab t ON t.name = gv.tabname
				WHERE  
					t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$gridViews = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$gridViews [] = GridView::getInstance()
						->setCreateDate ($row ['createdate'])
						->setGridViewBox ((!$headerOnly) ? $this->fetchGridViewBox ($row ['gridviewname'], true) : null)
						->setGridViewName ($row ['gridviewname'])
						->setId ($row ['gridviewid'])
						->setLabel ($row ['gridviewlabel'])
						->setLocked ($row ['locked'])
						->setModuleName ($row ['tablabel'])
						->setPosition ($row ['position'])
						->setStatus ($row ['gridviewstatus'])
						->setTabName($row ['tabname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($gridViews)) ? $gridViews : null;
		}

		/**
		 * @param boolean $headerOnly
		 *
		 * @return GridView[]|null
		 * @throws Exception
		 */
		public function fetchGridViewAll ($headerOnly = false) {
			$result = $this->adb->pquery (
				'SELECT 
					gv.*,
					t.tablabel
				FROM 
					vtiger_grid_view gv 
				INNER JOIN vtiger_tab t ON t.name = gv.tabname
				WHERE 
					t.presence IN(?, ?)',
				array (0, 2)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$gridViews = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$gridViews [] = GridView::getInstance()
						->setCreateDate ($row ['createdate'])
						->setGridViewBox ((!$headerOnly) ? $this->fetchGridViewBox ($row ['gridviewname']) : null)
						->setGridViewName ($row ['gridviewname'])
						->setId ($row ['gridviewid'])
						->setLabel ($row ['gridviewlabel'])
						->setLocked ($row ['locked'])
						->setModuleName ($row ['tablabel'])
						->setPosition ($row ['position'])
						->setStatus ($row ['gridviewstatus'])
						->setTabName($row ['tabname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($gridViews)) ? $gridViews : null;
		}

		/**
		 * @param string $tabName
		 * @param boolean $headerOnly
		 *
		 * @return GridView|null
		 * @throws Exception
		 */
		public function fetchGridViewByModule ($tabName, $headerOnly = false) {
			if (empty ($tabName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
					gv.*,
					t.tablabel
				FROM 
					vtiger_grid_view gv 
				INNER JOIN vtiger_tab t ON t.name = gv.tabname
				WHERE 
					gv.gridviewstatus=? AND
					t.presence IN(?, ?) AND 
					t.name=?',
				array ('ENABLED', 0, 2, $tabName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$gridViews = GridView::getInstance()
						->setCreateDate ($row ['createdate'])
						->setGridViewBox ((!$headerOnly) ? $this->fetchGridViewBox ($row ['gridviewname']) : null)
						->setGridViewName ($row ['gridviewname'])
						->setId ($row ['gridviewid'])
						->setLabel ($row ['gridviewlabel'])
						->setLocked ($row ['locked'])
						->setModuleName ($row ['tablabel'])
						->setPosition ($row ['position'])
						->setStatus ($row ['gridviewstatus'])
						->setTabName($row ['tabname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($gridViews)) ? $gridViews : null;
		}

		/**
		 * @param string $tabName
		 * @param boolean $headerOnly
		 *
		 * @return GridView|null
		 * @throws Exception
		 */
		public function getDefaultGridView ($tabName, $headerOnly = false) {
			if (empty ($tabName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
					gv.*,
					t.tablabel
				FROM 
					vtiger_grid_view gv 
				CROSS JOIN vtiger_tab t
				WHERE 
					gv.gridviewname=? AND
					t.presence IN(?, ?) AND 
					t.name=?',
				array ('DEFAULT_VIEW', 0, 2, $tabName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$gridViews = GridView::getInstance()
						->setCreateDate ($row ['createdate'])
						->setGridViewBox ((!$headerOnly) ? $this->fetchGridViewBox ($row ['gridviewname']) : null)
						->setGridViewName ($row ['gridviewname'])
						->setId ($row ['gridviewid'])
						->setLabel ($row ['gridviewlabel'])
						->setLocked ($row ['locked'])
						->setModuleName ($row ['tablabel'])
						->setPosition ($row ['position'])
						->setStatus ($tabName)
						->setTabName($row ['tabname']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($gridViews)) ? $gridViews : null;
		}

		/**
		 * @param BoxContent  $boxContent
		 */
		public function saveBoxContent ($boxContent) {
			if (! $boxContent instanceof BoxContent) {
				return;
			}
			if ($this->adb->dbName != 'pg_crm_audicleancap' && $this->adb->dbName != 'pg_crm_madre') {
				$targetDb = AdbManager::getInstance ()->getMasterAdb ();
			} else {
				$targetDb = $this->adb;
			}

			if (empty ($boxContent->getId ())) {
				$targetDb->pquery (
					'INSERT INTO vtiger_box_content (name, label, action, attributes, script) VALUES (?, ?, ?, ?, ?)',
					array ($boxContent->getName (), $boxContent->getLabel (), $boxContent->getAction (), $boxContent->getAttributes (), $boxContent->getScript ())
				);
			} else {
				$targetDb->pquery (
					'UPDATE vtiger_box_content SET name=?, label=?, action=?, attribute=?, script=? WHERE boxcontentid=?',
					array ($boxContent->getName (), $boxContent->getLabel (), $boxContent->getAction (), $boxContent->getAttributes (), $boxContent->getScript (), $boxContent->getId ())
				);
			}
			unset ($targetDb);
		}

		/**
		 * @param GridView $gridView
		 * @param boolean $ignoreLock
		 *
		 * @return GridView
		 * @throws GridViewException
		 * @throws Exception
		 */
		public function saveGridView ($gridView, $ignoreLock = true) {
			if (!$gridView instanceof GridView) {
				return null;
			}
			$gridView->validate ();
			$this->validate ($gridView);
			if (empty ($gridView->getId ())) {
				$thisGridView = $this->fetchGridView ($gridView->getGridViewName (),true);
				if (!empty ($thisGridView)) {
					$gridView->setId ($thisGridView->getId());
				}
			}

			if (empty ($gridView->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_grid_view (gridviewname, gridviewlabel, tabname, gridviewstatus, position, locked) VALUES (?, ?, ?, ?, ?, ?)',
					array ($gridView->getGridViewName (), $gridView->getLabel(), $gridView->getTabName (), $gridView->getStatus (), $gridView->getPosition(), $gridView->getLocked ())
				);
				$this->saveGridViewBox ($gridView->getGridViewBox ());
			} else if($ignoreLock) {
				$this->adb->pquery (
					'UPDATE vtiger_grid_view SET gridviewname=?, gridviewlabel=?, tabname=?, gridviewstatus=?, position=?, locked=? WHERE gridviewid=?',
					array ($gridView->getGridViewName (), $gridView->getLabel(), $gridView->getTabName (), $gridView->getStatus (), $gridView->getPosition(), $gridView->getLocked (), $gridView->getId ())
				);
				$this->adb->pquery ('DELETE FROM vtiger_grid_view_box WHERE gridviewname=?',array ($gridView->getGridViewName ()));
				$this->saveGridViewBox ($gridView->getGridViewBox ());
			}
			return $gridView;
		}

		/**
		 * @param Module $module
		 * @param boolean $ignoreLock
		 *
		 * @return null
		 * @throws Exception
		 */
		public function saveGridViews ($module, $ignoreLock = true) {
			if(empty ($module) || !$module instanceof Module) {
				return null;
			} else if (empty ($module->getGridViewes ())) {
				return null;
			}
			foreach ($module->getGridViewes() as $gridView) {
				if(empty($gridView) || !$gridView instanceof GridView) {
					continue;
				}
				$gridView->setId (null);
				$this->saveGridView ($gridView, $ignoreLock);
			}
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return GridViewManager|mixed
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
