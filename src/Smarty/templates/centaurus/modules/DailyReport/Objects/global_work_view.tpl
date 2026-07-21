<tr id="tr-row-{$idRow}" data-row-id="{$idRow}" class="tabla-field-row" style="vertical-align: top">
    <td style="width:28% ;vertical-align: top;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            <span id="orden_de_trabajoid" class="form-control" style="overflow-x: hidden;width: 100%">
                {$report["title"]}
            </span>
        </div>
    </td>
    <td style="width:10%;vertical-align: top">
        <div class="input-group" style="width: 100%;vertical-align: top;">
            <span id="estimated_time" class="form-control" style="overflow-x: hidden;width: 100%">
                 {$report['estimated_time']}
            </span>
        </div>
    </td>
    <td style="width=10%;vertical-align: top;">
        <div class="input-group" style="width: 100%;vertical-align: top;">
            <span id="advance_rate" class="form-control" style="overflow-x: hidden;width: 100%">
                {$report['overall_progress_perc']}
            </span>
        </div>
    </td>
    <td style="width=28%;vertical-align: top;">
        <div id="input-progress_report-{$idRow}" class="input-group" style="width: 100%;;vertical-align: top">
            <textarea id="progress_report-{$idRow}"
                      disabled class="form-control" rows="2">{$report['report']}</textarea>
        </div>
    </td>
    <td style="width:9%;vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <span id="time_used" class="form-control" style="overflow-x: hidden;width: 100%;vertical-align: top">
                {$report['duration_time']}
            </span>
        </div>
    </td>
    <td style="width:9%;vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <span id="actual_cost" class="form-control" style="overflow-x: hidden;width: 100%;vertical-align: top">
                {if isset($report['actual_cost'])}{$report['actual_cost']}{/if}
            </span>
        </div>
    </td>
    <td style="width:9%;vertical-align: top;">
        <div class="input-group" style="width: 100%;">
            <span id="total_progress" class="form-control" style="overflow-x: hidden;width: 100%;vertical-align: top">
                {$report['progress']}
            </span>
        </div>
    </td>
    <td style="width:5%;vertical-align: top;" class="text-center">
        {if $report['attachments'] neq NULL}
            <ul class="inline instance-list" style="list-style: none;text-align: center">
                {foreach $report['attachments'] as $attachment}
                    <li style="text-align: center">
                        <a href="{$attachment['uri']}" title="{$attachment['name']}" target="_blank">
                            <i class="fa {$attachment['type']}" style="color: #17a2b8;font-size:2em"></i>
                        </a>
                    </li>
                {/foreach}
            </ul>
        {/if}
    </td>
</tr>