{math equation= rand() assign= "idRowResourceInitiative"}
{assign var="moduleLabel" value=null}
<tr id="tr-row-{$idRowResourceInitiative}" data-row-id="{$idRowResourceInitiative}" class="tabla-field-row"
    valign="top">
    <td width="18%" style="vertical-align: top">

        <div class="input-group" style="width: 100%;">
            <span id="inputpc_task_advanced-{$idRowResourceInitiative}">
                {foreach $TYPE_RESOURCE as $tabname => $tablabel}
                    {if $resource->getTypeResource() eq $tabname}
                        {assign var="moduleLabel" value=$tablabel}
                        {$tablabel}
                    {/if}
                {/foreach}
            </span>
        </div>
    </td>
    <td width="25%" style="vertical-align: top">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_resource_{$idRowResourceInitiative}_">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <span id="resource_initiative-id-{$idRowResourceInitiative}"
                              class="form-control   b-left"
                              style="overflow-x: hidden;width: 100%">
                            <a href="index.php?module={$resource->getTypeResource()}&action=DetailView&record={$resource->getIdResource()}"
                               target="_blank"
                               title="{$moduleLabel}: Recurso para concretar la iniciativa">{$resource->getResourceDescription()}</a>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <span id="inputpc_task_advanced-{$idRowResourceInitiative}">
                {if $resource->getContributionFactor() neq NULL}
                    {number_format($resource->getContributionFactor(), 2, ',', '.')}
                {else}
                    0.00
                {/if}
            </span>
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
             <span id="inputpc_task_advanced-{$idRowResourceInitiative}">
                {if $resource->getResourceProgress() neq NULL}
                    {number_format($resource->getResourceProgress(), 2, ',', '.')}
                {else}
                    0.00
                {/if}
            </span>
        </div>
    </td>
    <td width="14%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <span id="inputpc_task_advanced-{$idRowResourceInitiative}">
                {if $resource->getTotalContribution() neq NULL}{number_format($resource->getTotalContribution(), 2, ',', '.')}
                {else}
                    0.00
                {/if}
            </span>
        </div>
    </td>
</tr>