{block name="css"}
    {math equation= rand() assign= "idKanban"}
    <link rel="stylesheet" href="include/dist/jkanban.css">
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <style>
     {literal}
        #myKanban-{/literal}{$idKanban}{literal} {
            overflow-x:  auto;
            padding:     0;
            font-weight: 400;
            font-size:   0.75em;
            scrollbar-width: thin;
        }
        #myKanban-top{/literal}{$idKanban}{literal} {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            margin-top: 2px;
            height: 20px;
            scrollbar-width: thin;
        }
        #myKanban-{/literal}{$idKanban}{literal}::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        #myKanban-{/literal}{$idKanban}{literal}::-webkit-scrollbar-thumb {
            background: #393812;
            -webkit-border-radius: 1ex;
            -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
        }

        #myKanban-{/literal}{$idKanban}{literal}::-webkit-scrollbar-corner {
            background: #000;
        }
        #myKanban-top{/literal}{$idKanban}::{literal}-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        #myKanban-top{/literal}{$idKanban}{literal}::-webkit-scrollbar-thumb {
            background: #393812;
            -webkit-border-radius: 1ex;
            -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
        }

        #myKanban-top{/literal}{$idKanban}{literal}::-webkit-scrollbar-corner {
            background: #000;
        }
        .kanban-container {
            display: flex;
            align-items: stretch;
            flex-direction: row;
            flex-wrap: nowrap;
            padding-left: 0!important;
        }

        .div1 {
            height: 20px;
        }
        .kanba-btn {
            float: right;
            z-index: 100000;
            width: 100%;
            margin-top: 0;
            padding-top: 0;
        }
        .del-kamba {
            float: right;
            cursor: pointer;
            padding: 2px !important;
            margin:  0 1px !important;
            z-index: 10000;
            color: #D8D8D8;
        }
        .change-kamba {
            float: right;
            cursor: pointer;
            padding: 2px !important;
            margin:  0 1px !important;
            z-index: 100000;
            color: #D8D8D8;
        }
        .edit-kamba {
            float: left;
            cursor: pointer;
            padding: 2px !important;
            margin:  0 1px !important;
            z-index: 100000;
            color: #D8D8D8;

        }
        .view-data-kamba {
            float: left;
            cursor: pointer;
            padding: 2px !important;
            margin:  0 1px !important;
            z-index: 100000;
            color: #D8D8D8;

        }
        #myKanban-content {
            width: 2000px;
        }
        .kanban-board {
            min-height: auto !important;
            height: auto !important;
            background-color: #ededed!important;
            border: 1px solid #cccccc;
        }

        .kanban-board .kanban-drag {
            height: auto !important;
            overflow-y: visible !important;
            padding: 10px !important;
            background-color: transparent!important;
        }
        .kanban-board-header {
            border-bottom-color: black;
        }
        @media (min-width: 360px) {
            #myKanban-{/literal}{$idKanban}{literal} {
                overflow-x:  auto;
                padding:     0;
                font-weight: 200;
                font-size:   0.835em;
            }
            .kanban-board {
                min-height: auto !important;
            }

            .kanban-board .kanban-drag {
                height: auto !important;
                overflow-y: visible !important;
                padding: 10px !important;
            }
        }

        @media (min-width: 768px) {
            #myKanban-{/literal}{$idKanban}{literal} {
                overflow-x:  auto;
                padding:     0;
                font-weight: 300;
                font-size:   0.75em;
            }
            .kanban-board {
                min-height: auto !important;
                height: auto !important;
            }

            .kanban-board .kanban-drag {
                height: auto !important;
                overflow-y: visible !important;
                padding: 10px !important;
            }
        }

        @media (min-width: 992px) {
            #myKanban-{/literal}{$idKanban}{literal} {
                overflow-x:  auto;
                padding:     0;
                font-weight: 400;
                font-size:   0.755em;
            }
            .kanban-board {
                min-height: auto !important;
            }

            .kanban-board .kanban-drag {
                height: auto !important;
                overflow-y: visible !important;
                padding: 10px !important;
            }
        }

        @media (min-width: 1200px) {
            #myKanban-{/literal}{$idKanban}{literal} {
                overflow-x:  auto;
                padding:     0;
                font-weight: 400;
                font-size:   0.875em;
            }

            .kanban-board {
                min-height: auto !important;
                height: auto !important;
            }

            .kanban-board .kanban-drag {
                height: auto !important;
                overflow-y: visible !important;
                padding: 10px !important;
            }

            .kanban-item {
                height: auto !important;
                overflow-wrap: break-word;
                z-index: 100;
            }

        }

        @media (min-width: 1920px) {
            #myKanban-{/literal}{$idKanban}{literal} {
                overflow-x:  auto;
                padding:     25px 0;
                font-weight: 400;
                font-size:   0.855em;
            }

            .kanban-board {
                min-height: auto !important;
                height: auto !important;

            }

            .kanban-board .kanban-drag {
                height: auto !important;
                overflow-y: visible !important;
                padding: 5px !important;
            }

            .kanban-item {
                height: auto !important;
                overflow-wrap: break-word;
            }
        }

        .row-kanban {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-flex-wrap: wrap;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack:    center !important;
            justify-content:  center !important
        }

        .calculation-data {
            background: #f7f7f7;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin: 4px 4px 6px 4px;
            padding: 6px 8px;
            transition: box-shadow 0.2s ease;
        }
        
        .calculation-data:hover {
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }

        .calculation-data p {
            text-align: center;
            padding: 0;
            margin: 0;
            font-size: 9.6px;
            line-height: 1.3;
        }
        
        .calculation-data .calc-title {
            font-weight: 600;
            color: #555;
            margin-bottom: 1px;
        }
        
        .calculation-data .calc-value {
            font-size: 10.4px;
            font-weight: 700;
            color: #2196F3;
        }
        
        .calculation-data .calc-percentage {
            font-size: 9.6px;
            font-weight: 600;
            color: #666;
            margin-left: 3px;
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
    {if !empty($RULECOLORS)}
        <style>
            {foreach $RULECOLORS as $itemcolor}
            .{$FIELDNAME}{$itemcolor.pickfieldid} {
               background:#3498db {*$itemcolor.backgroundcolor*}{literal};
                color: #ffffff;
                position: relative;
               /* border: 1px solid #cccccc*/{/literal}{*$itemcolor.backgroundcolor*}{literal};
            }

            {/literal}
            {/foreach}
            .main-box-body {
                box-shadow:    0px 0px 0px 0 #FFFFFF !important;
                background-color: #FFFFFF;
                border-radius: 0px !important;
                min-height: auto !important;
                margin-left:   -15px !important;
                margin-right:  -15px !important;
                margin-bottom: 0 !important;
            }
            #myKanban-{$idKanban} {
                min-height: auto !important;
                margin-left: 10px!important;
            }
        </style>
    {/if}
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
                                    <a data-toggle="tab" href="#VIEW-TASK-{$TAB_HOME_ID}" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
                                       title="Tareas"
                                       {if $TAB_GROUP neq 'ACTIVITY'}
                                       onclick="HomeUtils.activeTaskTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-TASK','{$TAB_GROUP}')"
                                       {/if}
                                       data-toggle="tab"><i class="fa fa-check-square" aria-hidden="true"></i></a>
                                    {*/if*}
                                    {* LIST-VIEW-KANBAN-VIEW *}
                                    {if $TAB_GROUP eq 'record'}
                                    <a data-toggle="tab" href="#ListViewHomeContents-{$TAB_HOME_ID}" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
                                       title="Listado de registros"
                                       data-toggle="tab"><i class="fa fa-list-ul"></i></a>
                                    {/if}
                                    {*if $STATUS_BUTTONS['kanban']*}
                                    <button type="button" class="btn btn-primary" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
                                            title="Vista kanban"><i class="fa fa-trello" aria-hidden="true"></i></button>
                                    {*/if*}
                                    {* LIST-VIEW-CALENDAR *}
                                    {*if $STATUS_BUTTONS['calendar']*}
                                    <a data-toggle="tab" href="#VIEW-CALENDAR-{$TAB_HOME_ID}" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
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
                                        <select name="viewname" id="viewname" class="form-control" onchange="HomeUtils.searchKanban(this,'{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-KANBAN')" title="">
                                            {if $KANBAN_LIST neq 'null'}
                                                {assign var='fieldSelected' value=''}
                                                {*<optgroup label="Kanban"> *}
                                                {foreach $KANBAN_LIST as $kanban}
                                                    {if $RELATED_VIEW neq NULL}
                                                        {if (!in_array($kanban.kanbanviewid, $RELATED_VIEW)) && $kanban.locked eq 0}
                                                            {continue}
                                                        {/if}
                                                    {/if}
                                                    <option value="{$kanban.kanbanviewid}" {if $kanban.kanbanviewid eq $KANBAN_VIEW}  selected {$fieldSelected= $kanban.fieldname} {/if} fieldname="{$kanban.fieldname}">{$kanban.label}</option>
                                                {/foreach}
                                                {* </optgroup> *}
                                            {/if}
                                        </select>
                                        <input type="hidden" name="modulename" id="modulename-{$idKanban}" value="{$MODULE}">
                                        <input type="hidden" name="fieldname" id="fieldname-{$idKanban}" value="{$fieldSelected}">
                                    </div>
                                </div>
                            </div>
                            {* /Home Buttons group *}
                            {else}
                            <div class="col-md-12" style="{if isset($IS_HOME_TAB)}display: none; {/if}padding: 0;">
                                {* Línea 1: Botones y selector de vista *}
                                <div class="form-group list-view-filter" style="margin-bottom: 5px; display: flex; align-items: center; flex-wrap: nowrap; gap: 1px; overflow: visible;">
                                    <div class="btn-group btn-control" style="margin-left: 10px; flex-shrink: 0; display: flex; gap: 1px; float: none !important; vertical-align: middle;">
										{* LIST-VIEW*}
										<a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
										   style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
										   onclick="ListViewTabUtils.activeListTab(event)"
										   data-toggle="tab" title="Listado de registros"><i
													class="fa fa-list-ul"></i></a>
										{* LIST-VIEW-KANBAN-VIEW *}
										<button type="button" class="btn btn-primary"
												title="Vista kanban"
												style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"><i class="fa fa-trello" aria-hidden="true"></i>
										</button>
										{* LIST-VIEW-BOX-SCORE *}
										{if $STATUS_BUTTONS['boxscore'] && false}
											<a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
											   title="Indicadores de gestión"
											   onclick="ListViewTabUtils.activeBoxScoreTab (event)"
											   data-toggle="tab"><i class="fa fa-heart-o"></i></a>
										{/if}
										{* LIST-VIEW-GRAPHIC *}
										{if $STATUS_BUTTONS['graphic'] && false}
											<a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
											   TITLE="Graficos"
											   onclick="ListViewTabUtils.activeGraphicTab (event)"
											   data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
										{/if}
										{* report *}
										{if $STATUS_BUTTONS['report'] && false}
											<a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
											   title="Informes"
											   onclick="ListViewTabUtils.activeReportTab (event)"
											   data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
										{/if}
										{* LIST-VIEW-CALENDAR *}
										{if $STATUS_BUTTONS['calendar']}
											<a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
											   title="vista calendario"
											   onclick="ListViewTabUtils.activeCalendarTab (event)"
											   data-toggle="tab"><i class="fa fa-calendar"></i></a>
										{/if}
										{if $STATUS_BUTTONS['task']}
											<a data-toggle="tab" href="#LIST-VIEW-KANBAN-TASK-VIEW" class="btn btn-default" style="font-size: 15px!important; height: 34px; width: 34px; padding: 6px 0; text-align: center;"
											   title="vista kanban de tareas"
											   onclick="ListViewTabUtils.activeKanbanTaskTab (event)"
											   data-toggle="tab"><i class="bi bi-kanban-fill"></i></a>
										{/if}
                                    </div>
                                    
                                    {* Selector de vista Kanban - en la misma línea *}
                                    <div style="margin-left: 7px; flex-shrink: 0; display: flex; align-items: center;margin-top:0.7em;">
                                        <div class="input-group" style="width: auto; margin-bottom: 0;">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="height: 34px; line-height: 1.42857143;">
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
                                        <select name="viewname" id="viewname" class="form-control" onchange="ListViewTabUtils.searchKanban(this)" title="" style="width: 250px; height: 34px; padding: 6px 12px;">
                                            {if $KANBAN_LIST neq 'null'}
                                                {assign var='fieldSelected' value=''}
                                                {*<optgroup label="Kanban"> *}
                                                {foreach $KANBAN_LIST as $kanban}
                                                    {if $RELATED_VIEW neq NULL}
                                                        {if (!in_array($kanban.kanbanviewid, $RELATED_VIEW)) && $kanban.locked eq 0}
                                                            {continue}
                                                        {/if}
                                                    {/if}
                                                    <option value="{$kanban.kanbanviewid}" {if $kanban.kanbanviewid eq $KANBAN_VIEW}  selected {$fieldSelected= $kanban.fieldname} {/if} fieldname="{$kanban.fieldname}">{$kanban.label}</option>
                                                {/foreach}
                                                {* </optgroup> *}
                                            {/if}
                                        </select>
                                        <input type="hidden" name="modulename" id="modulename-{$idKanban}" value="{$MODULE}">
                                        <input type="hidden" name="fieldname" id="fieldname-{$idKanban}" value="{$fieldSelected}">
                                    </div>
                                </div>
                                </div>
                                
                                {* Línea 2: Paginación en su propia línea debajo *}
                                {if isset($PAGINATION)}
                                <div style="text-align: right; padding-right: 10px; margin-bottom: 5px;">
                                <span style="margin-right: 10px; font-size: 13px; vertical-align: middle;">
                                    <i class="fa fa-info-circle"></i>
                                    Mostrando <strong>{$PAGINATION.showing}</strong> de <strong>{$PAGINATION.total}</strong> registros
                                    {if $PAGINATION.totalPages > 1}
                                        (Página <strong>{$PAGINATION.page}</strong> de <strong>{$PAGINATION.totalPages}</strong>)
                                    {/if}
                                </span>
                                {if $PAGINATION.totalPages > 1}
                                <div class="btn-group" role="group" style="display: inline-block; vertical-align: middle;background-color: white; color: black; border: 1px solid #efefef;">
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage(1)" 
                                            {if $PAGINATION.page == 1}disabled{/if}
                                            title="Primera página"
                                           style="background-color: white; color: black; border: 0px solid #ccc;">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage({$PAGINATION.page - 1})" 
                                            {if $PAGINATION.page == 1}disabled{/if}
                                            title="Página anterior"
                                            style="background-color: white; color: black; border: 0px solid #ccc;">
                                        <i class="fa fa-angle-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" disabled 
                                            style="background-color: white !important; color: black !important; border: 0px solid #ccc !important; opacity: 1 !important;">
                                        {$PAGINATION.page} / {$PAGINATION.totalPages}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage({$PAGINATION.page + 1})" 
                                            {if !$PAGINATION.hasMore}disabled{/if}
                                            title="Página siguiente" 
                                            style="background-color: white; color: black; border: 0px solid #ccc;">
                                        <i class="fa fa-angle-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage({$PAGINATION.totalPages})" 
                                            {if !$PAGINATION.hasMore}disabled{/if}
                                            title="Última página" 
                                            style="background-color: white; color: black; border: 0px solid #ccc;" >
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                </div>
                                {/if}
                                </div>
                                {/if}
                            </div>
                            {/if}
                            {* Área del gráfico Kanban *}
                            <div style="margin-left: -20px!important;top: -105px">
                                <div id="myKanban-top{$idKanban}" style="height:2px;padding:0px;margin:0">
                                    <!--<div class="div1"></div>-->
                                </div>
                                <div class="justify-content-center"  id="myKanban-{$idKanban}"></div>
                            </div>

                            {* Paginación inferior - duplicada *}
                            {if isset($PAGINATION)}
                            <div style="text-align: right; padding-right: 10px; margin-top: 10px; margin-bottom: 5px;">
                                <span style="margin-right: 10px; font-size: 13px; vertical-align: middle;">
                                    <i class="fa fa-info-circle"></i>
                                    Mostrando <strong>{$PAGINATION.showing}</strong> de <strong>{$PAGINATION.total}</strong> registros
                                    {if $PAGINATION.totalPages > 1}
                                        (Página <strong>{$PAGINATION.page}</strong> de <strong>{$PAGINATION.totalPages}</strong>)
                                    {/if}
                                </span>
                                {if $PAGINATION.totalPages > 1}
                                <div class="btn-group" role="group" style="display: inline-block; vertical-align: middle;background-color: white; color: black; border: 1px solid #efefef;">
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage(1)" 
                                            {if $PAGINATION.page == 1}disabled{/if}
                                            title="Primera página"
                                           style="background-color: white; color: black; border: 0px solid #ccc;">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage({$PAGINATION.page - 1})" 
                                            {if $PAGINATION.page == 1}disabled{/if}
                                            title="Página anterior"
                                            style="background-color: white; color: black; border: 0px solid #ccc;">
                                        <i class="fa fa-angle-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" disabled 
                                            style="background-color: white !important; color: black !important; border: 0px solid #ccc !important; opacity: 1 !important;">
                                        {$PAGINATION.page} / {$PAGINATION.totalPages}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage({$PAGINATION.page + 1})" 
                                            {if !$PAGINATION.hasMore}disabled{/if}
                                            title="Página siguiente" 
                                            style="background-color: white; color: black; border: 0px solid #ccc;">
                                        <i class="fa fa-angle-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default" 
                                            onclick="KanbanPagination.loadPage({$PAGINATION.totalPages})" 
                                            {if !$PAGINATION.hasMore}disabled{/if}
                                            title="Última página" 
                                            style="background-color: white; color: black; border: 0px solid #ccc;" >
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                </div>
                                {/if}
                            </div>
                            {/if}

                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    {/block}
{block name="js"}
<script src="include/dist/jkanban.js"></script>
<script type="text/javascript">
    {literal}
    var totalBoard = {/literal}{$RULECOLORS|@count}{literal}
    var boardWidth, myScreen = window.screen.availWidth;
    if (totalBoard > 4) {
        boardWidth = Math.floor(((myScreen * (1055 / 1280)) / 5) - 16);
    } else if(totalBoard === 4) {
        boardWidth = Math.floor(((myScreen * (1060 / 1280)) / 4) - 16);
    } else if(totalBoard === 3) {
        boardWidth = Math.floor(((myScreen * (1074 / 1280)) / 3) - 16);
    } else {
        boardWidth = Math.floor(((myScreen * (1092 / 1280)) / 2) - 16);

    }{/literal}
    stringBoradWidth = boardWidth + 'px';
    {literal}
    jQuery (function () {
        jQuery('#myKanban-top{/literal}{$idKanban}{literal}').on('scroll', function (e) {
            jQuery('#myKanban-{/literal}{$idKanban}{literal}').scrollLeft(jQuery('#myKanban-top{/literal}{$idKanban}{literal}').scrollLeft());
        });
        jQuery('#myKanban'-{/literal}{$idKanban}{literal}).on('scroll', function (e) {
            jQuery('#myKanban-top{/literal}{$idKanban}{literal}').scrollLeft(jQuery('#myKanban-{/literal}{$idKanban}{literal}').scrollLeft());
        });
    });
    var KanbanTest = new jKanban ({
        element:       '#myKanban-{/literal}{$idKanban}{literal}',
        gutter:        '10px',
        widthBoard:    stringBoradWidth,
        click:         function (el) {
        },
        buttonClick:   function (el, boardId) {
            // create a form to enter element
            var formItem = document.createElement ('form');
            formItem.setAttribute ("class", "itemform");
            formItem.innerHTML = '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="form-group"><button type="submit" class="btn btn-primary btn-xs pull-right">Submit</button><button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancel</button></div>'

            KanbanTest.addForm (boardId, formItem);
            formItem.addEventListener ("submit", function (e) {
                e.preventDefault ();
                var text = e.target[ 0 ].value;
                KanbanTest.addElement (boardId, {
                    "title": text,
                });
                formItem.parentNode.removeChild (formItem);
            });
            document.getElementById ('CancelBtn').onclick = function () {
                formItem.parentNode.removeChild (formItem)
            }
        },
        addItemButton: false,
        boards:        [
            {/literal}
            {foreach key=keyBoard item=itemcolor from=$RULECOLORS}
            {literal}
            {
                "id":    "{/literal}{$FIELDNAME}_{$itemcolor.pickfieldid}{literal}",
                "title": "{/literal}{$itemcolor.picklabel}{literal}",
                "class": "{/literal}{$FIELDNAME}{$itemcolor.pickfieldid}{literal}",
                {/literal}
                {if $itemcolor.calculation neq NULL}
                {literal}
                "CalculationTitle": "{/literal}{$itemcolor.operation}{literal}",
                "CalculationField": "{/literal}{$itemcolor.fieldname}{literal}",
                "CalculationValue": "{/literal}{$itemcolor.calculation}{literal}",
                "CalculationPercentage": "{/literal}{if $itemcolor.percentage neq NULL}{$itemcolor.percentage}{/if}{literal}",
                {/literal}
                {else}
                {literal}
                "CalculationTitle": "",
                "CalculationPercentage": "",
                {/literal}
                {/if}
                {literal}
                "item":  [
                    {/literal}
                    {assign var='modname' value=$MODULENAME|cat:'id'}
                    {assign var='fieldname' value=$FIELDNAME}
                    {foreach key=keyAlert item=item from=$ITEMVIEWS}
                    {assign var='title' value='<div class="kanba-btn"><a class="del-kamba" title="Eliminar expediente" onclick="deleteReg'|cat:$idKanban|cat:'(event, this)"><i class="fa fa-trash-o"></i></a>&nbsp;<a class="change-kamba" title="Asignar expediente" onclick="changeOwner'|cat:$idKanban|cat:'(event, this)"><i class="fa fa-refresh"></i></a>&nbsp;<a class="view-data-kamba"title="Ver detalles" onclick="cardViewModal'|cat:$idKanban|cat:'(event, this)"><i class="fa fa-eye"></i></a>&nbsp;<a class="edit-kamba"title="Ver detalles" onclick="detailViewReg'|cat:$idKanban|cat:'(event, this)"><i class="fa fa-external-link"></i></a></div><br/>'}
                    {assign var='todo' value=''}
                    {foreach key=keyItem item=itemCont from=$item}
                    {if $keyItem == $fieldname}
                    {if $itemCont == $itemcolor.picklabel}
                    {assign var='todo' value='todo'}
                    {else}
                    {break}
                    {/if}
                    {/if}
                    {if $keyItem != $fieldname}
                    {assign var='itemValue' value=$itemCont|truncate:50}
                    {assign var='title' value=$title|cat:$itemValue|cat:'</br>'}
                    {/if}
                    {assign var='countcalc' value=$countcalc + 1}
                    {/foreach}
                    {if $todo == 'todo'}
                        {literal}{
                        "id":      "{/literal}{$keyAlert}{literal}",
                        "title":   "{/literal}{$title|replace:'"':'\''}{literal}",
                        "drag":    function (el, source) {
                        },
                        "dragend": function (el) {
                        },
                        "drop":    function (el, target) {
                            var tid = target.parentNode.dataset.id;
                            var toBoardId = tid.split ('_');
                            var lBoard = toBoardId.length;
                            tid = toBoardId[ lBoard - 1 ];
                            updateFieldValue (el.dataset.eid, jQuery ('#fieldname-{/literal}{$idKanban}{literal}').val (), jQuery ('#modulename-{/literal}{$idKanban}{literal}').val (), tid);
                        },
                    },
                    {/literal}
                    {/if}
                    {/foreach}
                    {literal}
                ]
            },
            {/literal}
            {/foreach}
            {literal}
        ]
    });
    function updateFieldValue (recordid, fieldname, tabname, valueid) {
        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module='+ tabname + '&action=AjaxListViewUtils&function=KANBAN-UPDATE-FIELD&Ajax=true&recordid=' + recordid + '&valueid=' + valueid + '&fieldname=' + fieldname + '&tabname=' + tabname,
                onComplete: function (response) {
                }
            }
        );
    }
    function cardViewModal{/literal}{$idKanban}{literal}(event, element) {
        var idKanban = {/literal}{$KANBAN_VIEW}{literal},
            record = jQuery (element).closest ('div.kanban-item').attr ('data-eid'),
            module = jQuery ('#modulename-{/literal}{$idKanban}{literal}').val ();
        console.log(module);
        console.log('{/literal}{$idKanban}{literal}')
        ekkoLightBox = jQuery('<a href=index.php?module='+module+'&action=AjaxListViewUtils&Ajax=true&record='+record+'&function=KANBAN-VIEW-CARD&viewId=' + idKanban + ' data-toggle="lightbox" data-max-width="400" data-title="">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass('bottom');
                modalBackdrop.removeClass('z-index');
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }

    function detailViewReg{/literal}{$idKanban}{literal} (event, element) {
        var record = jQuery (element).closest ('div.kanban-item').attr ('data-eid'),
            module = jQuery ('#modulename-{/literal}{$idKanban}{literal}').val ();
        window.open ('index.php?module=' + module + '&action=DetailView&record=' + record, '_blank');
        event.stopPropagation();
        event.preventDefault();
    };

    function deleteReg{/literal}{$idKanban}{literal} (event, element) {
        var card   = jQuery(element).closest ('div.kanban-item'),
            record = card.attr ('data-eid'),
            module = jQuery ('#modulename-{/literal}{$idKanban}{literal}').val (),
            arguments = {
                'module':         module,
                'action':        'Delete',
                'record':         record,
                'return_action': 'KANBAN-DELETE'
            };
        if (!confirm('¿Está seguro que desea eliminar este expediente?')) {
            return
        }

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El expediente ha sido eliminado');
                    card.remove()
                }
            }
            catch (e) {
                alert(e);
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }
    function changeOwner{/literal}{$idKanban}{literal} (event, element) {
        var card   = jQuery(element).closest ('div.kanban-item'),
            record = parseInt (card.attr ('data-eid')),
            module = jQuery ('#modulename-{/literal}{$idKanban}{literal}').val ();
        console.log(module);
        ekkoLightBox = jQuery('<a href=index.php?module='+module+'&action=ChangeEntityOwner&Ajax=true&record='+record+' data-toggle="lightbox" data-max-width="400" data-title="Asignar expediente">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }
    jQuery(window).on('load', function (e) {
        jQuery('.div1').width (jQuery ('.kanban-container').width() + 50);
    });
    
    // Sistema de paginación para Kanban
    var KanbanPagination = {
        currentPage: {/literal}{if isset($PAGINATION)}{$PAGINATION.page}{else}1{/if}{literal},
        viewId: {/literal}{$KANBAN_VIEW}{literal},
        moduleName: '{/literal}{$MODULE}{literal}',
        fieldName: '{/literal}{$FIELDNAME}{literal}',
        pageSize: 100,
        
        loadPage: function(page) {
            if (page < 1) return;
            
            var self = this;
            jQuery('#KANBAN-LOADING').html('<i class="fa fa-spinner fa-spin"></i> Cargando página ' + page + '...');
            
            jQuery.ajax({
                url: 'index.php',
                type: 'POST',
                data: {
                    module: self.moduleName,
                    action: 'AjaxListViewUtils',
                    function: 'VIEW-KANBAN',
                    Ajax: true,
                    page: page,
                    pageSize: self.pageSize,
                    kanban: {
                        kanbanviewid: self.viewId,
                        fieldname: self.fieldName
                    }
                },
                success: function(response) {
                    try {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.error === 'OK') {
                            // Recargar la vista completa
                            jQuery('#LIST-VIEW-KANBAN-VIEW').html(data.html);
                            self.currentPage = page;
                            jQuery('#KANBAN-LOADING').html('');
                            
                            // Scroll automático al inicio de la vista Kanban
                            var kanbanTop = jQuery('#myKanban-top{/literal}{$idKanban}{literal}');
                            if (kanbanTop.length > 0) {
                                jQuery('html, body').animate({
                                    scrollTop: kanbanTop.offset().top - 100
                                }, 300);
                            }
                        } else {
                            alert('Error al cargar página: ' + data.error);
                            jQuery('#KANBAN-LOADING').html('');
                        }
                    } catch (e) {
                        console.error('Error procesando respuesta:', e);
                        alert('Error al cargar la página');
                        jQuery('#KANBAN-LOADING').html('');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    alert('Error de conexión al cargar la página');
                    jQuery('#KANBAN-LOADING').html('');
                }
            });
        }
    };
    {/literal}
</script>
{/block}
