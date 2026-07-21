{block name="css"}
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/graphics-improvements.css">
    <style type="text/css">
        svg {
            overflow: visible;
            width: 100% !important;
            height: 600px !important;
        }

        .tabs-wrapper > .tab-content {
            margin-bottom: 0;
        }

        .graph.simple {
            height: 382px;
            padding: 0;
            position: relative;
            width: 100%;
            min-height: 382px;
        }

        .graph.simple.embudo {
            width: 90%;
            margin: auto;
        }

        .graph.simple.embudo .legend {
            display: none;
        }

        .graph.simple.embudo .funnelLabel {
            background-color: rgba(0, 0, 0, 0.25);
            color: #FFFFFF;
            font-size: 12px;
            left: 50% !important;
            padding: 5px;
            transform: translate(-50%, -20px);
        }

        .rounded {
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
        }

        .row-graphic {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;

        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .justify-content-between {
            -webkit-box-pack: justify !important;
            -webkit-justify-content: space-between !important;
            -ms-flex-pack: justify !important;
            justify-content: space-between !important;
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
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
            display: inline-block; /* For IE11/ MS Edge bug */
            pointer-events: none;
            text-decoration: none;
        }
        .google-visualization-table-type-number {
            text-align: right!important;
        }
        .platzilla-headerRow {
            font-size: 12px;
            font-weight: bold;
            border-left: 1px solid #D0E4F5;
            padding: 6px 8px !important;
            height: 32px !important;
            line-height: 1.2 !important;
        }

        .platzilla-headerRow > .gradient {
            color: #FFFFFF !important;
            text-align: center !important;
            background: #2C3E50;
        }

        .platzilla-headerRow th {
            font-size: 12px !important;
            padding: 6px 8px !important;
            height: 32px !important;
            line-height: 1.2 !important;
        }

        .platzilla-tableRow {
            font-size: 12px !important;
            border: 1px solid #ddd !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }

        .platzilla-tableCell {
            font-size: 12px !important;
            border-left: 1px solid #ddd !important;
            padding: 4px 8px !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }

        .platzilla-tableRow tr {
            font-family: sans-serif !important;
            font-size: 12px !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }

        .platzilla-tableRow td {
            font-size: 12px !important;
            padding: 4px 8px !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }

        .platzilla-oddtableRow {
            font-size: 12px !important;
            border: 1px solid #ddd !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }

        .platzilla-oddtableRow tr {
            font-size: 12px !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }

        .platzilla-oddtableRow td {
            font-size: 12px !important;
            padding: 4px 8px !important;
            height: 28px !important;
            line-height: 1.2 !important;
        }
        @media (min-width: 1280px) and (max-width: 1300px) {
            .list-view-grafich {
                margin-left: -70px !important;
            }
            .graph-form-btn {
                margin-left: -8px!important;
            }
            .graph-form-input {
                margin-left: -8px!important;
            }
            .graph-select-btn {
                margin-left: -60px!important;
            }
            #graphic-listview {
                width: 104.5%;
                margin-left: -30px;
            }
            .graph-chat-btn-goup {
                margin-right: -12px!important;
            }
        }

        @media (min-width: 1400px) and (max-width: 1580px) {
            .list-view-grafich {
                margin-left: -105px !important;
            }
            .graph-form-btn {
                margin-left: -8px!important;
            }
            .graph-form-input {
                margin-left: -8px!important;
            }
            .graph-select-btn {
                margin-left: -70px!important;
            }
            #graphic-listview {
                width: 103%;
                margin-left: -25px;
            }
            .graph-chat-btn-goup {
                margin-right: -16px!important;
            }
        }
        @media (min-width: 1600px) and (max-width: 1800px) {
            .graph-form-btn {
                margin-left: -8px!important;
            }
            .graph-form-input {
                margin-left: -8px!important;
            }
            .graph-select-btn {
                margin-left: -80px!important;
            }
            #graphic-listview {
                width: 103%;
                margin-left: -25px;
            }
            .graph-chat-btn-goup {
                margin-right: -14px!important;
            }
        }
        .platzilla-card-header {
            background-color: #FFFFFF;
            border-bottom-color: #FFFFFF;
            font-family: helvetica, arial, sans-serif;
            font-size: 1.5em
        }
        .google-visualization-table-table {
            margin-left: auto;
            margin-right: auto;
        }
        .google-visualization-table {
            padding-top: 15px;
            padding-left: 20%!important;
            padding-right: 20%!important;
        }
        .input-group {
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        #graphic-tab-period { width: 120px; }
        #graphic-tab-from,
        #graphic-tab-to { width: 110px; }
        .graphics-fields-row .input-group {
            width: auto;
            flex: 0 0 auto;
        }
        #graphic-tab-period { width: 120px; }
        #graphic-tab-from,
        #graphic-tab-to { width: 110px; }
        .platzilla-graphics-inline-fields {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .platzilla-graphics-inline-fields .input-group {
            display: flex;
            align-items: center;
            margin-bottom: 0;
            flex: 1 1 0;
            min-width: 0;
            max-width: 220px;
        }
        .platzilla-graphics-inline-fields .input-group-addon {
            min-width: 36px;
            width: 36px;
            justify-content: center;
            padding: 6px 0;
            margin-right: 0;
            background: #f5f5f5;
            border-radius: 4px 0 0 4px;
            border: 1px solid #ccc;
            border-right: none;
            height: 34px;
        }
        .platzilla-graphics-inline-fields .input-group input.form-control,
        .platzilla-graphics-inline-fields .input-group select.form-control {
            width: 100%;
            min-width: 0;
            flex: 1 1 0;
            border-radius: 0 4px 4px 0;
            border-left: none;
            padding-left: 10px;
        }
        .platzilla-graphics-inline-fields .input-group i {
            font-size: 16px;
            margin: 0;
        }
        .platzilla-graphics-inline-fields .btn {
            height: 34px;
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            font-size: 16px;
        }
    </style>
{/block}
{block name="js"}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
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
{/block}
{assign var='today' value=date('Y-m-d')}
{assign var='lastWeek' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('7 days')), 'Y-m-d')}
{assign var='lastMonth' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('1 month')), 'Y-m-d')}
{assign var='lastQuarter' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('3 months')), 'Y-m-d')}
{assign var='lastMidyear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('6 months')), 'Y-m-d')}
{assign var='lastYear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('12 months')), 'Y-m-d')}
{math equation='x - y' x=12 y=$STATUS_TOTAL_BUTTONS assign='col'}
{block name="first-content"}{/block}
<div class="container-fluid base-list-container">
    <div class="row">
        <div class="col-lg-12">
            <div class="main-box clearfix">
                <div class="main-box-header clearfix">
                    <div class="row">
                        {* listView tab menu *}
                        <div class="col-md-2">
                            <div class="btn-group btn-control pull-left">
                                {* LIST-VIEW
                                <a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
                                   style=" font-size: 15px!important; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                   data-toggle="tab" title="Listado de registros"><i
                                            class="fa fa-list-ul"></i></a>
                                 *}
                                {* LIST-VIEW-KANBAN-VIEW *}
                                {if $STATUS_BUTTONS['kanban'] && false}
                                    <a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW"
                                       class="btn btn-default" style=" font-size: 15px!important; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                       title="Vista kanban"
                                       onclick="ListViewTabUtils.activeKanbanTab (event)"
                                       data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                {/if}
                                {* LIST-VIEW-BOX-SCORE *}
                                {if $STATUS_BUTTONS['boxscore']}
                                    <a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE"
                                       class="btn btn-default"
                                       title="Indicadores de gestión"
                                       onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                       data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                {/if}
                                {* LIST-VIEW-GRAPHIC *}
                                {if $STATUS_BUTTONS['graphic']}
                                    <button type="button" class="btn btn-primary"
                                            title="Graficos"><i
                                                class="fa fa-bar-chart-o"></i>
                                    </button>
                                {/if}
                                {* report *}
                                {if $STATUS_BUTTONS['report']}
                                    <a data-toggle="tab" href="#LIST-VIEW-REPORT"
                                       class="btn btn-default"
                                       title="Informes"
                                       onclick="ListViewTabUtils.activeReportTab (event)"
                                       data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
                                {/if}
                                {* LIST-VIEW-CALENDAR *}
                                {if $STATUS_BUTTONS['calendar'] & false}
                                    <a data-toggle="tab" href="#LIST-VIEW-CALENDAR"
                                       class="btn btn-default" style="font-size: 15px!important;height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                       title="vista calendario"
                                       onclick="ListViewTabUtils.activeCalendarTab (event)"
                                       data-toggle="tab"><i class="fa fa-calendar" style="vertical-align:middle;"></i></a>
                                {/if}
                            </div>
                        </div>
                        {* Graphics form *}
                        <div class="col-md-8">
                            <form id="graphic-filters" class="row">
                                <input type="hidden" name="Ajax" value="true"/>
                                <input type="hidden" name="activeTab" id="activeTab"
                                       value="{$ACTIVE_TAB}">
                                <input type="hidden" name="fl_module" id="fl_module"
                                       value="{$FL_MODULE}">
                                <input type="hidden" name="Favorites" id="Favorites" value="">
                                <input type="hidden" name="is_home" id="is_home" value=0>
                                <input type="hidden" name="hidden_tab" id="hidden_tab" value=1>
                                <input type="hidden" name="graphicCategory" id="graphicCategory" value='STANDARD'>
                                <input type="hidden" name="return_module" value="{$MODULE}"/>
                                <input type="hidden" name="howusename" value="{$HOW_USENAME}"/>
                                <div class="graphics-fields-row platzilla-graphics-inline-fields">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                        <select id="graphic-tab-period" class="form-control"
                                                title="Buscar por tiempo"
                                                data-last-time="{$lastYear}"
                                                data-today="{$today}"
                                                onchange="GraphUtils.searchGraphicsHome(this)">
                                            <option value="" disabled>Personalizado</option>
                                            <option value="{$today}">Hoy</option>
                                            <option value="{$lastWeek}">Última semana
                                            </option>
                                            <option value="{$lastMonth}">Último mes</option>
                                            <option value="{$lastQuarter}"
                                                    selected="selected">
                                                Último trimestre
                                            </option>
                                            <option value="{$lastMidyear}">Último semestre
                                            </option>
                                            <option value="{$lastYear}">Último año</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="graphic-tab-from"
                                               name="graphicsDateFrom" value="{$lastWeek}"
                                               class="form-control from-field"
                                               readonly="readonly"/>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="graphic-tab-to"
                                               name="graphicsDateTo"
                                               value="{$today}"
                                               class="form-control to-field"
                                               readonly="readonly"/>
                                    </div>
                                    <button name="submitSearch" id="graphicsSubmitSearch" class="btn btn-primary btn-sm" type="button" onclick="GraphUtils.customSearch(this)"><i class="fa fa-search" aria-hidden="true"></i></button>
                                </div>
                            </form>
                        </div>
                        {* Graphics Form*}
                        <div class="col-md-2">
                            {if $IS_INSTANCE}
                                <div class="btn-group pull-right graph-select-btn">
                                    <button  id="std-graphic-btn"  type="button" data-partner="#custom-graphic-btn" data-category="STANDARD" class="btn btn-primary" onclick="GraphUtils.selectCategory (this)">Estándar</button>
                                    <button id="custom-graphic-btn" type="button" data-partner="#std-graphic-btn" data-category="CUSTOM" class="btn btn-default" onclick="GraphUtils.selectCategory (this)">Personalizado</button>
                                </div>
                            {/if}
                        </div>
                        {* Tab platzilla custom *}
                    </div>
                    {* /Graphics *}
                    {* show graphics *}
                    <div id="graphic-listview" class="col-md-12">
                        {* include file='Home/TabsContents/GraphicListView.tpl' *}
                        {include file='modules/graficosgenerales/GraphicModulesListView.tpl'}
                    </div>
               </div>
            </div>
        </div>
    </div>
</div>
{literal}
    <script>
        jQuery('#graphic-tab-from').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
        jQuery('#graphic-tab-to').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
    </script>
{/literal}

{* Modal de Previsualización de Gráficos *}
{literal}
<!-- Modal de Previsualización de Gráfico -->
<div class="modal fade" id="graphPreviewModal" tabindex="-1" role="dialog" aria-labelledby="graphPreviewModalLabel">
    <div class="modal-dialog" role="document" style="width: 90vw; max-width: 90vw; height: 90vh; margin: 5vh auto;">
        <div class="modal-content" style="height: 100%;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title text-center" id="graphPreviewModalLabel">Previsualización del Gráfico</h4>
            </div>
            <div class="modal-body" style="height: calc(100% - 120px); padding: 20px; display: flex; justify-content: center; align-items: center;">
                <div id="graphPreviewContainer" style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
                    <img src="themes/images/loading.gif" alt="Cargando..." class="img-responsive center-block" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
#graphPreviewModal .modal-dialog {
    margin: 5vh auto;
    width: 90vw;
    max-width: 90vw;
}

#graphPreviewModal .modal-content {
    border-radius: 8px;
    height: 90vh;
}

#graphPreviewModal .modal-body {
    overflow: hidden;
    height: calc(90vh - 120px);
    padding: 20px;
}

#graphPreviewContainer {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

#graphPreviewContainer > div {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

#graphPreviewContainer svg {
    display: block;
    margin: auto;
}

#graphPreviewContainer img {
    max-width: 100%;
    max-height: 100%;
}
</style>
{/literal}

<script type="text/javascript" src="modules/graficosgenerales/graphPreview.js"></script>