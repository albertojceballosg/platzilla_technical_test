{strip}
<link href="{$TEMPLATE_PATH}/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/morris/morris.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/AdminLTE.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/iCheck/all.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" />
<link href="{$TEMPLATE_PATH}/css/timepicker/bootstrap-timepicker.min.css" rel="stylesheet" />
<script src="{$TEMPLATE_PATH}/js/bootstrap.min.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
<script src="modules/boxscore/boxscore.js" type="text/javascript"></script>
<style type="text/css">
	th {
		text-align: center;
	}
</style>
<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
		var count = {/literal}{if (isset ($RECORD))}{$BOX_SCORE->boxs[0]['all_objetivo']|@count}{else}0{/if}{literal};

		jQuery ("#addobjetive").click (function () {
			count = count + 1;
			var html = '<tr style="background-color: #e8e8e8;" role="row" id="fileObject_' + count + '"> ' +
							'<td>' +
								'<select class="form-control mesobjetivo" id="mesobjetivo_' + count + '" name="mesobjetivo[]">' +
									'<option value="">{/literal}{$MOD.LBL_SELECTION_MONTH}{literal}</option>' +
									'<option value="01">{/literal}{$MOD.LBL_ENERO}{literal}</option>' +
									'<option value="02">{/literal}{$MOD.LBL_FEBRERO}{literal}</option>' +
									'<option value="03">{/literal}{$MOD.LBL_MARZO}{literal}</option>' +
									'<option value="04">{/literal}{$MOD.LBL_ABRIL}{literal}</option>' +
									'<option value="05">{/literal}{$MOD.LBL_MAYO}{literal}</option>' +
									'<option value="06">{/literal}{$MOD.LBL_JUNIO}{literal}</option>' +
									'<option value="07">{/literal}{$MOD.LBL_JULIO}{literal}</option>' +
									'<option value="08">{/literal}{$MOD.LBL_AGOSTO}{literal}</option>' +
									'<option value="09">{/literal}{$MOD.LBL_SEPTIEMBRE}{literal}</option>' +
									'<option value="10">{/literal}{$MOD.LBL_OCTUBRE}{literal}</option>' +
									'<option value="11">{/literal}{$MOD.LBL_NOVIEMBRE}{literal}</option>' +
									'<option value="12">{/literal}{$MOD.LBL_DICIEMBRE}{literal}</option>' +
								'</select>' +
							'</td>' +
							'<td>' +
								'<select class="form-control operador" id="operador_' + count + '" name="operador[]">' +
									'<option value="menor-igual">&lt;=</option>' +
									'<option value="mayor-igual">&gt;=</option>' +
								'</select>' +
							'</td>' +
							'<td>' +
								'<input type="text" class="form-control objetivo" onkeyup="validateDecimal32General(\'objetivo_' + count + '\')" value="" id="objetivo_' + count + '" name="objetivo[]" placeholder="{/literal}{$MOD.Ingresar} {$MOD.LBL_OBJECT}{literal}">' +
							'</td>' +
							'<td>' +
								'<input width="16" type="image" height="16" title="Delete" src="themes/images/remove.png" onclick="deleteOtherOperation(fileObject_' + count + ')">' +
							'</td>' +
						'</tr>';
			jQuery ("#bodyObjtable").append (html);
		});

		// Validaciones del formulario para creación de KPI
		jQuery ("#boxscore").submit (function (event) {
			var boxscore = jQuery ("#box_score");
			if ((boxscore.val () == 'undefined') || (boxscore.val () == null) || (boxscore.val () == '')) {
				alert (alert_arr.SAVE_NAME_KPI);
				boxscore.focus ();
				event.preventDefault ();
				return false;
			}

			// Validación del mes objetivo
			var val = true,
				mesObjetivo = jQuery ("#bodyObjtable").find (".mesobjetivo");
			mesObjetivo.each (function (index, element) {
				var filter0 = jQuery (element).val ();
				if (filter0 != '') {
					if (filter0 != '') {
						mesObjetivo.each (function (c, element1) {
							var filter1 = jQuery (element1).val ();
							if (filter1 != '') {
								if (c >= (index + 1) && filter1 != '') {
									if (filter0 == filter1) {
										alert (alert_arr.REPETEAT_MESOBJECTIVE);
										jQuery (element1).focus ();
										val = false;
										return false;
									}
								}
							} else {
								alert (alert_arr.SAVE_MESOBJECTIVE);
								jQuery (element1).focus ();
								val = false;
								return false;
							}
						});

					}
					if (val == false) {
						return false;
					}
				} else {
					alert (alert_arr.SAVE_MESOBJECTIVE);
					jQuery (element).focus ();
					val = false;
					return false;
				}
			});

			if (val == false) {
				event.preventDefault ();
				return false;
			}
			//****************************************************************

			//Validación del valor objetivo
			//****************************************************************
			mesObjetivo.each (function (index, element) {
				var filter0 = jQuery (element).val ();

				if (filter0 == '') {
					alert (alert_arr.SAVE_OBJECTIVE);
					jQuery (element).focus ();
					val = false;
					return false;
				}
			});

			if (val == false) {
				event.preventDefault ();
				return false;
			}
			//****************************************************************

			var idUser = jQuery ("#idUSER").val ();
			if ((idUser != '') && (idUser == '1')) {
				var dao0 = jQuery ("#dao_inf_0");
				if ((dao0.val () == 'undefined') || (dao0.val () == null) || (dao0.val () == '')) {
					alert (alert_arr.SAVE_CUMPL);
					dao0.focus ();
					event.preventDefault ();
					return false;
				}

				var dao1 = jQuery ("#dao_inf_1");
				if ((dao1.val () == 'undefined') || (dao1.val () == null) || (dao1.val () == '')) {
					alert (alert_arr.SAVE_CUMPL);
					dao1.focus ();
					event.preventDefault ();
					return false;
				}

				var tipo0 = jQuery ("#tipo_dao_inf_0");
				var tipo1 = jQuery ("#tipo_dao_inf_1");
				if (jQuery ("#record").val () != '') {
					if (((dao0.val ().indexOf ('%') > 0) && (tipo0.val () != '%')) || ((dao0.val ().indexOf ('%') == -1) && (tipo0.val () == '%'))) {
						alert (alert_arr.FORMAT_PORCENTUAL);
						dao0.focus ();
						event.preventDefault ();
						return false;
					}

					if (((dao1.val ().indexOf ('%') > 0) && (tipo1.val () != '%')) || ((dao1.val ().indexOf ('%') == -1) && (tipo1.val () == '%'))) {
						alert (alert_arr.FORMAT_PORCENTUAL);
						dao1.focus ();
						event.preventDefault ();
						return false;
					}
				} else {
					//Validando que los valores del rango sean % o numeros
					if ((dao0.val ().indexOf ('%') > 0) && (dao1.val ().indexOf ('%') == -1)) {
						alert (alert_arr.FORMAT_PORCENTUAL_VAL);
						dao1.focus ();
						event.preventDefault ();
						return false;
					}

					if ((dao1.val ().indexOf ('%') > 0) && (dao0.val ().indexOf ('%') == -1)) {
						alert (alert_arr.FORMAT_PORCENTUAL_VAL);
						dao0.focus ();
						event.preventDefault ();
						return false;
					}

					if (dao0.val ().indexOf ('%') > 0) {
						tipo0.val ('%');
					}

					if (dao1.val ().indexOf ('%') > 0) {
						tipo1.val ('%');
					}
				}
			}
		});

		jQuery ("#mesobjetivo").change (function () {
			var record = jQuery ("input[name=record]").val ();
			var boxscoreid = jQuery ("input[name=boxscoreid]").val ();
			var monthsearch = jQuery ("#mesobjetivo").val ();
			new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=boxscore&action=boxscoreAjax&file=Searchkpiboxscore&record=' + record + '&boxscoreid=' + boxscoreid + '&monthsearch=' + monthsearch,
					onComplete: function (response) {
						if (response.responseText != '') {
							var str = response.responseText;
							var res = str.split ("---");
							var v = res[ 0 ].split ("@@");
							var v1 = res[ 1 ].split ("@@");

							jQuery ("#objetivo").val (v[ 1 ]);
							jQuery ("#boxscorecump_dao_0").val (v[ 2 ]);
							jQuery ("#dao_inf_0").val (v[ 3 ]);
							jQuery ("#tipo_dao_inf_0").val (v[ 5 ]);
							jQuery ("#operador").val (v[ 6 ]);

							jQuery ("#boxscorecump_dao_1").val (v1[ 2 ]);
							jQuery ("#dao_inf_1").val (v1[ 3 ]);
							jQuery ("#tipo_dao_inf_1").val (v1[ 5 ]);

						}
					}
				}
			);
		});
	});
{/literal}
</script>
<div id="dashboard_box_score">
	<section class="content">
		<div class="row">
			<div class="col-lg-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">{if (isset ($RECORD))}{$MOD.MESS_EDIT_BOX_SCORE}{else}{$MOD.MESS_ADD_BOX_SCORE}{/if}</h3>
					</div>
					<form role="form" name="boxscore" id="boxscore" action="index.php?module=boxscore&action=SaveBox" method="post">
						<input type="hidden" name="tipo" value="{if ($BOX_SCORE->boxs[0]['tipo'])}{$BOX_SCORE->boxs[0]['tipo']}{else}{$TYPE}{/if}">
						<input type="hidden" name="record" id="record" value="{$BOX_SCORE->boxs[0]['box_score_dataid']}">
						<input type="hidden" name="boxscoreid" value="{$ACCOUNT_ID}">
						<input type="hidden" name="monthsearch" value="{$MONTH_SEARCH}">
						<input type="hidden" name="box_score_objectiveid" value="{$BOX_SCORE->boxs[0]['box_score_objectiveid']}">
						<input type="hidden" name="idUSER" id="idUSER" value="{$CURRENT_USER->id}">
						<div class="box-body">
							<div class="form-group">
								<label for="box_score">Box Score</label>
								<input type="text" class="form-control" value="{$BOX_SCORE->boxs[0]['box_score']}" id="box_score" name="box_score" placeholder="{$MOD.Ingresar} Box Score">
							</div>
							<div class="form-group">
								<label for="description">{$MOD.LBL_DESCRIPTION}</label>
								<textarea id="description" class="form-control" rows="2" tabindex="" name="description">{$BOX_SCORE->boxs[0]['description']}</textarea>
							</div>
							<div class="form-group" align="right">
								<button type="button" name="addobjetive" id="addobjetive" class="btn btn-primary">{$MOD.LBL_ADD_BUTTON_OBJECT}</button>
							</div>
							<div class="form-group">
								<table id="table-objetive" class="table table-hover dataTable no-footer" role="grid" width="100%">
									<thead>
									<tr role="row">
										<th>{$MOD.LBL_MONTH}</th>
										<th>{$MOD.LBL_OPERATOR}</th>
										<th>{$MOD.LBL_OBJECT}</th>
										<th>&nbsp;&nbsp;</th>
									</tr>
									</thead>
									<tbody id="bodyObjtable">
{if (isset ($RECORD)) && (count ($BOX_SCORE->boxs[0]['all_objetivo']) == 0)}
									<tr style="background-color: #e8e8e8;" role="row" id="fileObject_0">
										<td>
											<select class="form-control mesobjetivo" id="mesobjetivo_0" name="mesobjetivo[]" title="">
												<option value="">{$MOD.LBL_SELECTION_MONTH}</option>
												<option value="01">{$MOD.LBL_ENERO}</option>
												<option value="02">{$MOD.LBL_FEBRERO}</option>
												<option value="03">{$MOD.LBL_MARZO}</option>
												<option value="04">{$MOD.LBL_ABRIL}</option>
												<option value="05">{$MOD.LBL_MAYO}</option>
												<option value="06">{$MOD.LBL_JUNIO}</option>
												<option value="07">{$MOD.LBL_JULIO}</option>
												<option value="08">{$MOD.LBL_AGOSTO}</option>
												<option value="09">{$MOD.LBL_SEPTIEMBRE}</option>
												<option value="10">{$MOD.LBL_OCTUBRE}</option>
												<option value="11">{$MOD.LBL_NOVIEMBRE}</option>
												<option value="12">{$MOD.LBL_DICIEMBRE}</option>
											</select>
										</td>
										<td>
											<select class="form-control operador" id="operador_0" name="operador[]" title="">
												<option value="menor-igual">&lt;=</option>
												<option value="mayor-igual">&gt;=</option>
											</select>
										</td>
										<td>
											<input type="text" class="form-control objetivo" onkeyup="validateDecimal32General('objetivo_0')" value="" id="objetivo_0" name="objetivo[]" placeholder="{$MOD.Ingresar} {$MOD.LBL_OBJECT}">
										</td>
										<td>
											&nbsp;&nbsp;
										</td>
									</tr>
{elseif (isset ($RECORD))}
	{for $i = 0; $i < count($BOX_SCORE->boxs[0]['all_objetivo']); $i++}
									<tr style="background-color: #e8e8e8;" role="row" id="fileObject_{$i}">
										<td>
											<select class="form-control mesobjetivo" id="mesobjetivo_{$i}" name="mesobjetivo[]" title="">
												<option value="">{$MOD.LBL_SELECTION_MONTH}</option>
												<option value="01"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '01')} selected="selected"{/if}>{$MOD.LBL_ENERO}</option>
												<option value="02"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '02')} selected="selected"{/if}>{$MOD.LBL_FEBRERO}</option>
												<option value="03"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '03')} selected="selected"{/if}>{$MOD.LBL_MARZO}</option>
												<option value="04"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '04')} selected="selected"{/if}>{$MOD.LBL_ABRIL}</option>
												<option value="05"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '05')} selected="selected"{/if}>{$MOD.LBL_MAYO}</option>
												<option value="06"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '06')} selected="selected"{/if}>{$MOD.LBL_JUNIO}</option>
												<option value="07"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '07')} selected="selected"{/if}>{$MOD.LBL_JULIO}</option>
												<option value="08"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '08')} selected="selected"{/if}>{$MOD.LBL_AGOSTO}</option>
												<option value="09"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '09')} selected="selected"{/if}>{$MOD.LBL_SEPTIEMBRE}</option>
												<option value="10"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '10')} selected="selected"{/if}>{$MOD.LBL_OCTUBRE}</option>
												<option value="11"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '11')} selected="selected"{/if}>{$MOD.LBL_NOVIEMBRE}</option>
												<option value="12"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['month_apli'] == '12')} selected="selected"{/if}>{$MOD.LBL_DICIEMBRE}</option>
											</select>
										</td>
										<td>
											<select class="form-control operador" id="operador_{$i}" name="operador[]" title="">
												<option value="menor-igual"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['operator'] == 'menor-igual')} selected="selected"{/if}>&lt;=</option>
												<option value="mayor-igual"{if ($BOX_SCORE->boxs[0]['all_objetivo'][$i]['operator'] == 'mayor-igual')} selected="selected"{/if}>&gt;=</option>
											</select>
										</td>
										<td>
											<input type="text" class="form-control objetivo" onkeyup="validateDecimal32General('objetivo_{$ii}')" value="{$BOX_SCORE->boxs[0]['all_objetivo'][$i]['objective']|replace: '.': ','}" id="objetivo_{$i}" name="objetivo[]" placeholder="{$MOD.Ingresar} {$MOD.LBL_OBJECT}">
										</td>
										<td>
		{if ($i == 0)}
											&nbsp;&nbsp;
		{else}
											<input width="16" type="image" height="16" title="Delete" src="themes/images/remove.png" onclick="deleteOtherOperation(fileObject_{$i})">
		{/if}
										</td>
									</tr>
	{/for}
{/if}
									</tbody>
								</table>
							</div>
							<div class="form-group">
								<table id="table-example" class="table table-hover dataTable no-footer" role="grid" width="100%">
									<tbody>
									<tr class="odd" role="row">
										<td style=" font-size:12px;">{$MOD.PERCENT_VAR_OF_TARGET}
											<input type="hidden" name="boxscorecump_dao_0" value="{$FULFILLMENT[0]['id']}">
											<input type="hidden" name="cumplimiento_0" value="De acuerdo al objetivo">
										</td>
										<td>
											<input type="text"{if ($CURRENT_USER->id != 1)} readonly="readonly"{/if} class="form-control" maxlength="13" onkeyup="validateDecimal32General('dao_inf_0')" value="{$FULFILLMENT[0]['valor_varianza']|replace: '.': ','}{$FULFILLMENT[0]['tipo_varianza']}" id="dao_inf_0" name="dao_inf_0" placeholder="">
											<input type="hidden" name="tipo_dao_inf_0" id="tipo_dao_inf_0" value="{$FULFILLMENT[0]['tipo_varianza']}">
										</td>
									</tr>
									<tr class="even" role="row">
										<td style="font-size:12px;">{$MOD.PERCENT_VAR_CLOSE_TARGET}
											<input type="hidden" name="boxscorecump_dao_1" value="{$FULFILLMENT[1]['id']}">
											<input type="hidden" name="cumplimiento_1" value="Cerca del objetivo">
										</td>
										<td>
											<input type="text"{if ($CURRENT_USER->id != 1)} readonly="readonly"{/if} class="form-control" maxlength="13" onkeyup="validateDecimal32General('dao_inf_1')" value="{$FULFILLMENT[1]['valor_varianza']|replace: '.': ','}{$FULFILLMENT[1]['tipo_varianza']}" id="dao_inf_1" name="dao_inf_1" placeholder="">
											<input type="hidden" name="tipo_dao_inf_1" id="tipo_dao_inf_1" value="{$FULFILLMENT[1]['tipo_varianza']}">
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>
						<br />
						<br />
						<div class="box-footer">
							<button type="submit" name="submit" class="btn btn-primary">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
							&nbsp;
							<a class="btn btn-warning" href="index.php?module=boxscore&action=DetailView&record={$ACCOUNT_ID}&account_id={$ACCOUNT_ID}&monthsearch={$MONTH_SEARCH}">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>
{/strip}