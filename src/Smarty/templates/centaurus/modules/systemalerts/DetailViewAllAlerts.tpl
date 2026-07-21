{strip}
    {assign var='today' value=date('Y-m-d')}
    {assign var='lastWeek' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('7 days')), 'Y-m-d')}
    {assign var='lastMonth' value=$FIRST_DAY}
    {assign var='lastQuarter' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('3 months')), 'Y-m-d')}
    {assign var='lastMidyear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('6 months')), 'Y-m-d')}
    {assign var='lastYear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('12 months')), 'Y-m-d')}
    <style type="text/css">
        th {
            text-align: center;
        }

        /* Important part */
        .modal-dialog {
            overflow-y: initial !important
        }

        .modal-body {
            height: 400px;
            overflow-y: auto;
        }
    </style>
    <div class="main-box clearfix" style="margin-top: 13px;background: transparent!important;">
        <div class="main-box-body"> {*clearfix*}
            <div id="reportrange" class="row" style="background-color: white;margin: 0 2px">
                <div class="col-md-12" style="margin-top: 20px">
                    <form name="parametersDetail" id="parametersDetail" method="POST" action="index.php">
                        <input type="hidden" name="module" id="module" value="{$MODULE}">
                        <input type="hidden" name="action" id="action" value="DetailViewAllAlerts">
                        <input type="hidden" name="app" id="app" value="{$TAB_ACTIVE}">
                        <input type="hidden" name="viewPeriod" id="viewPeriod" value="{if $VIEW_SEARCH neq NULL}{$VIEW_SEARCH}{else}Month{/if}">
                        <div class="col-md-2">
                            <div class="form-group">
                                <button type="button"
                                        data-table="#alerts-table-{$idAlertListView}"
                                        data-view="{if $COUNT_ALL_ALERTS neq NULL}pending{else}all{/if}"
                                        onclick="SystemAlertUtils.lookAlert(this)"
                                        class="btn btn-info">
                                    <i class="fa fa-eye"></i>&nbsp;{if $COUNT_ALL_ALERTS neq NULL}Todas las alertas{else}Alertas pendientes{/if}</button>
                                {*<label>{$MODSTRING.LBL_ALERT_PERIODICITY}</label>*}
                                <div class="input-group" style="display: none">
                                    <div class="input-group-addon">
                                        <i class="fa  fa-clock-o"></i>
                                    </div>
                                    <select class="form-control" id="viewPeriod" name="viewPeriod" title="">
                                        <option value="">{$MODSTRING.LBL_SELECTION_PERIODICITY}</option>
                                        <option value="Month" {if ($VIEW_SEARCH == 'Month')} selected="selected"{/if}>{$MODSTRING.LBL_VIEW_MONTH}</option>
                                        <option value="Week" {if ($VIEW_SEARCH == 'Week')} selected="selected"{/if}>{$MODSTRING.LBL_VIEW_WEEK}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">&nbsp;
                                <span class="label label-warning">{if $COUNT_ALL_ALERTS neq NULL}{$COUNT_ALL_ALERTS} Alertas{else} No hay alertas pendientes{/if}</span>
                            <div class="form-group" style="margin-top: 0">

                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row pull-right">
                                <div class="col-md-4" style="padding-right:0px;">
                                <div class="input-group">
                                    <span class="input-group-addon hidden-md"><i class="fa fa-clock-o"></i></span>
                                    <select id="graphic-tab-period" class="form-control" title="Buscar por tiempo"  data-last-time="{$lastYear}" data-today="{$today}" onchange="SystemAlertUtils.searchAlertsDate(this)">
                                        <option value="{$today}" {if $DATE_FROM eq $today} selected="selected"{/if}>Hoy</option>
                                        <option value="{$lastWeek}" {if $DATE_FROM eq $lastWeek} selected="selected"{/if}>Última semana</option>
                                        <option value="{$lastMonth}" {if $DATE_FROM eq $lastMonth} selected="selected"{/if}>Mes actual</option>
                                        <option value="{$lastQuarter}" {if $DATE_FROM eq $lastQuarter} selected="selected"{/if}>Último trimestre</option>
                                        <option value="{$lastMidyear}" {if $DATE_FROM eq $lastMidyear} selected="selected"{/if}>Último semestre</option>
                                        <option value="{$lastYear}" {if $DATE_FROM eq $lastYear} selected="selected"{/if}>Último año</option>
                                    </select>
                                </div>
                                </div>
                                <div class="col-md-3" style="padding-right:0px;">
                                    <div class="form-group">
                                        {*<label>{$MODSTRING.LBL_DATE_FROM}</label>*}
                                        <div class="input-group">
                                            <div class="input-group-addon" style="border: 1px solid #ddd !important">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right input-readonly b-left"
                                                   id="date_from" name="date_from" readonly="readonly"
                                                   value="{$DATE_FROM}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3" style="padding-right:0px;">
                                    <div class="form-group">
                                        {*<label>{$MODSTRING.LBL_DATE_TO}</label>*}
                                        <div class="input-group">
                                            <div class="input-group-addon" style="border: 1px solid #ddd !important">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right input-readonly b-left"
                                                   id="date_to" name="date_to" readonly="readonly" value="{$DATE_TO}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">&nbsp;
                                    <div class="pull-left">
                                        <button name="submitSearch" id="submitSearch" class="btn btn-primary btn-sm"
                                                onclick="SystemAlertUtils.searchAlerts()" type="button"><i class="fa fa-search"
                                                                                        aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="main-box clearfix" style="background: transparent!important;">
        {if $ALL_ALERTS neq NULL}
            {foreach $ALL_ALERTS as $keyApp => $itemAlert}
                <div class="main-box-body clearfix" style="margin-top: 13px;">
                    <div class="table-responsive" style="background-color: white;margin: 0 2px">
                        <table id="alerts-table-{$idAlertListView}" class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th colspan="6" class="alert-grey lft">{$MODSTRING.APP}:
                                    &nbsp;&nbsp;{$itemAlert.app_name} &nbsp;<span
                                            class="badge badge-warning">{$itemAlert.countAlert}</span></th>
                            </tr>
                            <tr>
                                <th class="ctr" style="width: 25%">{$MODSTRING.LBL_ALERT_TITLE}</th>
                                <th class="ctr" style="width: 15%">{$MODSTRING.LBL_ALERT_ENTITY}</th>
                                <th class="ctr" style="width: 15%">{$MODSTRING.LBL_ALERT_TYPE}</th>
                                {*<th class="ctr" style="width: 15%">{$MODSTRING.LBL_ALERT_OPERATOR}</th>*}
                                <th class="ctr" style="width: 15%">{$MODSTRING.LBL_ALERT_NUM_ALERT}</th>
                                <th class="ctr" style="width: 15%">&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $itemAlert.alerts as $keyAlert => $alert}
                                {if $alert.num_alerts == ''}
                                    {assign var='numAlert' value=0}
                                {else}
                                    {assign var='numAlert' value=$alert.num_alerts}
                                {/if}
                                <tr id="row-{$alert.systemalerts_id}" {if ($COUNT_ALL_ALERTS neq NULL) && ($numAlert eq '0') }class="hide"{/if} data-alerts-occurrences="{$numAlert}">
                                    <!-- {$alert.num_alerts} -->
                                    {if $MODSTRING[$alert.alert] == '' }
                                        <td style="text-align: center">{$alert.alert}{if $alert.description neq NULL}&nbsp;<a  href="#"  class=" protip" data-title='{$alert.description}' data-pt-title='{$alert.description}' data-placement="top" data-toggle="tooltip" >
                                                <i class="fa fa-info-circle"></i></a>{/if}
                                        </td>
                                    {else}
                                        <td style="text-align: center">{$MODSTRING[$alert.alert]}</td>
                                    {/if}
                                    {if $alert.source_alert == 'Indicators'}
                                        <td style="text-align: center">{$alert.box_score}</td>
                                    {elseif $alert.source_alert == 'Task_prog' || $alert.source_alert == 'Task_no_ejec'}
                                        <td style="text-align: center">Calendario</td>
                                    {else}
                                        <td style="text-align: center">{$alert.tab_label}</td>
                                    {/if}
                                    <td style="text-align: center">{$MODSTRING[$alert.source_alert]}</td>
                                    <!--
                                    {if $alert.source_alert == 'Indicators' && $alert.automatic == '1'}
                                        <td style="text-align: center;"> -</td>
                                    {else}
                                        <td style="text-align: center;"> {$alert.field_name}
                                            &nbsp;{$MODSTRING[$LABEL_OPERATOR[$alert.condition_alert]]}
                                            &nbsp;{$alert.value_alert} </td>
                                    {/if}
                                    -->
                                    <td style="text-align: center;">{$numAlert}</td>
                                    <td style="text-align: right;padding-right: 10px">
                                        {if $alert.source_alert == 'Indicators'}
                                            {assign var='indicatorId' value=$alert.indicator_id}
                                        {else}
                                            {assign var='indicatorId' value=''}
                                        {/if}
                                        <div class="btn-group" style="margin-right: 35px">
                                            {if $numAlert > 0} <!--   listview-controller -->
                                                <a href="viewIndicators" data-toggle="modal" class="btn btn-sm btn-warning"
                                                   title="Ver detalles de la alerta"
                                                   onclick="SystemAlertUtils.detailViewAlert('{$alert.systemalerts_id}', '{$VIEW_SEARCH}', '{$keyApp}', '{$alert.source_alert}');">
                                                    <i class="fa fa-eye"></i></a>
                                            {/if}
                                            {if $alert.automatic != '1'}
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    data-record=""
                                                    onclick="SystemAlertUtils.openModalAlert('edit', '{$alert.source_alert}', '{$alert.systemalerts_id}', '{$idAlertListView}')"
                                                    title="Editar alerta">
                                                 <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </button>
                                            {/if}
                                            <button type="button" class="btn btn-sm btn-default"
                                                    title="{if $alert.status}Desactivar{else}Activar{/if} alerta"
                                                    data-status="{$alert.status}"
                                                    onclick="SystemAlertUtils.changeStatus(this, '{$alert.systemalerts_id}')">
                                                {if $alert.status}
                                                    <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                {else}
                                                    <i class="fa fa-square-o" aria-hidden="true"></i>
                                                {/if}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-sm btn-danger"
                                                    data-record=""
                                                    onclick="SystemAlertUtils.deleteAlert('{$alert.systemalerts_id}','{$alert.source_alert}')"
                                                    title="{$MODSTRING.LBL_DELETE_ALERT}">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </div>

                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            {/foreach}
        {else}
            <div class="main-box-body clearfix" style="margin-top: 13px;">
                <div class="alert alert-info">
                    <strong></strong>
                    <p style="text-align: center">{$MODSTRING['NO_ALERTS_FOUND']}</p>
                </div>
            </div>
        {/if}
    </div>
    <script>
        {literal}
        jQuery(document).ready(function () {
            jQuery('#date_from').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
            jQuery('#date_to').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
        });
        {/literal}
    </script>
{/strip}