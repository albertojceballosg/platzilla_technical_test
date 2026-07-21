<?php

	class ConfigEditor {

		private function getSequence (PearDatabase $adb, $blockID) {
			$sql        = 'SELECT MAX(sequence) AS sequence FROM vtiger_settings_field WHERE blockid=?';
			$parameters = array ($blockID ? $blockID : 1);
			$result     = $adb->pquery ($sql, $parameters);
			return (intval ($adb->query_result ($result, 0, 'sequence')) + 1);
		}

		/**
		 * Invoked when special actions are performed on the module.
		 *
		 * @param String Module name
		 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
		 */
		public function vtlib_handler ($moduleName, $eventType) {
			if (($eventType == 'module.preuninstall') || ($eventType == 'module.preupdate') || ($eventType == 'module.postupdate')) {
				return;
			}
			$registerLink = ($eventType == 'module.postinstall') || ($eventType == 'module.enabled') ? true : false;
			$displayLabel = 'Configuration Editor';
			global $adb;
			if ($registerLink) {
				$blockID    = getSettingsBlockId ('LBL_OTHER_SETTINGS');
				$sequence   = $this->getSequence ($adb, $blockID);
				$fieldid    = $adb->getUniqueId ('vtiger_settings_field');
				$sql        = 'INSERT INTO vtiger_settings_field (fieldid, blockid, sequence, name, iconpath, description, linkto) VALUES (?, ?, ?, ?, ?, ?, ?)';
				$parameters = array (
					$fieldid,
					$blockID ? $blockID : 1,
					$sequence,
					$displayLabel,
					'migrate.gif',
					'Update configuration file of the application',
					'index.php?module=ConfigEditor&action=index',
				);
				$adb->pquery ($sql, $parameters);
			} else {
				$adb->pquery ('DELETE FROM vtiger_settings_field WHERE name=?', array ($displayLabel));
			}
		}

	}
