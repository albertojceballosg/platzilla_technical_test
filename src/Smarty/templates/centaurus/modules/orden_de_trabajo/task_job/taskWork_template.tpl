<tr class="tabla-field-row" valign="top" id="tr-row-__ID__">
    <td style="vertical-align: middle">
        <textarea name="projec_task[task_title][]" id="task_title-__ID__" placeholder="Título de la tarea"
            class="form-control daily-report-scroll tinymce-class edit-tinyMce"></textarea>
        <input type="hidden" name="projec_task[taskId][]" value="">
    </td>
    <td style="vertical-align: middle">
        <textarea name="projec_task[task][]" id="task-__ID__" placeholder="Descripción de la tarea"
            class="form-control daily-report-scroll tinymce-class edit-tinyMce"></textarea>
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_ACTIVITY_TYPES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="types__ID__" name="projec_task[types][]" onchange="" class="form-control">
                    {foreach $AVAILABLE_ACTIVITY_TYPES as $type => $label}
                        <option value="{$type}">{$label}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="start_date-__ID__" placeholder="Fecha de inicio" name="projec_task[start_date][]"
                value="{$TODAY}" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="due_date-__ID__" placeholder="Fecha de cierre" name="projec_task[due_date][]"
                value="{$TOMORROW}" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_EVENT_STATUSES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="status-__ID__" name="projec_task[status][]" onchange="" class="form-control">
                    {foreach $AVAILABLE_EVENT_STATUSES as $key => $status}
                        <option value="{$key}">{$status}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_SYSTEM_USERS neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="assigned-__ID__" name="projec_task[assigned][]" onchange="" class="form-control">
                    {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                        <option value="{$systemUser->getId()}" {if $systemUser->getId() eq $CURRENT_USER_ID}selected{/if}>
                            {$systemUser->getFirstName()} {$systemUser->getLastName()}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="hidden" id="supplier-__ID__" name="projec_task[supplier][]" value=""
                class="module-reference" />
            <input type="text" id="edit_supplier-__ID___display" name="projec_task[supplier_display][]" value=""
                class="form-control input-readonly b-right" readonly="readonly" placeholder="">
            <div class="input-group-addon" data-current-module="orden_de_trabajo"
                data-display-field-id="edit_supplier-__ID___display" data-field-id="supplier-__ID__"
                data-referenced-module="proveedores" data-title="{$TASK_EXECUTOR_LABEL}"
                onclick="RelatedModuleModalUtils.openModal (this);" style="padding: 6px 8px; width: 32px;">
                <i class="fa fa-plus-circle"></i>
            </div>
            <div class="input-group-addon"
                onClick="var fieldContainer = jQuery (this).closest ('.input-group'); fieldContainer.find ('#edit_supplier-__ID___display').val (''); fieldContainer.find ('#supplier-__ID__').val (''); return false;"
                style="padding: 6px 8px; width: 32px;">
                <i class="fa fa-eraser"></i>
            </div>
        </div>
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_ESTIMATED_TIME_UNITS neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="estimated_time_unit-__ID__" name="projec_task[estimated_time_unit][]" class="form-control"
                    onchange="TaskWorkUtls.updateNumFields(this, '{$idTaskProject}')">
                    {foreach $AVAILABLE_ESTIMATED_TIME_UNITS as $unitKey => $unitLabel}
                        <option value="{$unitKey}" {if $unitKey eq 'Hora'}selected{/if}>{$unitLabel}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="duration-__ID__" placeholder="Unidades" name="projec_task[duration][]" value=""
                class="form-control duration-time" onkeyup="TaskWorkUtls.updateNumFields(this, '{$idTaskProject}')">
        </div>
    </td>
    <td style="vertical-align: middle; text-align:right;">
        <div class="input-group" style="width: 100%;text-align:right;">
            <input type="text" id="estimated-cost-__ID__" placeholder="Costo" name="projec_task[estimated_cost][]"
                value="" class="form-control estimated-cost-field" style="width: 100%;text-align:right;"
                onkeyup="TaskWorkUtls.updateNumFields(this, '{$idTaskProject}')">
        </div>
    </td>
    <td style="vertical-align: middle; text-align:center;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="progress_weighting_factor-__ID__" placeholder="%"
                name="projec_task[progress_weighting_factor][]" value="" class="form-control"
                style="text-align:center;">
        </div>
    </td>
    <td class="text-center" style="vertical-align: middle">
        <button type="button" class="btn btn-primary btn-xs" onclick="TaskWorkUtls.moveRowUp (this, 'tr-row-__ID__')"><i
                class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
            onclick="TaskWorkUtls.moveRowDown (this, 'tr-row-__ID__')"><i class="fa fa-arrow-down"
                aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
            onclick="TaskWorkUtls.delRowToTable (this, 'tr-row-__ID__', '{$idTaskProject}');"><i
                class="fa fa-trash-o"></i>
        </button>
        <button type="button" class="btn btn-success btn-xs" rel="__ID__" data-toggle="modal"
            data-target="#precreated-task-{$idTaskDetailView}" data-source="__ID__"><i class="fa fa-list-alt"></i>
        </button>
    </td>
</tr>