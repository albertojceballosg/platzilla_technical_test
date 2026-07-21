{foreach key=keyG item=grafico from=$GRAPHS.boxscoreadvanced}
<div class="row">
	<div id="wtwid" class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix text-center">
				<h2 class="pull-left">{$grafico.title}</h2>
				<div class="pull-right" id="{$grafico.graficoid}">
					<a title="Borrar" id="deleteButton" href="javascript: void(0)" onclick="window.GraphUtils.deleteGraph ({$grafico.graficoid})" class="btn btn-danger pull-right" style="margin-right: 5px;">
						<span class="fa fa-trash-o"></span>
					</a>
				</div>
			</header>
			<div class="main-box-body no-header clearfix">
				<div class="row">
	{foreach key=keyGInd item=graficoInd from=$grafico.dataGrafico}
					<div class="col-lg-12">
						<div class="main-box">
							<header class="main-box-header clearfix text-center">
								<h2>{$graficoInd.titulo}</h2>
							</header>
							<div class="main-box-body clearfix text-center">
								<div id="graph-bar-{$grafico.graficoid}-{$keyGInd}"></div>
							</div>
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									{$graficoInd.tablaHTML}
								</table>
							</div>
						</div>
					</div>
	{/foreach}
				</div>
			</div>
		</div>
	</div>
</div>
{/foreach}
<script type="text/javascript">
	jQuery (function () {ldelim}
{foreach key=keyG item=grafico from=$GRAPHS.boxscoreadvanced}
		{$grafico.jsGraficoInd}
{/foreach}
	{rdelim});
</script>