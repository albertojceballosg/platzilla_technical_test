{math equation= rand() assign= "idFieldTable"}
{assign var="tableFields" value=$TABLE_FIELDS[$keyfldid]}
{if $keyoptions neq NULL}
{assign var="tableFieldsData" value=$keyoptions[$keyfldid]}
{assign var="tableFieldColumn" value=array_keys($tableFieldsData)}
    {if in_array('globallists', $tableFieldColumn)}
        {assign var= "totalRows" value=$tableFieldsData[$tableFieldColumn[1]]|count}
    {else}
        {assign var= "totalRows" value=$tableFieldsData[$tableFieldColumn[0]]|count}
    {/if}
{else}
{assign var="tableFieldsData" value=null}
{assign var="tableFieldColumn" value=null}
{assign var= "totalRows" value=0}
{/if}
{assign var="totalColumn" value=0}
{assign var="hasSummary" value=null}
{assign var="hasOperation" value=null}
<div id="dv-{$keyfldname}" class="row field-container" data-field-id="{$idFieldTable}">
    <div class="col-md-12">
        <div class="main-box-body clearfix" style="padding-bottom: 0!important;">
            <div class="table-responsive">
                <table id="{$keyfldname}-table" class="table table-bordered tablegridvalidate">
                    <thead>
                    <tr valign="top" >
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
                            <td class="text-center" width="{($stylesCell['width'] * 90)/100}%" style="{$stylesCell['style']}">
                                <span style="{* $stylesCell['style']*}">{$tableField->getFieldLabel()}</span>
                            </td>
                        {/foreach}
                    </tr>
                    </thead>
                    <tbody id="tbody-{$keyfldname}-{$idFieldTable}"  rowtotal="0">
                    {if $tableFieldsData neq NULL}
                        {section name=foo start=0 loop=$totalRows}
                            {include file='Settings/GridManager/TableFieldActions/TableFieldDetailViewRow.tpl' ID_TABLE_FIELD=$idFieldTable TABLE_FIELDS=$tableFields FIELD_NAME=$keyfldname indexRow=$smarty.section.foo.index}
                        {/section}
                    {else}
                        <tr>
                            <td colspan="{$totalColumn}" class="text-center">&nbsp;
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                    <tfoot id="tfoot-{$idFieldTable}">
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
                                        </td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}

                                {else}
                                    {foreach $summaryFields['filename'] as $key => $field}
                                        {if $field eq $tableField->getFieldName()}
                                            <td id="td-{$tableField->getFieldName()}-{$idFieldTable}" class="text-right">
                                                <span
                                                    id="{$tableField->getFieldName()}-{$idFieldTable}">
                                                    {if $summaryRowData neq NULL}{$summaryRowData[$tableField->getFieldName()]}{/if}
                                                </span>
                                            </td>
                                        {/if}
                                    {/foreach}
                                {/if}
                            {/foreach}
                        </tr>
                    {/if}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">&nbsp;</div>
</div>