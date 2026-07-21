{strip}
    <div class="main-box-header clearfix" style="padding: 0 1.2em">
        <div class="row" style="margin-top: 1.5em">
            <form role="form" method="post" id="actions_progress_form-{$actionId}">
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="flmodule" value="{$fromModule}">
                <input type="hidden" id="function-name-{$actionId}" name="function" value="{$ajaxFuntion}">
                <input type="hidden" name="action" value="{$ajaxFile}">
                <input type="hidden" name="Ajax" value="true">
                <input type="hidden" name="total_records" value="{$totalRecords}">
                <input type="hidden" name="page" value="{$page}">
                <input type="hidden" name="hometabid" value="{$actionId}">
                <input type="hidden" name="calendar_view" id="calendar_view-{$actionId}" value="NO">
                <input type="hidden" id="reported_day-{$actionId}"
                       value="{if $reportedDays neq NULL}{$reportedDays}{/if}">
                <div class="col-md-12 col-sm-12 col-xs-12" {if $actionId neq NULL}style="margin-top: 0"{/if}>
                    {if $actionId neq NULL} {/if}
                    <div id="col-lg-3 col-md-3 col-xs-3 btn-toolbar-{$actionId}" class="btn-toolbar" role="toolbar">
                        {if $AVAILABLE_USERS neq NULL}
                            <div class="btn-group" style="margin-left: 0.125em!important;margin-right: 2px">
                                <button id="btn-group-user-{$actionId}" type="button"
                                        class="btn btn-primary dropdown-toggle"
                                        title="asignar tarea"
                                        style="font-size: 15px!important;margin-left: 0.1em"
                                        data-toggle="dropdown">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    &nbsp;Filtrar por usuario&nbsp;
                                    <span class="caret"></span>
                                </button>
                                <ul id="daily-matrix-user-{$actionId}" class="dropdown-menu scroll-user-menu"
                                    role="menu">
                                    {if $AVAILABLE_USERS|count gte 1}
                                        {foreach $AVAILABLE_USERS as $id => $user}
                                            <li {if (in_array($id, $USER_IDS))}class="active" {/if}>
                                                <a href="#" title="{$user['name']}" rel="{{$id}}"
                                                   onclick="DailyMatrixUtls.selectedUser (event, this, '{$actionId}')">
                                                    <img class="img-circle" style="width: 60%; height: 60%"
                                                         data-src="{$user['avatar']}" alt="{$user['name']}"
                                                         src="{$user['avatar']}">
                                                </a>
                                            </li>
                                        {/foreach}
                                    {else}
                                        <li class="list-btn-header" title="Usuarios invitados"
                                            style="text-align: center">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;
                                            <small style="text-align: center; padding-left: 2px">No hay
                                                usuarios!</small>
                                        </li>
                                    {/if}
                                </ul>
                                <br>
                            </div>
                        {/if}
                        {* date period filters *}
                        <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-{$actionId}"
                             style="margin-bottom: 4px; margin-right: 0!important;">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa  fa-clock-o"></i>
                                </div>
                                <select id="period-dates-{$actionId}"
                                        onchange="DailyMatrixUtls.selectedPeriod (this, '{$actionId}')"
                                        name="periodtask"
                                        class="form-control" title="Seleccionar periodo">
                                    {if $PERIOD_DATES neq NULL}
                                        <option value="">Seleccionar periodo</option>
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
                                <input id="start-date-{$actionId}" type="text" name="datestart"
                                       readonly="readonly"
                                       class="form-control from-field daily-matrix-date-{$actionId} date start-date "
                                       value=""
                                       style="margin: 0!important;" placeholder="Desde"/>
                            </div>
                        </div>
                        {* date  filters *}
                        <div class="btn-group  col-lg-2 col-md-2 col-xs-2  hide"
                             style="margin-bottom: 4px;margin-right: 0!important;">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input id="end-date-{$actionId}" type="text" name="duedate"
                                       readonly="readonly"
                                       class="form-control daily-matrix-date-{$actionId} date end-date"
                                       value=""
                                       style="margin: 0!important;" placeholder="Hasta"/>
                            </div>
                        </div>
                        <div class="pull-left" style="margin-left: 2px">
                            <div class="btn-group">
                                <button name="submitSearch" id="submitSearch" class="btn btn-primary"
                                        title="{$searchTitle}"
                                        data-pagination-page="{$page}"
                                        onclick="DataViewUtils.goToPage (event, this, '{$actionId}')" type="button">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </button>
                                {if $hasOtherButton eq 'YES'}
                                <button type="button" class="btn {$otherButtonClass}"
                                        style="margin-left: 2px!important;"
                                        title="{$otherButtonToltip}"
                                        data-action=""
                                        onclick="{$otherButtonAction}(event, this, '{$actionId}')">
                                    <i class="fa {$otherButtonIcon}" aria-hidden="true"></i>{$otherButtonTitle}
                                </button>
                                    <input type="hidden" name="invitees_id" value="">
                                {/if}
                                {if $hasThirdButton eq 'YES'}
                                    <button type="button" class="btn {$thirdButtonClass}"
                                            style="margin-left: 2px!important;"
                                            title="{$thirdButtonToltip}"
                                            data-action="{$thirdButtonData}"
                                            onclick="{$thirdButtonAction}(event, this, '{$actionId}')">
                                        <i class="fa {$thirdButtonIcon}" aria-hidden="true"></i>{$thirdButtonTitle}
                                    </button>
                                {/if}
                            </div>
                        </div>
                        {* action button*}
                        <div class="btn-group pull-right" style="margin-bottom: 2px">
                            {block name= "daily-report"}{/block}
                            {block name="part_work"}{/block}
                        </div>
                    </div>
                </div>
            </form>
            <span id="help-user-{$actionId}" class="help-block"
                  style="color: red; display: inline-block!important;margin: 0 0.9em"><b>Usuario:</b>&nbsp;{$USER_NAME}
            </span>
        </div>
    </div>
{/strip}