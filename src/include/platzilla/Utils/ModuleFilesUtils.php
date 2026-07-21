<?php
	require_once ('include/platzilla/Objects/Module.php');
	require_once ('include/platzilla/Utils/FileSystemUtils.php');

	abstract class ModuleFilesUtils {
		private static $LANGUAGE_FILE_NAMES = array ('de_de.lang.php', 'en_us.lang.php', 'es_es.lang.php', 'pt_br.lang.php');

		/**
		 * @param string $moduleName
		 * @param string $moduleLabel
		 * @param string $sourceFilePath
		 * @param string $targetFilePath
		 */
		private static function createLanguageFile ($moduleName, $moduleLabel, $sourceFilePath, $targetFilePath) {
			if ((!$sourceFilePath) || (!file_exists ($sourceFilePath)) || (!is_file ($sourceFilePath))) {
				return;
			}

			$contents = file_get_contents ($sourceFilePath);
			$contents = str_replace ("'ModuleName'", "'{$moduleName}'", $contents);
			$contents = str_replace ("'SINGLE_ModuleName'", "'SINGLE_{$moduleName}'", $contents);
			$contents = str_replace ("'Module Name'", "'{$moduleLabel}'", $contents);
			$contents = str_replace ("'ModuleName ID'", "'{$moduleName} ID'", $contents);
			$contents = str_replace ("'Module Name ID'", "'{$moduleLabel} ID'", $contents);

			file_put_contents ($targetFilePath, $contents);
		}

		/**
		 * @param Module $module
		 * @param string $sourceFilePath
		 * @param string $targetFilePath
		 */
		private static function createModuleFile ($module, $sourceFilePath, $targetFilePath) {
			$moduleName             = $module->getName ();
			$moduleEntityIdentifier = $module->getEntityIdentifier ();

			$lstFields            = array ();
			$lstFieldNames        = array ();
			$lstMandatoryFields   = array ();
			$lstSearchFields      = array ();
			$lstSearchFieldNames  = array ();
			$lstAlphabeticalField = '';
			$lstDefaultOrderField = '';
			$lstDetailField       = '';
			$lstLinkField         = '';
			$lstPopupField        = '';
			$lstRequiredField     = '';

			$fields = $module->getFields ();
			foreach ($fields as $field) {
				$fieldLabel = $field->getLabel ();
				$fieldName  = $field->getName ();

				$lstFields []     = "'{$fieldLabel}' => array ('{$moduleName}', '{$fieldName}')";
				$lstFieldNames [] = "'{$fieldLabel}' => '{$fieldName}'";
				if (
					((!empty ($moduleEntityIdentifier)) && ($fieldName == $moduleEntityIdentifier)) ||
					((empty ($moduleEntityIdentifier)) && (empty ($lstSearchFields)))
				) {
					$lstMandatoryFields []  = "'{$fieldName}'";
					$lstSearchFields []     = "'{$fieldLabel}' => array ('{$moduleName}', '{$fieldName}')";
					$lstSearchFieldNames [] = "'{$fieldLabel}' => '{$fieldName}'";
					$lstAlphabeticalField   = $fieldName;
					$lstDefaultOrderField   = $fieldName;
					$lstDetailField         = $fieldName;
					$lstLinkField           = $fieldName;
					$lstPopupField          = "array ('{$fieldName}')";
					$lstRequiredField       = "array ('{$fieldName}' => 1)";
				} else if ($field->isMandatory ()) {
					$lstRequiredField       = "array ('{$fieldName}' => 1)";
					$lstMandatoryFields []  = "'{$fieldName}'";
					$lstSearchFields []     = "'{$fieldLabel}' => array ('{$moduleName}', '{$fieldName}')";
					$lstSearchFieldNames [] = "'{$fieldLabel}' => '{$fieldName}'";
				}
			}

            $defaultListLink = 'cod_' . $moduleName;
			$contents = file_get_contents ($sourceFilePath);
			$contents = str_replace ('{$_MODULE_NAME}', $moduleName, $contents);
			$contents = str_replace ('{$_LST_FIELDS}', join (",\n\t\t\t", $lstFields), $contents);
			$contents = str_replace ('{$_LST_FIELDS_NAME}', join (",\n\t\t\t", $lstFieldNames), $contents);
			$contents = str_replace ('{$_LINK_NAME}', $lstLinkField, $contents);
            $contents = str_replace ('{$_DEFAULT_LIST_LINK}', $defaultListLink, $contents);
			$contents = str_replace ('{$_SEARCH_FIELDS}', join (",\n\t\t\t", $lstSearchFields), $contents);
			$contents = str_replace ('{$_SEARCH_FIELDS_NAME}', join (",\n\t\t\t", $lstSearchFieldNames), $contents);
			$contents = str_replace ('{$_POPUP_FIELDS}', $lstPopupField, $contents);
			$contents = str_replace ('{$_ALPHABETICAL_FIELD}', $lstAlphabeticalField, $contents);
			$contents = str_replace ('{$_DETAIL_FIELD}', $lstDetailField, $contents);
			$contents = str_replace ('{$_REQUIRED_FIELD}', $lstRequiredField, $contents);
			$contents = str_replace ('{$_ORDER_FIELD}', $lstDefaultOrderField, $contents);
			$contents = str_replace ('{$_MANDATORY_FIELDS}', join (', ', $lstMandatoryFields), $contents);
			file_put_contents ($targetFilePath, $contents);
		}

		/**
		 * @param string $moduleName
		 * @param string $moduleLabel
		 * @param string[] $moduleFileNames
		 * @param string $rootFolderPath
		 */
		private static function createModuleFiles ($moduleName, $moduleLabel, $moduleFileNames, $rootFolderPath) {
			$moduleFolderPath = "{$rootFolderPath}/modules/{$moduleName}";
			FileSystemUtils::deleteFolder ($moduleFolderPath);
			if (!is_dir ("{$moduleFolderPath}/language")) {
				$oldumask = umask (0);
				mkdir ("{$moduleFolderPath}/language", 0777, true);
				umask ($oldumask);
			}

			foreach ($moduleFileNames as $sourceFileName => $targetFileName) {
				$sourceFilePath = "{$rootFolderPath}/vtlib/ModuleDir/5.4.0/{$sourceFileName}";
				if ((!file_exists ($sourceFilePath)) || (!is_file ($sourceFilePath))) {
					continue;
				}
				copy ($sourceFilePath, "{$moduleFolderPath}/{$targetFileName}");
			}

			foreach (self::$LANGUAGE_FILE_NAMES as $languageFileName) {
				self::createLanguageFile ($moduleName, $moduleLabel, "{$rootFolderPath}/vtlib/ModuleDir/5.4.0/language/{$languageFileName}", "{$moduleFolderPath}/language/{$languageFileName}");
			}
		}

		/**
		 * @param Module $module
		 * @param string $rootFolderPath
		 */
		public static function createSimpleModuleFiles ($module, $rootFolderPath) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return;
			}

			$moduleName      = $module->getName ();
			$moduleLabel     = $module->getLabel ();
			$moduleFileNames = array ('indexSimple.php' => 'index.php');
			self::createModuleFiles ($moduleName, $moduleLabel, $moduleFileNames, $rootFolderPath);
		}

		/**
		 * @param Module $module
		 * @param string $rootFolderPath
		 */
		public static function createFieldsModuleFiles ($module, $rootFolderPath) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return;
			}

			$moduleName      = $module->getName ();
			$moduleLabel     = $module->getLabel ();
			$moduleFileNames = array (
				'AddComment.php'           => 'AddComment.php',
				'AjaxDetailViewUtils.php'  => 'AjaxDetailViewUtils.php',
				'AjaxEditViewUtils.php'    => 'AjaxEditViewUtils.php',
				'AjaxListViewUtils.php'    => 'AjaxListViewUtils.php',
				'AjaxTableFieldUtils.php'  => 'AjaxTableFieldUtils.php',
				'CalendarView.php'         => 'CalendarView.php',
				'CallRelatedList.php'      => 'CallRelatedList.php',
				'ChangeEntityOwner.php'    => 'ChangeEntityOwner.php',
				'CustomView.php'           => 'CustomView.php',
				'Delete.php'               => 'Delete.php',
				'DeleteAttachment.php'     => 'DeleteAttachment.php',
				'DetailView.php'           => 'DetailView.php',
				'DetailViewAjax.php'       => 'DetailViewAjax.php',
				'EditView.php'             => 'EditView.php',
				'ExportModule.php'         => 'ExportModule.php',
				'ExportRecords.php'        => 'ExportRecords.php',
				'FindDuplicateRecords.php' => 'FindDuplicateRecords.php',
				'Import.php'               => 'Import.php',
				'ImportModule.php'         => 'ImportModule.php',
				'index.php'                => 'index.php',
				'ListView.php'             => 'ListView.php',
				'ListViewPagging.php'      => 'ListViewPagging.php',
				'MassEdit.php'             => 'MassEdit.php',
				'MassEditSave.php'         => 'MassEditSave.php',
				'MassMail.php'             => 'MassMail.php',
				'MassMailSend.php'         => 'MassMailSend.php',
				'Modal.php'                => 'Modal.php',
				'ModuleFileAjax.php'       => "{$moduleName}Ajax.php",
				'ModuleFile.js'            => "{$moduleName}.js",
				'popupPatron.php'          => 'popupPatron.php',
				'PreviewVideo.php'         => 'PreviewVideo.php',
				'ProcessDuplicates.php'    => 'ProcessDuplicates.php',
				'QuickCreate.php'          => 'QuickCreate.php',
				'Save.php'                 => 'Save.php',
				'SaveChat.php'             => 'SaveChat.php',
				'SaveFromDetailView.php'   => 'SaveFromDetailView.php',
				'SaveFromListView.php'     => 'SaveFromListView.php',
				'Settings.php'             => 'Settings.php',
				'TagCloud.php'             => 'TagCloud.php',
				'UnifiedSearch.php'        => 'UnifiedSearch.php',
				'UpdateRelatedRecords.php' => 'UpdateRelatedRecords.php',
				'updateRelations.php'      => 'updateRelations.php',
				'UploadAttachment.php'     => 'UploadAttachment.php',
			);
			self::createModuleFiles ($moduleName, $moduleLabel, $moduleFileNames, $rootFolderPath);
			self::createModuleFile ($module, "{$rootFolderPath}/vtlib/ModuleDir/5.4.0/ModuleFile.php", "{$rootFolderPath}/modules/{$moduleName}/{$moduleName}.php");
		}

	}
