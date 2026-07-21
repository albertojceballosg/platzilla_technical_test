<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('user_privileges/default_module_view.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/PickListUtils.php');

	global $adb, $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;

	$focus = CRMEntity::getInstance ($currentModule);

	$tool_buttons    = Button_Check ($currentModule);
	$smarty          = new vtigerCRM_Smarty();
	$singlepane_view = true;

	$record      = $_REQUEST['record'];
	$isduplicate = vtlib_purify ($_REQUEST['isDuplicate']);
	$tabid       = getTabid ($currentModule);
	$category    = getParentTab ($currentModule);
	$swDetailViewGrid = true;

	if ($record != '') {
		$focus->id = $record;
		$focus->retrieve_entity_info ($record, $currentModule);
	}

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
	$adb->setDieOnError ($oldDieOnError);

	$focus->actualizarEstadoHitos ($currentModule);

	if ($isduplicate == 'true') {
		$focus->id = '';
	}

	// Identify this module as custom module.
	$smarty->assign ('CUSTOM_MODULE', true);

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	// TODO: Update Single Module Instance name here.
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
		$recordNavigationInfo = ListViewSession::getListViewNavigation ($focus->id);
		VT_detailViewNavigation ($smarty, $recordNavigationInfo, $focus->id);
	}

	$smarty->assign ('IS_REL_LIST', isPresentRelatedLists ($currentModule));
	$smarty->assign ('SinglePane_View', $singlepane_view);

	if ($singlepane_view == 'true') {
		$related_array = getRelatedLists ($currentModule, $focus);

		// Se elimina el related lists de todotasks (se necesita conservar en la bd para la funcionalidad de cuadro de tareas)
		if ($related_array['HelpDesk']) {
			$relationIdHelpDesk = $related_array['HelpDesk']['relationId'];
			unset($related_array['HelpDesk']);
		}

		$smarty->assign ("RELATEDLISTS", $related_array);
		require_once ('include/ListView/RelatedListViewSession.php');
		if (!empty($_REQUEST['selected_header']) && !empty($_REQUEST['relation_id'])) {
			RelatedListViewSession::addRelatedModuleToSession (vtlib_purify ($_REQUEST['relation_id']),
				vtlib_purify ($_REQUEST['selected_header']));
		}
		$open_related_modules = RelatedListViewSession::getRelatedModulesFromSession ();
		$smarty->assign ("SELECTEDHEADERS", $open_related_modules);
	}

	if (isPermitted ($currentModule, 'EditView', $record) == 'yes') {
		$smarty->assign ('EDIT_DUPLICATE', 'permitted');
	}
	if (isPermitted ($currentModule, 'Delete', $record) == 'yes') {
		$smarty->assign ('DELETE', 'permitted');
	}

	$blocks = getBlocks ($currentModule, 'detail_view', '', $focus->column_fields);

	$smarty->assign ('SHOW_HITOS', 1);
	$smarty->assign ('SHOW_KANBAN', 1);
	$smarty->assign ('SHOW_GANTT', 1);

	$smarty->assign ('SEVERITIES', getPickListValues ('ticketseverities', $current_user->roleid, $encode = true));
	$smarty->assign ('PRIORITIES', getPickListValues ('ticketpriorities', $current_user->roleid, $encode = true));
	$smarty->assign ('CATEGORIES', getPickListValues ('ticketcategories', $current_user->roleid, $encode = true));
	$smarty->assign ('TICKETSTATUS', getPickListValues ('ticketstatus', $current_user->roleid, $encode = true));

	// Consultando hitos y tareas
	$smarty->assign ('HITOSTAREAS', $focus->getHitosTareas ());

	// lenguaje del GANTT
	$ganttLang = explode ('_', $current_language);
	$ganttLang = $ganttLang[0];
	$smarty->assign ('GANTT_LANG', $ganttLang);

	// Incorporando el GANTT
	$smarty->assign ('GANTT', $focus->obtenerEstructuraGantt ($currentModule));

	$smarty->assign ('BLOCKS', $blocks);
	// Gather the custom link information to display
	include_once ('vtlib/Vtiger/Link.php');
	$customlink_params = Array ('MODULE' => $currentModule, 'RECORD' => $focus->id, 'ACTION' => vtlib_purify ($_REQUEST['action']));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), Array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customlink_params));
	// END

	// Record Change Notification
	$focus->markAsViewed ($current_user->id);
	// END
	$smarty->assign ('CAMPOS_TIPO_GRID', escribeCamposGrid ($currentModule, $entity->id, $swDetailViewGrid));
	$smarty->assign ('CAMPOS_TIPO_MATRIX', escribeDetalleCamposMatrix ($currentModule, $focus->id));

	$smarty->assign ('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean ('DETAILVIEW_AJAX_EDIT', true));

	if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
		$smarty->assign ("PLATDB", vtlib_purify ($_REQUEST['platdb']));
	}

	$dataSelectUsers  = getInfoSelectAsignedUserId ();
	$dataSelectGroups = getInfoSelectGroupsId ();
	$smarty->assign ('USUARIOS', $dataSelectUsers);
	$smarty->assign ('GRUPOS', $dataSelectGroups);
	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smarty->assign ('ATTACHMENTS', AttachmentsUtils::fetchAttachments ($adb, $record, $currentModule));

	$smarty->display ('DetailViewPlan.tpl');

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
	$adb->setDieOnError ($oldDieOnError);

?>
<script src="<?php echo $theme_path; ?>js/jquery-ui.custom.min.js"></script>
<script src="<?php echo $theme_path; ?>js/fullcalendar.js"></script>
<!-- this page specific scripts -->
<script src="<?php echo $theme_path; ?>js/bootstrap-wizard.js"></script>
<script src="<?php echo $theme_path; ?>js/select2.min.js"></script>
<!-- this page specific scripts -->
<script src="<?php echo $theme_path; ?>js/bootstrap-datepicker.js"></script>
<script src="<?php echo $theme_path; ?>js/bootstrap-datepicker.es.js"></script>
<script src="<?php echo $theme_path; ?>js/moment.min.js"></script>
<script src="<?php echo $theme_path; ?>js/bootstrap-timepicker.min.js"></script>
<script>
	jQuery (document).ready (function () {
		refreshProgressBar ('<?php echo $currentModule;?>', 'todotasks', '<?php echo $focus->id;?>');
	});

	function setFinish (crmid) {
		if (confirm ("Esta seguro que desea marcar la actividad como Ejecutada?")) {
			var url = "index.php?module=todotasks&action=todotasksAjax&file=DetailViewAjax&ajxaction=DETAILVIEW&fldName=executed&fieldValue=1&tableName=vtiger_todotasks&recordid=" + crmid;
			jQuery.post (url, function (response) {
				var resp = response.split (':#:');
				if (resp[ 1 ] == 'SUCCESS') {
					window.location.reload ();
				}
			});
		}
	}
</script>
<script language="javascript">
	function openNewTask () {
		jQuery ('#open-wizard').click ();
	}

	function ValidarFechaInicio (el) {
		var valor = el.val ();
		ret = {
			status: true
		};

		if (!valor) {
			ret.status = false;
			ret.msg = "Especifique la fecha de Inicio";
		}

		return ret;
	}

	function ValidarAsunto (el) {
		var valor = el.val ();
		ret = {
			status: true
		};

		if (!valor) {
			ret.status = false;
			ret.msg = "Especifique el Titulo";
		}

		return ret;
	}

	function ValidarFechaFin (el) {
		var valor = el.val ();
		ret = {
			status: true
		};

		if (!valor) {
			ret.status = false;
			ret.msg = "Especifique la fecha de Finalizaci�n";
		}

		return ret;
	}

	function inicializarCampo (campo, valor) {
		jQuery ("#" + campo).val (valor);
	}

	jQuery (function () {

		//refreshProgressBar('proyectos', 'HelpDesk', <?php echo $focus->id;?>);

		jQuery ('#sel2').select2 ();

		jQuery.fn.wizard.logging = false;

		var wizard = jQuery ("#wizard-demo").wizard ({
			showCancel: true,
			buttons:    {
				cancelText:     "<?php echo $mod_strings['LBL_cancelText']; ?>",
				nextText:       "<?php echo $mod_strings['LBL_nextText']; ?>",
				backText:       "<?php echo $mod_strings['LBL_backText']; ?>",
				submitText:     "<?php echo $mod_strings['LBL_submitText']; ?>",
				submittingText: "<?php echo $mod_strings['LBL_submittingText']; ?>",
			}
		});

		wizard.on ("submit", function (wizard) {

			var ticket_title = jQuery ("#ticket_title").val ();

			var customerdescription = jQuery ("#customerdescription").val ();

			var assigntype = jQuery ("#assigntype_text").val ();

			var assigned_user_id = jQuery ("#assigned_user_id_p").val ();
			var assigned_group_id = jQuery ("#assigned_group_id_p").val ();

			var ticket_idProyecto = jQuery ("#ticket_idProyecto").val ();
			var ticket_idHito = jQuery ("#ticket_idHito").val ();

			var start_date = jQuery ("#datepickerDate").val ();
			var end_estimated_date = jQuery ("#datepickerDateEnd").val ();

			var urlstr = "&ticket_title=" + ticket_title + "&customerdescription=" + customerdescription;
			urlstr += "&ticket_idProyecto=" + ticket_idProyecto + "&ticket_idHito=" + ticket_idHito;
			urlstr += "&start_date=" + start_date + "&end_estimated_date=" + end_estimated_date;

			urlstr += "&assigntype=" + assigntype + "&assigned_user_id=" + assigned_user_id;
			urlstr += "&assigned_group_id=" + assigned_group_id;
			urlstr += "&record=" + <?php echo $record; ?>;

			var submit = {
				"hostname": jQuery ("#new-server-fqdn").val ()
			};

			new Ajax.Request (
				'index.php',
				{
					queue:     { position: 'end', scope: 'command' },
					method:    'post',
					dataType:  'json',
					postBody:  'module=todotasks&action=todotasksAjax&file=crearActividadAjax' + urlstr,
					onSuccess: function (response) {

						setTimeout (function () {
							wizard.trigger ("success");
							wizard.hideButtons ();
							wizard._submitting = false;
							wizard.showSubmitCard ("success");
							wizard.updateProgressBar (0);
						}, 2000);

						alert ("<?php echo $mod_strings['LBL_taskSuccessfullCreated']; ?>");

						//window.location.reload();

						//loadRelatedListBlock('module=proyectos&action=proyectosAjax&file=DetailViewAjax&ajxaction=LOADRELATEDLIST&header=LBL_TODO_TASKS&order_by=ticket_no&record='+<?php echo $record; ?>+'&sorder=ASC&relation_id='+<?php echo $relationIdHelpDesk; ?>,'tbl_proyectos_LBL_TODO_TASKS','proyectos_LBL_TODO_TASKS');
						//refreshProgressBar('proyectos', 'HelpDesk', <?php echo $focus->id;?>);

					}
				}
			);

		});

		wizard.on ("reset", function (wizard) {
			wizard.setSubtitle ("");
			wizard.el.find ("#new-server-fqdn").val ("");
			wizard.el.find ("#new-server-name").val ("");
		});

		wizard.el.find (".wizard-success .im-done").click (function () {
			wizard.reset ().close ();
			window.location.reload ();
		});

		wizard.el.find (".wizard-close .close").click (function () {
			wizard.reset ().close ();
			window.location.reload ();
		});

		wizard.el.find (".wizard-success .create-another-server").click (function () {
			wizard.reset ();
		});

		jQuery ("[id^='open-wizard-']").each (function () {

			jQuery (this).click (function () {
				wizard.show ();

				var idProyecto = jQuery (this).attr ("idProyecto");
				var idHito = jQuery (this).attr ("idHito");

				jQuery ("#ticket_idProyecto").val (idProyecto);
				jQuery ("#ticket_idHito").val (idHito);

			});

		});

		/*
		 jQuery("#open-wizard").click(function() {
		 wizard.show();

		 var idProyecto = jQuery(this).attr("idProyecto");
		 var idHito = jQuery(this).attr("idHito");

		 jQuery("#ticket_idProyecto").val(idProyecto);
		 jQuery("#ticket_idHito").val(idHito);

		 });
		 */

		//datepicker
		jQuery ('#datepickerDate').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
		jQuery ('#datepickerDateEnd').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });

		// Inicializando campos de formulario
		var FechaIni = '<?php echo date ("d-m-Y"); ?>';
		inicializarCampo ("datepickerDate", FechaIni);
		inicializarCampo ("datepickerDateEnd", FechaIni);
		toggleAssignType ("U");

		//timepicker
		jQuery ('#timepicker').timepicker ({
			minuteStep:   5,
			showSeconds:  true,
			showMeridian: false,
			disableFocus: false,
			showWidget:   true
		}).focus (function () {
			jQuery (this).next ().trigger ('click');
		});

		jQuery ('#timepickerEnd').timepicker ({
			minuteStep:   5,
			showSeconds:  true,
			showMeridian: false,
			disableFocus: false,
			showWidget:   true
		}).focus (function () {
			jQuery (this).next ().trigger ('click');
		});

	});

	function toggleAssignType (currType) {
		jQuery ("#assigntype_text").val (currType);
		if (currType == "U") {
			jQuery ("#assigned_user_p").css ("display", "block");
			jQuery ("#assign_team_p").css ("display", "none");
		} else {
			jQuery ("#assigned_user_p").css ("display", "none");
			jQuery ("#assign_team_p").css ("display", "block");
		}
	}
</script>



