{strip}
    {assign var="index" value=0}
    {foreach $OKR as $okr}
    {math equation= rand() assign= "idKeyObjResultRow"}
    <tr id="tr-row-{$idKeyObjResultRow}" data-row-id="{$idKeyObjResultRow}" class="tabla-field-row delete-row-{$idKeyObjResultRow}">
        <td class="col-lg-6 col-md-6 col-sm-6">
            <div class="input-group" style="width: 100%;">
                <input type="hidden" id="objetive_id-{$idKeyObjResultRow}_type" name="action_plan_id_type"
                       value="action_plan" class="small"/>
                <input type="hidden" id="objetive_id-{$idKeyObjResultRow}"
                       data-row-ids="{$idKeyObjResult}@{$idKeyObjResultRow}"
                       name="app_okr[objetive_id][]" value="{$okr['kr_achieve_objective']['business_objectivetfid'][0]}"
                       onchange="ActionPlanUtls.relatedKR(this)"
                       class="for-filter module-reference"/>
                <input type="text" id="edit_objetive_id-{$idKeyObjResultRow}_display"
                       name="app_okr[objective_name][]" value="{$okr['objective_name']}"
                       class="form-control input-readonly b-right process-step-code"
                       data-Tableid="{$idKeyObjResult}"
                       readonly="readonly" placeholder=""/>
                <div class="input-group-addon" data-current-module="process"
                     data-display-field-id="edit_objetive_id-{$idKeyObjResultRow}_display"
                     data-field-id="objetive_id-{$idKeyObjResultRow}"
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
            <div id="input-objetive_average-{$idKeyObjResultRow}" class="input-group" style="width: 100%;">
                <input type="text" id="objetive_average-{$idKeyObjResultRow}" name="app_okr[objetive_average][]"
                       value="{$okr['goal_progress']}" class="form-control">
            </div>
        </td>
        <td class="col-lg-1 col-md-1 col-sm-1 text-center">
            <button type="button" class="btn btn-primary btn-xs"
                    onclick="ActionPlanUtls.moveRowUp (this, 'tr-row-{$idKeyObjResultRow}', 'tr-table-row-{$idKeyObjResultRow}')"><i class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
            <button type="button" class="btn btn-danger btn-xs"
                    onclick="ActionPlanUtls.moveRowDown (this, 'tr-row-{$idKeyObjResultRow}', 'tr-table-row-{$idKeyObjResultRow}')"><i class="fa fa-arrow-down" aria-hidden="true"></i></button>&nbsp;
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                    onclick="ActionPlanUtls.delRowToTable (this, 'delete-row-{$idKeyObjResultRow}', '{$idKeyObjResult}');"><i class="fa fa-trash-o"></i></button>
        </td>
    </tr>
    <tr id="tr-table-row-{$idKeyObjResultRow}" data-row-id="{$idKeyObjResultRow}" class="tabla-field-row delete-row-{$idKeyObjResultRow}">
        <td class="col-lg-11 col-md-11 col-sm-11" id="kr-data-{$idKeyObjResultRow}" colspan="3">
            {assign var='TOTAL_ROW' value=$okr['total_objetives']}
            {assign var='KR' value=$okr['kr_achieve_objective']}
            {assign var='ID_TABLE' value=$idOKRPlan}
            {include file="modules/action_plan/KeyResultEditView.tpl"}
        </td>
        <td class="col-lg-1 col-md-1 col-sm-1 text-center">&nbsp;--&nbsp;</td>
    </tr>
        {assign var="index" value=$index+1}
    {/foreach}
{/strip}