<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/ComboUtil.php'); //new
	require_once ('include/utils/CommonUtils.php'); //new
	require_once 'modules/PickList/DependentPickListUtils.php';
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/DateDefaultValueUtils.php');

	function getOutputHtml ($uitype, $fieldname, $fieldlabel, $maxlength, $col_fields, $generatedtype, $module_name, $mode = '', $typeofdata = null) {
		global $log;
		$log->debug ("Entering getOutputHtml(" . $uitype . "," . $fieldname . "," . $fieldlabel . "," . $maxlength . "," . $col_fields . "," . $generatedtype . "," . $module_name . ") method ...");
		global $adb, $log, $default_charset;
		global $theme;
		global $app_strings;
		global $current_user;

		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');

		$theme_path         = "themes/" . $theme . "/";
		$image_path         = $theme_path . "images/";
		$fieldlabel         = from_html ($fieldlabel);
		$fieldvalue         = Array ();
		$final_arr          = Array ();
		$value              = $col_fields[ $fieldname ];
		$custfld            = '';
		$ui_type[]          = $uitype;
		$editview_fldname[] = $fieldname;

		// vtlib customization: Related type field
		if ($uitype == '10' && $fieldname == 'videoid' && $module_name == 'formacion_lecciones') {
			if (!empty($value)) {
				$sql   = "SELECT * FROM vtiger_videos WHERE idvideo='" . $value . "'";
				$q     = $adb->pquery ($sql);
				$video = $adb->fetchByAssoc ($q);
			}

			$editview_label[] = Array ('options' => array ('video'), 'displaylabel' => getTranslatedString ($fieldlabel, $module_name));
			$fieldvalue[]     = Array ('displayvalue' => $video['file'], 'entityid' => $value);
		} elseif ($uitype == '10') {
			global $adb;
			$adbBak        = clone $GLOBALS['adb'];
			$parameters    = html_entity_decode (obtenerValorVariable ($fieldname, $module_name), ENT_QUOTES);
			$lstParameters = unserialize ($parameters);

			$fldmod_result = $adb->pquery ('SELECT relmodule, status FROM vtiger_fieldmodulerel WHERE fieldid=
			(SELECT fieldid FROM vtiger_field, vtiger_tab WHERE vtiger_field.tabid=vtiger_tab.tabid AND fieldname=? AND name=? AND vtiger_field.presence IN (0,2))',
				Array ($fieldname, $module_name));

			$entityTypes = Array ();
			$parent_id   = $value;
			for ($index = 0; $index < $adb->num_rows ($fldmod_result); ++$index) {
				$entityTypes[] = $adb->query_result ($fldmod_result, $index, 'relmodule');
			}

			//Campos relacionales entre plaformas
			if (isset($lstParameters['relplat']) && !empty($lstParameters['relplat'])) {
				unset($GLOBALS['adb']);
				$GLOBALS['adb'] = conectaPlataformaHija ($lstParameters['relplat']);
				$entityTypes    = array ($lstParameters['srcmodule']);
			}

			if (!empty($value)) {
				$valueType = getSalesEntityType ($value);
				//Fix for detailview users
				if ($fieldname == 'user_id') {
					$valueType = 'Users';
				}
				$displayValueArray = getEntityName ($valueType, $value);
				if (!empty($displayValueArray)) {
					foreach ($displayValueArray as $key => $value) {
						$displayValue = $value;
					}
				}
			} else {
				$displayValue = '';
				$valueType    = '';
				$value        = '';
			}

			$editview_label[] = Array ('options' => $entityTypes, 'selected' => $valueType, 'displaylabel' => getTranslatedString ($fieldlabel, $module_name));
			$fieldvalue[]     = Array ('displayvalue' => $displayValue, 'entityid' => $parent_id);

			if (isset($lstParameters['relplat']) && !empty($lstParameters['relplat'])) {
				@$adb->disconnect ();
				unset($adb);

				$GLOBALS['adb'] = clone $adbBak;
				unset($adbBak);
			}
		} // END
		else if ($uitype == 5 || $uitype == 6 || $uitype == 23) {
			$log->info ("uitype is " . $uitype);
			$log->info ("Field: $fieldname, Value before processing: $value");
			
			// Procesar expresiones de fecha dinámicas (TODAY+X, CURRENT_DATE-X)
			if ($uitype == 5 && !empty($value)) {
				require_once('modules/Settings/lib/DateDefaultValueUtils.php');
				$processedValue = processDateDefaultValue($value);
				// Si el valor fue procesado (cambió), usarlo
				if ($processedValue !== $value && !empty($processedValue)) {
					$value = $processedValue;
				}
			}
			
			if ($value == '') {
				//modified to fix the issue in trac(http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/1469)
				if ($fieldname != 'birthday' && $generatedtype != 2 && getTabid ($module_name) != 14) {
					$disp_value = null;
				}

				if (($module_name == 'Events' || $module_name == 'Calendar') && $uitype == 6) {
					$curr_time = date ('H:i', strtotime ('+5 minutes'));
				}
				if (($module_name == 'Events' || $module_name == 'Calendar') && $uitype == 23) {
					$curr_time = date ('H:i', strtotime ('+10 minutes'));
				}
			} else {

				if ($uitype == 6) {
					if ($col_fields['time_start'] != '' && ($module_name == 'Events' || $module_name
																						== 'Calendar')
					) {
						$curr_time = $col_fields['time_start'];
						$value     = $value . ' ' . $curr_time;
					} else {
						$curr_time = date ('H:i', strtotime ('+5 minutes'));
					}
				}
				if (($module_name == 'Events' || $module_name == 'Calendar') && $uitype == 23) {
					if ($col_fields['time_end'] != '') {
						$curr_time = $col_fields['time_end'];
						$value     = $value . ' ' . $curr_time;
					} else {
						$curr_time = date ('H:i', strtotime ('+10 minutes'));
					}
				}
				$disp_value = getValidDisplayDate ($value);
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$date_format      = parse_calendardate ($app_strings['NTC_DATE_FORMAT']);
			if (!empty($curr_time)) {
				if (($module_name == 'Events' || $module_name == 'Calendar') && ($uitype == 23 ||
																				 $uitype == 6)
				) {
					$curr_time = DateTimeField::convertToUserTimeZone ($curr_time);
					$curr_time = $curr_time->format ('H:i');
				}
			}
			$fieldvalue[] = array ($disp_value => $curr_time);
			if ($uitype == 5 || $uitype == 23) {
				if ($module_name == 'Events' && $uitype == 23) {
					$fieldvalue[] = array ($date_format => $current_user->date_format . ' ' . $app_strings['YEAR_MONTH_DATE']);
				} else {
					$fieldvalue[] = array ($date_format => $current_user->date_format);
				}
			} else {
				$fieldvalue[] = array ($date_format => $current_user->date_format . ' ' . $app_strings['YEAR_MONTH_DATE']);
			}
		} elseif ($uitype == 16) {
			require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
			$picklist = GlobalPicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldname);
			$options  = array ();
			if (empty ($picklist)) {
				$options = null;
			} else if (!$picklist->isMultiple ()) {
				$picklistValues = $picklist->getValues ();
				if (!empty ($picklistValues)) {
					foreach ($picklistValues as $picklistValue) {
						$options [] = array (
							$picklistValue->getValue (),
							$picklistValue->getValue (),
							$picklistValue->getValue () == $value ? 'selected' : '',
						);
					}
				}
				$isMultiple = false;
			} else {
				$dummy          = explode (' |##| ', $value);
				$picklistValues = $picklist->getValues ();
				if (!empty ($picklistValues)) {
					foreach ($picklistValues as $picklistValue) {
						$options [] = array (
							$picklistValue->getValue (),
							$picklistValue->getValue (),
							in_array ($picklistValue->getValue (), $dummy) ? 'selected' : '',
						);
					}
				}
				$isMultiple = true;
			}
			$editview_label [] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []     = $options;
		} elseif ($uitype == 15 || $uitype == 33) {

			require_once 'modules/PickList/PickListUtils.php';
			$roleid         = $current_user->roleid;
			$picklistValues = getAssignedPicklistValues ($fieldname, $roleid, $adb);
			$valueArr       = explode ("|##|", $value);
			$pickcount      = 0;
			$options        = null;
			
			// Normalizar valores almacenados para comparación (trim y decode_html)
			$normalizedValueArr = array_map(function($val) {
				return trim(decode_html($val));
			}, $valueArr);
			
			if (!empty($picklistValues)) {
				$options = array ();
				foreach ($picklistValues as $order => $pickListValue) {
					// Normalizar valor del picklist para comparación
					$normalizedPickValue = trim(decode_html($pickListValue));
					
					if (in_array ($normalizedPickValue, $normalizedValueArr)) {
						$chk_val = "selected";
						$pickcount++;
					} else {
						$chk_val = '';
					}
					if (isset($_REQUEST['file']) && $_REQUEST['file'] == 'QuickCreate') {
						$options[] = array (htmlentities (getTranslatedString ($pickListValue), ENT_QUOTES, $default_charset), $pickListValue, $chk_val);
					} else {
						$options[] = array (getTranslatedString ($pickListValue), $pickListValue, $chk_val);
					}
				}
				// Si no hay coincidencias y hay valor, mostrar en rojo el valor original
				if ($pickcount == 0 && !empty($value)) {
					// Obtener el valor original para mostrar (con traducción si existe)
					$displayValue = getTranslatedString($value);
					if ($displayValue == $value) {
						// Si no hay traducción, usar el valor original decodificado
						$displayValue = decode_html($value);
					}
					
					// Envolver en span rojo para indicar valor inválido
					$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
					
					$options[] = array ($redDisplayValue, $value, 'selected');
				}
			} else {
				// Si no hay valores disponibles en el picklist pero hay un valor almacenado, mostrarlo en rojo
				if (!empty($value)) {
					
					// Obtener el valor original para mostrar (con traducción si existe)
					$displayValue = getTranslatedString($value);
					if ($displayValue == $value) {
						// Si no hay traducción, usar el valor original decodificado
						$displayValue = decode_html($value);
					}
					
					// Envolver en span rojo para indicar valor inválido
					$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
					
					$options = array ($redDisplayValue, $value, 'selected');
				}
			}
			
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $options;
		} elseif ($uitype == 404) {

			global $adb;
			$fldmod_result = $adb->pquery ('SELECT relmodule, status FROM vtiger_fieldmodulerel WHERE fieldid=
			(SELECT fieldid FROM vtiger_field, vtiger_tab WHERE vtiger_field.tabid=vtiger_tab.tabid AND fieldname=? AND name=? AND vtiger_field.presence IN (0,2))',
				Array ($fieldname, $module_name));

			$entityTypes = Array ();
			$parent_id   = $value;
			$entityTypes = $adb->query_result ($fldmod_result, 0, 'relmodule');

			$roleid         = $current_user->roleid;
			$picklistValues = getRelatedRecords ($entityTypes);
			$valueArr       = explode ("|##|", $value);
			$pickcount      = 0;

			if (!empty($picklistValues)) {
				foreach ($picklistValues as $order => $pickListValue) {
					if (in_array (trim ($order), array_map ("trim", $valueArr))) {
						$chk_val = "selected";
						$pickcount++;
					} else {
						$chk_val = '';
					}
					if (isset($_REQUEST['file']) && $_REQUEST['file'] == 'QuickCreate') {
						$options[] = array (htmlentities (getTranslatedString ($pickListValue), ENT_QUOTES, $default_charset), $order, $chk_val);
					} else {
						$options[] = array (getTranslatedString ($pickListValue), $order, $chk_val);
					}
				}

				if ($pickcount == 0 && !empty($value)) {
					// Obtener el valor original para mostrar (con traducción si existe)
					$displayValue = getTranslatedString($value);
					if ($displayValue == $value) {
						// Si no hay traducción, usar el valor original decodificado
						$displayValue = decode_html($value);
					}
					
					// Envolver en span rojo para indicar valor inválido
					$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
					
					$options[] = array ($redDisplayValue, $value, 'selected');
				}
			} else {
				// Si no hay valores disponibles pero hay un valor almacenado, mostrarlo en rojo
				if (!empty($value)) {
					// Obtener el valor original para mostrar (con traducción si existe)
					$displayValue = getTranslatedString($value);
					if ($displayValue == $value) {
						// Si no hay traducción, usar el valor original decodificado
						$displayValue = decode_html($value);
					}
					
					// Envolver en span rojo para indicar valor inválido
					$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
					
					$options = array ($redDisplayValue, $value, 'selected');
				}
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $options;
		} elseif ($uitype == 17) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $value;
		} elseif ($uitype == 85) //added for Skype by Minnie
		{
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $value;
		} elseif ($uitype == 14) //added for Time Field
		{
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $value;
		} elseif ($uitype == 19 || $uitype == 20) {
			if (isset($_REQUEST['body'])) {
				$value = ($_REQUEST['body']);
			}

			if ($fieldname == 'terms_conditions')//for default Terms & Conditions
			{
				//Assign the value from focus->column_fields (if we create Invoice from SO the SO's terms and conditions will be loaded to Invoice's terms and conditions, etc.,)
				$value = $col_fields['terms_conditions'];

				//if the value is empty then only we should get the default Terms and Conditions
				if ($value == '' && $mode != 'edit') {
					$value = getTermsandConditions ();
				}
			}

			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $value;
		} elseif ($uitype == 21 || $uitype == 24) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue []    = $value;
		} elseif ($uitype == 22) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $value;
		} elseif ($uitype == 52 || $uitype == 77 || $uitype == 407)    // EGC
		{
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			global $current_user;
			if ($value != '') {
				$assigned_user_id = $value;
			} else {
				$assigned_user_id = $current_user->id;
			}
			if ($uitype == 52) {
				$combo_lbl_name = 'assigned_user_id';
			} elseif ($uitype == 77) {
				$combo_lbl_name = 'assigned_user_id1';
			} elseif ($uitype == 407) {
				$combo_lbl_name = '';
			}

			if ($uitype == 407) {
				$picklistValues = get_user_array (false, "Active", $current_user->id);
				$valueArr       = explode ("|##|", $value);
				$pickcount      = 0;

				if (!empty($picklistValues)) {
					foreach ($picklistValues as $pickListValue => $texto) {
						$valorCombo = null;
						if (in_array (trim ($pickListValue), array_map ("trim", $valueArr))) {
							$chk_val = "selected";
							$pickcount++;
						} else {
							$chk_val = '';
						}
						if (!$valorCombo) {
							$valorCombo = $pickListValue;
						}
						$users_combo[] = array (getTranslatedString ($texto), $valorCombo, $chk_val);
					}

					if ($pickcount == 0 && !empty($value)) {
						// Obtener el valor original para mostrar (con traducción si existe)
						$displayValue = getTranslatedString($value);
						if ($displayValue == $value) {
							// Si no hay traducción, usar el valor original decodificado
							$displayValue = decode_html($value);
						}
						
						// Envolver en span rojo para indicar valor inválido
						$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
						
						$users_combo[] = array ($redDisplayValue, $value, 'selected');
					}
				} else {
					// Si no hay valores disponibles pero hay un valor almacenado, mostrarlo en rojo
					if (!empty($value)) {
						// Obtener el valor original para mostrar (con traducción si existe)
						$displayValue = getTranslatedString($value);
						if ($displayValue == $value) {
							// Si no hay traducción, usar el valor original decodificado
							$displayValue = decode_html($value);
						}
						
						// Envolver en span rojo para indicar valor inválido
						$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
						
						$users_combo = array ($redDisplayValue, $value, 'selected');
					}
				}
			} //Control will come here only for Products - Handler and Quotes - Inventory Manager
			else if ($is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module_name) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module_name) ] == 0)) {
				$users_combo = get_select_options_array (get_user_array (false, "Active", $assigned_user_id, 'private'), $assigned_user_id);
			} else {
				$users_combo = get_select_options_array (get_user_array (false, "Active", $assigned_user_id), $assigned_user_id);
			}
			$fieldvalue [] = $users_combo;
		} elseif ($uitype == 53) {
			global $noof_group_rows;
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			//Security Checks
			if ($fieldname == 'assigned_user_id' && $is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module_name) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module_name) ] == 0)) {
				$result = get_current_user_access_groups ($module_name);
			} else {
				$result = get_group_options ();
			}
			if ($result) {
				$nameArray = $adb->fetch_array ($result);
			}

			if ($value != '' && $value != 0) {
				$assigned_user_id = $value;
			} else {
				if ($value == '0') {
					if (isset($col_fields['assigned_group_info']) && $col_fields['assigned_group_info'] != '') {
						$selected_groupname = $col_fields['assigned_group_info'];
					} else {
						$record             = $col_fields["record_id"];
						$module             = $col_fields["record_module"];
						$selected_groupname = getGroupName ($record, $module);
					}
				} else {
					$assigned_user_id = $current_user->id;
				}
			}

			if ($fieldname == 'assigned_user_id' && $is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module_name) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module_name) ] == 0)) {
				$users_combo = get_select_options_array (get_user_array (false, "Active", $assigned_user_id, 'private'), $assigned_user_id);
			} else {
				$userData    = get_user_array (false, "Active", $assigned_user_id);
				$users_combo = get_select_options_array ($userData, $assigned_user_id);
			}

			if ($noof_group_rows != 0) {
				if ($fieldname == 'assigned_user_id' && $is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module_name) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module_name) ] == 0)) {
					$groups_combo = get_select_options_array (get_group_array (false, "Active", $assigned_user_id, 'private'), $assigned_user_id);
				} else {
					$groups_combo = get_select_options_array (get_group_array (false, "Active", $assigned_user_id), $assigned_user_id);
				}
			}
			$fieldvalue[] = $users_combo;
			$fieldvalue[] = $groups_combo;
		} elseif ($uitype == 51 || $uitype == 50 || $uitype == 73) {
			if ($_REQUEST['convertmode'] != 'update_quote_val' && $_REQUEST['convertmode'] != 'update_so_val') {
				if (isset($_REQUEST['account_id']) && $_REQUEST['account_id'] != '') {
					$value = $_REQUEST['account_id'];
				}
			}
			if ($value != '') {
				$account_name = getAccountName ($value);
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $account_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 54) {
			$options          = array ();
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$pick_query       = "SELECT * FROM vtiger_groups";
			$pickListResult   = $adb->pquery ($pick_query, array ());
			$noofpickrows     = $adb->num_rows ($pickListResult);
			for ($j = 0; $j < $noofpickrows; $j++) {
				$pickListValue = $adb->query_result ($pickListResult, $j, "name");

				if ($value == $pickListValue) {
					$chk_val = "selected";
				} else {
					$chk_val = '';
				}
				$options[] = array ($pickListValue => $chk_val);
			}
			$fieldvalue[] = $options;
		} elseif ($uitype == 55 || $uitype == 255) {
			require_once 'modules/PickList/PickListUtils.php';
			if ($uitype == 255) {
				$fieldpermission = getFieldVisibilityPermission ($module_name, $current_user->id, 'firstname', 'readwrite');
			}
			if ($uitype == 255 && $fieldpermission == '0') {
				$fieldvalue[] = '';
			} else {
				$fieldpermission = getFieldVisibilityPermission ($module_name, $current_user->id, 'salutationtype', 'readwrite');
				if ($fieldpermission == '0') {
					$roleid         = $current_user->roleid;
					$picklistValues = getAssignedPicklistValues ('salutationtype', $roleid, $adb);
					$pickcount      = 0;
					$salt_value     = $col_fields["salutationtype"];
					foreach ($picklistValues as $order => $pickListValue) {
						if ($salt_value == trim ($pickListValue)) {
							$chk_val = "selected";
							$pickcount++;
						} else {
							$chk_val = '';
						}
						if (isset($_REQUEST['file']) && $_REQUEST['file'] == 'QuickCreate') {
							$options[] = array (htmlentities (getTranslatedString ($pickListValue), ENT_QUOTES, $default_charset), $pickListValue, $chk_val);
						} else {
							$options[] = array (getTranslatedString ($pickListValue), $pickListValue, $chk_val);
						}
					}
					if ($pickcount == 0 && $salt_value != '') {
						// Obtener el valor original para mostrar (con traducción si existe)
						$displayValue = getTranslatedString($salt_value);
						if ($displayValue == $salt_value) {
							// Si no hay traducción, usar el valor original decodificado
							$displayValue = decode_html($salt_value);
						}
						
						// Envolver en span rojo para indicar valor inválido
						$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
						
						$options[] = array ($redDisplayValue, $salt_value, 'selected');
					}
				} else {
					// Si no hay valores disponibles pero hay un valor almacenado, mostrarlo en rojo
					if ($salt_value != '') {
						// Obtener el valor original para mostrar (con traducción si existe)
						$displayValue = getTranslatedString($salt_value);
						if ($displayValue == $salt_value) {
							// Si no hay traducción, usar el valor original decodificado
							$displayValue = decode_html($salt_value);
						}
						
						// Envolver en span rojo para indicar valor inválido
						$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . htmlentities($displayValue, ENT_QUOTES, $default_charset) . '</span>';
						
						$options = array ($redDisplayValue, $salt_value, 'selected');
						$fieldvalue [] = $options;
					}
				}
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $value;
		} elseif ($uitype == 63) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			if ($value == '') {
				$value = 1;
			}
			$options        = array ();
			$pick_query     = "SELECT * FROM vtiger_duration_minutes ORDER BY sortorderid";
			$pickListResult = $adb->pquery ($pick_query, array ());
			$noofpickrows   = $adb->num_rows ($pickListResult);
			$salt_value     = $col_fields["duration_minutes"];
			for ($j = 0; $j < $noofpickrows; $j++) {
				$pickListValue = $adb->query_result ($pickListResult, $j, "duration_minutes");

				if ($salt_value == $pickListValue) {
					$chk_val = "selected";
				} else {
					$chk_val = '';
				}
				$options[ $pickListValue ] = $chk_val;
			}
			$fieldvalue[] = $value;
			$fieldvalue[] = $options;
		} elseif ($uitype == 64) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$date_format      = parse_calendardate ($app_strings['NTC_DATE_FORMAT']);
			$fieldvalue[]     = $value;
		} elseif ($uitype == 156) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $value;
			$fieldvalue[]     = $is_admin;
		} elseif ($uitype == 56) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $value;
		} elseif ($uitype == 58) {

			if ($value != '') {
				$campaign_name = getCampaignName ($value);
			} elseif (isset($_REQUEST['campaignid']) && $_REQUEST['campaignid'] != '') {
				if ($_REQUEST['module'] == 'Campaigns' && $fieldname = 'campaignid') {
					$campaign_name = '';
				} else {
					$value         = $_REQUEST['campaignid'];
					$campaign_name = getCampaignName ($value);
				}
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $campaign_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 61) {
			if ($value != '') {
				$assigned_user_id = $value;
			} else {
				$assigned_user_id = $current_user->id;
			}
			if ($module_name == 'Emails' && $col_fields['record_id'] != '') {
				$attach_result = $adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($col_fields['record_id']));
				//to fix the issue in mail attachment on forwarding mails
				if (isset($_REQUEST['forward']) && $_REQUEST['forward'] != '') {
					global $att_id_list;
				}
				for ($ii = 0; $ii < $adb->num_rows ($attach_result); $ii++) {
					$attachmentid = $adb->query_result ($attach_result, $ii, 'attachmentsid');
					if ($attachmentid != '') {
						$attachquery     = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
						$attachmentsname = $adb->query_result ($adb->pquery ($attachquery, array ($attachmentid)), 0, 'name');
						if ($attachmentsname != '') {
							$fieldvalue[ $attachmentid ] = '[ ' . $attachmentsname . ' ]';
						}
						if (isset($_REQUEST['forward']) && $_REQUEST['forward'] != '') {
							$att_id_list .= $attachmentid . ';';
						}
					}
				}
			} else {
				if ($col_fields['record_id'] != '') {
					$attachmentid = $adb->query_result ($adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($col_fields['record_id'])), 0, 'attachmentsid');
					if ($col_fields[ $fieldname ] == '' && $attachmentid != '') {
						$attachquery = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
						$value       = $adb->query_result ($adb->pquery ($attachquery, array ($attachmentid)), 0, 'name');
					}
				}
				if ($value != '') {
					$filename = ' [ ' . $value . ' ]';
				}

				if ($filename != '') {
					$fieldvalue[] = $filename;
				}
				if ($value != '') {
					$fieldvalue[] = $value;
				}
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
		} elseif ($uitype == 28) {
			if ($col_fields['record_id'] != '') {
				$attachmentid = $adb->query_result ($adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($col_fields['record_id'])), 0, 'attachmentsid');
				if ($col_fields[ $fieldname ] == '' && $attachmentid != '') {
					$attachquery = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
					$value       = $adb->query_result ($adb->pquery ($attachquery, array ($attachmentid)), 0, 'name');
				}
			}
			if ($value != '' && $module_name != 'Documents') {
				$filename = ' [ ' . $value . ' ]';
			} elseif ($value != '' && $module_name == 'Documents') {
				$filename = $value;
			}
			if ($filename != '') {
				$fieldvalue[] = $filename;
			}
			if ($value != '') {
				$fieldvalue[] = $value;
			}

			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
		} //added by rdhital/Raju for better email support
		elseif ($uitype == 72) {

			$currencyField = new CurrencyField($value);
			// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
			if ($col_fields['record_id'] != '' && $uitype == 72) {
				if ($fieldname == 'unit_price') {
					$rate_symbol    = getCurrencySymbolandCRate (getProductBaseCurrency ($col_fields['record_id'], $module_name));
					$currencySymbol = $rate_symbol['symbol'];
				} else {
					$currency_info  = getInventoryCurrencyInfo ($module, $col_fields['record_id']);
					$currencySymbol = $currency_info['currency_symbol'];
				}
				$fieldvalue[] = $currencyField->getDisplayValue (null, true);
			} else {
				$fieldvalue[]   = $currencyField->getDisplayValue ();
				$currencySymbol = $currencyField->getCurrencySymbol ();
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name) . ': (' . $currencySymbol . ')';
		} elseif ($uitype == 75 || $uitype == 81) {
			if ($value != '') {
				$vendor_name = getVendorName ($value);
			} elseif (isset($_REQUEST['vendor_id']) && $_REQUEST['vendor_id'] != '') {
				$value       = $_REQUEST['vendor_id'];
				$vendor_name = getVendorName ($value);
			}
			$pop_type = 'specific';
			if ($uitype == 81) {
				$pop_type = 'specific_vendor_address';
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $vendor_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 76) {
			if ($value != '') {
				$potential_name = getPotentialName ($value);
			} elseif (isset($_REQUEST['potential_id']) && $_REQUEST['potential_id'] != '') {
				$value          = $_REQUEST['potental_id'];
				$potential_name = getPotentialName ($value);
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $potential_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 78) {
			if ($value != '') {
				$quote_name = getQuoteName ($value);
			} elseif (isset($_REQUEST['quote_id']) && $_REQUEST['quote_id'] != '') {
				$value          = $_REQUEST['quote_id'];
				$potential_name = getQuoteName ($value);
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $quote_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 79) {
			if ($value != '') {
				$purchaseorder_name = getPoName ($value);
			} elseif (isset($_REQUEST['purchaseorder_id']) && $_REQUEST['purchaseorder_id'] != '') {
				$value              = $_REQUEST['purchaseorder_id'];
				$purchaseorder_name = getPoName ($value);
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $purchaseorder_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 80) {
			if ($value != '') {
				$salesorder_name = getSoName ($value);
			} elseif (isset($_REQUEST['salesorder_id']) && $_REQUEST['salesorder_id'] != '') {
				$value           = $_REQUEST['salesorder_id'];
				$salesorder_name = getSoName ($value);
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $salesorder_name;
			$fieldvalue[]     = $value;
		} elseif ($uitype == 30) {
			$rem_days = 0;
			$rem_hrs  = 0;
			$rem_min  = 0;
			if ($value != '') {
				$SET_REM = "CHECKED";
			}
			$rem_days         = floor ($col_fields[ $fieldname ] / (24 * 60));
			$rem_hrs          = floor (($col_fields[ $fieldname ] - $rem_days * 24 * 60) / 60);
			$rem_min          = ($col_fields[ $fieldname ] - $rem_days * 24 * 60) % 60;
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$day_options      = getReminderSelectOption (0, 31, 'remdays', $rem_days);
			$hr_options       = getReminderSelectOption (0, 23, 'remhrs', $rem_hrs);
			$min_options      = getReminderSelectOption (1, 59, 'remmin', $rem_min);
			$fieldvalue[]     = array (array (0, 32, 'remdays', getTranslatedString ('LBL_DAYS'), $rem_days), array (0, 24, 'remhrs', getTranslatedString ('LBL_HOURS'), $rem_hrs), array (1, 60, 'remmin', getTranslatedString ('LBL_MINUTES') . '&nbsp;&nbsp;' . getTranslatedString ('LBL_BEFORE_EVENT'), $rem_min));
			$fieldvalue[]     = array ($SET_REM, getTranslatedString ('LBL_YES'), getTranslatedString ('LBL_NO'));
			$SET_REM          = '';
		} elseif ($uitype == 115) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$pick_query       = "SELECT * FROM vtiger_" . $adb->sql_escape_string ($fieldname);
			$pickListResult   = $adb->pquery ($pick_query, array ());
			$noofpickrows     = $adb->num_rows ($pickListResult);

			//Mikecrowe fix to correctly default for custom pick lists
			$options = array ();
			$found   = false;
			for ($j = 0; $j < $noofpickrows; $j++) {
				$pickListValue = $adb->query_result ($pickListResult, $j, strtolower ($fieldname));

				if ($value == $pickListValue) {
					$chk_val = "selected";
					$found   = true;
				} else {
					$chk_val = '';
				}
				$options[] = array (getTranslatedString ($pickListValue), $pickListValue, $chk_val);
			}
			$fieldvalue [] = $options;
			$fieldvalue [] = $is_admin;
		} elseif ($uitype == 116 || $uitype == 117) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$pick_query       = "SELECT * FROM vtiger_currency_info WHERE currency_status = 'Active' AND deleted=0";
			$pickListResult   = $adb->pquery ($pick_query, array ());
			$noofpickrows     = $adb->num_rows ($pickListResult);

			//Mikecrowe fix to correctly default for custom pick lists
			$options = array ();
			$found   = false;
			for ($j = 0; $j < $noofpickrows; $j++) {
				$pickListValue = $adb->query_result ($pickListResult, $j, 'currency_name');
				$currency_id   = $adb->query_result ($pickListResult, $j, 'id');
				if ($value == $currency_id) {
					$chk_val = "selected";
					$found   = true;
				} else {
					$chk_val = '';
				}
				$options[ $currency_id ] = array ($pickListValue => $chk_val);
			}
			$fieldvalue [] = $options;
			$fieldvalue [] = $is_admin;
		} elseif ($uitype == 98) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $value;
			$fieldvalue[]     = getRoleName ($value);
			$fieldvalue[]     = $is_admin;
		} elseif ($uitype == 257 || $uitype == 258) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			if (isset($col_fields['record_id']) && $col_fields['record_id'] != '') {
				$query        = "SELECT vtiger_attachments.path, vtiger_attachments.name FROM  vtiger_seattachmentsrel INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid WHERE vtiger_attachments.attachmentsid=?";
				$result_image = $adb->pquery ($query, array ($value));
				for ($image_iter = 0; $image_iter < $adb->num_rows ($result_image); $image_iter++) {
					$image_array[]      = $adb->query_result ($result_image, $image_iter, 'name');
					$image_path_array[] = $adb->query_result ($result_image, $image_iter, 'path');
				}
			}
			if (is_array ($image_array)) {
				for ($img_itr = 0; $img_itr < count ($image_array); $img_itr++) {
					$fieldvalue[] = array ('name' => $image_array[ $img_itr ], 'path' => $image_path_array[ $img_itr ], 'id' => $value);
				}
			} else {
				$fieldvalue[] = array ('name' => '', 'path' => '', 'id' => '');
			};
		} elseif ($uitype == 101) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = getOwnerName ($value);
			$fieldvalue[]     = $value;
		} elseif ($uitype == 108) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$tojson           = html_entity_decode ($col_fields[ $fieldname ]);
			$jvalues          = json_decode ($tojson, true);
			$fieldvalue[]     = $jvalues;
		} elseif ($uitype == 26) {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$folderid         = $col_fields['folderid'];
			$foldername_query = 'SELECT foldername FROM vtiger_attachmentsfolder WHERE folderid = ?';
			$res              = $adb->pquery ($foldername_query, array ($folderid));
			$foldername       = $adb->query_result ($res, 0, 'foldername');
			if ($foldername != '' && $folderid != '') {
				$fldr_name[ $folderid ] = $foldername;
			}
			$sql = "SELECT foldername,folderid FROM vtiger_attachmentsfolder ORDER BY foldername";
			$res = $adb->pquery ($sql, array ());
			for ($i = 0; $i < $adb->num_rows ($res); $i++) {
				$fid               = $adb->query_result ($res, $i, "folderid");
				$fldr_name[ $fid ] = $adb->query_result ($res, $i, "foldername");
			}
			$fieldvalue[] = $fldr_name;
		} elseif ($uitype == 27) {
			if ($value == 'E') {
				$external_selected = "selected";
				$filename          = $col_fields['filename'];
			} else {
				$internal_selected = "selected";
				$filename          = $col_fields['filename'];
			}
			$editview_label[] = array (
				getTranslatedString ('Internal'),
				getTranslatedString ('External'),
			);
			$editview_label[] = array (
				$internal_selected,
				$external_selected,
			);
			$editview_label[] = array ("I", "E");
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$fieldvalue[]     = $value;
			$fieldvalue[]     = $filename;
		} elseif ($uitype == '31') {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$options          = array ();
			$themeList        = get_themes ();
			foreach ($themeList as $theme) {
				if ($current_user->theme == $theme) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$options[] = array (getTranslatedString ($theme), $theme, $selected);
			}
			$fieldvalue [] = $options;
		} elseif ($uitype == '32') {
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			$options          = array ();
			$languageList     = Vtiger_Language::getAll ();
			foreach ($languageList as $prefix => $label) {
				if ($current_user->language == $prefix) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$options[] = array (getTranslatedString ($label), $prefix, $selected);
			}
			$fieldvalue [] = $options;
		} else {
			//Added condition to set the subject if click Reply All from web mail
			if ($_REQUEST['module'] == 'Emails' && $_REQUEST['mg_subject'] != '') {
				$value = $_REQUEST['mg_subject'];
			}
			$editview_label[] = getTranslatedString ($fieldlabel, $module_name);
			if ($fieldname == 'fileversion') {
				if (empty($value)) {
					$value = '';
				} else {
					$fieldvalue[] = $value;
				}
			} else {
				$fieldvalue[] = $value;
			}
		}

		// Mike Crowe Mod --------------------------------------------------------force numerics right justified.
		if (!preg_match ("/id=/i", $custfld)) {
			$custfld = preg_replace ("/<input/iS", "<input id='$fieldname' ", $custfld);
		}

		if (in_array ($uitype, array (71, 72, 7, 9, 90))) {
			$custfld = preg_replace ("/<input/iS", "<input align=right ", $custfld);
		}
		$final_arr[]  = $ui_type;
		$final_arr[]  = $editview_label;
		$final_arr[]  = $editview_fldname;
		$final_arr[]  = $fieldvalue;
		$type_of_data = explode ('~', $typeofdata);
		$final_arr[]  = $type_of_data[1];
		if (isset ($isMultiple)) {
			$final_arr[8] = $isMultiple;
		}
		$log->debug ("Exiting getOutputHtml method ...");

		return $final_arr;
	}

	/** This function returns the vtiger_invoice object populated with the details from sales order object.
	 * Param $focus - Invoice object
	 * Param $so_focus - Sales order focus
	 * Param $soid - sales order id
	 * Return type is an object array
	 */

	function getConvertSoToInvoice ($focus, $so_focus, $soid) {
		global $log, $current_user;
		$log->debug ("Entering getConvertSoToInvoice(" . get_class ($focus) . "," . get_class ($so_focus) . "," . $soid . ") method ...");
		$log->info ("in getConvertSoToInvoice " . $soid);
		$xyz = array ('bill_street', 'bill_city', 'bill_code', 'bill_pobox', 'bill_country', 'bill_state', 'ship_street', 'ship_city', 'ship_code', 'ship_pobox', 'ship_country', 'ship_state');
		for ($i = 0; $i < count ($xyz); $i++) {
			if (getFieldVisibilityPermission ('SalesOrder', $current_user->id, $xyz[ $i ]) == '0') {
				$so_focus->column_fields[ $xyz[ $i ] ] = $so_focus->column_fields[ $xyz[ $i ] ];
			} else {
				$so_focus->column_fields[ $xyz[ $i ] ] = '';
			}
		}
		$focus->column_fields['salesorder_id']    = $soid;
		$focus->column_fields['subject']          = $so_focus->column_fields['subject'];
		$focus->column_fields['customerno']       = $so_focus->column_fields['customerno'];
		$focus->column_fields['duedate']          = $so_focus->column_fields['duedate'];
		$focus->column_fields['contact_id']       = $so_focus->column_fields['contact_id'];//to include contact name in Invoice
		$focus->column_fields['account_id']       = $so_focus->column_fields['account_id'];
		$focus->column_fields['exciseduty']       = $so_focus->column_fields['exciseduty'];
		$focus->column_fields['salescommission']  = $so_focus->column_fields['salescommission'];
		$focus->column_fields['purchaseorder']    = $so_focus->column_fields['purchaseorder'];
		$focus->column_fields['bill_street']      = $so_focus->column_fields['bill_street'];
		$focus->column_fields['ship_street']      = $so_focus->column_fields['ship_street'];
		$focus->column_fields['bill_city']        = $so_focus->column_fields['bill_city'];
		$focus->column_fields['ship_city']        = $so_focus->column_fields['ship_city'];
		$focus->column_fields['bill_state']       = $so_focus->column_fields['bill_state'];
		$focus->column_fields['ship_state']       = $so_focus->column_fields['ship_state'];
		$focus->column_fields['bill_code']        = $so_focus->column_fields['bill_code'];
		$focus->column_fields['ship_code']        = $so_focus->column_fields['ship_code'];
		$focus->column_fields['bill_country']     = $so_focus->column_fields['bill_country'];
		$focus->column_fields['ship_country']     = $so_focus->column_fields['ship_country'];
		$focus->column_fields['bill_pobox']       = $so_focus->column_fields['bill_pobox'];
		$focus->column_fields['ship_pobox']       = $so_focus->column_fields['ship_pobox'];
		$focus->column_fields['description']      = $so_focus->column_fields['description'];
		$focus->column_fields['terms_conditions'] = $so_focus->column_fields['terms_conditions'];
		$focus->column_fields['currency_id']      = $so_focus->column_fields['currency_id'];
		$focus->column_fields['conversion_rate']  = $so_focus->column_fields['conversion_rate'];

		$log->debug ("Exiting getConvertSoToInvoice method ...");
		return $focus;
	}

	/** This function returns the vtiger_invoice object populated with the details from quote object.
	 * Param $focus - Invoice object
	 * Param $quote_focus - Quote order focus
	 * Param $quoteid - quote id
	 * Return type is an object array
	 */

	function getConvertQuoteToInvoice ($focus, $quote_focus, $quoteid) {
		global $log, $current_user;
		$log->debug ("Entering getConvertQuoteToInvoice(" . get_class ($focus) . "," . get_class ($quote_focus) . "," . $quoteid . ") method ...");
		$log->info ("in getConvertQuoteToInvoice " . $quoteid);
		$xyz = array ('bill_street', 'bill_city', 'bill_code', 'bill_pobox', 'bill_country', 'bill_state', 'ship_street', 'ship_city', 'ship_code', 'ship_pobox', 'ship_country', 'ship_state');
		for ($i = 0; $i < 12; $i++) {
			if (getFieldVisibilityPermission ('Quotes', $current_user->id, $xyz[ $i ]) == '0') {
				$quote_focus->column_fields[ $xyz[ $i ] ] = $quote_focus->column_fields[ $xyz[ $i ] ];
			} else {
				$quote_focus->column_fields[ $xyz[ $i ] ] = '';
			}
		}
		$focus->column_fields['subject']          = $quote_focus->column_fields['subject'];
		$focus->column_fields['account_id']       = $quote_focus->column_fields['account_id'];
		$focus->column_fields['bill_street']      = $quote_focus->column_fields['bill_street'];
		$focus->column_fields['ship_street']      = $quote_focus->column_fields['ship_street'];
		$focus->column_fields['bill_city']        = $quote_focus->column_fields['bill_city'];
		$focus->column_fields['ship_city']        = $quote_focus->column_fields['ship_city'];
		$focus->column_fields['bill_state']       = $quote_focus->column_fields['bill_state'];
		$focus->column_fields['ship_state']       = $quote_focus->column_fields['ship_state'];
		$focus->column_fields['bill_code']        = $quote_focus->column_fields['bill_code'];
		$focus->column_fields['ship_code']        = $quote_focus->column_fields['ship_code'];
		$focus->column_fields['bill_country']     = $quote_focus->column_fields['bill_country'];
		$focus->column_fields['ship_country']     = $quote_focus->column_fields['ship_country'];
		$focus->column_fields['bill_pobox']       = $quote_focus->column_fields['bill_pobox'];
		$focus->column_fields['ship_pobox']       = $quote_focus->column_fields['ship_pobox'];
		$focus->column_fields['description']      = $quote_focus->column_fields['description'];
		$focus->column_fields['terms_conditions'] = $quote_focus->column_fields['terms_conditions'];
		$focus->column_fields['currency_id']      = $quote_focus->column_fields['currency_id'];
		$focus->column_fields['conversion_rate']  = $quote_focus->column_fields['conversion_rate'];

		$log->debug ("Exiting getConvertQuoteToInvoice method ...");
		return $focus;
	}

	/** This function returns the sales order object populated with the details from quote object.
	 * Param $focus - Sales order object
	 * Param $quote_focus - Quote order focus
	 * Param $quoteid - quote id
	 * Return type is an object array
	 */

	function getConvertQuoteToSoObject ($focus, $quote_focus, $quoteid) {
		global $log, $current_user;
		$log->debug ("Entering getConvertQuoteToSoObject(" . get_class ($focus) . "," . get_class ($quote_focus) . "," . $quoteid . ") method ...");
		$log->info ("in getConvertQuoteToSoObject " . $quoteid);
		$xyz = array ('bill_street', 'bill_city', 'bill_code', 'bill_pobox', 'bill_country', 'bill_state', 'ship_street', 'ship_city', 'ship_code', 'ship_pobox', 'ship_country', 'ship_state');
		for ($i = 0; $i < 12; $i++) {
			if (getFieldVisibilityPermission ('Quotes', $current_user->id, $xyz[ $i ]) == '0') {
				$quote_focus->column_fields[ $xyz[ $i ] ] = $quote_focus->column_fields[ $xyz[ $i ] ];
			} else {
				$quote_focus->column_fields[ $xyz[ $i ] ] = '';
			}
		}
		$focus->column_fields['quote_id']         = $quoteid;
		$focus->column_fields['subject']          = $quote_focus->column_fields['subject'];
		$focus->column_fields['contact_id']       = $quote_focus->column_fields['contact_id'];
		$focus->column_fields['potential_id']     = $quote_focus->column_fields['potential_id'];
		$focus->column_fields['account_id']       = $quote_focus->column_fields['account_id'];
		$focus->column_fields['carrier']          = $quote_focus->column_fields['carrier'];
		$focus->column_fields['bill_street']      = $quote_focus->column_fields['bill_street'];
		$focus->column_fields['ship_street']      = $quote_focus->column_fields['ship_street'];
		$focus->column_fields['bill_city']        = $quote_focus->column_fields['bill_city'];
		$focus->column_fields['ship_city']        = $quote_focus->column_fields['ship_city'];
		$focus->column_fields['bill_state']       = $quote_focus->column_fields['bill_state'];
		$focus->column_fields['ship_state']       = $quote_focus->column_fields['ship_state'];
		$focus->column_fields['bill_code']        = $quote_focus->column_fields['bill_code'];
		$focus->column_fields['ship_code']        = $quote_focus->column_fields['ship_code'];
		$focus->column_fields['bill_country']     = $quote_focus->column_fields['bill_country'];
		$focus->column_fields['ship_country']     = $quote_focus->column_fields['ship_country'];
		$focus->column_fields['bill_pobox']       = $quote_focus->column_fields['bill_pobox'];
		$focus->column_fields['ship_pobox']       = $quote_focus->column_fields['ship_pobox'];
		$focus->column_fields['description']      = $quote_focus->column_fields['description'];
		$focus->column_fields['terms_conditions'] = $quote_focus->column_fields['terms_conditions'];
		$focus->column_fields['currency_id']      = $quote_focus->column_fields['currency_id'];
		$focus->column_fields['conversion_rate']  = $quote_focus->column_fields['conversion_rate'];

		$log->debug ("Exiting getConvertQuoteToSoObject method ...");
		return $focus;
	}

	/** This function returns the detail block information of a record for given block id.
	 * Param $module - module name
	 * Param $block - block name
	 * Param $mode - view type (detail/edit/create)
	 * Param $col_fields - vtiger_fields array
	 * Param $tabid - vtiger_tab id
	 * Param $info_type - information type (basic/advance) default ""
	 * Return type is an object array
	 */

	function getBlockInformation ($module, $result, $col_fields, $tabid, $block_label, $mode) {
		global $log, $adb;
		$log->debug ("Entering getBlockInformation(" . $module . "," . $result . "," . $col_fields . "," . $tabid . "," . $block_label . ") method ...");
	
		$editview_arr     = array ();

		$noofrows = $adb->num_rows ($result);
		for ($i = 0; $i < $noofrows; $i++) {
			$fieldtablename = $adb->query_result ($result, $i, 'tablename');
			$fieldcolname   = $adb->query_result ($result, $i, 'columnname');
			$uitype         = $adb->query_result ($result, $i, 'uitype');
			$fieldname      = $adb->query_result ($result, $i, 'fieldname');
			$fieldlabel     = $adb->query_result ($result, $i, 'fieldlabel');
			$block          = $adb->query_result ($result, $i, 'block');
			$maxlength      = $adb->query_result ($result, $i, 'maximumlength');
			$generatedtype  = $adb->query_result ($result, $i, 'generatedtype');
			$typeofdata     = $adb->query_result ($result, $i, 'typeofdata');
			$defaultvalue   = $adb->query_result ($result, $i, 'defaultvalue');
			$calculationid  = $adb->query_result ($result, $i, 'paradicional');
			
			// Procesar campos de fecha con expresiones dinámicas
			if ($mode == '' && $uitype == 5 && empty($col_fields[ $fieldname ])) {
				// Si es campo de fecha en modo creación y está vacío
				if (!empty($defaultvalue)) {
					$processed = processDateDefaultValue($defaultvalue);
					$col_fields[ $fieldname ] = $processed;
				} else {
					$col_fields[ $fieldname ] = date('Y-m-d');
				}
			} elseif ($mode == '' && empty($col_fields[ $fieldname ])) {
				// Para otros tipos de campos
				$col_fields[ $fieldname ] = $defaultvalue;
			}

			if (!empty ($calculationid) && $calculationid != null) {
				$platform                 = $_SESSION ['plat'];
				$CalculatedFields         = new CalculatedFieldsUtils ($adb, $platform);
				$lastValue                = $CalculatedFields->getCaculateSystemById (intval ($calculationid));
				$col_fields[ $fieldname ] = number_format ($lastValue, 2, '.', '');
			}

			$custfld = getOutputHtml ($uitype, $fieldname, $fieldlabel, $maxlength, $col_fields, $generatedtype, $module, $mode, $typeofdata);

			$custfld[6]               = obtenerParametrosAdicionales ($adb->query_result ($result, $i, "fieldid"));
			$custfld[7]               = $adb->query_result ($result, $i, "fieldid");
			$editview_arr[ $block ][] = $custfld;
		}

		foreach ($editview_arr as $headerid => $editview_value) {
			$editview_data = Array ();
			for ($i = 0, $j = 0; $i < count ($editview_value); $j++) {
				$key1 = $editview_value[ $i ];
				if (is_array ($editview_value[ $i + 1 ]) && ($key1[0][0] != 19 && $key1[0][0] != 20 && $key1[0][0] != 256)) {
					$key2 = $editview_value[ $i + 1 ];
				} else {
					$key2 = array ();
				}
				if ($key1[0][0] != 19 && $key1[0][0] != 20 && $key1[0][0] != 256) {
					$editview_data[ $j ] = array (0 => $key1, 1 => $key2);
					$i += 2;
				} else {
					$editview_data[ $j ] = array (0 => $key1);
					$i++;
				}
			}
			$editview_arr[ $headerid ] = $editview_data;
		}

		foreach ($block_label as $blockid => $label) {
			if ($editview_arr[ $blockid ] != null) {
				if ($label == '') {
					$returndata[ getTranslatedString ($curBlock, $module) ] = array_merge ((array) $returndata[ getTranslatedString ($curBlock, $module) ], (array) $editview_arr[ $blockid ]);
				} else {
					$curBlock = $label;
					if (is_array ($editview_arr[ $blockid ])) {
						$returndata[ getTranslatedString ($curBlock, $module) ] = array_merge ((array) $returndata[ getTranslatedString ($curBlock, $module) ], (array) $editview_arr[ $blockid ]);
					}
				}
			}
		}
		$log->debug ("Exiting getBlockInformation method ...");
		return $returndata;
	}

	/** This function returns the data type of the vtiger_fields, with vtiger_field label, which is used for javascript validation.
	 * Param $validationData - array of vtiger_fieldnames with datatype
	 * Return type array
	 */

	function split_validationdataArray ($validationData) {
		global $log;
		$log->debug ("Entering split_validationdataArray(" . $validationData . ") method ...");
		$fieldName   = '';
		$fieldLabel  = '';
		$fldDataType = '';
		$rows        = count ($validationData);
		foreach ($validationData as $fldName => $fldLabel_array) {
			if ($fieldName == '') {
				$fieldName = "'" . $fldName . "'";
			} else {
				$fieldName .= ",'" . $fldName . "'";
			}
			foreach ($fldLabel_array as $fldLabel => $datatype) {
				if ($fieldLabel == '') {
					$fieldLabel = "'" . addslashes ($fldLabel) . "'";
				} else {
					$fieldLabel .= ",'" . addslashes ($fldLabel) . "'";
				}
				if ($fldDataType == '') {
					$fldDataType = "'" . $datatype . "'";
				} else {
					$fldDataType .= ",'" . $datatype . "'";
				}
			}
		}
		$data['fieldname']  = $fieldName;
		$data['fieldlabel'] = $fieldLabel;
		$data['datatype']   = $fldDataType;
		$log->debug ("Exiting split_validationdataArray method ...");
		return $data;
	}

	/* Funcion que permite agregar parametros adicionales al campo, botones, ayudas, etc */

	function obtenerParametrosAdicionales ($id) {
		global $adb;

		$sql = "SELECT paradicional FROM vtiger_field WHERE fieldid = ?";

		$result = $adb->pquery ($sql, array ($id), false);

		if ($result) {
			$row = $adb->fetchByAssoc ($result);

			return html_entity_decode ($row['paradicional']);
		}
		return;
	}

	function getRelatedRecords ($related_module) {

		global $adb;

		if ($related_module == '-' or $related_module == '') {
			return;
		}

		$focus = CRMEntity::getInstance ($related_module);

		if ($focus) {
			$query = getListQuery ($related_module);

			$result = $adb->query ($query);

			while ($resultrow = $adb->fetch_array ($result)) {
				$arr[ $resultrow[ $focus->table_index ] ] = $resultrow[ $focus->def_basicsearch_col ];
			}
		}

		return $arr;
	}

	function getRelatedTaskModules () {
		global $adb;

		$sql = "SELECT name FROM vtiger_relatedtaskmodules INNER JOIN vtiger_tab USING(tabid) WHERE presence IN (0,2)";

		$adb->setDieOnError (false);
		$result = $adb->query ($sql, false);
		$adb->setDieOnError (true);

		if ($result) {
			while ($row = $adb->fetch_array ($result)) {
				$res[] = $row['name'];
			}
		}
		return $res;
	}

?>