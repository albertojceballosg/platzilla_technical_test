<?php

	abstract class CategoryApplicationsHelper {

		private static function getAssociatedApplications (PearDatabase $adb, $categoryId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_category=?', array ($categoryId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$applications [] = $row;
			}
			return $applications;
		}

		public static function getApplicationCategories (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_category_apps');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$categories = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$row ['appsAsociadas'] = self::getAssociatedApplications ($adb, $row ['catappid']);
				$categories [] = $row;
			}
			return $categories;
		}

	}
