<?php

	abstract class DeleteCustomFieldHelper {

		public static function deleteField (PearDatabase $adb, $fieldId, $fieldModuleName, $fieldColumnName, $fieldUiType) {
			$result = $adb->pquery ('SELECT tablename FROM vtiger_field WHERE fieldid=?', array ($fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$row       = $adb->fetchByAssoc ($result, -1, false);
			$tableName = $row ['tablename'];

			// Deleting the CustomField from the Custom Field Table
			$adb->pquery ('DELETE FROM vtiger_field WHERE fieldid=?', array ($fieldId));

			// Deleting from vtiger_profile2field table
			$adb->pquery ('DELETE FROM vtiger_profile2field WHERE fieldid=?', array ($fieldId));

			// Deleting from vtiger_def_org_field table
			$adb->pquery ('DELETE FROM vtiger_def_org_field WHERE fieldid=?', array ($fieldId));

			// vtlib customization: Hook added to allow action for custom modules too
			$adb->query ("ALTER TABLE {$tableName} DROP COLUMN {$adb->sql_escape_string ($fieldColumnName)}");

			// To remove customfield entry from vtiger_field table
			$adb->pquery ('DELETE FROM vtiger_field WHERE tablename=? AND fieldname=?', array ($tableName, $fieldColumnName));

			// we have to remove the entries in customview and report related tables which have this field ($colName)
			$adb->pquery ('DELETE FROM vtiger_cvcolumnlist WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_cvstdfilter WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_relcriteria WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datecolumnname LIKE ?', array ("%{$fieldColumnName}%"));
			$adb->pquery ('DELETE FROM vtiger_reportsummary WHERE columnname LIKE ?', array ("%{$fieldColumnName}%"));

			// HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
			if ($fieldUiType == 15) {
				$adb->query ("DROP TABLE vtiger_{$adb->sql_escape_string ($fieldColumnName)}");
			}
		}

	}
