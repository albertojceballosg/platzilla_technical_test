<?php
	require_once ('include/utils/CommonUtils.php');

	abstract class EditLabelsHelper {
		const EOL = "\n";

		private static function getModuleFilePath ($platform, $moduleName, $language) {
			$filePath = "{$platform}/modules/{$moduleName}/language/{$language}.lang.php";
			if (!isFileAccessible ($filePath)) {
				$filePath = "modules/{$moduleName}/language/{$language}.lang.php";
			}
			if (!isFileAccessible ($filePath)) {
				throw new Exception ('Imposible acceder al archivo de idioma');
			}
			return $filePath;
		}

		private static function getGlobalFilePath ($platform, $language) {
			$filePath = "{$platform}/include/language/{$language}.lang.php";
			if (!isFileAccessible ($filePath)) {
				$filePath = "include/language/{$language}.lang.php";
			}
			if ((!isFileAccessible ($filePath)) || (!is_readable ($filePath))) {
				throw new Exception ('Imposible acceder al archivo de idioma');
			}
			return $filePath;
		}

		private static function writeContents ($filePointer, $variableName, $variableValues) {
			fwrite ($filePointer, "{$variableName} = array (" . self::EOL);
			foreach ($variableValues as $key => $value) {
				if (is_array ($value)) {
					fwrite ($filePointer, "'{$key}' => array (" . self::EOL);
					foreach ($value as $keys => $values) {
						$values = addslashes ($values);
						fwrite ($filePointer, "'{$keys}' => '{$values}'," . self::EOL);
					}
					fwrite ($filePointer, '),' . self::EOL);
				} else {
					$value = addslashes ($value);
					fwrite ($filePointer, "'{$key}' => '{$value}'," . self::EOL);
				}
			}
			fwrite ($filePointer, ');' . self::EOL);
		}

		private static function writeModuleContents ($filePath, array $arguments, array $variables) {
			$fp = fopen ($filePath, 'w');
			if (!$fp) {
				throw new Exception ('Imposible acceder al archivo de idioma');
			}

			fwrite ($fp, '<?php' . self::EOL);
			fwrite ($fp, '$mod_strings = array (' . self::EOL);
			foreach ($arguments as $key => $value) {
				if ((!in_array ($key, $variables)) && (strpos ($key, 'label_') === false)) {
					$key   = "label_{$key}";
					$value = addslashes ($value);
					$line  = "'{$arguments [$key]}' => '{$value}'," . self::EOL;
					fwrite ($fp, $line);
				}
			}
			fwrite ($fp, ');' . self::EOL);
			fclose ($fp);
		}

		private static function writeGlobalContents ($filePath, array $arguments, array $variables) {
			require_once ($filePath);
			global $app_currency_strings, $app_list_strings;

			$fp = fopen ($filePath, 'w');
			if (!$fp) {
				throw new Exception ('Imposible acceder al archivo de idioma');
			}

			fwrite ($fp, '<?php' . self::EOL);
			fwrite ($fp, '$app_strings = array (' . self::EOL);
			foreach ($arguments as $key => $value) {
				if ((!in_array ($key, $variables)) && (strpos ($key, 'label_') === false) && (!empty ($key))) {
					$key   = "label_{$key}";
					$value = addslashes ($value);
					fwrite ($fp, "'{$arguments [$key]}' => '{$value}'," . self::EOL);
				}
			}
			fwrite ($fp, ');' . self::EOL . self::EOL);

			self::writeContents ($fp, 'app_list_strings', $app_list_strings);
			fwrite ($fp, self::EOL);
			self::writeContents ($fp, 'app_currency_strings', $app_currency_strings);
			fclose ($fp);
		}

		private static function writeModuleLanguageFile ($filePath, array $moduleStrings) {
			$fp = fopen ($filePath, 'w');
			if (!$fp) {
				throw new Exception ('Imposible acceder al archivo de idioma');
			}

			fwrite ($fp, '<?php' . self::EOL);
			fwrite ($fp, "\t\$mod_strings = array (" . self::EOL);
			foreach ($moduleStrings as $key => $value) {
				$value = addslashes ($value);
				$line  = "\t\t'{$key}' => '{$value}'," . self::EOL;
				fwrite ($fp, $line);
			}
			fwrite ($fp, "\t);" . self::EOL);
			fclose ($fp);
		}

		public static function writeLanguageFile ($platform, $moduleName, $language, array $arguments, array $variables) {
			if (!empty ($moduleName)) {
				$filePath = self::getModuleFilePath ($platform, $moduleName, $language);
				self::writeModuleContents ($filePath, $arguments, $variables);
			} else {
				$filePath = self::getGlobalFilePath ($platform, $language);
				self::writeGlobalContents ($filePath, $arguments, $variables);
			}
		}

	}
