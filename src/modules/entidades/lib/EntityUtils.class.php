<?php

	abstract class EntityUtils {

		public static function getEntityById (PearDatabase $adb, $entityId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_entidades WHERE entidadesid=?', array ($entityId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

	}
