<tr  class="tabla-field-row" valign="top" id="tr-row-__ID__">
    <td style="vertical-align: top" width="11%">
        {if $PROJECT_STAGES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="stage__ID__" name="projec_task[stage][]"
                        onchange=""
                        class="form-control">
                    {foreach $PROJECT_STAGES as $projectStage}
                        <option value="{$projectStage->id}">{$projectStage->stage}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">No se han definido las etpas del proyecto</span>
        {/if}
    </td>
    <td style="" width="23%">
        <textarea name="projec_task[task][]" id="task-__ID__" placeholder="descripción de la tarea"
                              class="form-control daily-report-scroll tinymce-class edit-tinyMce"></textarea>
        <input type="hidden" name="projec_task[taskId][]" value="">
    </td>
    <td style="vertical-align: top" width="11%;">
        {if $AVAILABLE_ACTIVITY_TYPES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="types__ID__" name="projec_task[types][]"
                        onchange=""
                        class="form-control">
                    {foreach $AVAILABLE_ACTIVITY_TYPES as $sctivityType}
                        <option value="{$sctivityType->type}">{$sctivityType->title}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: top" width="9%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="start_date-__ID__" placeholder="Fecha de inicio"
                   name="projec_task[start_date][]"
                   value="" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: top" width="9%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="due_date-__ID__" placeholder="Fecha de cierre"
                   name="projec_task[due_date][]"
                   value="" class="form-control datepickerDate">
        </div>
    </td>
    <td style="vertical-align: top" width="8%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="duration-__ID__" placeholder="Horas"
                   name="projec_task[duration][]"
                   value="" class="form-control"
                   onkeyup="TaskProjectUtls.updateNumFields(this, '')">
        </div>
    </td>
    <td style="vertical-align: top" width="11%">
        {if $AVAILABLE_SYSTEM_USERS neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="assigned-__ID__" name="projec_task[assigned][]"
                        onchange=""
                        class="form-control">
                    {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                        <option value="{$systemUser->getId()}"
                                {if $systemUser->getId() eq $CURRENT_USER_ID}selected{/if}>{$systemUser->getFirstName()} {$systemUser->getLastName()}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">&nbsp;</span>
        {/if}
    </td>
    <td style="vertical-align: top" width="8%">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="advance" placeholder="% de avance"
                   name="projec_task[advance][]"
                   value="" class="form-control"
                   onkeyup="TaskProjectUtls.updateNumFields(this, '')">
        </div>
    </td>
    <td class="text-center" style="vertical-align: top">
        <button type="button" class="btn btn-primary btn-xs" onclick="TaskProjectUtls.moveRowUp (this, 'tr-row-__ID__')"><i
                    class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs" onclick="TaskProjectUtls.moveRowDown (this, 'tr-row-__ID__')"><i
                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="TaskProjectUtls.delRowToTable (this, 'tr-row-__ID__', '{$idTaskProject}');"><i class="fa fa-trash-o"></i>
        </button>

    </td>
</tr>