{strip}
    <tr id="tr-row-__ID__" data-row-id="__ID__" class="tabla-field-row">
        <td style="{$colStyle1}">
            <div class="input-group" style="width: 100%;">
                <input type="hidden" id="step_id-__ID___type" name="step_id_type"
                       value="process_steps" class="small"/>
                <input type="hidden" id="step_id-__ID__"
                       data-row-ids="{$idProcessSteps}@__ID__"
                       name="app_process[step_id][]" value=""
                       onchange="ProcessStepsUtls.relatedModule(this, '', 'process')"
                       class="for-filter module-reference"/>
                <input type="text" id="edit_step_id-__ID___display"
                       name="app_process[step_code][]" value=""
                       class="form-control input-readonly b-right process-step-code"
                       data-Tableid="{$idProcessSteps}"
                       readonly="readonly" placeholder=""/>
                <div class="input-group-addon" data-current-module="process"
                     data-display-field-id="edit_step_id-__ID___display"
                     data-field-id="step_id-__ID__"
                     data-referenced-module="process_steps"
                     data-title="Código"
                     onclick="RelatedModuleModalUtils.openModal (this);">
                                <i class="fa fa-plus-circle"></i>
                </div>
                <div class="input-group-addon"
                     onClick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_{$actionField['fieldname']}_display').val (''); fieldContainer.find ('#{$actionField['fieldname']}').val (''); return false;">
                    <i class="fa fa-eraser"></i>
                </div>
            </div>
        </td>
        <td style="{$colStyle2}">
            <div id="input-step_name-__ID__" class="input-group" style="width: 100%;">
                <input type="text" id="step_name-__ID__" name="app_process[step_name][]"
                       readonly="readonly"
                       value="" class="form-control">
            </div>
        </td>
        <td style="{$colStyle3}">
            <div id="input-step_responsible_role-__ID__" class="input-group" style="width: 100%;">
                <input type="text" id="step_responsible_role-__ID__" name="app_process[step_responsible_role][]"
                       value="" class="form-control">
            </div>
        </td>
        <td style="{$colStyle4}">
            <div id="input-related_module-__ID__" class="input-group" style="width: 100%;">
                <input type="text" id="related_module-__ID__" name="app_process[related_module][]"
                       readonly="readonly"
                       value=""
                       class="form-control">
            </div>
        </td>
        <td style="{$colStyle5}">
            <div id="input-action_on-__ID__" class="input-group" style="width: 100%;">
                <select id="action_on-__ID__"
                	name="app_process[action_on][step][]"
                	class="form-control action-on-step">
                	<option value="">Paso del proceso</option>
                </select>
                <input type="hidden" id="step_type-__ID__"
                       name="app_process[step_type][]"
                       value="" class=""/>
            </div>
        </td>
        <td style="{$colStyle6}">
            <div id="input-step_state-__ID__" class="input-group" style="width: 100%;">
                <input type="text" id="step_type_view-__ID__"
                       readonly="readonly"
                       name=""
                       value=""
                       class="form-control">
            </div>
            <div id="input-action_task_on-__ID__" class="input-group hidden" style="width: 100%;margin-top: 2px">
                <select id="action_task_on-__ID__"
                        name="app_process[action_on][task][]"
                        class="form-control action-on-step">
                    <option value="">Paso del proceso</option>
                </select>
            </div>
        </td>
        <td style="{$colStyle7}">
            <div id="input-step_state-__ID__" class="input-group" style="width: 100%;">
                <input type="text" id="step_state-__ID__" name="app_process[step_state][]"
                       readonly="readonly"
                       value=""
                       class="form-control">
            </div>
        </td>
        <td style="{$colStyle8}">
            <button type="button" class="btn btn-primary btn-xs"
                    onclick="ProcessStepsUtls.moveRowUp (this, 'tr-row-__ID__')"><i class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
            <button type="button" class="btn btn-danger btn-xs"
                    onclick="ProcessStepsUtls.moveRowDown (this, 'tr-row-__ID__')"><i class="fa fa-arrow-down" aria-hidden="true"></i></button>&nbsp;
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                    onclick="ProcessStepsUtls.delRowToTable (this, 'tr-row-__ID__', '{$idProcessSteps}');"><i class="fa fa-trash-o"></i></button>
        </td>
    </tr>
{/strip}