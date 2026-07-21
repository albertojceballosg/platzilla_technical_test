{extends file='modules/DailyReport/base/progressOfWorkLayout.tpl'}
{assign var='summaryEstimated' value=0}
{assign var='summaryUsed' value=0}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
{/block}
{block name="col_1"} style="width:28%;vertical-align: top;" {/block}
{block name="col_2"} style="width:10%;vertical-align: top;" {/block}
{block name="col_3"} style="width:10%;vertical-align: top;" {/block}
{block name="col_4"} style="width:28%;vertical-align: top;" {/block}
{block name="col_5"} style="width:10%;vertical-align: top;" {/block}
{block name="col_6"} style="width:4%;vertical-align: top;" {/block}
{block name="tbodyJobReport"}
    {if $GLOBAL_REPORT neq NULL}
        {foreach $GLOBAL_REPORT as $report}
            {include file='modules/DailyReport/Objects/row-global-report-template.tpl'}
            {$summaryEstimated = ($summaryEstimated + $report['work']['estimated_time'])}
            {$summaryUsed = ($summaryUsed + $report['report']->getTimeDuration())}
        {/foreach}
    {else}
        <tr valign="top">
            <td colspan="7"></td>
        </tr>
    {/if}
{/block}
{block name="summaryRow"}
    <tr id="summary-row-{$idProgressJob}" valign="top">
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text"
                   id="total_time_reported-27368"
                   value="{$summaryEstimated|number_format:2:'.':''}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text"
                   id="total_time_reported-27368"
                   value="{$summaryUsed|number_format:2:'.':''}" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}