{strip}
    {math equation= rand() assign= "idDailyReportJobView"}
    <div class="row">
        {*$MODULES|var_dump*}
        <div class="col-md-12 col-lg-12 col-sm-12">
            <div class="form-group" style="margin-bottom: 0!important;">
                <label for="job-view">Trabajo:</label>
                <input type="text" class="form-control" id="job-view"
                       onkeyup="DailyReportUtils.searchJob(this, '{$idDailyReportJobView}')"
                       placeholder="Buscar por trabajo">
            </div>
        </div>
        <div class="col-md-12 col-lg-12 col-sm-12 daily-report-scroll" style="max-height: 300px; overflow-y: auto">
            <table class="table">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Trabajo</th>
                    <th>Tipo de actividad</th>
                    <th>Fecha de emision</th>
                    {* <th>Estado</th> *}
                </tr>
                </thead>
                <tbody id="job-view-{$idDailyReportJobView}">
                {*$JOBS_VIEW_DATA['tasks']|var_dump*}
                {if $JOBS_VIEW_DATA && $JOBS_VIEW_DATA['jobs']|@count > 0}
                    {foreach $JOBS_VIEW_DATA['jobs'] as $data}
                        <tr id="{$data['orden_de_trabajoid']}">
                            <td class="">
                                <a href="#"
                                   rel="{$data['orden_de_trabajoid']}@{$ROW_ID}"
                                   onclick="DailyReportUtils.setTasksBayJob(this, event)"
                                   data-display="{$data['titulo']}">
                                    {$data['cod_orden_de_tra']}
                                </a></td>
                            <td class="search-{$idDailyReportJobView}">{$data['titulo']}</td>
                            <td>{$data['tipo_dactividad']}</td>
                            <td>{$data['fecha_de_emision']}</td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">
                            <i class="fa fa-info-circle" style="font-size: 24px; color: #999; margin-bottom: 10px;"></i>
                            <p style="color: #666; margin: 0;">{$MOD.LBL_NO_JOBS_AVAILABLE}</p>
                        </td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/jquery.nicescroll.js"></script>
    <!-- <script type="text/javascript" src="modules/daily_report/daily_report.js"></script> -->
    {literal}
    <script type="text/javascript">
        jQuery(".daily-report-scroll").niceScroll();{/literal}
        {foreach $JOBS_VIEW_DATA['tasks'] as $crmid => $data}
        {literal}
        DailyReportUtils.taskByJob.set({/literal}{$crmid} {literal}, [{/literal}
        {foreach $data as $row}
            {$row}{literal},{/literal}
        {/foreach}
            {literal}]);{/literal}
        {/foreach}
        {literal}
        
        // Precargar tareas que ya tienen reportes para esta fecha
        {/literal}
        {if $JOBS_VIEW_DATA['preloaded'] && $JOBS_VIEW_DATA['preloaded']|@count > 0}
        {literal}
        var preloadedTasks = [
        {/literal}
        {foreach $JOBS_VIEW_DATA['preloaded'] as $task}
            {literal}{
                activityid: {/literal}{$task.activityid}{literal},
                subject: "{/literal}{$task.subject|escape:'javascript'}{literal}",
                jobid: {/literal}{$task.jobid}{literal},
                jobtitle: "{/literal}{$task.jobtitle|escape:'javascript'}{literal}"
            }{/literal}{if !$smarty.foreach.preload.last},{/if}
        {/foreach}
        {literal}
        ];
        
        // Disparar evento para que el daily_report.js procese las tareas precargadas
        jQuery(document).trigger('dailyReportPreloadTasks', [preloadedTasks, {/literal}'{$ROW_ID}'{literal}]);
        {/literal}
        {/if}
    </script>
{/strip}