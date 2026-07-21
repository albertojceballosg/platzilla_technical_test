<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('include/utils/VtlibUtils.php');

	abstract class SettingsUtils {
		
		const ADVANCED_OPTIONS = array (
			'LBL_PROFILES',
			'LBL_ROLES',
			'LBL_SHARING_ACCESS',
			'LBL_INSTANCES_DATA_SHARING_NAME',
		);
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $tabId
		 *
		 * @return array|null
		 */
		private static function fetchSettingsBlocks (PearDatabase $adb, $tabId) {
			$result = $adb->pquery ('SELECT f.* FROM vtiger_settings_field f WHERE f.blockid=? AND f.active=0 ORDER BY f.sequence', array ($tabId));
			if ($adb->num_rows ($result) > 0) {
				$blocks = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['tab'])) {
						$innerTab = $row ['tab'];
					} else {
						$innerTab = 0;
						$row ['name'] = Translator::translate ($row ['name'], $row ['name']);
					}
					$blocks [$innerTab][] = $row;
				}
				uksort ($blocks, 'strcmp');
			} else {
				$blocks = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $blocks;
		}

		public static function purify ($variable, $index, $returnValueIfNotSet = null) {
			if (
				(!isset ($variable)) ||
				((is_array ($variable)) && ((empty ($index)) || (!isset ($variable [$index]))))
			) {
				return $returnValueIfNotSet;
			}
			return vtlib_purify ($variable [$index]);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchSettingsTabs (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_settings_blocks ORDER BY sequence');
			if ($adb->num_rows ($result) > 0) {
				$tabs = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$blocks = self::fetchSettingsBlocks ($adb, $row ['blockid']);
					if (empty ($blocks)) {
						continue;
					}
					$innerTabs = array_keys ($blocks);
					$tabs []   = array (
						'id'        => $row ['blockid'],
						'innerTabs' => $innerTabs,
						'label'     => $row ['label'],
						'blocks'    => $blocks,
					);
				}
			} else {
				$tabs = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tabs;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function checkAdvancedOptions ($adb) {
			$dummyName = $adb->sql_expr_datalist (self::ADVANCED_OPTIONS);
			$result    = $adb->query ("SELECT fieldid FROM vtiger_settings_field  WHERE name IN {$dummyName} AND active=0");
			return ($adb->num_rows ($result) > 0);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $status
		 *
		 * @throws Exception
		 */
		public static function setAdvancedOptions ($adb, $status) {
			$dummyName = $adb->sql_expr_datalist (self::ADVANCED_OPTIONS);
			$adb->query ("UPDATE vtiger_settings_field SET active = {$status} WHERE NAME IN {$dummyName}");
		}
		
	}
