<?php

	abstract class PlatformRelationshipsHelper {

		public static function getInstancesData (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					i.code,
					i.name
				FROM
					vtiger_instances i
					INNER JOIN vtiger_crmentity crme ON crme.crmid=i.instanceid AND crme.deleted=0
				ORDER BY
					i.name'
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$instancesData = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$instancesData [] = $row;
			}
			return $instancesData;
		}

		public static function getPlatformsData (PearDatabase $adb, $platform) {
			$result = $adb->query ("SELECT '{$platform}', organizationname FROM vtiger_organizationdetails");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$platformsData = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$platformsData [] = $row;
			}
			return $platformsData;
		}

	}
