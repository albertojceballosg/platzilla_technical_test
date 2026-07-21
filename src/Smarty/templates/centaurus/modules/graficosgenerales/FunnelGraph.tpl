<div class="col-lg-6">
	<div class="main-box">
		<header class="main-box-header clearfix text-center">
			<h1>{$graph.title}</h1>
		</header>
		<div class="main-box-body clearfix">
			<div id="funnel-{$graph.applicationcode}-{$graph.graficoid}" style="height: 347px; padding: 0; position: relative; width: 100%;"></div>
		</div>
	</div>
</div>
<script type="text/javascript">window.GraphUtils.loadFunnelGraph ({$graph|@json_encode nofilter});</script>