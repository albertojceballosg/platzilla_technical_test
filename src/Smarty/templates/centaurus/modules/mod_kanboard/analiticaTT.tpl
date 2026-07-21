<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/timeline.css">
<link rel="stylesheet" href="themes/centaurus/css/libs/morris.css" type="text/css" />

	<div class="row">
		<div class="col-lg-6">
			<h1>Kanban anal&iacute;tica</h1>
		</div>
		<div class="col-lg-6 filter-block pull-right">
			<a href="index.php?module=mod_kanboard&action=index" id="" class="btn btn-default pull-right">
				<i class="fa fa-reorder" title="Kanban"></i> Kanban</a>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left"></h2>

					<div class="filter-block">

						<form method="post" action="index.php?module=mod_kanboard&action=analitica">
							<div class="filter-block pull-right">
								<div class="form-group pull-left  col-xs-3" >
									{$ACCOUNTS}
								</div>
								<div class="form-group pull-left col-xs-3" >
									<select class="form-control" name="proyectosid">
									<option value="" selected>Seleccione proyecto</option>
									{$PROJECTS}
									</select>
								</div>
								{*
								<div class="form-group pull-left col-xs-3" >
									{$VENDORS}
								</div>
								*}
								<div class="form-group pull-left col-xs-3" >
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
										<input type="text" class="form-control" name="fecha_desde" id="fecha_desde" value="{$DATASELECCIONADA.fecha_desde}" placeholder="Desde">
									</div>
								</div>
								<div class="form-group pull-left col-xs-3" >
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
										<input type="text" class="form-control" name="fecha_hasta" id="fecha_hasta" value="{$DATASELECCIONADA.fecha_hasta}" placeholder="Hasta">
									</div>
								</div>

								<input type="submit" value="  Filtrar  " name="filtrar" class="btn btn-primary pull-left">

							</div>

						</form>



					</div>
				</header>

				<div class="main-box-body clearfix">
					<div id="graph-flot-donut" style="height: 400px; padding: 0px; position: relative;"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2>&nbsp;</h2>
				</header>

				<div class="main-box-body clearfix">
					<div id="hero-bar"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<h1>Changelog</h1>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">



			<section id="cd-timeline" class="cd-container">
				{foreach item=rg2 key=k from=$TODOS_REGISTROS_GENERICOS}
						<div class="cd-timeline-block">
							<div class="cd-timeline-img cd-picture">
								<i class="fa fa-calendar fa-2x"></i>
							</div>

							<div class="cd-timeline-content">
								<h2>[{$rg2.todotasksid}] {$rg2.title}</h2>
								<p>{$rg2.status_todotasks|@getTranslatedString:$MODULE}<br/>
								Asignado a: {$rg2.user_name}
								</p>
								<div class="clearfix">
									<a class="btn btn-primary pull-right" href="index.php?action=DetailView&module=todotasks&record={$rg2.todotasksid}">Ver</a>
								</div>
								<span class="cd-date">{$rg2.createdtime}</span>
							</div>
						</div>
				{/foreach}

			</section>



		</div>
	</div>

<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<!-- this page specific scripts -->
<script src="themes/centaurus/js/modernizr.js"></script>
<script src="themes/centaurus/js/timeline.js"></script>
<script src="themes/centaurus/js/jquery.knob.js"></script>
<script src="themes/centaurus/js/raphael-min.js"></script>
<script src="themes/centaurus/js/morris.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/flot/excanvas.min.js"></script><![endif]-->
<script src="themes/centaurus/js/flot/jquery.flot.js"></script>
<script src="themes/centaurus/js/flot/jquery.flot.min.js"></script>
<script src="themes/centaurus/js/flot/jquery.flot.pie.min.js"></script>
<script src="themes/centaurus/js/flot/jquery.flot.stack.min.js"></script>
<script src="themes/centaurus/js/flot/jquery.flot.resize.min.js"></script>
<script src="themes/centaurus/js/flot/jquery.flot.time.min.js"></script>
<script src="themes/centaurus/js/flot/jquery.flot.orderBars.js"></script>
<script>
jQuery('#fecha_desde').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
jQuery('#fecha_hasta').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
// donut chart
	if (jQuery('#graph-flot-donut').length) {ldelim}
		var dataDonut = [
			{foreach item=arrX key=key from=$TIPOS_GENERICOS}
				{ldelim}label:"{$arrX|@getTranslatedString:$MODULE}",data:{$REGISTROS_GENERICOS[$key]|@count}{rdelim},
			{/foreach}

		];

		jQuery.plot('#graph-flot-donut', dataDonut, {ldelim}
			series: {ldelim}
				pie: {ldelim}
					show: true,
					innerRadius: 0.5,
					label: {ldelim}
						show: true,
					{rdelim}
				{rdelim}
			{rdelim},
			colors: ['#e74c3c', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#95a5a6'],
			legend: {ldelim}
				show: false,
			{rdelim}
		{rdelim});
	{rdelim}
	if (jQuery('#graph-bar').length) {ldelim}
		var db1 = [];
		{foreach item=arrX key=key from=$TIPOS_GENERICOS}
			db1.push(['{$arrX|@getTranslatedString:$MODULE}', {$REGISTROS_GENERICOS[$key]|@count}]);
		{/foreach}
		for (var i = 0; i <= 10; i += 1) {ldelim}
			db1.push([i, parseInt(Math.random() * 30)]);
		{rdelim}

		var series = new Array();

		series.push({ldelim}
			data : db1,
			bars : {ldelim}
				show : true,
				barWidth : 0.8,
				order : 1,
				lineWidth: 1,
				fill: 1
			{rdelim}
		{rdelim});


		jQuery.plot("#graph-bar", series, {ldelim}
			colors: ['#e74c3c', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#95a5a6'],
			grid: {ldelim}
				tickColor: "#ddd",
				borderWidth: 0
			{rdelim},
			shadowSize: 0
		{rdelim});
	{rdelim}

	graphBar2 = Morris.Bar({ldelim}
		element: 'hero-bar',
		data: [
			{foreach item=rh key=key from=$REGISTROS_HORAS}
				{ldelim}tarea: '{$rh.title}', horas: parseInt(Math.random() * 30) {rdelim},
			{/foreach}

		],
		barColors: ['#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'],
		xkey: 'tarea',
		ykeys: ['horas'],
		labels: ['Horas'],
		barRatio: 0.4,
		xLabelAngle: 35,
		hideHover: 'auto',
		resize: true
	{rdelim});
</script>