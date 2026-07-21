<?php
	abstract class CodeVerificationHelper {

		public static function getInstanceData (PearDatabase $adb, $instanceName) {
			$result = $adb->pquery (
				'SELECT
					i.*,
					c.nombre AS firstname,
					c.apellidos AS lastname,
					i.administrator AS email
				FROM
					vtiger_instances i
					INNER JOIN vtiger_contactos c ON c.email=i.administrator
				WHERE
					i.code=?
				LIMIT 1',
				array ($instanceName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		public static function isValidVerificationCode (PearDatabase $adb, $instanceName, $verificationCode) {
			$result = $adb->pquery ('SELECT * FROM vtiger_instances WHERE code=? AND verificationcode=?', array ($instanceName, $verificationCode));
			return ($result) && ($adb->num_rows ($result) > 0);
		}

		public static function markInstanceAsVerified (PearDatabase $adb, $instanceName) {
			$adb->pquery ("UPDATE vtiger_instances SET status='verified' WHERE code=?", array ($instanceName));
		}

	}
