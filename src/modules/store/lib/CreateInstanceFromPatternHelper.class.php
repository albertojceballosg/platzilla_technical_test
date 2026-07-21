<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Objects/Module.php');

	abstract class CreateInstanceFromPatternHelper {

		/**
		 * @param PearDatabase $adb
		 * @param string $tablename
		 *
		 * @return array
		 */
		private static function getGridFieldIds (PearDatabase $adb, $tablename) {
			$resultArray  = array ();
			$fieldisArray = array ();
			$lastFieldId  = 0;
			$result       = $adb->pquery (
				'SELECT ss.subfieldsid, ss.fieldid FROM vtiger_subfields_special ss
				INNER JOIN vtiger_field f ON ss.fieldid = f.fieldid
				WHERE f.tablename=?
				AND f.uitype=?
				ORDER BY ss.fieldid ASC, ss.subfieldsid ASC',
				array ($tablename, '2202')
			);
			if (($result) || ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				while ($row) {
					if ($lastFieldId == 0) {
						$fieldisArray[] = $row['subfieldsid'];
						$lastFieldId    = $row['fieldid'];
					} else if ($lastFieldId == $row['fieldid']) {
						$fieldisArray[] = $row['subfieldsid'];
					} else {
						$resultArray [] = $fieldisArray;
						unset($fieldisArray);
						$fieldisArray   = array ();
						$fieldisArray[] = $row['subfieldsid'];
						$lastFieldId    = $row['fieldid'];
					}
					$row = $adb->fetchByAssoc ($result, -1, false);
				}
				$resultArray [] = $fieldisArray;
			}
			return $resultArray;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tablename
		 *
		 * @return array
		 */
		private static function hasGridFieldValues (PearDatabase $adb, $tablename) {
			$resultArray  = array ();
			$fieldisArray = array ();
			$lastModuleId = 0;
			$result       = $adb->pquery (
				'SELECT
					sv.*,
					ss.fieldid
				FROM
					vtiger_subfields_values sv
					INNER JOIN vtiger_subfields_special ss ON ss.subfieldsid=sv.subfieldsid
					INNER JOIN vtiger_field f ON ss.fieldid=f.fieldid
				WHERE
					f.tablename=? AND
					f.uitype=?
				ORDER BY
					sv.modulecfid ASC,
					sv.subfieldsid ASC ',
				array ($tablename, '2202')
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				while ($row) {
					if ($lastModuleId == 0) {
						$fieldisArray[] = $row;
						$lastModuleId   = $row['modulecfid'];
					} else if ($lastModuleId == $row['modulecfid']) {
						$fieldisArray[] = $row;
					} else {
						$resultArray [] = $fieldisArray;
						unset ($fieldisArray);
						$fieldisArray   = array ();
						$fieldisArray[] = $row;
						$lastModuleId   = $row['modulecfid'];
					}
					$row = $adb->fetchByAssoc ($result, -1, false);
				}
				$resultArray [] = $fieldisArray;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $resultArray;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 *
		 * @return string
		 */
		private static function getFieldCodeTypeColumnName (PearDatabase $adb, $tableName) {
			$result = $adb->pquery (
				'SELECT columnname FROM vtiger_field WHERE tablename=? AND uitype=?',
				array ($tableName, Field::UI_TYPE_CODE)
			);
			if ($adb->num_rows ($result) > 0) {
				$row        = $adb->fetchByAssoc ($result, -1, false);
				$columnName = $row ['columnname'];
			} else {
				$columnName = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}

			return $columnName;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $rowTable
		 *
		 * @return array
		 */
		private static function insertRecords (PearDatabase $adb, $rowTable) {
			$modulecfid = array ();
			$moduleName = $rowTable['module'];
			$tablename  = $rowTable['tabalename'];
			$created    = date ('Y-m-d H:i:s');
			$fieldId    = $rowTable['fieldId'];
			$fieldCod   = self::getFieldCodeTypeColumnName ($adb, $tablename);
			foreach ($rowTable['values'] as $row) {
				$current_id = $adb->getUniqueID ('vtiger_crmentity');
				$adb->pquery (
					'INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, modifiedby, setype, createdtime, modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($current_id, 1, 1, 1, $moduleName, $created, $created)
				);

				$modulecfid[] = $current_id;
				if (in_array ($fieldCod, array_keys ($row))) {
					$row[ $fieldCod ] = 'RP-' . $row[ $fieldCod ];
				}

				$row[ $fieldId ] = $current_id;
				$strValues       = '';
				$rowField        = implode (', ', array_keys ($row));
				foreach (array_values ($row) as $value) {
					if (!empty($strValues)) {
						$strValues .= ', ';
					}
					if (is_numeric ($value)) {
						$strValues .= $value;
					} else {
						$strValues .= "'" . $value . "'";
					}
				}

				$adb->query ("INSERT INTO {$tablename} ( {$rowField} ) VALUES ({$strValues})");
			}
			return $modulecfid;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $rowTable
		 * @param array $modulecfid
		 * @param array $subFieldsId
		 */
		private static function insertRecordsToGridValues (PearDatabase $adb, $rowTable, $modulecfid, $subFieldsId) {
			$totalGeneralGridField = count ($rowTable['GridField']);
			for ($i = 0; $i < $totalGeneralGridField; $i++) {
				$numOfFields     = 0;
				$gridFieldNumber = 0;
				if (!empty($modulecfid[ $i ])) {
					$totalGridField = count ($rowTable['GridField'][ $i ]);
					for ($k = 0; $k < $totalGridField; $k++) {
						$adb->pquery (
							'INSERT INTO vtiger_subfields_values (modulecfid, subfieldsid, field_values) VALUES (?, ?, ?)',
							array ($modulecfid[ $i ], $subFieldsId[ $gridFieldNumber ][ $numOfFields ], $rowTable['GridField'][ $i ][ $k ]['field_values'])
						);
						if (count ($subFieldsId[ $gridFieldNumber ]) > $numOfFields) {
							$numOfFields++;
						} else {
							$numOfFields = 0;
							if (count ($subFieldsId) > ($gridFieldNumber + 1)) {
								$gridFieldNumber++;
							}
						}
					}
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param $data
		 *
		 * @return array
		 */
		public static function getCustomButtonByWhere (PearDatabase $adb, $data) {
			$whereClause = 'WHERE 1 ';
			$where       = '';
			if (is_array ($data)) {
				foreach ($data as $key => $value) {
					if (is_numeric ($value)) {
						$where .= ' AND  `' . $key . '` = ' . $value;
					} else {
						$where .= ' AND  `' . $key . "` = '" . $value . "'";
					}
				}
			}
			$whereClause .= $where;
			$result  = $adb->query ("SELECT * FROM vtiger_custombuttons {$whereClause}");
			$buttons = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				unset($row['custombuttonid']);
				$buttons [] = $row;
			}
			return $buttons;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $data
		 * @param bool $checkButton
		 */
		public static function addBatchCustomButton (PearDatabase $adb, $data, $checkButton = true) {
			if (is_array ($data) && !empty($data)) {
				$totalField  = count (array_keys ($data[0]));
				$fieldSet    = array_fill (0, $totalField, '?');
				$strfieldSet = join (',', $fieldSet);
				foreach ($data as $button) {
					if ($checkButton) {
						$where      = array ('module' => $button['module'], 'action' => $button['action'], 'type' => $button['type'], 'onclick' => $button['onclick'], 'link' => $button['link']);
						$thisButton = self::getCustomButtonByWhere ($adb, $where);
					}
					if (empty($thisButton)) {
						$fields = join (', ', array_keys ($button));
						$adb->pquery (
							"INSERT INTO vtiger_custombuttons ({$fields}) VALUES ({$strfieldSet})",
							array_values ($button)
						);
					}
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 *
		 * @return array|bool|null
		 */
		public static function getAllModuleData (PearDatabase $adb, $module) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return false;
			}
			$moduleName = $module->getName ();
			$result     = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE deleted=0 AND setype=? ORDER BY crmid ASC LIMIT 0,1', array ($moduleName));
			if ($adb->num_rows ($result) > 0) {
				$resultsArray = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$entity      = CRMEntity::getInstance ($moduleName);
					$totalTables = count ($entity->tab_name);
					for ($e = 0; $e < $totalTables; $e++) {
						if ($entity->tab_name[ $e ] != 'vtiger_crmentity') {
							$tableName    = $entity->tab_name[ $e ];
							$fieldId      = $entity->tab_name_index[ $tableName ];
							$moduleResult = $adb->query ("SELECT tn.* FROM {$tableName} tn INNER JOIN vtiger_crmentity cr ON tn.{$fieldId}=cr.crmid AND cr.deleted=0 ORDER BY {$fieldId} ASC");
							$moduleValues = array ();
							$modulecfid   = array ();
							$rowModule    = $adb->fetchByAssoc ($moduleResult, -1, false);
							while ($rowModule) {
								$moduleValues [] = $rowModule;
								$rowModule       = $adb->fetchByAssoc ($moduleResult, -1, false);
							}
							$resultsArray [] = array (
								'module'     => $moduleName,
								'tabalename' => $tableName,
								'fieldId'    => $fieldId,
								'values'     => $moduleValues,
								'GridField'  => self::hasGridFieldValues ($adb, $tableName),
							);
							unset($moduleValues, $modulecfid);
						}
					}
				}
			} else {
				$resultsArray = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $resultsArray;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $data
		 */
		public static function setAllDataToModule (PearDatabase $adb, $data) {
			foreach ($data as $rowTable) {
				$modulecfid = self::insertRecords ($adb, $rowTable);
				$tablename  = $rowTable['tabalename'];
				if (!empty($rowTable['GridField'][0])) {
					$subFieldsId = self::getGridFieldIds ($adb, $tablename);
					if (!empty($subFieldsId[0][0])) {
						self::insertRecordsToGridValues ($adb, $rowTable, $modulecfid, $subFieldsId);
					}
				}
				unset($modulecfid);
			}
		}

	}
