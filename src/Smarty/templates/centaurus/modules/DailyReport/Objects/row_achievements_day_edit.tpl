<tr id="tr-row{$idRow}" data-row-id="21770" class="tabla-field-row" style="vertical-align: top">
    <td>
        <div id="list-achievement_name{$idRow}" class="input-group hide" style="width: 100%;"></div>
        <div id="input-achievement_name{$idRow}" class="input-group" style="width: 100%;">
            <input type="text" id="achievement_name{$idRow}" name="achievements_day[achievement_name][]"
                value="{$achievements_day['achievement_name']}" class="form-control">
        </div>
    </td>
    <td>

        <div id="input-achievement_description{$idRow}" class="input-group" style="width: 100%;">
            <textarea id="achievement_description{$idRow}" name="achievements_day[achievement_description][]"
                class="form-control" rows="2">{$achievements_day['achievement_description']}</textarea>
        </div>
    </td>

    <td width="10%" class="text-center">
        <button type="button" class="btn btn-primary btn-xs"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            onclick="DailyReportUtils.moveRowUp (this, 'tr-row{$idRow}')">
            <i class="fa fa-arrow-up" aria-hidden="true" style="font-size: 11px;"></i></button>
        <button type="button" class="btn btn-danger btn-xs"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            onclick="DailyReportUtils.moveRowDown (this, 'tr-row{$idRow}')">
            <i class="fa fa-arrow-down" aria-hidden="true" style="font-size: 11px;"></i></button>
        <button type="button" class="btn btn-danger btn-xs delete-value-button"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            data-template="#tbody-achievements_day-" data-colspan="#chievements_day-colspan-template-"
            onclick="DailyReportUtils.delRowToTable (this, 'tr-row{$idRow}', '{$idAchievements}');">
            <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i>
        </button>
    </td>
</tr>