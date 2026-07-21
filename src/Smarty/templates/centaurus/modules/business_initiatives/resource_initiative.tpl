{math equation= rand() assign= "idRowResourceInitiative"}
{assign var="moduleLabel" value=null}
<tr id="tr-row-{$idRowResourceInitiative}" data-row-id="{$idRowResourceInitiative}" data-id-table="{$idResourceInitiative}"
    class="tabla-field-row" valign="top">
    <td width="18%"  style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="reported_task_condition-{$idRowResourceInitiative}"
                    name="resource_initiatives[type_resource][]"
                    onchange="BusinessInitiativesUtils.setTypeResource (this, '{$idRowResourceInitiative}')"
                    class="form-control">
                <option value="">Tipo de recurso</option>
                {foreach $TYPE_RESOURCE as $tabname => $tablabel}
                    <option value="{$tabname}"
                            {if $resource->getTypeResource() eq $tabname}
                            {assign var="moduleLabel" value=$tablabel}
                    selected{/if}>
                        {$tablabel}</option>
                {/foreach}
            </select>
        </div>
    </td>
    <td width="25%"  style="vertical-align: top">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_resource_{$idRowResourceInitiative}_">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <input type="hidden" id="resource_initiative-id-{$idRowResourceInitiative}" name="resource_initiatives[crmid_resource][]"
                               value="{$resource->getIdResource()}"
                               class="for-filter module-reference">
                        <input type="text" id="edit_resource_initiative-id-{$idRowResourceInitiative}_display"
                               name="resource_initiatives[field_resource][]"
                               value="{$resource->getResourceDescription()}"
                               class="form-control input-readonly  b-right" readonly="readonly" placeholder="Seleccionar recurso">
                        <div id="resource-initiative-modal-{$idRowResourceInitiative}" class="input-group-addon"
                             data-current-module="business_initiatives"
                             data-display-field-id="edit_resource_initiative-id-{$idRowResourceInitiative}_display"
                             data-field-id="resource_initiative-id-{$idRowResourceInitiative}"
                             data-referenced-module="{$resource->getTypeResource()}"
                             data-title="Seleccionar un(a) {$moduleLabel}"
                             onclick="RelatedModuleModalUtils.openModal (this);">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                        <div class="input-group-addon" onclick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_resource_initiative-id-{$idRowResourceInitiative}_display').val (''); return false;">
                            <i class="fa fa-eraser"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </td>
    <td width="14%"  style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="contribution_factor-{$idRowResourceInitiative}"
                   placeholder="0.00"
                   name="resource_initiatives[contribution_factor][]"
                   value="{$resource->getContributionFactor()}"
                   class="form-control contribution-factor"
                   onkeyup="BusinessInitiativesUtils.updateNumFields(this, '{$idRowResourceInitiative}', 100)">
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="resource_progress-{$idRowResourceInitiative}"
                   placeholder="0.00"
                   name="resource_initiatives[resource_progress][]"
                   value="{$resource->getResourceProgress()}"
                   class="form-control"
                   onkeyup="BusinessInitiativesUtils.updateNumFields(this, '{$idRowResourceInitiative}', 0)">
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="total_contribution-{$idRowResourceInitiative}"
                   placeholder="0.00"
                   readonly
                   name="resource_initiatives[total_contribution][]"
                   value="{$resource->getTotalContribution()}"
                   class="form-control total-contribution"
                   onkeyup="BusinessInitiativesUtils.updateNumFields(this, '{$idRowResourceInitiative}', 0)">
        </div>
    </td>
    <td class="text-center" width="15%" style="vertical-align: top">
        <button type="button" class="btn btn-primary btn-xs"
                onclick="BusinessInitiativesUtils.moveRowUp (this, 'tr-row-{$idRowResourceInitiative}')"><i class="fa fa-arrow-up"
                                                                                   aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
                onclick="BusinessInitiativesUtils.moveRowDown (this, 'tr-row-{$idRowResourceInitiative}')"><i
                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="BusinessInitiativesUtils.delRowToTable (this, 'tr-row-{$idRowResourceInitiative}', '{$idDailyReport}');"><i
                    class="fa fa-trash-o"></i></button>
    </td>
</tr>