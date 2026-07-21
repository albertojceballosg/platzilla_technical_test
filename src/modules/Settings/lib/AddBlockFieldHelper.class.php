<?php

	abstract class AddBlockFieldHelper {

		public static function getModuleFields (PearDatabase $adb, $module, $tabId, $blockId) {
			$sql    = "SELECT
							fieldid,
							fieldlabel,
							fieldname
						FROM
							vtiger_field
						WHERE
							tabid=? AND
							block<>? AND
							block NOT IN (SELECT blockid FROM vtiger_blocks WHERE blocklabel='LBL_RELATED_PRODUCTS') AND
							displaytype IN (1, 2, 4) AND
							presence IN (0, 2)
						ORDER BY
							fieldlabel ASC";
			$result = $adb->pquery ($sql, array ($tabId, $blockId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$fields [] = array (
					'fieldid'    => $row ['fieldid'],
					'fieldlabel' => getTranslatedString ($row ['fieldlabel'], $module),
					'fieldname'  => $row ['fieldname'],
				);
			}
			return $fields;
		}

	}
