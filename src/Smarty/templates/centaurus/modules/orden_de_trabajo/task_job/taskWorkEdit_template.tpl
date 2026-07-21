<tr class="tabla-field-row" valign="top" id="tr-row-{$idRow}" data-num-format="{$NUMBERING_FORMAT}">
    <td style="vertical-align: middle">
        <textarea name="projec_task[task_title][]" id="task_title-{$idRow}" placeholder="Nombre de la tarea"
            {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if}
            class="form-control daily-report-scroll tinymce-class edit-tinyMce">{$relatedTask['task_title']}</textarea>
    </td>
    <td style="vertical-align: middle">
        <textarea name="projec_task[task][]" id="task-{$idRow}" placeholder="Descripción de la tarea"
            {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if}
            class="form-control daily-report-scroll tinymce-class edit-tinyMce">{$relatedTask['task']}</textarea>
        {if $relatedTask['types'] neq 'Job'}
            <input type="hidden" name="projec_task[taskId][]" value="{$relatedTask['taskId']}">
        {/if}
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_ACTIVITY_TYPES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="types{$idRow}" name="projec_task[types][]"
                    {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} onchange="" class="form-control">
                    {foreach $AVAILABLE_ACTIVITY_TYPES as $type => $label}
                        <option value="{$type}" {if $relatedTask['types'] eq $type}selected{/if}>
                            {$label}</option>
                    {/foreach}
                    {if $relatedTask['types'] eq 'Job'}
                        <option value="Job" selected>Tarea global de trabajo</option>
                    {/if}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="start_date-{$idRow}" placeholder="Fecha de inicio"
                {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} name="projec_task[start_date][]"
                value="{$relatedTask['start_date']}" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="due_date-{$idRow}" placeholder="Fecha de cierre"
                {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} name="projec_task[due_date][]"
                value="{$relatedTask['due_date']}" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_EVENT_STATUSES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="status-{$idRow}" name="projec_task[status][]"
                    {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} onchange="" class="form-control">
                    {foreach $AVAILABLE_EVENT_STATUSES as $key => $status}
                        <option value="{$key}" {if $key eq $relatedTask['status']} selected {/if}>
                            {$status}
                        </option>
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
                <select id="assigned-{$idRow}" name="projec_task[assigned][]"
                    {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} onchange="" class="form-control">
                    {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                        <option value="{$systemUser->getId()}" {if $systemUser->getId() eq $relatedTask['assigned']} selected
                            {/if}>
                            {$systemUser->getFirstName()} {$systemUser->getLastName()}
                        </option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="hidden" id="supplier-{$idRow}" name="projec_task[supplier][]"
                value="{$relatedTask['supplierId']}" class="module-reference" />
            <input type="text" id="edit_supplier-{$idRow}_display" name="projec_task[supplier_display][]"
                value="{$relatedTask['supplierName']}" class="form-control input-readonly b-right" readonly="readonly"
                placeholder="">
            <div class="input-group-addon" data-current-module="orden_de_trabajo"
                data-display-field-id="edit_supplier-{$idRow}_display" data-field-id="supplier-{$idRow}"
                data-referenced-module="proveedores" data-title="{$TASK_EXECUTOR_LABEL}"
                {if $relatedTask['types'] neq 'Job'}onclick="RelatedModuleModalUtils.openModal (this);" {/if}
                style="padding: 6px 8px; width: 32px;">
                <i class="fa fa-plus-circle"></i>
            </div>
            <div class="input-group-addon"
                onClick="var fieldContainer = jQuery (this).closest ('.input-group'); fieldContainer.find ('#edit_supplier-{$idRow}_display').val (''); fieldContainer.find ('#supplier-{$idRow}').val (''); return false;"
                style="padding: 6px 8px; width: 32px;">
                <i class="fa fa-eraser"></i>
            </div>
        </div>
    </td>
    <td style="vertical-align: middle">
        {if $AVAILABLE_ESTIMATED_TIME_UNITS neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="estimated_time_unit-{$idRow}" name="projec_task[estimated_time_unit][]"
                    {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} class="form-control"
                    onchange="TaskWorkUtls.updateNumFields(this, '{$idTaskProject}')">
                    {foreach $AVAILABLE_ESTIMATED_TIME_UNITS as $unitKey => $unitLabel}
                        <option value="{$unitKey}"
                            {if $relatedTask['estimated_time_unit'] eq $unitKey}selected{elseif !$relatedTask['estimated_time_unit'] && $unitKey eq 'Hora'}selected{/if}>
                            {$unitLabel}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="vertical-align: middle">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="duration-{$idRow}" placeholder="Unidad de medida"
                {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} name="projec_task[duration][]"
                value="{$relatedTask['duration']}" class="form-control duration-time"
                onkeyup="TaskWorkUtls.updateNumFields(this, '{$idTaskProject}')">
        </div>
    </td>
    <td style="vertical-align: middle; text-align:right;">
        <div class="input-group" style="width: 100%;text-align:right;">
            <input type="text" id="estimated-cost-{$idRow}" placeholder="Costo estimado"
                {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if} name="projec_task[estimated_cost][]"
                value="{$relatedTask['estimated_cost']}" class="form-control estimated-cost-field"
                style="width: 100%;text-align:right;" onkeyup="TaskWorkUtls.updateNumFields(this, '{$idTaskProject}')">
        </div>
    </td>
    <td style="vertical-align: middle; text-align:center;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="progress_weighting_factor-{$idRow}" placeholder="%"
                {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if}
                name="projec_task[progress_weighting_factor][]"
                value="{if $relatedTask['progress_weighting_factor'] neq NULL}{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$relatedTask['progress_weighting_factor']|number_format:2:',':'.'}{else}{$relatedTask['progress_weighting_factor']|number_format:2:'.':','}{/if}{/if}"
                class="form-control" style="text-align:center;">
        </div>
    </td>
    <td class="text-center" style="vertical-align: middle">
        <button type="button" class="btn btn-primary btn-xs"
            onclick="TaskWorkUtls.moveRowUp (this, 'tr-row-{$idRow}')"><i class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
            onclick="TaskWorkUtls.moveRowDown (this, 'tr-row-{$idRow}')"><i class="fa fa-arrow-down"
                aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
            {if $relatedTask['types'] eq 'Job'}disabled="disabled" {/if}
            onclick="TaskWorkUtls.delRowToTable (this, 'tr-row-{$idRow}', '{$idTaskProject}');"><i
                class="fa fa-trash-o"></i>
        </button>
        <button type="button" class="btn btn-success btn-xs" rel="{$idRow}" data-toggle="modal"
            data-target="#precreated-task-{$idTaskDetailView}" data-source="{$idRow}"><i class="fa fa-list-alt"></i>
        </button>
    </td>
</tr>