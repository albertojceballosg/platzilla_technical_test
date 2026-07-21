{strip}
{assign var='tabs' value=$OPERATING_MODES->getTabTabs()}
{assign var='attributes' value=$OPERATING_MODES->getAttributes()}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/home-platzilla.css" />
<!-- Estilos migrados a home-platzilla.css -->
<!-- CSS de módulos y librerías usados en Home -->
<link rel="stylesheet" type="text/css" href="modules/graficosgenerales/graficosgenerales.css" />
<link rel="stylesheet" type="text/css" href="modules/materials/materials.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/datepicker.css" />
<link rel="stylesheet" type="text/css" href="modules/Courses/Courses.css" />
<!-- Si se requiere máxima optimización, considerar carga condicional de CSS según pestaña activa -->
{if (!empty ($SELECTED_TAB)) && (in_array ($SELECTED_TAB, array ('MESSAGES', 'ACTIVITY')))}
	{assign var='selectedTab' value=$SELECTED_TAB}
{else}
	{assign var='selectedTab' value='MESSAGES'}
{/if}
{if (!$CAN_CREATE_RECORDS)}
<div class="alert alert-danger">
	<span><strong>Advertencia: </strong> El módulo está suscrito en modo de pruebas. Has llegado al límite de registros que puedes crear en este modo.</span>
	{if ($IS_ADMIN)}
		<span>Te invitamos a actualizar <a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription">tu suscripción</a></span>
	{/if}
</div>
{/if}
{if (!empty ($MESSAGE))}
<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
	<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
</div>
{/if}
{* Display on-screen notifications (ALERT, NOTIFY) *}
{if $NOTIFICATIONS && count($NOTIFICATIONS) > 0}
	{foreach from=$NOTIFICATIONS item=notification}
		{if $notification->getStyle() == 'ALERT'}
			{$notification->getContents()|regex_replace:"/__ID__/":$notification->getId()}
		{/if}
	{/foreach}
{/if}
	{if $DEFAULT_OPERATING eq 'MANAGEMENT_MODE' || $DEFAULT_OPERATING eq 'DIRECTION_MODE'}
	<div class="row module-buttons">
		<div class="col-lg-12 col-md-12 col-xs-12" style="padding-right: 10px; padding-bottom: 0">
		<div class="row">
			<div class=
			{if $DEFAULT_OPERATING eq 'DIRECTION_MODE'}
				 "pull-left col-lg-9 col-md-9 col-xs-9">
			{else}
				 "pull-left col-lg-6 col-md-6 col-xs-6">
			{/if}
				{if $DEFAULT_OPERATING eq 'DIRECTION_MODE'}
					{include file='Home/WeeklyReport/Base/SearchButtonsblock.tpl'}
				{else}
					<h1 class="home-title">{$VIEW_TITLE}</h1>
				{/if}
			</div>
			<div class=
			{if $DEFAULT_OPERATING eq 'DIRECTION_MODE'}
				 "col-lg-3 col-md-3 col-xs-3">
			{else}
				 "col-lg-6 col-md-6 col-xs-6">
			{/if}
				<div class="pull-right">
				<ul class="nav nav-tabs nav-platzilla" {if $attributes['hide-tab']}style="display: none"{/if}>
                    {foreach $tabs as $tab}
                        {assign var='contentTab' value=$tab->getModesContent()}
                        {assign var='spanClass' value='hide'}
                        {assign var='totalInTab' value=''}
                        {if $contentTab->getBufferOut() eq NULL}{continue}{/if}
                        {if ($contentTab->getValue() neq '0') && ($contentTab->getValue() neq NULL) }
                            {assign var='spanClass' value ='status-new'}
                            {assign var='totalInTab' value = $contentTab->getValue()}
                        {/if}
						<li{if ($SELECTED_TAB eq $contentTab->getName())} class="active"{/if}><a data-toggle="tab" href="#{$contentTab->getName()}">{$contentTab->getLabel()}<span id="home-{$contentTab->getName()}"  class="{$spanClass}" >{$totalInTab}</span></a></li>
                    {/foreach}
				</ul>
				</div>
			</div>
		</div>
		</div>
	</div>
    {/if}
	<div class="container-fluid base-list-container" style="margin-top: -6px!important;">
		<div class="tab-content">
			{foreach $tabs as $tab}
			{assign var='contentTab' value=$tab->getModesContent()}
				{if $contentTab->getBufferOut() eq NULL}{continue}{/if}
				<div id="{$contentTab->getName()}" class="tab-pane fade{if ($SELECTED_TAB eq $contentTab->getName())} active in{/if}">
					<div {if $contentTab->getName() eq 'PROJECTS'}id="ListViewContents"{/if}>
						{$contentTab->getBufferOut()}
					</div>
				</div>
			{/foreach}
		</div>
	</div>
<script type="text/html" id="email-related-entity-row-template">
{include file='Home/TabsContents/RelatedEntityRow.tpl'}
</script>
<script type="text/html" id="email-viewer-modal-template">
{include file='Home/EmailViewerModal.tpl'}
</script>
{block name="js"}
    <!-- Scripts necesarios para Home -->
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        {literal}
        (function() {
            var userLang = '{/literal}{php}echo isset($_SESSION['authenticated_user_language']) ? $_SESSION['authenticated_user_language'] : 'es_es';{/php}{literal}';
            var theme = '{/literal}{$THEME}{literal}';
            
            // Si el idioma ya está cargado, no hacer nada
            if (jQuery.fn.datepicker.dates && jQuery.fn.datepicker.dates[userLang]) {
                return;
            }
            
            // Cargar el archivo de idioma
            var langFile = 'themes/' + theme + '/js/bootstrap-datepicker.' + userLang + '.js';
            
            jQuery.getScript(langFile)
                .done(function() {
                    // Idioma cargado exitosamente
                })
                .fail(function() {
                    // Intentar con español como fallback
                    jQuery.getScript('themes/' + theme + '/js/bootstrap-datepicker.es.js')
                        .done(function() {
                            // Español cargado como fallback
                        })
                        .fail(function() {
                            // Si todo falla, continuar sin idioma específico
                        });
                });
        })();
        {/literal}
    </script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="webmail/program/js/common.min.js"></script>
    <script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.6"></script>
    <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
    <script src="themes/centaurus/js/dx.all.js"></script>
    
    <script type="text/javascript">
        {if $NOTIFICATIONS && count($NOTIFICATIONS) > 0}
            // Scroll automático para mostrar las notificaciones
            jQuery(document).ready(function() {
                // Hacer scroll al div#content-wrapper para asegurar que las notificaciones sean visibles
                var contentWrapper = jQuery('div#content-wrapper');
                if (contentWrapper.length > 0) {
                    jQuery('html, body').animate({
                        scrollTop: contentWrapper.offset().top
                    }, 500);
                } else {
                    // Fallback: scroll al inicio de la página
                    jQuery('html, body').animate({ scrollTop: 0 }, 500);
                }
            });
        {/if}
    </script>
{/block}
<!-- Eliminada duplicidad de report_rails-utils.js y agrupados scripts en bloque js -->
{/strip}