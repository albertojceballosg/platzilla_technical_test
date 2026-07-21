{extends file='modules/orden_de_trabajo/base/TaskWorkEditLayout.tpl'}
{math equation= rand() assign= "idTaskDetailView"}
{assign var="estimated" value=0}
{assign var="duraction" value=0}
{assign var="estimated_cost_total" value=0}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css" />
    <style type="text/css">
        /* Reducir padding vertical general de la tabla de tareas */
        .task-work-table>thead>tr>td,
        .task-work-table>tbody>tr>td,
        .task-work-table>tfoot>tr>td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        /* Reducir espacio extra bajo los textarea de Asunto y Descripción */
        .task-work-table textarea.form-control {
            margin-bottom: 0;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        /* Botones de Acciones: iconos pequeños, separados y cuadrados */
        .task-work-table tbody tr td:last-child .btn {
            margin: 1px;
            width: 24px;
            /* mismo valor para ancho y alto para hacerlos cuadrados */
            height: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .task-work-table tbody tr td:last-child .btn i {
            font-size: 0.7em;
            line-height: 1;
        }

        table[id^="task-project"] tfoot input[id^="summary-estimated-cost-"].form-control {
            border: 0px !important;
            width: 100%;
            text-align: right;
        }

        table[id^="task-project"] tfoot input[id^="summary-weighting-"].form-control {
            border: 0px !important;
            width: 100%;
            text-align: center;
        }
    </style>
{/block}

{block name="table_margin"} style="margin-top: 20px"{/block}
{block name="card_header"}
    {if ($RELATED_TASK neq NULL)}
        <div class="row card-header platzilla-card-header" style="padding-left: 0!important;">
            <div class="col-md-5">
                <p class="text-center pull-left" style="font-weight: bold">Tareas del trabajo</p>
            </div>
            <div class="col-md-7">&nbsp;</div>
        </div>
    {/if}
{/block}

{block name="colspan_header"} colspan="14"{/block}

{block name="tbody_task_project"}
    {*$RELATED_TASK|var_dump*}
    {if $RELATED_TASK neq NULL}
        {foreach $RELATED_TASK as $key => $relatedTask}
            {math equation= rand() assign= "idRow"}
            {include file='modules/orden_de_trabajo/task_job/taskWorkEdit_template.tpl'}
            {$estimated = $estimated + $relatedTask['duration']}
            {$duration = $duration + $relatedTask['estimated_time']}
            {$estimated_cost_total = $estimated_cost_total + $relatedTask['estimated_cost_raw']}
        {/foreach}
    {else}
        {assign var="key" value= -1}
        <tr>
            <td colspan="9" style="text-align: center"></td>
        </tr>
    {/if}
{/block}
{block name="summaryRow"}
    <tr>
        <td colspan="9" style="text-align: right;">
            <b>{'LBL_TASK_TOTAL'|@getTranslatedString:'orden_de_trabajo'}</b>
        </td>
        <td style="text-align: left;">
            <input type="text" id="summary-duration-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$duration|number_format:2:',':'.'}{else}{$duration|number_format:2:'.':','}{/if}{if $WORK_UNIT_OF_MEASURE} {$WORK_UNIT_OF_MEASURE}{/if}"
                class="form-control" readonly="">
        </td>
        <td style="text-align: right;">
            <input type="text" id="summary-estimated-cost-{$idTaskProject}"
                value="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$estimated_cost_total|number_format:2:',':'.'}{else}{$estimated_cost_total|number_format:2:'.':','}{/if}"
                class="form-control" style="text-align: right; border-color: transparent; box-shadow: none;" readonly="">
        </td>
        <td style="text-align: center;">
            <input type="text" id="summary-weighting-{$idTaskProject}" value="0" class="form-control"
                style="text-align: center; border-color: transparent; box-shadow: none;" readonly="">
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
{/block}
{block name="addRow"}
    <tr>
        <td colspan="13" class="text-center">
            <button type="button" data-id-linkage="{$idTaskProject}" class="btn btn-primary" data-sequence="{($key + 1)}"
                onclick="TaskWorkUtls.addRowToTable (this, 'tbody-task-project-{$idTaskProject}', '{$idTaskProject}');">
                <i class="fa fa-plus"></i></button>
        </td>
    </tr>
{/block}
{block name="global_task"}
    <input type="hidden" id="usr" value="{$CURRENT_USER_ID}">
    <input type="hidden" id="work-progress-{$idTaskProject}"
        value="{if $TASK_WORK neq NULL}{$TASK_WORK->getProgress()}{else}0{/if}">
    <input type="hidden" id="time-duration-{$idTaskProject}" value="{$duration}">
{/block}
{block name="script"}
    <script type="text/javascript">
        if (typeof gUserDateFormat === 'undefined') {
            var gUserDateFormat = '{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}';
        }
    </script>
    <script src="modules/preloaded_tasks/precreated-task-utils.js"></script>
    <script src="modules/orden_de_trabajo/task-work-utls.js"></script>
    <script type="text/javascript">
        TaskWorkUtls.setCalendar('{$idTaskProject}');

        jQuery('#precreated-task-{$idTaskDetailView}').on('shown.bs.modal', function(event) {
        var button = jQuery(event.relatedTarget),
            source = button.data('source'),
            modalId = jQuery(this).attr('id').split('-')[2];
        jQuery('#row-' + modalId).val(source);
        });

        function formatNumberForUser(value) {
            if (!value && value !== 0) return '';
            var num = parseFloat(value);
            if (isNaN(num)) return '';
            var numberFormat = '{$NUMBERING_FORMAT}';
            if (numberFormat === 'EUROPEAN_FORMAT') {
                var parts = num.toFixed(2).split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return parts.join(',');
            } else {
                var parts = num.toFixed(2).split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                return parts.join('.');
            }
        }

        function convertFormattedNumberToDB(formattedValue) {
            if (!formattedValue || formattedValue === '') return 0;
            var cleanValue = formattedValue.toString();
            // Primero eliminar unidades de medida (letras al final) y espacios
            cleanValue = cleanValue.replace(/[a-zA-Z]+$/g, '').trim();
            cleanValue = cleanValue.replace(/\s/g, '');

            var hasComma = cleanValue.indexOf(',') !== -1;
            var hasDot = cleanValue.indexOf('.') !== -1;

            if (hasComma && hasDot) {
                // Formato europeo: 1.234,56 → 1234.56
                cleanValue = cleanValue.replace(/\./g, '').replace(',', '.');
            } else if (hasComma) {
                // Solo coma: puede ser decimal europeo (30,35) o millar americano (1,234)
                // Usar formato del usuario para decidir
                var numberFormat = '{$NUMBERING_FORMAT}';
                if (numberFormat === 'EUROPEAN_FORMAT') {
                    // En formato europeo, la coma es decimal
                    cleanValue = cleanValue.replace(',', '.');
                } else {
                    // En formato americano, la coma es separador de miles
                    cleanValue = cleanValue.replace(/,/g, '');
                }
            }
            // Si solo hay punto, ya está en formato correcto

            cleanValue = cleanValue.replace(/[^\d\.-]/g, '');
            var result = parseFloat(cleanValue);
            return isNaN(result) ? 0 : result;
        }

        function calculateWeightingTotal() {
            var taskIdProject = '{$idTaskProject}';
            var total = 0;

            jQuery('input[id^="progress_weighting_factor-"]').each(function() {
                var value = convertFormattedNumberToDB(jQuery(this).val());
                if (!isNaN(value)) {
                    total += value;
                }
            });

            var weightingElement = jQuery('#summary-weighting-' + taskIdProject);
            if (weightingElement.length) {
                weightingElement.val(formatNumberForUser(total));

                var parentTd = weightingElement.closest('td');
                if (total > 100) {
                    parentTd.css('background-color', '#ffb2b2');
                    weightingElement.css({
                        'font-weight': 'bold',
                        'background-color': '#ffb2b2',
                        'border-color': '#ffb2b2'
                    });
                    weightingElement.attr('style', function(i, style) {
                        return style + '; background-color: #ffb2b2 !important;';
                    });
                } else {
                    parentTd.css('background-color', '');
                    weightingElement.css({
                        'font-weight': '',
                        'background-color': '',
                        'border-color': 'transparent'
                    });
                    weightingElement.attr('style', function(i, style) {
                        if (style) {
                            return style.replace(/;\s*background-color:\s*#ffb2b2\s*!important;?/gi, '');
                        }
                        return style;
                    });
                }
            }
        }

        function syncWorkFieldsWithGridTotals() {
            var taskIdProject = '{$idTaskProject}';
            var durationElement = jQuery('#summary-duration-' + taskIdProject);
            var estimatedCostElement = jQuery('#summary-estimated-cost-' + taskIdProject);

            if (durationElement.length && estimatedCostElement.length) {
                var durationValue = durationElement.val();
                var estimatedCostValue = estimatedCostElement.val();
                var duration = convertFormattedNumberToDB(durationValue);
                var estimatedCost = convertFormattedNumberToDB(estimatedCostValue);

                var plannedUnitsField = jQuery('input[name="numero_unidades_planificadas"]');
                var estimatedCostField = jQuery('input[name="work_estimated_cost"]');

                if (plannedUnitsField.length) {
                    var currentPlannedUnits = convertFormattedNumberToDB(plannedUnitsField.val()) || 0;
                    if (duration > currentPlannedUnits) {
                        plannedUnitsField.val(formatNumberForUser(duration));
                        plannedUnitsField.trigger('change');
                    }
                }

                if (estimatedCostField.length) {
                    var currentEstimatedCost = convertFormattedNumberToDB(estimatedCostField.val()) || 0;
                    if (estimatedCost > currentEstimatedCost) {
                        estimatedCostField.val(formatNumberForUser(estimatedCost));
                        estimatedCostField.trigger('change');
                    }
                }
            }

            calculateWeightingTotal();
        }

        function setupGridTotalsObserver() {
            var taskIdProject = '{$idTaskProject}';
            var durationElement = jQuery('#summary-duration-' + taskIdProject);
            var estimatedCostElement = jQuery('#summary-estimated-cost-' + taskIdProject);

            if (durationElement.length && estimatedCostElement.length) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            syncWorkFieldsWithGridTotals();
                        }
                    });
                });
                observer.observe(durationElement[0], { attributes: true, attributeFilter: ['value'] });
                observer.observe(estimatedCostElement[0], { attributes: true, attributeFilter: ['value'] });
                durationElement.on('input blur change', syncWorkFieldsWithGridTotals);
                estimatedCostElement.on('input blur change', syncWorkFieldsWithGridTotals);
            }

            var taskTable = jQuery('table[id^="task-project-"]');
            if (taskTable.length) {
                taskTable.on('input change blur', 'input, select, textarea', function() {
                    setTimeout(syncWorkFieldsWithGridTotals, 200);
                });
            }
        }

        jQuery(document).ready(function() {
            setTimeout(function() {
                setupGridTotalsObserver();
                calculateWeightingTotal();
            }, 500);
        });

        jQuery(document).on('taskWorkUpdated', function() {
            setTimeout(syncWorkFieldsWithGridTotals, 100);
        });

        jQuery(document).on('DOMNodeInserted DOMNodeRemoved', 'tbody[id^="tbody-task-project-"]', function() {
            setTimeout(syncWorkFieldsWithGridTotals, 300);
        });
    </script>
{/block}
{block name="modal"}
    {include file='PrecreatedTaskActivity.tpl'}
{/block}
{block name="script_template"}
    <script type="text/html" id="task-project-template-{$idTaskProject}">
        {include file='modules/orden_de_trabajo/task_job/taskWork_template.tpl'}
    </script>
    <script type="text/html" id="task-project-tr-{$idTaskProject}">
        <tr>
            <td colspan="9" style="text-align: center"></td>
        </tr>
    </script>
{/block}