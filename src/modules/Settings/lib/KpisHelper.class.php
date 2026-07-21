<?php

	abstract class KpisHelper {

		public static function getVisibleModulesData (PearDatabase $adb, $keyword = null) {
			$sql = 'SELECT
						t.name,
						t.tabid,
						t.tablabel
					FROM
						vtiger_tab t
						INNER JOIN vtiger_entityname e ON e.modulename=t.name
					WHERE
						t.isentitytype=1 AND
						t.presence IN (0, 2) AND
						t.customized IN (0, 1, 2)';
			if ($keyword) {
				$sql .= ' AND t.name=?';
				$result = $adb->pquery ($sql, array ($keyword));
			} else {
				$result = $adb->query ($sql);
			}
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			return $modules;
		}

	}
