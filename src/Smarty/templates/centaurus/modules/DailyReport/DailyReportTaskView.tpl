{strip}
    {math equation= rand() assign= "idDailyReporttaskView"}
    <div class="row">
        {*$MODULES|var_dump*}
        <div class="col-md-12 col-lg-12 col-sm-12">
            <select id="taskfilter-{$idDailyReporttaskView}"
                    onchange="DailyReportUtils.filterTaskView(this, '{$idDailyReporttaskView}')"
                    class="form-control">
                <option value="">Filtar tarea</option>
                <option value="High-HIGH">Importante - Urgente</option>
                <option value="Low-HIGH">No importante - Urgente</option>
                <option value="High-LOW">Importante - No urgentee</option>
                <option value="Low-LOW">No importante - No Urgente</option>
                {if $MODULES neq NULL}
                    {foreach $MODULES as $myModule}
                        <option value="{$myModule}">{$myModule}</option>
                    {/foreach}
                {/if}

            </select>
        </div>
        <div class="col-md-12 col-lg-12 col-sm-12 daily-report-scroll" style="max-height: 300px; overflow-y: auto">
            <table class="table">
                <thead>
                <tr>
                    <th>Titulo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Modulo</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody id="task-view-{$idDailyReporttaskView}">
                {foreach $QUADRANTS as $quadrant}
                {foreach $TASKS_VIEW_DATA[$quadrant] as $data}
                    <tr id="{$data['modulename']}_{$quadrant}_{$data['crmid']}">
                        <td><a href="#"
                               rel="{$data['crmid']}@{$data['estimated_time']}@{$ROW_ID}@{$PERIOD}"
                               onclick="DailyReportUtils.getTaskFromModal(this, event)"
                               data-module="{$data['tab_name']}">
                                {$data['subject']}
                            </a></td>
                        <td>{$data['str_date_start']}</td>
                        <td>{$data['str_due_date']}</td>
                        <td>{$data['modulename']}</td>
                        <td>{$MOD[$data['eventstatus']]}</td>
                        <!-- {$data['eventstatus']} -->
                    </tr>
                {/foreach}
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/jquery.nicescroll.js"></script>
    <!-- <script type="text/javascript" src="modules/daily_report/daily_report.js"></script> -->
    <script type="text/javascript">
        jQuery(".daily-report-scroll").niceScroll();
    </script>
{/strip}