{math equation= rand() assign= "idBlock"}
<div class="row">
    <div class="col-md-5">
        <h1 style="margin-left: -3px;font-weight: bold">{$VIEW_TITLE}</h1></div>
    <div class="col-md-7">
        <form role="form" method="post" id="weekly-status_form-{$idBlock}">
            <input type="hidden" name="module" value="Home">
            <input type="hidden" name="action" value="AjaxHomeUtils">
            <input type="hidden" name="function" value="FETCH-WEEKLY-REPORT">
            <input type="hidden" name="hometabid" value="{$idBlock}">
            <input type="hidden" name="Ajax" value="true">
            <input type="hidden" id="tab_summary-{$idBlock}"  value="SUMMARY">
            <input type="hidden" id="tab_performance-{$idBlock}"  value="CONTEXT">
            <input type="hidden" id="is_instance-{$idBlock}"  value="{if $IS_INSTANCE}yes{else}no{/if}">
            <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 0">
                {if !$IS_INSTANCE}
                    {*  agents *}
                    <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-1769"
                         style="margin-bottom: 4px; margin-right: 0!important;">
                        <select class="form-control" id="report_agent-{$idBlock}" name="report_agent" title="Agente"
                                onchange="ReportRailesUtils.selectAgent(this)">
                            {if $AVAILABLE_AGENTS neq NULL}
                                <option value="">Seleccionar agente</option>
                                {foreach $AVAILABLE_AGENTS as $agent}
                                    <option value="{$agent->getId()}"
                                            {if $AGENT_ID eq $agent->getId()}selected{/if} >{$agent->getUserName()}
                                        : {$agent->getName()}</option>
                                {/foreach}
                            {else}
                                <option value="">No hay agentes</option>
                            {/if}
                        </select>
                        <span class="help-block" style="color: red"></span>
                    </div>
                    {*  instance *}
                    <div class="col-lg-4 col-md-4 col-xs-4 btn-group"
                         style="margin-bottom: 4px; margin-right: 0!important;">
                        <select class="form-control" name="report_instance"
                                onchange="ReportRailesUtils.selectInstance(this, {$idBlock}, 'Home')"
                                title="Instancia" id="report_instance-{$idBlock}">
                            {if $INSTANCES neq NULL}
                                {foreach $INSTANCES as $id => $instance}
                                    {if $instance->getAdministrator() neq NULL}
                                        {if ($AGENT_INSTANCES neq NULL)}
                                            {assign var="isSelected" value=null}
                                            {foreach $AGENT_INSTANCES as $agentInstance}
                                                {if ($agentInstance->getCode() eq $instance->getCode())}
                                                    {assign var="isSelected" value='selected'}
                                                {/if}
                                            {/foreach}
                                        {elseif $INSTANCE_ID neq NULL}
                                            {if $instance->getCode() eq $INSTANCE_ID}
                                                {assign var="isSelected" value='selected'}
                                            {/if}
                                        {else}
                                            {assign var="isSelected" value=null}
                                        {/if}
                                        <option value="{$instance->getCode()};{$instance->getAdministrator()->getEmail()}" {$isSelected}>
                                            {$instance->getAdministrator()->getFirstName()}  {$instance->getAdministrator()->getLastName()}
                                        </option>
                                    {/if}
                                {/foreach}
                            {/if}
                        </select>
                        <span class="help-block" style="color: red"></span>
                    </div>
                    {*  $period *}
                {/if}
                <div class="{if !$IS_INSTANCE}col-lg-4 col-md-4 col-xs-4 {else}col-lg-8 col-md-8 col-xs-8 {/if}btn-group date-time-1769"
                     style="margin-bottom: 4px; margin-right: 0!important;">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa  fa-clock-o"></i></div>
                        <select id="report_week-{$idBlock}"
                                name="selectedWeek" class="form-control" title="Seleccionar semana">
                            <option value="">Seleccionar Semana</option>
                            {if $AVAILABLE_REPORTS neq NULL}
                                {$AVAILABLE_REPORTS}
                            {else}
                                {html_week_days_select init_day=$FIRST_DAY offset_month=$OFFSET_MONTH  selected_week=$SELECTED_WEEK}
                            {/if}
                        </select>
                        <span class="help-block" style="color: red"></span>
                    </div>
                </div>
                <div class="pull-left" style="margin-left: 2px">
                    <div class="btn-group">
                        <button name="submitSearch" id="submitSearch" class="btn btn-primary" title=""
                                data-pagination-page="1"
                                onclick="ReportRailesUtils.fetchWeeklyReport (this, '{$idBlock}')"
                                type="button">
                            <i class="fa fa-search" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>