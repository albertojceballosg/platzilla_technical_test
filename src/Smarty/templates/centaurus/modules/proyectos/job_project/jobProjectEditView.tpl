{math equation= rand() assign= "idTaskProject"}
{if $RELATED_JOBS neq NULL}
    {if $RELATED_JOBS[0]->getSummaryRow() neq NULL}
        {assign var="rowSummary" value=$RELATED_JOBS[0]->getSummaryRow()}
        {assign var="totalJobFactor" value=$rowSummary['job_contribution_factor']}
        {assign var="totalProjectProgress" value= $rowSummary['project_progress']}
    {else}
        {assign var="totalJobFactor" value= 0.00}
        {assign var="totalProjectProgress" value= 0.00}
    {/if}
{else}
    {assign var="totalJobFactor" value= 0.00}
    {assign var="totalProjectProgress" value= 0.00}
{/if}
<link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css" />
{if $VIEW eq NULL}
    <style>
        {literal}
            table[id^="task-project-table-"] td {
                padding: 4px !important;
                vertical-align: middle !important;
            }

            table[id^="task-project-table-"] .form-control {
                padding: 3px 6px !important;
                height: 28px !important;
                border: none !important;
                box-shadow: none !important;
            }

            table[id^="task-project-table-"] div[id^="td_select_job-"] input[id^="edit_seleccione_job-"],
            table[id^="task-project-table-"] div[id^="td_select_job-"] input[id*="_display"],
            table[id^="task-project-table-"] select[id^="stage-"] {
                border: 1px solid #dee2e6 !important;
            }

            table[id^="task-project-table-"] div[id^="td_select_job-"] .input-group-addon {
                padding-left: 4px !important;
                padding-right: 4px !important;
                min-width: 0 !important;
                width: 30px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            table[id^="task-project-table-"] div[id^="td_select_job-"] .input-group {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
            }

            table[id^="task-project-table-"] div[id^="td_select_job-"] .col-md-12 {
                padding-left: 4px !important;
                padding-right: 4px !important;
            }

            table[id^="task-project-table-"] div[id^="td_select_job-"] input[id*="_display"] {
                flex: 1 1 auto !important;
                min-width: 0 !important;
            }

            table[id^="task-project-table-"] div[id^="td_select_job-"] .input-group-addon i {
                font-size: 90% !important;
            }

            table[id^="task-project-table-"] input[id^="job_contribution_factor-"],
            table[id^="task-project-table-"] input[id^="percentage_completion-"],
            table[id^="task-project-table-"] input[id^="project_progress-"],
            table[id^="task-project-table-"] input[id^="work_estimated_cost-"] {
                text-align: right !important;
            }

            table[id^="task-project-table-"] td.project-actions-cell {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 1px;
            }

            table[id^="task-project-table-"] td.project-actions-cell .btn {
                font-size: 90% !important;
                padding: 0 !important;
                line-height: 1 !important;
                width: 26px !important;
                height: 26px !important;
                min-width: 26px !important;
                min-height: 26px !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                outline: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* BLOQUEAR datepickers en campos de fecha readonly del project_work */
            /* Estos campos muestran datos del trabajo y no deben ser editables */
            input[id^="job-start_date-"],
            input[id^="job-due_date-"] {
                pointer-events: none !important;
                cursor: not-allowed !important;
            }
            
            /* Ocultar cualquier datepicker que intente aparecer en estos campos */
            input[id^="job-start_date-"] + .datepicker,
            input[id^="job-due_date-"] + .datepicker,
            input[id^="job-start_date-"] ~ .datepicker.dropdown-menu,
            input[id^="job-due_date-"] ~ .datepicker.dropdown-menu,
            input[id^="job-start_date-"].hasDatepicker + div,
            input[id^="job-due_date-"].hasDatepicker + div {
                display: none !important;
                visibility: hidden !important;
            }

        {/literal}
    </style>
{/if}
<div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 20px" {/if}>
    {if ($VIEW neq NULL) && ($RELATED_JOBS neq NULL)}
        <div id="job_project_tab_title" class="row card-header platzilla-card-header" style="padding-left: 0!important;">
            <div class="col-md-5">
                <p class="text-center pull-left" style="font-weight: bold">Trabajos para realizar el proyecto</p>
            </div>
            <div class="col-md-7">&nbsp;</div>
        </div>
    {/if}
    <div class="table-responsive field-container">
        <input type="hidden" id="usr" value="{$CURRENT_USER_ID}">
        <input type="hidden" id="project-user-proposed-cost-{$idTaskProject}" value="{$PROJECT_USER_PROPOSED_COST_RAW}">
        <table id="task-project-table-{$idTaskProject}" class="table table-bordered tablegridvalidate" name="task-project-table">
            {if $VIEW eq NULL}
                <thead>
                    <tr>
                        <td íd="lb_edit_jp" colspan="12" style="text-align: left;">Trabajos para realizar el proyecto:</td>
                    </tr>
                    <tr valign="top" id="job_project_column_header">
                        <td style="width:9%"><span style="">Etapa</span></td>
                        <td style="width:18%"><span style="">Nombre del trabajo</span></td>
                        <td style="width:8%"><span style="">Fecha est. inicio</span></td>
                        <td style="width:8%"><span style="">Fecha est. cierre</span></td>
                        <td style="width:8%"><span style="">Responsable</span></td>
                        <td style="width:6%"><span style="">Factor de avance (%) </span></td>
                        <td style="width:6%"><span style="">Avance trabajo(%)</span></td>
                        <td style="width:6%"><span style="">Avance proyecto(%) </span></td>
                        <td style="width:7.0%; "><span style="">Costo estimado</span></td>
                        <td style="width:7.0%; "><span style="">Costo ejecutado</span></td>
                        <td style="width:9%; "><span style="">Situación</span></td>
                        <td class="text-center" {if $VIEW eq NULL}style="width:9%" {/if}>
                            {if $VIEW eq NULL}Acciones{else}&nbsp;{/if}
                        </td>
                    </tr>	
                </thead>
            {/if}
            <tbody id="task-project-{$idTaskProject}" rowtotal="0" name="job_project_tbody">
                {if $VIEW eq NULL}
                    {if $RELATED_JOBS neq NULL}
                        {foreach $RELATED_JOBS as $key => $relatedJob}
                            {math equation = rand() assign= "idRow"}
                            {include file='modules/proyectos/job_project/jobProjectRowEdit.tpl'}
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="10" style="text-align: center"></td>
                        </tr>
                    {/if}
                {else}
                    {if ($RELATED_JOBS neq NULL) && ($PROJECT_STAGES neq NULL)}
                        {foreach $PROJECT_STAGES as $projectStage}
                            {if in_array ($projectStage->id,$RELATED_STAGES)}
                                <tr>
                                    <td id="job_project_lb_DV" colspan="10" style="text-align: left;">{$projectStage->stage}:</td>
                                </tr>
                                <tr valign="middle" id="job_project_td_DV">
                                    <td style="width:25%; vertical-align: middle;"><span style="">Nombre del trabajo</span></td>
                                    <td style="width:8%; vertical-align: middle; "><span style="">Fecha est. inicio</span></td>
                                    <td style="width:8%; vertical-align: middle;"><span style="">Fecha est. cierre</span></td>
                                    <td style="width:9%; vertical-align: middle;"><span style="">Responsable</span></td>
                                    <td style="width:7%; vertical-align: middle;"><span style="">Factor de avance (%) </span></td>
                                    <td style="width:7%; vertical-align: middle;"><span style="">Avance trabajo(%)</span></td>
                                    <td style="width:7%; vertical-align: middle;"><span style="">Avance proyecto(%) </span></td>
                                    <td style="width:9%; vertical-align: middle;"><span style="">Costo estimado</span></td>
                                    <td style="width:9%; vertical-align: middle;"><span style="">Costo ejecutado</span></td>
                                    <td style="width:11%; vertical-align: middle;"><span style="">Situación</span></td>
                                </tr>
                            {/if}
                            {foreach $RELATED_JOBS as $key => $relatedJob}
                                {if $relatedJob->getStageId () neq $projectStage->id}{continue}{/if}
                                {math equation= rand() assign= "idRow"}
                                {include file='modules/proyectos/job_project/jobProjectDetailView.tpl'}
                            {/foreach}
                        {/foreach}
                    {/if}
                {/if}
            </tbody>
            <tfoot id="tfoot-{$idTaskProject}" data-summary-row="" data-operation-row="">
                {if $VIEW neq NULL}
                    {include file='modules/proyectos/job_project/jobProjectFooterDetail.tpl'}
                {else}
                    {include file='modules/proyectos/job_project/jobProjectFooterEdit.tpl'}
                {/if}

            </tfoot>
        </table>
        {if $VIEW eq NULL}
            <script type="text/html" id="task-project-template-{$idTaskProject}">
                {include file='modules/proyectos/job_project/jobProjectEdit_template.tpl'}
            </script>
            <script type="text/html" id="task-project-tr-{$idTaskProject}">
                <tr>
                    <td colspan="10" style="text-align: center"></td>
                </tr>
            </script>
        {/if}
    </div>
</div>
<script type="text/javascript">
    // Definir formato de fecha del usuario para los datepickers de trabajos del proyecto
    if (typeof gUserDateFormat === 'undefined') {
        var gUserDateFormat = '{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}';
    }
</script>
<script src="modules/proyectos/task-project-utls.js?v=20260606d"></script>
{if $RELATED_JOBS neq NULL}
    <script type="text/javascript">
        {if $VIEW eq NULL}
            TaskProjectUtls.setCalendar ('#task-project-table-{$idTaskProject}');
        {/if}
        // Calcular totales iniciales al cargar la página
        jQuery(document).ready(function() {
            {if $VIEW neq NULL}
                // DetailView - pasar true como segundo parámetro
                TaskProjectUtls.calculateInitialTotals('{$idTaskProject}', true);
            {else}
                // EditView - pasar false como segundo parámetro
                TaskProjectUtls.calculateInitialTotals('{$idTaskProject}', false);
            {/if}
        });
    </script>
{/if}