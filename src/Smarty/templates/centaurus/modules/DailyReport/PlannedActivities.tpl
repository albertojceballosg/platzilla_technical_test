{math equation= rand() assign= "idDailyReport"}
{if $DAILY_REPORTS neq NULL}
    {assign var="dailyReportPlanned" value=$DAILY_REPORTS}
    {assign var="totalPlanned" value=count($dailyReportPlanned)}
    {assign var="totalTime" value=null}
    {assign var="totalTimeUnplanned" value=null}
{else}
    {assign var="dailyReportPlanned" value=null}
    {assign var="totalPlanned" value=null}
    {assign var="totalTimeUnplanned" value=null}
{/if}
<link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
<div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 20px"{/if}>
    <div class="table-responsive">
        {if $HAS_PLANNED_TASK || ($VIEW eq NULL)}
            <table id="planned_activities-table-{$idDailyReport}" class="table table-bordered tablegridvalidate">
                <thead>
                <tr>
                    <td colspan="5" style="text-align: left; background-color:#f9f8f7"><strong>Tareas planeadas</strong></td>
                </tr>
                <tr valign="top">
                    <td style="" width="33%"><span style="">Tarea</span></td>
                    <td style="" width="10%"><span style="">(%) Avance de la tarea</span></td>
                    <td style="" width="35%"><span style="">Reporte de avance</span></td>
                    <td style="" width="10%"><span style="">(hrs) Tiempo empleado</span></td>
                    <td class="text-center" {if $VIEW eq NULL}width="12%"{/if}>{if $VIEW eq NULL}Acciones{else}&nbsp;{/if}</td>
                </tr>
                </thead>
                <tbody id="tbody-daily-report-{$idDailyReport}" rowtotal="0">
                {if $dailyReportPlanned neq NULL}
                {foreach $dailyReportPlanned as $key => $plannedTask}
                    {math equation= rand() assign= "idRow"}
                    {if $VIEW eq NULL}
                        {include file='modules/DailyReport/planned_activities_edit_template.tpl'}
                    {else}
                        {if $plannedTask->getActivity()->getActivityCondition() neq 'PLANNED_AND_RECORDED'}
                            {continue}
                        {/if}
                        {include file='modules/DailyReport/planned_activities_detailview_template.tpl'}
                        {assign var="totalTime" value=($totalTime +  $plannedTask->getDurationTime())}
                    {/if}
                {/foreach}
                {else}
                <tr>
                    <td colspan="5" style="text-align: center"></td>
                </tr>
                {/if}
                </tbody>
                <tfoot id="tfoot-{$idDailyReport}" data-field-name="planned_activities" data-summary-row=""
                       data-operation-row="">
                <tr id="summary-row-{$idDailyReport}" valign="top">

                    <td colspan="3"><p style="text-align: right">Total (Horas):&nbsp;</p></td>
                    <td id="td-time_reported-{$idDailyReport}">
                        {if $VIEW eq NULL}
                        <input type="text" id="total_time_reported-{$idDailyReport}"
                               name="planned_activities[summaryRow][]" rel="SUM_COLUMN" value="0.00"
                               class="form-control" readonly="">
                         {else}
                            <div class="input-group text-right" style="width: 100%;">
                        <span id="input-total_time_reported-{$idDailyReport}">
                            {if $totalTime neq NULL}{$totalTime}{/if}
                        </span>
                            </div>
                        {/if}
                    </td>
                    <td class="text-center">&nbsp;</td>
                </tr>
                {if $VIEW eq NULL}
                <tr>
                    <td colspan="5" class="text-center">
                        <button type="button" data-id-linkage="{$idDailyReport}" class="btn btn-primary"
                                data-sequence="{$totalPlanned}"
                                data-template="planned_activities-template-{$idDailyReport}"
                                onclick="DailyReportUtils.addRowToTable (this, 'tbody-daily-report-{$idDailyReport}', '{$idDailyReport}');">
                            <i class="fa fa-plus"></i></button>

                    </td>
                </tr>
                {/if}
                </tfoot>
            </table>

            {if $VIEW eq NULL}
            <script type="text/html" id="planned_activities-template-{$idDailyReport}">
                {include file='modules/DailyReport/planned_activities_template.tpl'}
            </script>
            <script type="text/html" id="tbody-daily-report-{$idDailyReport}-template">
                <tr>
                    <td colspan="5" style="text-align: center"></td>
                </tr>
            </script>
            {/if}
        {/if}
        {if $HAS_UNPLANNED_TASK || ($VIEW eq NULL)}
            {math equation= rand() assign= "idDailyReport"}
            {* planned_unregistered-table *}
            <table id="planned_unregistered-table" class="table table-bordered tablegridvalidate">
                <thead>
                <tr>
                    <td colspan="9"  style="text-align: left; background-color:#f9f8f7"><strong>Tareas no registradas</strong></td>
                </tr>
                <tr valign="top">
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="9%"><span style="">Situación</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="17%"><span style="">Tarea</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="8%"><span style="">Tiempo estimado</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="8%"><span style="">Importancia</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="8%"><span style="">Prioridad</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="8%"><span style="">(%) avance</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" {if $VIEW eq NULL}width="22%"{else}width="22%"{/if}><span style="">Reporte de avance</span></td>
                    <td style="{* color:#3498DB;*}vertical-align:middle;" width="8%"><span style="">(hrs) Tiempo empleado</span></td>
                    <td class="text-center" {if $VIEW eq NULL}width="12%"{/if}>{if $VIEW eq NULL}Acciones{else}&nbsp;{/if}</td>
                </tr>
                </thead>
                <tbody id="tbody-daily-report-{$idDailyReport}" rowtotal="0">
                {if ($dailyReportPlanned neq NULL) && ($VIEW eq 'DetailView')}
                {foreach $dailyReportPlanned as $key => $plannedTask}
                    {math equation= rand() assign= "idRow"}
                        {if $plannedTask->getActivity()->getActivityCondition() eq 'PLANNED_AND_RECORDED'}
                            {continue}
                        {/if}
                        {include file='modules/DailyReport/planned_unregistered_detailview_template.tpl'}
                        {assign var="totalTimeUnplanned" value=($totalTimeUnplanned +  $plannedTask->getDurationTime())}

                {/foreach}
                {else}
                <tr>
                    <td colspan="9" style="text-align: center"></td>
                </tr>
                {/if}
                </tbody>
                <tfoot id="tfoot-{$idDailyReport}" data-field-name="planned_unregistered-table"
                       data-summary-row=""
                       data-operation-row="">
                <tr id="summary-row-{$idDailyReport}" valign="top">
                    <td colspan="7"><p style="text-align: right">Total (Horas):&nbsp;</p></td>
                    <td id="td-time_reported-{$idDailyReport}">
                        {if $VIEW eq NULL}
                        <input type="text" id="total_time_reported-{$idDailyReport}"
                               name="planned_unregistered[summaryRow][]" rel="SUM_COLUMN" value="0.00"
                               class="form-control" readonly="">
                        {else}
                            <div class="input-group text-right" style="width: 100%;">
                        <span id="input-total_time_reported-{$idDailyReport}">
                            {if $totalTimeUnplanned neq NULL}{$totalTimeUnplanned}{/if}
                        </span>
                            </div>
                        {/if}
                    </td>
                    <td class="text-center">&nbsp;</td>
                </tr>
                {if $VIEW eq NULL}
                <tr>
                    <td colspan="9" class="text-center">

                        <button type="button" data-id-linkage="{$idDailyReport}" class="btn btn-primary"
                                data-sequence ="0"
                                data-template ="planned_unregistered-template-{$idDailyReport}"
                                onclick="DailyReportUtils.addRowToTable (this, 'tbody-daily-report-{$idDailyReport}', '{$idDailyReport}');">
                                <i class="fa fa-plus"></i>
                        </button>
                    </td>
                </tr>
                {/if}
                </tfoot>
            </table>
        {/if}
    </div>
</div>
{if $VIEW eq NULL}
<script type="text/html" id="planned_unregistered-template-{$idDailyReport}">
    {include file='modules/DailyReport/planned_unregistered_template.tpl'}
</script>
<script type="text/html" id="tbody-daily-report-{$idDailyReport}-template">
    <tr>
        <td colspan="9" style="text-align: center"></td>
    </tr>
</script>
{/if}
<script type="text/javascript" src="themes/centaurus/js/jquery.nicescroll.js"></script>
<script type="text/javascript">
    jQuery(".daily-report-scroll").niceScroll();
</script>
{if $VIEW eq NULL}
<script src="https://cdn.tiny.cloud/1/890v9nqmb6w8aw4ibgargqluwbbu1lj05sfyklk6rqef1idd/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
{/if}