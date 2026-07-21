<div class="col-lg-12">
	<div class="main-box">
		<header class="main-box-header clearfix text-center">
			<h2 class="pull-left">{$graph.title}</h2>
{if ($IS_ADMIN)}
			<div class="pull-right">
				<a title="Borrar" id="deleteButton" href="javascript: void(0)" onclick="window.GraphUtils.deleteGraph ({$graph.graficoid})" class="btn btn-danger pull-right" style="margin-right: 5px;">
					<span class="fa fa-trash-o"></span>
				</a>
			</div>
{/if}
		</header>
{if $graph.comparar == 1}
		<div class="col-xs-12">
			<div class="main-box-body clearfix">
				<div id="graph-bar-{$graph.graficoid}" class="graph"></div>
			</div>
		</div>
{/if}
{foreach $graph.dataGrafico as $detail}
		<div class="col-lg-6">
			<div class="main-box">
				<header class="main-box-header clearfix text-center">
					<h2>{$detail.box_score} </h2>
				</header>
				<div class="main-box-body clearfix">
					<div id="hero-bar-{$graph.graficoid}-{$detail.boxscoreid}-{$detail.box_score_dataid}"></div>
				</div>
			</div>
		</div>
{/foreach}
	</div>
</div>
<script type="text/javascript">window.GraphUtils.loadBoxScoreSimpleGraph ({$graph|@json_encode nofilter}, {$COLORS|@json_encode nofilter});</script>