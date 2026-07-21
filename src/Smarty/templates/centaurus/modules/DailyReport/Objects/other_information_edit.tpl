<tr style="vertical-align: top" id="tr-row-__ID__" data-row-id="{$idAchievements}" class="tabla-field-row">
    <td>
        <div class="input-group" style="width: 100%;">
            <select id="other_info_type-__ID__" name="other_information[other_info_type][]" class="form-control">
                <option value="">Seleccionar: Tipo de información</option>
                <option value="Noticias">Noticias</option>
                <option value="Problema" selected="">Problema</option>
                <option value="Sugerencias">Sugerencias</option>
            </select>
        </div>
    </td>
    <td>
        <div id="list-other_info_title-__ID__" class="input-group hide" style="width: 100%;"></div>
        <div id="input-other_info_title-__ID__" class="input-group" style="width: 100%;">
            <input type="text" id="other_info_title-__ID__" name="other_information[other_info_title][]" value=""
                class="form-control">
        </div>
    </td>
    <td>
        <div id="input-other_info_description-__ID__" class="input-group" style="width: 100%;">
            <textarea id="other_info_description-__ID__" name="other_information[other_info_description][]"
                class="form-control" rows="2"></textarea>
        </div>
    </td>
    <td class="text-center" style="width: 10%">
        <button type="button" class="btn btn-primary btn-xs"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            onclick="DailyReportUtils.moveRowUp (this, 'tr-row-__ID__')">
            <i class="fa fa-arrow-up" aria-hidden="true" style="font-size: 11px;"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            onclick="DailyReportUtils.moveRowDown (this, 'tr-row-__ID__')">
            <i class="fa fa-arrow-down" aria-hidden="true" style="font-size: 11px;"></i></button>
        <button type="button" class="btn btn-danger btn-xs delete-value-button"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            data-template="#tbody-other_information-" data-colspan="#other_information-colspan-template-"
            onclick="DailyReportUtils.delRowToTable (this, 'tr-row-__ID__', '{$idAchievements}');">
            <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i>
        </button>
    </td>
</tr>