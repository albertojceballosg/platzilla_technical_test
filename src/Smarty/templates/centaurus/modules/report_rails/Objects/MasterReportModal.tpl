<div class="modal fade" id="new-calculated-modal" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php" method="post" class="form" id="master-report-form-__ID__">
                <input type="hidden" id="module" name="module" value="report_rails">
                <input type="hidden" id="action" name="action" value="AjaxRailsUtils">
                <input type="hidden" id="function" name="function" value="CREATE_MASTER_REPORT">
                <input type="hidden" id="Ajax" name="Ajax" value="true">
                <input type="hidden" id="id" name="id_modal" value="__ID__">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Crear informe semanal</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {* Agents *}
                        <div class="col-md-6">
                            <div class="label-input" style="text-align: left;">
                                <label for="performance-from">Agente&nbsp;<span class="required">*</span></label>
                            </div>
                            {*$AVAILABLE_AGENTS|var_dump*}
                            <div id="mr-div-report_agent" class="form-group field-container">
                                <select class="form-control" id="report_agent-__ID__" name="report_agent" title="Agente"
                                        onchange="ReportRailesUtils.selectAgent(this, __ID__)">
                                    {if $AVAILABLE_AGENTS neq NULL}
                                        <option value="">Seleccionar agente</option>
                                        {foreach $AVAILABLE_AGENTS as $agent}
                                            <option value="{$agent->getId()}">{$agent->getUserName()}
                                                : {$agent->getName()}</option>
                                        {/foreach}
                                    {else}
                                        <option value="">No hay agentes</option>
                                    {/if}
                                </select>
                                <span id="mr-report_agent" class="help-block"></span>
                            </div>
                        </div>
                        {* instancia *}
                        <div class="col-md-6">
                            <div class="label-input" style="text-align: left;">
                                <label for="news-from">Instancia&nbsp;<span class="required">*</span></label>
                            </div>
                            <div id="mr-div-report_instance" class="form-group field-container">
                                <select class="form-control"
                                        name="report_instance"
                                        onchange="ReportRailesUtils.selectInstance(this, __ID__, '')"
                                        title="Instancia" id="report_instance-__ID__">
                                    <option value="">Seleccionar Instancia</option>
                                </select>
                                <span id="mr-report_instance" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                {* Periodo *}
                <div class="row">
                    {* Periodo *}
                    <div class="col-md-12">
                        <div class="label-input" style="text-align: left;">
                            <label for="performance-from">Periodo&nbsp;<span class="required">*</span></label>
                        </div>
                        {*$AVAILABLE_AGENTS|var_dump*}
                        <div id="mr-div-report_week" class="form-group field-container">
                            <select id="report_week-__ID__"
                            name="report_week" class="form-control" title="Seleccionar semana">
                            <option value="">Seleccionar Semana</option>
                            {*html_week_days_select init_day=$FIRST_DAY offset_month=$OFFSET_MONTH  selected_week=$SELECTED_WEEK*}
                            </select>
                            <span id="mr-report_week" class="help-block"></span>
                        </div>
                    </div>
                    {* instancia
                    <div class="col-md-4">&nbsp;</div>
                    *}
                </div>
                {* title *}
                    <div class="row">
                        {* Periodo *}
                        <div class="col-md-12">
                            <div class="label-input" style="text-align: left;">
                                <label for="performance-from">Título para el reporte&nbsp;<span class="required">*</span></label>
                            </div>
                            {* *}
                            <div id="mr-div-report_title" class="form-group field-container">
                                <input type="text" id="report_title-__ID__" name="report_title" class="form-control" title="Título para el reporte">
                                <span id="mr-report_title" class="help-block"></span>
                            </div>
                        </div>
                        {*
                         <div class="col-md-4">&nbsp;</div>
                                      *}
                    </div>
                <div class="row">
                    <p class="col-xs-12">¿Qué quieres?</p>
                    <div class="form-group col-xs-12 field-container">
                        <div class="input-group" style="width: 100%;">
                            <div class="radio-group">
                                <label>
                                    <input type="radio" id="report_action" name="report_action-__ID__"
                                           value="CREATE_MASTER_REPORT" checked="checked"
                                           onchange="ReportRailesUtils.setReportPattern (this, __ID__);"/>
                                    &nbsp;Crear un informe nuevo
                                </label>
                            </div>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" id="report_action" name="report_action-__ID__"
                                           value="DUPLICATE_MASTER_REPORT"
                                           onchange="ReportRailesUtils.setReportPattern (this, __ID__);"/>&nbsp;
                                    Crear un informe basado en un patrón
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="report-pattern-__ID__" class="row hide">
                    <p class="col-xs-12">¿Cuál es el informe patrón?</p>
                    <div id="mr-div-master_report" class="col-xs-11" style="height: 100%; padding: 0px 12px">
                        <select id="master_report-__ID__"
                                name="master_report" class="form-control"
                                title="Seleccionar informe semanal">
                            {if $MASTER_REPORT neq NULL}
                                <option value="">Seleccionar agente</option>
                                {foreach $MASTER_REPORT as $report}
                                    <option value="{$report->getId()}"> {$report->getDescription()|truncate:60|cat:'...'}</option>
                                {/foreach}
                            {else}
                                <option value="">No informes disponibles</option>
                            {/if}

                        </select>
                        <span id="mr-master_report" class="help-block"></span>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button"
                    onclick="ReportRailesUtils.createMasterReport(__ID__);"
                    class="btn btn-primary">Crear</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Cancelar
            </button>
        </div>
        </form>
    </div>
</div>
</div>