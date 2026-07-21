<tr id="tr-row-__ID__" data-row-id="__ID__" data-id-table="{$idResourceInitiative}" class="tabla-field-row" valign="top">
    <td width="18%"  style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="reported_task_condition-__ID__"
                    name="resource_initiatives[type_resource][]"
                    onchange="BusinessInitiativesUtils.setTypeResource (this, '__ID__')"
                    class="form-control">
                <option value="">Tipo de recurso</option>
                {foreach $TYPE_RESOURCE as $tabname => $tablabel}
                    <option value="{$tabname}">{$tablabel}</option>
                {/foreach}
            </select>
        </div>
    </td>
    <td width="25%"  style="vertical-align: top">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_resource___ID___">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <input type="hidden" id="resource_initiative-id-__ID__" name="resource_initiatives[crmid_resource][]" value="" class="for-filter module-reference">
                        <input type="text" id="edit_resource_initiative-id-__ID___display"
                               name="resource_initiatives[field_resource][]"
                               value=""
                               class="form-control input-readonly  b-right" readonly="readonly" placeholder="Seleccionar recurso">
                        <div id="resource-initiative-modal-__ID__" class="input-group-addon hide"
                             data-current-module="business_initiatives"
                             data-display-field-id="edit_resource_initiative-id-__ID___display"
                             data-field-id="resource_initiative-id-__ID__"
                             data-referenced-module=""
                             data-title=""
                             onclick="RelatedModuleModalUtils.openModal (this);">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                        <div class="input-group-addon" onclick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_resource_initiative-id-__ID___display').val (''); return false;">
                            <i class="fa fa-eraser"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </td>
    <td width="14%"  style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="contribution_factor-__ID__"
                   placeholder="0.00"
                   name="resource_initiatives[contribution_factor][]" value=""
                   class="form-control contribution-factor"
                   onkeyup="BusinessInitiativesUtils.updateNumFields(this, '__ID__', 100)">
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="resource_progress-__ID__"
                   placeholder="0.00"
                   name="resource_initiatives[resource_progress][]" value="" class="form-control"
                   onkeyup="BusinessInitiativesUtils.updateNumFields(this, '__ID__', 0)">
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="total_contribution-__ID__"
                   placeholder="0.00"
                   readonly
                   name="resource_initiatives[total_contribution][]" value=""
                   class="form-control total-contribution"
                   onkeyup="BusinessInitiativesUtils.updateNumFields(this, '__ID__', 0)">
        </div>
    </td>
    <td class="text-center" width="15%" style="vertical-align: top">
        <button type="button" class="btn btn-primary btn-xs"
                onclick="BusinessInitiativesUtils.moveRowUp (this, 'tr-row-__ID__')"><i class="fa fa-arrow-up"
                                                                                   aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
                onclick="BusinessInitiativesUtils.moveRowDown (this, 'tr-row-__ID__')"><i
                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="BusinessInitiativesUtils.delRowToTable (this, 'tr-row-__ID__', '{$idResourceInitiative}');"><i
                    class="fa fa-trash-o"></i></button>
    </td>
</tr>