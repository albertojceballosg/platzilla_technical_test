<?php
	require_once ('include/utils/CommonUtils.php');

	abstract class AddBlockToDatabaseHelper {

		public static function isBlockLabelRegistered (PearDatabase $adb, $tabId, $label) {
			if (!$tabId) {
				return false;
			}
			$result = $adb->pquery ('SELECT blocklabel FROM vtiger_blocks WHERE tabid=?', array ($tabId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return false;
			}
			while ($row = $adb->fetchByAssoc ($result)) {
				$registeredLabel = getTranslatedString ($row ['blocklabel']);
				if ($label == $registeredLabel) {
					return true;
				}
			}
			return false;
		}

		public static function registerBlock (PearDatabase $adb, $tabId, $label, $afterBlockId) {
			$result   = $adb->pquery ('SELECT IFNULL(sequence, 0) AS sequence FROM vtiger_blocks WHERE blockid=? LIMIT 1', array ($afterBlockId));
			$row      = $adb->fetchByAssoc ($result);
			$sequence = $row ['sequence'];

			$adb->pquery ('UPDATE vtiger_blocks SET sequence=sequence+1 WHERE tabid=? AND sequence>?', array ($tabId, $sequence));

			$result = $adb->query ('SELECT MAX(blockid) AS maxid FROM vtiger_blocks');
			$row    = $adb->fetchByAssoc ($result);
			$maxId  = ($row ['maxid'] + 1);

			$adb->pquery (
				'INSERT INTO vtiger_blocks (tabid, blockid, sequence, blocklabel) VALUES (?, ?, ?, ?)',
				array ($tabId, $maxId, $sequence, $label)
			);
		}

	}
