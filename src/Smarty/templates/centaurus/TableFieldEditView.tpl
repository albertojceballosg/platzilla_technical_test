{math equation= rand() assign= "idFieldTable"}
{assign var="tableFields" value=$TABLE_FIELDS[$fldid]}
{if $TABLE_FIELD_DATA neq NULL}
{assign var="tableFieldsData" value=$TABLE_FIELD_DATA[$fldid]}
{assign var="tableFieldColumn" value=array_keys($tableFieldsData)}
{assign var= "totalRows" value=$tableFieldsData[$tableFieldColumn[0]]|count}
{else}
{assign var="tableFieldsData" value=null}
{assign var="tableFieldColumn" value=null}
{assign var= "totalRows" value=0}
{/if}
{assign var="totalColumn" value=1}
{assign var="hasSummary" value=null}
{assign var="hasOperation" value=null}
<div id="dv-{$fldname}" class="row field-container" data-field-id="{$idFieldTable}">
    <div class="col-md-12" id="td_{$fldname}" data-field-type="TABLE-FIELD">
        {*$tableFieldColumn|var_dump*}
        {*$totalRows|var_dump*}
        <div class="main-box-body clearfix">
            <div class="table-responsive">
                <table id="{$fldname}-table" class="table table-bordered tablegridvalidate">
                    <thead>
                    <tr valign="top" >
                        <!-- {$tableFields|count} -->
                        {foreach $tableFields as $tableField}
                            {if in_array($tableField->getUiType(), array(2203, 2207))}
                                {if $tableField->getFieldName() eq 'summaryRow'}
                                    {assign var="hasSummary" value='summaryRow'}
                                    {assign var="summaryFields" value=$tableField->getActionArray()}
                                    {elseif $tableField->getFieldName() eq 'operationRow'}
                                    {assign var="hasOperation" value='summaryRow'}
                                    {assign var="operationAction" value=$tableField->getActionArray()}
                                {/if}
                                {continue}
                            {/if}
                            {assign var="totalColumn" value=($totalColumn + 1)}
                            {assign var="stylesCell" value=$tableField->getAttributesArray()}
                            <td width="{($stylesCell['width'] * 90)/100}%" style="{$stylesCell['style']}">
                                <span style="{*$stylesCell['style']*}">{$tableField->getFieldLabel()}</span>
                                {*$tableField->getAttributesArray()|var_dump*}
                            </td>
                        {/foreach}
                        <td width="10%" class="text-center">Acciones</td>
                    </tr>
                    </thead>
                    <tbody id="tbody-{$fldname}-{$idFieldTable}"  rowtotal="0" data-num-format="{$NUMBERING_FORMAT}">
                    {if $tableFieldsData neq NULL}
                        {section name=foo start=0 loop=$totalRows}
                            {include file='Settings/GridManager/TableFieldActions/TableFieldEditViewRow.tpl' ID_TABLE_FIELD=$idFieldTable TABLE_FIELDS=$tableFields FIELD_NAME=$fldname indexRow=$smarty.section.foo.index }
                        {/section}
                    {else}
                        <tr>
                            <td colspan="{$totalColumn}" class="text-center">&nbsp;
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                    <tfoot id="tfoot-{$idFieldTable}"
                           data-field-name ="{$fldname}"
                           data-realted-summary-field = "{if $MODULE eq 'action_plan' && $fldname eq 'plan_initiatives'}overall_progress{/if}"
                           data-summary-row='{if $hasSummary neq NULL}{json_encode($summaryFields,JSON_FORCE_OBJECT)}{/if}'
                           data-operation-row='{if $hasOperation neq NULL}{json_encode($operationAction,JSON_FORCE_OBJECT)}{/if}'>
                    {if $hasSummary neq NULL}
                        {if $tableFieldsData neq NULL}
                            {assign var="summaryRowData" value=$tableFieldsData['summaryRow'][0]}
                        {else}
                            {assign var="summaryRowData" value=null}
                        {/if}
                        {assign var="setTitle" value='<p style="text-align: center">TOTALES</p>'}
                        <tr valign="top" id="summary-row-{$idFieldTable}">
                            {foreach $tableFields as $Fieldkey => $tableField}
                                {if in_array($tableField->getUiType(), array(2203, 2207))}
                                    {continue}
                                {elseif !in_array ($tableField->getFieldName(), $summaryFields['filename'])}
                                    {if $setTitle neq NULL}
                                        <td>
                                            {$setTitle}
                                            {assign var="setTitle" value=null}
                                        </td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}

                                {else}
                                    {foreach $summaryFields['filename'] as $key => $field}
                                        {if $field eq $tableField->getFieldName()}
                                            <td id="td-{$tableField->getFieldName()}-{$idFieldTable}">
                                                <input type="text" id="{$tableField->getFieldName()}-{$idFieldTable}" name="{$fldname}[summaryRow][{$tableField->getFieldName()}]" rel="{$summaryFields['operation'][$key]}"
                                                       value="{if $summaryRowData neq NULL}{$summaryRowData[$tableField->getFieldName()]}{else}0.00{/if}"
                                                       class="form-control" readonly />
                                            </td>
                                        {/if}
                                    {/foreach}
                                {/if}
                            {/foreach}
                            <td class="text-center">&nbsp;</td>
                        </tr>
                    {/if}
                    <tr>
                        <td colspan="{$totalColumn}" class="text-center">
                            <span class="hide"><i class="fa fa-spinner fa-spin fa-fw fa-2x"></i></span>
                            <button type="button" data-id-linkage="{$idFieldTable}" class="btn btn-primary"
                                    onclick="TableFieldUtils.addRowToTable (this, 'tbody-{$fldname}-{$idFieldTable}', '{$MODULE}');">
                                <i class="fa fa-plus"></i></button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>