<tr id="tr-row-{$idProgressJob}" data-row-id="{$idProgressJob}" class="tabla-field-row" valign="top">
    <td style="width:28% ;vertical-align: top;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            {if $UNFINISHED_JOBS neq NULL}
                {foreach $UNFINISHED_JOBS as $unfinishedJob}
                    {if $unfinishedJob["orden_de_trabajoid"] eq $report['work']['orden_de_trabajoid']}
                        <span id="orden_de_trabajoid" class="form-control" style="overflow-x: hidden;width: 100%">
                            {$unfinishedJob["cod_orden_de_tra"]}: {$unfinishedJob["titulo"]}
                        </span>
                    {/if}
                {/foreach}
            {else}
                <span id="orden_de_trabajoid" class="form-control" style="overflow-x: hidden;width: 100%">
                    No hay trabajos
                </span>
            {/if}
        </div>
    </td>
    <td style="width:10%;vertical-align: top" >
        <div class="input-group" style="width: 100%;vertical-align: top;">
            <span id="estimated_time" class="form-control" style="overflow-x: hidden;width: 100%">
                 {$report['work']['estimated_time']}
            </span>
        </div>
    </td>
    <td style="width=10%;vertical-align: top;" >
        <div class="input-group" style="width: 100%;vertical-align: top;">
            <span id="advance_rate" class="form-control" style="overflow-x: hidden;width: 100%">
                {$report['work']['progress_perc']}
            </span>
        </div>
    </td>
    <td style="width=28%;vertical-align: top;">
        <div id="input-progress_report-{$idProgressJob}" class="input-group" style="width: 100%;;vertical-align: top">
            <textarea id="progress_report-{$idProgressJob}"
                      disabled class="form-control" rows="2">{$report['report']->getReport()}</textarea>
        </div>
    </td>
    <td style="width:10%;vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <span id="time_used" class="form-control" style="overflow-x: hidden;width: 100%;vertical-align: top">
                {$report['report']->getTimeDuration()}
            </span>
        </div>
    </td>
    <td style="width:10%;vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <span id="total_progress" class="form-control" style="overflow-x: hidden;width: 100%;vertical-align: top">
                {$report['report']->getProgress()}
            </span>
        </div>
    </td>
    <td style="width:4%;vertical-align: top;" class="text-center" >&nbsp;</td>
</tr>