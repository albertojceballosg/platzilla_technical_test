{extends file='Home/TabsContents/Base/ProcessDetailViewLayOut.tpl'}
{block name="processes_name"}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td style="text-align: left;font-weight: bold">Proceso:</td>
            <td style="text-align: left">
                <a title="Ver detalles del proceso"
                   style="color: #000000"
                   target="_blank"
                   href="index.php?module=process&action=DetailView&record={$PROCESS_DATA['processid']}">
                    {$PROCESS_DATA['process_title']}
                </a>
            </td>
        </tr>
        </tbody>
    </table>
{/block}

{block name="summary_finished_cases"}
    {*$SUMMAY_FINISHED_CASE|var_dump*}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td colspan="2" style="text-align: left; font-weight: bold; color: #09498c">
                {$MOD['LBL_CASES_COMPLETED']}
            </td>
        </tr>
        <tr>
            <td style="text-align: left">{$MOD['LBL_NUMBER_OF_CASES']}</td>
            <td style="text-align: left">{$SUMMAY_FINISHED_CASE['total']}</td>

        </tr>
        <tr>
            <td style="text-align: left">{$MOD['LBL_AVERAGE_TIME']}</td>
            <td style="text-align: left">{$SUMMAY_FINISHED_CASE['avg']} hrs</td>

        </tr>
        <tr>
            <td style="text-align: left">{$MOD['LBL_STANDARD DEVIATION']}</td>
            <td style="text-align: left">{$SUMMAY_FINISHED_CASE['stddev']} hrs</td>

        </tr>
        </tbody>
    </table>
{/block}

{block name="chart_finished_case"}
    <div id="finished_case-{$HOME_TAB_ID}" style="width:100%; height:410px;"></div>
{/block}

{block name="table_finished_cases_involved"}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr style="background-color: #efefef">
            <td style="text-align: center; width: 8%">&nbsp;</td>
            <td style="text-align: left; width: 82%">{$MOD['LBL_CASE_PROCESS']}</td>
            <td style="text-align: center; width: 10%">{$MOD['LBL_RUN_TIME']}</td>
        </tr>
        {if $FINISHED_CASE_DETAIL neq NULL}
            {foreach $FINISHED_CASE_DETAIL as $cases}
                <tr>
                    <td style="text-align: center; width: 8%">
                        <i class="fa fa-circle fa-2x" aria-hidden="true"
                           style="color: {$CONTROL_BANDS[$cases['state']]}"></i>
                    </td>
                    <td style="text-align: left; width: 84%">
                        <a title="Ver detalles del caso"
                           rel="{$HOME_TAB_ID}"
                           data-processid="{$PROCESS_DATA['processid']}"
                           style="color: #000000"
                           onclick="ProcessCasesUtils.getProcessSteps(this,'{$cases['casesid']}', event)"
                           href="#{*index.php?module=process_cases&parenttab=&action=DetailView&record={$cases['casesid']*}">
                            {$cases['title']}
                        </a>
                    </td>
                    <td style="text-align: center; width: 8%">{$cases['time']}</td>

                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="3" style="text-align: center; font-weight: bold">{$MOD['LBL_NOT_PROCESSES']}</td>
            </tr>
        {/if}
        </tbody>
    </table>
{/block}

{block name="summary_unfinished_cases"}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td colspan="2" style="text-align: left; font-weight: bold; color: #09498c">
                {$MOD['LBL_CASES_IN_PROGRESS']}
            </td>
        </tr>
        <tr>
            <td style="text-align: left">{$MOD['LBL_NUMBER_OF_CASES']}</td>
            <td style="text-align: left">{$SUMMAY_UNFINISHED_CASE['total']}</td>

        </tr>
        <tr>
            <td style="text-align: left">{$MOD['LBL_AVERAGE_TIME']}</td>
            <td style="text-align: left">{$SUMMAY_UNFINISHED_CASE['avg']} hrs</td>

        </tr>
        <tr>
            <td style="text-align: left">{$MOD['LBL_STANDARD DEVIATION']}</td>
            <td style="text-align: left">{$SUMMAY_UNFINISHED_CASE['stddev']} hrs</td>

        </tr>
        </tbody>
    </table>
{/block}

{block name="chart_unfinished_case"}
    <div id="unfinished_case-{$HOME_TAB_ID}" style="width:100%; height:410px;"></div>
{/block}

{block name="table_unfinished_cases_involved"}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr style="background-color: #efefef">
            <td style="text-align: center; width: 8%">&nbsp;</td>
            <td style="text-align: left; width: 74%">{$MOD['LBL_CASE_PROCESS']}</td>
            <td style="text-align: center; width: 10%">{$MOD['LBL_RUN_TIME']}</td>
            <td style="text-align: center; width: 8%">{$MOD['LBL_NO_STEPS_TAKEN']}</td>
        </tr>
        {if $UNFINISHED_CASE_DETAIL neq NULL}
            {foreach $UNFINISHED_CASE_DETAIL as $cases}
                <tr>
                    <td style="text-align: center; width: 8%">
                        <i class="fa fa-circle fa-2x" aria-hidden="true"
                           style="color: {$CONTROL_BANDS[$cases['state']]}"></i>
                    </td>
                    <td style="text-align: left; width: 84%">
                        <a title="Ver detalles del caso"
                           rel="{$HOME_TAB_ID}"
                           data-processid="{$PROCESS_DATA['processid']}"
                           style="color: #000000"
                           onclick="ProcessCasesUtils.getProcessSteps(this,'{$cases['casesid']}', event)"
                           href="#{*index.php?module=process_cases&parenttab=&action=DetailView&record={$cases['casesid']*}">
                            {$cases['title']}
                        </a>
                    </td>
                    <td style="text-align: center; width: 8%">{$cases['time']}</td>
                    <td style="text-align: center; width: 8%">{$cases['step_exec']}</td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="4" style="text-align: center; font-weight: bold">{$MOD['LBL_NOT_PROCESSES']}</td>
            </tr>
        {/if}
        </tbody>
    </table>
{/block}
{block name="script"}
{literal}
    <script>
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawFinishedChart_{/literal}{$HOME_TAB_ID}{literal});

        function drawFinishedChart_{/literal}{$HOME_TAB_ID}{literal}() {
            var data = google.visualization.arrayToDataTable({/literal}{$FINISHED_CASE_GRAPH}{literal});
            var options = {
                title: '{/literal}{$MOD['LBL_CASES_COMPLETED']}{literal}',
                legend: {position: 'none'},
                height: 400,
                series: {
                    0:{color: 'blue', visibleInLegend: false, lineDashStyle: [4, 1, 4],format: 'decimal'},
                    1:{color: 'green', visibleInLegend: false, lineDashStyle: [4, 4]},
                    2:{color: 'green', visibleInLegend: false, lineDashStyle: [4, 4]},
                    3:{color: 'red', visibleInLegend: false, lineDashStyle: [4, 4]},
                    4:{color: 'red', visibleInLegend: false, lineDashStyle: [4, 4]},
                }

            };
            var chart = new google.visualization.LineChart(document.getElementById('finished_case-{/literal}{$HOME_TAB_ID}{literal}'));
            chart.draw(data, options);
        }
    </script>
{/literal}
{literal}
    <script>
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawUnfinishedChart_{/literal}{$HOME_TAB_ID}{literal});

        function drawUnfinishedChart_{/literal}{$HOME_TAB_ID}{literal}() {
            var data = google.visualization.arrayToDataTable({/literal}{$UNFINISHED_CASE_GRAPH}{literal});
            var options = {
                title: '{/literal}{$MOD['LBL_CASES_IN_PROGRESS']}{literal}',
                legend: {position: 'none'},
                height: 400,
                series: {
                    0:{color: 'blue', visibleInLegend: false, lineDashStyle: [4, 1, 3]},
                    1:{color: 'green', visibleInLegend: false, lineDashStyle: [4, 4]},
                    2:{color: 'green', visibleInLegend: false, lineDashStyle: [4, 4]},
                    3:{color: 'red', visibleInLegend: false, lineDashStyle: [4, 4]},
                    4:{color: 'red', visibleInLegend: false, lineDashStyle: [4, 4]},
                }

            };
            var chart = new google.visualization.LineChart(document.getElementById('unfinished_case-{/literal}{$HOME_TAB_ID}{literal}'));
            chart.draw(data, options);
        }
    </script>
{/literal}
{/block}