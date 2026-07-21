{strip}
    <div class="form-group">
        <label>{$MODSTRING.LBL_VIEW}</label>
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-exchange"></i>
            </div>
            <select class="form-control" id="viewScale" name="viewScale" title=""
                    onchange="getIndicatorsView(this)">
                <option value="">{$MODSTRING.LBL_SELECTION_VIEW}</option>
                <option value="Month" {if ($VIEW_SEARCH == 'Month')} selected="selected"{/if}>{$MODSTRING.LBL_VIEW_MONTH}</option>
                <option value="Week" {if ($VIEW_SEARCH == 'Week')} selected="selected"{/if}>{$MODSTRING.LBL_VIEW_WEEK}</option>
            </select>
        </div>
    </div>
{/strip}