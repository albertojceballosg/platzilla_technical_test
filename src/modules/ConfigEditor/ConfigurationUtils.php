<?php
	require_once 'include/utils/utils.php';

	class ConfigurationUtils {

		public static function getEntityModule () {
			global $adb;
			$unusedmodules     = array ('Events', 'Emails');
			$additionalModules = array ('Home');
			$query             = 'SELECT name FROM vtiger_tab WHERE isentitytype = 1';
			$res               = $adb->query ($query);
			$rows              = $adb->num_rows ($res);
			$module            = array ();
			for ($i = 0; $i < $rows; $i++) {
				$module[] = $adb->query_result ($res, $i, 'name');
			}
			return array_diff (array_merge ($module, $additionalModules), $unusedmodules);
		}

	}
