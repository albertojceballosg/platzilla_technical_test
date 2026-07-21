<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Objects/EditableFieldsButton.php');
	require_once ('include/platzilla/Objects/EditableFieldsField.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class EditableFieldsManager {
		/** @var EditableFieldsManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $buttonName
		 * @param string $moduleName
		 * @param boolean $onlyHeader
		 *
		 * @return EditableFieldsField[]|null
		 */
		private function fetchEditableFields ($buttonName, $moduleName, $onlyHeader = true) {
			if (empty($buttonName)) {
				return null;
			}
			$fm = null;
			if (!$onlyHeader && !empty($moduleName)) {
				$fm = FieldManager::getInstance($this->adb);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_editablefields_fields WHERE buttonname=?', array ($buttonName));
			if ($this->adb->num_rows ($result) > 0) {
				$editableFields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$editableFields [] = EditableFieldsField::getInstance()
						->setId ($row ['editablefields_fieldid'])
						->setButtonName ($row['buttonname'])
						->setField((!empty($fm) ? $fm->fetchFieldByName($moduleName, $row ['fieldname']) : null))
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname']);
				}
			} else {
				$editableFields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $editableFields;
		}

		/**
		 * @param string $buttonName
		 *
		 * @return array|mixed|null
		 */
		private function fetchEditableFieldButtonData ($buttonName) {
			if (!empty ($buttonName)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_editablefields_buttons WHERE name=?', array ($buttonName));
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
		 * @param EditableFieldsField[] $buttonFields
		 *
		 * @return null
		 */
		private function saveEditableFields ($buttonFields) {
			if(empty($buttonFields)) {
				return null;
			}

			foreach ($buttonFields as $buttonField) {
				if(empty($buttonField) || !$buttonField instanceof EditableFieldsField) {
					continue;
				}
				$this->adb->pquery (
					'INSERT INTO vtiger_editablefields_fields (buttonname, fieldname, fieldlabel) VALUES (?, ?, ?)',
					array ($buttonField->getButtonName(), $buttonField->getFieldName(), $buttonField->getFieldLabel ())
				);
			}
		}

		/**
		 * @param EditableFieldsButton $button
		 *
		 * @throws EditableFieldsException
		 */
		private function validate ($button) {
			if ((empty ($button)) || (!($button instanceof EditableFieldsButton))) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_EMPTY_BUTTON);
			}

			$button->validate ();

			$moduleName = $button->getModuleName ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_INVALID_MODULE_NAME);
			}
		}

		/**
		 * @param string $buttonName
		 * @param boolean $ignoreLock
		 *
		 * @return null|string
		 */
		public function deleteEditableButton ($buttonName, $ignoreLock = true) {
			if (empty ($buttonName)) {
				return null;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND eb.locked=0';
			} else {
				$whereClause = '';
			}
			$this->adb->pquery(
				"DELETE 
					ef.* 
				  FROM 
				  	vtiger_editablefields_fields ef 
				  INNER JOIN vtiger_editablefields_buttons eb ON eb.name = ef.buttonname
				  WHERE 
				  	eb.name=?
				  	{$whereClause} ",
				array($buttonName)
			);
			$this->adb->pquery ("DELETE eb.* FROM vtiger_editablefields_buttons eb WHERE eb.name=? {$whereClause}", array ($buttonName));
			return $buttonName;
		}

		/**
		 * @param string|integer $button
		 * @param boolean $onlyHeader
		 *
		 * @return EditableFieldsButton|null
		 */
		public function fetchEditableButtom ($button, $onlyHeader = true) {
			if (empty ($button)) {
				return null;
			} else if (is_scalar ($button)) {
				$where = 'name=?';
			} else if (is_numeric($button)) {
				$where = 'editablefields_buttonid=?';
			}

			$result = $this->adb->pquery ("SELECT * FROM vtiger_editablefields_buttons WHERE {$where}", array ($button));
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$editableButton = EditableFieldsButton::getInstance ()
					->setId ($row ['editablefields_buttonid'])
					->setEditableFields ($this->fetchEditableFields ($row ['name'], $row ['modulename'], $onlyHeader))
					->setDescription ($row ['description'])
					->setInstances ($row ['instances'])
					->setLabel($row ['label'])
					->setLocked(($row ['locked']) ? true : false)
					->setModuleName($row ['modulename'])
					->setName($row ['name'])
					->setStatus(($row ['status']) ? true : false);
			} else {
				$editableButton = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $editableButton;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $onlyHeader
		 *
		 * @return EditableFieldsButton[]|null
		 */
		public function fetchEditableButtonsByModule ($moduleName, $onlyHeader = true) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_editablefields_buttons WHERE modulename=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$editableButtons = array();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$editableButtons [] = EditableFieldsButton::getInstance()
						->setId($row ['editablefields_buttonid'])
						->setEditableFields($this->fetchEditableFields($row ['name'], $row ['modulename'], $onlyHeader))
						->setDescription($row ['description'])
						->setInstances($row ['instances'])
						->setLabel($row ['label'])
						->setLocked(($row ['locked']) ? true : false)
						->setModuleName($row ['modulename'])
						->setName($row ['name'])
						->setStatus(($row ['status']) ? true : false);
				}
			} else {
				$editableButtons = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $editableButtons;
		}

		/**
		 * @param EditableFieldsButton $button
		 * @param boolean $ignoreLock
		 *
		 * @return mixed
		 * @throws EditableFieldsException
		 */
		public function saveEditableFieldButton ($button, $ignoreLock = true) {
			$this->validate ($button);

			$isStatus = $button->isStatus ();
			if (!$isStatus && !$ignoreLock) {
				return $button;
			}

			$buttonName = $button->getName ();
			$data       = $this->fetchEditableFieldButtonData ($buttonName);
			if (!empty ($data)) {
				$isLocked  = $data ['locked'];
				$buttonId  = $data ['editablefields_buttonid'];
				$instances = $data ['instances'];
			} else {
				$isLocked  = ($button->isLocked ()) ? 1 : 0;
				$buttonId  = null;
				$instances = (empty($button->getInstances ())) ? json_encode(array('ALL-INSTANCES')) : $button->getInstances();
			}

			if (empty ($buttonId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_editablefields_buttons (`name`, `label`, `description`, `modulename`,`instances`) VALUES (?, ?, ?, ?, ?)',
					array ($button->getName (), $button->getLabel (), $button->getDescription (), $button->getModuleName (),  $instances),
					true
				);
				$button->setId ($this->adb->getLastInsertID ());
				$this->saveEditableFields ($button->getEditableFields ());
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_editablefields_buttons SET label=?, description=?, modulename=?, status=?, instances=?, locked=? WHERE name=?',
					array ($button->getLabel (), $button->getDescription (), $button->getModuleName (), $button->isStatus (), $instances, $isLocked, $button->getName ())
				);
				$this->adb->pquery('DELETE FROM vtiger_editablefields_fields WHERE buttonname=?', array($button->getName ()));
				$this->saveEditableFields ($button->getEditableFields ());
			}

			return $button;
		}

		/**
		 * @param Module $module
		 * @param boolean $ignoreLock
		 *
		 * @return null
		 * @throws Exception
		 */
		public function saveEditableFieldsButtons ($module, $ignoreLock = true) {
			if(empty($module) || !$module instanceof Module) {
				return null;
			} else if (empty ($module->getEditableFieldsButtons ())) {
				return null;
			}
			foreach ($module->getEditableFieldsButtons () as $button) {
				if(empty($button) || !$button instanceof EditableFieldsButton) {
					continue;
				}
				$this->saveEditableFieldButton ($button, $ignoreLock);
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return EditableFieldsManager
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
