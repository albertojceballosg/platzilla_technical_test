<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class Translator {
		/** @var string[] */
		private static $APPLICATION_DICTIONARY = array ();

		/** @var string[] */
		private static $MODULE_DICTIONARY = array ();

		/**
		 * @return string[]|null
		 */
		public static function getApplicationDictionary () {
			global $current_language;
			if (empty ($current_language)) {
				return null;
			} else if (!empty (self::$APPLICATION_DICTIONARY [ $current_language ])) {
				return self::$APPLICATION_DICTIONARY [ $current_language ];
			}

			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			if (!file_exists ("{$rootFolderPath}/include/language/{$current_language}.lang.php")) {
				return null;
			}

			global $app_strings;
			require ("include/language/{$current_language}.lang.php");
			if (!isset ($app_strings)) {
				return null;
			} else {
				self::$APPLICATION_DICTIONARY [ $current_language ] = $app_strings;
				return $app_strings;
			}
		}

		/**
		 * @param string $moduleName
		 *
		 * @return string[]|null
		 */
		public static function getModuleDictionary ($moduleName) {
			global $current_language;
			if ((empty ($current_language)) || (empty ($moduleName))) {
				return null;
			} else if (!empty (self::$MODULE_DICTIONARY [ $current_language ][ $moduleName ])) {
				return self::$MODULE_DICTIONARY [ $current_language ] [ $moduleName ];
			}

			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			if (!file_exists ("{$rootFolderPath}/modules/{$moduleName}/language/{$current_language}.lang.php")) {
				return null;
			}

			global $mod_strings;
			require ("modules/{$moduleName}/language/{$current_language}.lang.php");
			if (!isset ($mod_strings)) {
				return null;
			} else {
				self::$MODULE_DICTIONARY [ $current_language ][ $moduleName ] = $mod_strings;
				return $mod_strings;
			}
		}

		/**
		 * @param string $string
		 * @param string $moduleName
		 *
		 * @return string
		 */
		public static function translate ($string, $moduleName = null) {
			global $currentModule;

			if (!empty ($moduleName)) {
				$moduleDictionary = self::getModuleDictionary ($moduleName);
			} else if (!empty ($currentModule)) {
				$moduleDictionary = self::getModuleDictionary ($currentModule);
			}

			if (isset ($moduleDictionary [ $string ])) {
				return $moduleDictionary [ $string ];
			} else {
				$applicationDictionary = self::getApplicationDictionary ();
				if (isset ($applicationDictionary [ $string ])) {
					return $applicationDictionary [ $string ];
				} else {
					return $string;
				}
			}
		}

		/**
		 * @param string $string
		 *
		 * @return string
		 */
		public static function translateFromApplicationDictionary ($string) {
			$applicationDictionary = self::getApplicationDictionary ();
			if (isset ($applicationDictionary [ $string ])) {
				return $applicationDictionary [ $string ];
			} else {
				return $string;
			}
		}

	}
