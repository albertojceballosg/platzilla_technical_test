{strip}
    <div class="form-group">
        <label>{$MODSTRING.LBL_MONTH}</label>
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <select class="form-control" id="monthsearch" name="monthsearch" title=""
                        onchange="getIndicatorsMonths(this)">
                    <option value="">{$MODSTRING.LBL_SELECTION_MONTH}</option>
                    <option value="01"{if ($MONTH_SEARCH == '01')} selected="selected"{/if}>{$MODSTRING.LBL_ENERO}</option>
                    <option value="02"{if ($MONTH_SEARCH == '02')} selected="selected"{/if}>{$MODSTRING.LBL_FEBRERO}</option>
                    <option value="03"{if ($MONTH_SEARCH == '03')} selected="selected"{/if}>{$MODSTRING.LBL_MARZO}</option>
                    <option value="04"{if ($MONTH_SEARCH == '04')} selected="selected"{/if}>{$MODSTRING.LBL_ABRIL}</option>
                    <option value="05"{if ($MONTH_SEARCH == '05')} selected="selected"{/if}>{$MODSTRING.LBL_MAYO}</option>
                    <option value="06"{if ($MONTH_SEARCH == '06')} selected="selected"{/if}>{$MODSTRING.LBL_JUNIO}</option>
                    <option value="07"{if ($MONTH_SEARCH == '07')} selected="selected"{/if}>{$MODSTRING.LBL_JULIO}</option>
                    <option value="08"{if ($MONTH_SEARCH == '08')} selected="selected"{/if}>{$MODSTRING.LBL_AGOSTO}</option>
                    <option value="09"{if ($MONTH_SEARCH == '09')} selected="selected"{/if}>{$MODSTRING.LBL_SEPTIEMBRE}</option>
                    <option value="10"{if ($MONTH_SEARCH == '10')} selected="selected"{/if}>{$MODSTRING.LBL_OCTUBRE}</option>
                    <option value="11"{if ($MONTH_SEARCH == '11')} selected="selected"{/if}>{$MODSTRING.LBL_NOVIEMBRE}</option>
                    <option value="12"{if ($MONTH_SEARCH == '12')} selected="selected"{/if}>{$MODSTRING.LBL_DICIEMBRE}</option>
                </select>
        </div>
    </div>
{/strip}