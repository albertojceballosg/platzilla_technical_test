<?php
	require_once ('include/platzilla/Managers/FieldProfileManager.php');
	require_once ('include/platzilla/Managers/ModuleProfileManager.php');
	require_once ('include/platzilla/Objects/Module.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/HtmlGenerator.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	abstract class ProfileHelper {
		private static $PERMISSION_IMAGE = 0;
		private static $PERMISSION_FIELD = 1;

		private static function renderVersionOneHtmlPermissionField ($id, $actionId) {
			$actionName = $actionId == '1' ? 'view_all' : 'edit_all';

			$html = '';
			if ($id === '') {
				$html = '';
			} else if ($id == 0) {
				$html = "<input type=\"checkbox\" id=\"{$actionName}_chk\" onClick=\"invoke{$actionName} ();\" name=\"{$actionName}\" checked=\"checked\" />";
			} else if ($id == 1) {
				$html = "<input type=\"checkbox\" id=\"{$actionName}_chk\" onClick=\"invoke{$actionName} ();\" name=\"{$actionName}\" />";
			}
			return $html;
		}

		private static function renderVersionTwoHtmlPermissionField ($id, $moduleId, $actionId) {
			if ($actionId == '') {
				$name           = "{$moduleId}_tab";
				$checkboxId     = "tab_chk_com_{$moduleId}";
				$javascriptCode = "hideTab ({$moduleId})";
			} else {
				$dummy      = getActionname ($actionId);
				$name       = "{$moduleId}_{$dummy}";
				$checkboxId = "{tab_chk_{$actionId}_{$moduleId}";
				if ($actionId == 1) {
					$javascriptCode = "unSelectCreate ({$moduleId})";
				} else if ($actionId == 4) {
					$javascriptCode = "unSelectView ({$moduleId})";
				} else if ($actionId == 2) {
					$javascriptCode = "unSelectDelete ({$moduleId})";
				} else {
					$checkboxId     = "{$moduleId}_field_util_{$actionId}";
					$javascriptCode = 'javascript:';
				}
			}

			$html = '';
			if (($id == '') && ($id != 0)) {
				$html = '';
			} else if ($id == 0) {
				$html = "<input type=\"checkbox\" onClick=\"{$javascriptCode};\" id=\"{$checkboxId}\" name=\"{$name}\" checked=\"checked\">";
			} else if ($id == 1) {
				$html = "<input type=\"checkbox\" onClick=\"{$javascriptCode};\" id=\"{$checkboxId}\" name=\"{$name}\">";
			}
			return $html;
		}

		private static function renderHtmlPermissionImage ($id, $theme) {
			if ($id === 0) {
				return HtmlGenerator::renderImage (vtiger_imageurl ('prvPrfSelectedTick.gif', $theme));
			} else if ($id === 1) {
				return HtmlGenerator::renderImage (vtiger_imageurl ('no.gif', $theme));
			} else {
				return '&nbsp;';
			}
		}

		private static function getDisabledFields (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_def_org_field');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$disabledFields = array ();
			while ($row = $adb->fetch_array ($result)) {
				$disabledFields [ $row ['fieldid'] ] = $row ['visible'];
			}
			return $disabledFields;
		}

		private static function getModulePrivilegesByProfileId (PearDatabase $adb, $theme, $profileId, $permissionVersion, $addFirstModule = false) {
			$modulePrivileges = array ();
			/** @var array $modulePermissions */
			$modulePermissions = getTabsPermission ($profileId);
			$moduleIds         = array_keys ($modulePermissions);
			foreach ($moduleIds as $moduleId) {
				$result = $adb->pquery (
					"SELECT
						COALESCE (pt.parenttab_label, 'Otros') AS parenttab_label
					FROM
						vtiger_parenttab pt
						INNER JOIN vtiger_parenttabrel ptr ON ptr.parenttabid=pt.parenttabid
						RIGHT JOIN vtiger_tab t ON t.tabid=ptr.tabid
					WHERE
						 t.tabid=?",
					array ($moduleId)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					continue;
				}

				$firstModuleName = null;
				$moduleName      = getTabname ($moduleId);
				while ($row = $adb->fetchByAssoc ($result)) {
					if ($permissionVersion == self::$PERMISSION_IMAGE) {
						$renderedPermission = self::renderHtmlPermissionImage ($modulePermissions [ $moduleId ], $theme);
					} else if ($profileId == 1) {
						$renderedPermission = self::renderVersionTwoHtmlPermissionField (1, $moduleId, '');
					} else {
						$renderedPermission = self::renderVersionTwoHtmlPermissionField ($modulePermissions [ $moduleId ], $moduleId, '');
					}

					$modulePrivilege = array (
						$moduleName,
						$renderedPermission,
						getTranslatedString ($row ['parenttab_label']),
					);

					if ($addFirstModule) {
						if ($firstModuleName === null) {
							$firstModuleName    = getTranslatedString ($row ['parenttab_label']);
							$modulePrivilege [] = $moduleId;
						} else {
							$modulePrivilege [] = "{$moduleId}b";
							$modulePrivilege [] = $firstModuleName;
						}
					} else {
						$modulePrivilege [] = $moduleId;
					}

					$modulePrivileges [] = $modulePrivilege;
				}
			}
			return $modulePrivileges;
		}

		private static function getPrivilegeFieldsForEditMode ($profileId, $moduleNames, $disabledFields, $language) {
			$privilegeFields = array ();
			$fieldsData      = getProfile2AllFieldList ($moduleNames, $profileId);
			foreach ($fieldsData as $moduleName => $fieldData) {
				$fields     = array ();
				$moduleId   = getTabid ($moduleName);
				$dictionary = return_module_language ($language, $moduleName);
				foreach ($fieldData as $values) {
					$label      = $values [0];
					$typeOfData = $values [6];
					$fieldType  = explode ('~', $typeOfData);
					$mandatory  = '';
					$disabled   = '';
					if ($values [1] == 0) {
						$visible = 'checked';
					} else {
						$visible = '';
					}
					if ($fieldType [1] == 'M') {
						$mandatory = '<span style="color: red">*</span>';
						$disabled  = 'disabled';
						$visible   = 'checked';
					}
					if ($disabledFields [ $values [4] ] == 1) {
						$mandatory = '<span style="color: blue">*</span>';
						$disabled  = 'disabled';
						$visible   = '';
					}

					$fields [] = array (
						$dictionary [ $label ] != '' ? "{$mandatory} {$dictionary [ $label ]}" : "{$mandatory} {$label}",
						trim ("<input id=\"{$moduleId}_field_{$values [4]}\" onClick=\"selectUnselect (this);\" type=\"checkbox\" name=\"{$values [4]}\" {$visible} {$disabled} />"),
					);
				}
				$privilegeFields [ $moduleId ] = array_chunk ($fields, 3);
			}
			return $privilegeFields;
		}

		private static function getPrivilegeFieldsForMainProfileId ($moduleNames, $disabledFields, $language) {
			$privilegeFields = array ();
			$fieldsData      = getProfile2AllFieldList ($moduleNames, 1);
			foreach ($fieldsData as $moduleName => $fieldData) {
				$fields     = array ();
				$moduleId   = getTabid ($moduleName);
				$dictionary = return_module_language ($language, $moduleName);
				foreach ($fieldData as $values) {
					$label      = $values [0];
					$typeOfData = $values [6];
					$fieldType  = explode ('~', $typeOfData);
					$mandatory  = '';
					$disabled   = '';
					if ($fieldType [1] == 'M') {
						$mandatory = '<span style="color: red">*</span>';
						$disabled  = 'disabled';
					}
					if ($disabledFields [ $values [4] ] == 1) {
						$mandatory = '<span style="color: blue">*</span>';
						$disabled  = 'disabled';
						$visible   = '';
					} else {
						$visible = 'checked';
					}
					$fields [] = array (
						$dictionary [ $label ] != '' ? "{$mandatory} {$dictionary [ $label ]}" : "{$mandatory} {$label}",
						trim ("<input id=\"{$moduleId}_field_{$values [4]}\" onClick=\"selectUnselect (this);\" type=\"checkbox\" name=\"{$values [4]}\" {$visible} {$disabled} />"),
					);
				}
				$privilegeFields [ $moduleId ] = array_chunk ($fields, 3);
			}
			return $privilegeFields;
		}

		private static function getPrivilegeFieldsForParentProfileId ($profileId, $moduleNames, $disabledFields, $language) {
			$privilegeFields = array ();
			$fieldsData      = getProfile2AllFieldList ($moduleNames, $profileId);
			foreach ($fieldsData as $moduleName => $fieldData) {
				$fields     = array ();
				$moduleId   = getTabid ($moduleName);
				$dictionary = return_module_language ($language, $moduleName);
				foreach ($fieldData as $values) {
					$label      = $values [0];
					$typeOfData = $values [6];
					$fieldType  = explode ('~', $typeOfData);
					$mandatory  = '';
					$disabled   = '';
					if ($fieldType [1] == 'M') {
						$mandatory = '<span style="color: red">*</span>';
						$disabled  = 'disabled';
					}
					if ($values [3] == 0) {
						$visible = 'checked';
					} else {
						$visible = '';
					}
					if ($disabledFields [ $values [4] ] == 1) {
						$mandatory = '<span style="color: blue">*</span>';
						$disabled  = 'disabled';
						$visible   = '';
					}

					$fields [] = array (
						$dictionary [ $label ] != '' ? "{$mandatory} {$dictionary [ $label ]}" : "{$mandatory} {$label}",
						trim ("<input id=\"{$moduleId}_field_{$values [4]}\" onClick=\"selectUnselect (this);\" type=\"checkbox\" name=\"{$values [4]}\" {$visible} {$disabled} />"),
					);
				}
				$privilegeFields [ $moduleId ] = array_chunk ($fields, 3);
			}
			return $privilegeFields;
		}

		private static function getPrivilegeFieldsForViewMode ($profileId, $moduleNames, $disabledFields, $language, $theme) {
			$privilegeFields = array ();
			$fieldsData      = getProfile2AllFieldList ($moduleNames, $profileId);
			foreach ($fieldsData as $moduleName => $fieldData) {
				$fields     = array ();
				$moduleId   = getTabid ($moduleName);
				$dictionary = return_module_language ($language, $moduleName);
				foreach ($fieldData as $values) {
					$imageFileName = ($disabledFields [ $values [4] ] == 1) || ($values [1] != 0) ? 'no.gif' : 'prvPrfSelectedTick.gif';
					$imageUrl      = vtiger_imageurl ($imageFileName, $theme);
					$fields []     = array (
						$dictionary [ $values [0] ] != '' ? $dictionary [ $values [0] ] : $values [0],
						"<img src=\"{$imageUrl}\" />",
					);
				}
				$privilegeFields [ $moduleId ] = array_chunk ($fields, 3);
			}
			return $privilegeFields;
		}

		private static function getStandardPrivilegesByProfileId ($theme, $profileId, $permissionVersion) {
			$standardPrivileges = array ();
			$actionPermissions  = getTabsActionPermission ($profileId);
			foreach ($actionPermissions as $moduleId => $actions) {
				$moduleName = getTabname ($moduleId);
				if ($permissionVersion == self::$PERMISSION_IMAGE) {
					$createEditPermission = self::renderHtmlPermissionImage ($actions ['1'], $theme);
					$deletePermission     = self::renderHtmlPermissionImage ($actions ['2'], $theme);
					$viewPermission       = self::renderHtmlPermissionImage ($actions ['4'], $theme);
				} else {
					$createEditPermission = self::renderVersionTwoHtmlPermissionField ($actions ['1'], $moduleId, '1');
					$deletePermission     = self::renderVersionTwoHtmlPermissionField ($actions ['2'], $moduleId, '2');
					$viewPermission       = self::renderVersionTwoHtmlPermissionField ($actions ['4'], $moduleId, '4');
				}
				$standardPrivileges [] = array (
					$moduleName,
					$createEditPermission,
					$deletePermission,
					$viewPermission,
				);
			}
			return $standardPrivileges;
		}

		private static function getUtilitiesPrivilegesByProfileId ($theme, $profileId, $permissionVersion) {
			$utilitiesPrivileges = array ();
			$utilityPermissions  = getTabsUtilityActionPermission ($profileId);
			foreach ($utilityPermissions as $moduleId => $actions) {
				$utilityPrivileges = array ();
				foreach ($actions as $actionId => $action) {
					if ($permissionVersion == self::$PERMISSION_IMAGE) {
						$permission = self::renderHtmlPermissionImage ($action, $theme);
					} else {
						$permission = self::renderVersionTwoHtmlPermissionField ($profileId !== 1 ? $action : 0, $moduleId, $actionId);
					}
					$utilityPrivileges [] = getActionname ($actionId);
					$utilityPrivileges [] = $permission;
				}
				$utilitiesPrivileges [ $moduleId ] = array_chunk (array_chunk ($utilityPrivileges, 2), 3);
			}
			return $utilitiesPrivileges;
		}

		public static function getGlobalPrivileges ($theme, $mode, $profileId, $parentProfileId) {
			$globalPrivileges = array ();
			if ($mode == 'view') {
				$globalPermissions = getProfileGlobalPermission ($profileId);
				$globalPrivileges  = array (
					self::renderHtmlPermissionImage ($globalPermissions [1], $theme),
					self::renderHtmlPermissionImage ($globalPermissions [2], $theme),
				);
			} else if ($mode == 'edit') {
				$globalPermissions = getProfileGlobalPermission ($profileId);
				$globalPrivileges  = array (
					self::renderVersionOneHtmlPermissionField ($globalPermissions [1], 1),
					self::renderVersionOneHtmlPermissionField ($globalPermissions [2], 2),
				);
			} else if ($mode == 'create') {
				if ($parentProfileId != '') {
					$globalPermissions = getProfileGlobalPermission ($parentProfileId);
					$globalPrivileges  = array (
						self::renderVersionOneHtmlPermissionField ($globalPermissions [1], 1),
						self::renderVersionOneHtmlPermissionField ($globalPermissions [2], 2),
					);
				} else {
					$globalPrivileges = array (
						self::renderVersionOneHtmlPermissionField (1, 1),
						self::renderVersionOneHtmlPermissionField (1, 2),
					);
				}
			}
			return $globalPrivileges;
		}

		public static function getModulePrivileges (PearDatabase $adb, $theme, $mode, $profileId, $parentProfileId) {
			$modulePrivileges = array ();
			if ($mode == 'view') {
				$modulePrivileges = self::getModulePrivilegesByProfileId ($adb, $theme, $profileId, self::$PERMISSION_IMAGE);
			} else if ($mode == 'edit') {
				$modulePrivileges = self::getModulePrivilegesByProfileId ($adb, $theme, $profileId, self::$PERMISSION_FIELD, true);
			} else if (($mode == 'create') && ($parentProfileId != '')) {
				$modulePrivileges = self::getModulePrivilegesByProfileId ($adb, $theme, $parentProfileId, self::$PERMISSION_FIELD);
			} else if (($mode == 'create') && ($parentProfileId == '')) {
				$modulePrivileges = self::getModulePrivilegesByProfileId ($adb, $theme, 1, self::$PERMISSION_FIELD);
			}

			$date = array ();
			$time = array ();
			foreach ($modulePrivileges as $key => $modulePrivilege) {
				$date [ $key ] = strtolower ($modulePrivilege ['2']);
				$time [ $key ] = strtolower ($modulePrivilege ['0']);
			}
			array_multisort ($date, SORT_ASC, $time, SORT_ASC, $modulePrivileges);
			return $modulePrivileges;
		}

		public static function getParentModuleName (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery (
				'SELECT
					pt.parenttab_label
				FROM
					vtiger_parenttab pt
					INNER JOIN vtiger_parenttabrel ptr ON ptr.parenttabid=pt.parenttabid
					INNER JOIN vtiger_tab t ON t.tabid=ptr.tabid
				WHERE
					t.tabid=?',
				array ($moduleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['parenttab_label'];
		}

		public static function getPrivilegeFields (PearDatabase $adb, $theme, $language, $mode, $profileId, $parentProfileId) {
			$privilegeFields = array ();
			$moduleNames     = getFieldModuleAccessArray ();
			$disabledFields  = self::getDisabledFields ($adb);

			if ($mode == 'view') {
				$privilegeFields = self::getPrivilegeFieldsForViewMode ($profileId, $moduleNames, $disabledFields, $language, $theme);
			} else if ($mode == 'edit') {
				$privilegeFields = self::getPrivilegeFieldsForEditMode ($profileId, $moduleNames, $disabledFields, $language);
			} else if (($mode == 'create') && ($parentProfileId != '')) {
				$privilegeFields = self::getPrivilegeFieldsForParentProfileId ($parentProfileId, $moduleNames, $disabledFields, $language);
			} else if ($mode == 'create') {
				$privilegeFields = self::getPrivilegeFieldsForMainProfileId ($moduleNames, $disabledFields, $language);
			}
			return $privilegeFields;
		}

		public static function getStandardPrivileges ($theme, $mode, $profileId, $parentProfileId) {
			$standardPrivileges = array ();
			if ($mode == 'view') {
				$standardPrivileges = self::getStandardPrivilegesByProfileId ($theme, $profileId, self::$PERMISSION_IMAGE);
			} else if ($mode == 'edit') {
				$standardPrivileges = self::getStandardPrivilegesByProfileId ($theme, $profileId, self::$PERMISSION_FIELD);
			} else if (($mode == 'create') && ($parentProfileId != '')) {
				$standardPrivileges = self::getStandardPrivilegesByProfileId ($theme, $parentProfileId, self::$PERMISSION_FIELD);
			} else if (($mode == 'create') && ($parentProfileId == '')) {
				$standardPrivileges = self::getStandardPrivilegesByProfileId ($theme, 1, self::$PERMISSION_FIELD);
			}
			return $standardPrivileges;
		}

		public static function getUtilitiesPrivileges ($theme, $mode, $profileId, $parentProfileId) {
			$utilitiesPrivileges = array ();
			if ($mode == 'view') {
				$utilitiesPrivileges = self::getUtilitiesPrivilegesByProfileId ($theme, $profileId, self::$PERMISSION_IMAGE);
			} else if ($mode == 'edit') {
				$utilitiesPrivileges = self::getUtilitiesPrivilegesByProfileId ($theme, $profileId, self::$PERMISSION_FIELD);
			} else if (($mode == 'create') && ($parentProfileId != '')) {
				$utilitiesPrivileges = self::getUtilitiesPrivilegesByProfileId ($theme, $parentProfileId, self::$PERMISSION_FIELD);
			} else if ($mode == 'create') {
				$utilitiesPrivileges = self::getUtilitiesPrivilegesByProfileId ($theme, 1, self::$PERMISSION_FIELD);
			}
			return $utilitiesPrivileges;
		}

		// Nuevas funciones

		/**
		 * @param PearDatabase $adb
		 * @param Profile $profile
		 * @param Module $module
		 * @param string[] $permissions
		 *
		 * @return ModuleProfile
		 */
		public static function getModuleProfile (PearDatabase $adb, $profile, $module, $permissions) {
			$moduleProfile = ModuleProfileManager::getInstance ($adb)->fetchProfileByProfileName ($profile->getName (), $module->getName ());
			if (empty ($moduleProfile)) {
				$moduleProfile = ModuleProfile::getInstance ()
					->setModuleName ($module->getName ())
					->setProfileName ($profile->getName ());
			}
			if (($module->getType () == ModuleInterface::TYPE_TOOL) || (in_array ($module->getName (), array ('Documents', 'ModTracker')))) {
				$accessPermission           = ModuleProfileInterface::PERMISSION_ALLOW;
				$deletePermission           = ModuleProfileInterface::PERMISSION_ALLOW;
				$editPermission             = ModuleProfileInterface::PERMISSION_ALLOW;
				$exportPermission           = ModuleProfileInterface::PERMISSION_ALLOW;
				$handleDuplicatesPermission = ModuleProfileInterface::PERMISSION_ALLOW;
				$importPermission           = ModuleProfileInterface::PERMISSION_ALLOW;
				$listPermission             = ModuleProfileInterface::PERMISSION_ALLOW;
				$mergePermission            = ModuleProfileInterface::PERMISSION_ALLOW;
				$readPermission             = ModuleProfileInterface::PERMISSION_ALLOW;
				$savePermission             = ModuleProfileInterface::PERMISSION_ALLOW;
			} else if (!isset ($permissions ['accesspermission'])) {
				$accessPermission           = ModuleProfileInterface::PERMISSION_DENY;
				$deletePermission           = ModuleProfileInterface::PERMISSION_DENY;
				$editPermission             = ModuleProfileInterface::PERMISSION_DENY;
				$exportPermission           = ModuleProfileInterface::PERMISSION_DENY;
				$handleDuplicatesPermission = ModuleProfileInterface::PERMISSION_DENY;
				$importPermission           = ModuleProfileInterface::PERMISSION_DENY;
				$listPermission             = ModuleProfileInterface::PERMISSION_DENY;
				$mergePermission            = ModuleProfileInterface::PERMISSION_DENY;
				$readPermission             = ModuleProfileInterface::PERMISSION_DENY;
				$savePermission             = ModuleProfileInterface::PERMISSION_DENY;
			} else {
				$accessPermission           = ModuleProfileInterface::PERMISSION_ALLOW;
				$deletePermission           = isset ($permissions ['deletepermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$editPermission             = isset ($permissions ['editpermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$exportPermission           = isset ($permissions ['exportpermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$handleDuplicatesPermission = isset ($permissions) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$importPermission           = isset ($permissions ['importpermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$listPermission             = isset ($permissions ['listpermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$mergePermission            = isset ($permissions ['mergepermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$readPermission             = isset ($permissions ['readpermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
				$savePermission             = isset ($permissions ['savepermission']) ? ModuleProfileInterface::PERMISSION_ALLOW : ModuleProfileInterface::PERMISSION_DENY;
			}
			$moduleProfile->setAccessPermission ($accessPermission)
				->setDeletePermission ($deletePermission)
				->setEditPermission ($editPermission)
				->setExportPermission ($exportPermission)
				->setHandleDuplicatesPermission ($handleDuplicatesPermission)
				->setImportPermission ($importPermission)
				->setListPermission ($listPermission)
				->setMergePermission ($mergePermission)
				->setReadPermission ($readPermission)
				->setSavePermission ($savePermission);
			return $moduleProfile;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Profile $profile
		 * @param Module $module
		 * @param Field $field
		 * @param integer $moduleAccessPermission
		 * @param string[] $permissions
		 *
		 * @return FieldProfile
		 */
		public static function getFieldProfile (PearDatabase $adb, $profile, $module, $field, $moduleAccessPermission, $permissions) {
			if (!$module->getIsEntityType ()) {
				return null;
			}

			$fieldProfile = FieldProfileManager::getInstance ($adb)->fetchProfileByProfileName ($profile->getName (), $module->getName (), $field->getName ());
			if (empty ($fieldProfile)) {
				$fieldProfile = FieldProfile::getInstance ()
					->setFieldName ($field->getName ())
					->setModuleName ($module->getName ())
					->setProfileName ($profile->getName ())
					->setReadOnly (FieldProfileInterface::READ_WRITE);
			}

			if ($moduleAccessPermission == ModuleProfileInterface::PERMISSION_DENY) {
				$fieldProfile->setVisibility (FieldProfileInterface::VISIBILITY_HIDDEN);
			} else if (in_array ($field->getUiType (), array (FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_OWNER))) {
				$fieldProfile->setVisibility (FieldProfileInterface::VISIBILITY_VISIBLE);
			} else if (isset ($permissions)) {
				$fieldProfile->setVisibility (FieldProfileInterface::VISIBILITY_VISIBLE);
			} else {
				$fieldProfile->setVisibility (FieldProfileInterface::VISIBILITY_HIDDEN);
			}
			return $fieldProfile;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Profile $profile
		 * @param Module $module
		 * @param View $view
		 * @param integer $moduleAccessPermission
		 * @param string[] $permissions
		 * @param string $defaultViewName
		 *
		 * @return ViewProfile
		 */
		public static function getViewProfile (PearDatabase $adb, $profile, $module, $view, $moduleAccessPermission, $permissions, $defaultViewName) {
			if (!$module->getIsEntityType ()) {
				return null;
			}

			$viewProfile = ViewProfileManager::getInstance ($adb)->fetchProfileByProfileName ($profile->getName (), $module->getName (), $view->getName ());
			if (empty ($viewProfile)) {
				$viewProfile = ViewProfile::getInstance ()
					->setModuleName ($module->getName ())
					->setProfileName ($profile->getName ())
					->setViewName ($view->getName ());
			}
			$viewProfile->setDefault ($view->getName () == $defaultViewName ? ViewProfileInterface::DEFAULT_YES : ViewProfileInterface::DEFAULT_NO);

			if ($view->getDefault () == ViewInterface::DEFAULT_YES) {
				$viewProfile->setAccessPermission (ViewProfileInterface::PERMISSION_ALLOW);
			} else if ($moduleAccessPermission == ModuleProfileInterface::PERMISSION_DENY) {
				$viewProfile->setAccessPermission (ViewProfileInterface::PERMISSION_DENY);
			} else if (isset ($permissions)) {
				$viewProfile->setAccessPermission (ViewProfileInterface::PERMISSION_ALLOW);
			} else {
				$viewProfile->setAccessPermission (ViewProfileInterface::PERMISSION_DENY);
			}

			return $viewProfile;
		}

	}
