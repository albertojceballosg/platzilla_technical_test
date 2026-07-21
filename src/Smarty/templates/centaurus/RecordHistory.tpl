{extends file="platzilla_layout.tpl"}

{block name="action_css"}
    <link rel="stylesheet" href="themes/centaurus/css/libs/morris.css" type="text/css"/>
    <link rel="stylesheet" href="modules/historymanager/recordhistory.css" type="text/css"/>
    <link rel="stylesheet" href="modules/historymanager/timeline.css" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/platzilla-detailview.css"/>
{/block}

{block name="action_js"}
    <script type="text/javascript" src="themes/{$THEME}/js/jquery.knob.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.pie.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.stack.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.resize.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.time.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.orderBars.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/flot/jquery.flot.funnel.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/jquery-ui.custom.min.js"></script>
    <script type="text/javascript" src="include/js/highcharts/js/highcharts.js"></script>
    <script type="text/javascript" src="include/js/highcharts/js/modules/funnel.js"></script>
    <script type="text/javascript" src="include/js/highcharts/js/modules/exporting.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/raphael-min.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/morris.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
{/block}

{block name="action_content"}
{block name="messages"}{/block}
{include file='Buttons_List.tpl'}
{if $OP_MODE eq 'edit_view'}
    {assign var="action" value="EditView"}
{else}
    {assign var="action" value="DetailView"}
{/if}
{assign var="newLine" value=""}
{assign var='fieldToGraphic' value=','|explode:"7,9"}
<div id="editlistprice" style="position: absolute; width: 300px;"></div>
<div class="container-fluid" {if !$IS_MODAL}
     style="background-color: #ffffff; margin: 2px 0px -13px!important;border-top: 1px solid #D8D8D8 !important;">
    {else}
    style="background-color: #ffffff; margin: 4px -13px!important;border-top: 1px solid #D8D8D8 !important;">
    {/if}
    <div class="tabs-wrapper row">
        <div class="col-md-12">
            <ul class="nav nav-tabs nav-platzilla-history">
                <li class="active"><a href="#period" data-toggle="tab"
                                      onclick="HistoryUtils.setTab('period')">{$MOD.LBL_STEP_3_TITLE}</a>
                </li>
                <li class=""><a href="#advanced" data-toggle="tab"
                                onclick="HistoryUtils.setTab('advanced')">{$MOD.LBL_STEP_4_TITLE}</a>
                </li>
            </ul>
        </div>
        <div id="history-body" class="col-md-12" style="margin-top: 4px">
            <div class="row block-container">
                <div class="col-xs-12">
                    <div class="main-box" style="border-top: 1px solid #FFFFFF !important; height: 100% !important;">
                        <div class="main-box-body clearfix">
                            <div class="row">
                                <div id="history-search" class="col-md-12">
                                    <div class="tabs-wrapper">
                                        <form name="history-search-form" role="form">
                                            <input type="hidden" name="record" id="record" value="{$ID}">
                                            <input type="hidden" name="formodule" id="formodule" value="{$MODULE}">
                                            <input type="hidden" name="activetab" id="activetab" value="history-data">
                                            {* tabs *}
                                            <div class="tab-content">
                                                <div class="tab-pane fade active in" id="period"
                                                     style="padding: 12px 2px 0 2px; margin:0 24px">
                                                    <div class="form-inline row-history">
                                                        <div class="form-group" style="margin-right: 8px">
                                                            <div class="input-group">
                                                                <div class="input-group-addon">
                                                                    <i class="fa  fa-clock-o"></i>
                                                                </div>
                                                                <select class="form-control col-md-4" id="historyPeriod"
                                                                        name="historyPeriod" title="Buscar por tiempo"
                                                                        onchange="HistoryUtils.searchHistoryByTime(this) ">
                                                                    <option value=""{if $SEARCH_FORM eq ""} selected{/if}>{$MOD.OPT_CUSTOM}</option>
                                                                    <option value="{$HISTORY_TODAY}"{if $SEARCH_FORM eq $HISTORY_TODAY} selected{/if}>{$MOD.OPT_TODAY}</option>
                                                                    <option value="{$HISTORY_WEEK}"{if $SEARCH_FORM eq $HISTORY_WEEK} selected{/if}>{$MOD.OPT_LAST_WEEK}</option>
                                                                    <option value="{$HISTORY_MONTH}"{if $SEARCH_FORM eq $HISTORY_MONTH} selected{/if}>{$MOD.OPT_LAST_MONTH}</option>
                                                                    <option value="{$HISTORY_TH_MONTH}"{if $SEARCH_FORM eq $HISTORY_TH_MONTH} selected{/if}>{$MOD.OPT_LAST_THREE_MONTH}</option>
                                                                    <option value="{$HISTORY_SIX_MONTH}"{if $SEARCH_FORM eq $HISTORY_SIX_MONTH} selected{/if}>{$MOD.OPT_LAST_SIX_MONTH}</option>
                                                                    <option value="{$HISTORY_YEAR}"{if $SEARCH_FORM eq $HISTORY_YEAR} selected{/if}>{$MOD.OPT_LAST_YEAR}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-2" style="margin-left: 0">
                                                            <div class="input-group pull-right">
                                                                <div class="input-group-addon"
                                                                     style="border: 1px solid #ddd !important">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text"
                                                                       class="form-control pull-right input-readonly b-left col-md-12"
                                                                       id="historyDatefrom" name="historyDateFrom"
                                                                       readonly="readonly"
                                                                       value="{if $SEARCH_FORM neq ""}{$HISTORY_DATE_FROM}{/if}"
                                                                       placeholder="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-2" style="margin-right: 4px">
                                                            <div class="input-group">
                                                                <div class="input-group-addon"
                                                                     style="border: 1px solid #ddd !important">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text"
                                                                       class="form-control pull-right input-readonly b-left col-md-12"
                                                                       id="historyDateTo" name="historyDateTo"
                                                                       readonly="readonly"
                                                                       value="{if $SEARCH_FORM neq ""}{$HISTORY_DATE_TO}{/if}"
                                                                       placeholder="">
                                                            </div>
                                                        </div>
                                                        <button name="submitSearch" id="historySubmitSearch"
                                                                class="btn btn-primary btn-sm" style="margin-left: 0"
                                                                type="button"
                                                                onclick="HistoryUtils.getHistoricalData()"><i
                                                                    class="fa fa-search" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="advanced"
                                                     style="padding: 12px 2px 0 2px; margin-bottom: 0">
                                                    <div class="justify-content-center condition-groups">
                                                        <div class="action-bar text-center">
                                                            <button id="advanced-add-group" type="button"
                                                                    class="btn btn-success btn-sm" data-group="0"
                                                                    onclick="HistoryUtils.addFilterGroup (this);"
                                                                    title="Agregar grupo de condiciones">
                                                                <i class="fa fa-plus"></i></button>
                                                            <button name="submitSearch" id="advancedSubmitSearch"
                                                                    class="btn btn-primary btn-sm hide" type="button"
                                                                    onclick="HistoryUtils.getHistoricalData()"><i
                                                                        class="fa fa-search" aria-hidden="true"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 0;padding-top: 0">
                                <div class="col-md-12" style="margin-top: 0;padding-top: 0">
                                    <div class="tabs-wrapper" style="margin-top: 0;padding-top: 0">
                                        <p style="0 20px;padding: 0 20px">
                                            <a class="btn btn-default btn-xs" role="button" href="#history-data"
                                               data-toggle="tab"
                                               title="{$MOD.LBL_STEP_7_TITLE}"
                                               onclick="HistoryUtils.activateTab('history-data', 'history-graphic', 'history-events')">
                                                <i class="fa fa-th-list"></i></a>
                                            {if $HAS_NUM_FIELD eq 'YES'}
                                                <a class="btn btn-default btn-xs" role="button" href="#history-graphic"
                                                   data-toggle="tab" title="{$MOD.LBL_STEP_8_TITLE}"
                                                   onclick="HistoryUtils.activateTab('history-graphic', 'history-data', 'history-events')">
                                                    <i class="fa fa-signal" aria-hidden="true"></i></a>
                                            {/if}
                                            <a class="btn btn-default btn-xs" role="button" href="#history-events"
                                               data-toggle="tab" title="{$MOD.LBL_STEP_9_TITLE}"
                                               onclick="HistoryUtils.activateTab('history-events','history-graphic', 'history-data')">
                                                <i class="fa fa-calendar" aria-hidden="true"></i></a></p>
                                        <div class="tab-content">
                                            <div class="tab-pane fade active in" id="history-data"
                                                 style="padding: 2px 2px">
                                                <div class="panel-group accordion" id="RHContents">
                                                    <div id="historical-container" class="history-container">
                                                        <table class="table table-striped table-hover history-record">
                                                            <tbody>
                                                            {if $HISTORICALRECORDS eq null}
                                                                <div class="alert alert-info alert-dismissible"
                                                                     role="alert">
                                                                    <button type="button" class="close"
                                                                            data-dismiss="alert"
                                                                            aria-label="Close"><strong><span
                                                                                    aria-hidden="true">&times;</strong>
                                                                    </button>
                                                                    <i class="fa fa-info-circle fa-lg"
                                                                       aria-hidden="true"></i>&nbsp;
                                                                    {$MOD.LBL_NOT_HISTORICAL}
                                                                </div>
                                                            {else}
                                                                {foreach $HISTORICALRECORDS as $record}
                                                                    {if $record.fieldlabel neq 'Last Modified By'}
                                                                        {assign var="newLine" value=""}
                                                                        <tr>
                                                                            <td class="text-center">
                                                                                {if $record.uitype eq "1" || $record.uitype eq "2" || $record.uitype eq "7" ||
                                                                                $record.uitype eq "9" || $record.uitype eq "17" || $record.uitype eq "57"}
                                                                                    <i class="fa fa-comment"></i>
                                                                                {elseif $record.uitype eq "3" || $record.uitype eq "4"}
                                                                                    <i class="fa fa-asterisk"></i>
                                                                                {elseif $record.uitype eq "5" || $record.uitype eq "23" || $record.uitype eq "70"}
                                                                                    <i class="fa fa-calendar"></i>
                                                                                {elseif $record.uitype eq "6"}
                                                                                    <i class="fa fa-calendar-times-o"></i>
                                                                                {elseif $record.uitype eq "8"}
                                                                                    <i class="fa fa-clone"></i>
                                                                                {elseif $record.uitype eq "10"}
                                                                                    <i class="fa fa-book"></i>
                                                                                {elseif $record.uitype eq "11"}
                                                                                    <i class="fa fa-phone-square"></i>
                                                                                {elseif $record.uitype eq "12" || $record.uitype eq "13" || $record.uitype eq "25"}
                                                                                    <i class="fa fa-envelope"></i>
                                                                                {elseif $record.uitype eq "15" || $record.uitype eq "16" || $record.uitype eq "52" || $record.uitype eq "53"}
                                                                                    <i class="fa fa-angle-double-down"></i>
                                                                                {elseif $record.uitype eq "19" || $record.uitype eq "20" || $record.uitype eq "21" ||
                                                                                $record.uitype eq "22" || $record.uitype eq "24" || $record.uitype eq "33"}
                                                                                    <i class="fa fa-align-justify"></i>
                                                                                    {assign var="newLine" value="<br />"}
                                                                                {elseif $record.uitype eq "26"}
                                                                                    <i class="fa fa-folder"></i>
                                                                                {elseif $record.uitype eq "27"}
                                                                                    <i class="fa fa-file-archive-o"></i>
                                                                                {elseif $record.uitype eq "28"}
                                                                                    <i class="fa fa-file-code-o"></i>
                                                                                {elseif $record.uitype eq "30"}
                                                                                    <i class="fa fa-caret-square-o-down"></i>
                                                                                {elseif $record.uitype eq "51"}
                                                                                    <i class="fa fa-window-restore"></i>
                                                                                {elseif $record.uitype eq "55" || $record.uitype eq "255"}
                                                                                    <i class="fa fa-address-card"></i>
                                                                                {elseif $record.uitype eq "56"}
                                                                                    <i class="fa fa-check-square"></i>
                                                                                {elseif $record.uitype eq "5010"}
                                                                                    <i class="fa fa-tasks"></i>
                                                                                {/if}
                                                                            </td>
                                                                            <td>
                                                                                {if $record.modifiedon neq 0}
                                                                                    <a>{$record.first_name}
                                                                                        &nbsp;{$record.last_name}</a>
                                                                                    &nbsp;{$MOD.LBL_ACTION_MODIFY}
                                                                                    &nbsp;
                                                                                    <b>{$record.fieldlabel|@getTranslatedString : $MODULE}</b>
                                                                                    &nbsp;{$newLine}{$MOD.LBL_STEP_5_TITLE}
                                                                                    &nbsp;
                                                                                    <span class="text-primary"
                                                                                          title="{$record.oldvalue}"> {$record.oldvalue}</span>
                                                                                    &nbsp;{$newLine}{$MOD.LBL_STEP_6_TITLE}
                                                                                    &nbsp;
                                                                                    <span class="text-success"
                                                                                          title="{$record.newvalue}">{$record.newvalue}</span>
                                                                                {else}
                                                                                    <a>{$record.first_name}
                                                                                        &nbsp;{$record.last_name}</a>
                                                                                    &nbsp;{$newLine}{$MOD.LBL_ACTION_NEW}
                                                                                    &nbsp;
                                                                                    <b>{$record.fieldlabel|@getTranslatedString : $MODULE}</b>
                                                                                    &nbsp;{$newLine}{$MOD.LBL_VALUE}
                                                                                    <span class="text-success"
                                                                                          title="{$record.newvalue}">{$record.newvalue}</span>
                                                                                {/if}
                                                                            </td>
                                                                            <td>
                                                                                <i class="fa fa-clock-o"></i>&nbsp;
                                                                                {$record.date|date_format:"%d/%m/%Y %H:%M"}
                                                                            </td>
                                                                        </tr>
                                                                    {/if}
                                                                {/foreach}
                                                            {/if}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="history-graphic" style="padding: 2px 0;">
                                                <form id="" name="history-graphic-form" role="form">
                                                    <div class="form-inline row-history" style="margin: 4px 0">
                                                        <div class="col-md-11" style="padding: 0 !important;">
                                                            <div id="hg-dv-historyField" class="form-group  col-md-3">
                                                                <select class="form-control" id="historyField"
                                                                        name="historyField"
                                                                        title="Campos a graficar" style="width: 100%">
                                                                    <option value="">{$MOD.OPT_FIELDS}</option>
                                                                    {if (isset ($FIELD_LIST)) && (!empty ($FIELD_LIST))}
                                                                        {foreach $FIELD_LIST as $field}
                                                                            {if ($field.typeofdata neq '') && ($field.uitype|in_array:$fieldToGraphic)}
                                                                                <option value="{$field.fieldname}">{$field.label}</option>
                                                                            {/if}
                                                                        {/foreach}
                                                                    {else}
                                                                        <option value="">{$MOD.OPT_ELSE_FIELDS}</option>
                                                                    {/if}
                                                                </select>
                                                                <span id="hg-historyField" class="help-block"></span>
                                                            </div>
                                                            <div id="hg-dv-typeGraphic" class="form-group col-md-2">
                                                                <select name="typeGraphic" id="typeGraphic"
                                                                        class="form-control"
                                                                        title="Tipo de Gráfico" style="width: 100%">
                                                                    <option value="">Tipo de gráfico</option>
                                                                    {foreach $AVAILABLE_TYPES as $typeId => $typeName}
                                                                        <option value="{$typeId}"{if ($graphType == $typeId)} selected="selected"{/if}>{$typeName}</option>
                                                                    {/foreach}
                                                                </select>
                                                                <span id="hg-typeGraphic" class="help-block"></span>
                                                            </div>
                                                            <div class="form-group col-md-1"
                                                                 style="margin-left: 0 !important;">
                                                                <button name="submitGraphic" id="submitGraphic"
                                                                        class="btn btn-primary btn-sm pull-left"
                                                                        type="button"
                                                                        style="margin-left: 0"
                                                                        onclick="HistoryUtils.loadBasicGraph ()">{$MOD.LBL_STEP_8_TITLE}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                                <div class="col-md-12" style="margin-top: 12px">
                                                    <div class="main-box">
                                                        <div id="main-graphic-div" class="main-box-body clearfix"
                                                             style="height:580px; ">
                                                            <div id="graphic-view-div" class="graph simple">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="history-events" style="padding: 2px 2px">
                                                {* Histórico de eventos relacionados*}
                                                <div class="row">
                                                    <div id="history-timeline" class="col-md-12" style="top:-20px">
                                                        {if (isset ($RELHISTORY))}
                                                            <ul class="timeline">
                                                                {foreach $RELHISTORY as $relhistory}
                                                                    {if $relhistory.day neq $lastDay}
                                                                        {assign var='lastDay' value=$relhistory.day}
                                                                        {assign var='badge' value=true}
                                                                        {if $lastClass eq ''}
                                                                            {assign var='lastClass' value='timeline-inverted'}
                                                                        {else}
                                                                            {assign var='lastClass' value=''}
                                                                        {/if}
                                                                    {/if}
                                                                    <li class="{$lastClass}">
                                                                        {if $badge}
                                                                            {assign var='badge' value=false}
                                                                            <div class="timeline-badge">
                                                                                <span class="timeline-balloon-date-day">{$relhistory.day}</span>
                                                                                <span class="timeline-balloon-date-month">{$relhistory.month}</span>
                                                                            </div>
                                                                            <div><p><br></p></div>
                                                                        {/if}
                                                                        <div class="timeline-panel">
                                                                            <div class="timeline-heading">
                                                                                <h4 class="timeline-title">
                                                                                    <a {if $relhistory.type eq 'seactivityrel'}
                                                                                        href='index.php?action=DetailView&module=Calendar&record={$relhistory.record}&activity_mode=Events&parenttab='
                                                                                        title="Ver la tarea: {$relhistory.title}" target="_blank"
                                                                                            {elseif $relhistory.type eq 'crmentityrel'}
                                                                                        href='index.php?action=DetailView&module={$relhistory.module}&record={$relhistory.record}&tab=detail'
                                                                                        title="Ver el registro en: {$relhistory.module}" target="_blank"
                                                                                            {else}
                                                                                        title=" En este registro"
                                                                                        onclick="HistoryUtils.activateTab('history-data', 'history-graphic', 'history-events')"
                                                                                        style="cursor: pointer"
                                                                                            {/if}>
                                                                                        {if $relhistory.type eq 'seactivityrel'}{/if}
                                                                                        {$relhistory.modulelabel}
                                                                                        :&nbsp;{$relhistory.title}</a>
                                                                                    <small class="text-muted pull-right">
                                                                                        <i class="glyphicon glyphicon-time"></i>
                                                                                        {$relhistory.createdDay}
                                                                                    </small>
                                                                                </h4>
                                                                                <small class="text-muted"></small>
                                                                            </div>
                                                                            <div class="timeline-body">
                                                                                <a>{$relhistory.userName}</a>
                                                                                {if $relhistory.uitype eq 4}
                                                                                    {$MOD.LBL_ACTION_ASOCIAR}
                                                                                    <span class="text-success"
                                                                                          title="{$relhistory.newvalue}"> {$relhistory.newvalue}</span>
                                                                                {elseif $relhistory.oldvalue eq NULL}
                                                                                    {$MOD.LBL_ACTION_ASIGNAR}&nbsp;
                                                                                    <span class="text-success"
                                                                                          title="{$relhistory.newvalue}">{$relhistory.newvalue}</span>
                                                                                    &nbsp;{$MOD.LBL_TO_FIELD}&nbsp;
                                                                                    <b>{$relhistory.field}</b>
                                                                                {else}
                                                                                    &nbsp;{$MOD.LBL_ACTION_MODIFY}&nbsp;
                                                                                    <b>{$relhistory.field}</b>
                                                                                    &nbsp;{$newLine}{$MOD.LBL_STEP_5_TITLE}
                                                                                    &nbsp;
                                                                                    <span class="text-primary"
                                                                                          title="{$relhistory.oldvalue}"> {$relhistory.oldvalue}</span>
                                                                                    &nbsp;{$newLine}{$MOD.LBL_STEP_6_TITLE}
                                                                                    &nbsp;
                                                                                    <span class="text-success"
                                                                                          title="{$relhistory.newvalue}">{$relhistory.newvalue}</span>
                                                                                {/if}
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                {/foreach}
                                                            </ul>
                                                        {else}
                                                            <div class="alert alert-info alert-dismissible" role="alert"
                                                                 style="margin-top: 25px">
                                                                <button type="button" class="close" data-dismiss="alert"
                                                                        aria-label="Close"><strong><span
                                                                                aria-hidden="true">&times;</strong>
                                                                </button>
                                                                <i class="fa fa-info-circle fa-lg"
                                                                   aria-hidden="true"></i>&nbsp;
                                                                No se encontró información de eventos relacionados con
                                                                este
                                                                registro.
                                                            </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                                {* Histórico de eventos relacionados*}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {if !$IS_MODAL}
                            <div style="height: 750px"></div>
                        {/if}
                    </div>
                </div>
            </div>
            {if $EDIT_PERMISSION eq 'yes'}
                <div id="edita-record-history" style="display: none">
                    <form action="index.php" method="post" name="DetailView" id="form">
                        {include file='DetailViewHidden.tpl'}
                        {foreach key=header item=detail from=$BLOCKS}
                            {assign var="keyArray" value=key($detail[0])}
                            {assign var="uitype" value=$detail[0][$keyArray]['ui']}
                            {assign var="idField" value=$detail[0][$keyArray]['fldid']}
                            {assign var="fieldName" value=$detail[0][$keyArray]['fldname']}
                            {assign var="isEmpty" value=true}
                            {assign var=detailD value=$detail}
                            {if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
                                &nbsp;
                            {else}
                                {assign var=detailD value=$detail}
                                {foreach item=detail from=$detailD}
                                    {foreach key=label item=data from=$detail}
                                        {assign var=keycntimage value=$data.cntimage}
                                        {assign var=keyadmin value=$data.isadmin}

                                        {if $label ne ''}
                                            {if $keycntimage ne ''}
                                                <input type="hidden" id="hdtxt_IsAdmin"
                                                       value={$keyadmin} />{$keycntimage}
                                            {elseif $keyid eq '14'}
                                                <input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}/>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                {/foreach}
                            {/if}
                        {/foreach}
                    </form>
                </div>
            {/if}
        </div>
    </div>
</div>
{include file='CreateTaskWizard.tpl'}
<script type="text/html" id="condition-template">
    {include file="modules/historymanager/filterTemplate.tpl"}
</script>
<script type="text/html" id="condition-group-template">
    {include file="modules/historymanager/filterGroup.tpl"}
</script>
<script type="text/html" id="error-graphic-template">
    <div id class="alert alert-info text-center gh-alert"
         style="position: relative; transform: translate(0, -50%); top: 50%;">
        <div class="message" style="margin-bottom: 5px;">
            No hay data para graficar
        </div>
    </div>
</script>
<script type="text/html" id="graphic-view-div-template">
    <div id="graphic-view-div" class="">
    </div>
</script>
<script type="text/javascript" src="modules/historymanager/historymanager.js"></script>
<script type="text/html" id="instances-data-sharing-share-modal-template">
    {include file='modules/instancesdatasharing/ShareModal.tpl'}
{/block}
