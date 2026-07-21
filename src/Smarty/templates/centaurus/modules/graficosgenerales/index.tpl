{strip}
<link type="text/css" rel="stylesheet" href="modules/graficosgenerales/graficosgenerales.css"/>
	<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
	<style type="text/css">
		.main-box {
			box-shadow:    0px 0px 0px 0 #FFFFFF !important;
			border-radius: 0px !important;
		}
		.base-list-container {
			background-color: #ffffff;
			margin:           0px -13px!important;
			border-top:       1px solid #D8D8D8 !important;
			height:           auto;
			min-height:       1150px !important;
		}

		.nav-platzilla > li > a {
			font-weight: bold !important;
		}

		.nav-platzilla > li.active {
			background-color: #FFFFFF;
			margin-bottom:    -3px !important;
			height:           46px;
		}
	</style>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/morris.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.min.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.pie.min.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.stack.min.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.resize.min.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.time.min.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.orderBars.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/flot/jquery.flot.funnel.js"></script>
	<script type="text/javascript" src="modules/graficosgenerales/graficosgenerales.js"></script>
<div class="container-fluid base-list-container">
	<div class="col-lg-12" style="margin-bottom: 6px">
						<div id="graphicsSearch" class="row-graphic justify-content-center" style="background-color: white; padding: 14px 0px 2px 0px; margin: 4px 0px">
							<div class="col-md-2" style="padding-top: 2px">
								<h1 class="pull-left" >Gráficos</h1>
							</div>

					<div class="col-md-8"  style="padding-top: 2px">
						<form class="form-inline row-graphic justify-content-center" role="form" action="index.php?module=graficosgenerales&action=index" method="post">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">
										<i class="fa  fa-clock-o"></i>
									</div>
									<select class="form-control col-md-4" id="graphicsPeriod" name="graphicsPeriod" title="Buscar por tiempo" onchange="GraphUtils.searchGraphicsByTime(this)">
										<option value="CUSTOM_DATE" {if $searchFrom eq 'CUSTOM_DATE'} selected{/if}>{$MOD.OPT_CUSTOM_DATE}</option>
										<option value="{$graphicToday}" {if $searchFrom eq $graphicToday} selected{/if}>{$MOD.OPT_TODAY}</option>
										<option value="{$graphicWeek}" {if $searchFrom eq $graphicWeek} selected{/if}>{$MOD.OPT_LAST_WEEK}</option>
										<option value="{$graphicMonth}" {if $searchFrom eq $graphicMonth} selected{/if}>{$MOD.OPT_LAST_MONTH}</option>
										<option value="{$graphicThMonth}" {if $searchFrom eq $graphicThMonth} selected{/if}>{$MOD.OPT_LAST_THREE_MONTH}</option>
										<option value="{$graphicSixMonth}" {if $searchFrom eq $graphicSixMonth} selected{/if}>{$MOD.OPT_LAST_SIX_MONTH}</option>
										<option value="{$graphicYear}" {if $searchFrom eq $graphicYear} selected{/if}>{$MOD.OPT_LAST_YEAR}</option>
									</select>
								</div>
							</div>
							<div class="form-group col-md-3" style="margin-left: 4px">
								<div class="input-group">
									<div class="input-group-addon" style="border: 1px solid #ddd !important">
										<i class="fa fa-calendar"></i>
									</div>
									<input type="text" class="form-control pull-right input-readonly b-left col-md-12" id="graphicsDatefrom" name="graphicsDateFrom" readonly="readonly" value="{$graphicDateFrom}">
								</div>
							</div>
							<div class="form-group col-md-3"  style="margin-left: 4px">
								<div class="input-group">
									<div class="input-group-addon" style="border: 1px solid #ddd !important">
										<i class="fa fa-calendar"></i>
									</div>
									<input type="text" class="form-control pull-right input-readonly b-left col-md-12" id="graphicsDateTo" name="graphicsDateTo" readonly="readonly" value="{$graphicDateTo}">
								</div>
							</div>
							<button name="submitSearch" id="graphicsSubmitSearch" class="btn btn-primary btn-sm"  style="margin-left: 4px"  type="submit"><i class="fa fa-search" aria-hidden="true"></i>
							</button>
							<input type="hidden" name="activeTab" id="activeTab" value="{$activeTab}" >
						</form>

					</div>
							<div class="col-md-2"  style="padding-top: 2px">
                                {if ($IS_ADMIN)}
									<a href="index.php?module={$MODULE}&action=CreateGraph&parenttab=Settings" class="btn btn-primary pull-right" style="margin-right: 24px">
										<i class="fa fa-plus-circle fa-lg"></i> Gráfico</a>
                                {/if}
							</div>
						</div>
	</div>
<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-12">

			</div>
		</div>
	</div>
	<div class="main-box-body clearfix" style="width:100%;background-color:#FFFFFF;">
		<div class="tabs-wrapper">
			<ul class="nav nav-tabs nav-platzilla">
{assign var='hasActiveTab' value=false}
{if (!empty ($APPLICATIONS))}
	{assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
	{foreach $applicationCodes as $applicationCode}
		{if (empty ($GRAPHS.applications[$applicationCode]))}
			{continue}
		{/if}
		{if (empty ($activeTab)) }
			{$activeTab = $applicationCode}
		{/if}
				<li{if $activeTab eq $applicationCode} class="active"{/if}>
					<a href="#tab-{$applicationCode}" data-toggle="tab" onclick="GraphUtils.setTab('{$applicationCode}')">{$APPLICATIONS[$applicationCode].app_name}</a>
				</li>

		{assign var='hasActiveTab' value=true}
	{/foreach}
{/if}
{if (!empty ($GRAPHS.boxscoresimple)) || (!empty ($GRAPHS.boxscoreadvanced))}
				<li{if (!$hasActiveTab)} class="active"{/if}>
					<a href="#tab-boxscore" data-toggle="tab">BoxScore</a>
				</li>
	{assign var='hasActiveTab' value=true}
{/if}
{if (!empty ($GRAPHS.others))}
				<li{if $activeTab eq 'otros'} class="active"{/if}>
					<a href="#tab-otros" data-toggle="tab" onclick="GraphUtils.setTab('otros')">Otros</a>
				</li>
	{assign var='hasActiveTab' value=true}
{/if}
			</ul>
			<div class="tab-content">
{assign var='hasActiveTab' value=false}
{if (!empty ($APPLICATIONS))}
	{assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
	{foreach $applicationCodes as $applicationCode}
		{if (empty ($GRAPHS.applications[$applicationCode]))}
			{continue}
		{/if}
				<div id="tab-{$applicationCode}" class="tab-pane fade in{if $activeTab eq $applicationCode} active{/if}">
			<div class="row-graphic">
	{foreach $GRAPHS.applications[$applicationCode] as $graph}
        {assign var='myTab' value=$applicationCode}
		{include file='modules/graficosgenerales/BasicModuleGraph.tpl'}
	{/foreach}
			</div>
		</div>
		{assign var='hasActiveTab' value=true}
	{/foreach}
{/if}
{if (!empty ($GRAPHS.boxscoresimple)) || (!empty ($GRAPHS.boxscoreadvanced))}
				<div id="tab-boxscore" class="tab-pane fade in{if (!$hasActiveTab)} active{/if}">
	{if (!empty ($GRAPHS.boxscoresimple))}
		{foreach $GRAPHS.boxscoresimple as $graph}
			{include file='modules/graficosgenerales/BoxScoreSimpleGraph.tpl'}
			{assign var='hasActiveTab' value=true}
		{/foreach}
	{/if}
	{if (!empty ($GRAPHS.boxscoreadvanced))}
		{include file='modules/graficosgenerales/BoxScoreAdvancedGraph.tpl'}
		{assign var='hasActiveTab' value=true}
	{/if}
				</div>
{/if}
{if (!empty ($GRAPHS.others))}
				<div id="tab-otros" class="tab-pane fade in{if $activeTab eq 'otros'} active{/if}">
					<div class="row">
	{foreach $GRAPHS.others as $graph}
        {assign var='myTab' value='otros'}
		{include file='modules/graficosgenerales/BasicGraph.tpl'}
		{assign var='hasActiveTab' value=true}
	{/foreach}
					</div>
				</div>
{/if}
			</div>
		</div>
	</div>
</div>
</div>

{include file='modules/graficosgenerales/GraphPreviewModal.tpl'}

{/strip}