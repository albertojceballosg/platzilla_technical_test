{strip}
    <tr id="tr-row-__ID__" data-row-id="__ID__" class="tabla-field-row delete-row-__ID__">
        <td class="col-lg-6 col-md-6 col-sm-6">
            <div class="input-group" style="width: 100%;">
                <input type="hidden" id="objetive_id-__ID___type" name="action_plan_id_type"
                       value="action_plan" class="small"/>
                <input type="hidden" id="objetive_id-__ID__"
                       data-row-ids="{$idKeyObjResult}@__ID__"
                       name="app_okr[objetive_id][]" value=""
                       onchange="ActionPlanUtls.relatedKR(this)"
                       class="for-filter module-reference"/>
                <input type="text" id="edit_objetive_id-__ID___display"
                       name="app_okr[objective_name][]" value=""
                       class="form-control input-readonly b-right process-step-code"
                       data-Tableid="{$idKeyObjResult}"
                       readonly="readonly" placeholder=""/>
                <div class="input-group-addon" data-current-module="process"
                     data-display-field-id="edit_objetive_id-__ID___display"
                     data-field-id="objetive_id-__ID__"
                     data-referenced-module="business_objective"
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
        <td class="col-lg-4 col-md-4 col-sm-4" colspan="2">
            <div id="input-objetive_average-__ID__" class="input-group" style="width: 100%;">
                <input type="text" id="objetive_average-__ID__" name="app_okr[objetive_average][]"
                       value="" class="form-control">
            </div>
        </td>
        <td class="col-lg-1 col-md-1 col-sm-1 text-center">
            <button type="button" class="btn btn-primary btn-xs"
                    onclick="ActionPlanUtls.moveRowUp (this, 'tr-row-__ID__','tr-table-row-__ID__')"><i class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
            <button type="button" class="btn btn-danger btn-xs"
                    onclick="ActionPlanUtls.moveRowDown (this, 'tr-row-__ID__', 'tr-table-row-__ID__')"><i class="fa fa-arrow-down" aria-hidden="true"></i></button>&nbsp;
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                    onclick="ActionPlanUtls.delRowToTable (this, 'delete-row-__ID__', '{$idKeyObjResult}');"><i class="fa fa-trash-o"></i></button>
        </td>
    </tr>
    <tr id="tr-table-row-__ID__" data-row-id="__ID__" class="tabla-field-row delete-row-__ID__">
        <td class="col-lg-11 col-md-11 col-sm-11" id="kr-data-__ID__" colspan="3"></td>
        <td class="col-lg-1 col-md-1 col-sm-1 text-center">&nbsp;--&nbsp;</td>
    </tr>
{/strip}