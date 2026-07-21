<?php
	require_once ('include/platzilla/Managers/AppFieldManager.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('include/ComboUtil.php'); //new
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/TableFieldUtils.php');
	require_once ('vtlib/Vtiger/Language.php');
	require_once ('modules/PickList/PickListUtils.php');

	/** This function returns the detail view form vtiger_field and and its properties in array format.
	 * Param $uitype - UI type of the vtiger_field
	 * Param $fieldname - Form vtiger_field name
	 * Param $fieldlabel - Form vtiger_field label name
	 * Param $col_fields - array contains the vtiger_fieldname and values
	 * Param $generatedtype - Field generated type (default is 1)
	 * Param $tabid - vtiger_tab id to which the Field belongs to (default is "")
	 * Return type is an array
	 */
	function getDetailViewOutputHtml ($uitype, $fieldname, $fieldlabel, $col_fields, $generatedtype, $tabid = '', $module = '', $fieldid = 0, $typeOfData = null) {
		global $log;

		global $adb;
		global $mod_strings;
		global $app_strings;
		global $current_user;
		global $currentModule;
		global $theme;
		global $demoMode;
		$numberingHelper = NumberHelper::getInstance ($adb, $current_user);
		$theme_path      = "themes/" . $theme . "/";
		$image_path      = $theme_path . "images/";
		$fieldlabel      = from_html ($fieldlabel);
		$custfld         = '';
		$value           = '';
		$label_fld       = Array ();

		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');
		// vtlib customization: New uitype to handle relation between modules
		if ($uitype == 100) {
			// Related list as a field in a block
			ob_start ();
			$modObj     = CRMEntity::getInstance ($module);
			$ajaxaction = 'LOADRELATEDLIST';
			list($_REQUEST['relation_id'], $_REQUEST['header'], $_REQUEST['actions']) = $adb->fetch_row ($adb->pquery ("SELECT relation_id, label, actions FROM vtiger_relatedlists WHERE related_tabid IN (SELECT tabid FROM vtiger_tab WHERE name='todotasks') AND tabid=?", array ($tabid)));
			$as_uitype = true;
			require_once 'include/ListView/RelatedListViewContents.php';

			writeJsOnce ('RelatedLists');
			$htmlOutput = ob_get_clean ();

			$label_fld = array ('', $htmlOutput);
		} else if ($uitype == 101 && $fieldname != 'reports_to_id') {
			// Progress bar field in a block
			$result = $adb->pquery ("SELECT vtiger_blocks_properties.relmodule, vtiger_blocks_properties.relfieldname FROM vtiger_blocks_properties INNER JOIN vtiger_field ON(blockid=block)
										WHERE fieldname=? AND tabid=?",
				array ($fieldname, $tabid));

			list($relmodule) = $adb->fetch_row ($result);

			$progress  = getProgressBarValue ($fieldname, $tabid, $col_fields['record_id']);
			$color     = getProgressColor ($progress);
			$label_fld = array ('', array ('progress' => round ($progress * 100), 'color' => $color, 'relmodule' => $relmodule));
		} elseif ($uitype == 108) {
			$fieldlabel       = getTranslatedString ($fieldlabel, $module);
			$tojson           = html_entity_decode ($col_fields[ $fieldname ]);
			$jvalues          = json_decode ($tojson, true);
			$jvalues['width'] = $jvalues['max'] != 0 ? $jvalues['ini'] * 100 / $jvalues['max'] : 0;
			$label_fld        = array ($fieldlabel, $jvalues);
		} else if ($uitype == '10' || $uitype == 404) {
			$bRelated   = false;
			$fieldlabel = getTranslatedString ($fieldlabel, $module);

			$parent_id     = $col_fields[ $fieldname ];
			$adbBak        = clone $GLOBALS['adb'];
			$parameters    = html_entity_decode (obtenerValorVariable ($fieldname, $module), ENT_QUOTES);
			$lstParameters = unserialize ($parameters);

			if (isset($lstParameters['relplat']) && !empty($lstParameters['relplat'])) {
				unset($GLOBALS['adb']);
				$GLOBALS['adb'] = conectaPlataformaHija ($lstParameters['relplat']);
				$bRelated       = true;
			}

			if (!empty($parent_id)) {
				$parent_module = getSalesEntityType ($parent_id);
				//Fix for detailview users
				if ($fieldname == 'user_id') {
					$parent_module = 'Users';
				}
				$valueTitle = $parent_module;

				if ($app_strings[ $valueTitle ]) {
					$valueTitle = $app_strings[ $valueTitle ];
				}

				$displayValueArray = getEntityName ($parent_module, $parent_id);
				if (!empty($displayValueArray)) {
					foreach ($displayValueArray as $key => $value) {
						$displayValue = $value;
					}
				}

				if ($bRelated) {
					$label_fld = array ($fieldlabel, $displayValue);
				} else {
					if ($uitype == '10' && $fieldname == 'videoid') {
						$sql       = "SELECT * FROM vtiger_videos WHERE idvideo='" . $parent_id . "'";
						$q         = $adb->pquery ($sql);
						$video     = $adb->fetchByAssoc ($q);
						$label_fld = array (
							$fieldlabel,
							"<a href='index.php?module=video&action=index&record=$parent_id' title='" . $valueTitle . "'>" . $video['file'] . "</a>",
						);
					} else {
						$titleAttr = (strlen($displayValue) >= 150) ? " title='$displayValue'" : "";
						$label_fld = array (
							$fieldlabel,
							"<a href='index.php?module=$parent_module&action=DetailView&record=$parent_id'$titleAttr>$displayValue</a>",
						);
					}
				}
			} else {
				$moduleSpecificMessage = 'MODULE_NOT_SELECTED';
				if (isset($mod_strings[$moduleSpecificMessage]) && $mod_strings[$moduleSpecificMessage] != "") {
					$moduleSpecificMessage = $mod_strings[$moduleSpecificMessage];
				}
				$label_fld = array ($fieldlabel, '');
			}

			if (isset($lstParameters['relplat']) && !empty($lstParameters['relplat'])) {
				$adb->disconnect ();
				unset($adb);

				$GLOBALS['adb'] = clone $adbBak;
				unset($adbBak);
			}
		} // END
		else if ($uitype == 99) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
			if ($fieldname == 'confirm_password') {
				return null;
			}
		} elseif ($uitype == 116 || $uitype == 117) {
			$label_fld[]    = getTranslatedString ($fieldlabel, $module);
			$label_fld[]    = getCurrencyName ($col_fields[ $fieldname ]);
			$pick_query     = "SELECT * FROM vtiger_currency_info WHERE currency_status = 'Active' AND deleted=0";
			$pickListResult = $adb->pquery ($pick_query, array ());
			$noofpickrows   = $adb->num_rows ($pickListResult);

			//Mikecrowe fix to correctly default for custom pick lists
			$options = array ();
			$found   = false;
			for ($j = 0; $j < $noofpickrows; $j++) {
				$pickListValue = $adb->query_result ($pickListResult, $j, 'currency_name');
				$currency_id   = $adb->query_result ($pickListResult, $j, 'id');
				if ($col_fields[ $fieldname ] == $currency_id) {
					$chk_val = "selected";
					$found   = true;
				} else {
					$chk_val = '';
				}
				$options[ $currency_id ] = array ($pickListValue => $chk_val);
			}
			$label_fld ["options"] = $options;
		} elseif ($uitype == 13 || $uitype == 104) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
		} elseif ($uitype == 16) {
			require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
			$picklist = GlobalPicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldname);
			$options  = array ();
			if (empty ($picklist)) {
				$label_fld[]           = getTranslatedString ($fieldlabel, $module);
				$label_fld[]           = null;
				$label_fld ['options'] = null;
			} else if (!$picklist->isMultiple ()) {
				$picklistValues = $picklist->getValues ();
				if (!empty ($picklistValues)) {
					foreach ($picklistValues as $picklistValue) {
						$options [] = array (
							$picklistValue->getValue (),
							$picklistValue->getValue (),
							$picklistValue->getValue () == $col_fields [ $fieldname ] ? 'selected' : '',
							$picklist->isMultiple (),
						);
					}
				}
				$label_fld[]           = getTranslatedString ($fieldlabel, $module);
				$label_fld[]           = getTranslatedString ($col_fields[ $fieldname ], $module);
				$label_fld ['options'] = $options;
			} else {
				$dummy = explode (' |##| ', $col_fields[ $fieldname ]);
				$picklistValues = $picklist->getValues ();
				if (!empty ($picklistValues)) {
					foreach ($picklistValues as $picklistValue) {
						$options [] = array (
							$picklistValue->getValue (),
							$picklistValue->getValue (),
							in_array ($picklistValue->getValue (), $dummy) ? 'selected' : '',
							$picklist->isMultiple (),
						);
					}
				}
				$label_fld[] = getTranslatedString ($fieldlabel, $module);
				$label_fld[] = str_ireplace (' |##| ', ', ', $col_fields[ $fieldname ]);
				$label_fld ['options'] = $options;
			}
		} elseif ($uitype == 15) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = getTranslatedString ($col_fields[ $fieldname ]);
			$roleid      = $current_user->roleid;
			$value       = $col_fields[ $fieldname ];
			$valueArr       = explode ("|##|", $col_fields[ $fieldname ]);
			$picklistValues = getAssignedPicklistValues ($fieldname, $roleid, $adb);

			//Mikecrowe fix to correctly default for custom pick lists
			$options = array ();
			$count   = 0;
			$pickcount = 0;
			$found   = false;
			if (!empty($picklistValues)) {
				foreach ($picklistValues as $order => $pickListValue) {
					if (in_array (trim ($pickListValue), array_map ("trim", $valueArr))) {
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

				if ($pickcount == 0 && !empty($value)) {
					// Verificar si el valor existe en la tabla de picklist
					if (picklistValueExistsInDetailView($adb, $fieldname, $value)) {
						// El valor existe pero el usuario no tiene permiso para verlo
						$options[] = array ($app_strings['LBL_NOT_ACCESSIBLE'], $value, 'selected');
					} else {
						// El valor no existe en la tabla (valor obsoleto/huérfano)
						$translatedValue = getTranslatedString($value, $module);
						$obsoleteLabel = "<font color='red'>" . $translatedValue . "</font>";
						$options[] = array ($obsoleteLabel, $value, 'selected');
					}
				}
			}
			$label_fld ["options"] = $options;
		} elseif ($uitype == 115) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = getTranslatedString ($col_fields[ $fieldname ]);

			$pick_query     = "SELECT * FROM vtiger_" . $adb->sql_escape_string ($fieldname);
			$pickListResult = $adb->pquery ($pick_query, array ());
			$noofpickrows   = $adb->num_rows ($pickListResult);
			$options        = array ();
			$found          = false;
			for ($j = 0; $j < $noofpickrows; $j++) {
				$pickListValue = $adb->query_result ($pickListResult, $j, strtolower ($fieldname));

				if ($col_fields[ $fieldname ] == $pickListValue) {
					$chk_val = "selected";
					$found   = true;
				} else {
					$chk_val = '';
				}
				$options[] = array ($pickListValue => $chk_val);
			}
			$label_fld ["options"] = $options;
		} elseif ($uitype == 33) { //uitype 33 added for multiselector picklist - Jeri
			$roleid      = $current_user->roleid;
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = str_ireplace (' |##| ', ', ', $col_fields[ $fieldname ]);

			$picklistValues = getAssignedPicklistValues ($fieldname, $roleid, $adb);

			$options          = array ();
			$selected_entries = Array ();
			$selected_entries = explode (' |##| ', $col_fields[ $fieldname ]);

			if (!empty($picklistValues)) {
				foreach ($picklistValues as $order => $pickListValue) {
					foreach ($selected_entries as $selected_entries_value) {
						if (trim ($selected_entries_value) == trim ($pickListValue)) {
							$chk_val = 'selected';
							$pickcount++;
							break;
						} else {
							$chk_val = '';
						}
					}
					if (isset($_REQUEST['file']) && $_REQUEST['file'] == 'QuickCreate') {
						$options[] = array (htmlentities (getTranslatedString ($pickListValue), ENT_QUOTES, $default_charset), $pickListValue, $chk_val);
					} else {
						$options[] = array (getTranslatedString ($pickListValue), $pickListValue, $chk_val);
					}
				}
				if ($pickcount == 0 && !empty($value)) {
					// Verificar si el valor existe en la tabla de picklist
					if (picklistValueExistsInDetailView($adb, $fieldname, trim($selected_entries_value))) {
						// El valor existe pero el usuario no tiene permiso para verlo
						$not_access_lbl = "<font color='red'>" . $app_strings['LBL_NOT_ACCESSIBLE'] . "</font>";
						$options[] = array ($not_access_lbl, trim ($selected_entries_value), 'selected');
					} else {
						// El valor no existe en la tabla (valor obsoleto/huérfano)
						$translatedValue = getTranslatedString(trim($selected_entries_value), $module);
						$obsoleteLabel = "<font color='red'>" . $translatedValue . "</font>";
						$options[] = array ($obsoleteLabel, trim ($selected_entries_value), 'selected');
					}
				}
			}
			$label_fld ["options"] = $options;
		} elseif ($uitype == 17) {
			$label_fld[]  = getTranslatedString ($fieldlabel, $module);
			$matchPattern = "^[\w]+:\/\/^";
			$value        = $col_fields[ $fieldname ];
			preg_match ($matchPattern, $value, $matches);
			if (!empty ($matches[0])) {
				$fieldValue  = str_replace ($matches, "", $value);
				$label_fld[] = $value;
			} else {
				if ($value != null) {
					$label_fld[] = 'http://' . $value;
				} else {
					$label_fld[] = '';
				}
			}
		} elseif ($uitype == 19) {
			if ($fieldname == 'notecontent') {
				$col_fields[ $fieldname ] = decode_html ($col_fields[ $fieldname ]);
			} else {
				$col_fields[ $fieldname ] = str_replace ("&lt;br /&gt;", "<br>", $col_fields[ $fieldname ]);
			}
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
		} elseif ($uitype == 20 || $uitype == 21 || $uitype == 22 || $uitype == 24) { // Armando LC<scher 11.08.2005 -> B'descriptionSpan -> Desc: removed $uitype == 19 and made an aditional elseif above
			if ($uitype == 20)//Fix the issue #4680
			{
				$col_fields[ $fieldname ] = $col_fields[ $fieldname ];
			} else {
				$col_fields[ $fieldname ] = nl2br ($col_fields[ $fieldname ]);
			}
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
		} elseif ($uitype == 52 || $uitype == 77 || $uitype == 101 || $uitype == 407) {
			$label_fld[]      = getTranslatedString ($fieldlabel, $module);
			$user_id          = $col_fields[ $fieldname ];
			$assigned_user_id = $current_user->id;
			$valueArr         = explode (" |##| ", $user_id);
			$texto            = "";
			foreach ($valueArr as $key => $userid) {
				if ($key > 0) {
					$texto .= ", ";
				}
				$user_name = getOwnerName (trim ($userid));
				if (is_admin ($current_user)) {
					$texto .= '<a href="index.php?module=Users&action=DetailView&record=' . $userid . '">' . $user_name . '</a>';
				} else {
					$texto .= $user_name;
				}
			}
			$label_fld[] = $texto;
			if ($is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module) ] == 0)) {
				$user_array = get_user_array (false, "Active", $assigned_user_id, 'private');
				$users_combo = get_select_options_array ($user_array, $assigned_user_id);
			} else {
				$user_array = get_user_array (false, "Active", $user_id);
				$users_combo = get_select_options_array ($user_array, $assigned_user_id);
			}
			$label_fld ["options"] = $users_combo;
		} elseif ($uitype == 11) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
		} elseif ($uitype == 53) {
			global $noof_group_rows, $adb;
			$owner_id = $col_fields[ $fieldname ];

			$user   = 'no';
			$result = $adb->pquery ("SELECT count(*) AS count FROM vtiger_users WHERE id = ?", array ($owner_id));
			if ($adb->query_result ($result, 0, 'count') > 0) {
				$user = 'yes';
			}

			$owner_name  = getOwnerName ($owner_id);
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $owner_name;

			if (is_admin ($current_user)) {
				$label_fld["secid"][] = $owner_id;
				if ($user == 'no') {
					$label_fld["link"][] = "index.php?module=Settings&action=GroupDetailView&groupId=" . $owner_id;
				} else {
					$label_fld["link"][] = "index.php?module=Users&action=DetailView&record=" . $owner_id;
				}
				//$label_fld["secid"][] = $groupid;
				//$label_fld["link"][] = "index.php?module=Settings&action=GroupDetailView&groupId=".$groupid;
			}

			//Security Checks
			if ($fieldname == 'assigned_user_id' && $is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module_name) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module_name) ] == 0)) {
				$result = get_current_user_access_groups ($module_name);
			} else {
				$result = get_group_options ();
			}
			if ($result) {
				$nameArray = $adb->fetch_array ($result);
			}

			global $current_user;
			//$value = $user_id;
			if ($owner_id != '') {
				if ($user == 'yes') {
					$label_fld ["options"][] = 'User';
					$assigned_user_id        = $owner_id;
					$user_checked            = "checked";
					$team_checked            = '';
					$user_style              = 'display:block';
					$team_style              = 'display:none';
				} else {
					//$record = $col_fields["record_id"];
					//$module = $col_fields["record_module"];
					$label_fld ["options"][] = 'Group';
					$assigned_group_id       = $owner_id;
					$user_checked            = '';
					$team_checked            = 'checked';
					$user_style              = 'display:none';
					$team_style              = 'display:block';
				}
			} else {
				$label_fld ["options"][] = 'User';
				$assigned_user_id        = $current_user->id;
				$user_checked            = "checked";
				$team_checked            = '';
				$user_style              = 'display:block';
				$team_style              = 'display:none';
			}

			if ($fieldname == 'assigned_user_id' && $is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module) ] == 0)) {
				$user_array = get_user_array (false, "Active", $current_user->id, 'private');
				$users_combo = get_select_options_array ($user_array, $assigned_user_id);
			} else {
				$user_array = get_user_array (false, "Active", $current_user->id);
				$users_combo = get_select_options_array ($user_array, $assigned_user_id);
			}

			if ($noof_group_rows != 0) {
				if ($fieldname == 'assigned_user_id' && $is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ($module) ] == 3 or $defaultOrgSharingPermission[ getTabid ($module) ] == 0)) {
					$group_array = get_group_array (false, "Active", $current_user->id, 'private');
					$groups_combo = get_select_options_array ($group_array, $current_user->id);
				} else {
					$group_array = get_group_array (false, "Active", $current_user->id);
					$groups_combo = get_select_options_array ($group_array, $current_user->id);
				}
			}

			$label_fld ["options"][] = $users_combo;
			$label_fld ["options"][] = $groups_combo;
		} elseif ($uitype == 55 || $uitype == 255) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$value       = $col_fields[ $fieldname ];
			if ($uitype == 255) {
				global $currentModule;
				$fieldpermission = getFieldVisibilityPermission ($currentModule, $current_user->id, 'firstname');
			}
			if ($uitype == 255 && $fieldpermission == 0 && $fieldpermission != '') {
				$fieldvalue[] = '';
			} else {
				$roleid  = $current_user->roleid;
				$subrole = getRoleSubordinates ($roleid);
				if (count ($subrole) > 0) {
					$roleids = implode ("','", $subrole);
					$roleids = $roleids . "','" . $roleid;
				} else {
					$roleids = $roleid;
				}
				if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
					$pick_query = "SELECT salutationtype FROM vtiger_salutationtype ORDER BY salutationtype";
					$params     = array ();
				} else {
					$pick_query = "SELECT * FROM vtiger_salutationtype LEFT JOIN vtiger_role2picklist ON vtiger_role2picklist.picklistvalueid=vtiger_salutationtype.picklist_valueid WHERE picklistid IN (SELECT picklistid FROM vtiger_picklist WHERE name='salutationtype') AND roleid=? ORDER BY salutationtype";
					$params     = array ($current_user->roleid);
				}
				$pickListResult = $adb->pquery ($pick_query, $params);
				$noofpickrows   = $adb->num_rows ($pickListResult);
				$sal_value      = $col_fields["salutationtype"];
				$salcount       = 0;
				for ($j = 0; $j < $noofpickrows; $j++) {
					$pickListValue = $adb->query_result ($pickListResult, $j, "salutationtype");

					if ($sal_value == $pickListValue) {
						$chk_val = "selected";
						$salcount++;
					} else {
						$chk_val = '';
					}
				}
				if ($salcount == 0 && $sal_value != '') {
					$notacc = $app_strings['LBL_NOT_ACCESSIBLE'];
				}
				$sal_value = $col_fields["salutationtype"];
				if ($sal_value == '--None--') {
					$sal_value = '';
				}
				$label_fld["salut"]     = getTranslatedString ($sal_value);
				$label_fld["notaccess"] = $notacc;
			}
			$label_fld[] = $value;
		} elseif ($uitype == 56) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$value       = $col_fields[ $fieldname ];
			if ($value == 1) {
				//Since "yes" is not been translated it is given as app strings here..
				$displayValue = $app_strings['yes'];
			} else {
				$displayValue = $app_strings['no'];
			}
			$label_fld[] = $displayValue;
		} elseif ($uitype == 58) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$campaign_id = $col_fields[ $fieldname ];
			if ($campaign_id != '') {
				$campaign_name = getCampaignName ($campaign_id);
			}
			$label_fld[]        = $campaign_name;
			$label_fld["secid"] = $campaign_id;
			$label_fld["link"]  = "index.php?module=Campaigns&action=DetailView&record=" . $campaign_id;
		} elseif ($uitype == 61) {
			global $adb;
			$label_fld[] = getTranslatedString ($fieldlabel, $module);

			if ($tabid == 10) {
				$attach_result = $adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($col_fields['record_id']));
				for ($ii = 0; $ii < $adb->num_rows ($attach_result); $ii++) {
					$attachmentid = $adb->query_result ($attach_result, $ii, 'attachmentsid');
					if ($attachmentid != '') {
						$attachquery     = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
						$attachmentsname = $adb->query_result ($adb->pquery ($attachquery, array ($attachmentid)), 0, 'name');
						if ($attachmentsname != '') {
							$custfldval = '<a href = "index.php?module=uploads&action=downloadfile&return_module=' . $col_fields['record_module'] . '&fileid=' . $attachmentid . '&entityid=' . $col_fields['record_id'] . '">' . $attachmentsname . '</a>';
						} else {
							$custfldval = '';
						}
					}
					$label_fld['options'][] = $custfldval;
				}
			} else {
				$attachmentid = $adb->query_result ($adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($col_fields['record_id'])), 0, 'attachmentsid');
				if ($col_fields[ $fieldname ] == '' && $attachmentid != '') {
					$attachquery              = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
					$col_fields[ $fieldname ] = $adb->query_result ($adb->pquery ($attachquery, array ($attachmentid)), 0, 'name');
				}

				//This is added to strip the crmid and _ from the file name and show the original filename
				//$org_filename = ltrim($col_fields[$fieldname],$col_fields['record_id'].'_');
				/* Above line is not required as the filename in the database is stored as it is and doesn't have crmid attached to it.
			  This was the cause for the issue reported in ticket #4645 */
				$org_filename = $col_fields[ $fieldname ];
				// For Backward Compatibility version < 5.0.4
				$filename_pos = strpos ($org_filename, $col_fields['record_id'] . '_');
				if ($filename_pos === 0) {
					$start_idx    = $filename_pos + strlen ($col_fields['record_id'] . '_');
					$org_filename = substr ($org_filename, $start_idx);
				}
				if ($org_filename != '') {
					if ($col_fields['filelocationtype'] == 'E') {
						if ($col_fields['filestatus'] == 1) {//&& strlen($col_fields['filename']) > 7  ){
							$custfldval = '<a target="_blank" href =' . $col_fields['filename'] . ' onclick=\'javascript:dldCntIncrease(' . $col_fields['record_id'] . ');\'>' . $col_fields[ $fieldname ] . '</a>';
						} else {
							$custfldval = $col_fields[ $fieldname ];
						}
					} elseif ($col_fields['filelocationtype'] == 'I') {
						if ($col_fields['filestatus'] == 1) {
							$custfldval = '<a href = "index.php?module=uploads&action=downloadfile&return_module=' . $col_fields['record_module'] . '&fileid=' . $attachmentid . '&entityid=' . $col_fields['record_id'] . '" onclick=\'javascript:dldCntIncrease(' . $col_fields['record_id'] . ');\'>' . $col_fields[ $fieldname ] . '</a>';
						} else {
							$custfldval = $col_fields[ $fieldname ];
						}
					} else {
						$custfldval = '';
					}
				}
				$label_fld[] = $custfldval;
			}
		} elseif ($uitype == 28) {
			$label_fld[]  = getTranslatedString ($fieldlabel, $module);
			$attachmentid = $adb->query_result ($adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($col_fields['record_id'])), 0, 'attachmentsid');
			if ($col_fields[ $fieldname ] == '' && $attachmentid != '') {
				$attachquery              = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
				$col_fields[ $fieldname ] = $adb->query_result ($adb->pquery ($attachquery, array ($attachmentid)), 0, 'name');
			}
			$org_filename = $col_fields[ $fieldname ];
			// For Backward Compatibility version < 5.0.4
			$filename_pos = strpos ($org_filename, $col_fields['record_id'] . '_');
			if ($filename_pos === 0) {
				$start_idx    = $filename_pos + strlen ($col_fields['record_id'] . '_');
				$org_filename = substr ($org_filename, $start_idx);
			}
			if ($org_filename != '') {
				if ($col_fields['filelocationtype'] == 'E') {
					if ($col_fields['filestatus'] == 1) {//&& strlen($col_fields['filename']) > 7  ){
						$custfldval = '<a target="_blank" href =' . $col_fields['filename'] . ' onclick=\'javascript:dldCntIncrease(' . $col_fields['record_id'] . ');\'>' . $col_fields[ $fieldname ] . '</a>';
					} else {
						$custfldval = $col_fields[ $fieldname ];
					}
				} elseif ($col_fields['filelocationtype'] == 'I') {
					if ($col_fields['filestatus'] == 1) {
						$custfldval = '<a href = "index.php?module=uploads&action=downloadfile&return_module=' . $col_fields['record_module'] . '&fileid=' . $attachmentid . '&entityid=' . $col_fields['record_id'] . '" onclick=\'javascript:dldCntIncrease(' . $col_fields['record_id'] . ');\'>' . $col_fields[ $fieldname ] . '</a>';
					} else {
						$custfldval = $col_fields[ $fieldname ];
					}
				} else {
					$custfldval = '';
				}
			}
			$label_fld[] = $custfldval;
		} elseif ($uitype == 105) {//Added for user image
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			//$imgpath = getModuleFileStoragePath('Contacts').$col_fields[$fieldname];
			$sql        = "SELECT vtiger_attachments.* FROM vtiger_attachments LEFT JOIN vtiger_salesmanattachmentsrel ON vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid WHERE vtiger_salesmanattachmentsrel.smid=?";
			$image_res  = $adb->pquery ($sql, array ($col_fields['record_id']));
			$image_id   = $adb->query_result ($image_res, 0, 'attachmentsid');
			$image_path = $adb->query_result ($image_res, 0, 'path');
			$image_name = $adb->query_result ($image_res, 0, 'name');
			$imgpath    = $image_path . $image_id . "_" . $image_name;
			if ($image_name != '') {
				//Added the following check for the image to retain its in original size.
				list($pro_image_width, $pro_image_height) = getimagesize (decode_html ($imgpath));
				$label_fld[] = '<a href="' . $imgpath . '" target="_blank"><img src="' . $imgpath . '" width="' . $pro_image_width . '" height="' . $pro_image_height . '" alt="' . $col_fields['user_name'] . '" title="' . $col_fields['user_name'] . '" border="0"></a>';
			} else {
				$label_fld[] = '';
			}
		} elseif ($uitype == 256) {
			$col_fields[ $fieldname ] = str_replace ("\n", "", $col_fields[ $fieldname ]);
			$col_fields[ $fieldname ] = decode_html ($col_fields[ $fieldname ]);
			$label_fld[]              = getTranslatedString ($fieldlabel, $module);
			$label_fld[]              = $col_fields[ $fieldname ];
		} elseif ($uitype == 257 || $uitype == 258) {//Added for user image
			$value       = $col_fields[ $fieldname ];
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$sql         = "SELECT vtiger_attachments.* FROM vtiger_attachments WHERE vtiger_attachments.attachmentsid=?";
			$image_res   = $adb->pquery ($sql, array ($value));
			$image_id    = $adb->query_result ($image_res, 0, 'attachmentsid');
			$image_path  = $adb->query_result ($image_res, 0, 'path');
			$image_name  = $adb->query_result ($image_res, 0, 'name');
			$imgpath     = $image_path . $image_id . "_" . $image_name;
			if ($image_name != '' && is_file (__DIR__ . "/../../{$imgpath}")) {
				//Added the following check for the image to retain its in original size.
				list($pro_image_width, $pro_image_height) = getimagesize (decode_html ($imgpath));
				if ($uitype == 258) {
					$label_fld[] = '<a href="' . $imgpath . '" target="_blank"><img src="' . $imgpath . '" width="' . $pro_image_width . '" height="' . $pro_image_height . '" alt="' . $col_fields['user_name'] . '" title="' . $col_fields['user_name'] . '" border="0" style="max-height: 150px; max-width: 150px;"></a>';
				} else {
					$label_fld[] = '<a href="' . $imgpath . '" target="_blank">' . $image_name . '</a>';
				}
			} else {
				$label_fld[] = '';
			}
		} elseif ($uitype == 67) {
			$value = $col_fields[ $fieldname ];
			if ($value == '') {
				$label_fld[] = getTranslatedString ($fieldlabel, $module);
				$label_fld[] = $value;
			}
		} elseif ($uitype == 63) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ] . 'h&nbsp; ' . $col_fields['duration_minutes'] . 'm';
		} elseif ($uitype == 6) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			if ($col_fields[ $fieldname ] == '0') {
				$col_fields[ $fieldname ] = '';
			}
			if ($col_fields['time_start'] != '') {
				$start_time = $col_fields['time_start'];
			}
			$dateValue = $col_fields[ $fieldname ];
			if ($col_fields[ $fieldname ] == '0000-00-00' || empty($dateValue)) {
				$displayValue = '';
			} else {
				if (empty($start_time) && strpos ($col_fields[ $fieldname ], ' ') == false) {
					$displayValue = DateTimeField::convertToUserFormat ($col_fields[ $fieldname ]);
				} else {
					if (!empty($start_time)) {
						$date = new DateTimeField($col_fields[ $fieldname ] . ' ' . $start_time);
					} else {
						$date = new DateTimeField($col_fields[ $fieldname ]);
					}
					$displayValue = $date->getDisplayDateTimeValue ();
				}
			}
			$label_fld[] = $displayValue;
		} elseif ($uitype == 5 || $uitype == 23 || $uitype == 70) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$dateValue   = $col_fields[ $fieldname ];
			if (isset($col_fields['time_end']) && $col_fields['time_end'] != '' && ($tabid == 9 || $tabid == 16) && $uitype == 23) {
				$end_time = $col_fields['time_end'];
			}
			if ($dateValue == '0000-00-00' || empty($dateValue)) {
				$displayValue = '';
			} else {
				if (empty($end_time) && strpos ($dateValue, ' ') == false) {
					$displayValue = DateTimeField::convertToUserFormat ($col_fields[ $fieldname ]);
				} else {
					if (!empty($end_time)) {
						$date = new DateTimeField($col_fields[ $fieldname ] . ' ' . $end_time);
					} else {
						$date = new DateTimeField($col_fields[ $fieldname ]);
					}
					$displayValue = $date->getDisplayDateTimeValue ();
				}
			}
			$label_fld[] = $displayValue;
		} elseif ($uitype == 71 || $uitype == 72) {
			$dummy       = explode ('~', $typeOfData);
			$dummyNumber = explode (',', $dummy[2]);
			$precision   = $dummyNumber [1];
			$label_fld[]   = getTranslatedString ($fieldlabel, $module);
			$currencyField = new CurrencyField($col_fields[ $fieldname ]);
			if ($uitype == 72) {
				// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
				if ($fieldname == 'unit_price') {
					$rate_symbol          = getCurrencySymbolandCRate (getProductBaseCurrency ($col_fields['record_id'], $module));
					$label_fld[]          = $currencyField->getDisplayValue (null, true);
					$label_fld["cursymb"] = $rate_symbol['symbol'];
				} else {
					$currency_info        = getInventoryCurrencyInfo ($module, $col_fields['record_id']);
					$label_fld[]          = $currencyField->getDisplayValue (null, true);
					$label_fld["cursymb"] = $currency_info['currency_symbol'];
				}
			} else {
				$label_fld[]          = $numberingHelper->setNumberFormat ($col_fields[ $fieldname ], $fieldname);
				$label_fld["cursymb"] = $currencyField->getCurrencySymbol ();
			}
		} elseif ($uitype == 75 || $uitype == 81) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$vendor_id   = $col_fields[ $fieldname ];
			if ($vendor_id != '') {
				$vendor_name = getVendorName ($vendor_id);
			}
			$label_fld[]        = $vendor_name;
			$label_fld["secid"] = $vendor_id;
			$label_fld["link"]  = "index.php?module=Vendors&action=DetailView&record=" . $vendor_id;
		} elseif ($uitype == 76) {
			$label_fld[]  = getTranslatedString ($fieldlabel, $module);
			$potential_id = $col_fields[ $fieldname ];
			if ($potential_id != '') {
				$potential_name = getPotentialName ($potential_id);
			}
			$label_fld[]        = $potential_name;
			$label_fld["secid"] = $potential_id;
			$label_fld["link"]  = "index.php?module=Potentials&action=DetailView&record=" . $potential_id;
		} elseif ($uitype == 78) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$quote_id    = $col_fields[ $fieldname ];
			if ($quote_id != '') {
				$quote_name = getQuoteName ($quote_id);
			}
			$label_fld[]        = $quote_name;
			$label_fld["secid"] = $quote_id;
			$label_fld["link"]  = "index.php?module=Quotes&action=DetailView&record=" . $quote_id;
		} elseif ($uitype == 79) {
			$label_fld[]      = getTranslatedString ($fieldlabel, $module);
			$purchaseorder_id = $col_fields[ $fieldname ];
			if ($purchaseorder_id != '') {
				$purchaseorder_name = getPoName ($purchaseorder_id);
			}
			$label_fld[]        = $purchaseorder_name;
			$label_fld["secid"] = $purchaseorder_id;
			$label_fld["link"]  = "index.php?module=PurchaseOrder&action=DetailView&record=" . $purchaseorder_id;
		} elseif ($uitype == 80) {
			$label_fld[]   = getTranslatedString ($fieldlabel, $module);
			$salesorder_id = $col_fields[ $fieldname ];
			if ($salesorder_id != '') {
				$salesorder_name = getSoName ($salesorder_id);
			}
			$label_fld[]        = $salesorder_name;
			$label_fld["secid"] = $salesorder_id;
			$label_fld["link"]  = "index.php?module=SalesOrder&action=DetailView&record=" . $salesorder_id;
		} elseif ($uitype == 30) {
			$rem_days     = 0;
			$rem_hrs      = 0;
			$rem_min      = 0;
			$reminder_str = "";
			$rem_days     = floor ($col_fields[ $fieldname ] / (24 * 60));
			$rem_hrs      = floor (($col_fields[ $fieldname ] - $rem_days * 24 * 60) / 60);
			$rem_min      = ($col_fields[ $fieldname ] - $rem_days * 24 * 60) % 60;

			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			if ($col_fields[ $fieldname ]) {
				$reminder_str = $rem_days . '&nbsp;' . $mod_strings['LBL_DAYS'] . '&nbsp;' . $rem_hrs . '&nbsp;' . $mod_strings['LBL_HOURS'] . '&nbsp;' . $rem_min . '&nbsp;' . $mod_strings['LBL_MINUTES'] . '&nbsp;&nbsp;' . $mod_strings['LBL_BEFORE_EVENT'];
			}
			$label_fld[] = '&nbsp;' . $reminder_str;
		} elseif ($uitype == 98) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			if (is_admin ($current_user)) {
				$label_fld[] = '<a href="index.php?module=Settings&action=RoleDetailView&roleid=' . $col_fields[ $fieldname ] . '">' . getRoleName ($col_fields[ $fieldname ]) . '</a>';
			} else {
				$label_fld[] = getRoleName ($col_fields[ $fieldname ]);
			}
		} elseif ($uitype == 85) { //Added for Skype by Minnie
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
		} elseif ($uitype == 26) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$query       = "SELECT foldername FROM vtiger_attachmentsfolder WHERE folderid = ?";
			$result      = $adb->pquery ($query, array ($col_fields[ $fieldname ]));
			$folder_name = $adb->query_result ($result, 0, "foldername");
			$label_fld[] = $folder_name;
		} elseif ($uitype == 27) {
			if ($col_fields[ $fieldname ] == 'I') {
				$label_fld[] = getTranslatedString ($fieldlabel, $module);
				$label_fld[] = $mod_strings['LBL_INTERNAL'];
			} else {
				$label_fld[] = getTranslatedString ($fieldlabel, $module);
				$label_fld[] = $mod_strings['LBL_EXTERNAL'];
			}
		} elseif ($uitype == 31) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			$label_fld[] = $col_fields[ $fieldname ];
			$options     = array ();
			$themeList   = get_themes ();
			foreach ($themeList as $theme) {
				if ($current_user->theme == $theme) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$options[] = array (getTranslatedString ($theme), $theme, $selected);
			}
			$label_fld ["options"] = $options;
		} elseif ($uitype == 32) {
			$options      = array ();
			$languageList = Vtiger_Language::getAll ();
			$label_fld[]  = getTranslatedString ($fieldlabel, $module);
			$label_fld[]  = (isset($languageList[ $col_fields[ $fieldname ] ])) ?
				$languageList[ $col_fields[ $fieldname ] ] : $col_fields[ $fieldname ];
			foreach ($languageList as $prefix => $label) {
				if ($current_user->language == $prefix) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$options[] = array (getTranslatedString ($label), $prefix, $selected);
			}
			$label_fld ["options"] = $options;
		} elseif ($uitype == 8192) {
			require_once ('include/platzilla/Managers/PipelineManager.php');
			require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');
			$pipeline = PipelineManager::getInstance ($adb)->fetchPipeline ($module, $fieldname);
			$label_fld [] = getTranslatedString ($fieldlabel, $module);
			$label_fld [] = $col_fields[ $fieldname ];
			$pipelineOptions = !empty ($pipeline) ? $pipeline->getValues () : null;

			// Filtrar opciones según dependencia picklist->pipeline (vtiger_picklist2pipeline)
			// Si el campo pipeline tiene un picklist madre configurado, sólo se muestran los
			// valores permitidos para el valor actual de ese picklist en este registro.
			if (!empty ($pipelineOptions)) {
				$p2pRelationships = PicklistPipelineRelationshipManager::getInstance ($adb)
					->fetchPicklistPipelineRelationshipByModule ($module);
				if (!empty ($p2pRelationships)) {
					global $default_charset;
					$allowedValues = null;
					foreach ($p2pRelationships as $rel) {
						if ($rel ['pipelinefieldname'] !== $fieldname) {
							continue;
						}
						$motherField    = $rel ['motherpicklistname'];
						$motherValueRaw = isset ($col_fields [ $motherField ]) ? $col_fields [ $motherField ] : null;
						if (empty ($motherValueRaw)) {
							continue;
						}
						// Los picklists suelen guardarse con entidades HTML (ej. "Campa&ntilde;a")
						// en la tabla del módulo, mientras que motherlistvalue se guarda en UTF-8
						// plano. Comparar contra ambas variantes para evitar falsos negativos.
						$motherValueDec = html_entity_decode ($motherValueRaw, ENT_QUOTES, $default_charset);
						if (($rel ['motherlistvalue'] !== $motherValueDec) && ($rel ['motherlistvalue'] !== $motherValueRaw)) {
							continue;
						}
						$decoded = !empty ($rel ['pipelinevaluesvisible']) ? json_decode ($rel ['pipelinevaluesvisible'], true) : null;
						if (is_array ($decoded)) {
							$allowedValues = $decoded;
							break;
						}
					}
					if (is_array ($allowedValues)) {
						// Intersección preservando el orden original del pipeline
						$filtered = array ();
						foreach ($pipelineOptions as $option) {
							if (in_array ($option, $allowedValues, true)) {
								$filtered [] = $option;
							}
						}
						$pipelineOptions = $filtered;
					}
				}
			}

			$label_fld ['options'] = $pipelineOptions;
		} else if ($uitype == 2208) {
			$tableFieldData = TableFieldUtils::getInstance ($adb)->fetchDataTableField ($col_fields['record_module'], $col_fields['record_id'], $fieldname);
			if (!empty ($tableFieldData[$fieldid])) {
				//var_dump ($tableFieldData[$fieldid]);
				$label_fld[0]          = $fieldlabel;
				$label_fld[1]          = $fieldlabel;
				$label_fld[2]          = 2208;
				$label_fld ['options'] = $tableFieldData;
			}
		} else if ($uitype == 5010) {
			$crmId                = (isset($col_fields['record_id'])) ? $col_fields['record_id'] : null;
			$appFieldClass        = AppFieldManager::getInstance ($adb)->fetchAppFieldByName ($fieldname, $module);
			$handlerClassName     = $appFieldClass->getHandlerClass ();
			$handlerMethodName    = $appFieldClass->getHandlerMethod ();
			$handlerClassFilePath = "modules/{$module}/handlers/{$handlerClassName}.class.php";
			if (!file_exists ($_SERVER['DOCUMENT_ROOT'] . '/' . $handlerClassFilePath)) {
				$label_fld[1] = "No se encuentra la clase gestora {$handlerClassName}";
			}
			require_once ($handlerClassFilePath);
			/** @var  $handler */
			$handler = call_user_func_array (array ($handlerClassName, 'getInstance'), array ($adb));
			if (!is_callable (array ($handler, $handlerMethodName))) {
				$label_fld[1] = "No se encuentra el método {$handlerMethodName} en la clase gestora {$handlerClassName}";
			}
			$label_fld[0]         = $fieldlabel;
			$handlerResult = call_user_func_array (array ($handler, $handlerMethodName), array ($crmId, 'DetailView', $current_user));
			$label_fld[1] = $handlerResult;
			$label_fld[2] = array($fieldname, $fieldlabel, $uitype);
			$label_fld['options'] = $handlerClassName;
			$label_fld['secid'] = '';
			$label_fld['link'] = '';
			$label_fld['cursymb'] = '';
			$label_fld['salut'] = '';
			$label_fld['notaccess'] = '';
			$label_fld['cntimage'] = '';
			$label_fld['isadmin'] = is_admin($current_user);
		} elseif ($uitype == 2202) {
			// Campo GRID - Devuelve estructura vacía para que sea procesado por el template
			$label_fld[] = getTranslatedString($fieldlabel, $module);
			$label_fld[] = ''; // El valor real viene de CAMPOS_TIPO_GRID
			$label_fld[] = array($fieldname, $fieldlabel, $uitype);
		} elseif (!empty($typeOfData)  && in_array ($uitype, array(7, 9, 2206))) {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			
			// Determinar si el valor original era NULL
			$originalValue = $col_fields[$fieldname];
			$is_null_value = ($originalValue === null || $originalValue === '');
			
			$formattedValue = $numberingHelper->setNumberFormat ($originalValue, $fieldname);
			
			$label_fld [] = $formattedValue;
			// Pasar bandera adicional para que el template pueda distinguir NULL de 0
			$label_fld["is_null_value"] = $is_null_value;
		} else {
			$label_fld[] = getTranslatedString ($fieldlabel, $module);
			if ($col_fields[ $fieldname ] == '0' && $uitype != 7 && $fieldname != 'filedownloadcount' && $fieldname != 'filestatus' && $fieldname != 'filesize') {
				$col_fields[ $fieldname ] = '';
			}
			//code for Documents module :start
			if ($tabid == 8) {
				$downloadtype = $col_fields['filelocationtype'];
				if ($fieldname == 'filename') {
					if ($downloadtype == 'I') {
						//$file_value = $mod_strings['LBL_INTERNAL'];
						$fld_value = $col_fields['filename'];
						$ext_pos   = strrpos ($fld_value, ".");
						$ext       = substr ($fld_value, $ext_pos + 1);
						$ext       = strtolower ($ext);
						if ($ext == 'bin' || $ext == 'exe' || $ext == 'rpm') {
							$fileicon = "<img src='" . vtiger_imageurl ('fExeBin.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
						} elseif ($ext == 'jpg' || $ext == 'gif' || $ext == 'bmp') {
							$fileicon = "<img src='" . vtiger_imageurl ('fbImageFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
						} elseif ($ext == 'txt' || $ext == 'doc' || $ext == 'xls') {
							$fileicon = "<img src='" . vtiger_imageurl ('fbTextFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
						} elseif ($ext == 'zip' || $ext == 'gz' || $ext == 'rar') {
							$fileicon = "<img src='" . vtiger_imageurl ('fbZipFile.gif', $theme) . "' hspace='3' align='absmiddle'	border='0'>";
						} else {
							$fileicon = "<img src='" . vtiger_imageurl ('fbUnknownFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
						}
					} else {
						$fld_value = $col_fields['filename'];
						$fileicon  = "<img src='" . vtiger_imageurl ('fbLink.gif', $theme) . "' alt='" . $mod_strings['LBL_EXTERNAL_LNK'] . "' title='" . $mod_strings['LBL_EXTERNAL_LNK'] . "' hspace='3' align='absmiddle' border='0'>";
					}
					$label_fld[] = $fileicon . $fld_value;
				}
				if ($fieldname == 'filesize') {
					if ($col_fields['filelocationtype'] == 'I') {
						$filesize = $col_fields[ $fieldname ];
						if ($filesize < 1024) {
							$label_fld[] = $filesize . ' B';
						} elseif ($filesize > 1024 && $filesize < 1048576) {
							$label_fld[] = round ($filesize / 1024, 2) . ' KB';
						} else if ($filesize > 1048576) {
							$label_fld[] = round ($filesize / (1024 * 1024), 2) . ' MB';
						}
					} else {
						$label_fld[] = ' --';
					}
				}
				if ($fieldname == 'filetype' && $col_fields['filelocationtype'] == 'E') {
					$label_fld[] = ' --';
				}
			}
			//code for Documents module :end
			$label_fld[] = $col_fields[ $fieldname ];
		}
		$label_fld[] = $uitype;

		//sets whether the currenct user is admin or not
		if (is_admin ($current_user)) {
			$label_fld["isadmin"] = 1;
		} else {
			$label_fld["isadmin"] = 0;
		}

		if ($demoMode && $uitype != 17) {
			global $demoModeStyle;
			$label_fld[1] = '<span style="' . $demoModeStyle . '">' . $label_fld[1] . '</span>';
		}

		return $label_fld;
	}

	function ImageOrganization ($img) {
		$output = '';
		if ($img != null) {
			$output = '<img src="test/logo/' . $img . '" width="30px" height="30px" />';
		}
		return $output;
	}

	/** This function returns the related vtiger_tab details for a given entity or a module.
	 * Param $module - module name
	 * Param $focus - module object
	 * Return type is an array
	 */
	function getRelatedListsInformation ($module, $focus) {
		global $log;
		global $adb;
		global $current_user;
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');

		$cur_tab_id = getTabid ($module);
		$lstRoles   = explode (',', obtenerValorVariable ('ROLES_ADM_CURSOS', 'formacion_cursos'));
		//$sql1 = "select * from vtiger_relatedlists where tabid=? order by sequence";
		// vtlib customization: Do not picklist module which are set as in-active
		$sql1 = "SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid NOT IN (SELECT tabid FROM vtiger_tab WHERE presence = 1) ORDER BY sequence";
		// END
		$result  = $adb->pquery ($sql1, array ($cur_tab_id));
		$num_row = $adb->num_rows ($result);
		for ($i = 0; $i < $num_row; $i++) {
			$rel_tab_id    = $adb->query_result ($result, $i, "related_tabid");
			$function_name = $adb->query_result ($result, $i, "name");
			$label         = $adb->query_result ($result, $i, "label");
			$actions       = $adb->query_result ($result, $i, "actions");
			$relationId    = $adb->query_result ($result, $i, "relation_id");
			if ($rel_tab_id != 0) {
				if ($profileTabsPermission[ $rel_tab_id ] == 0 || in_array ($current_user->column_fields['roleid'], $lstRoles)) {
					if ($profileActionPermission[ $rel_tab_id ][3] == 0 || in_array ($current_user->column_fields['roleid'], $lstRoles)) {
						// vtlib customization: Send more information (from module, related module)
						// to the callee
						$focus_list[ $label ] = $focus->$function_name($focus->id, $cur_tab_id,
							$rel_tab_id, $actions);
						// END
					}
				}
			} else {
				// vtlib customization: Send more information (from module, related module)
				// to the callee
				$focus_list[ $label ] = $focus->$function_name($focus->id, $cur_tab_id, $rel_tab_id,
					$actions);
				// END
			}
		}
		return $focus_list;
	}

	/** This function returns the related vtiger_tab details for a given entity or a module.
	 * Param $module - module name
	 * Param $focus - module object
	 * Return type is an array
	 */
	function getRelatedLists ($module, $focus) {
		global $log;
		global $adb;
		global $current_user;
		$local_user = clone $current_user;
		$profileTabsPermission = array();
		$profileActionPermission = array();
		$focus_list = array();
		require ('user_privileges/user_privileges.php');

		$cur_tab_id = getTabid ($module);
		$lstRoles   = explode (',', obtenerValorVariable ('ROLES_ADM_CURSOS', 'formacion_cursos'));
		//$sql1 = "select * from vtiger_relatedlists where tabid=? order by sequence";
		// vtlib customization: Do not picklist module which are set as in-active
		$sql1 = "SELECT
		    rlt.*, tab.name
			FROM vtiger_relatedlists rlt
			INNER JOIN  vtiger_tab tab ON tab.tabid = rlt.related_tabid
			WHERE
			    rlt.presence IN(0, 2) AND
			    rlt.tabid=? AND
			      rlt.related_tabid NOT IN (SELECT tabid FROM vtiger_tab WHERE presence = 1)
			ORDER BY  sequence";
		
		// END
		$result  = $adb->pquery ($sql1, array ($cur_tab_id));
		$num_row = $adb->num_rows ($result);
		for ($i = 0; $i < $num_row; $i++) {
			$rel_tab_id    = $adb->query_result ($result, $i, "related_tabid");
			$function_name = $adb->query_result ($result, $i, "name");
			$label         = $adb->query_result ($result, $i, "label");
			$actions       = $adb->query_result ($result, $i, "actions");
			$relationId    = $adb->query_result ($result, $i, "relation_id");
			$tabName       = $adb->query_result ($result, $i, "name");
			$fieldName	 = $adb->query_result ($result, $i, "relfield");
			if ($rel_tab_id != 0) {
				// Permitir acceso si: es admin, tiene permisos de tab, no hay permisos definidos, o está en roles especiales
				$hasTabPermission = !isset($profileTabsPermission[$rel_tab_id]) || $profileTabsPermission[$rel_tab_id] == 0;
				$hasActionPermission = !isset($profileActionPermission[$rel_tab_id][3]) || $profileActionPermission[$rel_tab_id][3] == 0;
				
				if ($is_admin || $hasTabPermission || in_array ($current_user->column_fields['roleid'], $lstRoles)) {
					if ($is_admin || $hasActionPermission || in_array ($current_user->column_fields['roleid'], $lstRoles)) {
						// vtlib customization: Send more information (from module, related module)
						// to the callee
						$focus_list[ $label ] = array (
							'related_tabid' => $rel_tab_id,
							'relationId'    => $relationId,
							'actions'       => $actions,
							'tabName'       => $tabName,
							'fieldName'     => $fieldName,
						);
						// END
					}
				}
			} else {
				// vtlib customization: Send more information (from module, related module)
				// to the callee
				$focus_list[ $label ] = array (
					'related_tabid'            => $rel_tab_id, 'relationId' =>
						$relationId, 'actions' => $actions,
				);
				// END
			}
		}
		return $focus_list;
	}

	/** This function returns whether related lists is present for this particular module or not
	 * Param $module - module name
	 * Param $activity_mode - mode of activity
	 * Return type true or false
	 */
	function isPresentRelatedLists ($module, $activity_mode = '') {
		static $moduleRelatedListCache = array ();

		global $adb, $current_user;
		$retval = array ();

		//[ TT11181 ] Ajustes Menú Izquierdo (Módulos/APP´s) Producto Interno (Platzilla)
		//DM 28/06/2016
		//Leyendo de variable de session. No se escriben los archivos tabdata.php
		if (isset($_SESSION['authenticated_user_menu']['tabdata']) && isset($_SESSION['authenticated_user_menu']['tabdata']['tab_seq_array'])
			&& count ($_SESSION['authenticated_user_menu']['tabdata']['tab_seq_array'] > 0)
		) {
			$tab_seq_array = $_SESSION['authenticated_user_menu']['tabdata']['tab_seq_array'];
		}

		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		$tab_id = getTabid ($module);

		// We need to check if there is atleast 1 relation, no need to use count(*)
		$query  = "SELECT rl.relation_id, rl.related_tabid, rl.label, t.name as module_name 
	           FROM vtiger_relatedlists rl 
	           INNER JOIN vtiger_tab t ON t.tabid = rl.related_tabid 
	           WHERE rl.tabid=? AND rl.presence=0 
	           ORDER BY rl.sequence";
		$result = $adb->pquery ($query, array ($tab_id));
		$count  = $adb->num_rows ($result);

		if ($count < 1 || ($module == 'Calendar' && $activity_mode == 'task')) {
			$retval = 'false';
		} else if (empty($moduleRelatedListCache[ $module ])) {
			for ($i = 0; $i < $count; ++$i) {
			$relatedId     = $adb->query_result ($result, $i, 'relation_id');
			$relationLabel = $adb->query_result ($result, $i, 'label');
			$relatedTabId  = $adb->query_result ($result, $i, 'related_tabid');
			$moduleName    = $adb->query_result ($result, $i, 'module_name');
			
			//check for module disable.
			$permitted = isset($tab_seq_array[$relatedTabId]) ? $tab_seq_array[$relatedTabId] : 'NOT_SET';
			$profilePerm = isset($profileTabsPermission[$relatedTabId]) ? $profileTabsPermission[$relatedTabId] : 'NOT_SET';

			//Cambiando validación de $permitted === 0 a ($permitted != '' && $permitted == 0) puest que se lee un array no una cadena
			if (($permitted != '' && $permitted == 0) || empty($relatedTabId)) {
				if ($is_admin || $profileTabsPermission[ $relatedTabId ] === 0 || empty($relatedTabId)) {
					$retval[ $relatedId ] = $moduleName;
				}
			}
		}
		$moduleRelatedListCache[ $module ] = $retval;
	}

	return $moduleRelatedListCache[ $module ];
	}

	/** This function returns the detailed block information of a record in a module.
	 * Param $module - module name
	 * Param $block - block id
	 * Param $col_fields - column vtiger_fields array for the module
	 * Param $tabid - vtiger_tab id
	 * Return type is an array
	 */
	function getDetailBlockInformation ($module, $result, $col_fields, $tabid, $block_label) {
		global $log;
		global $adb;
		global $current_user;
		global $mod_strings;
		$label_data = Array ();
		$returndata = Array ();
		$curBlock = '';

		$noofrows = $adb->num_rows ($result);
		for ($i = 0; $i < $noofrows; $i++) {

			$fieldtablename = $adb->query_result ($result, $i, "tablename");
			$fieldcolname   = $adb->query_result ($result, $i, "columnname");
			$uitype         = $adb->query_result ($result, $i, "uitype");
			$fieldname      = $adb->query_result ($result, $i, "fieldname");
			$fieldid        = $adb->query_result ($result, $i, "fieldid");
			$fieldlabel     = $adb->query_result ($result, $i, "fieldlabel");
			$maxlength      = $adb->query_result ($result, $i, "maximumlength");
			$block          = $adb->query_result ($result, $i, "block");
			$generatedtype  = $adb->query_result ($result, $i, "generatedtype");
			$tabid          = $adb->query_result ($result, $i, "tabid");
			$displaytype    = $adb->query_result ($result, $i, 'displaytype');
			$readonly       = $adb->query_result ($result, $i, 'readonly');
			$typeOfData     = $adb->query_result ($result, $i, 'typeofdata');
			$custfld        = getDetailViewOutputHtml ($uitype, $fieldname, $fieldlabel, $col_fields, $generatedtype, $tabid, $module, $fieldid, $typeOfData);
			$dummy          = explode ('~', $typeOfData);
			$mandatory      = $dummy [1];
		
			if (is_array ($custfld)) {
				$label_data[ $block ][] = array (
					$custfld[0] => array (
						"value"       => isset($custfld[1]) ? $custfld[1] : '',
						"ui"          => isset($custfld[2]) ? $custfld[2] : array($fieldname, $fieldlabel, $uitype),
						"options"     => isset($custfld["options"]) ? $custfld["options"] : '',
						"secid"       => isset($custfld["secid"]) ? $custfld["secid"] : '',
						"link"        => isset($custfld["link"]) ? $custfld["link"] : '',
						"cursymb"     => isset($custfld["cursymb"]) ? $custfld["cursymb"] : '',
						"salut"       => isset($custfld["salut"]) ? $custfld["salut"] : '',
						"notaccess"   => isset($custfld["notaccess"]) ? $custfld["notaccess"] : '',
						"cntimage"    => isset($custfld["cntimage"]) ? $custfld["cntimage"] : '',
						"isadmin"     => isset($custfld["isadmin"]) ? $custfld["isadmin"] : '',
						"tablename"   => $fieldtablename,
						"fldname"     => $fieldname,
						"fldid"       => $fieldid,
						"displaytype" => $displaytype,
						"readonly"    => $readonly,
						'mandatory'   => $mandatory,
					),
				);
			}
		}
		foreach ($label_data as $headerid => $value_array) {
			$detailview_data = Array ();
			for ($i = 0, $j = 0; $i < count ($value_array); $j++) {
				$key2 = null;
				$keys = array_keys ($value_array[ $i ]);
				$key1 = $keys[0];
				if (isset($value_array[ $i + 1 ]) && is_array ($value_array[ $i + 1 ]) && ($value_array[ $i ][ $key1 ]['ui'] != 19 && $value_array[ $i ][ $key1 ]['ui'] != 20 && $value_array[ $i ][ $key1 ]['ui'] != 256)) {
					$keys = array_keys ($value_array[ $i + 1 ]);
					$key2 = $keys[0];
				}
				// Added to avoid the unique keys
				$use_key1 = $key1;
				if ($key1 == $key2) {
					$use_key1 = " " . $key1;
				}

				if ($value_array[ $i ][ $key1 ]['ui'] != 19 && $value_array[ $i ][ $key1 ]['ui'] != 20 && $value_array[ $i ][ $key1 ]['ui'] != 256) {
					if (isset($value_array[ $i + 1 ]) && isset($value_array[ $i + 1 ][ $key2 ])) {
						$detailview_data[ $j ] = array ($use_key1 => $value_array[ $i ][ $key1 ], $key2 => $value_array[ $i + 1 ][ $key2 ]);
						$i += 2;
					} else {
						$detailview_data[ $j ] = array ($use_key1 => $value_array[ $i ][ $key1 ]);
						$i++;
					}
				} else {
					$detailview_data[ $j ] = array ($use_key1 => $value_array[ $i ][ $key1 ]);
					$i++;
				}
			}
			$label_data[ $headerid ] = $detailview_data;
		}
		foreach ($block_label as $blockid => $label) {
			if ($label == '') {
				if (isset($label_data[ $blockid ])) {
					$translatedBlock = getTranslatedString ($curBlock, $module);
					if (!isset($returndata[ $translatedBlock ])) {
						$returndata[ $translatedBlock ] = array();
					}
					$returndata[ $translatedBlock ] = array_merge ((array) $returndata[ $translatedBlock ], (array) $label_data[ $blockid ]);
				}
			} else {
				$curBlock = $label;
				if (isset($label_data[ $blockid ]) && is_array ($label_data[ $blockid ])) {
					$translatedBlock = getTranslatedString ($curBlock, $module);
					if (!isset($returndata[ $translatedBlock ])) {
						$returndata[ $translatedBlock ] = array();
					}
					$returndata[ $translatedBlock ] = array_merge ((array) $returndata[ $translatedBlock ], (array) $label_data[ $blockid ]);
				}
			}
		}
		return $returndata;
	}

	function VT_detailViewNavigation ($smarty, $recordNavigationInfo, $currrentRecordId) {
		$pageNumber = 0;
		foreach ($recordNavigationInfo as $start => $recordIdList) {
			$pageNumber++;
			foreach ($recordIdList as $index => $recordId) {
				if ($recordId === $currrentRecordId) {
					if ($index == 0) {
						$smarty->assign ('privrecordstart', $start - 1);
						$smarty->assign ('privrecord', $recordNavigationInfo[ $start - 1 ][ count ($recordNavigationInfo[ $start - 1 ]) - 1 ]);
					} else {
						$smarty->assign ('privrecordstart', $start);
						$smarty->assign ('privrecord', $recordIdList[ $index - 1 ]);
					}
					if ($index == count ($recordIdList) - 1) {
						$smarty->assign ('nextrecordstart', $start + 1);
						$smarty->assign ('nextrecord', $recordNavigationInfo[ $start + 1 ][0]);
					} else {
						$smarty->assign ('nextrecordstart', $start);
						$smarty->assign ('nextrecord', $recordIdList[ $index + 1 ]);
					}
				}
			}
		}
	}

	function getRelatedListInfoById ($relationId) {
		static $relatedInfoCache = array ();
		if (isset($relatedInfoCache[ $relationId ])) {
			return $relatedInfoCache[ $relationId ];
		}
		$adb          = PearDatabase::getInstance ();
		$sql1         = "SELECT * FROM vtiger_relatedlists WHERE relation_id=?";
		$result       = $adb->pquery ($sql1, array ($relationId));
		$rowCount     = $adb->num_rows ($result);
		$relationInfo = array ();
		if ($rowCount > 0) {
			$relationInfo['relatedTabId'] = $adb->query_result ($result, 0, "related_tabid");
			$relationInfo['functionName'] = $adb->query_result ($result, 0, "name");
			$relationInfo['label']        = $adb->query_result ($result, 0, "label");
			$relationInfo['actions']      = $adb->query_result ($result, 0, "actions");
			$relationInfo['relationId']   = $adb->query_result ($result, 0, "relation_id");
		}
		$relatedInfoCache[ $relationId ] = $relationInfo;
		return $relatedInfoCache[ $relationId ];
	}

	/**
	 * Returns the progress value for a progress bar block
	 * @global type $adb
	 *
	 * @param type $fldname
	 * @param type $tabid
	 * @param type $record
	 * @param type $module
	 *
	 * @return type
	 */
	function getProgressBarValue ($fldname, $tabid, $record, $module = null) {
		global $adb;
		$progress = 0;

		if (empty($module)) {
			$module = getTabModuleName ($tabid);
		}

		$focus = CRMEntity::getInstance ($module);
		if (method_exists ($focus, 'getProgressBarValue')) {
			$focus->retrieve_entity_info ($record, $module);
			$focus->id = $record;
			$progress  = $focus->getProgressBarValue ();
			return $progress;
		}

		$whereModule = "and tabid=?";
		$moduleParam = $tabid;
		if ($module) {
			$whereModule = "and vtiger_tab.name=?";
			$moduleParam = $module;
		}
		$result = $adb->pquery ("select vtiger_blocks_properties.relmodule, vtiger_blocks_properties.relfieldname from vtiger_blocks_properties inner join vtiger_field on(blockid=block)
		inner join vtiger_tab using(tabid)
		where fieldname=? $whereModule",
			array ($fldname, $moduleParam));

		list($relmodule, $relfieldname) = $adb->fetch_row ($result);

		$progress = getProgressFromRelModule ($relmodule, $relfieldname, $record);

		return $progress;
	}

	/**
	 * returns the html color based on a progress value from 0 to 1.
	 *
	 * @param type $progress
	 *
	 * @return type
	 */
	function getProgressColor ($progress) {
		$r = 0xff;
		$g = pow ($progress * 2, 2) * 0xff;

		if ($progress > 0.5) {
			$g = 0xff;
			$r = (1 - pow ($progress, 6)) * 0xff;
		}

		$b = 80;
		if ($g < 30) {
			$b = $g = 30;
		}
		if ($r < 50) {
			$r = 50;
		}

		$color = sprintf ("#%02s%02s%02s", dechex ($r), dechex ($g), dechex ($b));

		return $color;
	}

	/**
	 * Get progress value from 0 to 1, based the field of a related module of $parentid.
	 * @global type $adb
	 *
	 * @param type $relmodule
	 * @param type $relfieldname
	 * @param type $parentid
	 *
	 * @return type
	 */
	function getProgressFromRelModule ($relmodule, $relfieldname, $parentid) {
		global $adb;

		$focus = CRMEntity::getInstance ($relmodule);

		$result = $adb->pquery ("SELECT tablename, columnname FROM vtiger_field WHERE fieldname=? AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)",
			array ($relfieldname, $relmodule));

		list($tablename, $columnname) = $adb->fetch_row ($result);
		$tableindex = $focus->table_index;

		$sql = "select count(*) as c from $tablename inner join vtiger_crmentity on (vtiger_crmentity.crmid=$tablename.$tableindex and deleted=0)
				inner join vtiger_crmentityrel on ($tableindex=vtiger_crmentityrel.crmid or $tableindex=relcrmid)
				where (vtiger_crmentityrel.crmid=? or vtiger_crmentityrel.relcrmid=?)";

		$params = array ($parentid, $parentid);

		list($t) = $adb->fetch_row ($adb->pquery ($sql, $params));

		$sql .= " and $tablename.$relfieldname is not null and $tablename.$relfieldname != ''
				and $tablename.$relfieldname != '0'";

		list($c) = $adb->fetch_row ($adb->pquery ($sql, $params));

		if ($t) {
			$progress = $c / $t;
		}

		return $progress;
	}
	
	/**
	 * Verifica si un valor de picklist existe en la tabla de valores del picklist
	 * @param PearDatabase $adb - Instancia de la base de datos
	 * @param string $fieldName - nombre del campo picklist
	 * @param string $value - valor a verificar
	 * @return boolean - true si el valor existe en la tabla, false si no
	 */
	function picklistValueExistsInDetailView ($adb, $fieldName, $value) {
		if (empty($value) || empty($fieldName)) {
			return false;
		}
		
		$tableName = 'vtiger_' . $adb->sql_escape_string($fieldName);
		$columnName = $adb->sql_escape_string($fieldName);
		
		// Verificar si la tabla existe
		$tableExists = $adb->pquery("SHOW TABLES LIKE ?", array($tableName));
		if ($adb->num_rows($tableExists) == 0) {
			return false;
		}
		
		// Verificar si el valor existe en la tabla
		$sql = "SELECT COUNT(*) as count FROM {$tableName} WHERE {$columnName} = ?";
		$result = $adb->pquery($sql, array($value));
		
		if ($result && $adb->num_rows($result) > 0) {
			$count = $adb->query_result($result, 0, 'count');
			return ($count > 0);
		}
		
		return false;
	}
