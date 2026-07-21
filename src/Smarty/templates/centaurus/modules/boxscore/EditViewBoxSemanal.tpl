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
<script src="{$TEMPLATE_PATH}/js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
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
<div id="dashboard_box_score">
	<section class="content">
		<div class="box box-primary">
			<div class="row">
				<div class="col-lg-12 col-xs-12">
					<div class="box-header">
						<h1 class="">{$APP.LBL_EDIT_BUTTON} Box Score</h1>
					</div>
					<div class="box-body">
						<p>
							<i class='fa fa-lock' style='color:#00a65a'></i> Estos valores se actualizan de forma automática
						</p>
					</div>
				</div>
			</div>
			<form name="EditViewBoxSemanal" id="EditViewBoxSemanal" method="post" action="index.php?module=boxscore&action=SaveBox">
				<input type="hidden" name="tipo" value="{$TYPE}">
				<input type="hidden" name="fecha_desde" value="{$FROM}">
				<input type="hidden" name="fecha_hasta" value="{$TO}">
				<input type="hidden" name="boxscoreid" value="{$BOX_SCORE_ID}">
				<input type="hidden" name="monthsearch" value="{$MONTH_SEARCH}">
				<div class="row">
					<div class="col-xs-12 connectedSortable">
						<table class="table">
							<tr>
								<th colspan="1">&nbsp;</th>
								<th>{$APP.boxscore_Semana}</th>
{foreach $BOX_SCORE->dates as $date}
								<th>{$date.week}</th>
{/foreach}
							</tr>
							<tr>
								<th class="lft" style="width: 220px;">BOX SCORE</th>
								<th class="alert-grey ctr">{$APP.boxscore_Objetivo}</th>
{foreach $BOX_SCORE->dates as $date}
								<th>{$date.date|date_format: 'M'}</th>
{/foreach}
							</tr>
{for $i=0; $i<count($BLOCKS); $i++}
	{foreach $BOX_SCORE->boxs as $boxScoreData}
							<tr id="row-{$boxScoreData.box_score_dataid}">
								<td class="show-tools" style="color: #A4A4A4; background-color: {$boxScoreData.colorbase};">
									<span class="text">
		{if ($boxScoreData.defaultplatzilla == 1)}
										<i class="fa fa-lock" style="color: #00a65a;"></i>
		{/if}
										{$boxScoreData.box_score}
									</span>
								</td>
								<td class="alert-grey rgt">
									{if ($boxScoreData.operator == 'menor-igual')}&lt;={elseif (!empty($boxScoreData.operator))}&gt;={/if} {$boxScoreData.objetivo|replace:'.':','}
								</td>
		{foreach $BOX_SCORE->dates as $date}
			{assign var='value' value=''}
			{if ($boxScoreData.escala == 'Week') && (isset ($boxScoreData.semanal[$date.week]['valor']))}
				{assign var='value' value=$boxScoreData.semanal[$date.week]['valor']}
			{else}
				{foreach $boxScoreData.semanal as $key => $data}
					{assign var='dummy' value=explode($boxScoreData.semanal.$key.fecha)}
					{if ($dummy[1] == $date.month)}
						{assign var='value' value=$boxScoreData.semanal.$key.valor}
						{break}
					{/if}
				{/foreach}
			{/if}
								<td style="background-color: {if ($date@index % 2 == 0)}{$boxScoreData.colordegrade}{else}{$boxScoreData.colorbase}{/if}" class="rgt show-tools">
			{if ($boxScoreData.defaultplatzilla == 1)}
									{$value}
			{else}
									<input name="date[{$boxScoreData.box_score_dataid}][{$date.week}]" type="hidden" value="{$date.date}">
									<input name="objetivo" type="hidden" value="{if (strpos ($boxScoreData.objetivo, '%') !== false)}%{/if}">
									<input name="semanalid[{$boxScoreData.box_score_dataid}][{$date.week}]" type="hidden" value="{$boxScoreData.semanal[$date.week]['semanalid']}">
									<input class="form-control input-sm" onkeyup="validateDecimal32General('value[{$boxScoreData.box_score_dataid}][{$date.week}]')" name="value[{$boxScoreData.box_score_dataid}][{$date.week}]" type="text" value="{$value}" objetivo="{if (strpos ($boxScoreData.objetivo, '%') !== false)}%{/if}" placeholder="">
			{/if}
								</td>
		{/foreach}
							</tr>
	{/foreach}
{/for}
							<tr>
								<td colspan="13" align="center">
									<input class="btn btn-success" id="submit-boxscore" name="submit-boxscore" type="submit" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
									&nbsp;
									<a class="btn btn-warning" href="index.php?module=boxscore&action=DetailView&record={$BOX_SCORE_ID}">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
		</div>
	</section>
</div>
<script type="text/javascript">
{literal}
	jQuery ("[fn='delete-row']").click (function (e) {
		e.preventDefault ();
		if (!confirm ("Esta seguro que desea eliminar el registro?")) {
			return false;
		}
		var rowid = this.id;
		jQuery ("#row-" + rowid).fadeOut (function () {
			jQuery ("#row-" + rowid).remove ();
		});
	});

	jQuery ("#EditViewBoxSemanal").submit (function (event) {
		jQuery (".input-sm").each (function (i) {
			if (jQuery (this).val () != '') {
				if (jQuery (this).attr ('objetivo') != '' && jQuery (this).attr ('objetivo') == '%') {
					if (jQuery (this).val ().indexOf ('%') == -1) {
						alert (alert_arr.FORMAT_PORCENTUAL_EDIT);
						jQuery (this).focus ();
						event.preventDefault ();
						return false;
					}
				}
			}
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
{/literal}
</script>
{/strip}