{strip}
    {if (isset ($RELATED_LIST))}
        {assign var='relatedListActions' value=$relatedList->getActions ()}
        {assign var='relatedListLabel' value=$relatedList->getLabel ()}
        {assign var='relatedListRelatedModuleName' value=$relatedList->getRelatedModuleName ()}
        {assign var='relatedListRelatedModuleLabel' value=$relatedList->getRelatedModuleLabel ()}
        {assign var='relatedListSequence' value=$relatedList->getSequence ()}
        {assign var='relatedListFields' value=$relatedList->getRelatedFields()}
        {if ($relatedListFields neq NULL) && ($relatedListFields->getFieldList() neq NULL)}
            {assign var='fieldToList' value=$relatedListFields->getFieldList()}
        {else}
            {assign var='fieldToList' value=null}
        {/if}
    {else}
        {assign var='relatedListActions' value=[]}
        {assign var='relatedListLabel' value=null}
        {if !isset($relatedListRelatedModuleName)}
            {assign var='relatedListRelatedModuleName' value=null}
        {/if}
        {assign var='relatedListSequence' value=null}
        {assign var='relatedListFields' value=null}
        {assign var='fieldToList' value=null}
    {/if}
    <div id="{$relatedListRelatedModuleName}" class="related-fields table-responsive {if !isset($FROM_AJAX)}hide{/if}">
        <table class="table related-lists-container">
            <thead>
            <tr>
                <th class="col-label">Etiqueta</th>
                <th class="col-module-name">Campo de {$relatedListRelatedModuleLabel}</th>
                <th class="col-actions" style="text-align: right">Acciones</th>
            </tr>
            </thead>
            <tbody id="{$INDEX}-{$relatedListRelatedModuleName}" class="related-lists-fields">
            {if $fieldToList neq NULL}
                {foreach  key=label item=relatedField from=$fieldToList name="listField"}
                    <tr class="related-list-field" data-index="{$INDEX}">
                        <td class="col-label">
                            <input type="text"name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_label][]" value="{$label}"
                                   class="form-control related-list-field-label" placeholder="Etiqueta"/>
                        </td>
                        <td class="col-field-name">
                            <select id="{$INDEX}-fields-{$relatedListRelatedModuleName}-{$smarty.foreach.listField.index}" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_name][]"
                                    class="form-control related-list-field-name"
                                    title="Módulo">
                                <option value="">Seleccionar campo</option>
                                {foreach   $AVAILABLE_RELATED_FIELDS[$relatedListRelatedModuleName] as $field}
                                    <option value="{$field->getName ()}"
                                            {if $field->getName() eq $relatedField[1]}
                                                selected="selected"
                                            {/if}
                                            >{$field->getLabel()}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td class="col-actions-field text-right"> <!-- {$smarty.foreach.listField.index} {count($relatedListFields->getFieldList())-1} -->
                            <span class="btn-icon btn-up-dummy hidden"></span>
                            <button type="button" class="btn btn-primary btn-icon btn-up {if $smarty.foreach.listField.index eq 0}hide{/if}"
                                    onclick="RelatedListsUtils.moveListFieldUp (this, {$INDEX}, '{$relatedListRelatedModuleName}');" title="Subir">
                                <i class="fa fa-arrow-up"></i>
                            </button>
                            <span class="btn-icon btn-down-dummy hidden"></span>
                            <button type="button" class="btn btn-primary btn-icon btn-down {if $smarty.foreach.listField.index eq (count($relatedListFields->getFieldList())) - 1}hide{/if}"
                                    onclick="RelatedListsUtils.moveListFieldDown (this, {$INDEX}, '{$relatedListRelatedModuleName}');" title="Bajar">
                                <i class="fa fa-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-icon {if $smarty.foreach.listField.index eq 0}hide{/if}"
                                    onclick="RelatedListsUtils.deleteFieldList (this, {$INDEX}, '{$relatedListRelatedModuleName}')"
                                    title="Eliminar">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr class="related-list-field" data-index="{$INDEX}">
                    <td class="col-label">
                        <input type="text" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_label][]" value=""
                               class="form-control related-list-field-label" placeholder="Etiqueta"/>
                    </td>
                    <td class="col-field-name">
                        <select id="{$INDEX}-fields-{$relatedListRelatedModuleName}-0" name="relatedlists[{$INDEX}][{$relatedListRelatedModuleName}][field_name][]"
                                class="form-control related-list-field-name"
                                title="Módulo">
                            <option value="">Seleccionar campo</option>
                            {foreach   $AVAILABLE_RELATED_FIELDS[$relatedListRelatedModuleName] as $field}
                                <option value="{$field->getName ()}">{$field->getLabel()}</option>
                            {/foreach}
                        </select>

                    </td>
                    <td class="col-actions-field text-right">
                        <span class="btn-icon btn-up-dummy hidden"></span>
                        <button type="button" class="btn btn-primary btn-icon btn-up hide"
                                onclick="RelatedListsUtils.moveListFieldUp (this, {$INDEX}, '{$relatedListRelatedModuleName}');" title="Subir">
                            <i class="fa fa-arrow-up"></i>
                        </button>
                        <span class="btn-icon btn-down-dummy hidden"></span>
                        <button type="button" class="btn btn-primary btn-icon btn-down hide"
                                onclick="RelatedListsUtils.moveListFieldDown (this, {$INDEX}, '{$relatedListRelatedModuleName}');" title="Bajar">
                            <i class="fa fa-arrow-down"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-icon hide"
                                onclick="RelatedListsUtils.deleteFieldList (this, {$INDEX}, '{$relatedListRelatedModuleName}')"
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
                            onclick="RelatedListsUtils.addFieldList (this, {$INDEX}, '{$relatedListRelatedModuleName}');">
                        <i class="fa fa-plus"></i>
                    </button>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
{/strip}