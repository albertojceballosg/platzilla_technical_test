{strip}
    <tr class="template-week-__ID__">
        <td id="target_month-{$idBoxScore}" colspan="3" class="text-center" style="padding: 2px !important;">
            {include file="modules/indicatorspanel/Objets/TargetMonthOption.tpl" optionType="target_month" idTargetMonth = '__ID__' targetMonth=''}
        </td>
        <td class="text-center" style="vertical-align: top; width: 5%">
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                    onclick="IndicatorUtils.delRowWeekToTable (this, 'template-week-__ID__', '{$idBoxScore}');">
                <i class="fa fa-trash-o"></i>
            </button>

        </td>
    </tr>
    <tr class="template-week-__ID__" id="tr-__ID__" style="padding: 1px!important;"></tr>
{/strip}