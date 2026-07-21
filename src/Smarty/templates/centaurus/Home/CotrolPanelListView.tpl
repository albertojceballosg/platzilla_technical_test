{strip}
    <link type="text/css" rel="stylesheet" href="modules/Home/home-metrics.css"/>
    <link type="text/css" rel="stylesheet" href="modules/materials/materials.css"/>
    <link type="text/css" rel="stylesheet" href="modules/Reports/foldrer-report.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
    <link type="text/css" rel="stylesheet" href="modules/Courses/Courses.css"/>
    {if (!$CAN_CREATE_RECORDS)}
        <div class="alert alert-danger">
            <span><strong>Advertencia: </strong> El módulo está suscrito en modo de pruebas. Has llegado al límite de registros que puedes crear en este modo.</span>
            {if ($IS_ADMIN)}
                <span>Te invitamos a actualizar
                    <a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription">tu suscripción</a></span>
            {/if}
        </div>
    {/if}
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    {*if $DEFAULT_OPERATING eq 'MANAGEMENT_MODE'*}
        <div class="row module-buttons">
            <div class="col-lg-12" style="padding-right: 10px; padding-bottom: 0;margin-top: 12px">
                <div class="pull-left">
                    <h1 style="margin-left: -3px;font-weight: bold">
                        Métricas
                    </h1>
                </div>
                <div class="pull-right">
                    <ul class="nav nav-tabs nav-platzilla">
                        <li class="">
                            <a href="index.php?module=indicatorspanel&action=index" title="ir al panel de indicadores">Indicadores</a>
                        </li>
                        <li {if $SELECTED_TAB eq 'graphics'}class="active"{/if}>
                            <a data-toggle="tab" href="#metrics-alerts">Alertas</a>
                        </li>
                        <li {if $SELECTED_TAB eq 'graphics'}class=""{/if}>
                            <a data-toggle="tab" href="#metrics-graphics">Gráficos</a>
                        </li>
                        <li {if $SELECTED_TAB eq 'report'}class="active"{/if}>
                            <a data-toggle="tab" href="#metrics-reports">Informes</a>
                        </li>
                        <li  style="display: none"  {if $SELECTED_TAB eq 'kpis'}class="active"{/if}>
                            <a data-toggle="tab" href="#metrics-kpis">KPIs</a>
                        </li>
                        <li style="display: none" {if $SELECTED_TAB eq 'okrs'}class="active"{/if}>
                            <a data-toggle="tab" href="#metrics-okrs">OKRs</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    {*/if*}
    <div class="container-fluid base-list-container">
    <div class="tab-content">
        {* Alerts *}
        <div id="metrics-alerts" class="tab-pane fade in{if $SELECTED_TAB eq 'graphics'} active{/if}">
            {include file='Home/TabsContents/SystemAlert.tpl'}
        </div>
        {* Graphics *}
        <div id="metrics-graphics" class="tab-pane fade in{if $SELECTED_TAB eq 'graphics'} {/if}">
        {include file='Home/TabsContents/graphicTab.tpl'}
        </div>
        {* Informes *}
        <div id="metrics-reports" class="tab-pane fade in{if $SELECTED_TAB eq 'report'} active{/if}">
            {include file='Home/ReportListView.tpl'}
        </div>
    </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="webmail/program/js/common.min.js"></script>
    <script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.6"></script>
    <script type="text/javascript" src="modules/Reports/Reports.js"></script>
    <script src="themes/centaurus/js/dx.all.js"></script>
{/strip}