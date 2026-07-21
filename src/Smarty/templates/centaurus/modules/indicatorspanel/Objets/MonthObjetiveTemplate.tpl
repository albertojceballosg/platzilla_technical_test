{strip}
    <tr class="template-month-__ID__">
        <td>
            {include file="modules/indicatorspanel/Objets/TargetMonthOption.tpl" optionType="month_objetive" idTargetMonth = '__ID__' targetMonth=''}
        </td>
        <td>
            <select class="form-control operator" id="operator" name="operator[]" title="Operador">
                <option value="less-equal"> <= </option>
                <option value="greater-equal"> &gt;= </option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control objetive" id="objetive-__ID__" name="objetive[]"
                   value="" title="{$MOD.LBL_OBJECT}"
                   placeholder="{$MOD.Ingresar} {$MOD.LBL_OBJECT}">
        </td>
        <td class="text-center" style="vertical-align: top; width: 5%">
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                    onclick="IndicatorUtils.delRowWeekToTable (this, 'template-month-__ID__', '{$idBoxScore}');">
                <i class="fa fa-trash-o"></i>
            </button>

        </td>
    </tr>
{/strip}