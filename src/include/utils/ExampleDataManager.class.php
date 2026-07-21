<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');

	abstract class ExampleDataManager {

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $fieldCod
		 */
		private static function clearCodeToModule (PearDatabase $adb, $tableName, $fieldCod) {
			$result = $adb->query ("SELECT {$fieldCod} FROM {$tableName} WHERE {$fieldCod} LIKE '%RP-%'");
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$cod = substr ($row[ $fieldCod ], 3);
					$adb->query ("UPDATE {$tableName} SET {$fieldCod}='{$cod}' WHERE {$fieldCod}='{$row[$fieldCod]}'");
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $selectedModuleName
		 *
		 * @return string[]|null
		 */
		private static function getEntityTypes (PearDatabase $adb, $selectedModuleName = null) {
			if (!empty ($selectedModuleName)) {
				return array ($selectedModuleName);
			}

			$result = $adb->query ("SELECT DISTINCT setype FROM vtiger_crmentity WHERE deleted=0 AND setype NOT IN ('Calendar', 'oportunidades Attachment')");
			if ($adb->num_rows ($result) > 0) {
				$moduleNames = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$moduleNames [] = $row ['setype'];
				}
			} else {
				$moduleNames = null;
			}

			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $moduleNames;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $selectedModuleName
		 *
		 * @return boolean
		 */
		public static function deleteData (PearDatabase $adb, $selectedModuleName = null) {
			$moduleNames = self::getEntityTypes ($adb, $selectedModuleName);
			if (empty ($moduleNames)) {
				return false;
			}

			foreach ($moduleNames as $moduleName) {
				$result = $adb->pquery (
					'SELECT
						crm.*,
						f.columnname,
						f.tablename
					FROM
						vtiger_crmentity crm
						INNER JOIN vtiger_tab vt ON vt.name=crm.setype
						INNER JOIN vtiger_field f ON f.tabid=vt.tabid
					WHERE
						crm.deleted=0 AND
						crm.setype=? AND
						f.uitype=?
					ORDER BY
						crm.crmid ASC
					LIMIT 0, 1',
					array ($moduleName, FieldInterface::UI_TYPE_CODE)
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$fieldCod  = $row ['columnname'];
						$tableName = $row ['tablename'];
						$fieldId   = strtolower ($moduleName) . 'id';
						$adb->query (
							"UPDATE
								vtiger_crmentity crm
								INNER JOIN {$tableName} mt ON mt.{$fieldId}=crm.crmid
							SET
								crm.deleted=1
							WHERE
								crm.deleted=0 AND mt.{$fieldCod} LIKE '%RP-%'"
						);
						self::clearCodeToModule ($adb, $tableName, $fieldCod);
					}
				}
				if ($result instanceof ADORecordSet) {
					$result->Close ();
					$result = null;
				}
			}

			return true;
		}

	}
