<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('user_privileges/default_module_view.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('formacion_cursos_tools.php');

	global $adb;
	global $mod_strings;
	global $app_strings;
	global $currentModule;
	global $current_user;
	global $theme;
	global $singlepane_view;
	global $plat;
	$focus        = CRMEntity::getInstance ($currentModule);
	$tool_buttons = Button_Check ($currentModule);
	$smarty       = new vtigerCRM_Smarty();
	$record       = $_REQUEST['record'];
	$isduplicate  = vtlib_purify ($_REQUEST['isDuplicate']);
	$tabid        = getTabid ($currentModule);
	$category     = getParentTab ();
	$evalu        = vtlib_purify ($_REQUEST['save']);
	$dateva       = vtlib_purify (parse_str ($_REQUEST['datapost'], $my));

	if ($record != '') {
		$focus->id = $record;
		$focus->retrieve_entity_info ($record, $currentModule);
	} else if ($record == '' && $evalu == true) {
		$estado = guardarEvaluacion ($my);
		if ($estado === 'Aplazado') {
			echo 'Lo sentimos, usted no aprobo la prueba!!';
		} else {
			echo 'Felicidades!!!!!, usted aprobó la prueba';
		}
		return;
	}
	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
	$adb->setDieOnError ($oldDieOnError);

	if ($isduplicate == 'true') {
		$focus->id = '';
	}

	// Identify this module as custom module.
	$smarty->assign ('CUSTOM_MODULE', true);

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	//  Update Single Module Instance name here.
	$smarty->assign ('SINGLE_MOD', 'SINGLE_' . $currentModule);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('ID', $focus->id);
	$smarty->assign ('MODE', $focus->mode);

	$recordName = array_values (getEntityName ($currentModule, $focus->id));
	$recordName = $recordName[0];
	$smarty->assign ('NAME', $recordName);
	$smarty->assign ('UPDATEINFO', updateInfo ($focus->id));

	// Module Sequence Numbering
	$mod_seq_field = getModuleSequenceField ($currentModule);
	if ($mod_seq_field != null) {
		$mod_seq_id = $focus->column_fields[ $mod_seq_field['name'] ];
	} else {
		$mod_seq_id = $focus->id;
	}
	$smarty->assign ('MOD_SEQ_ID', $mod_seq_id);
	// END

	$validationArray = split_validationdataArray (getDBValidationData ($focus->tab_name, $tabid));
	$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray['fieldname']);
	$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray['datatype']);
	$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray['fieldlabel']);

	$smarty->assign ('EDIT_PERMISSION', isPermitted ($currentModule, 'EditView', $record));
	$smarty->assign ('CHECK', $tool_buttons);

	if (PerformancePrefs::getBoolean ('DETAILVIEW_RECORD_NAVIGATION', true) && isset($_SESSION[ $currentModule . '_listquery' ])) {
		$recordNavigationInfo = call_user_func ('ListViewSession::getListViewNavigation($focus->id)');
		VT_detailViewNavigation ($smarty, $recordNavigationInfo, $focus->id);
	}

	$smarty->assign ('IS_REL_LIST', isPresentRelatedLists ($currentModule));
	$smarty->assign ('SinglePane_View', $singlepane_view);

	$singlepane_view = 'true';
	if ($singlepane_view == 'true') {
		$related_array = getRelatedLists ($currentModule, $focus);
		$smarty->assign ('RELATEDLISTS', $related_array);

		require_once ('include/ListView/RelatedListViewSession.php');
		if (!empty($_REQUEST['selected_header']) && !empty($_REQUEST['relation_id'])) {
			RelatedListViewSession::addRelatedModuleToSession (
				vtlib_purify ($_REQUEST['relation_id']),
				vtlib_purify ($_REQUEST['selected_header'])
			);
		}
		$open_related_modules = RelatedListViewSession::getRelatedModulesFromSession ();
		$smarty->assign ('SELECTEDHEADERS', $open_related_modules);
	}

	if (isPermitted ($currentModule, 'EditView', $record) == 'yes') {
		$smarty->assign ('EDIT_DUPLICATE', 'permitted');
	}
	if (isPermitted ($currentModule, 'Delete', $record) == 'yes') {
		$smarty->assign ('DELETE', 'permitted');
	}

	$focus->column_fields['image'] = getFileFieldValue ($currentModule, 'img_curso', $focus->id);
	$smarty->assign ('FIELDS', $focus->column_fields);

	$sql       = "SELECT vfl.*,CONCAT(va.`path`,va.`attachmentsid`,'_',va.`name`) AS material,va.`name` AS archivo FROM vtiger_formacion_lecciones vfl
				INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfl.`formacion_leccionesid` AND crm.`deleted`=0
				INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.`crmid`=$focus->id
				LEFT JOIN vtiger_attachments va ON va.`attachmentsid`=vfl.`materiales`
				ORDER by vfl.orden ASC";
	$q         = $adb->pquery ($sql, array ());
	$lecciones = array ();
	$order     = array ("\r\n", "\n", "\r");
	$replace   = '<br />';
	while ($r = $adb->fetchByAssoc ($q)) {
		$r             = str_replace ($order, $replace, $r);
		$r['ext']      = getExtension ($r['file']);
		$r['ext_arch'] = strtolower (getExtension ($r['archivo']));
		$eval          = getEvaluacion ($r['formacion_leccionesid']);
		if ($eval) {
			$r['eval'] = $eval;
			$lim       = calcularLimite ($eval[0]['formacion_pruebasid']);
			$preg      = getPreguntasRand ($eval[0]['formacion_pruebasid'], $lim);
			if ($lim != 100) {
				$r['preg'] = $preg;
			}
			$test = checkExamenporUsuario ($current_user->id, $eval[0]['formacion_pruebasid']);
			if (is_array ($test)) {
				$total = count ($test);
				if (is_Assoc_in_array ($test, 'estado', 'Aprobado') == 'yes') {
					$r['test'] = 'Aprobado';
				} else {
					if ($total < 3) {
						$r['test'] = '1';
					} else {
						$r['test'] = 'Aplazado';
					}
				}
			} else {
				$r['test'] = $test;
			}
		}

		$lecciones[] = $r;
	}
	$prog = calcularProgresodelCurso ($lecciones);
	if ($prog > 0) {
		$prog = ($prog * 100 / count ($lecciones));
	}
	$smarty->assign ('PROG', $prog);
	$smarty->assign ('LECCIONES', $lecciones);
	$smarty->assign ('LECCIONES_OBJ', json_encode ($lecciones));
	$smarty->assign ('RECORD', $focus->id);

	// Gather the custom link information to display
	require_once ('vtlib/Vtiger/Link.php');
	$customlink_params = array ('MODULE' => $currentModule, 'RECORD' => $focus->id, 'ACTION' => vtlib_purify ($_REQUEST['action']));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customlink_params));
	// END

	// Record Change Notification
	$focus->markAsViewed ($current_user->id);
	// END
	$smarty->assign ('CAMPOS_TIPO_GRID', escribeDetalleCamposGrid ($currentModule, $focus->id));
	$smarty->assign ('CAMPOS_TIPO_MATRIX', escribeDetalleCamposMatrix ($currentModule, $focus->id));

	$smarty->assign ('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean ('DETAILVIEW_AJAX_EDIT', true));
	$buttons = sendNotificationButton ($currentModule, $focus->id);
	$smarty->assign ('CUSTOM_BUTTONS', $buttons);

	$nplat = $plat;
	if (strstr ($plat, 'cliente-') || strstr ($plat, 'clienteweb-')) {
		$lstPlat = explode ('-', $plat);
		$nplat   = $lstPlat[1];
	}
	$smarty->assign ('PLAT_CODE', $nplat);
	$sql               = 'SELECT * FROM vtiger_organizationdetails';
	$result            = $adb->pquery ($sql, array ());
	$organization_logo = decode_html ($adb->query_result ($result, 0, 'logoname'));
	$smarty->assign ('LOGO', $organization_logo);

	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));

	$smarty->assign ('usr_id', $current_user->id);
	$smarty->assign ('ATTACHMENTS', AttachmentsUtils::fetchAttachments ($adb, $record, $currentModule));
	$smarty->display ('DetailView.tpl');

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
	$adb->setDieOnError ($oldDieOnError);
