{strip}
    {assign var='idActivity' value=$TAB_HOME_ID}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <link rel="stylesheet" type="text/css" href="modules/Home/daily_matrix.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
    <div class="main-box-header clearfix" style="padding: 0 1.2em">
        <div class="row" style="margin-top: 1.5em">
            <form  role="form" id="daily-matrix-form-{$idActivity}">
                <input type="hidden" name="record" value="">
                <input type="hidden" name="module" value="Home">
                <input type="hidden" name="function" value="DAILY-MATRIX-SEARCH">
                <input type="hidden" name="action" value="AjaxHomeUtils">
                <input type="hidden" name="Ajax" value="true">
                <input type="hidden" name="hometabid" value="{$TAB_HOME_ID}">
                <input type="hidden" id="reported_day-{$idActivity}" value="{$REPORTED_DAYS}">
            <div class="col-md-12 col-sm-12 col-xs-12" {if $TAB_HOME_ID neq NULL}style="margin-top: 0"{/if}>
                {if $TAB_HOME_ID neq NULL} {/if}
                <div id="col-lg-3 col-md-3 col-xs-3 btn-toolbar-{$idActivity}" class="btn-toolbar" role="toolbar">
                    {if $AVAILABLE_USERS neq NULL}
                        <div class="btn-group" style="margin-left: 0.125em!important;margin-right: 2px">
                            <button id="btn-group-user-{$idActivity}" type="button"
                                    class="btn btn-primary dropdown-toggle"
                                    title="asignar tarea"
                                    style="font-size: 15px!important;margin-left: 0.1em"
                                    data-toggle="dropdown">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                &nbsp;Filtrar por usuario&nbsp;
                                <span class="caret"></span>
                            </button>
                            <ul id="daily-matrix-user-{$idActivity}" class="dropdown-menu scroll-user-menu"
                                role="menu">
                                {if $AVAILABLE_USERS|count gt 1}
                                    {foreach $AVAILABLE_USERS as $id => $user}
                                        <li {if (in_array($id, $USERS))}class="active" {/if}>
                                            <a href="#" title="{$user['name']}" rel="{{$id}}"
                                               onclick="DailyMatrixUtls.selectedUser (event, this, '{$idActivity}')">
                                                <img class="img-circle" style="width: 60%; height: 60%"
                                                     data-src="{$user['avatar']}" alt="{$user['name']}"
                                                     src="{$user['avatar']}">
                                            </a>
                                        </li>
                                    {/foreach}
                                {else}
                                    <li class="list-btn-header" title="Usuarios invitados" style="text-align: center">
                                        <i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;
                                        <small style="text-align: center; padding-left: 2px">No hay usuarios!</small>
                                    </li>
                                {/if}
                            </ul><br>
                        </div>
                    {/if}
                    {* date period filters *}
                    <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-{$idActivity}" style="margin-bottom: 4px; margin-right: 0!important;">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa  fa-clock-o"></i>
                            </div>
                        <select id="period-dates-{$idActivity}"
                                onchange="DailyMatrixUtls.selectedPeriod (this, '{$idActivity}')"
                                name="periodtask"
                                class="form-control" title="Seleccionar periodo">
                            {if $PERIOD_DATES neq NULL}
                                <option value="" >Seleccionar periodo</option>
                                {foreach $PERIOD_DATES as $period => $perioName}
                                    <option value="{$period}" {if $period eq $PERIOD_SELECTED}selected{/if} >
                                        {$perioName}
                                    </option>
                                {/foreach}
                            {/if}
                        </select>
                        </div>
                    </div>
                    {* date  filters *}
                    <div class="btn-group col-lg-2 col-md-2 col-xs-2  hide"
                         style="margin-bottom: 4px; margin-left: 2px!important;">
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-calendar"></i></span>
                            <input id="start-date-{$idActivity}" type="text" name="datestart"
                                   readonly="readonly"
                                   class="form-control from-field daily-matrix-date-{$idActivity} date start-date "
                                   value=""
                                   style="margin: 0!important;" placeholder="Desde"/>
                        </div>
                    </div>
                    {* date  filters *}
                    <div class="btn-group  col-lg-2 col-md-2 col-xs-2  hide"
                         style="margin-bottom: 4px;margin-right: 0!important;">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input id="end-date-{$idActivity}" type="text" name="duedate"
                                   readonly="readonly"
                                   class="form-control daily-matrix-date-{$idActivity} date end-date"
                                   value=""
                                   style="margin: 0!important;" placeholder="Hasta"/>
                        </div>
                    </div>
                    <div class="pull-left" style="margin-left: 2px">
                        <button name="submitSearch" id="submitSearch" class="btn btn-primary"
                                title="Buscar tareas"
                                onclick="DailyMatrixUtls.searchTaskForMatrix(this, '{$idActivity}')" type="button">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                    {* action button*}
                    <div class="btn-group pull-right" style="margin-bottom: 2px">
                        <div id="date-group-{$idActivity}"  class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle"
                                    style="height: 34px;margin-right: 1px"
                                    data-toggle="dropdown">
                                <i class="fa fa-file-o" aria-hidden="true"></i> Crear informe diario <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu"">
                                <li><a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp={$REPORT_TODAY}"
                                       data-date="{$TODAY}"
                                       onclick="DailyMatrixUtls.goReportDate(this, '{$idActivity}', event)">
                                        Para hoy
                                    </a>
                                </li>
                                <li><a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp={$REPORT_YESTERDAY}"
                                       data-date="{$YESTERDAY}"
                                       onclick="DailyMatrixUtls.goReportDate(this, '{$idActivity}', event)">
                                        Para ayer
                                    </a>
                                </li>
                                <li><a id="other-date-{$idActivity}" href="#" rel="{$USER_ID}" data-date=""  onclick="DailyMatrixUtls.createReportDate(this, '{$idActivity}', event)">Otra fecha</a></li>
                                {*<li class="divider other-date hide"></li>*}
                                <li class="hide other-date">
                                    <input rel="{$USER_ID}" class="form-control pull-right input-readonly b-left col-md-3"
                                           placeholder="Seleccione fecha"
                                            onclick="DailyMatrixUtls.createReportDate(this, '{$idActivity}', event)"
                                           value=""
                                            type="text" id="report-date-{$idActivity}" readonly="readonly"></li>
                            </ul>
                        </div>
                       {* <button type="button"
                                style="height: 34px;"
                                onclick="CalendarWizard.open (null, null, null, 'index.php?module=Home&action=index&tab=DAILY_MATRIX', 'Activity');"
                                title="Crear tarea"
                                class="btn btn-info"><i class="fa fa-plus"></i></button> *}
                        <!-- WA 03-11-23 -->
                        <a href="index.php?module=daily_report&action=index" class="btn btn-primary"
                           style="height: 34px;margin-right: 1px"
                           title="Informe diario"><i class="fa fa-file-text-o" aria-hidden="true"></i></a>
                        <a href="index.php?module=Calendar&action=index&calendar_main=1" class="btn btn-warning"
                           style="height: 34px;"
                           title="Ir al calendario"><i class="fa fa-calendar"></i></a>
                        <a href="index.php?module=views_diagrams&action=index" class="btn btn btn-default"
                           style="height: 34px;"
                           title="Ir a la vista de tareas"><span class="glyphicon glyphicon-indent-left"></span></a>
                    </div>
                </div>
            </div>
            </form>
            <span id="help-user-{$idActivity}" class="help-block" style="color: red; display: inline-block!important;margin: 0 0.9em"><b>Usuario:</b>&nbsp;{$USER_NAME}</span>
        </div>
    </div>
    <div id="daily-matrix-quadrants-{$idActivity}" class="main-box-header clearfix" style="padding: 0 0.65em">
        {*$TOTALS_QUADRANTS|var_dump*}
        <div class="row" style="margin-top: 6px">
            <div class="col-xs-8">
                <p class="text-left" style="margin-left: 10px;margin-bottom: 1px"><small style="font-weight: bold">{$MOD.LBL_MATRIX_TITLE}</small></p>
                <ul id="rtl_func">
                    {foreach $QUADRANTS as $quadrant}
                        {assign var="quadrants" value=$quadrant|replace:'-':';'}
                        <li class="list_root col-xs-6"
                            id="f_{$quadrant}">
                            <div class="row">
                                <div class="col-md-10 col-xs-10 col-sm-10" style="text-align: center"><span>{$MOD[$quadrant]}</span></div>
                                <div class="col-md-2 col-xs-2 col-sm-2">
                                <span class="pull-right">
                                <button type="button"
                                        style="background-color: transparent!important;"
                                        onclick="CalendarWizard.open (null, null, null, 'index.php?module=Home&amp;action=index&amp;tab=DAILY_MATRIX', 'Activity;{$quadrants};');"
                                        title="Crear tarea:&nbsp;{$MOD[$quadrant]|replace:'-':'y'}" class="btn btn-circle btn-xs"><i class="fa fa-plus"></i></button></span>
                                </div>
                            </div>
                            <ul id="c_{$quadrant}" class="quadrant">
                                {foreach $TASKS_VIEW_DATA[$quadrant] as $data}
                                    {if $data['tab_name'] eq 'Calendar'}
                                        {assign var="crmId"  value=$data['crmid']}
                                    {else}
                                        {assign var="crmId"  value=$data['related_id']}
                                    {/if}
                                    <li class="list_child">
                                        {if ($data['tab_name'] neq 'Calendar') && ($data['tab_name'] neq 'Tarea') && ($data['related_id'] neq '0')}
                                            <a class="panel-title"
                                               href="index.php?module={$data['tab_name']}&parenttab=&action=DetailView&record={$data['related_id']}&tab=task-list"
                                               target="_blank"
                                               title="{$data.description}"
                                               style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">{$data.subject}</a>
                                        {else}
                                            <a class="panel-title"
                                               href="#"
                                               onclick="CalendarWizard.open ('{$data['tab_name']}', '{$crmId}', '{$data['modulename']}', 'index.php?module=Home&action=index&tab=DAILY_MATRIX', '{$data['parameters']}');"
                                               title="{$data.description}"
                                               style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">{$data.subject}</a>

                                        {/if}
                                        <!--
                                        <a class="panel-title"
                                               href="#"
                                               onclick="alert('Actividad no vinculada')"
                                               title="{$data.description}"
                                               style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">{$data.subject}</a>
                                         -->
                                    </li>
                                {/foreach}
                            </ul>
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div class="col-xs-4" style="height: 100%; vertical-align: bottom;text-align: center;margin-left: 0!important;">
                <div class="row">
                    {if $PROGRESS_BAR_OVER}
                        <style>
                            progress::-webkit-progress-bar {
                                background-color: red;
                                /*border: 0;
                                height: 6px;
                                border-radius: 9px;*/
                            }
                        </style>
                    {/if}
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <small style="font-weight: bold">Tiempo planificado y usado (%)
                            {if $PROGRESS_BAR_OVER}<br><span style="color: red">La ejecución ha excedido el tiempo planificado en&nbsp;{$OVER_TIME}%</span>{/if}
                        </small>
                        <div>
                        <progress id="file" max="{$PROGRESS_BAR_MAX}" value="{$PROGRESS_BAR_WIDTH}" style="width: 98%;{if $PROGRESS_BAR_OVER}background-color: red{/if}"> {$PROGRESS_BAR_WIDTH}% </progress>
                        </div>
                        <div class="text-left">
                            <small>Horas laborables totales para el período:&nbsp;{$WORKED_HOURS}</small><br>
                            <small>Horas reportadas como trabajadas:&nbsp;{$REPORTED_HOURS}</small><br>
                            <small>Horas extras:&nbsp;{$EXTRA_HOURS}</small>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <small style="font-weight: bold" id="piechart_3d_title"></small>
                        <div class="center-block" id="piechart_3d" style="border: 1px solid #ccc;padding-left: 30%"></div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12"style="margin-top: 2px">
                        <small style="font-weight: bold" id="piechart_3d_estimated_title"></small>
                        <div id="piechart_3d_estimated" class="center-block" style="border: 1px solid #ccc;width:100%;padding-left: 30%"></div>
                    </div>
                </div>
            </div>
        </div>
        {* Aditional Information *}
        {if $ADITIONAL_INFO neq NULL || $ACHIEVEMENTS neq NULL}
            {assign var="isCollapsed" value=null}
            <div class="row"  style="margin-top: 6px">
                <div class="col-lg-12 col-md-12 col-ms-12">
                    {if $ADITIONAL_INFO neq NULL}
                    <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist" style="margin-bottom: 1px!important;">
                        {foreach $ADITIONAL_INFO as $rowTitle => $rows}
                            {math equation= rand() assign= "idPanel"}
                            <div class="panel panel-default">
                                <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                                    <h4 class="panel-title" style="text-decoration: none!important;">
                                        <a style="text-decoration: none!important; text-underline: none"
                                           aria-controls="panel-info-{$idPanel}"
                                           aria-expanded="true"
                                           data-parent="#accordion"
                                           {if $isCollapsed neq NULL}
                                           class="collapsed"
                                           {/if}
                                           data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">{$rowTitle}&nbsp; del período
                                        </a>
                                    </h4>
                                </div>
                                <div aria-labelledby="heading1" class="panel-collapse collapse {if $isCollapsed eq NULL}in {/if}" id="panel-info-{$idPanel}" role="tabpanel">
                                    <div class="panel-body" style="padding-top: 1px!important;">
                                        <ul class="list-group" style="margin-bottom: 0!important;">
                                            {foreach $rows as $row}
                                                <li class="list-group-item"><span style="font-weight: bold">{$row['title']}:&nbsp;</span>{$row['description']}</li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {assign var="isCollapsed" value="collapsed"}
                        {/foreach}
                    </div>
                    {/if}
                    {if $ACHIEVEMENTS neq NULL}
                        <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
                                {math equation= rand() assign= "idPanel"}
                                <div class="panel panel-default">
                                    <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                                        <h4 class="panel-title" style="text-decoration: none!important;">
                                            <a style="text-decoration: none!important; text-underline: none"
                                               aria-controls="panel-info-{$idPanel}"
                                               aria-expanded="true"
                                               data-parent="#accordion"
                                               class="collapsed"
                                               data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">Logros del día
                                            </a>
                                        </h4>
                                    </div>
                                    <div aria-labelledby="heading1" class="panel-collapse collapse" id="panel-info-{$idPanel}" role="tabpanel">
                                        <div class="panel-body" style="padding-top: 1px!important;">
                                            <ul class="list-group">
                                                {foreach $ACHIEVEMENTS as $achievement}
                                                    <li class="list-group-item"><span style="font-weight: bold">{$achievement['title']}:&nbsp;</span>{$achievement['description']}</li>
                                                {/foreach}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>

    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <script type="text/javascript" src="modules/Home/daily-matriz-utils.js"></script>

    <script type="text/javascript">
        DailyMatrixUtls.initTask('{$idActivity}', {$TOTALS_QUADRANTS[0]}, {$TOTALS_QUADRANTS[1]}, {$TOTALS_QUADRANTS[2]}, {$TOTALS_QUADRANTS[3]}, {$TOTALS_QUADRANTS[4]});
        DailyMatrixUtls.initEstimated({$TOTALS_ESTIMATED[0]}, {$TOTALS_ESTIMATED[1]}, {$TOTALS_ESTIMATED[2]}, {$TOTALS_ESTIMATED[3]}, {$TOTALS_ESTIMATED[4]}, {$TOTAL_TIMES});

    </script>
{/strip}