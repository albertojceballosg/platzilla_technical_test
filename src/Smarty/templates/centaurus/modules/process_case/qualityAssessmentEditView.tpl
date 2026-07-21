{assign var="PROCESS_CASES_UTILS_LOADED" value=false}
{math equation= rand() assign= "idProcessCase"}
<link rel="stylesheet" href="themes/{$THEME}/css/libs/datepicker.css" type="text/css"/>
<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css"/>
<link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css"/>
<link rel="stylesheet" href="modules/grid_view/grid-view.css" type="text/css"/>
{* process_cases_utils.js se carga desde boilerplate.tpl cuando $PROCESS_CASES_UTILS_LOADED está activo *}
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<div class="row">
    {*$CASE_DETAILS|@var_dump*}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="col-md-12" style="margin-bottom: 12px">
        <div class="row-grid-view justify-content-center">
            {if $CASE_DETAILS neq NULL}
                <div class="col-md-12">
                    {*  Proceso *}
                    <div class="col-md-6">
                        <div class="form-group input-group" id="td_process"
                             style="display: block;padding-left: 0!important;">
                            <label for="td_process" style="line-height: 1.25em !important;">Proceso:</label>
                            <span id="dtlview_process" class="form-control border"
                                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;
                               min-height: 50px;line-height: 1.35em !important;">
                            {$CASE_DETAILS['process']['process_title']}
                        </span>
                        </div>
                    </div>
                    {*  Paso *}
                    <div class="col-md-6">
                        <div class="form-group input-group" id="td_process" style="display: block;">
                            <label for="td_process" style="line-height: 1.25em !important;">Paso: </label>
                            <span class="form-control border"
                                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;
                              min-height: 50px;line-height: 1.35em !important;">
                            {$CASE_DETAILS['step']['step_name']}
                        </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" style="margin-top: 6px">
                    {* Criterio de calidad  *}
                    <div class="col-md-6">
                        <div class="form-group input-group" id="td_process"
                             style="display: block;padding-left: 0!important;">
                            <label for="td_process" style="line-height: 1.25em !important;">Criterio de calidad
                                aplicable:</label>
                            <span id="dtlview_process" class="form-control border"
                                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;
                              min-height: 50px;line-height: 1.35em !important;">
                            {$CASE_DETAILS['step']['quality_criteria_step']}
                        </span>
                        </div>
                    </div>
                    {*  Entregables, Resultados *}
                    <div class="col-md-6">
                        <div class="form-group input-group" id="td_process" style="display: block;">
                            <label for="td_process" style="line-height: 1.25em !important;">Entregables,
                                resultados: </label>
                            <span class="form-control border"
                                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;
                                  min-height: 50px;line-height: 1.35em !important;">
                            {$CASE_DETAILS['step']['step_deliverables']}
                        </span>
                        </div>
                    </div>
                </div>
                <form method="post" id="case-form-{$idProcessCase}" name="case-form-{$idProcessCase}" class="col-md-12">
                    <div class="row" style="margin-top: 6px">
                        <div class="col-md-6">
                            <div class="form-group field-container">
                                <label for="td_quality_valuation" style="line-height: 1.25em !important;">Valoración de la  calidad:</label>
                                <select id="quality_valuation-{$idProcessCase}" name="quality_valuation"
                                        title="Validación de la calidad"
                                        class="form-control border">
                                    <option value="" disabled="disabled">Valoración de la  calidad:</option>
                                    {foreach $QUALITY_VALUATION  as $qualityValuation}
                                        <option value="{$qualityValuation}"
                                                {if $CASE_DETAILS['quality_valuation'] eq $qualityValuation}selected="selected"{/if} >
                                            {$qualityValuation}</option>
                                    {/foreach}
                                </select>
                                <span class="help-block" style="color: red"></span>
                            </div>
                        </div>
                        {*  Validación de la calidad *}
                        <div class="col-md-6">
                            <label for="progress">&nbsp;Razonamiento de la valoración de calidad:</label>
                            <textarea id="reason_valuation-{$idProcessCase}" name="reason_valuation"
                                      title="Razonamiento de la validación de calidad"
                                      class="form-control border"
                                      placeholder="Escriba aquí comentario sobre la evaluación de la calidad."
                                      style="height: 200px;resize: none;">{$CASE_DETAILS['reason_valuation']}</textarea>
                            <span class="help-block" style="color: red"></span>
                        </div>
                        {* Razonamiento de la validación de calidad *}
                    </div>

                    <div class="row-grid-view justify-content-center">
                        <input type="hidden" name="module" value="{$MODULE}">
                        <input type="hidden" name="action" value="AjaxDetailViewUtils">
                        <input type="hidden" name="record" value="{$CASE_DETAILS['process_casesid']}">
                        <input type="hidden" name="related_record" value="{$RELATED_RECORD}">
                        <input type="hidden" name="function" value="SAVE-QUALITY-ASSESSMENT">
                        <input type="hidden" name="code_step" value="{$CASE_DETAILS['step']['cod_process_steps']}">
                        <input type="hidden" name="Ajax" value="true">
                        <div class="col-xs-12 col-md-12" style="margin-top: 12px">
                            <header class="main-box-header clearfix">
                                <div class="action-bar text-center">
                                    <button type="button" class="btn btn-info" data-dismiss=""
                                            onclick="ProcessCaseUtils.saveQualityAssessment(this, '{$idProcessCase}')">Guardar
                                    </button>&nbsp;
                                </div>
                            </header>
                        </div>
                    </div>
                </form>
            {else}
                <div class="col-md-12" style="min-height: 120px">&nbsp;</div>
            {/if}
        </div>
    </div>
</div>