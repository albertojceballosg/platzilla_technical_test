<style type="text/css">
	#mass-edit-modal .required {
		display: none;
	}
	#mass-mail-modal .required {
		color: #FF0000;
	}
	#mass-mail-modal label {
		font-size: 1.2em;
		font-weight: 300;
	}
	#baseList-searh-group {
		margin-right:  .0em !important;
		margin-bottom: .0em !important;
		margin-top:    .0em !important;
		padding-top:   .0em !important;
		width:         100% !important;
		top:           .4em !important;
	}

	#baseList-select-list-group {
		margin-right:  .0em !important;
		margin-bottom: .0em !important;
		margin-top:    .0em !important;
		padding-top:   .0em !important;
		width:         100% !important;
		top:           .4em !important;
	}

	#massdelete {
		min-height: 200px!important;
	}

    @media (min-width: 768px) and (max-width: 990px) {
        #baseList-searh-group {
            margin-top: .25em !important;
            top:        .4em !important;
        }

        #baseList-select-list-group {
            margin-top: .25em !important;
            top:        .4em !important;
            float:      left !important;
        }

    }
	@media (min-width: 481px) and (max-width: 767px) {
		#baseList-searh-group {
			margin-top: .25em !important;
			top:        .4em !important;
		}

		#baseList-select-list-group {
			margin-top: .25em !important;
			top:        .4em !important;
			float:      left !important;
		}

	}
	@media (max-width: 480px) {
		#baseList-searh-group {
            margin-top:     .25em !important;
			top:           .4em !important;
		}

		#baseList-select-list-group {
            margin-top: .25em !important;
			top:        .4em !important;
			float:      left !important;
		}

        #baseList-profileids {
            margin-top:   .25em !important;
            margin-left:  0.0em !important;
            padding-left: 0.0em !important;
            float:        left !important;
        }

	}
	@media (max-width: 320px) {
		#baseList-searh-group {
            margin-top: .25em !important;
			top:        .4em !important;
            float:      left !important;
		}

		#baseList-select-list-group {
            margin-top: .25em !important;
			top:        .4em !important;
			float:      left !important;
		}
        #baseList-profileids {
            margin-top:   .25em !important;
            margin-left:  0.0em !important;
            padding-left: 0.0em !important;
            float:        left !important;
        }
	}
</style>

<link type="text/css" href="themes/centaurus/css/libs/ns-default.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/libs/ns-style-growl.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/libs/ns-style-bar.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/libs/ns-style-attached.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/libs/ns-style-other.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/libs/ns-style-theme.css" rel="stylesheet" />
<link type="text/css" href="themes/centaurus/css/compiled/pipeline.css" rel="stylesheet" />
<link type="text/css" href="modules/Settings/editable-fields-utils.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/nifty-component.css" />
<script type="text/javascript" src="modules/Settings/editable-fields-utils.js"></script>
<script type="text/javascript" src="include/js/ListView.js"></script>
<script type="text/javascript" src="include/js/search.js"></script>
<script type="text/javascript" src="include/js/Merge.js"></script>
<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
<script type="text/javascript" src="themes/centaurus/js/modernizr.custom.js"></script>
<script type="text/javascript" src="themes/centaurus/js/snap.svg-min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
<script type="text/javascript" src="themes/centaurus/js/notificationFx.js"></script>
<script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/moment.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript">
	var typeofdata = {
		'C': [ 'e', 'n' ],
		'D': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		'DT': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		'E': [ 'e', 'n', 's', 'ew', 'c', 'k' ],
		'I': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		'N': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		'NN': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		'T': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
		'V': [ 'e', 'n', 's', 'ew', 'c', 'k' ]
	};

	var fLabels = {
		'c':  "{$APP.contains}",
		'e':  "{$APP.is}",
		'ew': "{$APP.ends_with}",
		'g':  "{$APP.greater_than}",
		'h':  "{$APP.greater_or_equal}",
		'k':  "{$APP.does_not_contains}",
		'l':  "{$APP.less_than}",
		'm':  "{$APP.less_or_equal}",
		'n':  "{$APP.is_not}",
		's':  "{$APP.begins_with}"
	};
	var noneLabel;
	function trimfValues (value) {
		var string_array = value.split (":");
		return string_array[ 4 ];
	}

	function updatefOptions (sel, opSelName) {
		var selObj = document.getElementById (opSelName),
			fieldtype = null,
			currOption = selObj.options[ selObj.selectedIndex ],
			currField = sel.options[ sel.selectedIndex ],
			ops, nMaxVal, nLoop;

		if (currField.value != null && currField.value.length != 0) {
			fieldtype = trimfValues (currField.value);
			fieldtype = fieldtype.replace (/\\'/g, '');
			ops = typeofdata[ fieldtype ];
			if (ops != null) {
				nMaxVal = selObj.length;
				for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
					selObj.remove (0);
				}
				for (var i = 0; i < ops.length; i++) {
					var label = fLabels[ ops[ i ] ];
					if (label == null) {
						continue;
					}
					var option = new Option (fLabels[ ops[ i ] ], ops[ i ]);
					selObj.options[ i ] = option;
					if (currOption != null && currOption.value == option.value) {
						option.selected = true;
					}
				}
			}
		} else {
			nMaxVal = selObj.length;
			for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
				selObj.remove (0);
			}
			selObj.options[ 0 ] = new Option ('None', '');
			if (currField.value == '') {
				selObj.options[ 0 ].selected = true;
			}
		}
	}
</script>
<script type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>
<script type="text/javascript">
	function checkgroup () {
		if ($ ("group_checkbox").checked) {
			document['change_ownerform_name']['lead_group_owner' ].style.display = "block";
			document['change_ownerform_name']['lead_owner'].style.display = "none";
		} else {
			document['change_ownerform_name']['lead_owner'].style.display = "block";
			document['change_ownerform_name']['lead_group_owner'].style.display = "none";
		}
	}

	function callSearch (searchtype) {
		var search_fld_val = jQuery ('input[name=search_field]:checked').val (),
			search_txt_val = encodeURIComponent (jQuery ('#search_text').val ()),
			urlstring      = '',
			p_tab, advft_criteria, advft_criteria_groups;

		if (searchtype == 'Basic') {
			p_tab = document.getElementsByName ("parenttab");
			urlstring = 'search_field=' + search_fld_val + '&searchtype=BasicSearch&search_text=' + search_txt_val + '&';
			urlstring = urlstring + 'parenttab=' + p_tab[ 0 ].value + '&';
		} else if (searchtype == 'Advanced') {
			checkAdvancedFilter ();
			advft_criteria = $ ('advft_criteria').value;
			advft_criteria_groups = $ ('advft_criteria_groups').value;
			urlstring += '&advft_criteria=' + advft_criteria + '&advft_criteria_groups=' + advft_criteria_groups + '&';
			urlstring += 'searchtype=advance&';
		}
		jQuery ("#status").show ();

		new Ajax.Request ('index.php', {
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   urlstring + 'query=true&file=index&module={$MODULE}&action={$MODULE}Ajax&ajax=true&search=true',
			onComplete: function (response) {
				var result;
				jQuery ("#status").hide ();
				result = response.responseText.split ('&#&#&#');
				jQuery ("#ListViewContents-{$TAB_HOME_ID}").html (result[ 2 ]);
				if (result[ 1 ] != '') {
					alert (result[ 1 ]);
				}
			}
		});
		return false;
	}

	function alphabetic (module, url, dataid) {
		var i, data_td_id;

		for (i = 1; i <= 26; i++) {
			data_td_id = 'alpha_' + eval (i);
			getObj (data_td_id).className = 'searchAlph';
		}
		getObj (dataid).className = 'searchAlphselected';
		$ ("status").style.display = "inline";

		new Ajax.Request ('index.php', {
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   'module=' + module + '&action=' + module + 'Ajax&file=index&ajax=true&search=true&' + url,
			onComplete: function (response) {
				var result;

				$ ("status").style.display = "none";
				result = response.responseText.split ('&#&#&#');
				$ ("ListViewContents").innerHTML = result[ 2 ];
				if (result[ 1 ] != '') {
					alert (result[ 1 ]);
				}
				$ ('basicsearchcolumns').innerHTML = '';
			}
		});
	}
</script>
<div id="ListViewContents-{$TAB_HOME_ID}">
    <div class="tab-content">
        <div id="VIEW-TASK-{$TAB_HOME_ID}"
             class="tab-pane fade in">
            {include file='utils/HTMLPageLoanding.tpl'}
        </div>
        <div id="ListViewHomeContents-{$TAB_HOME_ID}"
             data-current-module="{$MODULE}"
             class="tab-pane fade in active">
	        {include file="Home/TabsContents/ListViewHomeEntries.tpl"}
        </div>
        <div id="VIEW-KANBAN-{$TAB_HOME_ID}"
             class="tab-pane fade in">
            {include file='utils/HTMLPageLoanding.tpl'}
        </div>
        <div id="VIEW-CALENDAR-{$TAB_HOME_ID}"
             class="tab-pane fade in">
            {include file='utils/HTMLPageLoanding.tpl'}
        </div>
    </div>
</div>
<div id="massedit" class="layerPopup" style="display: none; width: 80%;">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine">
	<tr>
		<td class="layerPopupHeading" align="left" width="60%">{$APP.LBL_MASSEDIT_FORM_HEADER}</td>
		<td>&nbsp;</td>
		<td align="right" width="40%"><img onClick="fninvsh('massedit');" title="{$APP.LBL_CLOSE}" alt="{$APP.LBL_CLOSE}" style="cursor:pointer;" src="{'close.gif'|@vtiger_imageurl:$THEME}" align="absmiddle" border="0"></td>
	</tr>
	</table>
	<div id="massedit_form_div"></div>
</div>
<script type="text/javascript">
	function ajaxChangeStatus (statusname) {
		var viewid, idstring, searchurl, tplstart, url, urlstring;
		$ ("status").style.display = "inline";
		viewid = document.getElementById ('viewname').options[ document.getElementById ('viewname').options.selectedIndex ].value;
		idstring = document.getElementById ('idlist').value;
		searchurl = document.getElementById ('search_url').value;
		tplstart = '&';
		if (gstart != '') {
			tplstart = tplstart + gstart;
		}
		if (statusname == 'status') {
			fninvsh ('changestatus');
			url = '&leadval=' + document.getElementById ('lead_status').options[ document.getElementById ('lead_status').options.selectedIndex ].value;
			urlstring = "module=Users&action=updateLeadDBStatus&return_module=Leads" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
		} else if (statusname == 'owner') {
			if ($ ("user_checkbox").checked) {
				fninvsh ('changeowner');
				url = '&owner_id=' + document.getElementById ('lead_owner').options[ document.getElementById ('lead_owner').options.selectedIndex ].value;
				urlstring = "module=Users&action=updateLeadDBStatus&return_module={$MODULE}" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
			} else {
				fninvsh ('changeowner');
				url = '&owner_id=' + document.getElementById ('lead_group_owner').options[ document.getElementById ('lead_group_owner').options.selectedIndex ].value;
				urlstring = "module=Users&action=updateLeadDBStatus&return_module={$MODULE}" + tplstart + url + "&viewname=" + viewid + "&idlist=" + idstring + searchurl;
			}
		}
		new Ajax.Request ('index.php', {
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   urlstring,
			onComplete: function (response) {
				var result;

				$ ("status").style.display = "none";
				result = response.responseText.split ('&#&#&#');
				$ ("ListViewContents-{$TAB_HOME_ID}").innerHTML = result[ 2 ];
				if (result[ 1 ] != '') {
					alert (result[ 1 ]);
				}
				$ ('basicsearchcolumns').innerHTML = '';
			}
		});
	}
</script>
{* Implementando notificaciones *}
{if $MENSAJE neq ''}
<script type="text/javascript">
(function () {
	new NotificationFx ({
		message : '<span class="icon fa fa-exclamation-circle fa-2x"></span><p>{$MENSAJE}</p>',
		layout : 'bar',
		effect : 'slidetop',
		type : {if $TIPO_MENSAJE EQ 'fail'} 'error' {else} 'success' {/if} , // notice, warning or error
		onClose : function () {
		}
	}).show ();
}) ();
</script>
{/if}
<script type="text/javascript">
{$BUILD_SEARCH}
</script>
{if ($IS_FIRST_CONNECTION)}
{include file='modal/FirstConnectionModal.tpl'}
{/if}
<style type="text/css">
	.md-modal {
		max-width:75% !important;
		min-width:65% !important;
		z-index: 1010 !important;
	}
	.md-effect-7-2 {
		left:35%!important;
		top:0 !important;
	}
	.modal-footer {
		text-align: center;
	}
</style>
{*math equation= rand() assign= "idModalDetalView"*}
{assign var="idModalDetalView" value=""}
<div class="md-modal md-effect-7-2" id="modal-detail-row-{$TAB_HOME_ID}">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close">&times;</button>
			<h4 class="modal-title">Modal title</h4>
		</div>
		<div id="modal-detail-body-{$idModalDetalView}" data-status="0"  class="modal-body">
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary md-close" data-status="0">Cerrar</button>
		</div>
	</div>
</div>
<div class="md-overlay"></div>
<script type="text/html" id="mass-edit-modal-template">
	<div class="modal fade" id="mass-edit-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
		<form action="index.php" method="post">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="MassEditSave" />
			<div class="modal-dialog" style="width: 90vw;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body" style="max-height: 70vh; min-height: 70vh; overflow-x: hidden; overflow-y: auto;"></div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Guardar</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</script>
<script type="text/html" id="mass-mail-modal-template">
	<div class="modal fade" id="mass-mail-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
		<form action="index.php" method="post" onsubmit="MassActionsUtils.sendEmail (this); return false;">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="MassMailSend" />
			<input type="hidden" name="Ajax" value="true" />
			<div class="modal-dialog" style="width: 90vw;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Enviar correo masivo</h4>
					</div>
					<div class="modal-body" style="max-height: 70vh; min-height: 70vh; overflow-x: hidden; overflow-y: auto;">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input"><label for="mass-mail-language">Idioma: <span class="required">*</span></label></div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="mass-mail-language" name="language" class="form-control parameter-value" onchange="MassActionsUtils.setTemplateOptions (this);"></select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input"><label for="mass-mail-template-name">Plantilla: <span class="required">*</span></label></div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="mass-mail-template-name" name="templatename" class="form-control parameter-value" onchange="MassActionsUtils.setVariableOptions (this);"></select>
								</div>
							</div>
						</div>
						<div class="col-md-12 parameter">
							<div class="col-md-2">
								<div class="label-input"><label for="mass-mail-recipients-type">Destinatarios: <span class="required">*</span></label></div>
							</div>
							<div class="col-md-3 form-group">
								<div class="input-group" style="width: 100%;">
									<select id="mass-mail-recipients-type" name="recipients[type]" class="form-control parameter-type" onchange="MassActionsUtils.setParameterValue (this);">
										<option value=""></option>
										<option value="SOURCE FIELD">Campo en los registros seleccionados</option>
										<option value="LITERAL">Valor</option>
										<option value="VARIABLE">Variable del sistema</option>
									</select>
								</div>
							</div>
							<div class="form-group col-md-7 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" name="recipients[value]" value="" class="form-control parameter-value" placeholder="" data-type="LITERAL" disabled="disabled" style="display: none;" />
									<select id="mass-mail-recipients-source-fields" name="recipients[value]" class="form-control parameter-value" title="" data-type="SOURCE FIELD" disabled="disabled" style="display: none;">
										<option></option>
										<option value="record_id">(El registro que se está procesando)</option>
									</select>
									<div class="input-group variable" style="display: none;">
										<input type="text" name="recipients[value]" class="form-control parameter-value" placeholder="" data-type="VARIABLE" disabled="disabled" style="display: none;" />
										<div class="input-group-btn">
											<button class="btn btn-default" type="button" title="Campos en la fuente de datos" onclick="MassActionsUtils.openFieldsModal (this);"><i class="fa fa-code"></i></button>
											<button class="btn btn-default" type="button" title="Variables de sistema" onclick="MassActionsUtils.openVariablesModal (this);"><i class="fa fa-cogs"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="mass-mail-variables-section" class="row" style="display: none;">
							<h4 class="col-md-12">Variables</h4>
							<div id="mass-mail-variables" class="col-md-12"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Enviar</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</script>
<script type="text/html" id="mass-mail-modal-template-variable">
	<div class="col-md-12 parameter">
		<div class="col-md-2">
			<div class="label-input">
				<input type="text" class="form-control variable-name" placeholder="" readonly="readonly" />
			</div>
		</div>
		<div class="col-md-3 form-group">
			<div class="input-group" style="width: 100%;">
				<select class="form-control parameter-type" title="" onchange="MassActionsUtils.setParameterValue (this);">
					<option value=""></option>
					<option value="SOURCE FIELD">Campo en los registros seleccionados</option>
					<option value="LITERAL">Valor</option>
					<option value="VARIABLE">Variable del sistema</option>
				</select>
			</div>
		</div>
		<div class="form-group col-md-7 field-container">
			<div class="input-group" style="width: 100%;">
				<input type="text" class="form-control parameter-value" placeholder="" data-type="LITERAL" />
				<select class="form-control parameter-value" title="" data-type="SOURCE FIELD">
					<option></option>
					<option value="record_id">(El registro que se está procesando)</option>
				</select>
				<div class="input-group variable">
					<input type="text" class="form-control parameter-value" placeholder="" data-type="VARIABLE" />
					<div class="input-group-btn">
						<button class="btn btn-default" type="button" title="Campos en la fuente de datos" onclick="MassActionsUtils.openFieldsModal (this);"><i class="fa fa-code"></i></button>
						<button class="btn btn-default" type="button" title="Variables del sistema" onclick="MassActionsUtils.openVariablesModal (this);"><i class="fa fa-cogs"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html"  id="mass-mail-auxiliary-modal-template">
	<div class="modal fade" id="mass-mail-auxiliary-modal" tabindex="-1" role="dialog" aria-hidden="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table">
							<tbody></tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/javascript"  src="include/js/mass-actions-utils.js"></script>
{include file='modules/instancesdatasharing/SyncsModal.tpl'}
<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
<script type="text/javascript" src="themes/centaurus/js/modalDetailOverListView.js"></script>
<script id="detail-over-listview" data-id-modal="{$idModalDetalView}" type="text/javascript" src="themes/centaurus/js/modal-detail-view.js"></script>