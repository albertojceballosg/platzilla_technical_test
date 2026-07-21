{assign var='selectedModule' value=','|explode:"calculated_fields,Home,Pricebooks,Settings,panelusuarios,instances,Pricebooks,almacenes,todotasks,operating_modes,indicatorspanel,grid_view,impuestos,materials,PlatformPerformance,Reports,instancesdatasharing,webmail,graficosgenerales,News,emailmanager,backgroundtasks,notifications,operating_modes,reportmanager,admin_widgets,grid_view,etapas_proyecto"}
{strip}
<style type="text/css">
	/* Estilos migrados a custom-platzilla.css */
</style>
<header class="navbar{if !empty($NAV_SMALL)} nav-small{/if}" id="header-navbar" {if $HIDE_MENU}style="left: -10px"{/if}>
	<div class="container" style="padding-left: 0;">
		<div class="visible-xs hidden-sm hidden-md hidden-lg">
			<a id="logo" class="navbar-brand" style="">
				<img src="/test/logo/platzi-logo-for-mobile.png">
			</a>
		</div>
		<div class="">
			<button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
				<span class="sr-only">Toggle navigation</span>
				<span class="fa fa-bars"></span>
			</button>
			<div class="nav-no-collapse navbar-left pull-left{if $HIDE_MENU} hidden{else} hidden-sm hidden-xs{/if}">
				<ul class="nav navbar-nav pull-left">
					<li><a class="btn" id="make-small-nav"><i class="fa fa-bars"></i></a></li>
					<li><a class="btn" id="status" style="display: none;"><i class="fa fa-spinner fa-spin"></i></a></li>
				</ul>
			</div>
{if ($REMAINING_TRIAL_DAYS !== null)}
			<div class="hidden-xs hidden-sm pull-left">
				<a href="index.php?module=Home&action=ViewSubscriptionDetails"><p class="text-{if ($REMAINING_TRIAL_DAYS < 0)}danger{else}warning{/if}" style="font-size: 14px; font-weight: 300; line-height: 50px; margin: 0;">{if ($REMAINING_TRIAL_DAYS < 0)}Tu período de prueba ha caducado{elseif ($REMAINING_TRIAL_DAYS == 0)}Hoy es tu último día de pruebas{else}Te quedan {$REMAINING_TRIAL_DAYS} días de prueba{/if}</p></a>
			</div>
{/if}
			<div class="nav-no-collapse hidden-xs hidden-sm col-md-push-{if ($REMAINING_TRIAL_DAYS !== null)}1{else}3{/if} col-md-4">
				<form role="search" name="UnifiedSearch" method="get" action="index.php" style="margin: 8px 0 0 0;" onsubmit="VtigerJS_DialogBox.block();">
					<div class="form-group" style="margin: 0;">
						<input type="hidden" name="action" value="UnifiedSearch" style="margin: 0;">
						<input type="hidden" name="module" value="Home" style="margin: 0;">
						<input type="hidden" name="parenttab" value="Settings" style="margin: 0;">
						<input type="hidden" name="search_onlyin" value="--USESELECTED--" style="margin: 0;">
						<input type="text" style="border:1px solid #dee2e6!important" name="query_string" class="form-control" placeholder="{$APP.LBL_SEARCH}">
					</div>
				</form>
			</div>
			<div class="nav-no-collapse navbar-right pull-right" id="header-nav">
                {math equation= rand() assign= "idDesk"}
				<ul id="btn-group-desk" class="nav navbar-nav pull-right  btn-no-hover">
					<!-- {$MODULE_NAME} -->
					<li>
						{* Comentado por EB - 20200922 para quitar el URL al ícono "?" del header en platzilla, por solicitud de Gladys Granados por usabilidad*}
                        {*<a href="#" class="dropdown-toggle" title="Consejos de uso de tu Platzilla" onclick="return HelpUtils.showHelp ('{$MODULE_NAME}');"><i class="fa fa-question-circle"></i></a>*}
                        {if ((in_array ($MODULE_NAME, $selectedModule)) && (!$URL_ACTION|strpos:'editview') && ($URL_ACTION neq 'editview') && false)}
							<a id="help_{$MODULE_NAME|strtolower}_{$URL_ACTION}" class="dropdown-toggle" title="Consejos de uso de tu Platzilla" onclick="" ><i class="fa fa-question-circle"></i></a>
                        {elseif (!$URL_ACTION|strpos:'editview')  && ($URL_ACTION neq 'editview') && false}
							<a id="help_modulo_{if $URL_ACTION eq 'index'}listview{else}{$URL_ACTION}{/if}" class="dropdown-toggle" title="Consejos de uso de tu Platzilla" onclick="" ><i class="fa fa-question-circle"></i></a>
						{/if}
					</li>
					{if ($MODULE_NAME neq 'daily_report') || true}
					<li>
					<div id="date-group-{$idDesk}" class="btn-group">
						<button type="button" class="btn dropdown-toggle"
								style="margin-top: 0.5em;background-color: white!important;padding: 0.5em!important;"
								title="Crear informe diario"
								data-toggle="dropdown">
							<i class="fa fa-file-text-o" aria-hidden="true"></i>&nbsp;<span class="caret"></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-left" role="menu">
							<li>
								<a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp={$REPORT_TODAY}"
								   data-date="{$HEADER_TODAY}"
								   onclick="DailyReportNavBar.goReportDate(this, '{$idDesk}', event)">Para hoy</a>
							</li>
							<li>
								<a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp={$REPORT_YESTERDAY}"
								   data-date="{$HEADER_YESTERDAY}"
								   onclick="DailyReportNavBar.goReportDate(this, '{$idDesk}', event)">Para ayer</a>
							</li>
							<li>
								<a id="other-date-{$idDesk}" href="#" rel="{$USER_ID}" data-date=""
								   onclick="DailyReportNavBar.createReportDate(this, '{$idDesk}', event)">Otra fecha</a>
							</li>
							<li id="other-date-input-{$idDesk}"  class="hide other-date">
								<input rel="{$USER_ID}" class="form-control pull-right input-readonly b-left col-md-3"
									   placeholder="Seleccione fecha"
									   onclick="DailyReportNavBar.createReportDate(this, '{$idDesk}', event)"
									   value=""
									   type="text" id="report-date-{$idDesk}" readonly="readonly">
							</li>
						</ul>
						<input type="hidden" id="reported_day-{$idDesk}" value="{if $REPORTED_DAYS neq NULL}{$REPORTED_DAYS}{/if}">
						<input type="hidden" id="user-date-format-{$idDesk}" value="{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}">
					</div>
					</li>
					{/if}
					<li id="nav-bell">
						<a href="/index.php?module=Home&action=index"
						   style="padding-right: 0.5em!important;padding-left: 0.5em!important"
						   class="btn{if ($URL_ACTION eq 'index') && ($MODULE_NAME eq 'Home')} btn-primary{/if}" title="Acciones en curso">
							{*<i class="fa fa-home" aria-hidden="true"></i>*}
							<span style="{if ($URL_ACTION eq 'index')  && ($MODULE_NAME eq 'Home')} color: white {else}color: #000000{/if}"  class="icon icon-02-iconos-chat"></span>
							<!--
							<i {if $URL_ACTION eq 'index'} style="color: white"{/if} class="fa fa-check-square" aria-hidden="true"></i>
							-->
						</a>
					</li>
					{* Panel Mail *}
					<li id="mail-panel"><!-- wa 08-11-2019 -->
						<a href="index.php?module=Home&action=MessagesListView" class="btn{if $URL_ACTION eq 'messageslistview'} btn-primary{/if}" title="Correos">
							<i {if $URL_ACTION eq 'messageslistview'} style="color: white"{/if} class="fa fa-envelope" aria-hidden="true"></i><span class="hide" id="bell-num" style="color: white; background-color: red;font-size: 0.6em; padding: 0px 2px;position: relative; top: -10px"></span>
						</a>
					</li>
                    {* /Panel Mail *}
					{* control panel *}
					<li id="metrics-panel">
						<a href="index.php?module=Home&action=CotrolPanelListView" class="btn{if $URL_ACTION eq 'cotrolpanellistview'} btn-primary{/if}" title="Métricas">
							{if $TOTAL_ALERTS > 0}
							<span class="badge  badge-pill badge-danger pull-right" style="margin: 0 2px"><small style="vertical-align: center!important;">{$TOTAL_ALERTS}</small></span>
                            {/if}
							<i {if $URL_ACTION eq 'cotrolpanellistview'} style="color: white"{/if} class="fa fa-tachometer" aria-hidden="true"></i>
						</a>
					</li>
                    {* control panel *}
					{* operting mode button. It was here *}
					<li class="dropdown">
						<a href="#" class="dropdown-toggle btn" data-toggle="dropdown" title="Crear registros"><i class="fa fa-plus"></i></a>
{$MENU_QUICKCREATE}
					</li>
					<li class="dropdown profile-dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<img src="{if $CURRENT_USER_IMAGE}{$CURRENT_USER_IMAGE}{else}themes/centaurus/img/photo.png{/if}" alt="" {if !$CURRENT_USER_IMAGE}style="background-color: #ACACAC;" {/if} />
							<span class="hidden-xs" style="min-width: 3em;">{$USER_FIRST_NAME}</span>
							<i class="fa fa-chevron-circle-down hidden-xs"></i>
						</a>
						<ul class="dropdown-menu">
{if ($IS_ADMIN)}
							<li><a href="index.php?module=Settings&action=index&parenttab=Settings"><i class="fa fa-cog"></i>{$APP.LBL_SETTINGS}</a></li>
{/if}
							<li><a href="index.php?module=Home&action=CustomerView"><i class="fa fa-files-o"></i>{$APP.LBL_MY_ACCOUNT}</a></li>
{if ($IS_ADMIN) && ($IS_INSTANCE)}
							<li><a href="index.php?module=Home&action=ViewSubscriptionDetails"><i class="fa fa-briefcase"></i>Mi suscripción</a></li>
{/if}
{if ($IS_ADMIN) && ($HAS_DEMO_DATA)}
							<li role="separator" class="divider"></li>
							<li>
								<form action="index.php" method="post" onsubmit="return confirm ('¿Estás seguro de eliminar los datos de ejemplo para evaluar Platzilla?');">
									<input type="hidden" name="module" value="Home" />
									<input type="hidden" name="action" value="DeleteDemoData" />
									<input type="hidden" name="code" value="{$CODE}" />
									<input type="hidden" name="Ajax" value="true" />
								</form>
								<a href="javascript:;" style="padding-left: 17px;" onclick="jQuery(this).closest('li').find ('form').submit ();">Eliminar datos de<br class="hidden-xs hidden-sm" />ejemplo</a>
							</li>
{/if}
{if ($REMAINING_TRIAL_DAYS !== null)}
							<li role="separator" class="divider hidden-md hidden-lg"></li>
							<li><a href="index.php?module=Home&action=ViewSubscriptionDetails" class="hidden-md hidden-lg" style="padding-left: 17px;"><span class="text-{if ($REMAINING_TRIAL_DAYS < 0)}danger{else}warning{/if}">{if ($REMAINING_TRIAL_DAYS < 0)}Tu período de prueba ha caducado{elseif ($REMAINING_TRIAL_DAYS == 0)}Hoy es tu último día de pruebas{else}Te quedan {$REMAINING_TRIAL_DAYS} días de prueba{/if}</span></a></li>
{/if}
							<li role="separator" class="divider"></li>
							<li><a href="index.php?module=Users&action=Logout{if (isset ($smarty.session.impersonation_token))}&impersonationtoken={$smarty.session.impersonation_token}{/if}"><i class="fa fa-power-off"></i>{$APP.LBL_LOGOUT}</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</div>
    {* Agregado por AV para integrar el AFTERCLICK - 20210427 *}
    {literal}
	<!-- Pixel Code for https://app.afterclick.co/
		 <script async src="https://app.afterclick.co/pixel/ifGwlnV7IXFqt3YN"></script>
	END Pixel Code -->
    {/literal}
    {* Agregado por AV para integrar el AFTERCLICK - 20210427 *}
	{* Agregado por AV para integrar el Tooltip Player - 20191112 *}
	{if ($SITE_URL eq "https://apphelpcrunch.platzillatest.com/")}
        {* Tooltip player *}
	{literal}
		<script type='text/javascript'>
			window.Tooltip||function(t,e){var o={
			url:"https://cdn.tooltip.io/static/player.js",
			key:"4eaa3ff4-ae46-4a07-918f-1c1dd6af1038",
			async:true
			};
			window.Tooltip={cs:[],_apiKey:o.key};for(
			var r=["identify","goal","updateUserData","start","stop","refresh","show","hide","on"],
			i={},n=0;n<r.length;n++){var a=r[n];i[a]=function(t){return function(){var e=Array.prototype.slice.call(arguments);
			window.Tooltip.cs.push({method:t,args:e})}}(a)}window.Tooltip.API=i;var n=t.createElement(e),s=t.getElementsByTagName(e)[0];
			n.type="text/javascript",n.async=o.async,s.parentNode.insertBefore(n,s),n.src=o.url}(document,"script");
		</script>
	{/literal}
        {* Tooltip player *}
	{elseif $SITE_URL eq "https://app.platzilla.com/"}
	{literal}
		<!--- Tooltip player -->
		<script type='text/javascript'>
			window.Tooltip||function(t,e){var o={
			url:"https://cdn.tooltip.io/static/player.js",
			key:"9fdb423a-2693-4cf1-8221-48ca461ccfea",
			async:true
			};
			window.Tooltip={cs:[],_apiKey:o.key};for(
			var r=["identify","goal","updateUserData","start","stop","refresh","show","hide","on"],
			i={},n=0;n<r.length;n++){var a=r[n];i[a]=function(t){return function(){var e=Array.prototype.slice.call(arguments);
			window.Tooltip.cs.push({method:t,args:e})}}(a)}window.Tooltip.API=i;var n=t.createElement(e),s=t.getElementsByTagName(e)[0];
			n.type="text/javascript",n.async=o.async,s.parentNode.insertBefore(n,s),n.src=o.url}(document,"script");
		</script>
		<!--- Tooltip player -->
	{/literal}
	{elseif $SITE_URL eq "https://app.platzillatest.com/"}
        {* Tooltip player *}
	{literal}
		<script type='text/javascript'>
			window.Tooltip||function(t,e){var o={
			url:"https://cdn.tooltip.io/static/player.js",
			key:"4bea5d59-026b-482e-800a-b64fe11a79a5",
			async:true
			};
			window.Tooltip={cs:[],_apiKey:o.key};for(
			var r=["identify","goal","updateUserData","start","stop","refresh","show","hide","on"],
			i={},n=0;n<r.length;n++){var a=r[n];i[a]=function(t){return function(){var e=Array.prototype.slice.call(arguments);
			window.Tooltip.cs.push({method:t,args:e})}}(a)}window.Tooltip.API=i;var n=t.createElement(e),s=t.getElementsByTagName(e)[0];
			n.type="text/javascript",n.async=o.async,s.parentNode.insertBefore(n,s),n.src=o.url}(document,"script");
		</script>
	{/literal}
        {* Tooltip player *}
	{elseif ($SITE_URL eq "https://appwilfredo.platzillatest.com/") && false}
        {* Campana usabilidad platzilla sept 2020. Gladys Granados *}
        {* Tooltip player *}
	{literal}
				<script type='text/javascript'>
				window.Tooltip||function(t,e){var o={
				url:"https://cdn.tooltip.io/static/player.js",
				key:"87a958fa-addd-4b3e-bdb8-0104e13e1f93",
				async:true
				};
				window.Tooltip={cs:[],_apiKey:o.key};for(
				var r=["identify","goal","updateUserData","start","stop","refresh","show","hide","on"],
				i={},n=0;n<r.length;n++){var a=r[n];i[a]=function(t){return function(){var e=Array.prototype.slice.call(arguments);
				window.Tooltip.cs.push({method:t,args:e})}}(a)}window.Tooltip.API=i;var n=t.createElement(e),s=t.getElementsByTagName(e)[0];
				n.type="text/javascript",n.async=o.async,s.parentNode.insertBefore(n,s),n.src=o.url}(document,"script");
				</script>
	{/literal}
        {* Tooltip player *}
	{else}
        {* Tooltip player *}
	{literal}
		<!--
		<script type='text/javascript'>
			window.Tooltip||function(t,e){var o={
			url:"https://cdn.tooltip.io/static/player.js",
			key:"9fdb423a-2693-4cf1-8221-48ca461ccfea",
			async:true
			};
			window.Tooltip={cs:[],_apiKey:o.key};for(
			var r=["identify","goal","updateUserData","start","stop","refresh","show","hide","on"],
			i={},n=0;n<r.length;n++){var a=r[n];i[a]=function(t){return function(){var e=Array.prototype.slice.call(arguments);
			window.Tooltip.cs.push({method:t,args:e})}}(a)}window.Tooltip.API=i;var n=t.createElement(e),s=t.getElementsByTagName(e)[0];
			n.type="text/javascript",n.async=o.async,s.parentNode.insertBefore(n,s),n.src=o.url}(document,"script");
		</script>
		-->
	{/literal}
        {* Tooltip player *}
	{/if}
	{* Agregado por AV para integrar el Tooltip Player - 20191112 *}
</header>
{/strip}
