{strip}
    {if $REPORT neq NULL}
        {assign var='taskId' value=$REPORT->getActivityId ()}
        {assign var='taskDescription' value=''}
        {if $PRESELECTED_TASK neq NULL}
            {assign var='taskDescription' value=$PRESELECTED_TASK->getDescription()}
        {/if}
        {assign var='taskReport' value=$REPORT->getReport ()}
        {assign var='reportTitle' value=$REPORT->getTitle ()}
        {assign var='reportId' value=$REPORT->getId()}
        {assign var='taskProgress' value=$REPORT->getProgress()}
        {assign var='reportTime' value=$REPORT->getTimeDuration()}
        {assign var='reportOn' value=$REPORT->getReportOn()}
    {elseif $PRESELECTED_ACTIVITY_ID neq NULL && $PRESELECTED_TASK neq NULL}
        {assign var='taskId' value=$PRESELECTED_ACTIVITY_ID}
        {assign var='taskDescription' value=$PRESELECTED_TASK->getDescription()}
        {assign var='taskReport' value=null}
        {assign var='reportTitle' value=null}
        {assign var='reportId' value=null}
        {* Para nuevos reportes, inicializar el progreso en 0 relativo a la tarea seleccionada *}
        {assign var='taskProgress' value='0'}
        {assign var='reportTime' value=null}
        {assign var='reportOn' value='TASK'}
    {elseif $TASK_ID neq NULL}
        {assign var='taskId' value=$TASK_ID}
        {assign var='taskDescription' value=null}
        {assign var='taskReport' value=null}
        {assign var='taskTitle' value=null}
        {assign var='reportId' value=null}
        {* Para nuevos reportes, inicializar el progreso en 0 *}
        {assign var='taskProgress' value='0'}
        {assign var='reportTime' value=null}
        {assign var='reportOn' value=null}
        {assign var='reportTitle' value=null}
    {else}
        {assign var='taskId' value=null}
        {assign var='taskDescription' value=null}
        {assign var='taskReport' value=null}
        {assign var='taskTitle' value=null}
        {assign var='reportId' value=null}
        {* Para nuevos reportes, inicializar el progreso en 0 *}
        {assign var='taskProgress' value='0'}
        {assign var='reportTime' value=null}
        {assign var='reportOn' value=null}
        {assign var='reportTitle' value=null}
    {/if}

    {math equation= rand() assign= "idReportAndFeedback"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css" />
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <ul class="nav nav-tabs">
                <li {if $ACTIVE_TAB neq 'FEEDBACK'}class="active" {/if}><a href="#ACTIVITY_REPORT" data-toggle="tab">
                        <i class="fa fa-comment-o" aria-hidden="true"></i>&nbsp;Reportes
                    </a>
                </li>
                <li {if $ACTIVE_TAB eq 'FEEDBACK'}class="active" {/if}><a href="#ACTIVITY_FEEDBACK" data-toggle="tab">
                        <i class="fa fa-comments-o" aria-hidden="true"></i>&nbsp;Feedback
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="tab-content" style="margin-top: 2em">
                <div class="tab-pane fade {if $ACTIVE_TAB neq 'FEEDBACK'}in active{/if}" id="ACTIVITY_REPORT">
                    {* Reports Form *}
                    <form method="post" id="arctity-report-form-{$idReportAndFeedback}"
                        name="arctity-report-form-{$idReportAndFeedback}">
                        <div class="row-grid-view justify-content-center">
                            {if $FL_MODULE eq 'orden_de_trabajo'}
                                <div class="col-sm-12 col-md-12 col-lg-12">
                                    <div id="gv-div-task" class="form-group">
                                        <label for="progress"><span style="color: red;">*</span>&nbsp;Reporte sobre:</label>
                                        <select id="report-on" title="Reportar sobre" name="reportOn"
                                            class="form-control border"
                                            onchange="ReprtActivityUtils.reportOn(this, '{$idReportAndFeedback}')"
                                            style="margin-bottom: 1em">
                                            <option vallue="" {if $reportOn eq NULL}selected{/if}>Seleccione ...</option>
                                            <option value="TASK" {if $reportOn eq 'TASK'}selected{/if}>Tarea</option>
                                            <option value="JOB" {if $reportOn eq 'JOB'}selected{/if}>Trabajo</option>
                                        </select>
                                    </div>
                                </div>
                            {/if}
                            <div class="col-md-6">
                                <div id="gv-div-task" class="form-group">
                                    <label for="progress"><span style="color: red;">*</span>&nbsp;Actividad:</label>
                                    <select id="task-{$idReportAndFeedback}" title="{if $reportOn neq 'JOB'}La tarea{/if}"
                                        name="task" class="form-control border "
                                        {if $AVAILABLE_TASK eq NULL || $ACTION_REPORT eq 'edit'} disabled="disabled" {/if}
                                        onchange="ReprtActivityUtils.setActivity(this)">
                                        <option value="">Seleccione ...</option>
                                        {foreach $AVAILABLE_TASK as $task}
                                            <option value="{$task->getActivityId ()}"
                                                {if $taskId neq NULL && $taskId == $task->getActivityId()}selected="selected"
                                                {/if}
                                                {if ($FL_MODULE eq 'orden_de_trabajo') && ($REPORT eq NULL) && ($reportOn eq NULL || $reportOn eq 'JOB')}
                                                disabled="disabled" {/if}>
                                                <small><b>{if ($FL_MODULE eq 'orden_de_trabajo')}
                                                            {$MOD.EVENT_STATUS[$task->getActivityType()]}
                                                        {else}
                                                            Acción
                                                        {/if}
                                                    </b></small>
                                                :&nbsp;{$task->getSubject()}
                                            </option>
                                        {/foreach}
                                    </select>
                                    <span id="gv-task" class="help-block"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="gv-div-progress" class="form-group" style="display: block">
                                    <label for="progress">Progreso: <span id="progress-display">{$taskProgress}</span>
                                        %</label>
                                    <input type="range" id="progress" name="progress" value="{$taskProgress}"
                                        class="slider border" min="0" max="100"
                                        oninput="ReprtActivityUtils.setProgress(this)">
                                    <span id="gv-progress" class="help-block"></span>
                                </div>
                            </div>
                        </div>
                        <div id="activity-description"
                            class="row-grid-view justify-content-center {if ($taskDescription eq NULL && $reportTitle eq NULL)}hidden{/if}">
                            <div class="col-md-12">
                                <div id="well-{$idReportAndFeedback}" class="well well-sm">
                                    {if $reportOn eq 'JOB'}
                                        {$reportTitle nofilter}
                                    {else}
                                        {$taskDescription|unescape:'html' nofilter}
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <div class="row-grid-view justify-content-center">
                            <div id="gv-div-title" class="form-group col-md-9 field-container"
                                style="margin-top: 2px!important;margin-bottom: 2px!important;">
                                <label for="title"><span style="color: red;">*</span>&nbsp;Título del reporte:</label>
                                <input type="text" name="title" id="title" title="título del reporte"
                                    class="form-control border" value="{$reportTitle}" placeholder="Título del reporte">
                                <span id="gv-title" class="help-block"></span>
                            </div>
                            <div id="gv-div-activity-report-date" class="form-group col-md-3 field-container"
                                style="margin-top: 2px!important;margin-bottom: 2px!important;">
                                <label for="activity_report_date">Fecha del avance:</label>
                                <input type="text" name="activity_report_date_display" id="activity_report_date"
                                    title="Fecha a la que corresponde el avance"
                                    class="form-control border"
                                    autocomplete="off"
                                    placeholder="{$USER_DATE_FORMAT|default:'dd/mm/yyyy'}"
                                    value="{if $REPORT neq NULL && $REPORT->getActivityReportDate() neq NULL}{$REPORT->getActivityReportDate()|date_format:'%d/%m/%Y'}{/if}">
                                <input type="hidden" name="activity_report_date" id="activity_report_date_db">
                                <span id="gv-activity-report-date" class="help-block"></span>
                            </div>
                        </div>
                        <div class="row-grid-view justify-content-center">
                            <div id="gv-div-timeduration" class="form-group col-md-6 field-container"
                                style="margin-top: 2px!important;margin-bottom: 2px!important;">
                                <label for="timeduration" id="timeduration-label">
                                    <span style="color: red;">*</span>&nbsp;Unidades utilizadas
                                    {if $PRESELECTED_TASK neq NULL && $PRESELECTED_TASK->getEstimatedTimeUnit() neq NULL && $PRESELECTED_TASK->getEstimatedTimeUnit() neq ''}
                                        [{$PRESELECTED_TASK->getEstimatedTimeUnit()}]
                                    {/if}:
                                </label>
                                <input type="text" name="timeduration" id="timeduration"
                                    title="Unidades utilizadas en la actividad" class="form-control border"
                                    value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$reportTime|string_format:"%.2f"|replace:'.':','}{else}{$reportTime|string_format:"%.2f"}{/if}"
                                    data-format="{$NUMBERING_FORMAT}"
                                    onkeydown="ReprtActivityUtils.normalizeReportTime (this, event);"
                                    onfocus="ReprtActivityUtils.cleanNumberOnFocus(this);"
                                    onblur="ReprtActivityUtils.formatNumberOnBlur(this);" placeholder="Unidades utilizadas">
                                <span id="gv-timeduration" class="help-block"></span>
                            </div>
                            <div id="gv-div-actualcost" class="form-group col-md-6 field-container"
                                style="margin-top: 2px!important;margin-bottom: 2px!important;">
                                <label for="actualcost"><span style="color: red;">*</span>&nbsp;Costo Real:</label>
                                <div class="input-group">
                                    <span class="input-group-addon border">$</span>
                                    <input type="text" name="actualcost" id="actualcost" title="Costo real de la actividad"
                                        class="form-control border"
                                        value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$reportCost|string_format:"%.2f"|replace:'.':','}{else}{$reportCost|string_format:"%.2f"}{/if}"
                                        data-format="{$NUMBERING_FORMAT}"
                                        onkeydown="ReprtActivityUtils.normalizeReportTime (this, event);"
                                        onfocus="ReprtActivityUtils.cleanNumberOnFocus(this);"
                                        onblur="ReprtActivityUtils.formatNumberOnBlur(this);" placeholder="0.00" required>
                                </div>
                                <small class="form-text text-muted">Costo real de la actividad requerido</small>
                                <span id="gv-actualcost" class="help-block"></span>
                            </div>
                        </div>
                        <div class="row-grid-view justify-content-center"
                            style="margin-top: 2px!important;margin-bottom: 2px!important;">
                            <div id="gv-div-description" class="form-group col-md-12 field-container">
                                <label for="progress"><span style="color: red;">*</span>&nbsp;Reporte de actividad:</label>
                                <textarea id="description" style="" name="description" class="form-control border"
                                    title="descripción">{$taskReport}</textarea>
                                <span id="gv-description" class="help-block"></span>
                            </div>
                        </div>
                        {* Sección de adjuntos *}
                        <div class="row-grid-view justify-content-center {if $taskId eq NULL}hidden{/if}"
                            id="attachments-section-report"
                            style="margin-top: 11.25px!important;margin-bottom: 10px!important;">
                            <div class="col-md-12">
                                <label><i class="fa fa-paperclip"></i>&nbsp;Evidencias del avance reportado:</label>
                                <div style="margin-top: 5px;">
                                    {if $REPORT neq NULL}
                                        {* Modo edición: mostrar botón activo *}
                                        <button type="button" class="btn btn-sm btn-warning"
                                            onclick="ReprtActivityUtils.uploadTaskAttachments('task-{$idReportAndFeedback}')"
                                            style="margin-bottom: 10px;">
                                            <i class="fa fa-upload"></i>&nbsp;Adjuntar evidencias del avance
                                        </button>
                                        <ul id="task-attachments-list" style="list-style: none; padding: 0; margin: 0;">
                                            {if $TASK_ATTACHMENTS neq NULL && count($TASK_ATTACHMENTS) > 0}
                                                {foreach $TASK_ATTACHMENTS as $attachment}
                                                    <li
                                                        style="border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 12px; margin-bottom: 5px; background-color: #fff;">
                                                        <a href="{$attachment.uri}" title="{$attachment.name}" target="_blank"
                                                            style="text-decoration: none; color: #007bff;">
                                                            <i class="fa fa-file-o"></i>
                                                            <span>{$attachment.name}</span>
                                                            <span style="color: #6c757d; font-size: 0.9em;">
                                                                ({number_format($attachment.size / 1024, 2, '.', '')} KB)
                                                            </span>
                                                        </a>
                                                    </li>
                                                {/foreach}
                                            {else}
                                                <li style="color: #6c757d; font-style: italic;">No hay adjuntos</li>
                                            {/if}
                                        </ul>
                                    {else}
                                        {* Modo creación: mostrar mensaje informativo *}
                                        <div
                                            style="padding: 10px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; margin-bottom: 10px;">
                                            <p style="margin: 0; color: #0c5460; font-size: 14px;">
                                                <i class="fa fa-info-circle"></i>&nbsp;
                                                <strong>Nota:</strong> {'LBL_EVIDENCE_NOTE'|@getTranslatedString:'Calendar'}
                                            </p>
                                        </div>
                                        <ul id="task-attachments-list" style="list-style: none; padding: 0; margin: 0;">
                                            <li style="color: #6c757d; font-style: italic;">No hay adjuntos</li>
                                        </ul>
                                        {* ... *}
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <input type="hidden" name="module" value="grid_view" />
                            <input type="hidden" name="action" value="SaveActivityReport" />
                            <input type="hidden" id="record-{$idReportAndFeedback}" name="record" value="{$ID}" />
                            <input type="hidden" name="reportid" value="{$reportId}">
                            <input type="hidden" name="action_report" value="{$ACTION_REPORT}">
                            <input type="hidden" name="fl_module" value="{$FL_MODULE}">
                            <div class="col-xs-12 col-md-12" style="margin-top: 12px">
                                <header class="main-box-header clearfix">
                                    <div class="action-bar pull-right">
                                        {if $ACTION_REPORT eq 'edit' && $REPORT neq NULL && $REPORT->getUserId() neq $CURRENT_USER->id}
                                            <button type="button" class="btn btn-info" disabled
                                                title="Solo el autor del reporte puede modificarlo">
                                                Guardar
                                            </button>&nbsp;
                                        {else}
                                            <button type="button" class="btn btn-info"
                                                onclick="ReprtActivityUtils.saveReport (this, '{$idReportAndFeedback}')">
                                                Guardar
                                            </button>&nbsp;
                                        {/if}
                                    </div>
                                </header>
                            </div>
                        </div>
                    </form>
                    {* Reports Form *}
                </div>
                <div class="tab-pane fade {if $ACTIVE_TAB eq 'FEEDBACK'}in active{/if}" id="ACTIVITY_FEEDBACK">
                    {* Feedbacks Form *}
                    <form method="post" id="arctity-feedback-form-{$idReportAndFeedback}"
                        name="arctity-feedback-form-{$idReportAndFeedback}">
                        <div class="row-grid-view justify-content-center">
                            <div class="col-md-6">
                                <div id="gv-div-task" class="form-group">
                                    <label for="progress"><span style="color: red;">*</span>&nbsp;Actividad:</label>
                                    <select id="taskFeedback" title="La tarea" name="taskFeedback"
                                        class="form-control border " {if $AVAILABLE_TASK eq NULL} disabled="disabled" {/if}
                                        onchange="ReprtActivityUtils.selectedActivity(this)">
                                        <option value="">Seleccione ...</option>
                                        {foreach $AVAILABLE_TASK as $task}
                                            <option value="{$task->getActivityId ()}" {if $taskId eq $task->getActivityId ()}
                                                    selected="selected" {assign var='taskDescription' value=$task->getDescription()}
                                                {/if}>
                                                <small><b>{$MOD.EVENT_STATUS[$task->getActivityType()]}</b></small>
                                                :&nbsp;{$task->getSubject()}
                                            </option>
                                        {/foreach}
                                    </select>
                                    <span id="gv-task" class="help-block"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="gv-div-task" class="form-group">
                                    <label for="taskreport">&nbsp;Reporte de actividad (opcional):</label>
                                    <select id="taskreport" title="" name="taskreport" class="form-control border "
                                        {if $ACTIVITY_REPORTS eq NULL} disabled="disabled" {/if}>
                                        <option value="">Seleccione ...</option>
                                        {foreach $ACTIVITY_REPORTS as $taskReport}
                                            <option value="{$taskReport->getId ()}"
                                                {if $ASSOCIATED_REPORT_ID eq $taskReport->getId ()} selected="selected" {/if}
                                                data-activity="{$taskReport->getActivityId()}">
                                                {$taskReport->getTitle()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="gv-task" class="help-block"></span>
                                </div>
                            </div>
                        </div>
                        <div id="activity-description-feedback"
                            class="row-grid-view justify-content-center {if $taskDescription eq NULL}hidden{/if}">
                            <div class="col-md-12">
                                <div class="well well-sm" style="margin-left: 10px;">
                                    {$taskDescription|unescape:'html' nofilter}</div>
                            </div>
                        </div>

                        <div class="row-grid-view justify-content-center">
                            <div id="gv-div-title" class="form-group col-md-12 field-container">
                                <label for="progress"><span style="color: red;">*</span>&nbsp;Título del feedbak:</label>
                                <input type="text" name="titlefeedback" id="titlefeedback" title="título del feedback"
                                    class="form-control border"
                                    value="{if $FEEDBACK neq NULL}{$FEEDBACK->getTitle()|escape:'html'}{/if}"
                                    placeholder="Título del feedback">
                                <span id="gv-title" class="help-block"></span>
                            </div>
                        </div>
                        <div class="row-grid-view justify-content-center">
                            <div id="gv-div-description" class="form-group col-md-12 field-container">
                                <label for="progress"><span style="color: red;">*</span>&nbsp;Reporte de actividad:</label>
                                <textarea id="feedback" style="" name="feedback" class="form-control border"
                                    title="descripción del feedback">{if $FEEDBACK neq NULL}{$FEEDBACK->getFeedback()|escape:'html'}{/if}</textarea>
                                <span id="gv-description" class="help-block"></span>
                            </div>
                        </div>
                        <div class="row">
                            <input type="hidden" name="module" value="grid_view" />
                            <input type="hidden" name="action" value="SaveActivityFeedback" />
                            <input type="hidden" name="record" value="{$ID}" />
                            <input type="hidden" name="feedbackid"
                                value="{if $FEEDBACK neq NULL}{$FEEDBACK->getId()}{/if}" />
                            <div class="col-xs-12 col-md-12" style="margin-top: 12px">
                                <header class="main-box-header clearfix">
                                    <div class="action-bar pull-right">
                                        <button type="button" class="btn btn-info"
                                            onclick="ReprtActivityUtils.saveFeedback (this, '{$idReportAndFeedback}')">
                                            Guardar
                                        </button>&nbsp;
                                    </div>
                                </header>
                            </div>
                        </div>
                    </form>
                    {* Feedbacks Form *}
                </div>
            </div>
        </div>
    </div>
{/strip}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/datepicker.css"/>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript">
    // Necesario porque este fragmento se inyecta vía AJAX (jQuery.html()), lo cual
    // ejecuta los <script src> mediante DOMEval (sin atributo src real en el DOM),
    // impidiendo que ckeditor.js autodetecte su basePath escaneando los script tags.
    window.CKEDITOR_BASEPATH = 'include/ckeditor/';
</script>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="modules/grid_view/ReprtActivityUtils.js"></script>
<script type="text/javascript">
    ReprtActivityUtils.init({$AVAILABLE_OBJETCS_TASK}, '{$ACTION_REPORT}', '{if $REPORT neq NULL}{$REPORT->getId()}{else}0{/if}');

    // Handle preselected activity
    {if $PRESELECTED_ACTIVITY_ID neq NULL && $PRESELECTED_TASK neq NULL}
        jQuery(document).ready(function() {
            var reportOnSelect = jQuery('#report-on');
            if (reportOnSelect.length && reportOnSelect.val() === '') {
                reportOnSelect.val('TASK').trigger('change');
            }

            var taskSelect = jQuery('#task-{$idReportAndFeedback}');
            if (taskSelect.length && taskSelect.val() !== '') {
                setTimeout(function() {
                    ReprtActivityUtils.setActivity(taskSelect[0]);
                }, 100);
            }
        });
    {/if}

    // Handle editing existing report - ensure task is loaded
    {if $REPORT neq NULL && $taskId neq NULL}
        jQuery(document).ready(function() {
                {if $FL_MODULE eq 'orden_de_trabajo'}
                    var reportOnSelect = jQuery('#report-on');
                    if (reportOnSelect.length && '{$reportOn}' !== '') {
                    if ('{$reportOn}' === 'JOB') {
                    var taskSelect = jQuery('#task-{$idReportAndFeedback}');
                    taskSelect.find("option").each(function() {
                        jQuery(this).attr("disabled", "");
                    });
                    taskSelect.attr("title", "");
                    } else if ('{$reportOn}' === 'TASK') {
                    var taskSelect = jQuery('#task-{$idReportAndFeedback}');
                    taskSelect.find("option").each(function() {
                        jQuery(this).removeAttr("disabled");
                    });
                    taskSelect.attr("title", "La tarea");
                }
            }
        {/if}

        var taskSelect = jQuery('#task-{$idReportAndFeedback}');

        {if $taskId neq NULL}
            taskSelect.val('{$taskId}');
            if (taskSelect.val() === null || taskSelect.val() === '') {
                taskSelect.find('option').each(function() {
                        var optionValue = jQuery(this).val();
                        if (optionValue == '{$taskId}') {
                        jQuery(this).prop('selected', true);
                        taskSelect.val('{$taskId}');
                        return false;
                    }
                });
            }
        {/if}

        if (taskSelect.length && taskSelect.val() !== '') {
            setTimeout(function() {
                ReprtActivityUtils.setActivity(taskSelect[0]);
            }, 100);
        }
        });
    {/if}

    // Load attachments if task is already selected AND we're editing an existing report
    {if $taskId neq NULL && $ACTION_REPORT eq 'edit'}
    jQuery(document).ready(function() {
        setTimeout(function() {
            ReprtActivityUtils.loadTaskAttachments('{$taskId}');
        }, 200);
    });
    {/if}

    // Truncar la descripción inicial en ambas pestañas si existe
    jQuery(document).ready(function() {
        // Truncar en pestaña de Reportes
        var reportDescription = jQuery('#activity-description .well');
        if (reportDescription.length && reportDescription.html()) {
            var content = reportDescription.html();
            if (content.length > 600) {
                reportDescription.html(content.substring(0, 600) + '...');
            }
        }

        // Truncar en pestaña de Feedback
        var feedbackDescription = jQuery('#activity-description-feedback .well');
        if (feedbackDescription.length && feedbackDescription.html()) {
            var content = feedbackDescription.html();
            if (content.length > 600) {
                feedbackDescription.html(content.substring(0, 600) + '...');
            }
        }

        // Fix aria-hidden attribute on close button (it shouldn't be on interactive elements)
        jQuery(document).on('shown.bs.modal', '.ekko-lightbox', function() {
            // Remove aria-hidden from close button when modal is shown
            jQuery(this).find('.close').removeAttr('aria-hidden');
        });

        // Remove focus from all elements before hiding ekko-lightbox modal to avoid aria-hidden warning
        jQuery(document).on('hide.bs.modal', '.ekko-lightbox', function() {
            // Remove aria-hidden from close button (it shouldn't be on interactive elements)
            jQuery(this).find('.close').removeAttr('aria-hidden');
            // Remove focus from all elements in the modal
            jQuery(this).find(':focus').blur();
        });

        // Inicializar bootstrap-datepicker en el campo de fecha del avance
        var $dateDisplay = jQuery('#activity_report_date');
        var $dateDb     = jQuery('#activity_report_date_db');
        var userDateFmt  = ($dateDisplay.attr('placeholder') || 'dd/mm/yyyy').toLowerCase();

        // Convertir formato del usuario (dd/mm/yyyy) a formato bootstrap-datepicker (dd/mm/yyyy)
        $dateDisplay.datepicker({
            format: userDateFmt,
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom auto'
        });

        // Al seleccionar fecha: actualizar hidden con formato yyyy-mm-dd para la BD
        $dateDisplay.on('changeDate', function(e) {
            if (e.date) {
                var d = e.date;
                var yyyy = d.getFullYear();
                var mm   = String(d.getMonth() + 1).padStart(2, '0');
                var dd   = String(d.getDate()).padStart(2, '0');
                $dateDb.val(yyyy + '-' + mm + '-' + dd);
            } else {
                $dateDb.val('');
            }
        });

        // Si el campo está vacío (modo creación), preseleccionar la fecha de hoy
        if (!$dateDisplay.val()) {
            var today = new Date();
            $dateDisplay.datepicker('setDate', today);
            // Poblar el hidden con yyyy-mm-dd
            var ty   = today.getFullYear();
            var tm   = String(today.getMonth() + 1).padStart(2, '0');
            var td   = String(today.getDate()).padStart(2, '0');
            $dateDb.val(ty + '-' + tm + '-' + td);
        }

        // Si ya hay un valor al cargar (modo edición), poblar el hidden
        if ($dateDisplay.val()) {
            var parts = $dateDisplay.val().split('/');
            if (parts.length === 3) {
                // Asume dd/mm/yyyy como formato más común; si el formato es distinto
                // el datepicker ya lo muestra bien y necesitamos guardarlo como yyyy-mm-dd
                var dp = $dateDisplay.data('datepicker');
                if (dp && dp.dates && dp.dates.length > 0) {
                    var d0 = dp.dates[0];
                    var yyyy = d0.getFullYear();
                    var mm   = String(d0.getMonth() + 1).padStart(2, '0');
                    var dd2  = String(d0.getDate()).padStart(2, '0');
                    $dateDb.val(yyyy + '-' + mm + '-' + dd2);
                }
            }
        }

    });
</script>