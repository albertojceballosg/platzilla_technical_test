{strip}
    {assign var='opionTotalValue' value = $graphOptions|@count}
    {if $opionTotalValue gt 0}
        <table width="100%" style="border: 0 solid #ffffff">
            {foreach $PROPERTIES as $property}
                {assign var='varTitle' value=$property.objectname|cat: '-'|cat:$property.optionname}
                {assign var='optionGraphValue' value = ''}
                {if isset($graphOptions[$property.objectname])}
                    {if  $graphOptions[$property.objectname]|is_array}
                        {assign var='optionGraphValue' value = $graphOptions[$property.objectname][$property.optionname]}
                    {else}
                        {assign var='optionGraphValue' value = $graphOptions[$property.optionname]}
                    {/if}
                {else}
                    {assign var='optionGraphValue' value = $graphOptions[$property.optionname]}
                {/if}
                {if $property.optionname eq 'series'}
                    {assign var='optionGraphValue' value = $optionGraphValue|@count}
                {/if}
                {if $property.element eq 'input'}
                    <tr>
                        <td class="col-labels">{$MOD.$varTitle}</td>
                        <td>
                            <input type="text" class="form-control" title=""
                                   name="options{if $property.objectname neq 'graphic'}[{$property.objectname}][{$property.optionname}][]{else}[{$property.optionname}][]{/if}"
                                   placeholder=""
                                   value="{$optionGraphValue}">
                        </td>
                    </tr>
                {elseif $property.element eq 'select'}
                    <tr>
                        <td class="col-labels">{$MOD.$varTitle}</td>
                        <td>
                            <select title=""
                                    name="options{if $property.objectname neq 'graphic'}[{$property.objectname}][{$property.optionname}][]{else}[{$property.optionname}][]{/if}"
                                    class="form-control" title="">
                                <option value="" {if $optionGraphValue eq NULL} selected="selected" {/if}>
                                    Seleccione
                                </option>
                                {foreach $property.optionvalue as $valueId => $value}
                                    <option value="{$valueId}" {if $optionGraphValue eq $valueId} selected="selected" {/if}>{$value}</option>
                                {/foreach}
                            </select>
                            <span id="gr-dategrouping" class="help-block"></span>
                        </td>
                    </tr>
                {/if}
            {/foreach}
        </table>
    {else}
        &nbsp;
    {/if}
{/strip}