<?php

	abstract class AddBlockHelper {

		public static function getBlockLabel (PearDatabase $adb, $blockId) {
			if (!$blockId) {
				return '';
			}
			$result = $adb->pquery ('SELECT blocklabel FROM vtiger_blocks WHERE blockid=?', array ($blockId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['blocklabel'];
		}

		public static function getModuleBlocks (PearDatabase $adb, $module, $tabId) {
			$result = $adb->pquery ('SELECT blocklabel, blockid FROM vtiger_blocks WHERE tabid=?', array ($tabId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$blocks = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$blocks [] = array (
					'blocklabel' => getTranslatedString ($row ['blocklabel'], $module),
					'blockid'    => $row ['blockid'],
				);
			}
			return $blocks;
		}

	}
