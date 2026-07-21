<?php
	require_once ('include/platzilla/Objects/Button.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ButtonManager {
		/** @var ButtonManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param integer $buttonId
		 *
		 * @return array|null
		 */
		private function fetchButtonData ($buttonId) {
			if (!empty ($buttonId)) {
				$result = $this->adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.custombuttonid=?', array ($buttonId));
				if (($result) && ($this->adb->num_rows ($result) > 0)) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
				} else {
					$row = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$row = null;
			}
			return $row;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Button[]
		 */
		private function fetchDeletedButtons ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$buttons = array ();
			$result  = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('button', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var Button $button */
					$button = unserialize ($row ['serializedobject']);
					$button->setDeleted (true);
					$buttons [] = $button;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $buttons;
		}

		/**
		 * @param Button $button
		 *
		 * @throws ButtonException
		 */
		private function validate ($button) {
			if ((empty ($button)) || (!($button instanceof Button))) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY);
			}

			$button->validate ();

			$moduleName = $button->getModuleName ();
			if (empty ($moduleName)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_INVALID_MODULE_NAME);
			}
		}

		/**
		 * @param Button $button
		 */
		public function deleteButton ($button) {
			if ((empty ($button)) || (!($button instanceof Button))) {
				return;
			}

			$buttonId = $button->getId ();
			if (empty ($buttonId)) {
				return;
			}
			$moduleName = $button->getModuleName ();
			$identifier = $buttonId;
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('block', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('button', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($button)));
			}
			$this->adb->pquery ('DELETE FROM vtiger_custombuttons WHERE custombuttonid=?', array ($buttonId));
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteButtons ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->pquery ("DELETE FROM vtiger_custombuttons WHERE module=? {$whereClause}", array ($moduleName));
		}

		/**
		 * @param integer $buttonId
		 *
		 * @return Button|null
		 */
		public function fetchButton ($buttonId) {
			if (empty ($buttonId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.custombuttonid=?', array ($buttonId));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$row  = $this->adb->fetchByAssoc ($result, -1, false);
			$type = $row ['type'];
			return Button::getInstance ()
				->setAction ($type == ButtonInterface::TYPE_LINK ? $row ['link'] : $row ['onclick'])
				->setArrayVisibility (json_decode ($row ['arrayvisibility']))
				->setDescription ($row ['description'])
				->setId (intval ($buttonId))
				->setIsActive ($row ['active'] == 1 ? true : false)
				->setLabel ($row ['label'])
				->setLocation ($row ['action'])
				->setLocked ($row ['locked'] == 1)
				->setModuleName ($row ['module'])
				->setRunInNewWindow ($row ['runinnewwindow'] == 1 ? true : false)
				->setSqlVisibility (json_decode ($row ['sqlvisibility'], true))
				->setStyle ($row ['style'])
				->setType ($type)
				->setFaIcon (isset($row['faicon']) ? $row['faicon'] : null);

		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return Button[]|null
		 */
		public function fetchButtons ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=?', array ($moduleName));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$buttons = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$type       = $row ['type'];
				$buttons [] = Button::getInstance ()
					->setAction ($type == ButtonInterface::TYPE_LINK ? $row ['link'] : $row ['onclick'])
					->setArrayVisibility (json_decode ($row ['arrayvisibility'], true, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)))
					->setDescription ($row ['description'])
					->setId (intval ($row ['custombuttonid']))
					->setIsActive ($row ['active'] == 1 ? true : false)
					->setLabel ($row ['label'])
					->setLocation ($row ['action'])
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setRunInNewWindow ($row ['runinnewwindow'] == 1 ? true : false)
					->setSqlVisibility (json_decode ($row ['sqlvisibility']))
					->setStyle ($row ['style'])
					->setType ($type)
					->setFaIcon (isset($row['faicon']) ? $row['faicon'] : null);

			}

			if ($includeDeleted) {
				$deletedButtons = $this->fetchDeletedButtons ($moduleName);
			} else {
				$deletedButtons = array ();
			}

			return array_merge ($buttons, $deletedButtons);
		}

		/**
		 * @param Button $button
		 * @param boolean $ignoreLock
		 *
		 * @return Button
		 * @throws ButtonException
		 */
		public function saveButton ($button, $ignoreLock = true) {
			$this->validate ($button);

			$isDeleted = $button->isDeleted ();
			if ($isDeleted) {
				return $button;
			}

			$buttonId = $button->getId ();
			$data     = $this->fetchButtonData ($buttonId);
			if (!empty ($data)) {
				$isLocked = ($data ['locked'] == 1);
			} else {
				$isLocked = false;
				$buttonId = null;
			}

			$isActive       = $button->getIsActive () ? 1 : 0;
			$runInNewWindow = $button->getRunInNewWindow () ? 1 : 0;
			$type           = $button->getType ();
			if ($type == ButtonInterface::TYPE_JAVASCRIPT) {
				$onClick = $button->getAction ();
				$link    = null;
			} else {
				$onClick = null;
				$link    = $button->getAction ();
			}

			if (empty ($buttonId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_custombuttons (module, action, style, label, onclick, link, type, description, active, runinnewwindow, sqlvisibility, arrayvisibility, locked, faicon) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',	
					array ($button->getModuleName (), $button->getLocation (), $button->getStyle (), $button->getLabel (), $onClick, $link, $type, $button->getDescription (), $isActive, $runInNewWindow, $button->getSqlVisibility(), $button->getArrayVisibility (), $button->isLocked (), $button->getFaIcon ())
				);
				$button->setId ($this->adb->getLastInsertID ());
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_custombuttons SET module=?, action=?, style=?, label=?, onclick=?, link=?, type=?, description=?, active=?, runinnewwindow=?, sqlvisibility=?, arrayvisibility=?, locked=?, faicon=? WHERE custombuttonid=?',
					array ($button->getModuleName (), $button->getLocation (), $button->getStyle (), $button->getLabel (), $onClick, $link, $type, $button->getDescription (), $isActive, $runInNewWindow, $button->getSqlVisibility(), $button->getArrayVisibility (), $button->isLocked (), $button->getFaIcon (), $buttonId)
				);
			}

			return $button;
		}

		/**
		 * @param string $moduleName
		 * @param Button[]|null $buttons
		 * @param boolean $ignoreLock
		 */
		public function saveButtons ($moduleName, $buttons, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($buttons)) {
				$this->deleteButtons ($moduleName, $ignoreLock);
				return;
			}

			$processedButtonIds = array ();
			foreach ($buttons as $button) {
				$button->setModuleName ($moduleName);
				$this->saveButton ($button, $ignoreLock);
				$processedButtonIds [] = $button->getId ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedButtonIds) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_custombuttons WHERE module=? AND custombuttonid NOT IN ({$questionMarks}) {$whereClause}", array_merge (array ($moduleName), $processedButtonIds));
		}

		/**
		 * @param Button $button
		 *
		 * @throws ButtonException
		 */
		public function validateButton ($button) {
			$this->validate ($button);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ButtonManager
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
