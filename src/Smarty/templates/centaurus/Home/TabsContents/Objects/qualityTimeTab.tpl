{math equation= rand() assign= "idQualityTime"}
<div class="row" style="margin-top: 1.5em">
    <form role="form" id="process-panel-form-{$idQualityTime}">
        <input type="hidden" name="record" value="">
        <input type="hidden" name="module" value="Home">
        <input type="hidden" name="function" value="PROCESS-QUALITY-SEARCH">
        <input type="hidden" name="action" value="AjaxHomeUtils">
        <input type="hidden" name="Ajax" value="true">
        <input type="hidden" name="hometabid" value="{$idQualityTime}">
        <input type="hidden" id="users-{$idQualityTime}"   name="users" value="{$USER_IDS}">
        <input type="hidden" id="reported_day-{$idQualityTime}" value="{$REPORTED_DAYS}">
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 0">
            {if $TAB_HOME_ID neq NULL} {/if}
            <div id="col-lg-3 col-md-3 col-xs-3 btn-toolbar-{$idQualityTime}" class="btn-toolbar" role="toolbar">
                {* users*}
                {if $AVAILABLE_USERS neq NULL}
                    <div class="btn-group" style="margin-left: 0.125em!important;margin-right: 2px">
                        <button id="btn-group-user-{$idQualityTime}" type="button"
                                class="btn btn-primary dropdown-toggle"
                                title="asignar tarea"
                                style="font-size: 15px!important;margin-left: 0.1em"
                                data-toggle="dropdown">
                            <i class="fa fa-user" aria-hidden="true"></i>
                            &nbsp;Filtrar por usuario&nbsp;
                            <span class="caret"></span>
                        </button>
                        <ul id="process-quality-user-{$idQualityTime}" class="dropdown-menu scroll-user-menu"
                            role="menu">
                            {if $AVAILABLE_USERS|count gte 1}
                                {foreach $AVAILABLE_USERS as $id => $user}
                                    <li {if $id eq $USER_IDS}class="active" {/if}>
                                        <a href="#" title="{$user['name']}" rel="{{$id}}"
                                           onclick="ProcessCasesUtils.selectedUser (event, this, '{$idQualityTime}')">
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
                <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-{$idQualityTime}"
                     style="margin-bottom: 4px; margin-right: 0!important;">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa  fa-clock-o"></i>
                        </div>
                        <select id="period-dates-{$idQualityTime}"
                                onchange="ProcessCasesUtils.selectedPeriod (this, '{$idQualityTime}')"
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
                        <input id="start-date-{$idQualityTime}" type="text" name="datestart"
                               readonly="readonly"
                               class="form-control from-field process-control-date-{$idQualityTime} date start-date "
                               value=""
                               style="margin: 0!important;" placeholder="Desde"/>
                    </div>
                </div>
                {* date  filters *}
                <div class="btn-group  col-lg-2 col-md-2 col-xs-2  hide"
                     style="margin-bottom: 4px;margin-right: 0!important;">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input id="end-date-{$idQualityTime}" type="text" name="duedate"
                               readonly="readonly"
                               class="form-control process-control-date-{$idQualityTime} date end-date"
                               value=""
                               style="margin: 0!important;" placeholder="Hasta"/>
                    </div>
                </div>
                <div class="pull-left" style="margin-left: 2px">
                    <button name="submitSearch" id="submitSearch" class="btn btn-primary"
                            title="Buscar Procesos"
                            onclick="ProcessCasesUtils.searchProcessCase(this, '{$idQualityTime}')" type="button">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
        <span id="help-user-{$idQualityTime}" class="help-block"
              style="color: red; display: inline-block!important;margin: 0 0.9em">
                <b>Usuario(s):</b>&nbsp;{$USER_NAME}</span>
        {* user and period time*}
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px;margin-left: -10px">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                Proceso <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li class="active">
                                    <a href="#" title="Todos los procesos"
                                       onclick="ProcessCasesUtils.selectedProcessType (this, '{$idQualityTime}')"
                                       rel="">
                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                        &nbsp;Todos los procesos
                                    </a>
                                </li>
                                {foreach $PROCESS_TYPES as $id => $process}
                                    <li class="">
                                        <a href="#" title="{$process['process_type']}"
                                           onclick="ProcessCasesUtils.selectedProcessType (this, '{$idQualityTime}')"
                                           rel="{$process['process_type']}">
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            &nbsp;{$process['process_type']}
                                        </a>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                        <select id="quality-process-{$idQualityTime}"
                                onchange="ProcessCasesUtils.selectedProcess (this, '{$idQualityTime}')"
                                name="quality_process"
                                class="form-control" title="Seleccionar PROCESO">
                            {if $AVAILABLE_PROCESS neq NULL}
                                <option value="" data-type="BLOCK">Seleccionar proceso</option>
                                {foreach $AVAILABLE_PROCESS as $process}
                                    <option class="" value="{$process['processid']}" data-type="{$process['process_type']}"
                                            {if $process['processid'] eq $PROCESS_ID}selected{/if} >
                                        {{$process['process_title']}}
                                    </option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
            </div>
            {* Process types and process*}
    </form>

</div>
<div id="process-panel-container-{$idQualityTime}" class="main-box-header clearfix" style="padding: 0 0.65em">
    {block name="quality_content"}{/block}
</div>