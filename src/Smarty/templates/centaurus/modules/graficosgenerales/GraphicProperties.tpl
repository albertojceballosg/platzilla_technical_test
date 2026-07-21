{strip}
    <table width="100%" style="border: 0 solid #ffffff">
        {foreach $PROPERTIES as $property}
            {assign var='varTitle' value=$property.objectname|cat: '-'|cat:$property.optionname}
            {if $property.element eq 'input'}
                <tr>
                    <td class="col-labels"><p style="text-align: left; padding-left: 20px">{$MOD.$varTitle}</p></td>
                    <td>
                        <input type="text" class="form-control" title=""
                               name="options{if $property.objectname neq 'graphic'}[{$property.objectname}][{$property.optionname}][]{else}[{$property.optionname}][]{/if}"
                               placeholder=""
                               value="{if ! empty($GRAPH_DATA['title'])} {$GRAPH_DATA['title']}{/if}">
                    </td>
                </tr>
            {elseif $property.element eq 'select'}
                <tr>
                    <td class="col-labels"><p style="text-align: left; padding-left: 20px">{$MOD.$varTitle}</p></td>
                    <td>
                        <select title=""
                                name="options{if $property.objectname neq 'graphic'}[{$property.objectname}][{$property.optionname}][]{else}[{$property.optionname}][]{/if}"
                                class="form-control" title="">
                            <option value="" selected="selected">
                                Seleccione
                            </option>
                            {foreach $property.optionvalue as $valueId => $value}
                                <option value="{$valueId}">{$value}</option>
                            {/foreach}
                        </select>
                        <span id="gr-dategrouping" class="help-block"></span>
                    </td>
                </tr>
            {/if}
        {/foreach}
    </table>
{/strip}