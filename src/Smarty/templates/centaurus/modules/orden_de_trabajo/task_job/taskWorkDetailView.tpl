{extends file='modules/orden_de_trabajo/base/TaskWorkLayout.tpl'}
{assign var="duration" value=0}
{assign var="total_progress" value=0}
{assign var="reported_total" value=0}
{assign var="estimated_cost_total" value=0}
{assign var="reported_cost_total" value=0}
{assign var="reported_duration_filtered" value=0}
{assign var="total_weighting" value=0}
{assign var="total_work_progress" value=0}

{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css" />
    <style type="text/css">
        /* Contenedor responsive para la tabla */
        .task-work-container {
            max-width: 100%;
            overflow-x: auto;
        }

        /* Permitir que los tooltips de la columna situación se muestren fuera del contenedor */
        .task-work-container .task-situation-cell [data-toggle="tooltip"] {
            position: relative;
        }
        
        .task-work-container .task-situation-cell .tooltip {
            position: fixed !important;
            z-index: 9999 !important;
        }
        
        .task-work-container .task-situation-cell .tooltip-inner {
            max-width: 300px !important;
            word-wrap: break-word !important;
        }

        /* Forzar layout de tabla fijo para respetar anchos */
        .task-work-table {
            table-layout: fixed;
            width: 100%;
            max-width: 100%;
        }

        /* Control de overflow solo en celdas de datos, no en encabezados */
        .task-work-table td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Los encabezados deben mostrar texto completo */
        .task-work-table th {
            overflow: visible;
            white-space: normal;
            word-wrap: break-word;
        }

        /* Permitir wrapping en columnas de texto (asunto, descripción y situación) */
        .task-work-table td:nth-child(1),
        .task-work-table td:nth-child(2),
        .task-work-table td:nth-child(16) {
            white-space: normal;
            word-wrap: break-word;
            max-width: 0;
        }

        /* Mantener estilos existentes */
        div[id^="task-project-table-"] {
            max-width: 100% !important;
        }

        .task-work-table>thead>tr>td,
        .task-work-table>tbody>tr>td,
        .task-work-table>tfoot>tr>td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .task-work-table textarea.form-control {
            margin-bottom: 0;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        table[id^="task-project-table-"] tfoot input[id^="summary-weighting-"].form-control {
            border: 0px !important;
            width: 100%;
            text-align: center;
        }

        /* Clases para coloreo de celdas de progreso según combined_condition */
        .task-cell-retraso-sobrecosto {
            background-color: #D32F2F !important;
            color: white !important;
        }

        .task-cell-retraso-costo {
            background-color: #F57C00 !important;
            color: white !important;
        }

        .task-cell-tiempo-sobrecosto {
            background-color: #7B1FA2 !important;
            color: white !important;
        }

        .task-cell-tiempo-costo {
            background-color: #388E3C !important;
            color: white !important;
        }
    </style>
{/block}
{block name="table_margin"}{/block}
{block name="card_header"}{/block}
{block name="colspan_header"}colspan="16"{/block}
{block name="col_0"} {/block}
<!-- Asunto -->
{block name="col_1"} {/block}
<!-- Descripción -->
{block name="col_2"} {/block}
<!-- Tipo -->
{block name="col_3"} {/block}
<!-- Inicio -->
{block name="col_4"} {/block}
<!-- Realizar antes de -->
{block name="col_2b"} {/block}
<!-- Tipo de unidad -->
{block name="col_5"} {/block}
<!-- Duración estimada -->
{block name="col_5b"} {/block}
<!-- Costo estimado -->
{block name="col_6"} {/block}
<!-- Asignado -->
{block name="col_supplier"} {/block}
<!-- Ejecutor -->
{block name="col_7"} {/block}
<!-- % Avance -->
{block name="col_reported"} {/block}
<!-- Horas -->
{block name="col_costreported"} {/block}
<!-- Costo reportado -->
{block name="col_status"}{/block}
<!-- estado-->
{block name="col_situation"}{/block}
<!-- situación-->
{block name="col_help"}{/block}
<!-- ayuda-->
<!--{block name="col_action"}&nbsp;{/block}-->

{block name="tbody_task_project"}
    {*$RELATED_TASK|var_dump*}
    {if $RELATED_TASK neq NULL}
        {foreach $RELATED_TASK as $key => $relatedTask}
            {math equation= rand() assign= "idRow"}
            {include file='modules/orden_de_trabajo/task_job/taskWorkDetailView_template.tpl'}
            {* Calcular solo los totales que no dependen de filtrado por unidad *}
            {if $relatedTask['types'] neq 'Job'}
                {* Unidades estimadas: filtrado por tipo de unidad (se hace en backend) *}
                {if $WORK_UNIT_OF_MEASURE and $relatedTask['estimated_time_unit'] and $relatedTask['estimated_time_unit'] eq $WORK_UNIT_OF_MEASURE}
                    {$duration = $duration + $relatedTask['estimated_time']}
                {/if}
                {* Unidades ejecutadas: filtrado por tipo de unidad (misma política que estimadas) *}
                {if $WORK_UNIT_OF_MEASURE and $relatedTask['estimated_time_unit'] and $relatedTask['estimated_time_unit'] eq $WORK_UNIT_OF_MEASURE}
                    {$reported_duration_filtered = $reported_duration_filtered + $relatedTask['reported_hours']}
                {/if}
                {assign var="prog_raw" value=$relatedTask['progress']|default:0}
                {assign var="prog_val" value=$prog_raw|replace:',':'.'}
                {$total_progress = $total_progress + $prog_val}
                {$estimated_cost_total = $estimated_cost_total + $relatedTask['estimated_cost_raw']}
                {assign var="pwf_raw" value=$relatedTask['progress_weighting_factor']|default:0}
                {assign var="pwf_val" value=$pwf_raw|replace:',':'.'}
                {$total_weighting = $total_weighting + $pwf_val}
                {assign var="prog_raw" value=$relatedTask['progress']|default:0}
                {assign var="prog_val" value=$prog_raw|replace:',':'.'}
                {math equation="(pwf * progress) / 100" pwf=$pwf_val progress=$prog_val assign="work_progress_value"}
                {$total_work_progress = $total_work_progress + $work_progress_value}
            {/if}
        {/foreach}
    {/if}
{/block}
{block name="summaryRow"}
    <tr>
        <td colspan="8" style="text-align: right;"> <!-- 1 a 8 --> 
            <b>{'LBL_TASK_TOTAL'|@getTranslatedString:'orden_de_trabajo'}</b>
        </td>
        <td style="text-align: right;">	<!-- 9 -->
            <input type="text" id="duration-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$duration|number_format:2:',':'.'}{else}{$duration|number_format:2:'.':','}{/if}{if $WORK_UNIT_OF_MEASURE} {$WORK_UNIT_OF_MEASURE}{/if}"
                class="form-control" readonly="" style="text-align: right;">
        </td>
        <td style="text-align: right;"> <!-- 10 -->
            <input type="text" id="estimated-cost-total-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$estimated_cost_total|number_format:2:',':'.'}{else}{$estimated_cost_total|number_format:2:'.':','}{/if}"
                class="form-control" style="text-align: right;" readonly="">
        </td>
        <td style="text-align: right;"> <!-- 11 -->
            <input type="text" id="summary-weighting-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$total_weighting|number_format:2:',':'.'}{else}{$total_weighting|number_format:2:'.':','}{/if}"
                class="form-control" style="text-align: right; border-color: transparent; box-shadow: none;" readonly="">
        </td>
        <td style="text-align: right; background-color: #e0dfde;">  <!-- 12 -->
            <input type="text" id="reported-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$reported_duration_filtered|number_format:2:',':'.'}{else}{$reported_duration_filtered|number_format:2:'.':','}{/if}{if $WORK_UNIT_OF_MEASURE} {$WORK_UNIT_OF_MEASURE}{/if}"
                class="form-control" style="text-align: right;" readonly="">
        </td>
        <td style="text-align: right; background-color: #e0dfde;">  <!-- 13 -->
            <input type="text" id="reported-cost-total-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$REPORTED_COST_TOTAL|number_format:2:',':'.'}{else}{$REPORTED_COST_TOTAL|number_format:2:'.':','}{/if}"
                class="form-control" style="text-align: right;" readonly="">
        </td>
        <td style="background-color: #e0dfde;">&nbsp;</td>    <!-- 14 -->
		
        <td style="text-align: center; background-color: #e0dfde;"> <!-- 15 -->
            <input type="text" id="summary-work-progress-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$total_work_progress|number_format:2:',':'.'}{else}{$total_work_progress|number_format:2:'.':','}{/if}"
                class="form-control" style="text-align: center; border-color: transparent; box-shadow: none;" readonly="">
        </td>
        <td style="background-color: #e0dfde;">&nbsp;</td>   <!-- 16 -->
    </tr>
{/block}

{block name="modal"}
    {assign var="idCalendar" value="work_`$WORK_ID`"}
    <script type="text/html" id="worktask-activity-modal-template-{$idCalendar}">
        <div class="modal fade" id="activity-modal-{$idCalendar}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document" style="width: 850px; max-width: 95%;">
                <div class="modal-content" style="height: auto;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Nueva tarea</h4>
                    </div>
                    <div class="modal-body">
                        {include file='Home/ActionTabs/ActivityModal.tpl'}
                    </div>
                    <div class="modal-footer">
                        <input type="button" value="Guardar" id="task-create-btn-{$idCalendar}"
                             class="btn btn-primary activity-modal-btn add_button">
                        <button type="button" class="btn btn-default activity-modal-btn"
                            data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </script>
{/block}

{block name="script"}
    <script type="text/javascript" src="modules/Calendar/TaskViewModal.js"></script>
    <script type="text/javascript" src="modules/orden_de_trabajo/TaskWorkProgressColors.js"></script>
    <script type="text/javascript">
        (function(jQuery) {
            'use strict';
            var viewId = 'work_{$WORK_ID}';
            var workId = '{$WORK_ID}';
            var modalSel = '#activity-modal-' + viewId;
            var modalTplSel = '#worktask-activity-modal-template-' + viewId;

            function ensureModalInBody() {
                if (jQuery(modalSel).length) {
                    return;
                }
                var tpl = jQuery(modalTplSel);
                if (tpl.length) {
                    jQuery('body').append(tpl.html());
                }
            }

            function setHidden(form, name, value) {
                var el = form.find('input[name="' + name + '"]');
                if (el.length === 0) {
                    el = jQuery('<input>', { type: 'hidden', name: name });
                    form.append(el);
                }
                el.val(value);
            }

            function setBtnLabel(btnSel, text) {
                var btn = jQuery(btnSel);
                if (!btn.length) return;
                var label = btn.find('.btn-label');
                if (label.length) label.text(text);
            }

            function bindOnce() {
                var modal = jQuery(modalSel);
                if (modal.data('worktask-bound')) return;
                modal.data('worktask-bound', true);

                modal.on('click', '#detailview-task-importance-' + viewId + ' a', function(e) {
                    e.preventDefault();
                    var a = jQuery(this);
                    jQuery('#taskImport-' + viewId).val(a.attr('rel'));
                    setBtnLabel('#btn-group-importance-' + viewId, a.text().trim());
                });
                modal.on('click', '#detailview-task-priority-' + viewId + ' a', function(e) {
                    e.preventDefault();
                    var a = jQuery(this);
                    jQuery('#taskpriority-' + viewId).val(a.attr('rel'));
                    setBtnLabel('#btn-group-priority-' + viewId, a.text().trim());
                });
                modal.on('click', '#detailview-task-categories-' + viewId + ' a', function(e) {
                    e.preventDefault();
                    var a = jQuery(this);
                    jQuery('#categoryid-' + viewId).val(a.attr('rel'));
                    setBtnLabel('#btn-group-task-categories-' + viewId, a.text().trim());
                });

                jQuery(document).on('click', '#task-create-btn-' + viewId, function(e) {
                    e.preventDefault();
                    var form = jQuery('#main_input_box-' + viewId);
                    if (!form.length) return;
                    setHidden(form, 'module', 'Calendar');
                    setHidden(form, 'action', 'Save');
                    setHidden(form, 'function', 'TASK_FROM_MODULE');
                    setHidden(form, 'Ajax', 'true');
                    jQuery.ajax({
                        url: 'index.php',
                        type: 'POST',
                        data: form.serializeArray(),
                        dataType: 'json'
                    }).done(function(resp) {
                        if (resp && resp.error === 'OK') {
                            jQuery(modalSel).modal('hide');
                            window.location.reload();
                        } else {
                            alert('Error al guardar la tarea');
                        }
                    }).fail(function() {
                        alert('Error de red al guardar la tarea');
                    });
                });
            }

            window.WorkTaskActivityModal = window.WorkTaskActivityModal || {};
            window.WorkTaskActivityModal.openEdit = function(activityId) {
                ensureModalInBody();
                bindOnce();
                var form = jQuery('#main_input_box-' + viewId);
                if (!form.length) return;

                setHidden(form, 'record', activityId);
                setHidden(form, 'mode', 'edit');
                setHidden(form, 'formodule', 'orden_de_trabajo');
                setHidden(form, 'relatedcrmids', workId);
                jQuery.post('index.php', {
                    module: 'Home',
                    action: 'AjaxHomeUtils',
                    function: 'FETCH-ACTIVITY-WIZARD',
                    record: activityId,
                    Ajax: true
                }, function(r) {
                    if (r && r.error === 'OK' && r.html) {
                        jQuery('#taskname-' + viewId).val(r.html.subject || '');
                        jQuery('#task_description-' + viewId).val(r.html.description || '');
                        jQuery('#date_start-' + viewId).val(r.html.startdate || '');
                        jQuery('#due_date-' + viewId).val(r.html.enddate || '');
                        jQuery('#start_time-' + viewId).val(r.html.starttime || '09:00:00');
                        if (r.html.taskpriority) {
                            jQuery('#taskpriority-' + viewId).val(r.html.taskpriority);
                            setBtnLabel('#btn-group-priority-' + viewId, r.html.taskpriority === 'High' ?
                                'Alta' : 'Básica');
                        }
                        if (r.html.importance) {
                            jQuery('#taskImport-' + viewId).val(r.html.importance);
                            setBtnLabel('#btn-group-importance-' + viewId, r.html.importance === 'HIGH' ?
                                'Alta' : 'Estándar');
                        }
                        if (r.html.categoryid) {
                            jQuery('#categoryid-' + viewId).val(r.html.categoryid);
                        }
                    }
                    if (!jQuery('#taskImport-' + viewId).val()) {
                        jQuery('#taskImport-' + viewId).val('HIGH');
                        setBtnLabel('#btn-group-importance-' + viewId, 'Alta');
                    }
                    if (!jQuery('#taskpriority-' + viewId).val()) {
                        jQuery('#taskpriority-' + viewId).val('Low');
                        setBtnLabel('#btn-group-priority-' + viewId, 'Básica');
                    }
                    jQuery(modalSel).modal({ backdrop: 'static', keyboard: false, show: true });
                }, 'json');
            };
        })(jQuery);

        // Aplicar estilo condicional al total de % ponderación
        jQuery(document).ready(function() {
            var taskIdProject = '{$idTaskProject}';
            var weightingElement = jQuery('#summary-weighting-' + taskIdProject);

            if (weightingElement.length) {
                var totalValue = parseFloat(weightingElement.val()) || 0;
                var parentTd = weightingElement.closest('td');

                if (totalValue > 100) {
                    parentTd.css('background-color', '#ffb2b2');
                    weightingElement.css({
                        'font-weight': 'bold',
                        'background-color': '#ffb2b2',
                        'border-color': '#ffb2b2'
                    });
                    weightingElement.attr('style', function(i, style) {
                        return style + '; background-color: #ffb2b2 !important;';
                    });
                }
            }
        });
    </script>
{/block}