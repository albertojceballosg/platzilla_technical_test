<?php
	require_once ('include/platzilla/Exceptions/GridException.php');
	require_once ('include/platzilla/Managers/FieldModuleReferenceManager.php');
	require_once ('include/platzilla/Objects/Grid.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Objects/GridFieldValues.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class GridManager {
		/** @var GridManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param GridField $gridField
		 * @param $fieldId
		 * @param $uiType
		 *
		 * @throws Exception
		 */
		private function changeNameToActionField ($gridField, $fieldId, $uiType) {
			if (empty ($gridField->getActionField ())) {
				return;
			}

			$actionField = $gridField->getActionField ();
			if ($uiType == GridFieldInterface::UI_TYPE_CALCULATED) {
				$array_actionField = unserialize (base64_decode ($actionField));
				$array_fieldName   = $array_actionField ['subFieldName'];
				$totalFieldNames   = count ($array_fieldName);
				for ($k = 0; $k < $totalFieldNames; $k++) {
					$dummy = explode ('_', $array_fieldName[ $k ]);
					if (count ($dummy) > 1) {
						array_pop ($dummy);
						$array_fieldName[ $k ] = join ('_', $dummy) . '_' . $fieldId;
					} else {
						$array_fieldName[ $k ] = $array_fieldName[ $k ] . '_' . $fieldId;
					}
				}
				$array_actionField ['subFieldName'] = $array_fieldName;
				$gridField->setActionField (base64_encode (serialize ($array_actionField)));
				unset($array_actionField);
			} else if (($uiType == GridFieldInterface::UI_TYPE_MODULE_REFERENCE)) {
				$array_actionField = unserialize (base64_decode ($actionField));
				$action_keys       = array_keys ($array_actionField);
				$totalNames        = count ($action_keys);
				for ($k = 0; $k < $totalNames; $k++) {
					$dummy = explode ('_', $action_keys[ $k ]);
					if (count ($dummy) > 1) {
						array_pop ($dummy);
						$action_keys[ $k ] = join ('_', $dummy) . '_' . $fieldId;
					} else {
						$action_keys[ $k ] = $action_keys[ $k ] . '_' . $fieldId;
					}
				}

				$actionValues = array_values ($array_actionField);
				$totalValues  = count ($actionValues);
				for ($k = 0; $k < $totalValues; $k++) {
					$rowIds = $this->getIdSummaryRows ($actionValues[ $k ]);
					if (!count ($rowIds)) {
						continue;
					}
					$actionValues[ $k ] = str_replace ($rowIds [0], $rowIds [1], $actionValues[ $k ]);
					unset ($rowIds);
				}
				$array_actionField = array_combine ($action_keys, $actionValues);
				$gridField->setActionField (base64_encode (serialize ($array_actionField)));
				unset ($array_actionField);
				unset ($action_keys);
				unset ($actionValues);
			}
		}

		/**
		 * @param GridField $gridField
		 * @param $fieldId
		 * @param $uiType
		 */
		private function changeNameToDataField ($gridField, $fieldId, $uiType) {
			if (empty ($gridField->getDataField ())) {
				return;
			}
			$dataField = $gridField->getDataField ();

			if ($uiType == GridFieldInterface::UI_TYPE_CALCULATED) {
				$pos       = strrpos ($dataField, '_');
				$posTwo    = strrpos ($dataField, '[');
				$idLenght  = (($posTwo - 1) - $pos);
				$theId     = substr ($dataField, ($pos + 1), $idLenght);
				$calculate = str_replace ($theId, $fieldId, $dataField);
				$gridField->setDataField ($calculate);
			} else if ($uiType == GridFieldInterface::UI_TYPE_GRID) {
				//TODO: Definir como trataremos las columnas importadas ya que ellas se importa desde subfield_values pero asociado a un registro del modulo
				$gridField->setDataField (null);
			} else if ($uiType == GridFieldInterface::UI_TYPE_SUMMARY) {
				$array_dataField = unserialize (base64_decode ($dataField));
				$totalDataField  = count ($array_dataField);
				for ($k = 0; $k < $totalDataField; $k++) {
					if (($array_dataField[ $k ]['field'] != 'false') || (!$array_dataField[ $k ]['field'])) {
						$dummy = explode ('_', $array_dataField[ $k ]['field']);
						if (count ($dummy) > 1) {
							array_pop ($dummy);
							$array_dataField[ $k ]['field'] = join ('_', $dummy) . '_' . $fieldId;
						} else {
							$array_dataField[ $k ]['field'] = $array_dataField[ $k ]['field'] . '_' . $fieldId;
						}
					}
				}
				$gridField->setDataField (base64_encode (serialize ($array_dataField)));
			}
		}

		/**
		 * @param GridField $gridField
		 * @param $fieldId
		 */
		private function changeNameToFilterField ($gridField, $fieldId) {
			if (empty ($gridField->getFilterField ())) {
				return;
			}

			$filterField  = $gridField->getFilterField ();
			$array_filter = unserialize (base64_decode ($filterField));
			$totalFilter  = count ($array_filter);
			for ($k = 0; $k < $totalFilter; $k++) {
				$dummy = explode ('_', $array_filter[ $k ]['field']);
				if (count ($dummy) > 1) {
					array_pop ($dummy);
					$array_filter[ $k ]['field'] = join ('_', $dummy) . '_' . $fieldId;
				} else {
					$array_filter[ $k ]['field'] = $array_filter[ $k ]['field'] . '_' . $fieldId;
				}
			}
			$gridField->setFilterField (base64_encode (serialize ($array_filter)));
		}

		/**
		 * @param integer $fieldId
		 *
		 * @return GridField[]|null
		 */
		private function fetchGridFields ($fieldId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_subfields_special WHERE fieldid=? ORDER BY sequence', array ($fieldId));
			if ($this->adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy = explode ('_', $row ['name']);
					if (count ($dummy) > 1) {
						array_pop ($dummy);
						$fieldName = join ('_', $dummy);
					} else {
						$fieldName = $row ['name'];
					}
					$fields [] = GridField::getInstance ()
						->setActionField ($row ['action_field'])
						->setDataField ($row ['data_field'])
						->setDefaultValue ($row ['defaultvalue'])
						->setFilterField ($row ['filter_field'])
						->setLabel ($row ['label'])
						->setLength ($row ['length'])
						->setModuleReferences ($row ['relmodule'])
						->setName ($fieldName)
						->setPrecision ($row ['precision'])
						->setSequence (intval ($row ['sequence']))
						->setSubFieldId ($row ['subfieldsid'])
						->setUiType ($row ['uitype'])
						->setValues ($row ['values']);
				}
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $fields;
		}

		/**
		 * @param string $moduleAdnField
		 *
		 * @return array
		 * @throws Exception
		 */
		private function getIdSummaryRows ($moduleAdnField) {
			$dummy     = explode('@', $moduleAdnField);
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$resultMaster = $masterAdb->pquery(
				'SELECT f.fieldid, f.fieldname FROM vtiger_field f INNER JOIN vtiger_subfields_special sf ON sf.fieldid = f.fieldid WHERE sf.name=?',
				array ($dummy [1])
			);
			if ($masterAdb->num_rows ($resultMaster) > 0) {
				$row             = $masterAdb->fetchByAssoc ($resultMaster, -1, false);
				$masterFieldId   = $row ['fieldid'];
				$fieldName       = $row ['fieldname'];
				DatabaseUtils::closeResult ($resultMaster);
				$resultMaster = null;
				$result = $this->adb->pquery(
					'SELECT fieldid FROM vtiger_field WHERE fieldname=?',
					array ($fieldName)
				);
				if ($this->adb->num_rows ($result) > 0) {
					$row  = $this->adb->fetchByAssoc ($result, -1, false);
					DatabaseUtils::closeResult ($result);
					$result = null;
					return array ($masterFieldId, $row ['fieldid']);
				}
			}
			DatabaseUtils::closeResult ($resultMaster);
			$resultMaster = null;
			return array ();
		}

		/**
		 * @param Grid $grid
		 */
		public function deleteGrid ($grid) {
			if ((empty ($grid)) || (!($grid instanceof Grid))) {
				return;
			}

			$moduleName = $grid->getModuleName ();
			$gridName   = $grid->getName ();
			if ((empty ($moduleName)) || (empty ($gridName))) {
				return;
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=? AND f.uitype=?',
				array ($moduleName, $gridName, FieldInterface::UI_TYPE_GRID)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId = intval ($row ['fieldid']);

				$this->adb->startTransaction ();
				$this->adb->pquery ('DELETE FROM vtiger_subfields_values WHERE subfieldsid IN (SELECT subfieldsid FROM vtiger_subfields_special WHERE fieldid=?)', array ($fieldId));
				$this->adb->pquery ('DELETE FROM vtiger_subfields_special WHERE fieldid=?', array ($fieldId));
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param string $moduleName
		 * @param string $gridName
		 *
		 * @return Grid|null
		 */
		public function fetchGridByName ($moduleName, $gridName) {
			if ((empty ($moduleName)) || (empty ($gridName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=? AND f.uitype=?',
				array ($moduleName, $gridName, FieldInterface::UI_TYPE_GRID)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId = intval ($row ['fieldid']);
				$grid    = Grid::getInstance ()
					->setModuleName ($moduleName)
					->setName ($gridName)
					->setFields ($this->fetchGridFields ($fieldId));
			} else {
				$grid = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $grid;
		}

		/**
		 * @param Grid $grid
		 *
		 * @return Grid
		 * @throws GridException
		 */
		public function saveGrid ($grid) {
			if ((empty ($grid)) || (!($grid instanceof Grid))) {
				return $grid;
			}

			$moduleName = $grid->getModuleName ();
			$fieldName  = $grid->getName ();
			$result     = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=? AND f.uitype=?',
				array ($moduleName, $fieldName, FieldInterface::UI_TYPE_GRID)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId = $row ['fieldid'];

				$this->adb->startTransaction ();
				$sequence                = 1;
				$processedGridFieldNames = array ();
				$gridFields              = $grid->getFields ();
				foreach ($gridFields as $gridField) {
					$this->changeNamesToSpecialField ($gridField, $fieldId);
					$result = $this->adb->pquery ('SELECT * FROM vtiger_subfields_special WHERE fieldid=? AND name=?', array ($fieldId, "{$gridField->getName ()}_{$fieldId}"));
					if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
						$this->adb->pquery (
							'INSERT INTO vtiger_subfields_special (fieldid, name, label, sequence, uitype, length, `precision`, defaultvalue, `values`, action_field, filter_field, relmodule, data_field) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
							array ($fieldId, "{$gridField->getName ()}_{$fieldId}", $gridField->getLabel (), $gridField->getSequence (), $gridField->getUiType (), $gridField->getLength (), $gridField->getPrecision (), $gridField->getDefaultValue (), $gridField->getValues (), $gridField->getActionField (), $gridField->getFilterField (), $gridField->getModuleReferences (), $gridField->getDataField ())
						);
					} else {
						$this->adb->pquery (
							'UPDATE vtiger_subfields_special SET label=?, sequence=?, uitype=?, length=?, `precision`=?, defaultvalue=?, `values`=?, action_field=?, filter_field=?, relmodule=?, data_field=? WHERE fieldid=? AND name=?',
							array ($gridField->getLabel (), $gridField->getSequence (), $gridField->getUiType (), $gridField->getLength (), $gridField->getPrecision (), $gridField->getDefaultValue (), $gridField->getValues (), $gridField->getActionField (), $gridField->getFilterField (), $gridField->getModuleReferences (), $gridField->getDataField (), $fieldId, "{$gridField->getName ()}_{$fieldId}")
						);
					}
					$processedGridFieldNames [] = "{$gridField->getName ()}_{$fieldId}";
					$sequence++;
				}

				$questionMarks = str_repeat ('?, ', (count ($processedGridFieldNames) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_subfields_special WHERE fieldid=? AND name NOT IN ({$questionMarks})", array_merge (array ($fieldId), $processedGridFieldNames));
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $grid;
		}

		/**
		 * @param GridField $gridField
		 * @param $fieldId
		 */
		public function changeNamesToSpecialField ($gridField, $fieldId) {
			if ((empty ($gridField->getUitype ())) || (empty ($fieldId))) {
				return;
			}
			$uitype = $gridField->getUiType ();
			$this->changeNameToFilterField ($gridField, $fieldId);
			$this->changeNameToActionField ($gridField, $fieldId, $uitype);
			$this->changeNameToDataField ($gridField, $fieldId, $uitype);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return GridManager
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
