<tr id="tr-row-__ID__" data-row-id="__ID__" class="tabla-field-row" valign="top">
    <td width="9%"  style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="reported_task_condition-__ID__"
                    name="planned_unregistered[reported_task_condition][]"
                    onchange="DailyReportUtils.setUnregisteredActivity(this, '__ID__')"
                    class="form-control">
                <option value="">Situación</option>
                <option value="PLANNED_UNREGISTERED">Planeada no registrada</option>
                <option value="UNEXPECTED">Imprevista</option>
            </select>
        </div>
    </td>
    <td width="17%"  style="vertical-align: top">
        <div id="list-reported_task_title-__ID__" class="input-group hide" style="width: 100%;"></div>
        <div id="input-reported_task_title-__ID__" class="input-group" style="width: 100%;">
            <input type="text" id="reported_task_title-__ID__"
                   placeholder="Tarea"
                   name="planned_unregistered[reported_task][]" value="" class="form-control">
        </div>
        {if $AVAILABLE_MODULES neq NULL}
            <div class="has-error">
                <select id="reported_task_module-__ID__" style="margin-top: 2px"
                        data-current-module="daily_report"
                        data-field-id="module_related_record__ID__"
                        data-referenced-module="" data-title=""
                        onchange="DailyReportUtils.setModuleActivity(this, '__ID__')"
                        class="form-control hide">
                    <option value="">Modulo asociado</option>
                    {foreach $AVAILABLE_MODULES as $avaModule}
                        <option value="{$avaModule['value']}">{$avaModule['label']}</option>
                    {/foreach}
                </select>
            </div>
            <input type="hidden" id="module_related_record__ID__"
                   name="planned_unregistered[relatedcrmids][]"
                   value=""
                   class="for-filter module-reference">
        {/if}
    </td>
    <td width="8%"  style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="estimated_time_task-__ID__"
                   placeholder="Horas"
                   name="planned_unregistered[estimated_time_task][]" value="" class="form-control"
                   onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    <td width="8%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="task_importance-__ID__" name="planned_unregistered[task_importance][]"
                    class="form-control">
                <option value="">Seleccionar: Importancia</option>
                <option value="HIGH">Alta</option>
                <option value="LOW">Baja</option>

            </select>
        </div>
    </td>
    <td width="8%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <select id="task_priority-__ID__" name="planned_unregistered[task_priority][]"
                    class="form-control">
                <option value="">Seleccionar: Prioridad</option>
                <option value="Alto">Alto</option>
                <option value="Bajo">Bajo</option>

            </select>
        </div>
    </td>
    <td width="8%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="pc_task_advanced-__ID__"
                   placeholder="% de avance"
                   name="planned_unregistered[pc_task_advanced][]" value="" class="form-control"
                   onkeyup="DailyReportUtils.updateNumFields(this, '')">
        </div>
    </td>
    <td width="23%" style="vertical-align: top">
        <div id="input-task_advanced_report-__ID__" class="input-group" style="width: 100%;">
            <textarea id="task_advanced_report-__ID__" name="planned_unregistered[task_advanced_report][]"
                      class="form-control" rows="2">
            </textarea>
        </div>
    </td>
    <td width="8%" style="vertical-align: top">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="time_reported-__ID__" name="planned_unregistered[time_reported][]"
                   placeholder="Horas"
                   value="" class="form-control total-time"
                   onkeyup="DailyReportUtils.updateNumFields(this, '{$idDailyReport}')">
        </div>
    </td>
    <td class="text-center" width="12%" style="vertical-align: top">
        <button type="button" class="btn btn-primary btn-xs"
                onclick="DailyReportUtils.moveRowUp (this, 'tr-row-__ID__')"><i class="fa fa-arrow-up"
                                                                                   aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
                onclick="DailyReportUtils.moveRowDown (this, 'tr-row-__ID__')"><i
                    class="fa fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
                onclick="DailyReportUtils.delRowToTable (this, 'tr-row-__ID__', '{$idDailyReport}');"><i
                    class="fa fa-trash-o"></i></button>
    </td>
</tr>