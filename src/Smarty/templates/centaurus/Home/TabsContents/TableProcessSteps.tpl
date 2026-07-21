{extends file='Home/TabsContents/Base/TableStepsLayOut.tpl'}
{block name="processes_name"}
    {*$CASE_DETAILS|var_dump*}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td style="text-align: left;font-weight: bold;width: 10%">Proceso:</td>
            <td style="text-align: left">
                {if $CASE_DETAILS neq NULL}
                    <a title="Ver detalles del proceso"
                       style="color: #000000"
                       target="_blank"
                       href="index.php?module=process&action=DetailView&record={$CASE_DETAILS[0]['process']['processid']}">
                        {$CASE_DETAILS[0]['process']['process_title']}
                    </a>
                {else}
                    <span style="width: 100%">No hay proceso</span>
                {/if}
            </td>
        </tr>
        </tbody>
    </table>
{/block}

{block name="case_name"}
    <table class="table table-bordered table-condensed">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td style="text-align: left;font-weight: bold;width: 12%">Casos:</td>
            <td style="text-align: left">
                {if $ALL_CASES neq NULL || $ALL_CASES|@count gt 0}
                    <select class="form-control input-sm"
                            data-processid="{{$CASE_DETAILS[0]['process']['processid']}}"
                            onchange="ProcessCasesUtils.selectedCase(this,'{$HOME_TAB_ID}')"
                            style="margin-bottom: 0!important;margin-top: 0!important;">
                        <option value="">Seleccionar caso</option>
                        {foreach $ALL_CASES as $case}
                            <option value="{$case['casesid']}" {if $CASE_ID eq $case['casesid']}selected{/if}>
                                {$case['step_name']}: {$case['title']}
                            </option>
                        {/foreach}
                    </select>
                {else}
                    <span style="width: 100%">No hay casos de proceso</span>
                {/if}
            </td>
        </tr>
        </tbody>
    </table>
    {*$STEPS_GRAPH2|var_dump*}
{/block}

{block name="flow_steps"}
    {$PROCESS_CASE}
{/block}

{block name="row_steps"}
    {if $CASE_DETAILS neq NULL}
        {foreach $CASE_DETAILS as $key => $step}
            {if $step['step']['process_stepsid'] eq NULL}{continue}{/if}
            {include file='Home/TabsContents/Objects/RowTableProcessSteps.tpl' step=$step}
        {/foreach}
    {else}
        <tr>
            <td colspan="8" style="text-align: center">No hay pasos en este proceso</td>
        </tr>
    {/if}
{/block}

{block name="steps_total_time"}
    {if $CASE_DETAILS neq NULL}
        {$CASE_DETAILS['summary_time']}
    {else}
        0
    {/if}
{/block}
{block name="page_loading"}
    <div id="page-loading-{$HOME_TAB_ID}" style="width:100%;"></div>
{/block}

{block name="chart_steps"}
    <div id="chart_steps-{$HOME_TAB_ID}" style="width:100%; height:410px;"></div>
{/block}
{block name="script"}
    {if $STEPS_GRAPH neq NULL}
    {literal}
        <script>
            google.charts.load('current', {'packages': ['corechart']});
            google.charts.setOnLoadCallback(drawStepsChart_{/literal}{$HOME_TAB_ID}{literal});
            function drawStepsChart_{/literal}{$HOME_TAB_ID}{literal}() {
                var data = google.visualization.arrayToDataTable({/literal}{$STEPS_GRAPH}{literal});
                var options = {
                    title: 'Dispersión de tiempos de ejecución respecto a la media',
                    legend: {position: 'none'},
                    height: 500,
                    series: {
                        0: {color: 'green', visibleInLegend: false, lineDashStyle: [4, 4]},
                        1: {color: 'green', visibleInLegend: false, lineDashStyle: [4, 4]},
                        2: {color: 'red', visibleInLegend: false, lineDashStyle: [4, 4]},
                        3: {color: 'red', visibleInLegend: false, lineDashStyle: [4, 4]},
                {/literal}
                {foreach $SERIES_LABEL as $key => $lebel}
                {($key + 4)}{literal}: {visibleInLegend: false, format: 'decimal'},{/literal}
                {/foreach}
                {literal}
            },

                vAxis: {
                    viewWindow: {
                        min:-5, max:5
                    }
                }
            }
                var chart = new google.visualization.LineChart(document.getElementById('chart_steps-{/literal}{$HOME_TAB_ID}{literal}'));
                google.visualization.events.addListener(chart, 'select', function() {
                      var selectedItem = chart.getSelection()[0],
                          excludedSeries = ['Caso', 'tiempo','Control sup ','Control inf','Control sup 3s','Control inf 3s'];
                      if (selectedItem) {
                        var stepName = data.getValue(selectedItem.row, 0);
                        var seriesIndex = selectedItem.column - 1;
                        var seriesTitle = data.getColumnLabel(seriesIndex + 1);
                        if (!excludedSeries.includes(seriesTitle)) {
                            var container = jQuery ('#page-loading-{/literal}{$HOME_TAB_ID}{literal}'),
                                arguments = {
                                'module':      'Home',
                                'action':      'AjaxHomeUtils',
                                'function':    'GET-PROCESS-CASE',
                                'case_number': seriesTitle,
                                'step_name':   stepName,
                                'Ajax':        true
                            };
                            container.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
                            jQuery.post ('index.php', arguments, function (data) {
                                var message, data;
                                try {
                                    message = JSON.parse (JSON.stringify (data));
                                    if(message.error !== 'OK') {
                                        throw message.error;
                                    } else {
                                        container.html('');
                                        window.open (
                                            'index.php?module=process_cases&parenttab=&action=DetailView&record=' + message.html,
                                            '_blank'
                                        );
                                    }
                                } catch (e) {
                                    alert (e);
                                    container.html('');
                                }
                            });
                        }

                        //alert('Punto: ' + point + ' - ' + point2);
                      }
                    });

                chart.draw(data, options);
            }
        </script>
    {/literal}
    {/if}
{/block}