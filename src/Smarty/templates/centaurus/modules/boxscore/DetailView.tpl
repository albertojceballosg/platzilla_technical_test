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
	.tools {
		display:  none;
		float:   right;
		color:    #f56954;
		position: absolute;
		left:     200px;
	}
	.show-tools:hover .tools {
		display: inline-block;
	}
{/literal}
</style>
<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
		var monthSearch = jQuery ('#monthsearch');
		if (monthSearch.val () == '') {
			var date = new Date ();
			var m;
			m = date.getMonth () + 1;
			if (m < 10) {
				monthSearch.val ('0' + m);
			} else {
				monthSearch.val (m);
			}
			jQuery ('#EditView').submit ();
		}
		jQuery ('.delete-row-cal').click (function () {
			var idop = jQuery (this).attr ('idop');
			new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=boxscore&action=boxscoreAjax&file=DeleteFieldboxscore&recordop=' + idop,
					onComplete: function (response) {
						console.log (response.responseText);
						if (response.responseText == 'delete_on') {
							alert (alert_arr.delete_on);
							location.reload ();
						} else {
							alert (response.responseText);
						}
					}
				}
			);
		});
		jQuery ('#saveBloque').click (function () {
			var colorbase = jQuery ('#colorbase').val ();
			var colordegrade = jQuery ('#colordegrade').val ();
			if (colorbase == '' || colordegrade == '') {
				alert (alert_arr.COLORBASE_NO_EMPTY);
			} else {

				new Ajax.Request (
					'index.php',
					{
						queue:      { position: 'end', scope: 'command' },
						method:     'post',
						postBody:   'module=boxscore&action=boxscoreAjax&file=SaveBlockboxscore&colorbase=' + colorbase + '&colordegrade=' + colordegrade,
						onComplete: function (response) {
							if (response.responseText == 'success') {
								alert (alert_arr.SAVE_BLOCK);
								jQuery ('#crearblock').removeClass ('in').hide ();
								location.reload ();
							} else {
								alert (alert_arr.ERROR);
							}
						}
					}
				);
			}
		});

		monthSearch.change (function () {
			var date = new Date ();
			var ultimoDia = '';
			new Date (date.getFullYear (), date.getMonth () + 1, 0);
			var fecha_desde = '';
			var fecha_hasta = '';
			var diaf = '';
			var month = [];
			month[ 0 ] = '01';
			month[ 1 ] = '02';
			month[ 2 ] = '03';
			month[ 3 ] = '04';
			month[ 4 ] = '05';
			month[ 5 ] = '06';
			month[ 6 ] = '07';
			month[ 7 ] = '08';
			month[ 8 ] = '09';
			month[ 9 ] = '10';
			month[ 10 ] = '11';
			month[ 11 ] = '12';

			if (monthSearch.val () == month[ date.getMonth () ] || monthSearch.val () == '') {
				ultimoDia = new Date (date.getFullYear (), date.getMonth () + 1, 0);
				if (ultimoDia.getDate () < 10) {
					diaf = '0' + ultimoDia.getDate ();
				} else {
					diaf = ultimoDia.getDate ();
				}
				fecha_desde = date.getFullYear () + '-' + month[ date.getMonth () ] + '-' + '01';
				fecha_hasta = date.getFullYear () + '-' + month[ date.getMonth () ] + '-' + diaf;
			} else {
				ultimoDia = new Date (date.getFullYear (), monthSearch.val () + 1, 0);
				if (ultimoDia.getDate () < 10) {
					diaf = '0' + ultimoDia.getDate ();
				} else {
					diaf = ultimoDia.getDate ();
				}
				fecha_desde = date.getFullYear () + '-' + monthSearch.val () + '-' + '01';
				fecha_hasta = date.getFullYear () + '-' + monthSearch.val () + '-' + diaf;
			}
			jQuery ('#fecha_desde').val (fecha_desde);
			jQuery ('#fecha_hasta').val (fecha_hasta);
			jQuery ('#EditView').submit ();
		});
	});
{/literal}
</script>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<div id="reportrange" class="filter-block pull-left">
					<form name="EditView" id="EditView" method="POST" action="index.php">
						<input type="hidden" name="module" id="module" value="boxscore">
						<input type="hidden" name="action" id="action" value="DetailView">
						<input type="hidden" name="record" id="record" value="{$RECORD}">
						<input type="hidden" name="fecha_desde" id="fecha_desde" value="">
						<input type="hidden" name="fecha_hasta" id="fecha_hasta" value="">
						<div class="form-group">
							<table>
								<tr>
									<td>
										<label>{$MOD.LBL_MONTH}</label>
										<div class="input-group" style="width: 300px;">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<select class="form-control" id="monthsearch" name="monthsearch" title="">
												<option value="">{$MOD.LBL_SELECTION_MONTH}</option>
												<option value="01"{if ($MONTH_SEARCH == '01')} selected="selected"{/if}>{$MOD.LBL_ENERO}</option>
												<option value="02"{if ($MONTH_SEARCH == '02')} selected="selected"{/if}>{$MOD.LBL_FEBRERO}</option>
												<option value="03"{if ($MONTH_SEARCH == '03')} selected="selected"{/if}>{$MOD.LBL_MARZO}</option>
												<option value="04"{if ($MONTH_SEARCH == '04')} selected="selected"{/if}>{$MOD.LBL_ABRIL}</option>
												<option value="05"{if ($MONTH_SEARCH == '05')} selected="selected"{/if}>{$MOD.LBL_MAYO}</option>
												<option value="06"{if ($MONTH_SEARCH == '06')} selected="selected"{/if}>{$MOD.LBL_JUNIO}</option>
												<option value="07"{if ($MONTH_SEARCH == '07')} selected="selected"{/if}>{$MOD.LBL_JULIO}</option>
												<option value="08"{if ($MONTH_SEARCH == '08')} selected="selected"{/if}>{$MOD.LBL_AGOSTO}</option>
												<option value="09"{if ($MONTH_SEARCH == '09')} selected="selected"{/if}>{$MOD.LBL_SEPTIEMBRE}</option>
												<option value="10"{if ($MONTH_SEARCH == '10')} selected="selected"{/if}>{$MOD.LBL_OCTUBRE}</option>
												<option value="11"{if ($MONTH_SEARCH == '11')} selected="selected"{/if}>{$MOD.LBL_NOVIEMBRE}</option>
												<option value="12"{if ($MONTH_SEARCH == '12')} selected="selected"{/if}>{$MOD.LBL_DICIEMBRE}</option>
											</select>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</form>
				</div>
				<div class="pull-right " style="padding-left: 15px;vertical-align: bottom; margin-top: 5%;" align="center">
					<a href="#crearblock" data-toggle="modal" class="btn btn-success btn-sm">{$MOD.LBL_CREATE_BLOCK}</a>
				</div>
			</header>
			<div id="crearblock" class="modal fade" aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" style="display: none;">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header" style="text-align:center">
							<button class="close" aria-hidden="true" data-dismiss="modal" type="button">×</button>
							<h4 class="modal-title">
								<span style="color: black;">{$MOD.LBL_CREATE_BLOCK}</span>
							</h4>
						</div>
						<div class="modal-body">
							<form role="form">
								<div class="form-group">
									<label for="colorbase">{$MOD.LBL_COLORBASE}</label>
									<input id="colorbase" class="form-control" type="color" value="#F7D358" title="{$MOD.LBL_HEXCOLOR}">
								</div>
								<div class="form-group">
									<label for="colordegrade">{$MOD.LBL_COLORDEGRADE}</label>
									<input id="colordegrade" class="form-control" type="color" value="#F3E2A9" title="{$MOD.LBL_HEXCOLOR}">
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button class="btn btn-default" data-dismiss="modal" type="button">{$MOD.LBL_CLOSE}</button>
							<button class="btn btn-primary" type="button" id="saveBloque">{$MOD.LBL_SAVE}</button>
						</div>
					</div>
				</div>
			</div>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<tr>
							<th colspan="2">&nbsp;</th>
							<th>{$MOD.LBL_WEEK}</th>
{foreach $BOX_SCORE->dates as $date}
							<th>{$date['week']}</th>
{/foreach}
						</tr>
						<tr>
							<th class="lft" style="width: 220px;">BOX SCORE</th>
							<th class="alert-grey ctr">{$MOD.LBL_OBJECT}</th>
							<th>{$MOD.LBL_CUMPL}</th>
{foreach $BOX_SCORE->dates as $date}
							<th>{$date.date|date_format: 'M'}</th>
{/foreach}
						</tr>
{for $i=0; $i<count($BLOCKS); $i++}
	{assign var='countbox' value=0}
	{foreach $BOX_SCORE->boxs as $boxScoreData}
		{if ($boxScoreData.tipo == $BLOCKS[$i]['tipo'])}
						<tr id="row-{$boxScoreData.box_score_dataid}">
							<td class="show-tools" style="color: #A4A4A4; background-color: {$boxScoreData.colorbase};">
								<span class="text">
			{if (preg_match ('/OEE/i', $boxScoreData.box_score))}
									<a href="index.php?module=boxscore&action=graph&record={$boxScoreData.box_score_dataid}&account_id={$boxScoreData.accountid}&fecha_desde={$FROM}&fecha_hasta={$TO}">{$boxScoreData.box_score}</a>
			{else}
									{$boxScoreData.box_score}
			{/if}
								</span>
								<div class="tools">
									<a href="index.php?module=boxscore&action=EditViewBox&record={$boxScoreData.box_score_dataid}&account_id={$boxScoreData.boxscoreid}&monthsearch={$MONTH_SEARCH}"><i title="Editar" class="fa fa-edit"></i></a>
									<a href="#" fn="delete-row" id="{$boxScoreData.box_score_dataid}" style="color:red;"><i title="Borrar" class="fa fa-trash-o"></i></a>
									<a href="#myModalInfo_{$boxScoreData.box_score_dataid}" data-toggle="modal"><i title="{$MOD.LBL_MOREINFO}" class="fa fa-info-circle"></i></a>
								</div>
								<div id="myModalInfo_{$boxScoreData.box_score_dataid}" class="modal fade" aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" style="display: none;">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header" style="text-align:center">
												<button class="close" aria-hidden="true" data-dismiss="modal" type="button">×</button>
												<h4 class="modal-title"><span style="color: black">{$MOD.LBL_MOREINFO}</span></h4>
											</div>
											<div class="modal-body">
												<span style="color: black">{$boxScoreData.description}</span>
											</div>
										</div>
									</div>
								</div>
								<br />
							</td>
							<td class="alert-grey rgt">
			{if ($boxScoreData.objetivo)}
								{if ($boxScoreData.operator == 'menor-igual')}&lt;={elseif (!empty($boxScoreData.operator))}&gt;={/if} {$boxScoreData.objetivo|replace:'.':','}
			{/if}
							</td>
							<td class="ctr">
								<span class="label label-{if (preg_match ('/Cerca/i', $boxScoreData.cumplimiento))}warning{elseif (preg_match ('/Lejos/i', $boxScoreData.cumplimiento))}danger{else}success{/if}">{$MOD[$boxScoreData.cumplimiento]}</span>
							</td>
			{foreach $BOX_SCORE->dates as $date}
				{assign var='value' value='&nbsp;'}
				{if ($boxScoreData.escala == 'Week')}
					{if (isset ($boxScoreData.semanal[$date.week]['valor']))}
						{assign var='value' value=$boxScoreData.semanal[$date.week]['valor']}
					{else}
						{assign var='value' value=0}
					{/if}
				{else}
					{foreach $boxScoreData.semanal as $key => $data}
						{assign var='dummy' value=explode($boxScoreData.semanal.$key.fecha)}
						{if ($dummy[1] == $date.month)}
							{assign var='value' value=$boxScoreData.semanal.$key.valor}
							{break}
						{/if}
					{/foreach}
				{/if}
							<td style="padding-right: 20px; background-color: {if ($date@index % 2 == 0)}{$boxScoreData.colordegrade}{else}{$boxScoreData.colorbase}{/if}" id="td-ed-{$boxScoreData.box_score_dataid}-{$date.week}" class="rgt show-tools show-tools-4q">
								<span id="bs-id-{$boxScoreData.box_score_dataid}-{$date.week}">{$value}</span>
								<div class="tool4q" style="display: none">
				{if ($CUATROQ[$boxScoreData.box_score_dataid][$date.date] == 'yes')}
									<a href="index.php?module=boxscore&action=graph&record={$boxScoreData.boxscoreid}&box_score_dataid={$boxScoreData.box_score_dataid}"><i class="fa fa-bar-chart-o"></i></a>
				{elseif ($CUATROQ[$boxScoreData.box_score_dataid][$date.date] == 'no')}
									<a style="color: red" href="index.php?module=boxscore&action=graph&record={$boxScoreData.boxscoreid}&box_score_dataid={$boxScoreData.box_score_dataid}"><i class="fa fa-bar-chart-o"></i></a>
				{else}
									<a style="float:left; font-size:12px" href="index.php?module=boxscore&action=DetailView&crear4Q=1&record={$boxScoreData.boxscoreid}&box_score_dataid={$boxScoreData.box_score_dataid}&fecha4q={$date.date}"><i class="fa fa-plus"></i></a>
				{/if}
								</div>
							</td>
			{/foreach}
						</tr>
			{assign var='countbox' value=$countbox + 1}
		{/if}
	{/foreach}
	{foreach $CALCULATIONS as $calculation}
		{if ($calculation.tipo == $block[$i]['tipo'])}
						<tr id="row-cal-{$calculation.operacion_id}">
							<td class="show-tools" style="background-color: {$calculation.colorbase}">
								<span class="text" title="{$calculation.calculo}">{$MOD.LBL_CALCULATE}</span>
			{if ($CURRENT_USER.id == $calculation.usuario)}
								<div class="tools">
									<a href="index.php?module=boxscore&action=EditViewBoxCalc&modeView=edit&record={$calculation.operacion_id}&account_id={$calculation.boxscoreid}&tipo={$calculation.tipo}&monthsearch={$MONTH_SEARCH}"><i title="Editar" class="fa fa-edit"></i></a>
									<a href="#" fn="delete-row-cal" class="delete-row-cal" idop="{$calculation.operacion_id}" id="idoperation{$calculation.operacion_id}" style="color: red;"><i title="Borrar" class="fa fa-trash-o"></i></a>
								</div>
			{/if}
							</td>
							<td class="alert-grey rgt">&nbsp;</td>
							<td class="ctr"><span class="label">&nbsp;</span></td>
			{foreach $BOX_SCORE->dates as $date}
				{assign var='value' value='&nbsp;'}
				{if ($calculation.totalsemanal[$date.week]['cal'])}
					{assign var='value' value=$calculation.totalsemanal[$date.week]['cal']}
				{/if}
							<td style="padding-right: 20px; background-color: {if ($date@index % 2 == 0)}{$calculation.colorbase}{else}{$calculation.colordegrade}{/if}" id="td-edcal-{$calculation.operacion_id}-{$date.week}-{$calculation.totalsemanal[$date.week]['cal']}" class="rgt show-tools">
								<span id="bs-idcal-{$calculation.operacion_id}-{$date.week}-{$calculation.totalsemanal[$date.week]['cal']}"><span style="color: black">{$value}</span></span>
							</td>
			{/foreach}
		{/if}
	{/foreach}
						<tr>
							<td align="center" style="background-color: {$BLOCKS[$i].colorbase};">
								<a href="index.php?module=boxscore&action=EditViewBox&tipo={$BLOCKS[$i].tipo}&account_id={$RECORD}&monthsearch={$MONTH_SEARCH}"><i class="fa fa-edit"></i> {$MOD.LBL_ADD_BS}</a>
							</td>
							<td colspan="2"></td>
							<td colspan="10" align="center" style="background-color: {$BLOCKS[$i].colorbase};">
	{if ($countbox > 0)}
								<a href="index.php?module=boxscore&action=EditViewBoxSemanal&tipo={$BLOCKS[$i].tipo}&record={$RECORD}&monthsearch={$MONTH_SEARCH}"><i class="fa fa-edit"></i> {$MOD.LBL_EDIT_VALUE}</a>
	{else}
								&nbsp;
	{/if}
							</td>
						</tr>
						<tr>
							<td colspan="13" align="center" style="background-color: {$BLOCKS[$i].colorbase};">
	{if ($countbox > 0)}
								<a href="index.php?module=boxscore&action=EditViewBoxCalc&mode_=create&tipo={$BLOCKS[$i].tipo}&account_id={$RECORD}&monthsearch={$MONTH_SEARCH}"><i class="fa fa-edit"></i> {$MOD.LBL_ADD_BS_CALC}</a>
	{else}
								&nbsp;
	{/if}
							</td>
						</tr>
						<tr>
							<td colspan="13"></td>
						</tr>
{/for}
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script src="themes/centaurus/js/moment.min.js"></script>
<script src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript">
{literal}
	jQuery ('[fn="delete-row"]').click (function (e) {
		e.preventDefault ();
		if (!confirm ('Esta seguro que desea eliminar el registro?')) {
			return false;
		}
		var rowid = this.id;
		jQuery.ajax ({
			type: 'POST',
			url:  'index.php',
			data: { module: 'boxscore', action: 'boxscoreAjax', file: 'DeleteBox', record: rowid, 'delete': 'true' }
		}).done (function (response) {
			console.log (response);
			jQuery ('#row-' + rowid).fadeOut (function () {
				jQuery ('#row-' + rowid).remove ();
			});
		});

	});

	var showTools4q = jQuery ('.show-tools-4q');
	showTools4q.mouseover (function (e) {
		e.preventDefault ();
		jQuery (this).find ('.tool4q').css ('display', 'block');
	});

	showTools4q.mouseout (function (e) {
		e.preventDefault ();
		jQuery (this).find ('.tool4q').css ('display', 'none');
	});

	function editvalue (id) {
		jQuery ('#bs-id-' + id).hide ();
		jQuery ('#bs-ed-id-' + id).show ();
		jQuery ('#td-ed-' + id).removeClass ('show-tools');
	}

	function canceledit (id) {
		jQuery ('#bs-ed-id-' + id).hide ();
		jQuery ('#td-ed-' + id).addClass ('show-tools');
		jQuery ('#bs-id-' + id).show ();
	}

	function guardarValor (id) {
		jQuery ('#bs-ed-id-' + id).hide ();
		jQuery ('#td-ed-' + id).addClass ('show-tools');
		jQuery ('#bs-id-' + id).show ();
	}

	//datepicker
	jQuery ('#fecha_desde').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	jQuery ('#fecha_hasta').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });

	function openPopup (recordid) {
		window.open ('index.php?module=Accounts&action=Popup&popuptype=specific_contact_account_address&form=TasksEditView&form_submit=false&fromlink=&recordid=' + recordid, 'test', 'width=640,height=602,resizable=0,scrollbars=0');
		return false;
	}
	var account_id = {/literal}'{$RECORD}'{literal};
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