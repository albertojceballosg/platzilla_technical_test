<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/platzilla/Managers/FieldManager.php');

	/**
	 * Class Class AddGridFieldsHelper
	 *
	 * Esta clase contiene los metodos para crear y editar las tablas y sus funcionalidades.
	 */
	abstract class AddGridFieldsHelper {

		/**
		 * @param string $nameField
		 *
		 * @return string
		 */
		private static function clearFieldName ($nameField) {
			$arrayName = explode ('_', $nameField);
			array_pop ($arrayName);
			return implode ('_', $arrayName);
		}

		/**
		 * Cambia la etiqueta seleccionada por el nombre del subcampo en una tabla
		 *
		 * @param array $fieldLabels
		 * @param array $fieldNames
		 * @param array $selectDestination
		 * @param array $checkDestination
		 */
		private static function changeDestinationByName ($fieldLabels, $fieldNames, &$selectDestination, &$checkDestination) {
			$totalLabels = count ($fieldLabels);
			if (is_array ($checkDestination)) {
				$totalCheck = count ($checkDestination);
			} else {
				$totalCheck = 0;
			}
			if (is_array ($selectDestination)) {
				$totalSelect = count ($selectDestination);
			} else {
				$totalSelect = 0;
			}
			for ($n = 0; $n < $totalLabels; $n++) {
				for ($s = 0; $s < $totalSelect; $s++) {
					if ($selectDestination[ $s ] == $fieldLabels[ $n ]) {
						$selectDestination[ $s ] = $fieldNames[ $n ];
						break;
					}
				}
				for ($c = 0; $c < $totalCheck; $c++) {
					if ($checkDestination[ $c ] == $fieldLabels[ $n ]) {
						$checkDestination[ $c ] = $fieldNames[ $n ];
						break;
					}
				}
			}
		}

		/**
		 * Obtiene la estructura de datos de los filtros de color
		 *
		 * @param integer $fieldId
		 * @param string $name
		 * @param array $fieldColor
		 * @param array $fieldsData
		 *
		 * @return string
		 */
		private static function getFilterColorToField ($fieldId, $name, $fieldColor, $fieldsData) {
			$joinCondition = $fieldsData ['joinCondition'];
			$selectedColor = $fieldsData ['selectedColor'];
			$fieldToFilter = $fieldsData ['fieldToFilter'];
			$actionFilter  = $fieldsData ['actionFilter'];
			$selectedValue = $fieldsData ['selectedValue'];
			$arrayFilter   = array ();
			$totalFields   = count ($fieldColor);
			for ($k = 0; $k < $totalFields; $k++) {
				if ($fieldColor[ $k ] == $name) {
					if (!empty($fieldId)) {
						$myField = self::sanitizeString ($fieldToFilter[ $k ]);
						$myField .= '_' . $fieldId;
					} else {
						$myField = $fieldToFilter[ $k ];
					}
					$arrayFilter[] = array (
						'field'     => $myField,
						'color'     => $selectedColor[ $k ],
						'condition' => $actionFilter[ $k ],
						'value'     => $selectedValue[ $k ],
						'join'      => $joinCondition[ $k ],
					);
				}
			}
			return base64_encode (serialize ($arrayFilter));
		}

		/**
		 * Obtiene la estructura de datos de los modulos relacionados
		 *
		 * @param PearDatabase $adb
		 * @param integer $relationId
		 *
		 * @return string
		 * @throws Exception
		 */
		private static function getRelatedModuleName (PearDatabase $adb, $relationId) {
			$results = $adb->pquery (
				'SELECT
					vtiger_entityname.modulename
				FROM
					vtiger_entityname
 					INNER JOIN vtiger_relatedlists ON vtiger_relatedlists.related_tabid=vtiger_entityname.tabid
 				WHERE
 					vtiger_relatedlists.relation_id = ?',
				array ($relationId)
			);
			return $adb->query_result ($results, 0, 'modulename');
		}

		/**
		 * Verifica si se configurado una fila resumen en la tabla
		 *
		 * @param PearDatabase $adb
		 * @param integer $fieldId
		 *
		 * @return boolean|null|string|string[]
		 * @throws Exception
		 */
		private static function hasSummaryRow (PearDatabase $adb, $fieldId) {
			$result = $adb->pquery (
				'SELECT `subfieldsid` FROM `vtiger_subfields_special` WHERE fieldid = ? AND uitype = ? ',
				array ($fieldId, '2203')
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				return $adb->query_result ($result, 0, 'subfieldsid');
			} else {
				return false;
			}
		}

		/**
		 * Elimina caracteres especiales en el nombre de una tabla
		 *
		 * @param string $string
		 *
		 * @return string
		 */
		private static function sanitizeString ($string) {
			$string = str_replace (
				array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$string
			);
			$string = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'_',
				$string
			);
			$string = trim (strtolower ($string));
			return $string;
		}

		/**
		 * Establece la estructura de datos para acciones del tipo checkbox
		 *
		 * @param string $fieldNames
		 * @param string $checkName
		 * @param array $checkValue
		 * @param array $checkDestination
		 *
		 * @return string
		 */
		private static function setCheckAction ($fieldNames, $checkName, $checkValue, $checkDestination) {
			if (!is_array ($checkValue)) {
				return null;
			}
			$resultsArray    = array ();
			$listDestination = array ();
			if (in_array ($fieldNames, $checkName)) {
				$totalFieldName = count ($checkName);
				for ($k = 0; $k < $totalFieldName; $k++) {
					if ($checkName[ $k ] == $fieldNames) {
						foreach ($checkDestination as $desttination) {
							list($check, $value) = explode ('@', $desttination);
							if ($check == $checkName[ $k ]) {
								$listDestination[] = $value;
							}
						}
						$resultsArray[ $checkValue[ $k ] ] = join (',', $listDestination);
						unset($listDestination);
					}
				}
			} else {
				return null;
			}
			return base64_encode (serialize ($resultsArray));
		}

		/**
		 * Establece la estructura de datos para importar columnas de otra tabla
		 *
		 * @param array $fieldToExport
		 * @param array $fieldToImport
		 * @param string $moduleReference
		 * @param integer $fieldId
		 *
		 * @return string
		 */
		private static function setImportData ($fieldToExport, $fieldToImport, $moduleReference, $fieldId) {
			$resultArray = array ();
			$totalImport = count ($fieldToExport [ $moduleReference ]);
			for ($k = 0; $k < $totalImport; $k++) {
				$fieldsName = self::sanitizeString ($fieldToImport [ $moduleReference ][ $k ]);
				$fieldsName .= '_' . $fieldId;
				$resultArray[ $fieldsName ] = $moduleReference . '@' . $fieldToExport [ $moduleReference ][ $k ];
			}
			return base64_encode (serialize ($resultArray));
		}

		/**
		 * Establece la estructura de datos  para listas relacionadas
		 *
		 * @param PearDatabase $adb
		 * @param array $listDataArray
		 * @param string $moduleName
		 *
		 * @return string
		 * @throws Exception
		 */
		private static function setRelatedListData ($adb, &$listDataArray, $moduleName) {
			$totalListData = count ($listDataArray);
			$dataReturn    = '';
			for ($l = 0; $l < $totalListData; $l++) {
				$pos = strpos ($listDataArray[ $l ], $moduleName);
				if ($pos !== false) {
					$postDelimiter = strpos ($listDataArray[ $l ], '@');
					if ($postDelimiter !== false) {
						$relationSelected    = explode ('@', $listDataArray[ $l ]);
						$moduleId            = $relationSelected[0];
						$relationSelected[0] = self::getRelatedModuleName ($adb, $relationSelected[0]);
						$dataReturn          = implode ('@', $relationSelected);
						$moduleLabel         = getTabIdLabelByName ($moduleName);
						$dataReturn .= '@' . $moduleId . '@' . $moduleLabel;
						$listDataArray[ $l ] = '';
						break;
					}
				}
			}
			if (!empty($dataReturn)) {
				return $dataReturn;
			} else {
				return $moduleName;
			}
		}

		/**
		 * Establece la estructura de datos para las acciones de campos tipo desplegables
		 *
		 * @param string $fieldNames
		 * @param array $selectName
		 * @param array $actionFieldId
		 * @param array $selectDestination
		 *
		 * @return string
		 */
		private static function setSelectFieldAction ($fieldNames, $selectName, $actionFieldId, $selectDestination) {
			if (!is_array ($selectName)) {
				return null;
			}
			$resultsArray = array ();
			if (in_array ($fieldNames, $selectName)) {
				$totalFieldName = count ($selectName);
				for ($k = 0; $k < $totalFieldName; $k++) {
					if ($selectName[ $k ] == $fieldNames) {
						$resultsArray[ $actionFieldId[ $k ] ] = trim ($selectDestination[ $k ]);
					}
				}
			}
			return base64_encode (serialize ($resultsArray));
		}

		/**
		 * Establece los valores de campos tipos desplegables.
		 *
		 * @param string $fieldName
		 * @param string $fieldValue
		 * @param array $selectName
		 * @param array $selectValue
		 * @param array $actionFieldId
		 *
		 * @return string
		 */
		private static function setSelectFieldValue ($fieldName, $fieldValue, $selectName, $selectValue, $actionFieldId) {
			$resultsArray = array ();
			if (is_array ($fieldValue)) {
				$totalFieldName = count ($selectName);
				for ($k = 0; $k < $totalFieldName; $k++) {
					if ($selectName[ $k ] == $fieldName) {
						$resultsArray[ $actionFieldId[ $k ] ] = $selectValue[ $k ];
					}
				}
			} else {
				$listValues = preg_split ("/(\r\n|\n|\r)/", $fieldValue);
				if (!is_array ($listValues)) {
					$listValues = (explode ("\n", $fieldValue));
				}
				$totalFieldValues = count ($listValues);
				if (count($actionFieldId) > 0) {
					for ($k = 0; $k < $totalFieldValues; $k++) {
						if ($actionFieldId[$k] == 1) {
							$resultsArray[$listValues[$k]] = $listValues[$k];
						} else {
							$resultsArray[$actionFieldId[$k]] = $listValues[$k];
						}
					}
				} else {
					for ($k = 0; $k < $totalFieldValues; $k++) {
						$resultsArray[$listValues[$k]] = $listValues[$k];
					}
				}
			}
			
			return base64_encode (serialize ($resultsArray));
		}

		/**
		 * Establece la estructura de las tablas para campos tipo grid
		 *
		 * @param PearDatabase $adb
		 */
		private static function setSubFieldDataBase (PearDatabase $adb) {
			$adb->query (
				'CREATE TABLE IF NOT EXISTS `vtiger_subfields_special` (
					`subfieldsid` INT(11) NOT NULL AUTO_INCREMENT,
					`fieldid` INT(19) NOT NULL,
					`name` VARCHAR(255) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`sequence` INT(11) NOT NULL,
					`uitype` INT(11) NOT NULL,
					`length` INT(11) NULL DEFAULT NULL,
					`precision` INT(11) NULL DEFAULT NULL,
					`defaultvalue` VARCHAR(255) NULL DEFAULT NULL,
					`values` TEXT NULL,
					`action_field` LONGBLOB NULL DEFAULT NULL,
					`filter_field` LONGBLOB NULL DEFAULT NULL,
					`relmodule` TEXT NULL DEFAULT NULL,
					`data_field` TEXT NULL DEFAULT NULL,
					PRIMARY KEY  (`subfieldsid`,`fieldid`),
					CONSTRAINT `vtiger_subfields_special_ibfk` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8'
			);
			$adb->query (
				'CREATE TABLE IF NOT EXISTS `vtiger_subfields_values` (
					`subfieldsvaluesid` INT(11) NOT NULL AUTO_INCREMENT,
					`modulecfid` INT(11) NOT NULL,
					`subfieldsid` INT(11) NOT NULL,
					`field_values` LONGBLOB NULL DEFAULT NULL,
					PRIMARY KEY  (`subfieldsvaluesid`,`subfieldsid`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8'
			);
		}

		/**
		 * Establece la estructura de datos para la fila resumen
		 *
		 * @param array $fieldsData
		 * @param integer $fieldId
		 *
		 * @return string
		 */
		private static function setSummaryData (array $fieldsData, $fieldId) {
			$resultsArray       = array ();
			$calculatedSystemId = $fieldsData ['calculatedSystemId'];
			$summaryActionField = $fieldsData ['summaryActionField'];
			$summaryField       = $fieldsData ['summaryField'];

			$totalSummary = count ($summaryField);
			for ($i = 0; $i < $totalSummary; $i++) {
				$fieldsName = $summaryField[ $i ];
				if ($fieldsName != 'false') {
					$fieldsName = self::sanitizeString ($summaryField[ $i ]);
					$fieldsName .= '_' . $fieldId;
				}

				$resultsArray[ $i ] = array (
					'field'        => $fieldsName,
					'action'       => $summaryActionField[ $i ],
					'calculatedId' => $calculatedSystemId[ $i ],
				);
			}
			return base64_encode (serialize ($resultsArray));
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Esta función Establece registra de un campo tipo Grid, o tabla inteligente
		 * NOTA: CodeSniffer detecta una violación de complejidad ciclomática, dada la cantidad de casos. Es imposible reducirlos (todos son necesarios)
		 * y no tiene sentido refacturizar la función
		 *
		 * @param PearDatabase $adb
		 * @param $moduleName
		 * @param $gridLabel
		 * @param $blockId
		 * @param array $fieldsData
		 * @param $idSelectedReg
		 *
		 * @throws Exception
		 * @throws FieldException
		 */
		public static function setGridField (PearDatabase $adb, $moduleName, $gridLabel, $blockId, array $fieldsData, $idSelectedReg) {
			$actionFieldId     = $fieldsData ['actionFieldId'];
			$checkName         = $fieldsData ['checkNameField'];
			$checkDestination  = $fieldsData ['checkFieldDest'];
			$checkValue        = $fieldsData ['checkValue'];
			$fieldLabels       = $fieldsData ['labels'];
			$fieldLengths      = $fieldsData ['lengths'];
			$fieldModules      = $fieldsData ['modules'];
			$fieldNames        = $fieldsData ['names'];
			$fieldPrecisions   = $fieldsData ['precisions'];
			$fieldTypes        = $fieldsData ['types'];
			$fieldValues       = $fieldsData ['values'];
			$fieldToColor      = $fieldsData ['fieldToColor'];
			$selectName        = $fieldsData ['selectNameField'];
			$fieldImportId     = $fieldsData ['fieldImportId'];
			$fieldToExport     = $fieldsData ['fieldToExport'];
			$fieldToImport     = $fieldsData ['fieldToImport'];
			$fieldId           = $adb->getUniqueID ('vtiger_field');
			$gridName          = self::sanitizeString ($gridLabel);
			$importData        = '';
			$listData          = $fieldsData ['listSelected'];
			$moduleReference   = explode (';', $fieldsData ['moduleReference']);
			$selectValue       = $fieldsData ['selectValue'];
			$selectDestination = $fieldsData ['destinationField'];
			$sequence          = 0;
			$summaryField      = $fieldsData ['summaryField'];

			self::setSubFieldDataBase ($adb);
			$field = Field::getInstance ('X~O')
				->setId ($fieldId)
				->setModuleName ($moduleName)
				->setColumnName ($gridName)
				->setTableName ('vtiger_' . $moduleName)
				->setGeneratedType ('1')
				->setUiType (FieldInterface::UI_TYPE_GRID)
				->setName ($gridName)
				->setLabel ($gridLabel)
				->setReadOnly (1)
				->setPresence (2)
				->setDefaultValue ('')
				->setSequence (2)
				->setBlockId ($blockId)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
				->setQuickCreate (1)
				->setMassEditable (1)
				->setMandatory (true)
				->setLocked (false);

			$totalFields = count ($fieldNames);
			self::changeDestinationByName ($fieldLabels, $fieldNames, $selectDestination, $checkDestination);
			if (!empty($listData)) {
				$listDataArray = explode (';', $listData);
			}

			if ((!empty($moduleReference)) && (count ($fieldToExport) > 0) && (count ($fieldToImport) > 0)) {
				foreach ($moduleReference as $modReference) {
					$importData [$modReference] = self::setImportData($fieldToExport, $fieldToImport, $modReference, $fieldId);
				}
			}

			for ($i = 0; $i < $totalFields; $i++) {
				if (!empty ($fieldNames [ $i ])) {
					$precision     = 0;
					$fieldLength   = 0;
					$values        = '';
					$action        = '';
					$colorFilter   = '';
					$relatedModule = '';
					$dataValues    = '';
					if (!empty($fieldValues[ $i ])) {
						$values = str_replace ("\n", ',', $fieldValues[ $i ]);
					}
					if ($fieldTypes [ $i ] == '10') {
						if (isset($listDataArray)) {
							$relatedModule = self::setRelatedListData ($adb, $listDataArray, $fieldModules [ $i ]);
						} else {
							$relatedModule = $fieldModules [ $i ];
						}
						if ((in_array ($fieldModules [ $i ], $moduleReference)) && !empty($importData [ $fieldModules [ $i ] ])) {
							$action = $importData [ $fieldModules [ $i ] ];
						}
					} else if ($fieldTypes [ $i ] == FieldInterface::UI_TYPE_PICKLIST) {
						$values = self::setSelectFieldValue ($fieldNames[ $i ], $fieldValues[ $i ], $selectName, $selectValue, $actionFieldId);
						$action = self::setSelectFieldAction ($fieldNames[ $i ], $selectName, $actionFieldId, $selectDestination);
					} else if ($fieldTypes [ $i ] == FieldInterface::UI_TYPE_CHECKBOX) {
						$action = self::setCheckAction ($fieldNames[ $i ], $checkName, $checkValue, $checkDestination);
					} else if ($fieldTypes [ $i ] == FieldInterface::UI_TYPE_GRID) {
						$fieldNames[ $i ] = self::clearFieldName ($fieldNames[ $i ]);
						$dataValues       = $idSelectedReg . '@' . $fieldImportId[ $i ];
					} else if ($fieldTypes [ $i ] == FieldInterface::UI_TYPE_ATTACHMENTS) {
						$relatedModule = $moduleName;
					}
					if (!empty($fieldPrecisions[ $i ])) {
						$precision = $fieldPrecisions[ $i ];
					}
					if (!empty($fieldLengths[ $i ])) {
						$fieldLength = $fieldLengths[ $i ];
					}
					if (in_array ($fieldNames[ $i ], $fieldToColor)) {
						$colorFilter = self::getFilterColorToField ($fieldId, $fieldNames[ $i ], $fieldToColor, $fieldsData);
					}
					$fieldsName = self::sanitizeString ($fieldNames[ $i ]);
					$sequence = ($i + 1);
					$gridFields [] = GridField::getInstance()
						->setName ($fieldsName)
						->setLabel ($fieldLabels [ $i ])
						->setSequence ($sequence)
						->setUiType ($fieldTypes [ $i ])
						->setLength ($fieldLength)
						->setPrecision ($precision)
						->setDefaultValue (null)
						->setValues ($values)
						->setActionField ($action)
						->setFilterField ($colorFilter)
						->setModuleReferences ($relatedModule)
						->setDataField ($dataValues);
				}
			}
			if (count ($summaryField) > 0) {
				$dataValues = self::setSummaryData ($fieldsData, $fieldId);
				$sequence++;
				$gridFields [] = GridField::getInstance ()
					->setName ('summary')
					->setLabel ('Fila resumen')
					->setSequence (($sequence + 1))
					->setUiType (FieldInterface::UI_TYPE_SUMMARY_ROW)
					->setLength (0)
					->setPrecision (0)
					->setDefaultValue ('')
					->setValues ('')
					->setActionField ('')
					->setFilterField ('')
					->setModuleReferences ('')
					->setDataField ($dataValues);
			}
			$field->setGrid (
				Grid::getInstance ()
					->setName ($gridName)
					->setModuleName ($moduleName)
					->setFields ($gridFields)
			);
			FieldManager::getInstance ($adb)->saveField ($field);
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param PearDatabase $adb
		 * @param string $fieldColumn
		 * @param integer $fieldId
		 * @param string $gridLabel
		 */
		public static function addSpecialField (PearDatabase $adb, $fieldColumn, $fieldId, $gridLabel) {
			if (is_array ($fieldColumn)) {
				if (count ($fieldColumn) > 1) {
					$values = base64_encode (serialize ($fieldColumn));
					$adb->pquery (
						'INSERT INTO `vtiger_subfields_special` (`fieldid`, `name`, `label`, `sequence`, `uitype`, `length`, `precision`, `defaultvalue`, `values`, `action_field`, `relmodule`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array (
							$fieldId,
							'special_fields',
							$gridLabel,
							1,
							'2202',
							null,
							null,
							null,
							$values,
							null,
							null,
						)
					);
				}
			}
		}

		/**
		 *  @codingStandardsIgnoreStart
		 *
		 * @param PearDatabase $adb
		 * @param array $fieldsData
		 * @param $fieldId
		 * @param $idSelectedReg
		 * @param $fieldsDataFilter
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function updateDataGrid (PearDatabase $adb, array $fieldsData, $fieldId, $idSelectedReg, $fieldsDataFilter) {
			$fieldLabels      = $fieldsData ['labels'];
			$fieldLengths     = $fieldsData ['lengths'];
			$fieldModules     = $fieldsData ['modules'];
			$fieldNames       = $fieldsData ['names'];
			$fieldPrecisions  = $fieldsData ['precisions'];
			$fieldTypes       = $fieldsData ['types'];
			$fieldValues      = $fieldsData ['values'];
			$fieldImportId    = $fieldsData ['fieldImportId'];
			$fieldImportRegId = $fieldsData ['fieldImportRegId'];
			$subFieldId       = $fieldsData ['subfieldsid'];
			$fieldToColor     = $fieldsDataFilter ['fieldToColor'];
			$checkValue       = $fieldsData ['checkValue'];
			$checkFieldDest   = $fieldsData ['checkFieldDest'];
			$checkNameField   = $fieldsData ['checkNameField'];
			$selectNameField  = $fieldsData ['selectNameField'];
			$selectValue      = $fieldsData ['selectValue'];
			$actionFieldId    = $fieldsData ['actionFieldId'];
			$destinationField = $fieldsData ['destinationField'];
			$listData         = $fieldsData ['listSelected'];
			$summaryField     = $fieldsData ['summaryField'];
			$fieldToExport    = $fieldsData ['fieldToExport'];
			$fieldToImport    = $fieldsData ['fieldToImport'];
			$moduleReference  = $fieldsData ['moduleReference'];
			$importData       = '';
			$moduleName       = $fieldsData ['moduleName'];
			$defaultValues    = $fieldsData ['defaultValues'];
			$responseMessage = '';
			self::changeDestinationByName ($fieldLabels, $fieldNames, $destinationField, $checkFieldDest);
			if (!empty($listData)) {
				$listDataArray = explode (';', $listData);
			}

			if ((!empty ($moduleReference)) && (count ($fieldToExport) > 0) && (count ($fieldToImport) > 0)) {
				foreach ($moduleReference as $modReference) {
					$importData [$modReference] = self::setImportData ($fieldToExport, $fieldToImport, $modReference, $fieldId);
				}
			}
			$totalFields = count ($fieldNames);
			for ($i = 0; $i < $totalFields; $i++) {
				if (!empty ($fieldNames [ $i ])) {
					$precision     = null;
					$fieldLength   = null;
					$values        = null;
					$action        = null;
					$colorFilter   = null;
					$relatedModule = null;
					$dataValues    = null;
					if (!empty($fieldValues[ $i ])) {
						$values = str_replace ("\n", ',', $fieldValues[ $i ]);
					}
					if ($fieldTypes [ $i ] == '10') {
						if (isset($listDataArray)) {
							$relatedModule = self::setRelatedListData ($adb, $listDataArray, $fieldModules [ $i ]);
						} else {
							$relatedModule = $fieldModules [ $i ];
						}

						if ((in_array ($fieldModules [ $i ], $moduleReference)) && !empty($importData [ $fieldModules [ $i ] ])) {
							$action = $importData [ $fieldModules [ $i ] ];
						}
					} else if ($fieldTypes [ $i ] == '15') {
						$values = self::setSelectFieldValue ($fieldNames[ $i ], $fieldValues[ $i ], $selectNameField, $selectValue, $actionFieldId);
						$action = self::setSelectFieldAction ($fieldNames[ $i ], $selectNameField, $actionFieldId, $destinationField);
					} else if ($fieldTypes [ $i ] == '56') {
						$action = self::setCheckAction ($fieldNames[ $i ], $checkNameField, $checkValue, $checkFieldDest);
					} else if ($fieldTypes [ $i ] == '2202') {
						if ($fieldImportRegId[ $i ] == 0) {
							$dataValues = $idSelectedReg . '@' . $fieldImportId[ $i ];
						} else {
							$dataValues = $fieldImportRegId[ $i ] . '@' . $fieldImportId[ $i ];
						}
					} else if ($fieldTypes [ $i ] == '4096') {
						$relatedModule = $moduleName;
					}
					if (!empty($fieldPrecisions[ $i ])) {
						$precision = $fieldPrecisions[ $i ];
					}
					if (!empty($fieldLengths[ $i ])) {
						$fieldLength = $fieldLengths[ $i ];
					}
					if ((is_array ($fieldToColor)) && (in_array ($fieldNames[ $i ], $fieldToColor))) {
						$colorFilter = self::getFilterColorToField ($fieldId, $fieldNames[ $i ], $fieldToColor, $fieldsDataFilter);
					} else {
						$colorFilter = null;
					}

					$fieldsName = self::sanitizeString ($fieldNames[ $i ]);
					$fieldsName .= '_' . $fieldId;

					if ($subFieldId[ $i ] == 0) {
						$adb->pquery (
							'INSERT INTO `vtiger_subfields_special` (`fieldid`, `name`, `label`, `sequence`, `uitype`, `length`, `precision`, `defaultvalue`, `values`, `action_field`, `filter_field`, `relmodule`, `data_field`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
							array ($fieldId, $fieldsName, $fieldLabels[ $i ], $i, $fieldTypes[ $i ], $fieldLength, $precision, $defaultValues[ $i ], $values, $action, $colorFilter, $relatedModule, $dataValues)
						);
						$responseMessage .= '- Se ha insertado la columna ' . $fieldLabels[ $i ] . '@';
					} else if ($fieldTypes [ $i ] != '2204') {
						$adb->pquery (
							'UPDATE vtiger_subfields_special SET `name` = ?,`label` = ?, `sequence` = ?, `uitype` = ?, `length` = ?, `precision` = ?, `defaultvalue` = ?, `values`=?, `action_field` =?, `filter_field`= ?, `relmodule`= ?, `data_field` = ? WHERE subfieldsid = ? ',
							array ($fieldsName, $fieldLabels[ $i ], $i, $fieldTypes[ $i ], $fieldLength, $precision, $defaultValues[ $i ], $values, $action, $colorFilter, $relatedModule, $dataValues, $subFieldId[ $i ])
						);
						$responseMessage .= '- Se ha actualizado la columna ' . $fieldLabels[ $i ] . '@';
					}
				}
			}
			if (is_array ($summaryField) && !empty($summaryField)) {
				$dataValues = self::setSummaryData ($fieldsData, $fieldId);
				$subId      = self::hasSummaryRow ($adb, $fieldId);
				$fieldsName = 'summary_' . $fieldId;
				$sequence   = ($totalFields + 1);
				if ($subId) {
					$adb->pquery (
						'UPDATE vtiger_subfields_special SET `data_field` = ? WHERE subfieldsid = ? ',
						array ($dataValues, $subId)
					);
					$responseMessage .= '- Se ha actualizado la columna Fila resumen @';
				} else {
					$adb->pquery (
						'INSERT INTO `vtiger_subfields_special` (`fieldid`, `name`, `label`, `sequence`, `uitype`, `length`, `precision`, `defaultvalue`, `values`, `action_field`, `filter_field`, `relmodule`, `data_field`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($fieldId, $fieldsName, 'Fila resumen', $sequence, '2203', null, null, null, null, null, null, null, $dataValues)
					);
					$responseMessage .= '- Se ha insertado la columna Fila resumen @';
				}
			}
		
			// Validar y recrear tabla summary si existe y la estructura cambió
			require_once('include/utils/GridFieldUtils.class.php');
			try {
				// Obtener nombre del grid
				$gridFieldQuery = "SELECT f.fieldname, t.name as module 
				                   FROM vtiger_field f 
				                   INNER JOIN vtiger_tab t ON t.tabid = f.tabid 
				                   WHERE f.fieldid = ? AND f.uitype = 2202";
				$gridFieldResult = $adb->pquery($gridFieldQuery, array($fieldId));
			
				if ($adb->num_rows($gridFieldResult) > 0) {
					$gridName = $adb->query_result($gridFieldResult, 0, 'fieldname');
					$moduleName = $adb->query_result($gridFieldResult, 0, 'module');
					$summaryTableName = 'vtiger_grid_summary_' . $gridName;
				
					// Verificar si el grid tiene fila summary (uitype 2203)
					$hasSummaryQuery = "SELECT COUNT(*) as count FROM vtiger_subfields_special 
					                    WHERE fieldid = ? AND uitype = 2203";
					$hasSummaryResult = $adb->pquery($hasSummaryQuery, array($fieldId));
					$hasSummaryRow = ($adb->query_result($hasSummaryResult, 0, 'count') > 0);
				
					if ($hasSummaryRow) {
						// Verificar si existe la tabla summary
						$tableExistsQuery = "SHOW TABLES LIKE ?";
						$tableExistsResult = $adb->pquery($tableExistsQuery, array($summaryTableName));
						$tableExists = ($adb->num_rows($tableExistsResult) > 0);
					
						if ($tableExists) {
							// Validar si la estructura de la tabla coincide con la configuración actual
							$currentColumns = GridFieldUtils::getSummaryGridFields($adb, $moduleName, $gridName);
						
							// Obtener columnas actuales de la tabla
							$showColumnsQuery = "SHOW COLUMNS FROM {$summaryTableName}";
							$showColumnsResult = $adb->pquery($showColumnsQuery, array());
							$existingColumns = array();
							while ($columnRow = $adb->fetchByAssoc($showColumnsResult)) {
								if ($columnRow['Field'] != 'recordid') {
									$existingColumns[] = $columnRow['Field'];
								}
							}
						
							// Comparar columnas (bidireccional: detecta agregadas Y eliminadas)
							$columnsMatch = true;
							$currentColumnNames = array_keys($currentColumns);
						
							// Verificar si el número de columnas cambió
							if (count($currentColumnNames) != count($existingColumns)) {
								$columnsMatch = false;
							} else {
								// Verificar si hay columnas nuevas que no existen en la tabla
								foreach ($currentColumnNames as $colName) {
									if (!in_array($colName, $existingColumns)) {
										$columnsMatch = false;
										break;
									}
								}
							
								// Verificar si hay columnas en la tabla que ya no están en la configuración (eliminadas)
								if ($columnsMatch) {
									foreach ($existingColumns as $existingCol) {
										if (!in_array($existingCol, $currentColumnNames)) {
											$columnsMatch = false;
											break;
										}
									}
								}
							}
						
							// Si la estructura no coincide, recrear tabla y recalcular totales
							if (!$columnsMatch) {
								$responseMessage .= '- Estructura de tabla summary desincronizada, recreando...@';
							
								// Recrear tabla summary
								GridFieldUtils::createTempGridValues($adb, $moduleName, $gridName, $summaryTableName, false);
							
								// Recalcular todos los totales
								$recalcResult = GridFieldUtils::recalculateGridField($adb, $fieldId, 100);
							
								if ($recalcResult['success']) {
									$responseMessage .= '- Tabla summary recreada y totales recalculados exitosamente@';
									$responseMessage .= '- Registros procesados: ' . $recalcResult['processed'] . '@';
								} else {
									$responseMessage .= '- Advertencia: Error al recalcular totales@';
								}
							}
						} else {
							// Si no existe la tabla pero tiene summary, crearla y calcular totales
							$responseMessage .= '- Creando tabla summary optimizada...@';
						
							// Crear tabla summary
							GridFieldUtils::createTempGridValues($adb, $moduleName, $gridName, $summaryTableName, false);
						
							// Calcular todos los totales
							$recalcResult = GridFieldUtils::recalculateGridField($adb, $fieldId, 100);
						
							if ($recalcResult['success']) {
								$responseMessage .= '- Tabla summary creada y totales calculados exitosamente@';
								$responseMessage .= '- Registros procesados: ' . $recalcResult['processed'] . '@';
							}
						}
					}
				}
			} catch (Exception $summaryException) {
				// Si falla la validación/recreación de la tabla summary, solo registrar el error
				// No interrumpir el guardado de la estructura del grid
				error_log("[AddGridFieldsHelper::updateDataGrid] Error validando tabla summary: " . $summaryException->getMessage());
				$responseMessage .= '- Advertencia: No se pudo validar tabla summary@';
			}
		
			return $responseMessage;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * Obtiene los campos con cálculo para fila resumen
		 *
		 * @param PearDatabase $adb
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getCalculatedSystem ($adb) {
			$resultsArray = array ();
			$result       = $adb->query (
				'SELECT `calculated_systemid`, `name` FROM vtiger_calculated_system
					ORDER BY  `name` ASC'
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				while ($row) {
					$resultsArray[] = $row;
					$row            = $adb->fetchByAssoc ($result, -1, false);
				}
			}
			return $resultsArray;
		}

	}
