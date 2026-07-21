{strip}
<link rel="stylesheet" type="text/css" href="modules/PlatformPerformance/PlatformPerformance.css?v1.2" />
<div class="row">
	<div class="col-xs-12">
		<h1>Evolution</h1>
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
		<div class="col-xs-12 col-md-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>Total sesiones en instancias caducadas</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="expired-instances-total-sessions" class="chart simple">
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
					<h2>Total registros en instancias caducadas</h2>
				</header>
				<div class="main-box-body clearfix chart-container">
					<div id="expired-instances-total-records" class="chart simple">
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
{if (!empty ($EXPIRED_INSTANCES_TOTAL_SESSIONS))}
		PlatformPerformanceUtils.createBarChart ('expired-instances-total-sessions', {$EXPIRED_INSTANCES_TOTAL_SESSIONS|@json_encode nofilter});
{/if}
{if (!empty ($EXPIRED_INSTANCES_TOTAL_RECORDS))}
		PlatformPerformanceUtils.createBarChart ('expired-instances-total-records', {$EXPIRED_INSTANCES_TOTAL_RECORDS|@json_encode nofilter});
{/if}
	});
</script>
{/strip}