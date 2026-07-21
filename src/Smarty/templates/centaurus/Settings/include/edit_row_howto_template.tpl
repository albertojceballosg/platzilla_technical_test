<tr id="tr-row-{$rowId}">
    <td style="" width="33%">
        <select id="how-to-assign-module-{$rowId}"
                name="howto_assign[module][]"
                onchange="HowToUtils.selectedModule(this, '{$rowId}')"
                class="form-control">
            <option value="">Seleccionar: Modulo</option>
            {if ($AVAILABLE_MODULES neq NULL)}
                {foreach $AVAILABLE_MODULES as $moduleName}
                    <option value="{$moduleName['name']}"{if ($howToEntity->getTabName() == $moduleName['name'])} selected="selected"{/if}>{$moduleName['label']}
                        ({$moduleName['name']})
                    </option>
                {/foreach}
            {/if}
        </select>
    </td>
    <td style="" width="33%">
        <div class="form-group col-xs-12 field-container" id="td_howto-assign-record-{$rowId}"
             style="margin-bottom: 0!important;">
            <input type="hidden" id="howto-assign-record-{$rowId}-name" name="howto_assign[record_name][]"
                   value="" class="small">
            <div class="input-group" style="width: 100%;">
                <input type="hidden" id="howto-assign-record-{$rowId}" name="howto_assign[record][]" value="{$howToEntity->getCrmId()}"
                       class="for-filter module-reference">
                <input type="text" id="edit_howto-assign-record-{$rowId}_display"
                       name="howto_assign[record_display][]" value="{$howToEntity->getEntityTitle()}"
                       class="form-control input-readonly b-right" readonly="readonly"
                       placeholder="">
                <div id="div_howto-assign-record-{$rowId}" class="input-group-addon"
                     data-current-module="" data-display-field-id="edit_howto-assign-record-{$rowId}_display"
                     data-field-id="howto-assign-record-{$rowId}" data-referenced-module="{$howToEntity->getTabName()}"
                     data-title="{$howToEntity->getTabName()}" data-filter-field-names=""
                     data-filter-description=""
                     onclick="if (HowToUtils.verifyModule('{$rowId}')){ RelatedModuleModalUtils.openModal (this);}">
                    <i class="fa fa-plus-circle"></i>
                </div>
                <div class="input-group-addon"
                     onclick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_howto-assign-record-{$rowId}_display').val (''); fieldContainer.find ('#howto-assign-record-{$rowId}').val (''); return false;">
                    <i class="fa fa-eraser"></i>
                </div>
            </div>
        </div>
    </td>
    <td style="" width="22%">
        <div class="input-group" style="width: 100%;">
            <select id="how-to-assign-file-{$rowId}" name="howto_assign[file][]"
                    class="form-control">
                <option value="">Seleccionar: vista</option>
                {if ($HOW_TO_FILES neq NULL)}
                    {foreach $HOW_TO_FILES as $value => $label}
                        <option value="{$value}"{if ($howToEntity->getFile() == $value)} selected="selected"{/if}>{$label}</option>
                    {/foreach}
                {/if}
            </select>
        </div>
    </td>
    <td class="text-center" style="vertical-align: top" width="16%">
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="HowToUtils.delRowToTable (this, 'tr-row-{$rowId}');">
            <i class="fa fa-trash-o"></i></button>
    </td>
</tr>