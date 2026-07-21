{block name="css"}
    {math equation= rand() assign= "idGanttDiagram"}
    <style>
     {literal}

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack:    center !important;
            justify-content:  center !important
        }

        .calculation-data {
            margin: 4px 2px;
        }

        .calculation-data p {
            text-align: center;
            padding: 4px 0;
        }

        .flex-container {
            display: flex;
            align-items: stretch;
            flex-direction: row;
            flex-wrap: nowrap;
        }

        .flex-container > div {
            margin: 0 0.15em 0 0.15em;
            text-align:left;
        }
     {/literal}
    </style>
{/block}
{block name="first-content"}
    {math equation='x - y' x=12 y=$STATUS_TOTAL_BUTTONS assign='col'}
    <div class="container-fluid base-list-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix" {if $TAB_HOME_ID neq NULL}style="margin-top: 1px!important;"{/if}>
                    <div class="main-box-header clearfix">
                        <div class="row" style="padding-top: 0!important;margin-top: -5px!important;">
                            {if $TAB_HOME_ID neq NULL}
                            {* Home Buttons group *}
                            <div class="col-lg-12 col-md-12 col-xs-12">
                                <div class="btn-group pull-left">
                                    {* LIST-VIEW-GRAPHIC *}
                                    {*if $STATUS_BUTTONS['graphic']*}
                                    <a data-toggle="tab" href="#VIEW-TASK-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
                                       title="Tareas"
                                       {if $TAB_GROUP neq 'ACTIVITY'}
                                       onclick="HomeUtils.activeTaskTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-TASK','{$TAB_GROUP}')"
                                       {/if}
                                       data-toggle="tab"><i class="fa fa-check-square" aria-hidden="true"></i></a>
                                    {*/if*}
                                    {* LIST-VIEW-KANBAN-VIEW *}
                                    {if $TAB_GROUP eq 'record'}
                                    <a data-toggle="tab" href="#ListViewHomeContents-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
                                       title="Listado de registros"
                                       data-toggle="tab"><i class="fa fa-list-ul"></i></a>
                                    {/if}
                                    {*if $STATUS_BUTTONS['kanban']*}
                                    <button type="button" class="btn btn-primary" style=" font-size: 15px!important;"
                                            title="Vista kanban"><i class="fa fa-trello" aria-hidden="true"></i></button>
                                    {*/if*}
                                    {* LIST-VIEW-CALENDAR *}
                                    {*if $STATUS_BUTTONS['calendar']*}
                                    <a data-toggle="tab" href="#VIEW-CALENDAR-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
                                       title="vista calendario"
                                       onclick="HomeUtils.activeTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-CALENDAR','{$TAB_GROUP}')"
                                       data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                    {*/if*}
                                    {* Kanban-task *}
                                    <div class="input-group" style="margin-left: 1px">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="height: 34px;">
                                                <i class="fa fa-filter">&nbsp;</i></i><span class="caret"></span></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li>
                                                    <a href="index.php?module=Settings&action=KanbanViewEditView&return_module={$MODULE}&parenttab=Settings">{$APP.LNK_KANBAN_CREATEVIEW}</a>
                                                </li>
                                                {if $CV_EDIT_PERMIT eq 'yes'}
                                                    <li>
                                                        <a href="index.php?module=Settings&action=KanbanViewEditView&record={$KANBAN_VIEW}&return_module={$MODULE}&parenttab=Settings">{$APP.LNK_CV_EDIT}</a>
                                                    </li>
                                                {/if}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {* /Home Buttons group *}
                            {else}
                            <div class="form-group col-md-6 list-view-filter"
                                 style="{if isset($IS_HOME_TAB)}display: none; {/if}margin-bottom: 0;">
                                <div class="btn-group btn-control pull-left" style="margin-left: 10px">
                                    {* LIST-VIEW*}
                                    <a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
                                       style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                       onclick="ListViewTabUtils.activeListTab(event)"
                                       data-toggle="tab" title="Listado de registros"><i
                                                class="fa fa-list-ul"></i></a>
                                    {* LIST-VIEW-KANBAN-VIEW *}
                                    {if $STATUS_BUTTONS['kanban']}
                                        <a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                           title="Vista kanban de registos"
                                           onclick="ListViewTabUtils.activeKanbanTab (event)"
                                           data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                    {/if}
                                    {* LIST-VIEW-BOX-SCORE *}
                                    {if $STATUS_BUTTONS['boxscore'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                           title="Indicadores de gestión"
                                           onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                           data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                    {/if}
                                    {* LIST-VIEW-GRAPHIC *}
                                    {if $STATUS_BUTTONS['graphic'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.3em; margin-right:0.05em;margin-left:0.05em;"
                                           TITLE="Graficos"
                                           onclick="ListViewTabUtils.activeGraphicTab (event)"
                                           data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                    {/if}
                                    {* report *}
                                    {if $STATUS_BUTTONS['report'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.3em; margin-right:0.05em;margin-left:0.05em;"
                                           title="Informes"
                                           onclick="ListViewTabUtils.activeReportTab (event)"
                                           data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
                                    {/if}
                                    {* LIST-VIEW-CALENDAR *}
                                    {if $STATUS_BUTTONS['calendar']}
                                        <a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
                                           title="Vista calendario"
                                           onclick="ListViewTabUtils.activeCalendarTab (event)"
                                           data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                    {/if}
                                    <button type="button" class="btn btn-primary"
                                            title="Vista kanban de tareas"
                                            style=" font-size: 15px!important;"><i class="bi bi-kanban-fill"></i>
                                    </button>
                                    <div class="input-group">
                                        <div class="input-group col-md-12 col-sm-12 col-xs-12" style="margin-left: 2px">
                                            <div class="input-group-addon">
                                                <i class="fa  fa-clock-o"></i>
                                            </div>
                                            <select id="period-dates-{$idGanttDiagram}"
                                                    name="periodtask"
                                                    onchange="ListViewTabUtils.searchKanbanTask(this, '{$idGanttDiagram}')"
                                                    class="form-control" title="Seleccionar periodo">
                                                {if $PERIOD_DATES neq NULL}
                                                    <option value="" >Seleccionar periodo</option>
                                                    {foreach $PERIOD_DATES as $period => $perioName}
                                                        {if $period eq 'custom'}{continue}{/if}
                                                        <option value="{$period}" {if $period eq $PERIOD_SELECTED}selected{/if} >
                                                            {$perioName}
                                                        </option>
                                                    {/foreach}
                                                {/if}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {* combobox *}
                            </div>
                            <div class="col-md-3" id="KANBAN-LOADING-{$idGanttDiagram}" style="padding-right: 0">&nbsp;</div>
                            <div class="col-md-3" style="padding-right: 0">
                                <div style="display: inline-block; float: right; padding-right: 5px"><h4 style="font-weight: bold; color: #cccccc">Vista kanban de tareas</h4></div>
                            </div>
                            {/if}
                        </div>
                         <div style="margin-left: -2px!important;margin-top: 30px">
                            <div class="justify-content-center"  id="myKanbanTask-{$idGanttDiagram}">
                                {if $KANBAN_BLOCKS neq NULL}
                                    {include file="KanbanDiagram.tpl"}
                                {else}
                                    <div class="alert alert-info">No hay en el periodo tareas!</div>
                                {/if}
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}
{block name="js"}{/block}