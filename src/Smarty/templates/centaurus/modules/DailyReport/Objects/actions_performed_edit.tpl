<tr id="row_unregistered-action-__ID__" data-row-id="__ID__" class="tabla-field-row" style="vertical-align: top">
    {* Situación   *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="reported_task_condition-__ID__" name="performed_actions[reported_task_condition][]"
                onchange="DailyReportUtils.setUnregisteredActivity(this, '__ID__')" class="form-control">
                <option value="">Situación</option>
                <option value="PLANNED_UNREGISTERED">Planeada no registrada</option>
                <option value="UNEXPECTED">Imprevista</option>
            </select>
        </div>
    </td>
    {*  Acciones  *}
    <td style="vertical-align: top">
        <div id="list-reported_task_title-__ID__" class="input-group hide" style="width: 100%;"></div>
        <div id="input-reported_task_title-__ID__" class="input-group" style="width: 100%;">
            <input type="text" id="reported_task_title-__ID__" placeholder="Acción:"
                name="performed_actions[reported_task][]" value="" class="form-control">
        </div>
        {if $AVAILABLE_MODULES neq NULL}
            <div class="has-error">
                <select id="reported_task_module-__ID__" style="margin-top: 2px" data-current-module="daily_report"
                    data-display-field-id="reported_task_title-__ID__" data-field-id="module_related_record-__ID__"
                    data-referenced-module="" data-title="" onchange="DailyReportUtils.setModuleActivity(this, '__ID__')"
                    class="form-control hide">
                    <option value="">Modulo asociado</option>
                    {if $AVAILABLE_MODULES neq NULL}
                        {foreach $AVAILABLE_MODULES as $avaModule}
                            <!-- {$avaModule['label']} -->
                            {if $avaModule['label'] eq 'Trabajos'}{continue}{/if}
                            <option value="{$avaModule['value']}">{$avaModule['label']}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
            <input type="hidden" id="module_related_record-__ID__" name="performed_actions[relatedcrmids][]"
                class="for-filter module-reference" value="">
            <input type="hidden" id="module_related-__ID__" name="performed_actions[reported_task_module][]"
                class="for-filter module-reference" value="">
        {/if}
    </td>
    {*  Tiempo estimado  *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="estimated_time_task-__ID__" placeholder="Horas"
                name="performed_actions[estimated_time_task][]" value="" class="form-control"
                onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    {* Reporte de avance   *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="pc_task_advanced-__ID__" placeholder="% de avance"
                name="performed_actions[pc_task_advanced][]" value="" class="form-control"
                onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    {* Reporte de avance   *}
    <td style="vertical-align: top">
        <div id="input-task_advanced_report-__ID__" class="input-group" style="width: 100%;">
            <textarea id="task_advanced_report-__ID__" name="performed_actions[task_advanced_report][]"
                class="form-control" rows="2"></textarea>
        </div>
    </td>
    {*  Tiempo empleado  *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="time_reported-__ID__" name="performed_actions[time_reported][]" placeholder="Horas"
                value="" class="form-control performed-action-total-time"
                onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    {*  Costo incurrido  *}
    <td style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="actual_cost-__ID__" name="performed_actions[actual_cost][]" placeholder="Costo"
                value="" class="form-control numericvalidate"
                onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    {* Botones de acción   *}
    <td class="text-center" style="vertical-align: top">
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-primary btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowUp (this, 'row_unregistered-action-__ID__')">
                <i class="fa fa-arrow-up" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowDown (this, 'row_unregistered-action-__ID__')">
                <i class="fa fa-arrow-down" aria-hidden="true" style="font-size: 11px;"></i></button>
        </div>
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-warning btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-module="module_related-__ID__" data-id="module_related_record-__ID__"
                onclick="DailyReportUtils.uploadDoc (this, 'row_unregistered-action-__ID__')">
                <i class="fa fa-upload" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-template="#tbody-tasks-performed-" data-colspan="#planned_unregistered-colspan-template-"
                onclick="DailyReportUtils.delRowToTable (this, 'row_unregistered-action-__ID__', '{$idProgressJob}');">
                <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i></button>
        </div>
    </td>
</tr>