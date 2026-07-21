<tr  class="tabla-field-row" valign="top" id="tr-row-{$idRow}">
    <td style="vertical-align: top" width="11%">
        {if $PROJECT_STAGES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="stage-{$idRow}" name="projec_task[stage][]"
                        onchange=""
                        class="form-control">
                    {foreach $PROJECT_STAGES as $projectStage}
                        <option value="{$projectStage->id}" {if $relatedTask['stage'] eq $projectStage->id}selected{/if}>{$projectStage->stage}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">No se han definido las etpas del proyecto</span>
        {/if}
    </td>
    <td style="" width="23%">
        <textarea name="projec_task[task][]" id="task-{$idRow}" placeholder="descripción de la tarea"
                              class="form-control daily-report-scroll tinymce-class edit-tinyMce">{$relatedTask['task']}</textarea>
        <input type="hidden" name="projec_task[taskId][]" value="{$relatedTask['taskId']}">
    </td>
    <td style="vertical-align: top" width="11%;">
        {if $AVAILABLE_ACTIVITY_TYPES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="types{$idRow}" name="projec_task[types][]"
                        onchange=""
                        class="form-control">
                    {foreach $AVAILABLE_ACTIVITY_TYPES as $sctivityType}
                        <option value="{$sctivityType->type}" {if $relatedTask['types'] eq $sctivityType->type}selected{/if}>{$sctivityType->title}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: top" width="9%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="start_date-{$idRow}" placeholder="Fecha de inicio"
                   name="projec_task[start_date][]"
                   value="{$relatedTask['start_date']}" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: top" width="9%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="due_date-{$idRow}" placeholder="Fecha de cierre"
                   name="projec_task[due_date][]"
                   value="{$relatedTask['due_date']}" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: top" width="8%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="duration-{$idRow}" placeholder="Horas"
                   name="projec_task[duration][]"
                   value="{$relatedTask['duration']}" class="form-control"
                   onkeyup="TaskProjectUtls.updateNumFields(this, '')">
        </div>
    </td>
    <td style="vertical-align: top" width="11%">
        {if $AVAILABLE_SYSTEM_USERS neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="assigned-{$idRow}" name="projec_task[assigned][]"
                        onchange=""
                        class="form-control">
                    {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                        <option value="{$systemUser->getId()}"
                                {if $systemUser->getId() eq $relatedTask['assigned']} selected {/if} >
                            {$systemUser->getFirstName()} {$systemUser->getLastName()}
                        </option>
                    {/foreach}
                </select>
        {else}
            <span style="">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: top" width="8%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="advance-{$idRow}" placeholder="% de avance"
                   name="projec_task[advance][]"
                   value="{$relatedTask['advance']}" class="form-control"
                   onkeyup="TaskProjectUtls.updateNumFields(this, '')">
        </div>
    </td>
    <td class="text-center" style="vertical-align: top">
        <button type="button" class="btn btn-primary btn-xs" onclick="TaskProjectUtls.moveRowUp (this, 'tr-row-{$idRow}')"><i
                    class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs" onclick="TaskProjectUtls.moveRowDown (this, 'tr-row-{$idRow}')"><i
                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="TaskProjectUtls.delRowToTable (this, 'tr-row-{$idRow}', '{$idTaskProject}');"><i class="fa fa-trash-o"></i>
        </button>

    </td>
</tr>