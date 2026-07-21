{extends file='base/BaseListViewEntries.tpl'}
{block name="header-columns"}
    {assign var='today' value=date('Y-m-d')}
    {assign var='lastWeek' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('7 days')), 'Y-m-d')}
    {assign var='lastMonth' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('1 month')), 'Y-m-d')}
    {assign var='lastQuarter' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('3 months')), 'Y-m-d')}
    {assign var='lastSemester' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('6 months')), 'Y-m-d')}
    {assign var='lastYear' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('12 months')), 'Y-m-d')}
    {strip}
        <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css" />
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" {if $TAB_HOME_ID neq NULL}style="margin-top: 0" {/if}>
                {if $TAB_HOME_ID neq NULL}
                    <div id="btn-toolbar-{$idActivity}" class="btn-toolbar" role="toolbar">
                        <div class="btn-group" style="margin-right:0;margin-left: 10px">
                            {* LIST-VIEW-GRAPHIC *}
                            {*if $STATUS_BUTTONS['graphic']*}
                            <button id="VIEW-TASK-{$TAB_HOME_ID}" type="button" class="btn btn-primary" style=" font-size: 15px!important;" 
                            		title="Tareas"><i class="fa fa-check-square" aria-hidden="true"></i></button>

                            {*/if*}
                            {if isset($TAB_GROUP) && $TAB_GROUP neq 'ACTIVITY'}
                                <a data-toggle="tab" href="#ListViewHomeContents-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;" 
                                	title="Listado de registros" 
                                	data-toggle="tab"><i class="fa fa-list-ul"></i></a>
                            {/if}
                            {* LIST-VIEW-KANBAN-VIEW *}
                            {*if $STATUS_BUTTONS['kanban']*}
                            <a data-toggle="tab" href="#VIEW-KANBAN-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;" 
                            	title="Vista kanban"
                                onclick="HomeUtils.activeTab (event, '{$TAB_HOME_ID}', '{$FLMODULE}', 'VIEW-KANBAN','{$TAB_GROUP}')"
                                data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                            {*/if*}
                            {* LIST-VIEW-CALENDAR *}
                            {*if $STATUS_BUTTONS['calendar']*}
                            <a data-toggle="tab" href="#VIEW-CALENDAR-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;" 
                            	title="vista calendario"
                                onclick="HomeUtils.activeTab (event, '{$TAB_HOME_ID}', '{$FLMODULE}', 'VIEW-CALENDAR','{$TAB_GROUP}')"
                                data-toggle="tab"><i class="fa fa-calendar"></i></a>
                            {*/if*}
                        </div>
                    {/if}
                    {if $QUICK_VIEW neq NULL}
                        <div class="btn-group" style="margin-bottom: 4px">
                            {foreach $QUICK_VIEW as $key => $value}
                                <button type="button" 
                                	id="btn-quick-{$idActivity}-{$value}" 
                                	data-module-name="{$MODULE}"
                                    data-related-module="{$RELATED_MODULE}" 
                                    data-activity-id="{$idActivity}"
                                    class="btn {if $value eq $VIEW->getId ()}btn-primary{else}btn-default{/if}"
                                    onclick="HomeUtils.setQuickView(this, '{$value}')" 
                                    style="height: 34px;">{$key}</button>
                            {/foreach}
                        </div>
                    {/if}
                    {* filters *}
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" 
                        style="height: 34px;"
                            data-toggle="dropdown">
                            Filtros <span class="caret"></span>
                        </button>

                        <ul class="dropdown-menu" role="menu">
                            <li class="active"><a rel="{$idActivity}" data-filter="standard"
                                    onclick="HomeUtils.setFilter(this, event)" 
                                    href="#">Estándar</a></li>
                            <li class=""><a rel="{$idActivity}" data-filter="user" 
                            		onclick="HomeUtils.setFilter(this, event)"
                                    href="#">Por usuarios</a></li>
                            <li class=""><a rel="{$idActivity}" data-filter="date-time"
                                    onclick="HomeUtils.setFilter(this, event)" 
                                    href="#">Temporales</a></li>
                            <li class="divider"></li>
                            <li class=""><a rel="{$idActivity}" data-filter="hidden" 
                            		onclick="HomeUtils.setFilter(this, event)"
                                    href="#">Ocultar filtros</a></li>
                        </ul>
                    </div>
                    {* standard filter *}
                    <div class="btn-group col-lg-3 col-md-3 col-xs-3 standard-{$idActivity}" style="margin-bottom: 4px">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                    style="height: 34px;"><i class="fa fa-filter"></i><span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="index.php?module={$MODULE}&action=CustomView&parenttab={$returnModule}">{$APP.LNK_CV_CREATEVIEW}</a>
                                    </li>
                                    {if (in_array (DataViewUtils::PERMISSION_CAN_EDIT, $VIEW_PERMISSIONS))}
                                        <li><a href="#"
                                                onclick="DataViewUtils.editView (this,'{$returnModule}'); return false;">{$APP.LNK_CV_EDIT}</a>
                                        </li>
                                    {/if}
                                    {if (in_array (DataViewUtils::PERMISSION_CAN_DELETE, $VIEW_PERMISSIONS))}
                                        <li>
                                            <form action="index.php" method="post"
                                                onsubmit="return confirm ('¿Estás seguro que quieres eliminar la vista seleccionada?');">
                                                <input type="hidden" name="module" value="CustomView" />
                                                <input type="hidden" name="action" value="Delete" />
                                                <input type="hidden" name="dmodule" value="{$MODULE}" />
                                                <input type="hidden" name="record" value="{$VIEW->getId ()}" />
                                                <input type="hidden" name="return_action" value="{$returnAction}" />
                                                <input type="hidden" name="return_module" value="{$returnModule}" />
                                                <button type="submit" class="submit-link">{$APP.LNK_CV_DELETE}</button>
                                            </form>
                                        </li>
                                    {/if}
                                </ul>
                            </div>
                            <select id="viewname-home-{$idActivity}" name="viewname" class="form-control col-md-3 col-sm-3 col-xs-3" 
                            	data-module-name="{$MODULE}"
                                data-related-module="{$RELATED_MODULE}" 
                                data-activity-id="{$idActivity}"
                                onchange="DataViewUtils.openView (this, '{$TAB_NAME}'); HomeUtils.setStdFilter(this,'{$idActivity}');" title="">
                                {if (!empty ($AVAILABLE_VIEWS))}
                                    <optgroup label="Filtros">
                                        {foreach $AVAILABLE_VIEWS as $availableView}
                                            <option value="{$availableView->getId ()}" {if ($availableView->getId () == $VIEW->getId ())} selected="selected" {/if}
                                                data-view-type="REGULAR">{if ($availableView->getName () != 'All')}{$availableView->getName ()}{else}Filtro estándar{/if}</option>
                                        {/foreach}
                                    </optgroup>
                                {/if}
                                {if $KANBAN_LIST neq NULL }
                                    <optgroup label="Kanban">
                                        {foreach $KANBAN_LIST as $kanban}
                                            <option value="{$kanban.kanbanviewid}" data-field-name="{$kanban.fieldname}"
                                                data-view-type="KANBAN">{$kanban.label}</option>
                                        {/foreach}
                                    </optgroup>
                                {/if}
                            </select>
                        </div>
                    </div>
                    {* user filters *}
                    <div class="btn-group hide user-{$idActivity}" style="margin-bottom: 4px">
                        <select name="assigned_user_id" class="form-control list-view-filter-{$idActivity} col-md-3 col-sm-3 col-xs-3" onchange="" title="">
                            <option value="">Filtrar por usuario</option>
                            {foreach $AVAILABLE_SYSTEM_USERS as $availableUser}
                                <option value="{$availableUser->getId ()}">{$availableUser->getFirstName ()} {$availableUser->getLastName ()}</option>
                            {/foreach}
                        </select>
                    </div>
                    {* date period filters *}
                    <div class="btn-group hide date-time-{$idActivity}" style="margin-bottom: 4px">
                        <select id="period-dates-{$idActivity}" class="form-control" onchange="" title="">
                            <option value="" selected="selected">Filtrar por períodos</option>
                            <option value="{$today}">Hoy</option>
                            <option value="{$lastWeek}">Última semana</option>
                            <option value="{$lastMonth}">Último mes</option>
                            <option value="{$lastQuarter}">Último trimestre</option>
                            <option value="{$lastSemester}">Último semestre</option>
                            <option value="{$lastYear}">Último año</option>
                        </select>

                    </div>
                    {* date  filters *}
                    <div class="btn-group col-lg-3 col-md-3 col-xs-3  hide date-time-{$idActivity}" style="margin-bottom: 4px;display: none">
                        <div class="input-group">
                            <span class="input-group-addon "><i class="fa fa-calendar"></i></span>
                            <input id="start-date-home" type="text" name="date_start"
                                class="form-control from-field list-view-filter-{$idActivity} date start-date"
                                style="margin: 0!important;" placeholder="Desde" />
                        </div>
                    </div>
                    {* date  filters *}
                    <div class="btn-group  col-lg-3 col-md-3 col-xs-3  hide date-time-{$idActivity}" style="margin-bottom: 4px;display: none">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input id="end-date-home" type="text" name="due_date"
                                class="form-control list-view-filter-date-{$idActivity} date end-date"
                                style="margin: 0!important;" placeholder="Hasta" />
                        </div>
                    </div>
                    {* action button *}
                    <div class="btn-group pull-right" style="margin-bottom: 2px">
                        <button type="button" 
                        	style="height: 34px;"
                            onclick="CalendarWizard.open (null, null, null, 'index.php?module=Home&action=index&tab={$TAB_NAME}');"
                            title="Crear tarea" 
                            class="btn btn-info"><i class="fa fa-plus"></i></button>
                        <a href="index.php?module=Calendar&amp;action=index" class="btn btn-warning" 
                        	style="height: 34px;"
                            title="Ir al calendario"><i class="fa fa-calendar"></i></a>
                        <button type="button" class="btn btn-success hide" style="height: 34px;">
                            <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-danger hide" style="height: 34px;">
                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {/strip}
{/block}
{block name="tbody-item"}
    {foreach $viewColumns as $index => $viewColumn}
        {if $viewColumn.fieldname eq 'modulename' && !$HAS_RELATED}
            {continue}
        {/if}
        <td {if $record['isNew']}style="font-weight: bold" {/if}>
            {if ((empty ($VIEW_DATA.entityidentifier)) && ($index === 0)) || ($viewColumn.fieldname == $VIEW_DATA.entityidentifier)}
                {$record[$viewColumn.fieldname]}
                {*
                <a href="index.php?module={$MODULE}&action=DetailView&record={$record.crmid}">{$record[$viewColumn.fieldname]}</a>
                *}
            {elseif (in_array ($viewColumn.fieldname, array ('eventstatus', 'activitytype')))}
                {$record[$viewColumn.fieldname]|@getTranslatedString: $MODULE}
            {elseif (in_array ($viewColumn.fieldname, array ('progress')))}
                <div class="text-center">{intval ($record[$viewColumn.fieldname])} %</div>
                <input type="range" value="{$record[$viewColumn.fieldname]}" class="progress" min="1" max="100" 
                	placeholder="" disabled="disabled" />

            {elseif (($record['related_to'] neq NULL) && $viewColumn.fieldname eq 'related_to')}
                <a href="index.php?module={$record['tab_name']}&action=DetailView&record={$record.related_id}" target="_blank">{$record['related_to']}</a>

            {elseif ($viewColumn.fieldname eq 'assigned_user_id')}
                <figure class="center-block" style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px;"><img class="img-responsive img-circle" alt="Platzi el guapo" title="{$record['assigned_user_id']}o" src="{$record['useravatar']}"></figure>
            {elseif ((($record['reports'] neq NULL) && ($record['reports'] gte 1) && ($record['reports'] neq '0'))  && ($record['related_id'] neq NULL) && ($viewColumn.fieldname eq 'reports') && ($record['related_to'] neq NULL))}
                <a 
                	data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title="Reportes sobre actividad:" 
                	title="Reportes y feedbacks"
                    href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$record['related_id']}&formodule={$record['tab_name']}&boxtype=REPORT_ACTIVITY&function=ITERATIONS&Ajax=true">{$record['reports']}
                </a>
            {elseif ((($record['feedbacks'] neq NULL) && ($record['feedbacks'] gte 1) && ($record['feedbacks'] neq '0'))  && ($record['related_id'] neq NULL) && ($viewColumn.fieldname eq 'feedbacks') && ($record['related_to'] neq NULL))}
                <a 
                	data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title="Reportes sobre actividad:" 
                	title="Reportes y feedbacks"
                	href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$record['related_id']}&formodule={$record['tab_name']}&boxtype=REPORT_ACTIVITY&function=ITERATIONS&Ajax=true">{$record['feedbacks']}
                </a>
            {else}
                {$record[$viewColumn.fieldname]}
            {/if}
        </td>
    {/foreach}
{/block}
{block name="row-actions"}
    <table style="width: 100%; border: hidden">
        <tr>
            {if $record['tab_name'] neq NULL && $record['related_id'] neq NULL}
                <td style="width: 8%">
                    <!-- wa 23-05-2023 tasksListView-->
                    <a 
                    	data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title="{*Reporte sobre una actividad*}"
                        href="index.php?module=grid_view&action=EditActivityReport&record={$record['related_id']}&formodule={$record['tab_name']}&activityid={$record.crmid}&Ajax=true" title="Reportes y feedbacks"><span class="icon icon-02-iconos-chat"></span>
                    </a>
                </td>
            {/if}
            <td style="width: 8%">
                {if $record['how_to'] neq NULL}
                    <a class="btn btn-link" 
                    data-width="950" 
                    data-toggle="lightbox" 
                    data-parent="" 
                    data-gallery="remoteload"
                    data-title="¡Aprende como!"
                    href="index.php?module={$record['tab_name']}&action=AjaxDetailViewUtils&record={$record['how_to']}&function=GET-HOW-TO&Ajax=true"
                    title="¡Aprende como!"><i class="bi bi-question-square"></i></a>

                {/if}
                {*  btn-xs
                <a href="index.php?module={$MODULE}&action=EditView&record={$record.crmid}&return_module={$returnModule}&return_action={$returnAction}&return_viewname={$VIEW->getId ()}&tab={$TAB_NAME}"
                   class="btn btn-link" style="padding-left: 7px; padding-right: 7px;" title="Editar tarea"><i
                            class="fa fa-pencil"></i></a>
                *}
            </td>
            <td style="width: 8%">
                {if $record['eventstatus'] eq 'Planned' && false}
                    <form action="index.php" method="post" class="form-inline"
                        onsubmit="return confirm ('¿Estás seguro que quieres eliminar el registro seleccionado?');">
                        <input type="hidden" name="module" value="{$MODULE}" />
                        <input type="hidden" name="action" value="Delete" />
                        <input type="hidden" name="record" value="{$record.crmid}" />
                        <input type="hidden" name="return_action" value="{$returnAction}&tab={$TAB_NAME}" />
                        <input type="hidden" name="return_module" value="{$returnModule}" />
                        <input type="hidden" name="Ajax" value="true" />
                        <button title="Eliminar tarea" type="submit" class="btn btn-link"
                            style="padding-left: 7px; padding-right: 7px;">
                            <i class="fa fa-trash-o"></i></button>
                    </form>
                {/if}
            </td>
            {if ($record['eventstatus'] != 'Held') && false}
                <td style="width: 8%">
                    <form action="index.php" method="post" class="form-inline"
                        onsubmit="return confirm ('¿Estás seguro que quieres finalizar la tarea seleccionada?');">
                        <input type="hidden" name="module" value="Calendar" />
                        <input type="hidden" name="action" value="FinishTask" />
                        <input type="hidden" name="record" value="{$record.crmid}" />
                        <input type="hidden" name="return_action" value="{$returnAction}&tab={$TAB_NAME}" />
                        <input type="hidden" name="return_module" value="{$returnModule}" />
                        <input type="hidden" name="Ajax" value="true" />
                        <button title="Finalizar tarea" type="submit" class="btn btn-link"
                            style="padding-left: 7px; padding-right: 7px;"><i class="fa fa-check"></i></button>
                    </form>
                </td>
            {/if}
        </tr>
    </table>
{/block}