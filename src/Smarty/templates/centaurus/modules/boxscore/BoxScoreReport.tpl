{strip}
<style type="text/css">
{literal}
	th {
		text-align: center;
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
					</table>
				</div>
			</div>
			<div class="row">
{foreach $DATA as $key => $value}
				<div class="col-lg-12">
					<div class="main-box">
						<header class="main-box-header clearfix text-center">
							<h2>{$value.titulo}</h2>
						</header>
						<div class="main-box-body clearfix">
							<div id="graph-bar-{$value.id}"></div>
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
						<input type="hidden" name="registrarNuevoGraficoBSAvanzado2" id="registrarNuevoGraficoBSAvanzado2" value="1" />
						<input type="hidden" name="comparar" id="comparar" value="1" />
						<textarea style="display: none" name="sqlprimarioreporte" placeholder="">{$QUERY}</textarea>
						<textarea style="display: none" name="varreporte" placeholder="">{$VARIABLES|@json_encode}</textarea>
					</div>
					<input title="" accessKey="" class="btn btn-success btn-sm" onclick="" type="submit" name="button" value="Guardar Gráfico">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/jquery.knob.js"></script>
<script type="text/javascript" src="themes/centaurus/js/raphael-min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/morris.js"></script>
<script type="text/javascript">
	jQuery (function () {ldelim}
{foreach $DATA as $key => $value}
		Morris.Bar ({ldelim}
			element: 'graph-bar-{$key}',
			data: [
	{foreach $WEEKS as $week}
				{ldelim}
					x: '{$week|date_format: 'd-M'}',
					y: {if (isset ($value.BSid[$BOX_SCORE_VALUES[0]]['dataSemanal'][$week]))}{$value.BSid[$BOX_SCORE_VALUES[0]]['dataSemanal'][$week]}{else}0{/if},
					z: {if (isset ($value.BSid[$BOX_SCORE_VALUES[1]]['dataSemanal'][$week]))}{$value.BSid[$BOX_SCORE_VALUES[1]]['dataSemanal'][$week]}{else}0{/if},
					a: {if (isset ($value.BSid[$BOX_SCORE_VALUES[2]]['dataSemanal'][$week]))}{$value.BSid[$BOX_SCORE_VALUES[2]]['dataSemanal'][$week]}{else}0{/if},
		{if (isset ($value.BSid[$BOX_SCORE_VALUES[3]]['dataSemanal'][$week]))}
					b: {$value.BSid[$BOX_SCORE_VALUES[3]]['dataSemanal'][$week]},
		{/if}
		{if (isset ($value.BSid[$BOX_SCORE_VALUES[4]]['dataSemanal'][$week]))}
					c: {$value.BSid[$BOX_SCORE_VALUES[4]]['dataSemanal'][$week]},
		{/if}
		{if (isset ($value.BSid[$BOX_SCORE_VALUES[5]]['dataSemanal'][$week]))}
					d: {$value.BSid[$BOX_SCORE_VALUES[5]]['dataSemanal'][$week]},
		{/if}
		{if (isset ($value.BSid[$BOX_SCORE_VALUES[6]]['dataSemanal'][$week]))}
					e: {$value.BSid[$BOX_SCORE_VALUES[6]]['dataSemanal'][$week]},
		{/if}
		{if (isset ($value.BSid[$BOX_SCORE_VALUES[7]]['dataSemanal'][$week]))}
					f: {$value.BSid[$BOX_SCORE_VALUES[7]]['dataSemanal'][$week]},
		{/if}
				{rdelim}
	{/foreach}
			],
			barColors: [ '#339933', '#990000', '#006699', '#FFCC00', '#9b59b6', '#95a5a6' ],
			xkey: 'x',
			ykeys: [
				'y',
				'z',
				'a',
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[3]]['dataSemanal'][$week]))}'b',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[4]]['dataSemanal'][$week]))}'c',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[5]]['dataSemanal'][$week]))}'d',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[6]]['dataSemanal'][$week]))}'e',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[7]]['dataSemanal'][$week]))}'f',{/if}
			],
			labels: [
				'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[0]]}',
				'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[1]]}',
				'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[2]]}',
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[3]]['dataSemanal'][$week]))}'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[3]]}',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[4]]['dataSemanal'][$week]))}'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[4]]}',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[5]]['dataSemanal'][$week]))}'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[5]]}',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[6]]['dataSemanal'][$week]))}'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[6]]}',{/if}
				{if (isset ($value.BSid[$BOX_SCORE_VALUES[7]]['dataSemanal'][$week]))}'{$BOX_SCORE_TITLES[$BOX_SCORE_VALUES[7]]}',{/if}
			],
			resize: true
		{rdelim});
{/foreach}
	{rdelim});
</script>
{/strip}