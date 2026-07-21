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
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<div id="reportrange" class="filter-block pull-right">
					<a class="btn btn-info btn-sm" href="index.php?module={$CURRENT_MODULE}&action=listadoKPI">Volver a Buscar</a>
					<a class="btn btn-info btn-sm" href="index.php?module={$CURRENT_MODULE}&action=listadoBS&record={$RECORD}&fecha_desde={$FROM}&fecha_hasta={$TO}">Agregar boxscore</a>
				</div>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<tr>
							<th colspan="2">&nbsp;</th>
							<th>Semana</th>
{foreach $BOX_SCORE->dates as $date}
							<th>{$date.week}</th>
{/foreach}
						</tr>
						<tr>
							<th class="lft" style="width: 220px;">BOX SCORE</th>
							<th class="alert-grey ctr">Objetivo</th>
							<th>Cumplimiento</th>
{foreach $BOX_SCORE->dates as $date}
							<th>{$date.date|date_format: 'd-M'}</th>
{/foreach}
						</tr>
{foreach $BOX_SCORE->boxs as $boxScoreData}
	{if ($boxScoreData.tipo == 1)}
						<tr id="row-{$boxScoreData.box_score_dataid}">
							<td class="alert-warning show-tools">
								<span class="text">
		{if (preg_match ('/OEE/i', $boxScoreData.box_score))}
									<a href="index.php?module=boxscore&action=graph&record={$boxScoreData.box_score_dataid}&account_id={$boxScoreData.accountid}&fecha_desde={$FROM}&fecha_hasta={$TO}">{$boxScoreData.box_score}</a>
		{else}
									{$boxScoreData.box_score}
		{/if}
								</span>
							</td>
							<td class="alert-grey rgt">{$boxScoreData.objetivo}</td>
							<td class="ctr">
								<span class="label label-{if (preg_match ('/Cerca/i', $boxScoreData.cumplimiento))}warning{elseif (preg_match ('/Lejos/i', $boxScoreData.cumplimiento))}danger{else}success{/if}">{$boxScoreData.cumplimiento}</span>
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
							<td style="padding-right: 20px;{if ($date@index % 2 == 0)} background-color: #faebcc;{/if}" id="td-ed-{$boxScoreData.box_score_dataid}-{$date.week}" class="alert-warning rgt show-tools">
								<span id="bs-id-{$boxScoreData.box_score_dataid}-{$date.week}">{$value}</span>
							</td>
		{/foreach}
						</tr>
	{/if}
{/foreach}
					</table>
				</div>
			</div>
			<div class="row">
{if ($COMPARE)}
				<div class="col-lg-12">
					<div class="main-box">
						<header class="main-box-header clearfix text-center">
							<h2>Box Score</h2>
						</header>
						<div class="main-box-body clearfix">
							<div id="graph-bar"></div>
						</div>
					</div>
				</div>
{/if}
{foreach $BOX_SCORE->boxs as $boxScoreData}
				<div class="col-lg-6">
					<div class="main-box">
						<header class="main-box-header clearfix text-center">
							<h2>{$boxScoreData.box_score}</h2>
						</header>
						<div class="main-box-body clearfix">
							<div id="hero-bar-{$boxScoreData.box_score_dataid}"></div>
						</div>
					</div>
				</div>
{/foreach}
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2>¿Desea guardar este gráfico?</h2>
			</header>
			<div class="main-box-body clearfix">
				<form id="CustomView" name="CustomView" method="post" action="index.php?module=graficosgenerales&action=index&parenttab=Settings">
					<div class="row form-group">
						<label for="nombre">Nombre</label>
						<input type="text" name="nombre" id="nombre" value="" class="form-control" />
					</div>
					<div class="row form-group">
						<label for="roles">Seleccione los roles que podrán ver este reporte</label>
						<select id="roles" multiple="multiple" name="roles_grafico[]" class="form-control col-lg-6">
{foreach $ROLES as $key => $value}
							<option value="{$key}">{$value}</option>
{/foreach}
						</select>
						<input type="hidden" name="registrarNuevoGraficoBS" id="registrarNuevoGraficoBS" value="1" />
						<input type="hidden" name="comparar" id="comparar" value="{if ($COMPARE)}1{else}0{/if}" />
						<textarea style="display: none" name="sqlprimarioreporte" placeholder="">{$BOX_SCORE->sqlPrimarioReporte}</textarea>
						<textarea style="display: none" name="varreporte" placeholder="">{$BOX_SCORE->varreporte|@json_encode}</textarea>
					</div>
					<input class="btn btn-success btn-sm" onclick="" type="submit" name="button" value="Guardar Gráfico">
				</form>
			</div>
		</div>
	</div>
</div>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script src="themes/centaurus/js/moment.min.js"></script>
<script src="themes/centaurus/js/daterangepicker.js"></script>
<script src="themes/centaurus/js/jquery.knob.js"></script>
<script src="themes/centaurus/js/raphael-min.js"></script>
<script src="themes/centaurus/js/morris.js"></script>
<script type="text/javascript">
{literal}
	jQuery (function () {
{/literal}
{if ($COMPARE)}
	{literal}
		Morris.Bar ({
			element: 'graph-bar',
			data:    [
	{/literal}
	{foreach $WEEKS as $key => $value}
	{literal}
				{
					x: {/literal}'{$WEEKS[$key][0]['fecha']|date_format: 'd-M'}'{literal},
					y: {/literal}{$WEEKS[$key][0]['valor']}{literal},
					z: {/literal}{$WEEKS[$key][1]['valor']}{literal},
					a: {/literal}{if (isset ($WEEKS[$key][2]['valor']))}{$WEEKS[$key][2]['valor']}{else}0{/if}{literal}
				}
	{/literal}
	{/foreach}
	{literal}
			],
			barColors: [ '#2ecc71', '#f1c40f', '#e74c3c', '#3498db', '#9b59b6', '#95a5a6' ],
			xkey: 'x',
			ykeys: [ 'y', 'z', 'a' ],
			labels: [
				{/literal}'{$WEEKS[$keyW][0]['titulo']}'{literal},
				{/literal}'{$WEEKS[$keyW][1]['titulo']}'{literal},
				{/literal}'{$WEEKS[$keyW][2]['titulo']}'{literal}
			],
			resize: true
		});
	{/literal}
{/if}
{foreach $BOX_SCORE->boxs as $boxScoreData}
{literal}
		Morris.Bar ({
			element: {/literal}'hero-bar-{$boxScoreData.box_score_dataid}'{literal},
			data:    [
	{/literal}
	{foreach $BOX_SCORE->dates as $date}
	{literal}
				{
					device: {/literal}{$WEEKS[$key][0]['fecha']|date_format: 'd-M'}',{literal}
					geekbench: {/literal}{if ($boxScoreData['semanal'][$date.week]['valor'])}{$boxScoreData['semanal'][$date.week]['valor']}{else}0{/if},{literal}
				}
	{/literal}
	{/foreach}
	{literal}
			],
			barColors:   [ '#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad' ],
			xkey:        'device',
			ykeys:       [ 'geekbench' ],
			labels:      [ 'Valor' ],
			barRatio:    0.4,
			xLabelAngle: 35,
			hideHover:   'auto',
			resize:      true
		});
	{/literal}
{/foreach}
{literal}
	});
{/literal}
</script>
{/strip}