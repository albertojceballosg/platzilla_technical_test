{strip}
<link rel="stylesheet" type="text/css" href="modules/PlatformPerformance/PlatformPerformance.css?v1.2" />
<div class="row">
	<div class="col-xs-12">
		<h1>Application usage</h1>
	</div>
</div>
{if (!empty ($MESSAGE))}
<div class="row">
	<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
<div class="main-box no-header clearfix">
	<div class="main-box-body clearfix">
		<form action="index.php" method="get" class="form-inline text-center" role="form">
			<input type="hidden" name="module" value="PlatformPerformance" />
			<input type="hidden" name="action" value="ApplicationUsage" />
			<div class="form-group">
				<label for="from">Desde</label>
				<div class="input-group">
					<input type="text" id="from" name="from" value="{$FROM}" class="form-control date" readonly="readonly" />
					<div class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="to">Hasta</label>
				<div class="input-group">
					<input type="text" id="to" name="to" value="{$TO}" class="form-control date" readonly="readonly" />
					<div class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-default">Filtrar</button>
			</div>
		</form>
	</div>
</div>
<div class="main-box no-header clearfix">
	<div class="main-box-body clearfix">
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Registros por aplicación</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="records-per-application" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Registros por aplicación</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="total-records-per-application" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Registros por módulo</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="records-per-module" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Registros por módulo</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="total-records-per-module" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Instancias con más registros</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="records-per-customer" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Instancias con más tiempo</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="time-per-customer" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Visitas por aplicación</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="total-visits-per-application" class="chart simple">
						<div class="alert alert-info text-center">
							<div class="message">No hay data para graficar</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.pie.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.stack.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.time.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.orderBars.js"></script>
<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.funnel.js"></script>
<script type="text/javascript" src="include/js/highcharts/js/highcharts.js"></script>
<script type="text/javascript" src="include/js/highcharts/js/modules/funnel.js"></script>
<script type="text/javascript" src="include/js/highcharts/js/modules/exporting.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/raphael-min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/morris.js"></script>
<script type="text/javascript" src="modules/PlatformPerformance/PlatformPerformance.js?v=1.2"></script>
<script type="text/javascript">
	jQuery (document).ready (function () {
{if (!empty ($RECORDS_PER_MODULE))}
		PlatformPerformanceUtils.createBarChart ('records-per-module', {$RECORDS_PER_MODULE|@json_encode nofilter});
		PlatformPerformanceUtils.createPieChart ('total-records-per-module', {$RECORDS_PER_MODULE|@json_encode nofilter});
{/if}
{if (!empty ($RECORDS_PER_CUSTOMER))}
		PlatformPerformanceUtils.createBarChart ('records-per-customer', {$RECORDS_PER_CUSTOMER|@json_encode nofilter});
{/if}
{if (!empty ($RECORDS_PER_APPLICATION))}
		PlatformPerformanceUtils.createBarChart ('records-per-application', {$RECORDS_PER_APPLICATION|@json_encode nofilter});
		PlatformPerformanceUtils.createPieChart ('total-records-per-application', {$RECORDS_PER_APPLICATION|@json_encode nofilter});
{/if}
{if (!empty ($TIME_PER_CUSTOMER))}
		PlatformPerformanceUtils.createBarChart ('time-per-customer', {$TIME_PER_CUSTOMER|@json_encode nofilter});
{/if}
{if (!empty ($VISITS_PER_APPLICATION))}
		PlatformPerformanceUtils.createPieChart ('total-visits-per-application', {$VISITS_PER_APPLICATION|@json_encode nofilter});
{/if}
	});
</script>
{/strip}