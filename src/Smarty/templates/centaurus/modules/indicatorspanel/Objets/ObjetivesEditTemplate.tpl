{strip}
    {if ($allObjetives neq NULL) && ($targetMonth neq NULL)}
        {if $objectiveScale eq 'WEEK'}
            {assign var='lastMonth' value=null}
            {foreach $allObjetives as $objetive}
                {if $lastMonth eq $objetive['month_apli']}{continue}{/if}
                {math equation= rand() assign= "id"}
                <tr class="template-week-{$id}">
                    <td id="target_month-{$idBoxScore}" colspan="3" class="text-center"
                        style="padding: 2px !important;">
                        {include file="modules/indicatorspanel/Objets/TargetMonthOption.tpl" optionType="target_month" idTargetMonth= $id targetMonth= $objetive['month_apli']}
                    </td>
                    <td class="text-center" style="vertical-align: top; width: 5%">
                        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                                onclick="IndicatorUtils.delRowWeekToTable (this, 'template-week-{$id}', '{$idBoxScore}');">
                            <i class="fa fa-trash-o"></i>
                        </button>

                    </td>
                </tr>
                <tr class="template-week-{$id}" id="tr-{$id}" style="padding: 1px!important;">
                    {if $MONTHS neq NULL && $lastMonth neq $objetive['month_apli']}
                        {assign var='MONTH' value=$objetive['month_apli']}
                        {assign var='WEEKS' value=$MONTHS[$objetive['month_apli']]}
                        {include file="modules/indicatorspanel/Objets/WeekObjetiveDetailView.tpl"}
                    {/if}
                </tr>
                {assign var='lastMonth' value=$objetive['month_apli']}
            {/foreach}
        {elseif $objectiveScale eq 'MONTH'}
            {foreach $allObjetives as $objetive}
                {math equation= rand() assign= "id"}
                <tr class="template-month-{$id}">
                    <td>
                        {include file="modules/indicatorspanel/Objets/TargetMonthOption.tpl" optionType="month_objetive" idTargetMonth= $id targetMonth=$objetive['month_apli']}
                    </td>
                    <td>
                        <select class="form-control operator" id="operator" name="operator[]" title="Operador">
                            <option value="less-equal"
                                    {if $objetive['operator'] eq 'less-equal'}selected{/if}> <= </option>
                            <option value="greater-equal"
                                    {if $objetive['operator'] eq 'greater-equal'}selected{/if}> >=  </option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control objetive" id="objetive-{$id}" name="objetive[]"
                               value="{$objetive['objective']}" title="{$MOD.LBL_OBJECT}"
                               placeholder="{$MOD.Ingresar} {$MOD.LBL_OBJECT}">
                    </td>
                    <td class="text-center" style="vertical-align: top; width: 5%">
                        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                                onclick="IndicatorUtils.delRowWeekToTable (this, 'template-month-{$id}', '{$idBoxScore}');">
                            <i class="fa fa-trash-o"></i>
                        </button>

                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td id="target_month-{$idBoxScore}" colspan="4" class="text-center hide"
                    style="padding: 2px !important;">
                </td>
            </tr>
            <tr id="tr-{$idBoxScore}"></tr>
        {/if}
    {else}
        <tr>
            <td id="target_month-{$idBoxScore}" colspan="4" class="text-center hide"
                style="padding: 2px !important;">
            </td>
        </tr>
        <tr id="tr-{$idBoxScore}"></tr>
    {/if}
{/strip}