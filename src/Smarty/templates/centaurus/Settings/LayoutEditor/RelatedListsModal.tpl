{strip}
    <style>

    </style>
    {if (isset ($MODULE))}
        {assign var='moduleName' value=$MODULE->getName ()}
        {assign var='moduleLabel' value=$MODULE->getLabel ()}
    {else}
        {assign var='moduleName' value=null}
    {/if}
    {math equation= rand() assign= "idRelatedList"}
    <script type="text/html" id="related-lists-modal-template">
        <div class="modal fade" id="related-lists-modal" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="post" action="index.php"
                          onsubmit="RelatedListsUtils.saveRelatedLists (this); return false;">
                        <input type="hidden" name="module" value="Settings"/>
                        <input type="hidden" name="action" value="SaveRelatedLists"/>
                        <input type="hidden" name="modulename" value="{$moduleName}"/>
                        <input type="hidden" name="Ajax" value="true"/>
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                ×
                            </button>
                            <h4 class="modal-title">Listas relacionadas</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xs-12" style="margin: 6px 0">
                                    <ul id="ce-nav-tab" class="nav nav">
                                        <li class="active"><a data-toggle="tab"
                                                              onclick="RelatedListsUtils.setActiveTab(this)"
                                                              class="btn btn-xs btn-primary" href="#rl-related-list"
                                                              role="button"><span
                                                        class="glyphicon glyphicon-align-right"></span>&nbsp;{$MOD.LBL_RELATED_MODULES}
                                            </a>
                                        </li>
                                        <li style="padding: 0 10px"><a data-toggle="tab"
                                                                       onclick="RelatedListsUtils.setActiveTab(this)"
                                                                       class="btn btn-xs" href="#rl-related-field-list"
                                                                       role="button"><span
                                                        class="glyphicon glyphicon-list"></span>&nbsp;{$MOD.LBL_RELATED_FIELD}
                                            </a>
                                        </li>
                                        <li><a class="btn btn-xs"  data-toggle="tab"
                                               onclick="RelatedListsUtils.setActiveTab(this)"
                                               href="#rl-related-field-import"><span
                                                        class="glyphicon glyphicon-import"></span>&nbsp;{$MOD.LBL_RELATED_FIELD_IMPORT}
                                            </a>
                                        </li>
                                    </ul>
                                    <hr style="width: 95%;text-align: center">
                                </div>
                                <div class="col-xs-12">
                                    {*  related list *}
                                    <div class="tab-content"> {* tab contenet*}
                                        <div id="rl-related-list" class="rl-tab tab-pane fade in active">
                                            <div class="table-responsive">
                                                <table class="table related-lists-container">
                                                    <thead>
                                                    <tr>
                                                        <th class="col-label">Etiqueta</th>
                                                        <th class="col-module-name">Módulo</th>
                                                        <th class="col-available-for">Disponible para</th>
                                                        <th class="col-actions"></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody class="related-lists">
                                                    {if (count ($RELATED_LISTS) > 0)}
                                                        {foreach $RELATED_LISTS as $index => $relatedList}
                                                            {include file='Settings/LayoutEditor/RelatedList.tpl' INDEX=$index RELATED_LIST=$relatedList}
                                                        {/foreach}
                                                    {/if}
                                                    </tbody>
                                                    <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-center">
                                                            <button type="button" class="btn btn-default btn-icon"
                                                                    onclick="RelatedListsUtils.addList (this);">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        {* Fields List*}
                                        <div id="rl-related-field-list" class="rl-tab tab-pane fade">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <select id="related-module-list-{$idRelatedList}" class="form-control " title="Módulos Asociados"
                                                            onchange="RelatedListsUtils.selectRelatedModule(this, {$idRelatedList}, '')">
                                                        <option value="">Seleccionar módulo</option>
                                                        {if (count ($RELATED_LISTS) > 0)}
                                                            {foreach $RELATED_LISTS as $index => $relatedList}
                                                                <option value="{$relatedList->getRelatedModuleName ()}" data-index="{$index}" >{$relatedList->getRelatedModuleLabel ()}</option>
                                                            {/foreach}
                                                        {/if}
                                                    </select>
                                                </div>
                                                <div class="col-sm-6"></div>
                                                <div id="{$idRelatedList}-field-list" class="col-sm-12">
                                                    {if (count ($RELATED_LISTS) > 0)}
                                                        {foreach $RELATED_LISTS as $index => $relatedList}
                                                            {include file='Settings/LayoutEditor/RelatedListFields.tpl' INDEX=$index RELATED_LIST=$relatedList}
                                                        {/foreach}
                                                    {/if}
                                                </div>
                                            </div>
                                        </div> {* /Fields List*}

                                        {* Fields import *}
                                        <div id="rl-related-field-import" class="rl-tab tab-pane fade">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <select id="related-module-import-{$idRelatedList}" class="form-control " title="Módulos Asociados"
                                                            onchange="RelatedListsUtils.selectRelatedModule(this, {$idRelatedList}, '{$moduleName}')">
                                                        <option value="">Seleccionar módulo</option>
                                                        {if (count ($RELATED_LISTS) > 0)}
                                                            {foreach $RELATED_LISTS as $index => $relatedList}
                                                                <option value="{$relatedList->getRelatedModuleName ()}" data-index="{$index}" >{$relatedList->getRelatedModuleLabel ()}</option>
                                                            {/foreach}
                                                        {/if}
                                                    </select>
                                                </div>
                                                <div id="{$idRelatedList}-field-import" class="col-sm-12">
                                                    {if (count ($RELATED_LISTS) > 0)}
                                                        {foreach $RELATED_LISTS as $index => $relatedList}
                                                            {include file='Settings/LayoutEditor/RelatedImportFields.tpl' INDEX=$index RELATED_LIST=$relatedList}
                                                        {/foreach}
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    </div> {* tab contenet*}

                                </div>
                            </div> {* Row *}

                        </div> {* Body*}
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div> {* modal-content *}
            </div>
        </div>
    </script>
    <script type="text/html" id="related-list-template">
        {include file='Settings/LayoutEditor/RelatedList.tpl'}
    </script>
    <script type="text/html" id="related-field-row-template">
        <tr class="related-list-field" data-index="1">
            <td class="col-label">
                <input type="text" name="relatedlists[__INDEX__][__MODULE__][field_label][]" value=""
                       class="form-control related-list-field-label" placeholder="Etiqueta"/>
            </td>
            <td class="col-field-name">
                <select id="__INDEX__-fields-__MODULE__-__ID__" name="relatedlists[__INDEX__][__MODULE__][field_name][]"
                        class="form-control related-list-field-name"
                        title="Módulo">
                </select>

            </td>
            <td class="col-actions-field text-right">
                <span class="btn-icon btn-up-dummy hidden"></span>
                <button type="button" class="btn btn-primary btn-icon btn-up hide"
                        onclick="RelatedListsUtils.moveListFieldUp (this, __INDEX__, '__MODULE__');" title="Subir">
                    <i class="fa fa-arrow-up"></i>
                </button>
                <span class="btn-icon btn-down-dummy hidden"></span>
                <button type="button" class="btn btn-primary btn-icon btn-down hide"
                        onclick="RelatedListsUtils.moveListFieldDown (this, __INDEX__, '__MODULE__');" title="Bajar">
                    <i class="fa fa-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-danger btn-icon hide"
                        onclick="RelatedListsUtils.deleteFieldList (this, __INDEX__, '__MODULE__')"
                        title="Eliminar">
                    <i class="fa fa-trash-o"></i>
                </button>
            </td>
        </tr>
    </script>
    <script type="text/html" id="related-field-import-row-template">
        <tr class="related-list-field" data-index="__INDEX__">
            <td class="col-field-name">
                <select id="__INDEX__-fields-import-__MODULE__-__ID__" name="relatedlists[__INDEX__][__MODULE__][field_import][]"
                        onchange="RelatedListsUtils.relatedFieldToImport(this, __ID__, '__MODULE__')"
                        class="form-control related-list-field-name"
                        title="Módulo">
                </select>
            </td>
            <td  style="text-align: center; width: 10%">
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
                <input id="__INDEX__-fields-type-import-{$moduleName}-__ID__" type="hidden" name="relatedlists[__INDEX__][__MODULE__][field_type][]" value="">
            </td>
            <td class="col-label">
                <div id="OTHER-__MODULE__-__ID__" class="">
                <select id="__INDEX__-fields-import-{$moduleName}-__ID__"
                        name="relatedlists[__INDEX__][__MODULE__][field_home][]"
                        class="form-control related-list-field-name"
                        disabled=""
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
                <div id="LIST-__MODULE__-__ID__" class="hide">
                    <select id="__INDEX__-fields-import-{$moduleName}-LIST-__ID__"  name="relatedlists[__INDEX__][__MODULE__][field_home][]"
                            disabled=""
                            class="form-control related-list-field-name"
                            title="Módulo">
                        <option value="">Cargando lista...</option>
                    </select>
                </div>
                <div id="DATE-__MODULE__-__ID__" class="hide">
                    <select id="__INDEX__-fields-import-{$moduleName}-DATE-__ID__"  name="relatedlists[__INDEX__][__MODULE__][field_home][]"
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
                <div id="CHECK-__MODULE__-__ID__" class="hide">
                    <select id="__INDEX__-fields-import-{$moduleName}-CHECK-__ID__"  name="relatedlists[__INDEX__][__MODULE__][field_home][]"
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
                <span class="btn-icon btn-up-dummy "></span>
                {*
                <button type="button" class="btn btn-primary btn-icon btn-up hide"
                        onclick="RelatedListsUtils.moveListFieldUp (this, __INDEX__, '__MODULE__-field-import');" title="Subir">
                    <i class="fa fa-arrow-up"></i>
                </button>
                *}
                <span class="btn-icon btn-down-dummy"></span>
                {*
                <button type="button" class="btn btn-primary btn-icon btn-down hide"
                        onclick="RelatedListsUtils.moveListFieldDown (this, __INDEX__, '__MODULE__-field-import');" title="Bajar">
                    <i class="fa fa-arrow-down"></i>
                </button>
                *}
                <button type="button" class="btn btn-danger btn-icon hide"
                        onclick="RelatedListsUtils.deleteFieldList (this, __INDEX__, '__MODULE__-field-import')"
                        title="Eliminar">
                    <i class="fa fa-trash-o"></i>
                </button>

            </td>
        </tr>
    </script>
{/strip}