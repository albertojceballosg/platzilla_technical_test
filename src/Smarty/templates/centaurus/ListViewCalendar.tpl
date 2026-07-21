{block name="css"}{/block}
{block name="first-content"}
    <style type="text/css">
        .flex-container {
            display: flex;
            align-items: stretch;
            flex-direction: row;
            flex-wrap: nowrap;
        }

        .flex-container > div {
            margin: 0 0.15em 0 0.15em;
            text-align: left;
        }
    </style>
    {math equation='x - y' x=12 y=$STATUS_TOTAL_BUTTONS assign='col'}
    {math equation= rand() assign= "idCalendar"}
    <div class="container-fluid base-list-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix" {if $TAB_HOME_ID neq NULL}style="margin-top: 1px!important;"{/if}>
                    <div class="main-box-header clearfix">
                        <div class="row">
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
                                    <a data-toggle="tab" href="#VIEW-KANBAN-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
                                       title="Vista kanban"
                                       onclick="HomeUtils.activeTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-KANBAN','{$TAB_GROUP}')"
                                       data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                    {*/if*}
                                    {* LIST-VIEW-CALENDAR *}
                                    {*if $STATUS_BUTTONS['calendar']*}
                                    <button type="button" class="btn btn-primary" style=" font-size: 15px!important;"
                                            title="vista calendario"><i class="fa fa-calendar"></i></button>
                                    {*/if*}
                                    <div class="input-group" style="margin-left: 1px">
                                    <select id="listview-calendar" class="form-control"
                                            title="Ver otros calendarios"
                                            style="{if isset($IS_HOME_TAB)}display: none{/if}"
                                            onchange="HomeUtils.searchCalendar(this, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-CALENDAR')">
                                        <option value="" disabled>Ver otros calendarios</option>
                                        {foreach $CALENDAR_VIEWS.records as $calendarView}
                                            {if $RELATED_VIEW neq NULL}
                                                {if !in_array($calendarView.calendarviewid, $RELATED_VIEW)}
                                                    {continue}
                                                {/if}
                                            {/if}
                                            <option value="{$calendarView.calendarviewid}" {if $calendarView.calendarviewid eq $VIEW_ID} selected="selected" {/if}
                                                    data-module="{$calendarView.modulename}">{$calendarView.label}</option>
                                        {/foreach}
                                    </select>
                                    </div>
                                </div>
                            </div>
                            {* /Home Buttons group *}
                            {else}
                            <div class="col-lg-6 col-md-6 col-xs-12 col-sm-12" style="padding-left: 0!important;">
                                <div class="btn-group pull-left">
                                    {* LIST-VIEW*}
                                    <a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
                                       style=" font-size: 15px!important;"
                                       onclick="ListViewTabUtils.activeListTab(event)"
                                       data-toggle="tab" title="Listado de registros"><i
                                                class="fa fa-list-ul"></i></a>
                                    {* LIST-VIEW-KANBAN-VIEW *}
                                    {if $STATUS_BUTTONS['kanban']}
                                        <a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           title="Vista kanban"
                                           onclick="ListViewTabUtils.activeKanbanTab (event)"
                                           data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                    {/if}
                                    {* LIST-VIEW-BOX-SCORE *}
                                    {if $STATUS_BUTTONS['boxscore'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                           data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                    {/if}
                                    {* LIST-VIEW-GRAPHIC *}
                                    {if $STATUS_BUTTONS['graphic'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           onclick="ListViewTabUtils.activeGraphicTab (event)"
                                           data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                    {/if}
                                    {* report *}
                                    {if $STATUS_BUTTONS['report'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           title="Informes"
                                           onclick="ListViewTabUtils.activeReportTab (event)"
                                           data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
                                    {/if}
                                    {* LIST-VIEW-CALENDAR *}
                                    {if $STATUS_BUTTONS['calendar']}
                                        <button type="button" class="btn btn-primary"
                                                title="Vista calendario"
                                                style=" font-size: 15px!important;"><i class="fa fa-calendar"></i>
                                        </button>
                                    {/if}
                                    {if $STATUS_BUTTONS['task']}
                                        <a data-toggle="tab" href="#LIST-VIEW-KANBAN-TASK-VIEW" class="btn btn-default" style=" font-size: 15px!important;"
                                           title="Vista kanban de tareas"
                                           onclick="ListViewTabUtils.activeKanbanTaskTab (event)"
                                           data-toggle="tab"><i class="bi bi-kanban-fill"></i></a>
                                    {/if}
                                    <div class="input-group">
                                    {if (isset ($CALENDAR_VIEWS)) && ($CALENDAR_VIEWS.totalRecords > 0)}
                                        <select id="listview-calendar" class="form-control"
                                                style="margin-left:0.4em"
                                                title="Ver otros calendarios"
                                                onchange="ListViewTabUtils.searchCalendar(this)">
                                            <option value="" disabled>Ver otros calendarios</option>
                                            {foreach $CALENDAR_VIEWS.records as $calendarView}
                                                {if $RELATED_VIEW neq NULL}
                                                    {if !in_array($calendarView.calendarviewid, $RELATED_VIEW)}
                                                        {continue}
                                                    {/if}
                                                {/if}
                                                <option value="{$calendarView.calendarviewid}" {if $calendarView.calendarviewid eq $VIEW_ID} selected="selected" {/if}
                                                        data-module="{$calendarView.modulename}">{$calendarView.label}</option>
                                            {/foreach}
                                        </select>
                                    {/if}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xs-3" id="CALANCDER-LOADING">&nbsp;</div>
                            <div class="col-lg-3 col-md-3 col-xs-3">
                                <div style="display: inline-block; float: right; padding-right: 20px"><h4 style="font-weight: bold; color: #cccccc">Vista de calendario</h4></div>
                            </div>
                            {/if}
                            <div class="col-md-12">
                                <div class="main-box">
                                    <div class="main-box-body clearfix">
                                        <div class="fc fc-ltr" id="calendar-{$idCalendar}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {*$DATA|var_dump*}
    </div>
    </div>
{/block}

{block name="js"}
    <script>
        {literal}
        jQuery(document).ready(function() {});
            CalendarManager.init({
                currentModule: '{/literal}{$MODULE}{literal}',
                currentViewId: '{/literal}{$idCalendar}{literal}',
                type: '{/literal}{$CALENDAR_TYPE}{literal}',
                currentLangCode: 'es',
                events: {/literal}{$DATA|json_encode}{literal}
            });
        {/literal}
    </script>
{/block}