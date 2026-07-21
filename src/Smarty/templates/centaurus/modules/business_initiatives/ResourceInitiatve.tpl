{math equation= rand() assign= "idResourceInitiative"}
{if $RESOURCE_INITIATIVE neq NULL}
    {assign var="totalResource" value=count($RESOURCE_INITIATIVE)}
{else}
    {assign var="totalResource" value=null}
{/if}
<link rel="stylesheet" type="text/css" href=modules/grid_view/grid-view.css"/>
<div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 20px"{/if}>
    <div class="table-responsive  field-container">
        <table id="resource-initiative-table-{$idResourceInitiative}"
               class="table table-bordered tablegridvalidate">
            <thead>
            <tr>
                <td colspan="6" style="text-align: left; background-color:#f9f8f7"><strong>Recursos para concretar la iniciativa</strong></td>
            </tr>
            <tr valign="top">
                <td style="" width="20%"><span style="">Tipo de recurso</span></td>
                <td style="" width="20%"><span style="">Recurso</span></td>
                <td style="" width="15%"><span style="">Factor de contribución</span></td>
                <td style="" width="15%"><span style="">% de avance</span></td>
                <td style="" width="15%"><span style="">Total contribución</span></td>
                {if $VIEW eq NULL}
                    <td class="text-center" width="15%">Acciones</td>
                {/if}
            </tr>
            </thead>
            <tbody id="tbody-resource-initiative-{$idResourceInitiative}" rowtotal="0">
            {if $RESOURCE_INITIATIVE neq NULL}
                {foreach $RESOURCE_INITIATIVE as $resource}
                    {if $VIEW eq NULL}
                        {include file='modules/business_initiatives/resource_initiative.tpl'}
                    {else}
                        {include file='modules/business_initiatives/resource_initiative_detail-view.tpl'}
                    {/if}
                {/foreach}
            {else}
                <tr>
                    <td colspan="5" style="text-align: center"></td>
                </tr>
            {/if}
            </tbody>
            <tfoot id="tfoot-{$idResourceInitiative}" data-field-name="resource_initiatives" data-summary-row=""
                   data-operation-row="">
            <tr id="summary-row-{$idResourceInitiative}" valign="top">
                <td colspan="2"><p style="text-align: right">Totales:&nbsp;</p></td>
                <td id="td-time_reported-{$idResourceInitiative}">
                    {if $VIEW eq NULL}
                        <input type="text" id="total_contribution_factor-{$idResourceInitiative}"
                               name="resource_initiatives[summary_factor]" rel="SUM_COLUMN"
                               value="{if $RESOURCE_INITIATIVE neq NULL}{$RESOURCE_INITIATIVE[0]->getSummaryFactor ()}{else}0.00{/if}"
                               class="form-control" readonly="">
                    {else}
                        <div class="input-group text-right" style="width: 100%; text-align: left">
                            <span id="input-total_time_reported-{$idResourceInitiative}" style="text-align: right">
                                {if $RESOURCE_INITIATIVE neq NULL}
                                    {$RESOURCE_INITIATIVE[0]->getSummaryFactor ()|number_format:2:',':'.'}
                                {else}0.00
                                {/if}
                            </span>
                        </div>
                    {/if}
                </td>
                <td class="text-center">&nbsp;</td>
                <td id="td-time_reported-{$idResourceInitiative}">
                    {if $VIEW eq NULL}
                        <input type="text" id="total_total_contribution-{$idResourceInitiative}"
                               name="resource_initiatives[summary_contribution]" rel="SUM_COLUMN"
                               value="{if $RESOURCE_INITIATIVE neq NULL}{$RESOURCE_INITIATIVE[0]->getSummaryContribution ()}{else}0.00{/if}"
                               class="form-control" readonly="">
                    {else}
                        <div class="input-group text-right" style="width: 100%; text-align: left">
                            <span id="input-total_time_reported-{$idResourceInitiative}" style="text-align: right">
                                {if $RESOURCE_INITIATIVE neq NULL}
                                    {$RESOURCE_INITIATIVE[0]->getSummaryContribution ()|number_format:2:',':'.'}
                                {else}
                                    0.00
                                {/if}
                            </span>
                        </div>
                    {/if}
                </td>
                <td class="text-center">&nbsp;</td>
            </tr>
            {if $VIEW eq NULL}
                <tr>
                    <td colspan="6" class="text-center">
                        <button type="button" data-id-linkage="{$idResourceInitiative}" class="btn btn-primary"
                                data-sequence="{$totalResource}"
                                data-template="resource-initiative-template-{$idResourceInitiative}"
                                onclick="BusinessInitiativesUtils.addRowToTable (this, 'tbody-resource-initiative-{$idResourceInitiative}', '{$idResourceInitiative}');">
                            <i class="fa fa-plus"></i></button>

                    </td>
                </tr>
            {/if}
            </tfoot>
        </table>
    </div>
</div>
{if $VIEW eq NULL}
    <script type="text/html" id="resource-initiative-template-{$idResourceInitiative}">
        {include file='modules/business_initiatives/resource_initiative_template.tpl'}
    </script>
    <script type="text/html" id="tbody-resource-initiative-{$idResourceInitiative}-template">
        <tr>
            <td colspan="5" style="text-align: center"></td>
        </tr>
    </script>
{/if}