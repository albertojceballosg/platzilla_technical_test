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
						<input type="hidden" name="action" id="action" value="listadoKPI">
						<input type="hidden" name="record" id="record" value="{$RECORD}">
						<div class="form-group">
							<table>
								<tr>
									<td>
										<label for="boxscore">boxscore:</label>
										<div class="input-group">
											<select id="boxscore" name="boxscoreselect" class="form-control">
												<option value="">-</option>
{foreach $BOX_SCORES as $values}
												<option value="{$values.boxscoreid}"{if ($RECORD == $values.boxscoreid)} selected="selected"{/if}></option>
{/foreach}
											</select>
										</div>
									</td>
									<td style="padding-left: 15px;">
										<label for="fecha_desde">{$MOD.LBL_DATE_FROM}:</label>
										<div class="input-group" style="width: 135px;">
											<div class="input-group-addon" id="btn_fecha_desde">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right" id="fecha_desde" name="fecha_desde" value="{$FROM}" />
										</div>
									</td>
									<td style="padding-left: 15px;">
										<label for="fecha_hasta">{$MOD.LBL_DATE_TO}:</label>
										<div class="input-group" style="width: 135px;">
											<div class="input-group-addon" id="btn_fecha_hasta">
												<i class="fa fa-calendar"></i>
											</div>
											<input type="text" class="form-control pull-right" id="fecha_hasta" name="fecha_hasta" value="{$TO}" />
										</div>
									</td>
									<td style="padding-left: 15px;vertical-align: bottom;" align="center">
										<button class="btn btn-info" type="submit" name="submitbutton" value="search">{$MOD.LBL_SEARCH}</button>
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
							</tr>
	{/foreach}
						</table>
						<div class="checkbox-nice">
							<input id="checkbox-comparar" name="checkbox-comparar" type="checkbox">
							<label for="checkbox-comparar">Comparar Datos</label>
						</div>
						<div class="text-center">
							<button class="btn btn-info" type="button" onclick="jQuery('#action').val('reporte');jQuery('#EditView').submit();">Generar Gráficos</button>
						</div>
					</div>
				</div>
{else}
				<p class="text-center">Seleccione boxscore</p>
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
{/literal}
</script>
{/strip}