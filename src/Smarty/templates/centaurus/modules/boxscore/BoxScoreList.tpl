{strip}
<style type="text/css">
{literal}
	.alert-grey {
		background-color: #eee;
	}
	.rgt {
		text-align: right;
	}
	th, .ctr {
		text-align: center;
	}
	.lft {
		text-align: left;
	}
{/literal}
</style>
<div class="row">
	<div class="col-lg-12">
		<h1><a href="index.php?module={$CURRENT_MODULE}&action=index">Box Score</a></h1>
	</div>
</div>
<form name="EditView" id="EditView" method="POST" action="index.php">
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<div id="reportrange" class="filter-block pull-left">
						<input type="hidden" name="module" id="module" value="boxscore">
						<input type="hidden" name="action" id="action" value="reporteCompuesto">
						<input type="hidden" name="record" id="record" value="{$RECORD}">
						<div class="form-group">
							<table>
								<tr>
									<td>
										<label>Desde:</label>
										<div class="input-group" style="width: 135px;">
											<div class="input-group-addon" id="btn_fecha_desde">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right" id="fecha_desde" name="fecha_desde" value="{$FROM}" placeholder="" />
										</div>
									</td>
									<td style="padding-left: 15px;">
										<label>Hasta:</label>
										<div class="input-group" style="width: 135px;">
											<div class="input-group-addon" id="btn_fecha_hasta">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right" id="fecha_hasta" name="fecha_hasta" value="{$TO}" placeholder="" />
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</header>
{if (!empty ($RECORD))}
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table">
							<tr>
								<th class="lft" style="">BOX SCORE</th>
								<th class="alert-grey ctr">Objetivo</th>
								<th class="alert-grey ctr">
									<select id="boxscoreselect_0" name="boxscoreselect[0]" class="form-control" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}"{if ($values.boxscoreid == $RECORD)} selected="selected"{/if}>{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_1" value="" name="activarBS_1" type="checkbox">
										<label for="activarBS_1">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_1" name="boxscoreselect[1]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_2" value="" name="activarBS_2" type="checkbox">
										<label for="activarBS_2">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_2" name="boxscoreselect[2]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_3" value="" name="activarBS_3" type="checkbox">
										<label for="activarBS_3">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_3" name="boxscoreselect[3]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_4" value="" name="activarBS_4" type="checkbox">
										<label for="activarBS_4">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_4" name="boxscoreselect[4]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_5" value="" name="activarBS_5" type="checkbox">
										<label for="activarBS_5">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_5" name="boxscoreselect[5]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_6" value="" name="activarBS_6" type="checkbox">
										<label for="activarBS_6">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_6" name="boxscoreselect[6]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
								<th class="alert-grey ctr">
									<div class="checkbox-nice">
										<input id="activarBS_7" value="" name="activarBS_7" type="checkbox">
										<label for="activarBS_7">Agregar boxscore</label>
									</div>
									<select id="boxscoreselect_7" name="boxscoreselect[7]" class="form-control" disabled="disabled" title="">
										<option value="">-</option>
	{foreach $BOX_SCORES as $values}
										<option value="{$values.boxscoreid}">{$values.titulo}</option>
	{/foreach}
									</select>
								</th>
							</tr>
	{foreach $BOX_SCORE->boxs as $boxScoreData}
							<tr id="row-{$boxScoreData.box_score_dataid}">
								<td class="alert-warning show-tools">
									<div class="checkbox-nice">
										<input id="checkbox-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBS[]" type="checkbox">
										<label for="checkbox-{$boxScoreData.box_score_dataid}">{$boxScoreData.box_score}</label>
									</div>
								</td>
								<td class="alert-grey rgt">{$boxScoreData.objetivo}</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-1-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[1][]" type="checkbox">
										<label for="checkbox-1-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice text-center">
										<input id="checkbox-2-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[2][]" type="checkbox" disabled="disabled">
										<label for="checkbox-2-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-3-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[3][]" type="checkbox" disabled="disabled">
										<label for="checkbox-3-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-4-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[4][]" type="checkbox" disabled="disabled">
										<label for="checkbox-4-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-5-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[5][]" type="checkbox" disabled="disabled">
										<label for="checkbox-5-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-6-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[6][]" type="checkbox" disabled="disabled">
										<label for="checkbox-6-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-7-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[7][]" type="checkbox" disabled="disabled">
										<label for="checkbox-7-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
								<td class="alert-grey rgt">
									<div class="checkbox-nice">
										<input id="checkbox-8-{$boxScoreData.box_score_dataid}" value="{$boxScoreData.box_score_dataid}" name="idsBSlista[8][]" type="checkbox" disabled="disabled">
										<label for="checkbox-8-{$boxScoreData.box_score_dataid}"></label>
									</div>
								</td>
							</tr>
	{/foreach}
						</table>
						<div class="text-center">
							<button class="btn btn-info" type="button" onclick="jQuery('#action').val('reporteBS');jQuery('#EditView').submit();">Generar Gráficos</button>
						</div>
					</div>
				</div>
{else}
				<p class="text-center">Seleccione un boxscore</p>
{/if}
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript">
{literal}
	jQuery ("[fn='delete-row']").click (function (e) {
		e.preventDefault ();
		if (!confirm ("Esta seguro que desea eliminar el registro?")) {
			return false;
		}
		var rowid = this.id;
		jQuery.ajax ({
			type: "POST",
			url:  "index.php",
			data: { module: "boxscore", action: "boxscoreAjax", file: "DeleteBox", record: rowid, 'delete': 'true' }
		}).done (function () {
			jQuery ("#row-" + rowid).fadeOut (function () {
				jQuery ("#row-" + rowid).remove ();
			});
		});
	});

	function editvalue (id) {
		jQuery ("#bs-id-" + id).hide ();
		jQuery ("#bs-ed-id-" + id).show ();
		jQuery ("#td-ed-" + id).removeClass ("show-tools");
	}

	function canceledit (id) {
		jQuery ("#bs-ed-id-" + id).hide ();
		jQuery ("#td-ed-" + id).addClass ("show-tools");
		jQuery ("#bs-id-" + id).show ();
	}

	function guardarValor (id) {
		jQuery ("#bs-ed-id-" + id).hide ();
		jQuery ("#td-ed-" + id).addClass ("show-tools");
		jQuery ("#bs-id-" + id).show ();
	}

	jQuery ('#fecha_desde').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	jQuery ('#fecha_hasta').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });

	function openPopup (recordid) {
		window.open ("index.php?module=Accounts&action=Popup&popuptype=specific_contact_account_address&form=TasksEditView&form_submit=false&fromlink=&recordid=" + recordid, "test", "width=640,height=602,resizable=0,scrollbars=0");
		return false;
	}

	var account_id = {/literal}'{$ACCOUNT_ID}'{literal};
	function checkAccountid () {
		var newAcc = jQuery ('#account_id').val ();
		if (newAcc != account_id) {
			account_id = newAcc;
			jQuery ('#EditView').submit ();
		}
	}

	jQuery ("[id^='activarBS_']").on ("change", "", function () {
		var check, id = (this.id).split ('_');
		id = id[ 1 ];
		check = this.checked != true;
		jQuery ("#boxscoreselect_" + id).attr ("disabled", check);
		id = id * 1 + 1;
		jQuery ("[id^='checkbox-" + (id) + "-']").each (function () {
			var idCB = (this.id);
			jQuery ("#" + idCB).attr ("disabled", check);
		});
	});

	jQuery ("[id^='checkbox-']").on ("change", "", function () {
		var check, id = (this.id).split ('-');
		id = id[ 1 ];
		if (this.checked == true) {
			check = 'checked'
		} else {
			check = ''
		}
		if (check == 'checked') {
			jQuery ("#checkbox-1-" + id).prop ("checked", 'checked');
		} else {
			jQuery ("#checkbox-1-" + id).prop ("checked", false);
		}
		for (var i = 1; i < 5; i++) {
			var idCB = "checkbox-" + i + "-" + id;
			if ((jQuery ("#activarBS_" + i).is (':checked') == true)) {
				idCB = "checkbox-" + (i + 1) + "-" + id;
				if (check == 'checked') {
					jQuery ("#" + idCB).prop ("checked", 'checked');
				} else {
					jQuery ("#" + idCB).prop ("checked", false);
				}
			}
		}
	});
{/literal}
</script>
{/strip}