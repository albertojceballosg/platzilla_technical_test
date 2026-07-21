<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/FieldValidationsHelper.class.php');

	abstract class EditableFieldsHelper {

		/**
		 * @param PearDatabase $adb
		 * @param Field $field
		 * @param array $values
		 * @param integer $recordId
		 *
		 * @return null|boolean
		 * @throws Exception
		 */
		private static function checkValidationRules($adb, $field, $values, $recordId) {
			if (empty($values) || empty($field)) {
				return true;
			}
			$isValidated = 'ok';
			foreach ($field->getValidations() as $rule) {
				if (!$rule instanceof FieldValidation) {
					continue;
				}
				try {
					FieldValidationsHelper::validateFields (
						$adb,
						array (
							'fieldname'      => $field->getName (),
							'fieldValue'     => $values[$field->getName ()],
							'maxvalue'       => $rule->getMaximumValue (),
							'minvalue'       => $rule->getMaximumValue (),
							'modulename'     => $field->getModuleName (),
							'recordid'       => $recordId,
							'tablename'      => $field->getTableName (),
							'validationtype' => $rule->getType (),
						)
					);
				} catch (Exception $e) {
					$isValidated = $e->getMessage ();
				}
			}
			return $isValidated;
		}

		private static function getEntityIdField (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT entityidfield FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['entityidfield'];
		}

		/**
		 * @param EditableFieldsButton $button
		 * @param integer $record
		 * @param array $app_strings
		 *
		 * @return null|string
		 * @throws Exception
		 * @throws SmartyException
		 */
		private static function getWindowsLinks ($button, $record, $app_strings) {
			if (!$button instanceof EditableFieldsButton || !$record) {
				return null;
			}
			$focus       = CRMEntity::getInstance ($button->getModuleName ());
			$focus->id   = $record;
			$focus->mode = 'edit';
			$focus->retrieve_entity_info ($record, $button->getModuleName ());
			$userSelected = $focus->column_fields['assigned_user_id'];
			$userList     = str_replace ('value=' . $userSelected. '>', 'value=' . $userSelected . ' selected="selected">', getUserslist(false));

			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('CHANGE_OWNER', $userList);
			$smarty->assign ('COLUMN_FIELDS', $focus->column_fields);
			$smarty->assign ('EDITABLE_BUTTOM', $button);
			$smarty->assign ('MASS_EDIT', '0');
			$smarty->assign ('RECORD', $record);
			$output = $smarty->fetch ('Settings/LayoutEditor/EditableFieldWindow.tpl');
			return $output;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param Field $field
		 * @param array $data
		 * @param integer $recordId
		 *
		 * @return string
		 * @throws Exception
		 */
		private static function updateDataFromDetailView ($adb, $userId, $field, $data, $recordId) {
			$isValidated = 'ok';
			$focus       = CRMEntity::getInstance ($field->getModuleName ());
			$focus->id   = $recordId;
			$focus->mode = 'edit';

			$focus->retrieve_entity_info ($recordId, $field->getModuleName ());
			$oldData = array();
			try {
				if (empty($field) || ($focus->column_fields[ $field->getName() ] == $data[ $field->getName() ])) {
					unset ($data[ $field->getName() ]);
					return $isValidated;
				}

				$entityFieldId = self::getEntityIdField ($adb, $field->getModuleName ());
				if (empty($entityFieldId)) {
					throw new Exception("Imposible encontar el campo index del módulo {$field->getModuleName ()}");
				}
				$oldData[ $field->getName() ] = $focus->column_fields[ $field->getName() ];
				$fieldId                           = ($field->getTableName() == 'vtiger_crmentity') ? 'crmid' : $entityFieldId;

				$adb->query("UPDATE {$field->getTableName()} SET {$field->getColumnName()} = '{$data[$field->getName ()]}' WHERE {$fieldId}= {$recordId}");

				$today = date('Y-m-d H:i:s');
				CrmEntityUtils::audit ($adb, $recordId, $field->getModuleName (), $oldData, $data, $userId);
				$adb->pquery('UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?', array($today, $userId, $recordId));
			} catch (Exception $e) {
				$isValidated = $e->getMessage();
			}
			return $isValidated;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param EditableFieldsButton $button
		 * @param array $data
		 * @param integer $recordId
		 *
		 * @return string
		 * @throws Exception
		 */
		private static function updateDataFromListView ($adb, $userId, $button, $data, $recordId) {
			$isUpdated   = false;
			$isValidated = 'ok';
			$focus       = CRMEntity::getInstance ($button->getModuleName ());
			$focus->id   = $recordId;
			$focus->mode = 'edit';

			$focus->retrieve_entity_info ($recordId, $button->getModuleName ());
			$oldData = array();
			try {
				foreach ($button->getEditableFields() as $field) {
					if (empty($field) || ($focus->column_fields[ $field->getFieldName() ] == $data[ $field->getFieldName() ])) {
						unset ($data[ $field->getFieldName() ]);
						continue;
					}

					$entityFieldId = self::getEntityIdField ($adb, $field->getField()->getModuleName ());
					if (empty($entityFieldId)) {
						throw new Exception("Imposible encontar el campo index del módulo {$field->getField()->getModuleName ()}");
					}
					$oldData[ $field->getFieldName() ] = $focus->column_fields[ $field->getFieldName() ];
					$fieldId                           = ($field->getField()->getTableName() == 'vtiger_crmentity') ? 'crmid' : $entityFieldId;

					$adb->query("UPDATE {$field->getField()->getTableName()} SET {$field->getField()->getColumnName()} = '{$data[$field->getFieldName ()]}' WHERE {$fieldId}= {$recordId}");
					$isUpdated = true;
				}
				if ($isUpdated) {
					$today = date('Y-m-d H:i:s');
					CrmEntityUtils::audit ($adb, $recordId, $button->getModuleName(), $oldData, $data, $userId);
					$adb->pquery('UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?', array($today, $userId, $recordId));
				}
			} catch (Exception $e) {
				$isValidated = $e->getMessage();
			}
			return $isValidated;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return Field[]|null
		 */
		public static function getEditableFields ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}
			$fields             = FieldManager::getInstance ($adb)->fetchFields ($moduleName);
			$totalFields        = count ($fields);
			$editableFieldsList = array (
				Field::UI_TYPE_DATE,
				Field::UI_TYPE_EMAIL,
				Field::UI_TYPE_NUMBER,
				Field::UI_TYPE_OWNER,
				Field::UI_TYPE_PICKLIST,
				Field::UI_TYPE_PERCENTAGE,
				Field::UI_TYPE_PHONE,
				Field::UI_TYPE_TEXT,
				Field::UI_TYPE_TEXTAREA,
			);
			for ($k = 0; $k < $totalFields; $k++) {
				if (!in_array ($fields[ $k ]->getUiType (), $editableFieldsList)) {
					unset($fields[ $k ]);
				} else if ($fields[ $k ]->getUiType () == Field::UI_TYPE_OWNER) {
					$fields[ $k ]->setLabel('Asignado a');
				}
			}
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function fetchEditableButtonsByModule ($adb, $moduleName, $recordId, $app_strings) {
			if(empty($moduleName)) {
				return null;
			}

			$buttons = EditableFieldsManager::getInstance($adb)->fetchEditableButtonsByModule($moduleName,false);
			if (!empty ($buttons)) {
				$buttonList = array ();
				foreach ($buttons as $button) {
					if(!$button->isStatus()) {
						continue;
					}
					$buttonList [] = self::getWindowsLinks ($button, $recordId, $app_strings);
				}
				return $buttonList;
			} else {
				return null;
			}
		}

		public static function getEditableFieldButtonName ($label) {
			$label = str_replace (
				array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$label
			);
			$label = str_replace (
				array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$label
			);
			$label = str_replace (
				array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$label
			);
			$label = str_replace (
				array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$label
			);
			$label = str_replace (
				array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$label
			);
			$label = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$label
			);
			$label = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'',
				$label
			);
			$label    = substr (trim (strtoupper ($label)), 0, 15);
			$randomId = rand (1000, 9999);
			return $label . $randomId;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $mod_strings
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public static function getEditableListHtml ($adb, $moduleName, $mod_strings) {
			if(empty($moduleName)) {
				return null;
			}
			$smarty  = new vtigerCRM_Smarty ();
			$buttons = EditableFieldsManager::getInstance($adb)->fetchEditableButtonsByModule($moduleName);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign('EDITABLE_BUTTOM', $buttons);
			return $smarty->fetch('Settings/LayoutEditor/EditableFieldsListView.tpl');
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param EditableFieldsButton $button
		 * @param array $data
		 * @param integer $recordId
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public static function saveDataFronListView ($adb, $userId, $button, $data, $recordId) {
			if(!count($data) || empty($button) || !$button instanceof EditableFieldsButton) {
				return null;
			}
			$isValidated = 'ok';
			foreach ($button->getEditableFields() as $field) {
				if (empty($field)) {
					continue;
				} else if (!empty ($field->getField()->getValidations())) {
					$isValidated = self::checkValidationRules ($adb, $field->getField(), $data, $recordId);
				}
			}
			if ($isValidated == 'ok') {
				$isValidated = self::updateDataFromListView ($adb, $userId, $button, $data, $recordId);
			}
			return $isValidated;
		}

		/**
		 * @param PearDatabase$adb
		 * @param integer $userId
		 * @param Field $field
		 * @param array $data
		 * @param integer $recordId
		 *
		 * @return boolean|null|string
		 * @throws Exception
		 */
		public static function saveDataFromDetailView ($adb, $userId, $field, $data, $recordId) {
			$isValidated = self::checkValidationRules ($adb, $field, $data, $recordId);

			if ($isValidated == 'ok') {
				$isValidated = self::updateDataFromDetailView ($adb, $userId, $field, $data, $recordId);
			}
			return $isValidated;
		}

	}
