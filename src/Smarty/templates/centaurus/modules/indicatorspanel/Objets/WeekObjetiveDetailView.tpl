{strip}
    {math equation= rand() assign= "idWeekBoxScore"}
    <td colspan="4" class="text-center" style="padding: 1px !important;">
    <table id="table-week-objetive-{$idWeekBoxScore}"
           style="margin: 0!important; padding: 0!important; width: 100% !important;"
           class="table table-hover table-bordered dataTable" role="grid">
        <thead id="header-week-range-{$idWeekBoxScore}">&nbsp;</thead>
        <tbody id="body-range-{$idWeekBoxScore}">
        {foreach $WEEKS as $week => $weekDate}
            <tr class="">
                <td style="width: 32%">
                    <input type="hidden" name="week[{$MONTH}][]" value="{$week}">
                    <input type="text" class="form-control " id="week-{$idWeekBoxScore}"
                           readonly="readonly"
                           name="week_target[{$MONTH}][from][]"
                           value="{$weekDate['start']}" title="{$MOD.LBL_WEEK}">
                </td>
                <td style="width: 32%">
                    <input type="text" class="form-control " id="week-{$idWeekBoxScore}"
                           readonly="readonly"
                           name="week_target[{$MONTH}][to][]"
                           value="{$weekDate['end']}" title="{$MOD.LBL_WEEK}">
                </td>
                <td style="width: 16%">
                    <select class="form-control operator" id="operator" name="operator[{$MONTH}][]" title="Operador">
                        <option value="less-equal"
                                {if $weekDate['operator'] eq 'less-equal'}selected{/if}> <= </option>
                        <option value="greater-equal"
                                {if $weekDate['operator'] eq 'greater-equal'}selected{/if}> >=  </option>
                    </select>
                </td>
                <td style="width: 20%">
                    <input type="text" class="form-control objetive" id="objetive-{$idWeekBoxScore}"
                           name="objetive[{$MONTH}][]"
                           value="{$weekDate['objective']}" title="{$MOD.LBL_OBJECT}"
                           placeholder="{$MOD.LBL_OBJECT}">
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    </td>
{/strip}