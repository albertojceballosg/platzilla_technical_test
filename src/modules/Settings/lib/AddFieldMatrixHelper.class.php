<?php
	require_once ('include/utils/CommonUtils.php');

	abstract class AddFieldMatrixHelper {

		public static function updateFields (PearDatabase $adb, $module, $name, $label, $rows, $cols) {
			$result = $adb->query ('SELECT id FROM vtiger_field_seq');
			if (!$result) {
				return;
			}
			if (($adb->num_rows ($result) == 0)) {
				$adb->query ('INSERT INTO vtiger_field_seq (id) VALUES (1)');
				$id = 1;
			} else {
				$id = ($adb->query_result ($result, 0, 'id') + 1);
				$adb->pquery ('UPDATE vtiger_field_seq SET id=?', array ($id));
			}

			$tabid = getTabid ($module);
			$adb->pquery (
				'INSERT INTO vtiger_field (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable, helpinfo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)',
				array ($tabid, $id, $name, "vtiger_{$module}", '1', '2302', $name, $label, '1', '-1', "$rows/$cols", '100', '2', '-1', '1', 'V~O', '1', 'BAS', '1', '')
			);
			$adb->query (
				"CREATE TABLE IF NOT EXISTS vtiger_{$module}_{$name} (
					{$name}id INT(11) NOT NULL AUTO_INCREMENT,
					{$module}id INT(11) NOT NULL,
					{$name} VARCHAR(255) NULL,
					PRIMARY KEY ({$name}id),
					KEY {$module}id ({$module}id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;"
			);
		}

	}
