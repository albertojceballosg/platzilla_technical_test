<div class="row" style="margin-top: 1.5em">
    <form role="form" id="process-panel-form-{$idPanelProcess}">
        <input type="hidden" name="record" value="">
        <input type="hidden" name="module" value="Home">
        <input type="hidden" name="function" value="PROCESS-PANEL-SEARCH">
        <input type="hidden" name="action" value="AjaxHomeUtils">
        <input type="hidden" name="Ajax" value="true">
        <input type="hidden" name="hometabid" value="{$idPanelProcess}">
        <input type="hidden" id="reported_day-{$idPanelProcess}" value="{$REPORTED_DAYS}">
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 0">
            {if $TAB_HOME_ID neq NULL} {/if}
            <div id="col-lg-3 col-md-3 col-xs-3 btn-toolbar-{$idPanelProcess}" class="btn-toolbar" role="toolbar">
                {* date period filters *}
                <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-{$idPanelProcess}"
                     style="margin-bottom: 4px; margin-right: 0!important;">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa  fa-clock-o"></i>
                        </div>
                        <select id="period-dates-{$idPanelProcess}"
                                onchange="ProcessCasesUtils.selectedPeriod (this, '{$idPanelProcess}')"
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
                        <input id="start-date-{$idPanelProcess}" type="text" name="datestart"
                               readonly="readonly"
                               class="form-control from-field process-control-date-{$idPanelProcess} date start-date "
                               value=""
                               style="margin: 0!important;" placeholder="Desde"/>
                    </div>
                </div>
                {* date  filters *}
                <div class="btn-group  col-lg-2 col-md-2 col-xs-2  hide"
                     style="margin-bottom: 4px;margin-right: 0!important;">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input id="end-date-{$idPanelProcess}" type="text" name="duedate"
                               readonly="readonly"
                               class="form-control process-control-date-{$idPanelProcess} date end-date"
                               value=""
                               style="margin: 0!important;" placeholder="Hasta"/>
                    </div>
                </div>
                <div class="pull-left" style="margin-left: 2px">
                    <button name="submitSearch" id="submitSearch" class="btn btn-primary"
                            title="Buscar Procesos"
                            onclick="ProcessCasesUtils.searchProcessCase(this, '{$idPanelProcess}')" type="button">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<div id="process-panel-container-{$idPanelProcess}" class="main-box-header clearfix" style="padding: 0 0.65em">
    {block name="panel_content"}{/block}
</div>