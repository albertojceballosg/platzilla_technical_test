<?php
	require_once ('include/mpdf/mpdf.php');

	abstract class PdfCreator {

		private static function getRepeatableFragments ($content) {
			if (!preg_match_all ('#\\{REPEAT \\$([a-zA-Z_][a-zA-Z_0-9]+)}(.*?)\\{/REPEAT\\}#s', $content, $matches)) {
				return null;
			}

			return array (
				'contents'  => $matches [0],
				'fragments' => $matches [2],
				'variables' => $matches [1],
			);
		}

		private static function getTemplateVariableNames ($template) {
			if (!preg_match_all ('#\\{\\$([a-zA-Z_][a-zA-Z_0-9]+)}#s', $template, $matches)) {
				return null;
			}
			return $matches [1];
		}

		private static function replaceRepeatableBlocks ($contents, $variables) {
			$repeatableBlocks = self::getRepeatableFragments ($contents);
			if (empty ($repeatableBlocks)) {
				return $contents;
			}

			$dummy = $contents;
			$n     = count ($repeatableBlocks ['contents']);
			for ($index = 0; $index < $n; $index++) {
				$repeatableContents = $repeatableBlocks ['contents'][ $index ];
				$repeatableVariable = $repeatableBlocks ['variables'][ $index ];
				$repeatableFragment = $repeatableBlocks ['fragments'][ $index ];
				if ((!empty ($variables [ $repeatableVariable ])) && (is_array ($variables [ $repeatableVariable ]))) {
					$anotherDummy = '';
					foreach ($variables [ $repeatableVariable ] as $fragmentVariables) {
						$anotherDummy .= self::replaceVariables ($repeatableFragment, $fragmentVariables);
					}
				} else {
					$anotherDummy = '';
				}
				$dummy = str_replace ($repeatableContents, $anotherDummy, $dummy);
			}
			return $dummy;
		}

		private static function replaceVariables ($templateContents, $variables) {
			if ((empty ($templateContents)) || (empty ($variables))) {
				return $templateContents;
			}

			$contents              = $templateContents;
			$templateVariableNames = self::getTemplateVariableNames ($contents);
			if ((!empty ($templateVariableNames)) && (is_array ($templateVariableNames))) {
				foreach ($templateVariableNames as $templateVariableName) {
					if ((!isset ($variables [ $templateVariableName ])) || (!is_scalar ($variables [ $templateVariableName ]))) {
						continue;
					}

					$contents = str_replace ('{$' . $templateVariableName . '}', $variables [ $templateVariableName ], $contents);
				}
			}
			return $contents;
		}

		public static function createFromHtmlTemplate ($templateFileName, $variables = null, $pageSize = 'Letter', $pageOrientation = 'P', $fileName = null) {
			if (empty ($templateFileName)) {
				throw new Exception ('ERROR_EMPTY_TEMPLATE_FILE_NAME');
			} else if (!file_exists ($templateFileName)) {
				throw new Exception ('ERROR_EMPTY_TEMPLATE_FILE_NOT_FOUND');
			}

			$templateContents = file_get_contents ($templateFileName);
			$fileContents     = self::replaceRepeatableBlocks ($templateContents, $variables);
			$fileContents     = self::replaceVariables ($fileContents, $variables);
			$format           = $pageSize . ($pageOrientation != 'P' ? "-{$pageOrientation}" : '');
			$mpdf             = new mPDF ('utf-8', $format, null, null, 0, 0, 0, 0, 0, 0);
			$mpdf->WriteHTML ($fileContents);
			if (!empty ($fileName)) {
				return $mpdf->Output ($fileName, 'F');
			} else {
				return $mpdf->Output ('', 'S');
			}
		}

	}
