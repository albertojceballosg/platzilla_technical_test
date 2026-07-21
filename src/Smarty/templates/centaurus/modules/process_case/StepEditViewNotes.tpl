{assign var="PROCESS_CASES_UTILS_LOADED" value=false}
{math equation= rand() assign= "idProcessCase"}
<link rel="stylesheet" href="themes/{$THEME}/css/libs/datepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css" />
{* process_cases_utils.js se carga desde boilerplate.tpl cuando $INCLUDE_PROCESS_JS está activo *}
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<div id="StepEditViewNotes" class="row">
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
                <div class="col-md-6">
                    <div class="col-md-3">
                        <div class="label-input" style="margin-left: 2px !important;padding-left: 0!important;">
                            <label for="td_process" style="line-height: 1.25em !important;">
                                Proceso:
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-9 data-input" id="td_process"
                         style="display: block;padding-left: 0!important;">
                        <span id= "process_title" class="form-control b-left"
                              title="{$CASE_DETAILS['process']['process_description']}"
                              style="overflow-x: hidden;width: 100%;margin-left:0!important;">
                            {$CASE_DETAILS['process']['process_title']}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="col-md-2">
                        <div class="label-input" style="margin-left: 2px !important;">
                            <label for="td_process" style="line-height: 1.25em !important;">
                                Paso:
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-10 data-input" id="td_process" style="display: block;">
                        <span id= "step_title" class="form-control   b-left"
                              title="{$CASE_DETAILS['step']['step_description']}"
                              style="overflow-x: hidden;width: 100%;margin-left:0!important;">
                            {$CASE_DETAILS['step']['step_name']}
                        </span>
                    </div>
                </div>
                <form method="post" id="case-form-{$idProcessCase}" name="case-form-{$idProcessCase}" class="col-md-12">
                    {*   Caso *}
                    {if $CASE_DETAILS['step']['step_type'] eq 'MANUAL'}
                    <div class="col-md-12">
                        <div class="form-group col-md-6" style="padding-left: 0!important;">
                            <label for="datepickerDate">Fecha de inicio</label>
                            <div class="input-group">
                                <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                                <input class="form-control border" id="datepickerDate" name="date_start" type="text"
                                       value="{$CASE_DETAILS['start_date']}">
                            </div>
                        </div>
                        <div class="form-group col-md-6" style="padding-right: 0!important;">
                            <label for="datepickerDate">Hora de inicio</label>
                            <div class="input-group input-append bootstrap-timepicker">
                                <input type="text" class="form-control border" id="timepicker" name="time_start"
                                       value="{$CASE_DETAILS['start_time']}"
                                       placeholder="">
                                <span class="add-on input-group-addon border"><i class="fa fa-clock-o"></i></span>
                            </div>
                        </div>
                    </div>
                    {/if}
                    {*   Caso *}
                    <div class="col-md-12">
                        <input type="text" class="form-control border" value="{$CASE_DETAILS['case_title']}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label for="progress">&nbsp;Reporte de actividad del paso:</label>
                    </div>
                    <div class="col-md-12">
                        <textarea id="step_notes-{$idProcessCase}" name="step_notes" class="form-control border"
                                  placeholder="Escriba aquí el comentarios del caso, proceso o sobre el paso en particular"
                                  style="height: 200px;resize: none;">{$CASE_DETAILS['comment']}</textarea>
                    </div>

                    <div class="row-grid-view justify-content-center">
                        <input type="hidden" name="module" value="{$MODULE}">
                        <input type="hidden" name="action" value="AjaxDetailViewUtils">
                        <input type="hidden" name="record" value="{$CASE_DETAILS['process_casesid']}">
                        <input type="hidden" name="Ajax" value="true">
                        <input type="hidden" name="function" value="SAVE-STEP-NOTES">
                        <input type="hidden" name="step_type" value="{$CASE_DETAILS['step']['step_type']}">
                        <div class="col-xs-12 col-md-12" style="margin-top: 12px">
                            <header class="main-box-header clearfix">
                                <div class="action-bar text-center">
                                    <button type="button" class="btn btn-info" data-dismiss="modal"
                                            onclick="ProcessCaseUtils.saveComments (this, '{$idProcessCase}')">Guardar
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
</div>
{literal}
	<script type="text/javascript">
        jQuery ('#datepickerDate').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });

        jQuery ('#timepicker').timepicker ({
            minuteStep:   5,
            showSeconds:  true,
            showMeridian: false,
            disableFocus: false,
            showWidget:   true
        }).focus (function () {
            jQuery (this).next ().trigger ('click');
        });
        jQuery (document).ready (function () {
        	jQuery ('#datepickerDate').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
        	jQuery ('#timepicker').timepicker ({
        		minuteStep:   5,
        		showSeconds:  true,
        		showMeridian: false,
        		disableFocus: false,
        		showWidget:   true
        	}).focus (function () {
        		jQuery (this).next ().trigger ('click');
        	});
        });
	</script>
{/literal}