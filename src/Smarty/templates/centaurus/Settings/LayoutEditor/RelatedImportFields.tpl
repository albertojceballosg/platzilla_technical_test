{strip}
    {if (isset ($RELATED_LIST))}
        {assign var='relatedListActions' value=$RELATED_LIST->getActions ()}
        {assign var='relatedListLabel' value=$RELATED_LIST->getLabel ()}
        {assign var='relatedListRelatedModuleName' value=$RELATED_LIST->getRelatedModuleName ()}
        {assign var='relatedListRelatedModuleLabel' value=$RELATED_LIST->getRelatedModuleLabel ()}
        {assign var='relatedListSequence' value=$RELATED_LIST->getSequence ()}
        {assign var='relatedListFields' value=$RELATED_LIST->getRelatedFields()}
        {if ($relatedListFields neq NULL) && ($relatedListFields->getFieldImport() neq NULL)}
            {assign var='relatedImportFields' value=$relatedListFields->getFieldImport()}
        {else}
            {assign var='relatedImportFields' value=null}
        {/if}
    {else}
        {assign var='relatedListActions' value=[]}
        {assign var='relatedListLabel' value=null}
        {if !isset($relatedListRelatedModuleName)}
            {assign var='relatedListRelatedModuleName' value=null}
        {/if}
        {assign var='relatedListSequence' value=null}
        {assign var='relatedListFields' value=null}
        {assign var='relatedImportFields' value=null}
    {/if}
    <div id="{$relatedListRelatedModuleName}-field-import" class="related-fields-import table-responsive {if !isset($FROM_AJAX)}hide{/if}">
        <table class="table related-lists-container">
            <thead>
            <tr>
                <th class="col-module-name" style="text-align: left; width: 30%">Campo destino ({$relatedListRelatedModuleLabel})</th>
                <th class="col-module-name" style="text-align: center; width: 10%"><i class="fa fa-arrow-right" aria-hidden="true"></th>
                <th class="col-label" style="text-align: left; width: 30%">Campo origen ({$moduleLabel})</th>
                <th class="col-actions" style="text-align: right; width: 30%">Acciones</th>
            </tr>
            </thead>
            <tbody id="{$INDEX}-{$relatedListRelatedModuleName}-field-import" class="related-lists-fields">
            {if $relatedImportFields neq NULL}
                {foreach  key=homeField item=importField from=$relatedImportFields name="importField"}
                    <tr class="related-list-field" data-index="{$INDEX}">
                        <td class="col-field-name">
                            <select id="{$INDEX}-fields-import-{$relatedListRelatedModuleName}-{$smarty.foreach.importField.index}"
                                    name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_import][]"
                                    onchange="RelatedListsUtils.relatedFieldToImport(this, {$smarty.foreach.importField.index}, '{$relatedListRelatedModuleName}')"
                                    class="form-control related-list-field-name"
                                    title="Módulo">
                                <option value="">Seleccionar campo</option>
                                {foreach   $AVAILABLE_RELATED_FIELDS[$relatedListRelatedModuleName] as $field}
                                    {if in_array($field->getUiType(), $N0_IMPORT_FIELD)}{continue}{/if}
                                    <option value="{$field->getName ()}"
                                            data-uitype="{$field->getUiType()}"
                                            {if $field->getName() eq $homeField}
                                                selected="selected"
                                            {/if}
                                            >{$field->getLabel()}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td style="width: 10%">
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            <input id="{$INDEX}-fields-type-import-{$moduleName}-{$smarty.foreach.importField.index}" type="hidden" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_type][]" value="{$importField[0]}">
                        </td>
                        <td class="col-label">
                            <div id="OTHER-{$relatedListRelatedModuleName}-{$smarty.foreach.importField.index}" class="{if ($importField[0] neq 'FIELD') && ($importField[0] neq 'GRID')} hide{/if}">
                                <select id="{$INDEX}-fields-import-{$moduleName}-{$smarty.foreach.importField.index}" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                        {if ($importField[0] neq 'FIELD') && ($importField[0] neq 'GRID')} disabled=""{/if}
                                        class="form-control related-list-field-name"
                                        title="Módulo">
                                    <option value="">Seleccionar campo</option>
                                    <option value="record_id"
                                            {if $importField[1] eq 'record_id'}
                                                selected="selected"
                                            {else}
                                                disabled=""
                                            {/if}
                                            data-uitype="10">{$moduleLabel}</option>
                                    {foreach   $AVAILABLE_RELATED_FIELDS[$moduleName] as $field}
                                        {if in_array($field->getUiType(), $N0_IMPORT_FIELD)}{continue}{/if}
                                        <option value="{$field->getName ()}"
                                                {if $field->getName() eq $importField[1]}
                                                    selected="selected"
                                                {else}
                                                    disabled=""
                                                {/if}
                                                data-uitype="{$field->getUiType()}">{$field->getLabel()}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div id="LIST-{$relatedListRelatedModuleName}-{$smarty.foreach.importField.index}" class="{if ($importField[0] neq 'LIST')} hide{/if}">
                                <select id="{$INDEX}-fields-import-{$moduleName}-LIST-{$smarty.foreach.importField.index}"  name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                        {if $importField[0] neq 'LIST'} disabled=""{/if}
                                        class="form-control related-list-field-name"
                                        title="Módulo">
                                    {if $importField[0] eq 'LIST'}
                                        {$AVAILABLE_RELATED_FIELDS[$homeField]}
                                    {else}
                                        <option value="">Cargando lista...</option>
                                    {/if}

                                </select>
                            </div>
                            <div id="DATE-{$relatedListRelatedModuleName}-{$smarty.foreach.importField.index}" class="{if $importField[0] neq 'DATE'} hide{/if}">
                                <select id="{$INDEX}-fields-import-{$moduleName}-DATE-{$smarty.foreach.importField.index}"  name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                        {if $importField[0] neq 'DATE'} disabled=""{/if}
                                        class="form-control related-list-field-name"
                                        title="Módulo">
                                    <option value="">Selecionar..</option>
                                    {foreach   $DATE_FIELD_IMPORT  as $key => $value}
                                        <option value="{$key}" {if $importField[1] eq $key}  selected="selected" {/if}>{$value}</option>
                                    {/foreach}
                                    {foreach   $AVAILABLE_RELATED_FIELDS[$moduleName] as $field}
                                        {if (in_array($field->getUiType(), $N0_IMPORT_FIELD)) || ($field->getUiType() neq 5)}{continue}{/if}
                                        <option value="{$field->getName ()}" {if $importField[1] eq $field->getName ()}  selected="selected" {/if} >{$moduleLabel}: {$field->getLabel()}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div id="CHECK-{$relatedListRelatedModuleName}-{$smarty.foreach.importField.index}" class="{if $importField[0] neq 'CHECK'} hide{/if}">
                                <select id="{$INDEX}-fields-import-{$moduleName}-CHECK-0"  name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                        {if $importField[0] neq 'CHECK'} disabled=""{/if}
                                        class="form-control related-list-field-name"
                                        title="Módulo">
                                    <option value="">Selecionar..</option>
                                    <option value="1" {if $importField[1] eq 1}  selected="selected" {/if}>Si</option>
                                    <option value="0" {if $importField[1] eq 0}  selected="selected" {/if}>No</option>
                                </select>
                            </div>
                        </td>
                        <td class="col-actions-field text-right"> <!-- {$smarty.foreach.importField.index} {count($relatedListFields->getFieldList())-1} -->
                            <span class="btn-icon btn-up-dummy "></span>
                            {*
                            <button type="button" class="btn btn-primary btn-icon btn-up {if $smarty.foreach.importField.index eq 0}hide{/if}"
                                    onclick="RelatedListsUtils.moveListFieldUp (this, {$INDEX}, '{$relatedListRelatedModuleName}');" title="Subir">
                                <i class="fa fa-arrow-up"></i>
                            </button>
                            *}
                            <span class="btn-icon btn-down-dummy "></span>
                            {*
                            <button type="button" class="btn btn-primary btn-icon btn-down {if $smarty.foreach.importField.index eq (count($relatedListFields->getFieldList())) - 1}hide{/if}"
                                    onclick="RelatedListsUtils.moveListFieldDown (this, {$INDEX}, '{$relatedListRelatedModuleName}');" title="Bajar">
                                <i class="fa fa-arrow-down"></i>
                            </button> *}
                            <button type="button" class="btn btn-danger btn-icon {if $smarty.foreach.importField.index eq 0}hide{/if}"
                                    onclick="RelatedListsUtils.deleteFieldList (this, {$INDEX}, '{$relatedListRelatedModuleName}')"
                                    title="Eliminar">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr class="related-list-field" data-index="{$INDEX}">
                    <td class="col-field-name">
                        <select id="{$INDEX}-fields-import-{$relatedListRelatedModuleName}-0" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_import][]"
                                class="form-control related-list-field-name"
                                onchange="RelatedListsUtils.relatedFieldToImport(this, 0, '{$relatedListRelatedModuleName}')"
                                title="Módulo">
                            <option value="">Seleccionar campo</option>
                            {foreach   $AVAILABLE_RELATED_FIELDS[$relatedListRelatedModuleName] as $field}
                                {if in_array($field->getUiType(), $N0_IMPORT_FIELD)}{continue}{/if}
                                <option value="{$field->getName ()}" data-uitype="{$field->getUiType()}">{$field->getLabel()}</option>
                            {/foreach}
                        </select>
                    </td>
                    <td  style="text-align: center; width: 10%">
                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        <input id="{$INDEX}-fields-type-import-{$moduleName}-0" type="hidden" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_type][]" value="">
                    </td>
                    <td class="col-label">
                        <div id="OTHER-{$relatedListRelatedModuleName}-0" class="">
                        <select id="{$INDEX}-fields-import-{$moduleName}-0" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                disabled=""
                                class="form-control related-list-field-name"
                                title="Módulo">
                            <option value="">Seleccionar campo</option>
                            <option value="record_id"
                                    data-uitype="10">{$moduleLabel}</option>
                            {foreach   $AVAILABLE_RELATED_FIELDS[$moduleName] as $field}
                                {if in_array($field->getUiType(), $N0_IMPORT_FIELD)}{continue}{/if}
                                <option value="{$field->getName ()}" data-uitype="{$field->getUiType()}" >{$field->getLabel()}</option>
                            {/foreach}
                        </select>
                        </div>
                        <div id="LIST-{$relatedListRelatedModuleName}-0" class="hide">
                            <select id="{$INDEX}-fields-import-{$moduleName}-LIST-0"  name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                    disabled=""
                                    class="form-control related-list-field-name"
                                    title="Módulo">
                                <option value="">Cargando lista...</option>
                            </select>
                        </div>
                        <div id="DATE-{$relatedListRelatedModuleName}-0" class="hide">
                            <select id="{$INDEX}-fields-import-{$moduleName}-DATE-0"  name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                    disabled=""
                                    class="form-control related-list-field-name"
                                    title="Módulo">
                                <option value="">Selecionar..</option>
                                {foreach   $DATE_FIELD_IMPORT  as $key => $value}
                                    <option value="{$key}">{$value}</option>
                                {/foreach}
                                {foreach   $AVAILABLE_RELATED_FIELDS[$moduleName] as $field}
                                    {if (in_array($field->getUiType(), $N0_IMPORT_FIELD)) || ($field->getUiType() neq 5)}{continue}{/if}
                                    <option value="{$field->getName ()}" >{$moduleLabel}: {$field->getLabel()}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div id="CHECK-{$relatedListRelatedModuleName}-0" class="hide">
                            <select id="{$INDEX}-fields-import-{$moduleName}-CHECK-0"  name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_home][]"
                                    disabled=""
                                    class="form-control related-list-field-name"
                                    title="Módulo">
                                <option value="">Selecionar..</option>
                                <option value="1" >Si</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </td>
                    <td class="col-actions-field text-right">

                        <span class="btn-icon btn-up-dummy"></span>
                        {*
                        <button type="button" class="btn btn-primary btn-icon btn-up hide"
                                onclick="RelatedListsUtils.moveListFieldUp (this, {$INDEX}, '{$relatedListRelatedModuleName}-field-import');" title="Subir">
                            <i class="fa fa-arrow-up"></i>
                        </button>
                        *}
                        <span class="btn-icon btn-down-dummy "></span>
                        {*
                        <button type="button" class="btn btn-primary btn-icon btn-down hide"
                                onclick="RelatedListsUtils.moveListFieldDown (this, {$INDEX}, '{$relatedListRelatedModuleName}-field-import');" title="Bajar">
                            <i class="fa fa-arrow-down"></i>
                        </button>
                          *}
                        <button type="button" class="btn btn-danger btn-icon hide"
                                onclick="RelatedListsUtils.deleteFieldList (this, {$INDEX}, '{$relatedListRelatedModuleName}-field-import')"
                                title="Eliminar">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </td>
                </tr>
            {/if}
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4" class="text-center">
                    <button type="button" class="btn btn-default btn-icon"
                            onclick="RelatedListsUtils.addFieldImport (this, {$INDEX}, '{$relatedListRelatedModuleName}', '{$moduleName}');">
                        <i class="fa fa-plus"></i>
                    </button>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
{/strip}