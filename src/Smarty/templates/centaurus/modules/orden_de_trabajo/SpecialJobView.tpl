{*
 * SpecialJobView - Vista especial de expediente de trabajo
 * Muestra toda la información del trabajo en una sola pantalla con scroll
 *}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Expediente: {$WORK_INFO.titulo|escape} - Platzilla</title>
    <link rel="stylesheet" href="themes/{$THEME}/css/style.css" type="text/css">
    <link rel="stylesheet" href="themes/{$THEME}/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="themes/{$THEME}/css/general.css" type="text/css">
    <link rel="stylesheet" href="themes/{$THEME}/css/detailview-custom.css" type="text/css">
    <style type="text/css">
        /* Estilos para funcionar correctamente dentro del modal */
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }
        #sjv-container {
            padding: 20px;
            box-sizing: border-box;
            background: #fff;
            max-width: 100%;
            width: 100%;
        }
        /* Asegurar que el contenido sea scrolleable dentro del modal */
        #sjv-container {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }
        #sjv-container::-webkit-scrollbar {
            width: 8px;
        }
        #sjv-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        #sjv-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        #sjv-container::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        /* Ajustar tablas y contenido al ancho disponible */
        #sjv-container table {
            max-width: 100%;
            table-layout: fixed;
        }
        #sjv-container .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }
    </style>
</head>
<body>
<div id="sjv-container">

    {* ===== HEADER DEL TRABAJO ===== *}
    <div id="sjv-work-header">
        {* Mostrar botón de imprimir solo cuando no viene desde listview *}
        {if !$HIDE_PRINT_BUTTON}
        <div id="sjv-print-btn-wrap">
            <button type="button" onclick="window.print();" class="btn btn-default btn-sm" id="sjv-print-btn" title="Imprimir expediente">
                &#x1f5a8;
            </button>
        </div>
        {/if}
        <div id="sjv-work-code">
            <i class="fa fa-briefcase"></i>
            {if $WORK_INFO.proyecto_name}
                <b>PROYECTO:</b>&bull;&nbsp;<i class="fa fa-folder-o"></i>
                <a href="index.php?module=proyectos&action=DetailView&record={$WORK_INFO.proyecto}">{$WORK_INFO.proyecto_name}</a><br><br>
            {/if}
			TRABAJO: {$WORK_INFO.cod_orden_de_tra|default:"---"}
        </div>
        <h1>{$WORK_INFO.titulo|escape}</h1>

        <div id="sjv-header-badges">
            {* Estado *}
            {if $WORK_INFO.estado_de_la_orden}
                <span class="label label-default" style="font-size:12px; padding:5px 12px;">
                    <i class="fa fa-check-circle"></i>
                    {if $WORK_INFO.estado_de_la_orden_translated neq ''}{$WORK_INFO.estado_de_la_orden_translated}{else}{$WORK_INFO.estado_de_la_orden}{/if}
                </span>
            {/if}
            {* Tipo *}
            {if $WORK_INFO.tipo_dactividad}
                <span class="label label-primary" style="font-size:12px; padding:5px 12px;">
                    <i class="fa fa-tag"></i>
                    {if $WORK_INFO.tipo_dactividad_translated neq ''}{$WORK_INFO.tipo_dactividad_translated}{else}{$WORK_INFO.tipo_dactividad}{/if}
                </span>
            {/if}
            {* Importancia *}
            {if $WORK_INFO.importance_work}
                <span class="label label-primary" style="font-size:12px; padding:5px 12px;">
                    {if $WORK_INFO.importance_work eq 'High' || $WORK_INFO.importance_work eq 'Alta'}<i class="fa fa-arrow-up"></i>{else}<i class="fa fa-arrow-down"></i>{/if}
                    {if $WORK_INFO.importance_work_translated neq ''}{$WORK_INFO.importance_work_translated}{else}{$WORK_INFO.importance_work}{/if}
                </span>
            {/if}
            {* Prioridad *}
            {if $WORK_INFO.work_priority}
                <span class="label label-primary" style="font-size:12px; padding:5px 12px;">
                    <i class="fa fa-sort-amount-desc"></i>
                    {if $WORK_INFO.work_priority_translated neq ''}{$WORK_INFO.work_priority_translated}{else}{$WORK_INFO.work_priority}{/if}
                    {if $WORK_INFO.priority_index} ({$WORK_INFO.priority_index}){/if}
                </span>
            {/if}
            {* Situación del trabajo *}
            {if $WORK_INFO.work_situation}
                {assign var="wSitBg" value="#ffc107"}
                {assign var="wSitColor" value="#212529"}
                {assign var="wSitKey" value=$WORK_INFO.work_situation|lower}
                {if $wSitKey|strpos:'ptima' !== false}{assign var="wSitBg" value="#28a745"}{assign var="wSitColor" value="#fff"}{/if}
                {if $wSitKey|strpos:'control' !== false}{assign var="wSitBg" value="#17a2b8"}{assign var="wSitColor" value="#fff"}{/if}
                {if $wSitKey|strpos:'alerta' !== false}{assign var="wSitBg" value="#fd7e14"}{assign var="wSitColor" value="#fff"}{/if}
                {if $wSitKey|strpos:'retraso' !== false}{assign var="wSitBg" value="#F57C00"}{assign var="wSitColor" value="#fff"}{/if}
                {if $wSitKey|strpos:'tica' !== false}{assign var="wSitBg" value="#D32F2F"}{assign var="wSitColor" value="#fff"}{/if}
                {if $wSitKey|strpos:'planificado' !== false}{assign var="wSitBg" value="#6f42c1"}{assign var="wSitColor" value="#fff"}{/if}
                <span style="display:inline-block; padding:5px 14px; border-radius:12px; font-size:12px; font-weight:600; background:{$wSitBg}; color:{$wSitColor};">
                    <i class="fa fa-info-circle"></i> {$WORK_INFO.work_situation}
                </span>
            {/if}
        </div>

        {* Barras de progreso *}
        {assign var="realProg" value=$WORK_INFO.overall_progress_perc|default:0|replace:',':'.'|string_format:"%.4f"|replace:',':'.'}
        {assign var="expProg"  value=$WORK_INFO.expected_work_progress|default:0|replace:',':'.'|string_format:"%.4f"|replace:',':'.'}
        <div id="sjv-progress-row">
            <div id="sjv-progress-labels">
                <span><i class="fa fa-bar-chart"></i> Avance real: <strong>{$WORK_INFO.overall_progress_perc_formatted|default:"0"}%</strong></span>
                <span>Esperado: {$WORK_INFO.expected_work_progress_formatted|default:"0"}%</span>
            </div>
            <div id="sjv-progress-track">
                <div id="sjv-progress-fill-real" style="width:{if $realProg > 100}100{else}{$realProg}{/if}%;"></div>
                {if $expProg > 0}<div id="sjv-progress-marker-expected" style="left:{if $expProg > 100}100{else}{$expProg}{/if}%;"></div>{/if}				
            </div>
        </div>
	</div>
	
    {* ===== INFORMACIÓN GENERAL DEL TRABAJO ===== *}
    <div id="sjv-section-info" class="sjv-section-card">
        <div class="sjv-section-header"><i class="fa fa-info-circle"></i> Información del Trabajo</div>
        <div class="sjv-section-body">
            {if $WORK_INFO.descripcion}
                <div id="sjv-work-description">{$WORK_INFO.descripcion|escape}</div>
            {/if}
            <div class="sjv-info-grid">
                {if $WORK_INFO.assigned_user_name}
                    <div id="sjv-field-assigned" class="sjv-info-item"><div class="sjv-info-label">Asignado a</div><div class="sjv-info-value"><i class="fa fa-user"></i> {$WORK_INFO.assigned_user_name|escape}</div></div>
                {/if}
                {if $WORK_INFO.coordinado_po}
                    <div id="sjv-field-coordinado" class="sjv-info-item"><div class="sjv-info-label">Coordinado por</div><div class="sjv-info-value"><i class="fa fa-user-circle"></i> {$WORK_INFO.coordinado_po|escape}</div></div>
                {/if}
                {if $WORK_INFO.cliente_name}
                    <div id="sjv-field-cliente" class="sjv-info-item"><div class="sjv-info-label">Cliente</div><div class="sjv-info-value"><a href="index.php?module=clientes&action=DetailView&record={$WORK_INFO.cliente}"><i class="fa fa-building"></i> {$WORK_INFO.cliente_name|escape}</a></div></div>
                {/if}
                {if $WORK_INFO.contrato_code}
                    <div id="sjv-field-contrato" class="sjv-info-item"><div class="sjv-info-label">Contrato</div><div class="sjv-info-value"><a href="index.php?module=contratos_de_servicio&action=DetailView&record={$WORK_INFO.contrato}"><i class="fa fa-file-text"></i> {$WORK_INFO.contrato_code|escape}</a></div></div>
                {/if}
                {if $WORK_INFO.plan_code}
                    <div id="sjv-field-plan" class="sjv-info-item"><div class="sjv-info-label">Plan de Servicios</div><div class="sjv-info-value"><a href="index.php?module=plan_de_mantenimiento&action=DetailView&record={$WORK_INFO.plan_de_servicios}"><i class="fa fa-list-alt"></i> {$WORK_INFO.plan_code|escape}</a></div></div>
                {/if}
                {if $WORK_INFO.fecha_de_emision_formatted}
                    <div id="sjv-field-fecha-emision" class="sjv-info-item"><div class="sjv-info-label">Fecha Emisión</div><div class="sjv-info-value"><i class="fa fa-calendar"></i> {$WORK_INFO.fecha_de_emision_formatted}</div></div>
                {/if}
                {if $WORK_INFO.fecha_de_inicio_formatted}
                    <div id="sjv-field-fecha-inicio" class="sjv-info-item"><div class="sjv-info-label">Fecha Inicio</div><div class="sjv-info-value"><i class="fa fa-calendar"></i> {$WORK_INFO.fecha_de_inicio_formatted}</div></div>
                {/if}
                {if $WORK_INFO.fecha_prevista_formatted}
                    <div id="sjv-field-fecha-prevista" class="sjv-info-item"><div class="sjv-info-label">Fecha Prevista Fin</div><div class="sjv-info-value"><i class="fa fa-calendar-o"></i> {$WORK_INFO.fecha_prevista_formatted}</div></div>
                {/if}
                {if $WORK_INFO.fecha_real_de_ci_formatted}
                    <div id="sjv-field-fecha-cierre" class="sjv-info-item"><div class="sjv-info-label">Fecha Cierre Real</div><div class="sjv-info-value"><i class="fa fa-calendar-check-o"></i> {$WORK_INFO.fecha_real_de_ci_formatted}</div></div>
                {/if}
                {if $WORK_INFO.unidades_de_medida}
                    <div id="sjv-field-unid-plan" class="sjv-info-item"><div class="sjv-info-label">Unid. Planificadas</div><div class="sjv-info-value large">{$WORK_INFO.numero_unidades_planificadas_formatted|default:"0"} <small style="font-size:12px;font-weight:normal;">{$WORK_INFO.unidades_de_medida}</small></div></div>
                    <div id="sjv-field-unid-cons" class="sjv-info-item"><div class="sjv-info-label">Unid. Consumidas</div><div class="sjv-info-value large">{$WORK_INFO.unidades_consumidas_formatted|default:"0"} <small style="font-size:12px;font-weight:normal;">{$WORK_INFO.unidades_de_medida}</small></div></div>
                {/if}
                <div id="sjv-field-costo-est" class="sjv-info-item"><div class="sjv-info-label">Costo Estimado</div><div class="sjv-info-value large">{$WORK_INFO.work_estimated_cost_formatted|default:"0"}</div></div>
                <div id="sjv-field-costo-eje" class="sjv-info-item"><div class="sjv-info-label">Costo Ejecutado</div><div class="sjv-info-value large">{$WORK_INFO.cost_work_performed_formatted|default:"0"}</div></div>
                {if $WORK_INFO.unit_ratio_formatted}
                    <div id="sjv-field-ratio-unid" class="sjv-info-item"><div class="sjv-info-label">Ratio Unidades</div><div class="sjv-info-value">{$WORK_INFO.unit_ratio_formatted}</div></div>
                {/if}
                {if $WORK_INFO.cost_ratio_formatted}
                    <div id="sjv-field-ratio-costo" class="sjv-info-item"><div class="sjv-info-label">Ratio Costo</div><div class="sjv-info-value">{$WORK_INFO.cost_ratio_formatted}</div></div>
                {/if}
            </div>
            {if $WORK_INFO.comentarios_resultado}
                <div id="sjv-comentarios-resultado" style="margin-top:14px; padding:12px 15px; background:#fff3cd; border-left:4px solid #ffc107; border-radius:4px; font-size:13px;">
                    <strong><i class="fa fa-comment"></i> Comentarios resultado:</strong><br>
                    {$WORK_INFO.comentarios_resultado|escape|nl2br}
                </div>
            {/if}
        </div>
    </div>

    {* ===== REPORTES DE AVANCE GLOBALES DEL TRABAJO ===== *}
    {if $JOB_REPORTS neq NULL && $JOB_REPORTS.reports|@count > 0}
        <div id="sjv-section-job-reports" class="sjv-section-card">
            <div class="sjv-section-header">
                <i class="fa fa-flag"></i> Reportes de avance del trabajo
                <span class="badge" style="margin-left:6px;">({$JOB_REPORTS.reports|@count})</span>
            </div>
            <div class="sjv-section-body">
                <div style="margin-bottom:8px; font-size:13px; color:#555;">
                    <i class="fa fa-briefcase"></i> <strong>{$JOB_REPORTS.task_title|escape}</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed sjv-table-reports" style="margin-bottom:0; background:white;">
                        <thead>
                            <tr>
                                <th style="width:9%;">Fecha avance</th>
                                <th style="width:11%;">Fecha registro</th>
                                <th style="width:10%;">Usuario</th>
                                <th style="width:27%;">Título</th>
                                <th style="width:9%; text-align:center;">% Avance</th>
                                <th style="width:10%; text-align:center;">Unidades</th>
                                <th style="width:11%; text-align:center;">Costo</th>
                                <th style="width:13%;">Evidencias</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$JOB_REPORTS.reports item=report}
                                <tr>
                                    <td style="vertical-align:middle;" rowspan="2">
                                        {if $report.activity_report_date neq NULL && $report.activity_report_date neq ''}{$report.activity_report_date}{else}<span style="color:#999;">-</span>{/if}
                                    </td>
                                    <td style="vertical-align:middle;" rowspan="2">
                                        {$report.formatted_datetime|default:'-'}
                                    </td>
                                    <td style="vertical-align:middle;">
                                        {$report.user_name|escape}
                                    </td>
                                    <td style="vertical-align:middle; padding:4px 6px;">
                                        {$report.title|escape|default:'-'}
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        {$report.progress_formatted}%
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        {$report.duration}
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        {$report.cost}
                                    </td>
                                    <td style="vertical-align:middle;" rowspan="2">
                                        {if $report.evidences|@count > 0}
                                            <ul style="list-style:none; padding:0; margin:0;">
                                                {foreach from=$report.evidences item=evidence}
                                                    <li style="margin-bottom:3px;">
                                                        <a href="{$evidence.uri}" target="_blank" style="color:#337ab7; text-decoration:none; font-size:11px;">
                                                            <i class="fa fa-file-o"></i> {$evidence.name|truncate:25:'...'|escape}
                                                        </a>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        {else}
                                            <span style="color:#999; font-style:italic; font-size:11px;">Sin evidencias</span>
                                        {/if}
                                    </td>
                                </tr>
                                {if $report.report neq ''}
                                    <tr>
                                        <td colspan="5" style="padding:5px 8px; font-size:11px; background:#f9f9f9; border-top:none;">
                                            <b>INFORME:</b> {$report.report|unescape:'html' nofilter}
                                        </td>
                                    </tr>
                                {else}
                                    <tr><td colspan="5" style="border-top:none; padding:0; height:1px; background:#f9f9f9;"></td></tr>
                                {/if}
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {/if}

    {* ===== DOCUMENTOS RELACIONADOS DEL TRABAJO ===== *}
    {if $WORK_DOCUMENTS|@count > 0}
        <div id="sjv-section-documents" class="sjv-section-card">
            <div class="sjv-section-header"><i class="fa fa-file-text-o"></i> Documentos <span class="badge" style="margin-left:6px;">({$WORK_DOCUMENTS|@count})</span></div>
            <div class="sjv-section-body">
                <div class="sjv-documents-list">
                    {foreach from=$WORK_DOCUMENTS item=doc}
                        <div class="sjv-document-item">
                            <i class="fa fa-file-o"></i>
                            <a href="{$doc.download_url}" onclick="window.open(this.href); return false;" title="{$doc.name|escape}">
                                {$doc.name|escape}
                            </a>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {* ===== ARCHIVOS ADJUNTOS DEL TRABAJO ===== *}
    {if $WORK_ATTACHMENTS|@count > 0}
        <div id="sjv-section-attachments" class="sjv-section-card">
            <div class="sjv-section-header"><i class="fa fa-paperclip"></i> Archivos Adjuntos <span class="badge" style="margin-left:6px;">({$WORK_ATTACHMENTS|@count})</span></div>
            <div class="sjv-section-body">
                <div class="sjv-attachments-list">
                    {foreach from=$WORK_ATTACHMENTS item=attachment}
                        <div class="sjv-attachment-item">
                            <i class="fa fa-file-o"></i>
                            <a href="{$attachment.uri}" target="_blank" title="{$attachment.name|escape}">{$attachment.name|truncate:40|escape}</a>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {* ===== TAREAS ===== *}
    <div id="sjv-section-tasks" class="sjv-section-card">
        <div class="sjv-section-header"><i class="fa fa-tasks"></i> Tareas <span class="badge" style="margin-left:6px;">({$TASKS|@count})</span></div>
        <div class="sjv-section-body">
            {if $TASKS|@count > 0}
                {foreach from=$TASKS item=task}

                    {* Clase de borde izquierdo según estado/condición *}
                    {assign var="taskCardClass" value=""}
                    {if $task.combined_condition eq 'PICK_ACTIVITY_DELAYED_OVER_BUDGET'}{assign var="taskCardClass" value="cond-critical"}
                    {elseif $task.combined_condition eq 'PICK_ACTIVITY_DELAYED_ON_BUDGET'}{assign var="taskCardClass" value="cond-delayed-budget"}
                    {elseif $task.combined_condition eq 'PICK_ACTIVITY_ON_TIME_OVER_BUDGET'}{assign var="taskCardClass" value="cond-over-budget"}
                    {elseif $task.eventstatus|lower eq 'held'}{assign var="taskCardClass" value="status-completed"}
                    {elseif $task.eventstatus|lower eq 'in progress'}{assign var="taskCardClass" value="status-in-progress"}
                    {elseif $task.eventstatus|lower eq 'not started'}{assign var="taskCardClass" value="status-planned"}
                    {/if}

                    <div id="sjv-task-{$task.activityid}" class="sjv-task-card {$taskCardClass}">

                        {* Título y meta de la tarea *}
                        <div class="sjv-task-title">
                            {$task.subject|escape}
                            {if $task.progress_weighting_factor > 0}
                                <small style="font-weight:normal; font-size:12px; color:#6c757d; margin-left:8px;">
                                    Ponderación: {$task.progress_weighting_factor_formatted}%
                                </small>
                            {/if}
                        </div>
                        <div class="sjv-task-meta">
                            <!--<span><i class="fa fa-hashtag"></i> #{$task.activityid}</span>-->
                            {if $task.date_start_formatted}<span><i class="fa fa-calendar"></i> {$task.date_start_formatted}</span>{/if}
                            {if $task.due_date_formatted}<span><i class="fa fa-calendar-o"></i> {$task.due_date_formatted}</span>{/if}
                            {if $task.assigned_user_name}<span><i class="fa fa-user"></i> {$task.assigned_user_name|escape}</span>{/if}
                            {if $task.supplier_name}<span><i class="fa fa-truck"></i> {$task.supplier_name|escape}</span>{/if}
                        </div>
                        {* Descripción *}
                        {if $task.description}
                            <div class="sjv-task-description">{$task.description nofilter}</div>
                        {/if}

                        {* Badges de estado, tipo, situación, etc. *}
                        <div class="sjv-task-badges">
                            {if $task.activitytype}
                                <span class="sjv-badge-pill badge-type">
                                    <i class="fa fa-tag"></i> {if $task.activitytype_translated neq ''}{$task.activitytype_translated}{else}{$task.activitytype}{/if}
                                </span>
                            {/if}
                            {if $task.eventstatus}
                                <span class="sjv-badge-pill badge-status">
                                    <i class="fa fa-check"></i> {if $task.eventstatus_translated neq ''}{$task.eventstatus_translated}{else}{$task.eventstatus}{/if}
                                </span>
                            {/if}
                            {if $task.importance}
                                <span class="sjv-badge-pill">
                                    {if $task.importance eq 'High' || $task.importance eq 'Alta'}<i class="fa fa-arrow-up"></i>{else}<i class="fa fa-arrow-down"></i>{/if}
                                    {if $task.importance_translated neq ''}{$task.importance_translated}{else}{$task.importance}{/if}
                                </span>
                            {/if}
                            {if $task.priority}
                                <span class="sjv-badge-pill">
                                    {if $task.priority eq 'High' || $task.priority eq 'Alta'}<i class="fa fa-arrow-up"></i>{else}<i class="fa fa-arrow-down"></i>{/if}
                                    {if $task.priority_translated neq ''}{$task.priority_translated}{else}{$task.priority}{/if}
                                </span>
                            {/if}
                            {if $task.location}
                                <span class="sjv-badge-pill"><i class="fa fa-map-marker"></i> {$task.location|escape}</span>
                            {/if}
                            <!--<span class="sjv-badge-pill">
                                <i class="fa fa-th"></i> Matriz: {if $task.show_in_matrix eq 'YES'}Sí{else}No{/if}
                            </span>-->
                            {* Badge de situación combinada con color *}
                            {if $task.combined_condition}
                                {assign var="condBg"    value="#e9ecef"}
                                {assign var="condColor" value="#495057"}
                                {if $task.combined_condition eq 'PICK_ACTIVITY_ON_TIME_ON_BUDGET'}{assign var="condBg" value="#388E3C"}{assign var="condColor" value="#fff"}{/if}
                                {if $task.combined_condition eq 'PICK_ACTIVITY_ON_TIME_OVER_BUDGET'}{assign var="condBg" value="#7B1FA2"}{assign var="condColor" value="#fff"}{/if}
                                {if $task.combined_condition eq 'PICK_ACTIVITY_DELAYED_ON_BUDGET'}{assign var="condBg" value="#F57C00"}{assign var="condColor" value="#fff"}{/if}
                                {if $task.combined_condition eq 'PICK_ACTIVITY_DELAYED_OVER_BUDGET'}{assign var="condBg" value="#D32F2F"}{assign var="condColor" value="#fff"}{/if}
                                <span style="display:inline-block; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:500; background:{$condBg}; color:{$condColor};">
                                    <i class="fa fa-info-circle"></i>
                                    {$task.combined_condition|@getTranslatedString:'Calendar'}
                                </span>
                            {/if}
                        </div>

                        {* Tabla comparativa Estimado / Real / Proporciones *}
                        <div class="table-responsive" style="margin-bottom:10px;">
                            <table class="table table-bordered table-condensed sjv-task-table" style="margin-bottom:0; background:white;">
                                <thead>
                                    <tr style="background:#f5f5f5;">
                                        <th style="width:16%;"></th>
                                        <th style="width:16%; text-align:center;">Fecha inicio</th>
                                        <th style="width:16%; text-align:center;">Realizar antes de</th>
                                        <th style="width:17%; text-align:center;">% Progreso</th>
                                        <th style="width:17%; text-align:center;">
                                            Unidades{if $task.estimated_time_unit neq ''} [{$task.estimated_time_unit}]{/if}
                                        </th>
                                        <th style="width:18%; text-align:center;">Costo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {* Fila 1: Estimado *}
                                    <tr>
                                        <td style="font-weight:bold; background:#f9f9f9;" class="control-label">Estimado</td>
                                        <td style="text-align:center;">{$task.date_start_formatted|default:'-'}</td>
                                        <td style="text-align:center;">{$task.due_date_formatted|default:'-'}</td>
                                        <td style="text-align:center;">{$task.estimated_progress_formatted|default:'0'}%</td>
                                        <td style="text-align:center;">{$task.estimated_time_formatted|default:'0'}</td>
                                        <td style="text-align:center;">{$task.estimated_cost_formatted|default:'0'}</td>
                                    </tr>
                                    {* Fila 2: Real *}
                                    <tr id="sjv-comparativa-tr-real">
                                        <td style="font-weight:bold; background:#f9f9f9;" class="control-label">Real</td>
                                        <td style="text-align:center;">{if $task.actual_data.has_reports}{$task.actual_data.min_date|default:'-'}{else}-{/if}</td>
                                        <td style="text-align:center;">{if $task.actual_data.has_reports}{$task.actual_data.max_date|default:'-'}{else}-{/if}</td>
                                        <td style="text-align:center;">{$task.progress_formatted|default:'0'}%</td>
                                        <td style="text-align:center;">{if $task.actual_data.has_reports}{$task.actual_data.total_duration_display|default:'0'}{else}0{/if}</td>
                                        <td style="text-align:center;">{if $task.actual_data.has_reports}{$task.actual_data.total_cost_display|default:'0'}{else}0{/if}</td>
                                    </tr>
                                    {* Fila 3: Proporciones *}
                                    <tr style="background:#f9f9f9; font-weight:bold;">
                                        <td style="font-weight:bold;" class="control-label">Proporciones</td>
                                        <td style="text-align:center;">{if $task.actual_data.has_reports}{$task.actual_data.min_date|default:'-'}{else}-{/if}</td>
                                        <td style="text-align:center;">{if $task.actual_data.has_reports}{$task.actual_data.max_date|default:'-'}{else}-{/if}</td>
                                        <td style="text-align:center;">{$task.progress_formatted|default:'0'}%</td>
                                        <td style="text-align:center;">
                                            {if $task.actual_data.has_reports && $task.indicators.duration_ratio_display neq ''}
                                                <span {if $task.indicators.duration_over_budget}class="sjv-over-budget"{/if}>
                                                    {$task.indicators.duration_ratio_display}
                                                </span>
                                            {else}-{/if}
                                        </td>
                                        <td style="text-align:center;">
                                            {if $task.actual_data.has_reports && $task.indicators.cost_ratio_display neq ''}
                                                <span {if $task.indicators.cost_over_budget}class="sjv-over-budget"{/if}>
                                                    {$task.indicators.cost_ratio_display}
                                                </span>
                                            {else}-{/if}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {* Barra de progreso de la tarea *}
                        {assign var="taskProgress" value=$task.progress|default:0|replace:',':'.'|floatval}
                        {assign var="taskExpected" value=$task.estimated_progress|default:0|replace:',':'.'|floatval}
                        <div class="sjv-task-progress-wrap">
                            <div class="sjv-task-progress-labels">
                                <span>Progreso: <strong>{$task.progress_formatted|default:'0'}%</strong></span>
                                {if $task.estimated_progress_formatted}<span>Esperado: {$task.estimated_progress_formatted}%</span>{/if}
                            </div>
                            <div class="sjv-task-progress-track">
                                <div class="sjv-task-progress-fill" style="width:{if $taskProgress > 100}100{else}{$taskProgress}{/if}%;"></div>
                                {if $taskExpected > 0}<div class="sjv-task-progress-marker" style="left:{if $taskExpected > 100}100{else}{$taskExpected}{/if}%;"></div>{/if}
                            </div>
                        </div>

                        {* ===== REPORTES DE AVANCE ===== *}
                        {if $task.reports|@count > 0}
                            <div class="sjv-subsection-title"><i class="fa fa-flag"></i> Reportes de avance ({$task.reports|@count})</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed sjv-table-reports" style="margin-bottom:0; background:white;">
                                    <thead>
                                        <tr>
                                            <th style="width:9%;">Fecha avance</th>
                                            <th style="width:11%;">Fecha registro</th>
                                            <th style="width:10%;">Usuario</th>
                                            <th style="width:27%;">Título</th>
                                            <th style="width:9%; text-align:center;">% Avance</th>
                                            <th style="width:10%; text-align:center;">Unidades</th>
                                            <th style="width:11%; text-align:center;">Costo</th>
                                            <th style="width:13%;">Evidencias</th>
                                            <!--<th style="width:8%; text-align:center;">Ops.</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$task.reports item=report}
                                            <tr>
                                                <td style="vertical-align:middle;" rowspan="2">
                                                    {if $report.activity_report_date neq NULL && $report.activity_report_date neq ''}{$report.activity_report_date}{else}<span style="color:#999;">-</span>{/if}
                                                </td>
                                                <td style="vertical-align:middle;" rowspan="2">
                                                    {$report.formatted_datetime|default:'-'}
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    {$report.user_name|escape}
                                                </td>
                                                <td style="vertical-align:middle; padding:4px 6px;">
                                                    {$report.title|escape|default:'-'}
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    {$report.progress_formatted}%
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    {$report.duration}
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    {$report.cost}
                                                </td>
                                                <td style="vertical-align:middle;" rowspan="2">
                                                    {if $report.evidences|@count > 0}
                                                        <ul style="list-style:none; padding:0; margin:0;">
                                                            {foreach from=$report.evidences item=evidence}
                                                                <li style="margin-bottom:3px;">
                                                                    <a href="{$evidence.uri}" target="_blank" style="color:#337ab7; text-decoration:none; font-size:11px;">
                                                                        <i class="fa fa-file-o"></i> {$evidence.name|truncate:25:'...'|escape}
                                                                    </a>
                                                                </li>
                                                            {/foreach}
                                                        </ul>
                                                    {else}
                                                        <span style="color:#999; font-style:italic; font-size:11px;">Sin evidencias</span>
                                                    {/if}
                                                </td>
                                                <!--<td style="text-align:center; vertical-align:middle;" rowspan="2">
                                                    {if $report.can_edit}
                                                        <a href="index.php?module=grid_view&action=EditActivityReport&record={$task.related_id}&formodule={$task.related_module}&activityid={$task.activityid}&reportid={$report.activityreportid}&Ajax=true"
                                                            target="_blank" class="btn btn-xs btn-primary" title="Editar reporte" style="padding:3px 7px;">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    {else}
                                                        <span style="color:#ccc;">-</span>
                                                    {/if}
                                                </td>-->
                                            </tr>
                                            {if $report.report neq ''}
                                                <tr id="sjv-table-tr-informe">
                                                    <td colspan="5" style="padding:5px 8px; font-size:11px; background:#f9f9f9; border-top:none;">
                                                        <b>INFORME:</b> {$report.report|unescape:'html' nofilter}
                                                    </td>
                                                </tr>
                                            {else}
                                                <tr><td colspan="5" style="border-top:none; padding:0; height:1px; background:#f9f9f9;"></td></tr>
                                            {/if}
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {/if}

                        {* ===== FEEDBACKS ===== *}
                        {if $task.feedbacks|@count > 0}
                            <div class="sjv-subsection-title"><i class="fa fa-comments"></i> Feedbacks ({$task.feedbacks|@count})</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed sjv-table-feedbacks" style="margin-bottom:0; background:white;">
                                    <thead>
                                        <tr>
                                            <th style="width:9%;">Fecha</th>
                                            <th style="width:14%;">Usuario</th>
                                            <th style="width:16%;">Reporte asociado</th>
                                            <th style="width:14%;">Título</th>
                                            <th style="width:47%;">Comentario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$task.feedbacks item=feedback}
                                            <tr>
                                                <td style="text-align:center; vertical-align:middle; font-size:11px;">
                                                    {$feedback.feedback_date|date_format:"%d/%m/%Y"}<br>
                                                    <span style="color:#999;">{$feedback.feedback_date|date_format:"%H:%M"}</span>
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    <div style="display:flex; align-items:center; gap:6px;">
                                                        <img src="{$feedback.user_avatar}"
                                                            style="width:30px; height:30px; border-radius:50%; border:1px solid #ddd; flex-shrink:0;"
                                                            alt="{$feedback.user_name|escape}">
                                                        <span style="font-size:12px;">{$feedback.user_name|escape}</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align:middle; font-size:11px;">
                                                    {if $feedback.report_title neq ''}
                                                        <strong>{$feedback.report_title|escape}</strong>
                                                    {else}
                                                        <span style="color:#999; font-style:italic;">-</span>
                                                    {/if}
                                                </td>
                                                <td style="vertical-align:middle; font-size:12px;">
                                                    {$feedback.title|escape}
                                                </td>
                                                <td style="vertical-align:middle; font-size:12px; padding:4px 8px;">
                                                    {$feedback.feedback|strip_tags|escape}
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {/if}

                    </div>{* /task-card *}
                {/foreach}
            {else}
                <div class="sjv-empty-state">
                    <i class="fa fa-tasks" style="font-size:40px; opacity:0.3; display:block; margin-bottom:10px;"></i>
                    <p>No hay tareas relacionadas con este trabajo.</p>
                </div>
            {/if}
        </div>
    </div>

    {* ===== RELACIONES DEL TRABAJO ===== *}
    {* Filtrar solo tarjetas con registros *}
    {assign var="relatedCardsWithData" value=[]}
    {foreach from=$RELATED_LIST_CARDS item=relatedCard}
        {assign var="entryCount" value=0}
        {if isset($relatedCard.cardData.entries)}
            {assign var="entryCount" value=$relatedCard.cardData.entries|@count}
        {/if}
        {if $entryCount > 0}
            {$relatedCardsWithData[] = $relatedCard}
        {/if}
    {/foreach}
    
    {if $relatedCardsWithData|@count > 0}
        <div id="sjv-section-related" class="sjv-section-card">
            <div class="sjv-section-header"><i class="fa fa-link"></i> Relaciones <span class="badge" style="margin-left:6px;">({$relatedCardsWithData|@count})</span></div>
            <div class="sjv-section-body">
                {foreach from=$relatedCardsWithData item=relatedCard}
                    <div class="sjv-related-card">
                        <div class="sjv-related-header">
                            <span class="sjv-related-title"><i class="fa fa-folder-o"></i> {$relatedCard.header|escape}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed sjv-related-table">
                                <thead>
                                    <tr>
                                        {foreach key=index item=_HEADER_FIELD from=$relatedCard.cardData.header}
                                            <th>{$_HEADER_FIELD}</th>
                                        {/foreach}
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach key=_RECORD_ID item=_RECORD from=$relatedCard.cardData.entries}
                                        <tr>
                                            {foreach key=index item=_RECORD_DATA from=$_RECORD.records}
                                                <td>{$_RECORD_DATA}</td>
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}

    {* ===== FOOTER ===== *}
    <div id="sjv-footer">
        <span style="color:#6c757d; font-size:11px;">
            <i class="fa fa-clock-o"></i> Generado: {$smarty.now|date_format:"%d/%m/%Y %H:%M"}
        </span>
    </div>

</div>
</body>
</html>
