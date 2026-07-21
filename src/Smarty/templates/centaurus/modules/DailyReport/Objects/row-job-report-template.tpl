<tr id="tr-row-__ID__" data-row-id="__ID__" class="tabla-field-row" valign="top">
    <td style="vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <select id="reported_work-__ID__" name="report_job[reported_work][]"
                onchange="DailyReportUtils.getUnfinishedJob (this, '__ID__', '{$idProgressJob}')" class="form-control">
                {if $UNFINISHED_JOBS neq NULL}
                    <option value="" data-job="0.00@0.00">Seleccione trabajo</option>
                    {foreach $UNFINISHED_JOBS as $unfinishedJob}
                        <option value="{$unfinishedJob["orden_de_trabajoid"]}"
                            data-job="{$unfinishedJob["estimated_time"]}@{$unfinishedJob["progress_perc"]}">
                            {$unfinishedJob["cod_orden_de_tra"]}
                            : {$unfinishedJob["titulo"]}</option>
                    {/foreach}
                {else}
                    <option value="">No hay trabajos</option>
                {/if}
            </select>
            <input type="hidden" id="global_record-__ID__" value="">
            <input type="hidden" id="global_module-__ID__" value="orden_de_trabajo">
        </div>
    </td>
    <td style="vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="estimated_time-__ID__" placeholder="Horas" readonly
                name="report_job[estimated_time][]" value="" class="form-control estimated_time"
                onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    <td style="vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="advance_rate-__ID__" placeholder="% de avance" readonly
                name="report_job[advance_rate][]" value="" class="form-control"
                onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    <td style="vertical-align: top;">
        <div id="input-progress_report-__ID__" class="input-group" style="width: 100%;">
            <textarea id="progress_report-__ID__" name="report_job[progress_report][]" class="form-control"
                rows="2"></textarea>
        </div>
        <a href="javascript:void(0);"
            onclick="DailyReportUtils.uploadTaskEvidence(this, '__ID__', 'reported_work-__ID__')"
            style="color: #28a745; font-size: 11px; display: block; margin-top: 3px;">
            <i class="fa fa-plus" aria-hidden="true"></i> Cargar evidencias
        </a>
    </td>
    <td style="vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="time_used-__ID__" placeholder="Horas" name="report_job[time_used][]" value=""
                class="form-control time_used" onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    <td style="vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="total_progress-__ID__" placeholder="% de avance" name="report_job[total_progress][]"
                value="" class="form-control" onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    <td style="vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="actual_cost-__ID__" placeholder="Costo" name="report_job[actual_cost][]" value=""
                class="form-control numericvalidate"
                onkeyup="DailyReportUtils.updateNumFields(this, '{$idProgressJob}')">
        </div>
    </td>
    <td class="text-center" style="vertical-align: top;text-align: center">
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-primary btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowUp (this, 'tr-row-__ID__')"><i class="fa fa-arrow-up"
                    aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                onclick="DailyReportUtils.moveRowDown (this, 'tr-row-__ID__')"><i class="fa fa-arrow-down"
                    aria-hidden="true" style="font-size: 11px;"></i>
            </button>
        </div>
        <div class="btn-group" style="margin-top: 1px">
            <button type="button" class="btn btn-warning btn-xs"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-module="global_module-__ID__" data-id="global_record-__ID__"
                onclick="DailyReportUtils.uploadDoc (this, 'tr-row-__ID__')">
                <i class="fa fa-upload" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn btn-danger btn-icon delete-value-button" aria-label="Delete"
                style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
                data-template="#tbody-job-report-" data-colspan="#tbody-job-report-colspan-template-"
                onclick="DailyReportUtils.delRowToTable (this, 'tr-row-__ID__', '{$idProgressJob}');">
                <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i>
            </button>
        </div>
    </td>
</tr>