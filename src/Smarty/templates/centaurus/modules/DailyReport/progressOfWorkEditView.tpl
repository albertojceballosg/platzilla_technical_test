{extends file='modules/DailyReport/base/progressOfWorkLayout.tpl'}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
{/block}
{block name="col_1"}style="width:25%;vertical-align: top;"{/block}
{block name="col_2"}style="width:10%;vertical-align: top;"{/block}
{block name="col_3"}style="width:10%;vertical-align: top;"{/block}
{block name="col_4"}style="width:23%;vertical-align: top;"{/block}
{block name="col_5"}style="width:10%;vertical-align: top;"{/block}
{block name="col_6"}style="width:12%;vertical-align: top;"{/block}
{block name="tbodyJobReport"}
    <tr valign="top">
        <td colspan="7" ></td>
    </tr>
{/block}
{block name="summaryRow"}
    <tr id="summary-row-{$idProgressJob}" valign="top">
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text"
                   id="total_estimated_time-{$idProgressJob}"
                   name="report_job[summary_estimated]"
                   value="0.00" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">
            <input type="text"
                   id="total_time_reported-{$idProgressJob}"
                   name="report_job[summary_used]"
                   value="0.00" class="form-control" readonly="">
        </td>
        <td class="text-center">&nbsp;</td>
        <td class="text-center">&nbsp;</td>
    </tr>
{/block}
{block name="addRow"}
    <tr>
        <td colspan="7" class="text-center">
            <button type="button" class="btn btn-primary" data-sequence="0"
                    data-template="row-job-report-template-{$idProgressJob}"
                    onclick="DailyReportUtils.addRowToTable (this, 'tbody-job-report-{$idProgressJob}', '{$idProgressJob}');">
                <i class="fa fa-plus"></i></button>
        </td>
    </tr>
{/block}
{block name="script_template"}
<script type="text/html" id="row-job-report-template-{$idProgressJob}">
    {include file='modules/DailyReport/Report/row-job-report-template.tpl'}
</script>
<script type="text/html" id="tbody-job-report-{$idProgressJob}-template">
    <tr valign="top">
        <td colspan="7" ></td>
    </tr>
</script>
{/block}