{math equation= rand() assign= "idLinkages"}
{strip}
    <div class="panel-group" id="group-linkages-list-{$idLinkages}">
        {foreach key=key item=list from=$AVAILABLE_LIST}
        <div class="panel panel-default" style="margin-top: 1em">
            <div class="panel-heading" style="margin-bottom: 0 !important;">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#group-linkages-list-{$iidLinkages}"
                       href="#{$list['name']}-{$idLinkages}">Campo lista:&nbsp;{$list['label']}</a>
                </h4>
            </div>
            <div id="{$list['name']}-{$idLinkages}" class="panel-collapse collapse in">
                <div class="panel-body" style="padding-top: 0!important;">

                    <table class="table">
                        <thead>
                        <tr>
                            <th class="field-name-cell" style="width: 30%; text-align: center">Valor de lista</th>
                            <th class="field-label-cell" style="width: 35%;text-align: center">Vincular con</th>
                            <th class="field-type-cell" style="width: 35%">Columna destino</th>
                        </tr>
                        </thead>
                        <tbody id="tbody-{$list['name']}-{$key}">
                        {if $ACTIONS_FILES neq NULL}
                            {assign var="values" value=$ACTIONS_FILES[$list['name']]['option']}
                            {assign var="picklist" value=$ACTIONS_FILES[$list['name']]['picklist']}
                            {assign var="columnData" value=$ACTIONS_FILES[$list['name']]['column']}
                            {assign var="mode" value="edit"}
                        {else}
                            {assign var="values" value=$list['values']}
                            {assign var="mode" value="create"}
                            {assign var="picklist" value=null}
                            {assign var="columnData" value=null}
                        {/if}
                        {foreach $values as $key => $value}
                            {if $value eq NULL}{continue}{/if}
                        <tr class="field" data-id="{$idFieldImport}">
                            <td class="text-center">
                               <input type="text" class="form-control" name="linkages[list][{$list['name']}][option][]" readonly value="{$value}"/>
                            </td>
                            <td>
                                <select id="module-field-{$idFieldImport}" name="linkages[list][{$list['name']}][picklist][]"
                                        class="form-control linkage-related-module"
                                        onchange="TableFieldUtils.selectedFieldImport(this)"
                                        title="Campo para importar">
                                    <option value="" data-type="0">Seleccionar campo</option>
                                    <option value="DO_NOT_LINK" data-type="0" {if ($picklist neq NULL && $picklist[$key] eq DO_NOT_LINK) || ($MODULE_WITH_LIST eq NULL)}selected{/if}>No vincular</option>
                                    {if $MODULE_WITH_LIST neq NULL}
                                    {foreach from=$MODULE_WITH_LIST key=k item=v}
                                        <option value="{$v.fieldname}" {if $picklist neq NULL && $v.fieldname eq $picklist[$key]}selected{/if}>{$v.tablabel}: {$v.fieldlabel}</option>
                                    {/foreach}
                                    {/if}
                                </select>
                            </td>
                            <td>
                                <select id="tabble-column-{$idFieldImport}" name="linkages[list][{$list['name']}][column][]"
                                        class="form-control linkage-related-module"
                                        title="Columna destino">
                                    <option value="" data-type="0">Seleccionar columna</option>
                                    <option value="DO_NOT_LINK" data-type="0" {if ($columnData neq NULL && $columnData[$key] eq DO_NOT_LINK) || ($AVAILABLE_COLUMNS eq NULL)}selected{/if}>No vincular</option>
                                    {if $AVAILABLE_COLUMNS neq NULL}
                                    {foreach $AVAILABLE_COLUMNS as $column}
                                        <option value="{$column.name}" data-type="{$column.type}" {if $columnData neq NULL && $column.name eq $columnData[$key]}selected{/if}>{$column.label}</option>
                                    {/foreach}
                                    {/if}
                                </select>
                            </td>

                        </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-center">
                                <input type="hidden" name="linkages[list][name][{$key}]" value="{$list['name']}"/>
                                <input type="hidden" name="linkages[list][label][{$key}]" value="{$list['label']}"/>
                                <input type="hidden" name="linkages[list][stringvalues][{$key}]" value="{$list['string']}"/>
                            </td>
                        </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
            {/foreach}
        </div>
    </div>
{/strip}