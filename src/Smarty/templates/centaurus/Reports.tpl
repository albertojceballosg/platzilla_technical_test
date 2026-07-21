{strip}
<style type="text/css">
	.main-box {
		box-shadow:    0px 0px 0px 0 #FFFFFF !important;
		border-radius: 0px !important;
	}
	.base-list-container {
		background-color: #ffffff;
		margin:           0px -13px!important;
		border-top:       1px solid #D8D8D8 !important;
		height:           auto;
		min-height:       1150px !important;
	}
	@media (min-width: 768px) {
		.wizard-modal.modal {
			max-width:   1024px;
		}
	}
	@media (max-width: 767px) {
		.linea {
			display:      inline-block !important;
			margin-right: 10px !important;
		}
	}
</style>
<script type="text/javascript" src="modules/Reports/Reports.js"></script>
<div class="row module-buttons">
	<div class="col-lg-12">
		<div class="pull-left" style="float: left;">
			<h1><a href="index.php?module={$MODULE}&action=index">{$MODULE|@getTranslatedString: $MODULE}</a></h1>
		</div>
		<div class="pull-right" align="margin-right">
			<button type="button" class="btn btn-primary" onclick="createrepFolder (this, 'orgLay');" style="margin-left:.5em; margin-right: 0;">{$MOD.Create_New_Folder}</button>
		</div>
	</div>
</div>
<div class="container-fluid base-list-container">
<div id="reportContents" class="row">
{include file="ReportContents.tpl"}
</div>
<div id="orgLay" style="display: none;" class="modal fade in" role="dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="fninvsh ('orgLay');">X</button>
			<h4 class="modal-title">{$MOD.LBL_ADD_NEW_GROUP}</h4>
		</div>
		<div class="row modal-body">
			<div class="col-xs-12">
				<div class="col-xs-5">
					<div class="label-input">
						<label for="folder_name">{$MOD.LBL_REP_FOLDER_NAME}</label>
					</div>
				</div>
				<div class="form-group col-xs-7">
					<div class="input-group" style="width: 100%;">
						<input type="hidden" id="folder_id" name="folderId" value="" />
						<input type="hidden" id="fldrsave_mode" name="folderId" value="save">
						<input type="text" id="folder_name" name="folderName" class="form-control" maxlength="100" />
					</div>
				</div>
			</div>
			<div class="col-xs-12">
				<div class="col-xs-5">
					<div class="label-input">
						<label for="folder_desc">{$MOD.LBL_REP_FOLDER_DESC}</label>
					</div>
				</div>
				<div class="form-group col-xs-7" style="margin-bottom: 0;">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="folder_desc" name="folderDesc" class="form-control" />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" name="save" class="btn btn-success" onclick="AddFolder ()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
			<button type="button" name="cancel" class="btn btn-warning" onclick="closeEditReport ();">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
		</div>
	</div>
</div>
</div>
{include file='ReportWizard.tpl'}
<script type="text/javascript">
{literal}
	jQuery ( document ).ready(function() {
		jQuery('.wizard-modal').removeAttr('style');
	});
	function createrepFolder (oLoc, divid) {
		getObj ('fldrsave_mode').value = 'save';
		$ ('folder_id').value = '';
		$ ('folder_name').value = '';
		$ ('folder_desc').value = '';
		fnvshobj (oLoc, divid);
	}
	function closeEditReport () {
		$ ('folder_id').value = '';
		$ ('folder_name').value = '';
		$ ('folder_desc').value = '';
		fninvsh ('orgLay')
	}
	function DeleteFolder (id) {
		var arguments;
		if (!confirm ('¿Estás seguro que quieres eliminar la carpeta seleccionada?')) {
			return;
		}

		arguments = [
			'module=Reports',
			'action=ReportsAjax',
			'mode=ajax',
			'file=DeleteReportFolder',
			'record=' + encodeURIComponent (id)
		];
		jQuery.ajax ('index.php', {
			data: arguments.join ('&'),
			dataType: 'text',
			method: 'post'
		}).done (function (response) {
			window.location.reload ();
		});
	}
	function AddFolder () {
		if (getObj ('folder_name').value.replace (/^\s+/g, '').replace (/\s+$/g, '').length == 0) {
			alert ({/literal}'{$APP.FOLDERNAME_CANNOT_BE_EMPTY}'{literal});
		} else if ((getObj ('folder_name').value).match (/['"<>/\+]/) || (getObj ('folder_desc').value).match (/['"<>/\+]/)) {
			alert (alert_arr.SPECIAL_CHARS + ' ' + alert_arr.NOT_ALLOWED + alert_arr.NAME_DESC);
		} else {
			var foldername = encodeURIComponent (getObj ('folder_name').value);
			new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'action=ReportsAjax&mode=ajax&file=CheckReport&module=Reports&check=folderCheck&folderName=' + foldername,
					onComplete: function (response) {
						var folderid = getObj ('folder_id').value,
							resresult = response.responseText.split ("::"),
							mode = getObj ('fldrsave_mode').value,
								url;
						if (resresult[ 0 ] != 0 && mode == 'save' && resresult[ 0 ] != 999) {
							alert ({/literal}"{$APP.FOLDER_NAME_ALREADY_EXISTS}"{literal});
							return false;
						} else if (((resresult[ 0 ] != 1 && resresult[ 0 ] != 0) || (resresult[ 0 ] == 1 && resresult[ 0 ] != 0 && resresult[ 1 ] != folderid )) && mode == 'Edit' && resresult[ 0 ] != 999) {
							alert ({/literal}"{$APP.FOLDER_NAME_ALREADY_EXISTS}"{literal});
							return false;
						} else if (response.responseText == 999) {
							alert ({/literal}"{$APP.SPECIAL_CHARS_NOT_ALLOWED}"{literal});
							return false;
						} else {
							fninvsh ('orgLay');
							var folderdesc = encodeURIComponent (getObj ('folder_desc').value);
							getObj ('folder_name').value = '';
							getObj ('folder_desc').value = '';
							foldername = foldername.replace (/^\s+/g, '').replace (/\s+$/g, '');
							foldername = foldername.replace (/&/gi, '*amp*');
							folderdesc = folderdesc.replace (/^\s+/g, '').replace (/\s+$/g, '');
							folderdesc = folderdesc.replace (/&/gi, '*amp*');
							if (mode == 'save') {
								url = '&savemode=Save&foldername=' + foldername + '&folderdesc=' + folderdesc;
							} else {
								folderid = getObj ('folder_id').value;
								url = '&savemode=Edit&foldername=' + foldername + '&folderdesc=' + folderdesc + '&record=' + folderid;
							}
							getObj ('fldrsave_mode').value = 'save';
							new Ajax.Request (
								'index.php',
								{
									queue:      { position: 'end', scope: 'command' },
									method:     'post',
									postBody:   'action=ReportsAjax&mode=ajax&file=SaveReportFolder&module=Reports' + url,
									onComplete: function (response) {
										window.location.reload (true);
									}
								}
							);
						}
					}
				}
			);
		}
	}
	function EditFolder (id, name, desc) {
		$ ('editfolder_info').innerHTML = {/literal}' {$MOD.LBL_RENAME_FOLDER} '{literal};
		getObj ('folder_name').value = name;
		getObj ('folder_desc').value = desc;
		getObj ('folder_id').value = id;
		getObj ('fldrsave_mode').value = 'Edit';
	}
	function massDeleteReport () {
		var folderids = getObj ('folder_ids').value,
			folderid_array = folderids.split (','),
			idstring = '',
			count = 0,
			i, row;
		for (i = 0; i < folderid_array.length; i++) {
			var selectopt_id = 'selected_id' + folderid_array[ i ];
			var objSelectopt = getObj (selectopt_id);
			if (objSelectopt != null) {
				var length_folder = getObj (selectopt_id).length;
				if (length_folder != undefined) {
					var cur_rep = getObj (selectopt_id);
					for (row = 0; row < length_folder; row++) {
						var currep_id = cur_rep[ row ].value;
						if (cur_rep[ row ].checked) {
							count++;
							idstring = currep_id + ':' + idstring;
						}
					}
				} else {
					if (getObj (selectopt_id).checked) {
						count++;
						idstring = getObj (selectopt_id).value + ':' + idstring;
					}
				}
			}
		}
		if (idstring != '') {
			if (confirm ({/literal}"{$APP.DELETE_CONFIRMATION}"{literal} + count + "{$APP.RECORDS}")) {
				new Ajax.Request (
					'index.php',
					{
						queue:      { position: 'end', scope: 'command' },
						method:     'post',
						postBody:   'action=ReportsAjax&mode=ajax&file=Delete&module=Reports&idlist=' + idstring,
						onComplete: function (response) {
							getObj ('customizedrep').innerHTML = response.responseText;
						}
					}
				);
			} else {
				return false;
			}
		} else {
			alert ({/literal}'{$APP.SELECT_ATLEAST_ONE_REPORT}'{literal});
			return false;
		}
	}
	function deleteReport (id) {
		if (confirm ({/literal}"{$APP.DELETE_REPORT_CONFIRMATION}"{literal})) {
			new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'action=ReportsAjax&file=Delete&module=Reports&record=' + id,
					onComplete: function (response) {
						getObj ('reportContents').innerHTML = response.responseText;
					}
				}
			);
		} else {
			return false;
		}
	}
	function MoveReport (id, foldername) {
		fninvsh ('folderLay');
		var folderids = getObj ('folder_ids').value,
			folderid_array = folderids.split (','),
			idstring = '',
			count = 0,
			i, row;
		for (i = 0; i < folderid_array.length; i++) {
			var selectopt_id = 'selected_id' + folderid_array[ i ];
			var objSelectopt = getObj (selectopt_id);
			if (objSelectopt != null) {
				var length_folder = getObj (selectopt_id).length;
				if (length_folder != undefined) {
					var cur_rep = getObj (selectopt_id);
					for (row = 0; row < length_folder; row++) {
						var currep_id = cur_rep[ row ].value;
						if (cur_rep[ row ].checked) {
							count++;
							idstring = currep_id + ':' + idstring;
						}
					}
				} else {
					if (getObj (selectopt_id).checked) {
						count++;
						idstring = getObj (selectopt_id).value + ':' + idstring;
					}
				}
			}
		}
		if (idstring != '') {
			if (confirm ({/literal}"{$APP.MOVE_REPORT_CONFIRMATION}" + foldername + "{$APP.FOLDER}"{literal})) {
				new Ajax.Request (
					'index.php',
					{
						queue:      { position: 'end', scope: 'command' },
						method:     'post',
						postBody:   'action=ReportsAjax&file=ChangeFolder&module=Reports&folderid=' + id + '&idlist=' + idstring,
						onComplete: function (response) {
							getObj ('reportContents').innerHTML = response.responseText;
						}
					}
				);
			}
		} else {
			alert ({/literal}'{$APP.SELECT_ATLEAST_ONE_REPORT}'{literal});
		}
	}
{/literal}
</script>
{/strip}