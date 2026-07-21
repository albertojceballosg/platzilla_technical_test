<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="{$APP.LBL_CLOSE}">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title">
        <i class="bi bi-calendar-check"></i> {$MOD.LBL_TASK_INFORMATION|default:'Información de la Tarea'}
    </h4>
</div>

<div id="tvm-container" class="modal-body" style="max-height: 70vh; overflow-y: auto;">
    <div class="container-fluid">

        {if !$IS_JOB_ACTIVITY}
        {* Asunto y Descripción en columnas paralelas *}
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-6">
                <label class="control-label"><strong>{$MOD.Subject|default:'Asunto'}:</strong></label>
                <div style="margin-top: 5px;">{$TASK_DATA.subject|escape:'html'}</div>
            </div>
            <div class="col-md-6">
                <label class="control-label"><strong>{$MOD.Description|default:'Descripción'}:</strong></label>
                <div style="margin-top: 5px; white-space: pre-wrap;">{$TASK_DATA.description nofilter}</div>
            </div>
        </div>

        {* Tabla comparativa: Valores estimados, ejecutados e indicadores *}
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background-color: #f5f5f5;">
                                <th style="width: 16%; text-align: center; vertical-align: middle; padding: 8px 4px;">
                                </th>
                                <th style="width: 16%; text-align: center; vertical-align: middle; padding: 8px 4px;"
                                    class="control-label">{$MOD.Start_Date_Time|default:'Fecha de inicio'}</th>
                                <th style="width: 16%; text-align: center; vertical-align: middle; padding: 8px 4px;"
                                    class="control-label">{$MOD.Due_Date|default:'Realizar antes de'}</th>
                                <th style="width: 17%; text-align: center; vertical-align: middle; padding: 8px 4px;"
                                    class="control-label">% progreso</th>
                                <th style="width: 17%; text-align: center; vertical-align: middle; padding: 8px 4px;"
                                    class="control-label">
                                    Unidades
                                    {if $TASK_DATA.estimated_time_unit neq NULL && $TASK_DATA.estimated_time_unit neq ''}
                                        [{$TASK_DATA.estimated_time_unit}]
                                    {/if}
                                </th>
                                <th style="width: 18%; text-align: center; vertical-align: middle; padding: 8px 4px;"
                                    class="control-label">{$MOD.LBL_ESTIMATED_COST|default:'Costo'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {* Fila 1: Valores estimados *}
                            <tr>
                                <td style="font-weight: bold; background-color: #f9f9f9; padding: 0;"
                                    class="control-label">Valores estimados</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.formatted_start_datetime|default:'-'}</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.formatted_due_date|default:'-'}</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.estimated_progress|default:'0.00'}</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.formatted_estimated_time|default:'0.00'}</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.formatted_estimated_cost|default:'0.00'}</td>
                            </tr>

                            {* Fila 2: Valores ejecutados *}
                            <tr id="tvm-comparativa-tr-real">
                                <td style="font-weight: bold; background-color: #f9f9f9; padding: 0;"
                                    class="control-label">Valores reales</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports}
                                        {$TASK_DATA.actual_data.min_date|default:'-'}
                                    {else}
                                        -
                                    {/if}
                                </td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports}
                                        {$TASK_DATA.actual_data.max_date|default:'-'}
                                    {else}
                                        -
                                    {/if}
                                </td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.progress|default:'0.00'}</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports}
                                        {$TASK_DATA.actual_data.total_duration_display|default:'0.00'}
                                    {else}
                                        0.00
                                    {/if}
                                </td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports}
                                        {$TASK_DATA.actual_data.total_cost_display|default:'0.00'}
                                    {else}
                                        0.00
                                    {/if}
                                </td>
                            </tr>

                            {* Fila 3: Indicadores (proporciones) *}
                            <tr id="tvm-comparativa-tr-proporciones" >
                                <td >Proporciones</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports}
                                        {$TASK_DATA.actual_data.min_date|default:'-'}
                                    {else}
                                        -
                                    {/if}
                                </td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports}
                                        {$TASK_DATA.actual_data.max_date|default:'-'}
                                    {else}
                                        -
                                    {/if}
                                </td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {$TASK_DATA.progress_ratio|default:'0.00'}</td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports && $TASK_DATA.indicators.duration_ratio_display neq ''}
                                        <span
                                            style="{if $TASK_DATA.indicators.duration_over_budget}color: #d9534f; font-weight: bold;{/if}">
                                            {$TASK_DATA.indicators.duration_ratio_display}
                                        </span>
                                    {else}
                                        -
                                    {/if}
                                </td>
                                <td style="text-align: center; padding: 0; font-size: 1vw;">
                                    {if $TASK_DATA.actual_data.has_reports && $TASK_DATA.indicators.cost_ratio_display neq ''}
                                        <span
                                            style="{if $TASK_DATA.indicators.cost_over_budget}color: #d9534f; font-weight: bold;{/if}">
                                            {$TASK_DATA.indicators.cost_ratio_display}
                                        </span>
                                    {else}
                                        -
                                    {/if}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {* Badges de Tipo, Importancia, Prioridad, Ubicación, Mostrar en matriz *}
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12" style="display: flex; flex-wrap: wrap; gap: 5px;">
                <span class="label label-primary" style="font-size: 0.9em; font-weight: normal; padding: 6px 12px;">
                    <i class="fa fa-bars"></i> {$MOD.Type|default:'Tipo'}:
                    {if $TASK_DATA.activitytype_translated neq ''}{$TASK_DATA.activitytype_translated|escape:'html'}{else}{$TASK_DATA.activitytype|escape:'html'}{/if}
                </span>
                {if $TASK_DATA.importance neq ''}
                    <span class="label label-primary" style="font-size: 0.9em; font-weight: normal; padding: 6px 12px;">
                        {if $TASK_DATA.importance eq 'High' || $TASK_DATA.importance eq 'Alta'}
                            <i class="fa fa-arrow-up"></i>
                        {else}
                            <i class="fa fa-arrow-down"></i>
                        {/if}
                        {$MOD.LBL_IMPORTANCE|default:'Importancia'}:
                        {if $TASK_DATA.importance_translated neq ''}{$TASK_DATA.importance_translated|escape:'html'}{else}{$TASK_DATA.importance|escape:'html'}{/if}
                    </span>
                {/if}
                {if $TASK_DATA.priority neq ''}
                    <span class="label label-primary" style="font-size: 0.9em; font-weight: normal; padding: 6px 12px;">
                        {if $TASK_DATA.priority eq 'High' || $TASK_DATA.priority eq 'Alta'}
                            <i class="fa fa-arrow-up"></i>
                        {else}
                            <i class="fa fa-arrow-down"></i>
                        {/if}
                        {$MOD.Priority|default:'Prioridad'}:
                        {if $TASK_DATA.priority_translated neq ''}{$TASK_DATA.priority_translated|escape:'html'}{else}{$TASK_DATA.priority|escape:'html'}{/if}
                    </span>
                {/if}
                {if $TASK_DATA.location neq ''}
                    <span class="label label-primary" style="font-size: 0.9em; font-weight: normal; padding: 6px 12px;">
                        <i class="fa fa-map-marker"></i> {$MOD.Location|default:'Ubicación'}:
                        {$TASK_DATA.location|escape:'html'}
                    </span>
                {/if}
                <span class="label label-primary" style="font-size: 0.9em; font-weight: normal; padding: 6px 12px;">
                    <i class="fa fa-th"></i> {$MOD.LBL_SHOW_IN_MATRIX|default:'¿Mostrar en matriz?'}:
                    {if $TASK_DATA.show_in_matrix eq 'YES'}{$MOD.LBL_YES|default:'Sí'}{else}{$MOD.LBL_NO|default:'No'}{/if}
                </span>
                {if $TASK_DATA.eventstatus neq ''}
                    <span class="label label-primary" style="font-size: 0.9em; font-weight: normal; padding: 6px 12px;">
                        <i class="fa fa-check"></i> {$MOD.Status|default:'Estado'}:
                        {if $TASK_DATA.eventstatus_translated neq ''}{$TASK_DATA.eventstatus_translated|escape:'html'}{else}{$TASK_DATA.eventstatus|escape:'html'}{/if}
                    </span>
                {/if}
                {if $TASK_DATA.combined_condition neq ''}
                    {* Determinar el valor a mostrar y colores según la condición *}
                    {assign var="situacion_display" value=$TASK_DATA.combined_condition}
                    {assign var="situacion_bg_color" value="#ffffff"}
                    {assign var="situacion_text_color" value="#FFFFFF"}

                    {* Si es una clave de traducción (PICK_ACTIVITY_*), traducirla *}
                    {if $TASK_DATA.combined_condition|substr:0:14 eq 'PICK_ACTIVITY_'}
                        {assign var="situacion_display" value=$TASK_DATA.combined_condition|@getTranslatedString:'Calendar'}
                    {/if}

                    {* Determinar color según clave o valor traducido *}
                    {if $TASK_DATA.combined_condition eq 'PICK_ACTIVITY_DELAYED_OVER_BUDGET' }
                        {assign var="situacion_bg_color" value="#D32F2F"}
                        {assign var="situacion_text_color" value="#FFFFFF"}
                    {elseif $TASK_DATA.combined_condition eq 'PICK_ACTIVITY_DELAYED_ON_BUDGET' }
                        {assign var="situacion_bg_color" value="#F57C00"}
                        {assign var="situacion_text_color" value="#FFFFFF"}
                    {elseif $TASK_DATA.combined_condition eq 'PICK_ACTIVITY_ON_TIME_OVER_BUDGET' }
                        {assign var="situacion_bg_color" value="#7B1FA2"}
                        {assign var="situacion_text_color" value="#FFFFFF"}
                    {elseif $TASK_DATA.combined_condition eq 'PICK_ACTIVITY_ON_TIME_ON_BUDGET' }
                        {assign var="situacion_bg_color" value="#388E3C"}
                        {assign var="situacion_text_color" value="#ffffff"}
                    {/if}

                    <span class="label" style="font-size: 0.9em; font-weight: 500; 
                        padding: 6px 12px; background-color: {$situacion_bg_color}; color: {$situacion_text_color};">
                        <i class="fa fa-info-circle"></i> Situación:
                        {$situacion_display|trim|escape:'html'}
                    </span>
                {/if}
            </div>
        </div>

        {* Barra de progreso *}
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <label class="control-label" style="margin-bottom: 5px;"><strong>{$MOD.LBL_PROGRESS|default:'Progreso'}:
                        {if $TASK_DATA.estimated_time_unit neq NULL && $TASK_DATA.estimated_time_unit neq ''}[{$TASK_DATA.estimated_time_unit}]{/if}
                        {$TASK_DATA.progress|default:'0'} %</strong></label>
                <div style="position: relative; width: 100%;">
                    {assign var="progressValue" value=$TASK_DATA.progress|default:'0'|replace:',':'.'|floatval}
                    <input type="range" min="0" max="100" value="{$progressValue}" disabled style="width: 100%; 
                                  -webkit-appearance: none;
                                  appearance: none;
                                  height: 4px;
                                  background: #ddd;
                                  outline: none;
                                  border-radius: 2px;
                                  cursor: default;">
                    <style>
                        input[type=range]::-webkit-slider-thumb {
                            -webkit-appearance: none;
                            appearance: none;
                            width: 16px;
                            height: 16px;
                            background: #337ab7;
                            cursor: default;
                            border-radius: 50%;
                        }

                        input[type=range]::-moz-range-thumb {
                            width: 16px;
                            height: 16px;
                            background: #337ab7;
                            cursor: default;
                            border-radius: 50%;
                            border: none;
                        }
                    </style>
                </div>
            </div>
        </div>

        {* Ejecutor y Usuario *}
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-6">
                <label class="control-label"><strong>{$MOD.LBL_EXECUTOR|default:'Ejecutor'}:</strong></label>
                <div style="margin-top: 5px;">
                    {if $TASK_DATA.supplier_name neq ''}
                        {$TASK_DATA.supplier_name|escape:'html'}
                    {else}
                        -
                    {/if}
                </div>
            </div>
            <div class="col-md-6">
                <label class="control-label"><strong>{$MOD.Assigned_To|default:'Usuario'}:</strong></label>
                <div style="margin-top: 5px;">{$TASK_DATA.owner_name|escape:'html'|default:'-'}</div>
            </div>
        </div>
        {/if}{* /if !IS_JOB_ACTIVITY *}

        {* Tabla de Reportes de Avance *}
        {if $ACTIVITY_REPORTS neq NULL && count($ACTIVITY_REPORTS) > 0}
            <div class="row" style="margin-bottom: 12px;">
                <div class="col-md-12">
                    <label class="control-label" style="color: #000; font-weight: bold; margin-bottom: 10px;">
                        <strong>Reportes de avance:</strong>
                    </label>
                    <div style="overflow-x: auto;">
                        <table id="taskviewmodalreport" class="table table-bordered table-condensed"
                            style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width:8%">
                                        Fecha avance</th>
                                    <th style="width:7%">
                                        Fechas registro</th>
                                    <th style="width:9%">
                                        Usuario</th>
                                    <th style="width:17%">
                                        Título</th>
                                    <th style="width:7%">
                                        % Avance reportado</th>
                                    <th style="width:9%">
                                        Unidades reportadas</th>
                                    <th style="width:11%">
                                        Costo reportado por el avance</th>
                                    <th style="width:14%">
                                        Evidencias</th>
                                    <th style="width:8%">
                                        Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $ACTIVITY_REPORTS as $report}
                                    <tr>
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;"
                                            rowspan=2>
                                            {if $report.activity_report_date neq NULL && $report.activity_report_date neq ''}
                                                {$report.activity_report_date}
                                            {else}
                                                <span style="color:#999; font-style:italic;">-</span>
                                            {/if}
                                        </td>
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;"
                                            rowspan=2>
                                            {$report.formatted_datetime|default:'-'}
                                            {if !empty($report.modificated_date)}
                                                <br><span style="font-size:0.85em; color:#888; display:block; margin-top:3px;">Modificado:<br>{$report.modificated_date}</span>
                                            {/if}
                                        </td>
                                        <td style="text-align: left; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            {$report.user_name|escape:'html'}
                                        </td>
                                        <td style="text-align: left; padding: 4px; font-size: 1vw; vertical-align: middle;">
                                            {$report.title|escape:'html'|default:'-'}
                                        </td>
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            {$report.progress_formatted}%
                                        </td>
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            {$report.duration}
                                        </td>
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            ${$report.cost}
                                        </td>
                                        <td style="text-align: left; padding: 0; font-size: 1vw; vertical-align: middle;"
                                            rowspan=2>
                                            {if $report.evidences neq NULL && count($report.evidences) > 0}
                                                <ul style="list-style: none; padding: 0; margin: 0;">
                                                    {foreach $report.evidences as $evidence}
                                                        <li style="margin-bottom: 3px;">
                                                            <a href="{$evidence.uri}" target="_blank"
                                                                title="{$evidence.name} ({($evidence.size/1024)|string_format:"%.2f"} KB)"
                                                                style="text-decoration: none; color: #337ab7;">
                                                                <i class="fa fa-file-o"></i> {$evidence.name|truncate:25:'...'}
                                                            </a>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            {else}
                                                <span style="color: #999; font-style: italic;">Sin evidencias</span>
                                            {/if}
                                        </td>
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;"
                                            rowspan=2>
                                            {if $IS_JOB_ACTIVITY}
                                                <button type="button"
                                                    class="btn btn-xs btn-default job-evidence-btn"
                                                    style="padding: 4px 8px; font-size: 10px;"
                                                    title="Gestionar evidencias"
                                                    data-reportid="{$report.activityreportid}"
                                                    data-activityid="{$TASK_DATA.activityid}"
                                                    onclick="toggleEvidencePanel(this); return false;">
                                                    <i class="fa fa-paperclip"></i>
                                                </button>
                                                <div class="job-evidence-panel"
                                                    id="evidence-panel-{$report.activityreportid}"
                                                    style="display:none; margin-top:6px; padding:8px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px; min-width:200px; text-align:left;">
                                                    <div class="evidence-list" id="evidence-list-{$report.activityreportid}">
                                                        {if $report.evidences neq NULL && count($report.evidences) > 0}
                                                            {foreach $report.evidences as $ev}
                                                                <div class="evidence-item" id="evidence-item-{$ev.attachmentsid}" style="display:flex; align-items:center; gap:4px; margin-bottom:4px; font-size:11px;">
                                                                    <a href="{$ev.uri}" target="_blank" style="color:#337ab7; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{$ev.name|escape}">
                                                                        <i class="fa fa-file-o"></i> {$ev.name|truncate:20:'...'|escape}
                                                                    </a>
                                                                    <button type="button" class="btn btn-xs btn-danger"
                                                                        style="padding:2px 5px; font-size:10px; flex-shrink:0;"
                                                                        title="Eliminar evidencia"
                                                                        onclick="deleteReportEvidence({$report.activityreportid}, {$ev.attachmentsid}, {$TASK_DATA.activityid}); return false;">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            {/foreach}
                                                        {else}
                                                            <div id="evidence-empty-{$report.activityreportid}" style="color:#999; font-size:11px; margin-bottom:4px;">Sin evidencias</div>
                                                        {/if}
                                                    </div>
                                                    <div style="margin-top:6px;">
                                                        <label style="font-size:11px; font-weight:normal; cursor:pointer; color:#337ab7; margin:0;">
                                                            <i class="fa fa-plus-circle"></i> Agregar archivo
                                                            <input type="file" style="display:none;"
                                                                data-reportid="{$report.activityreportid}"
                                                                data-activityid="{$TASK_DATA.activityid}"
                                                                onchange="uploadReportEvidence(this);">
                                                        </label>
                                                    </div>
                                                </div>
                                            {else}
                                                {* Botón Editar - visible para dueño o admin *}
                                                {if $report.can_edit}
                                                    <a href="index.php?module=grid_view&action=EditActivityReport&record={$TASK_DATA.related_id}&formodule={$TASK_DATA.related_module}&activityid={$TASK_DATA.activityid}&reportid={$report.activityreportid}&Ajax=true"
                                                        data-width="850" data-title="Editar reporte" title="Editar reporte"
                                                        class="btn btn-xs btn-primary" style="padding: 4px 8px; font-size: 10px; margin-right: 4px;"
                                                        onclick="return closeTaskModalAndOpenReport(this);">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                {/if}
                                                
                                                {* Botón Eliminar - SOLO para dueño o admin (más restrictivo) *}
                                                {if $report.can_delete}
                                                    <button type="button" class="btn btn-xs btn-danger"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        onclick="deleteActivityReport({$report.activityreportid}, '{$TASK_DATA.activityid}');"
                                                        title="Eliminar reporte">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                {/if}
                                                
                                                {* Si no tiene ningún permiso, mostrar guión *}
                                                {if !$report.can_edit && !$report.can_delete}
                                                    <span style="color: #999;">-</span>
                                                {/if}
                                            {/if}
                                        </td>
                                    </tr>
                                    {if $report.report neq NULL && $report.report neq ''}
                                        <tr>
                                            <td colspan="5"
                                                style="padding: 6px 8px; font-size: 1vw; background-color: #f9f9f9; border-top: none;">
                                                <b>Informe:</b> {$report.report|unescape:'html' nofilter}
                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {/if}

        {* Tabla de Feedbacks de la Tarea *}
        {if $ACTIVITY_FEEDBACKS neq NULL && count($ACTIVITY_FEEDBACKS) > 0}
            <div class="row" style="margin-bottom: 12px;">
                <div class="col-md-12">
                    <label class="control-label" style="color: #000; font-weight: bold; margin-bottom: 10px;">
                        <strong>Feedbacks recibidos:</strong>
                    </label>
                    <div style="overflow-x: auto;">
                        <table id="taskviewmodalfeedback" class="table table-bordered table-condensed"
                            style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width:9%">
                                        Fecha</th>
                                    <th style="width:10%">
                                        Usuario</th>
                                    <th style="width:15%">
                                        Reporte de avance</th>
                                    <th style="width:14%">
                                        Título</th>
                                    <th style="width:44%">
                                        Comentario</th>
                                    <th style="width:8%">
                                        Operaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $ACTIVITY_FEEDBACKS as $feedback}
                                    <tr>
                                        <!-- Fecha -->
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            {$feedback.feedback_date|date_format:"%d/%m/%Y"}
                                            {$feedback.feedback_date|date_format:"%H:%M"}
                                        </td>

                                        <!-- Usuario con Avatar -->
                                        <td style="text-align: left; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            <div style="display: flex; align-items: center;">
                                                <img src="{$feedback.user_avatar}"
                                                    style="width: 36px; height: 36px; border-radius: 50%; margin-right: 12px; border: 2px solid #ddd;"
                                                    alt="Avatar">
                                                <div>
                                                    {$feedback.user_name|escape:'html'}
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Reporte de avance -->
                                        <td style="text-align: left; padding: 4px; font-size: 1vw; vertical-align: middle;">
                                            {if $feedback.report_title neq ''}
                                                <strong>{$feedback.report_title|escape:'html'}</strong>
                                            {else}
                                                <span style="color: #999; font-style: italic;">-</span>
                                            {/if}
                                        </td>

                                        <!-- Título -->
                                        <td style="text-align: left; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            {$feedback.title|escape:'html'}
                                        </td>

                                        <!-- Comentario -->
                                        <td
                                            style="text-align: left; padding-left: 8px;padding-rigth: 8px; font-size: 1vw; vertical-align: middle; height:1.5em; max-height:5em; overflow-y: auto; line-height: 1.15em; ">
                                            <!--<div
                                                style="height:2.5em; max-height:7.5em; overflow-y: auto; line-height: 1.15em; word-wrap: break-word; white-space: pre-wrap;">-->
                                            {$feedback.feedback|strip_tags|escape:'html'}
                                        </td>

                                        <!-- Operaciones -->
                                        <td style="text-align: center; padding: 0; font-size: 1vw; vertical-align: middle;">
                                            {if $feedback.can_edit}
                                                <a href="index.php?module=grid_view&action=EditActivityReport&record={$TASK_DATA.related_id}&formodule={$TASK_DATA.related_module}&activityid={$TASK_DATA.activityid}&feedbackid={$feedback.id}&Ajax=true"
                                                    data-width="850" data-title="Editar feedback" title="Editar feedback"
                                                    class="btn btn-xs btn-primary"
                                                    style="padding: 4px 8px; font-size: 10px; margin-right: 4px;"
                                                    onclick="return closeTaskModalAndOpenFeedback(this);">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            {/if}
                                            {if $feedback.can_edit || $IS_ADMIN}
                                                <button type="button" class="btn btn-xs btn-danger"
                                                    style="padding: 4px 8px; font-size: 10px;"
                                                    onclick="deleteFeedback({$feedback.id}, '{$TASK_DATA.activityid}');"
                                                    title="Eliminar feedback">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            {/if}
                                            {if !$feedback.can_edit && !$IS_ADMIN}
                                                <span style="color: #999;">-</span>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {else}
            <div class="row" style="margin-bottom: 12px;">
                <div class="col-md-12">
                    <label class="control-label" style="color: #000; font-weight: bold; margin-bottom: 10px;">
                        <strong>Feedbacks recibidos:</strong>
                    </label>
                    <div
                        style="padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                        <span style="color: #666; font-style: italic;">No se han recibido feedbacks para esta tarea.</span>
                    </div>
                </div>
            </div>
        {/if}

    </div>
</div>

<div class="modal-footer">
    <div class="pull-left">
        {if !$IS_JOB_ACTIVITY}
            {if $TASK_DATA.eventstatus eq 'Held'}
                <a href="javascript:void(0);" class="btn btn-default btn-circle"
                    style="margin-right: 10px; cursor: not-allowed; opacity: 0.6;"
                    title="No se pueden agregar reportes a tareas completadas">
                    <span class="icon icon-02-iconos-chat"></span>
                </a>
            {else}
                <a href="index.php?module=grid_view&action=EditActivityReport&record={$TASK_DATA.related_id}&formodule={$TASK_DATA.related_module}&activityid={$TASK_DATA.activityid}&Ajax=true"
                    data-width="850" data-title="Reportes y feedbacks" title="Reportes y feedbacks"
                    class="btn btn-primary btn-circle" style="margin-right: 10px;"
                    onclick="return closeTaskModalAndOpenReport(this);">
                    <span class="icon icon-02-iconos-chat"></span>
                </a>
            {/if}
        {/if}
    </div>
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <i class="bi bi-x-circle"></i> {$APP.LBL_CLOSE|default:'Cerrar'}
    </button>
</div>

{* Overlay y Modal para EditActivityReport *}
<div id="task-report-overlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; background: rgba(0,0,0,0.75);">
    <div id="task-report-modal"
        style="position: relative; width: 90%; max-width: 900px; margin: 40px auto; background: white; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.4);">
        <div
            style="padding: 15px; border-bottom: 1px solid #e5e5e5; display: flex; justify-content: space-between; align-items: center;">
            <h4 id="task-report-modal-title" style="margin: 0; font-size: 16px; font-weight: bold;">Reportes y feedbacks
            </h4>
            <button id="task-report-close-btn"
                style="font-size: 24px; cursor: pointer; border: none; background: none; line-height: 1;">&times;</button>
        </div>
        <div id="task-report-modal-body" style="padding: 15px; max-height: 75vh; overflow-y: auto;">
        </div>
    </div>
</div>

<script>
    // Función para crear el overlay del reporte si no existe
    function ensureReportOverlayExists() {
        var overlay = jQuery('#task-report-overlay');
        if (overlay.length === 0) {
            var overlayHtml =
                '<div id="task-report-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; background: rgba(0,0,0,0.75);">' +
                '<div id="task-report-modal" style="position: relative; width: 90%; max-width: 900px; margin: 40px auto; background: white; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.4);">' +
                '<div style="padding: 15px; border-bottom: 1px solid #e5e5e5; display: flex; justify-content: space-between; align-items: center;">' +
                '<h4 id="task-report-modal-title" style="margin: 0; font-size: 16px; font-weight: bold;">Reportes y feedbacks</h4>' +
                '<button id="task-report-close-btn" style="font-size: 24px; cursor: pointer; border: none; background: none; line-height: 1;">&times;</button>' +
                '</div>' +
                '<div id="task-report-modal-body" style="padding: 15px; max-height: 75vh; overflow-y: auto;"></div>' +
                '</div>' +
                '</div>';
            jQuery('body').append(overlayHtml);

            // Re-bind event handlers para el nuevo overlay
            jQuery(document).off('click', '#task-report-close-btn').on('click', '#task-report-close-btn', function(e) {
                e.preventDefault();
                jQuery('#task-report-overlay').css('display', 'none');
            });
            jQuery(document).off('click', '#task-report-overlay').on('click', '#task-report-overlay', function(e) {
                if (e.target === this) {
                    jQuery('#task-report-overlay').css('display', 'none');
                }
            });

            return jQuery('#task-report-overlay');
        }
        return overlay;
    }

    function closeTaskModalAndOpenReport(linkElement) {

        // Prevent default link behavior
        event.preventDefault();

        // Quitar el foco del elemento para evitar warning de aria-hidden
        if (linkElement) {
            linkElement.blur();
        }

        // Cerrar todas las modales usando jQuery (compatible con Bootstrap 3/4)
        jQuery('.modal:visible').modal('hide');
        jQuery('.modal-backdrop').remove();
        jQuery('body').removeClass('modal-open');

        // Wait a moment for the modal to close, then open the report
        setTimeout(function() {
            // Asegurar que el overlay existe
            var overlay = ensureReportOverlayExists();

            // Get the href from the link element
            var href = jQuery(linkElement).attr('href');
            var title = jQuery(linkElement).attr('data-title') || 'Reportes y feedbacks';
            var modal = jQuery('#task-report-modal');
            var modalBody = jQuery('#task-report-modal-body');
            var modalTitle = jQuery('#task-report-modal-title');


            // Actualizar título del modal
            modalTitle.text(title);

            // Mostrar loading
            modalBody.html(
                '<div style="text-align:center; padding: 40px 0;"><img src="themes/images/loading.gif" alt="Loading" style="max-width: 200px; width: auto; height: auto;"/></div>'
            );

            // El overlay ya fue asegurado arriba, solo mostrarlo
            overlay.css('display', 'block');

            // Cargar contenido vía AJAX
            jQuery.get(href, function(data) {
                try {
                    if ((data !== '') && data !== undefined) {
                        modalBody.html(data);
                    } else {
                        modalBody.html('<h2>Información no encontrada!</h2>');
                        console.error('[closeTaskModalAndOpenReport] data vacío');
                    }
                } catch (e) {
                    console.error('[EditActivityReport] Error al cargar contenido:', e);
                    modalBody.html('<h2>Error al cargar el contenido</h2>');
                }
            }).fail(function(xhr, status, error) {
                console.error('[EditActivityReport] Error AJAX:', status, error);
                modalBody.html('<h2>Error al cargar el contenido</h2>');
            });
        }, 350);

        return false;
    }

    // Función para cerrar el modal de reportes
    function closeTaskReportModal() {
        jQuery('#task-report-overlay').css('display', 'none');
    }

    // Manejar clic en botón de cerrar del modal
    jQuery(document).on('click', '#task-report-close-btn', function(e) {
        e.preventDefault();
        closeTaskReportModal();
    });

    // Manejar clic en el overlay (fuera del modal) para cerrar
    jQuery(document).on('click', '#task-report-overlay', function(e) {
        if (e.target === this) {
            closeTaskReportModal();
        }
    });

    function closeTaskModalAndOpenFeedback(linkElement) {
        // Prevenir comportamiento por defecto
        event.preventDefault();

        // Quitar el foco del elemento para evitar warning de aria-hidden
        if (linkElement) {
            linkElement.blur();
        }

        // Cerrar todas las modales usando jQuery
        jQuery('.modal:visible').modal('hide');
        jQuery('.modal-backdrop').remove();
        jQuery('body').removeClass('modal-open');

        // Esperar y abrir modal de feedback
        setTimeout(function() {
            // Asegurar que el overlay existe
            var overlay = ensureReportOverlayExists();

            var href = jQuery(linkElement).attr('href');
            var title = jQuery(linkElement).attr('data-title') || 'Editar Feedback';
            var modalBody = jQuery('#task-report-modal-body');
            var modalTitle = jQuery('#task-report-modal-title');

            // Actualizar título del modal
            modalTitle.text(title);

            // Mostrar loading
            modalBody.html(
                '<div style="text-align:center; padding: 40px 0;"><img src="themes/images/loading.gif" alt="Loading" style="max-width: 200px; width: auto; height: auto;"/></div>'
            );

            // Mostrar el overlay
            overlay.css('display', 'block');

            // Cargar contenido vía AJAX
            jQuery.get(href, function(data) {
                try {
                    if ((data !== '') && data !== undefined) {
                        modalBody.html(data);
                    } else {
                        modalBody.html('<h2>Información no encontrada!</h2>');
                    }
                } catch (e) {
                    console.error('Error al cargar contenido:', e);
                    modalBody.html('<h2>Error al cargar el contenido</h2>');
                }
            }).fail(function() {
                modalBody.html('<h2>Error al cargar el contenido</h2>');
            });
        }, 350);

        return false;
    }

    function deleteFeedback(feedbackId, activityId) {
        // Obtener mensajes traducidos del template
        var confirmMessage = '{$MOD.LBL_DELETE_FEEDBACK_CONFIRM|default:"¿Está seguro de que desea eliminar este feedback? Esta acción es irreversible."}';
        var successMessage = '{$MOD.LBL_FEEDBACK_DELETED|default:"Feedback eliminado exitosamente"}';
        var errorMessage = '{$MOD.LBL_ERROR_DELETING_FEEDBACK|default:"Error al eliminar el feedback"}';

        // Confirmar eliminación
        if (!confirm(confirmMessage)) {
            return false;
        }

        // Realizar petición AJAX para eliminar el feedback
        jQuery.ajax({
            url: 'index.php?module=grid_view&action=DeleteActivityFeedback',
            type: 'POST',
            data: {
                feedbackid: feedbackId,
                activityid: activityId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(successMessage);
                    // Recargar el modal de la tarea para reflejar los cambios
                    if (window.WorkTaskActivityModal && typeof window.WorkTaskActivityModal.openView ===
                        'function') {
                        window.WorkTaskActivityModal.openView(activityId);
                    }
                } else {
                    alert(errorMessage + ': ' + (response.error || ''));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al eliminar feedback:', error);
                alert(errorMessage);
            }
        });

        return false;
    }

    function toggleEvidencePanel(btn) {
        var reportId = jQuery(btn).data('reportid');
        var panel    = jQuery('#evidence-panel-' + reportId);
        panel.toggle();
    }

    function uploadReportEvidence(inputEl) {
        var file       = inputEl.files[0];
        var reportId   = jQuery(inputEl).data('reportid');
        var activityId = jQuery(inputEl).data('activityid');

        if (!file) return;

        var formData = new FormData();
        formData.append('evidence',   file);
        formData.append('reportid',   reportId);
        formData.append('activityid', activityId);

        var btn = jQuery(inputEl).closest('label');
        btn.html('<i class="fa fa-spinner fa-spin"></i> Subiendo...');

        jQuery.ajax({
            url:         'index.php?module=Calendar&action=CalendarAjax&function=UPLOAD-REPORT-EVIDENCE',
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            dataType:    'json',
            success: function(resp) {
                btn.html('<i class="fa fa-plus-circle"></i> Agregar archivo');
                btn.append('<input type="file" style="display:none;" data-reportid="' + reportId + '" data-activityid="' + activityId + '" onchange="uploadReportEvidence(this);">');
                inputEl.value = '';

                if (resp.success) {
                    var emptyMsg = jQuery('#evidence-empty-' + reportId);
                    if (emptyMsg.length) emptyMsg.remove();

                    var list = jQuery('#evidence-list-' + reportId);
                    var html = '<div class="evidence-item" id="evidence-item-' + resp.attachmentid + '" style="display:flex; align-items:center; gap:4px; margin-bottom:4px; font-size:11px;">' +
                        '<a href="' + resp.uri + '" target="_blank" style="color:#337ab7; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + resp.name + '">' +
                        '<i class="fa fa-file-o"></i> ' + resp.name.substring(0, 20) + (resp.name.length > 20 ? '...' : '') +
                        '</a>' +
                        '<button type="button" class="btn btn-xs btn-danger" style="padding:2px 5px; font-size:10px; flex-shrink:0;" title="Eliminar evidencia"' +
                        ' onclick="deleteReportEvidence(' + reportId + ', ' + resp.attachmentid + ', ' + activityId + '); return false;">' +
                        '<i class="fa fa-times"></i></button></div>';
                    list.append(html);
                } else {
                    alert('Error al subir evidencia: ' + (resp.error || ''));
                }
            },
            error: function() {
                btn.html('<i class="fa fa-plus-circle"></i> Agregar archivo');
                btn.append('<input type="file" style="display:none;" data-reportid="' + reportId + '" data-activityid="' + activityId + '" onchange="uploadReportEvidence(this);">');
                alert('Error al subir el archivo.');
            }
        });
    }

    function deleteReportEvidence(reportId, attachmentId, activityId) {
        if (!confirm('¿Eliminar esta evidencia?')) return false;

        jQuery.ajax({
            url:      'index.php?module=Calendar&action=CalendarAjax&function=DELETE-REPORT-EVIDENCE',
            type:     'POST',
            data:     { reportid: reportId, attachmentid: attachmentId },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    jQuery('#evidence-item-' + attachmentId).remove();
                    if (jQuery('#evidence-list-' + reportId + ' .evidence-item').length === 0) {
                        jQuery('#evidence-list-' + reportId).html('<div id="evidence-empty-' + reportId + '" style="color:#999; font-size:11px; margin-bottom:4px;">Sin evidencias</div>');
                    }
                } else {
                    alert('Error al eliminar evidencia: ' + (resp.error || ''));
                }
            },
            error: function() {
                alert('Error al eliminar evidencia.');
            }
        });
        return false;
    }

    function deleteActivityReport(reportId, activityId) {
        // Obtener mensajes traducidos del template
        var confirmMessage = '{$MOD.LBL_DELETE_REPORT_CONFIRM|default:"¿Está seguro de que desea eliminar este reporte de avance?"}';
        var successMessage = '{$MOD.LBL_REPORT_DELETED_SUCCESS|default:"Reporte eliminado exitosamente"}';
        var errorMessage = '{$MOD.LBL_ERROR_DELETING_REPORT|default:"Error al eliminar el reporte"}';

        // Confirmar eliminación
        if (!confirm(confirmMessage)) {
            return false;
        }

        // Realizar petición AJAX para eliminar el reporte (soft delete)
        jQuery.ajax({
            url: 'index.php?module=Calendar&action=CalendarAjax&function=DELETE-ACTIVITY-REPORT',
            type: 'POST',
            data: {
                reportid: reportId,
                activityid: activityId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(successMessage);
                    // Recargar el modal de la tarea para reflejar los cambios
                    if (window.WorkTaskActivityModal && typeof window.WorkTaskActivityModal.openView === 'function') {
                        window.WorkTaskActivityModal.openView(activityId);
                    }
                } else {
                    alert(errorMessage + ': ' + (response.error || ''));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al eliminar reporte:', error);
                alert(errorMessage);
            }
        });

        return false;
    }
</script>