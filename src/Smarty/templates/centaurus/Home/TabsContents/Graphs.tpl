{strip}
{assign var='today' value=date('Y-m-d')}
{assign var='lastWeek' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('7 days')), 'Y-m-d')}
{assign var='lastMonth' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('1 month')), 'Y-m-d')}
{assign var='lastQuarter' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('3 months')), 'Y-m-d')}
{assign var='lastMidyear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('6 months')), 'Y-m-d')}
{assign var='lastYear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('12 months')), 'Y-m-d')}
{assign var='MODULE' value="graficosgenerales"}
	<style type="text/css">
		svg {
			overflow: visible;
			width:    100% !important;
			height:   600px !important;
		}
		.tabs-wrapper > .tab-content {
			margin-bottom: 0;
		}

		.graph.simple {
			height:   380px;
			padding:  0;
			position: relative;
			width:    100%;
		}
		.graph.simple iframe {
			margin-top: -2px!important;
			/*height: 525px!important;*/
		}
		.rounded {
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
		.row-graphic {
			display:-webkit-box;
			display:-ms-flexbox;
			display:flex;-ms-flex-wrap:wrap;
			flex-wrap:wrap;
			margin-right:-15px;
			margin-left:-15px
		}
		.justify-content-center {
			-webkit-box-pack:center!important;
			-ms-flex-pack:center!important;
			justify-content:center!important
		}
		.justify-content-between {
			-webkit-box-pack: justify !important;
			-webkit-justify-content: space-between !important;
			-ms-flex-pack: justify !important;
			justify-content: space-between !important;
		}
		.no-gutters>.col,
		.no-gutters>[class*=col-] {
			padding-right: 1px;
			padding-left: 1px;
		}
		.box_shadow {
			-webkit-box-shadow: 1px 1px 3px #ccc;
			-moz-box-shadow: 1px 1px 3px #ccc;
			box-shadow: 1px 1px 3px #ccc;
		}
		.isDisabled {
			cursor: not-allowed;
			opacity: 0.5;
		}
		.isDisabled > a {
			color: currentColor;
			display: inline-block;  /* For IE11/ MS Edge bug */
			pointer-events: none;
			text-decoration: none;
		}
		.google-visualization-table-table {
			margin-left: auto;
			margin-right: auto;
			max-height: 340px!important;
			min-width: 320px!important;
		}
		.google-visualization-table {
			padding-top: 15px;
			padding-left: 4%!important;
			padding-right: 4%!important;
			max-height: 350px!important;
			min-width: 320px!important;
		}
	</style>
	<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
{if (empty ($GRAPHS))}
		<div class="alert alert-warning text-center">
			No tiene gráficos favoritos
		</div>
{else}
	<div class="row">
		<div class="col-md-6" style="margin-top: 2px!important;">
			<div class="btn-group">
                {assign var='hasActiveTab' value=false}
                {if (!empty ($APPLICATIONS))}
                    {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
                    {foreach $applicationCodes as $applicationCode}
                        {if (empty ($GRAPHS.applications[$applicationCode]))}
                            {continue}
                        {/if}
                        {if (empty ($activeTab) && !$hasActiveTab) }
                            {$activeTab = $applicationCode}
                        {/if}
						<a href="#tab-{$applicationCode}"
						   class="btn  {if ($activeTab eq $applicationCode)}btn-primary{else}btn-default{/if}" data-toggle="tab"
						   onclick="GraphUtils.setTab(this, event, '{$applicationCode}')">{$APPLICATIONS[$applicationCode].app_name}</a>
                        {assign var='hasActiveTab' value=true}
                    {/foreach}
                {/if}
                {if (!empty ($GRAPHS.boxscoresimple)) || (!empty ($GRAPHS.boxscoreadvanced))}
					<a href="#tab-boxscore" class="btn btn-default"
					   data-toggle="tab">BoxScore</a>
                    {assign var='hasActiveTab' value=true}
                {/if}
                {if (!empty ($GRAPHS.others))}
					<a href="#tab-otros" data-toggle="tab"
					   class="btn {if ($activeTab eq $applicationCode)}btn-primary{else}btn-default{/if}"
					   onclick="GraphUtils.setTab('otros')">Otros</a>
                    {assign var='hasActiveTab' value=true}
                {/if}
                {if (!empty ($FAVORITES))}
					<a href="#tab-FAVORITES" data-toggle="tab"
					   class="btn {if ($activeTab eq 'FAVORITES')}btn-primary{else}btn-default{/if}"
					   onclick="GraphUtils.setTab(this, event, 'FAVORITES')">Favoritos</a>
                    {assign var='hasActiveTab' value=true}
                {/if}
			</div>
		</div>
		<div class="col-md-6"  style="margin-top: 2px!important;">
		<form  id="graphic-filters" class="row graphic-filters-form"">
			<input type="hidden" name="Ajax" value="true" />
			<input type="hidden" name="activeTab" id="activeTab" value="{$activeTab}" >
			<input type="hidden" name="fl_module" id="fl_module" value="{$FLMODULE}" >
			<input type="hidden" name="Favorites" id="Favorites" value="{*$IS_FAVORITES*}" >
			<input type="hidden" name="is_home" id="is_home" value="true" >
            {if isset($IS_MODAL)}
			<input type="hidden" name="is_modal" id="is_modal" value="{$IS_MODAL}" >
			{/if}
			<div class="col-xs-12 col-md-4 col-lg-6">
				<div class="form-group">
					<label class="hide"  for="graphic-tab-period">Período:</label>
					<div class="input-group">
						<span class="input-group-addon hidden-md"><i class="fa fa-clock-o"></i></span>
						<select id="graphic-tab-period" class="form-control" title="Buscar por tiempo"  data-last-time="{$lastYear}" data-today="{$today}" onchange="GraphUtils.searchGraphicsHome(this)">
							<option value="{$today}">Hoy</option>
							<option value="{$lastWeek}">Última semana</option>
							<option value="{$lastMonth}">Último mes</option>
							<option value="{$lastQuarter}" selected="selected">Último trimestre</option>
							<option value="{$lastMidyear}">Último semestre</option>
							<option value="{$lastYear}">Último año</option>
						</select>
					</div>
					<span id="graphic-tab-help"  class="help-block"></span>
				</div>
			</div>
			<div class="col-xs-12 col-md-3">
				<div class="form-group">
					<label class="hide"  for="graphic-tab-from">Desde:</label>
					<div class="input-group">
						<span class="input-group-addon hidden-md"><i class="fa fa-calendar"></i></span>
						<input type="text" id="graphic-tab-from" name="graphicsDateFrom"  value="{$lastWeek}" class="form-control from-field" readonly="readonly" />
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-3">
				<div class="form-group">
					<label class="hide"  for="graphic-tab-to">Hasta:</label>
					<div class="input-group">
						<span class="input-group-addon hidden-md"><i class="fa fa-calendar"></i></span>
						<input type="text" id="graphic-tab-to" name="graphicsDateTo" value="{$today}" class="form-control to-field" readonly="readonly" />
					</div>
				</div>
			</div>
		</form>
		</div>
		{* Tabs graphics *}
	</div>
    {* Inicio de gráficos*}
	<div id="graphic-listview" class="col-md-12">
        {include file='Home/TabsContents/GraphicListView.tpl'}
	</div>
	{*fin de gráficos*}
{/if}
{/strip}