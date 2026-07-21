{strip}
    {math equation= rand() assign= "idAlertSource"}
    <fieldset  name="alert-source"  id="{$idAlertSource}">
        {*$DETAIL_ALERT|var_dump*}
        {* app type source element content *}
        <div class="form-group" id="dv-appAlertType-{$idAlertSource}">
            <label for="colorbase">{$MODSTRING.LBL_ALERT_TYPE}</label>
            <select class="form-control" id="codetype-{$idAlertSource}" name="codetype"
                    title="{$MODSTRING.LBL_ALERT_TYPE}"
                    onchange="SystemAlertUtils.selectAlertType(this, '{$idAlertSource}')">
                <option value="">{$MODSTRING.LBL_SELECTION}</option>
                <option value="Indicators"
                        {if $DETAIL_ALERT.source_alert == 'Indicators'}selected="selected"{/if}>{$MODSTRING.LBL_OPTION_INDICATORS}</option>
                <option value="Task_object_no_cump"
                        {if $DETAIL_ALERT.source_alert == 'Task_object_no_cump'}selected="selected"{/if}>{$MODSTRING.LBL_ALERT_MODULE}</option>
                <option value="Task_prog"
                        {if $DETAIL_ALERT.source_alert == 'Task_prog'}selected="selected"{/if}>{$MODSTRING.LBL_OPTION_TASK_PROG}</option>
            </select>
            <span id="sp-codetype" class="help-block" style="color: red"></span>
        </div>
        {* period of alert evaluaton *}
        <div class="form-group" id="dv-periodAlert-{$idAlertSource}"
             style="display:{if $DETAIL_ALERT.source_alert eq 'Indicators'}block{else} none{/if};">
            <label for="colorbase">{$MODSTRING.LBL_ALERT_PERIODICITY}</label>
            <select class="form-control" id="scale-{$idAlertSource}" title="{$MODSTRING.LBL_ALERT_PERIODICITY}"
                    name="scale"
                    onchange="SystemAlertUtils.selectAlertType('#codetype-{$idAlertSource}', '{$idAlertSource}')">
                title="{$MODSTRING.LBL_ALERT_PERIODICITY}">
                <option value="">{$MODSTRING.LBL_SELECTION_PERIODICITY}</option>
                <option value="Month"
                        {if $DETAIL_ALERT.scale == 'Month'}selected="selected"{/if}>{$MODSTRING.LBL_VIEW_MONTH}</option>
                <option value="Week"
                        {if $DETAIL_ALERT.scale == 'Week'}selected="selected"{/if}>{$MODSTRING.LBL_VIEW_WEEK}</option>
            </select>
            <span id="sp-scale" class="help-block" style="color: red"></span>
        </div>
        {* Element type for alert *}
        <div class="form-group" id="dv-appAlertElement-{$idAlertSource}"
             style="display:{if $DETAIL_ALERT neq NULL}block{else} none{/if};">
            <label id="codeElement-title-{$idAlertSource}"
                   for="colorbase">{if $DETAIL_ALERT.source_alert neq NULL} {$MODSTRING[$DETAIL_ALERT.source_alert]}{else} {$MODSTRING.LBL_ALERT_ENTITY}{/if}</label>
            <select class="form-control" id="codeElement-{$idAlertSource}"
                    onchange="SystemAlertUtils.selectAlertModule(this, '{$idAlertSource}')">
                    name="codeElement" title=" ">
                <option value="">{$MODSTRING.LBL_SELECTION}</option>
                {if $DETAIL_ALERT.source_alert neq NULL}
                    {if in_array ($DETAIL_ALERT.source_alert, array('Task_object_no_cump', 'Task_prog'))}
                        {foreach $DETAIL_ALERT.element as $module}
                            <option value="{$module['tabid']}" tabname="{$module['name']}"
                                    tablabel="{$module['tablabel']}"
                                    {if $module['name'] eq $DETAIL_ALERT.tab_name}
                                        selected="selected"
                                    {/if}
                            >{$module['tablabel']}</option>
                        {/foreach}
                    {elseif $DETAIL_ALERT.source_alert eq 'Indicators'}
                        {foreach $DETAIL_ALERT.element as $indicator}
                            <option value="{$indicator['box_score_dataid']}" datarel="{$indicator['datarel']}"
                                    scale="{$indicator['scale']}" bxdatarel="{$indicator['bxdatarel']}"
                                    scaledatarel="{$indicator['scaledatarel']}"
                                    {if ($indicator['bxdatarel'] eq $DETAIL_ALERT.boxscore_id) &&
                                    ($indicator['datarel'] eq $DETAIL_ALERT.indicator_id)}
                                        selected="selected"
                                    {/if}
                            >{$indicator['box_score']}</option>
                        {/foreach}
                    {/if}

                {/if}
            </select>
            <span id="sp-codeElement" class="help-block" style="color: red"></span>
        </div>
    </fieldset>
{/strip}