{strip}
<link rel="stylesheet" type="text/css" href="modules/PlatformPerformance/PlatformPerformance.css?v1.1" />
<div class="row">
	<div class="col-xs-12">
		<h1>Onboarding analysis</h1>
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
			<input type="hidden" name="action" value="OnboardingAnalysis" />
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
				<label for="minimum-sessions">Sesiones</label>
				<div class="input-group">
					<input type="number" id="minimum-sessions" name="sessions" value="{$MINIMUM_SESSIONS}" class="form-control number" min="0" step="1" />
				</div>
			</div>
			<div class="form-group">
				<label for="minimum-records">Registros</label>
				<div class="input-group">
					<input type="number" id="minimum-records" name="records" value="{$MINIMUM_RECORDS}" class="form-control number" min="0" step="1" />
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
					<h2>Registros diarios</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="registrations-per-day" class="chart simple">
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
					<h2>Subscripciones diarias</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="subscriptions-per-day" class="chart simple">
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
					<h2>Registros vs subscripciones</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="registrations-vs-subscriptions" class="chart simple">
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
					<h2>Registros vs Subscripciones diarias</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="registrations-vs-subscriptions-per-day" class="chart simple">
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
					<h2>Evolución</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="evolution" class="chart funnel">
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
					<h2>Atracción a la oferta</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="offer" class="chart simple">
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
<script type="text/javascript" src="modules/PlatformPerformance/PlatformPerformance.js?v=1.1"></script>
<script type="text/javascript">
	jQuery (document).ready (function () {
{if (!empty ($DAILY_REGISTRATIONS))}
		PlatformPerformanceUtils.createBarChart ('registrations-per-day', {$DAILY_REGISTRATIONS|@json_encode nofilter});
		PlatformPerformanceUtils.createBarChart ('subscriptions-per-day', {$DAILY_SUBSCRIPTIONS|@json_encode nofilter});
{/if}
{if (!empty ($REGISTRATIONS_VS_SUBSCRIPTIONS))}
		PlatformPerformanceUtils.createPieChart ('registrations-vs-subscriptions', {$REGISTRATIONS_VS_SUBSCRIPTIONS|@json_encode nofilter});
{/if}
{if (!empty ($DAILY_REGISTRATIONS_VS_SUBSCRIPTIONS))}
		PlatformPerformanceUtils.createBarChart ('registrations-vs-subscriptions-per-day', {$DAILY_REGISTRATIONS_VS_SUBSCRIPTIONS|@json_encode nofilter});
{/if}
{if (!empty ($EVOLUTION))}
		PlatformPerformanceUtils.createFunnelChart ('evolution', {$EVOLUTION|@json_encode nofilter});
{/if}
{if (!empty ($OFFER_DATA))}
		PlatformPerformanceUtils.createPointsChart ('offer', {$OFFER_DATA|@json_encode nofilter});
{/if}
	});
</script>
{/strip}