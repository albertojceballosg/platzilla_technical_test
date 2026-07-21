<tr style="vertical-align: top" id="tr-row-{$idRow}" data-row-id="{$idAchievements}" class="tabla-field-row">
    <td>
        <div class="input-group" style="width: 100%;">
            <select id="other_info_type-{$idRow}" name="other_information[other_info_type][]" class="form-control">
                <option value="">Seleccionar: Tipo de información</option>
                <option value="Noticias" {if $otherInformation['other_info_type'] eq 'Noticias'}selected{/if}>Noticias
                </option>
                <option value="Problema" {if $otherInformation['other_info_type'] eq 'Problema'}selected{/if}>Problema
                </option>
                <option value="Sugerencias" {if $otherInformation['other_info_type'] eq 'Sugerencias'}selected{/if}>
                    Sugerencias</option>
            </select>
        </div>
    </td>
    <td>
        <div id="list-other_info_title-{$idRow}" class="input-group hide" style="width: 100%;"></div>
        <div id="input-other_info_title-{$idRow}" class="input-group" style="width: 100%;">
            <input type="text" id="other_info_title-{$idRow}" name="other_information[other_info_title][]"
                value="{$otherInformation['other_info_title']}" class="form-control">
        </div>
    </td>
    <td>
        <div id="input-other_info_description-{$idRow}" class="input-group" style="width: 100%;">
            <textarea id="other_info_description-{$idRow}" name="other_information[other_info_description][]"
                class="form-control" rows="2">{$otherInformation['other_info_description']}</textarea>
        </div>
    </td>
    <td class="text-center" style="width: 10%">
        <button type="button" class="btn btn-primary btn-xs"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            onclick="DailyReportUtils.moveRowUp (this, 'tr-row-{$idRow}')">
            <i class="fa fa-arrow-up" aria-hidden="true" style="font-size: 11px;"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            onclick="DailyReportUtils.moveRowDown (this, 'tr-row-{$idRow}')">
            <i class="fa fa-arrow-down" aria-hidden="true" style="font-size: 11px;"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs delete-value-button"
            style="font-size: 11px !important; padding: 3px 7px !important; margin: 1px;"
            data-template="#tbody-other_information-" data-colspan="#other_information-colspan-template-"
            onclick="DailyReportUtils.delRowToTable (this, 'tr-row-{$idRow}', '{$idAchievements}');">
            <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 11px;"></i>
        </button>
    </td>
</tr>