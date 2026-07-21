<link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
<link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css" />
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
    <div class="row">
        <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
            <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    </div>
{/if}
<form method="post" >
    <input type="hidden" name="module" value="grid_view"/>
    <input type="hidden" name="action" value="SaveRecordActivity"/>
    <input type="hidden" name="record" value="{$ID}"/>
    <input type="hidden" name="Ajax" value="true"/>
    <div class="row-grid-view justify-content-center">
        <div class="table-responsive col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <th class="text-center" style="width: 45%;">Actividad</th>
                    <th class="text-center" style="width: 25%;">Inicio</th>
                    <th class="text-center" style="width: 25%;">Fin</th>
                    <th class="text-center" style="width: 5%;">&nbsp;</th>
                </tr>
                </thead>
                <tbody id="activity-tbody">
                <tr id="activity-main-row" class="activity-row">
                    <td style="vertical-align: top;">
                        <input type="text" class="form-control border activity-name" name="name[]"
                        placeholder=" Nueva tarea..."  style="margin-bottom: 0.5em;" />
                        <textarea class="form-contro border l activity-comment" name="description[]" rows="1"
                                  placeholder=" Descripción" style="width: 100%"></textarea>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="input-group" style="margin-bottom: 0.5em;">
                            <span class="input-group-addon border "><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control border  activity-start-date" placeholder=""
                                   name="startdate[]"/>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon  border "><i class="fa fa-clock-o"></i></span>
                            <input type="time" class="form-control border  activity-start-time" placeholder="HH:MM:SS"
                                   name="starttime[]"/>

                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="input-group" style="margin-bottom: 0.5em;">
                            <span class="input-group-addon border "><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control  border activity-end-date" placeholder=""
                                   name="enddate[]"/>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon border "><i class="fa fa-clock-o"></i></span>
                            <input type="time" class="form-control border activity-end-time" placeholder="HH:MM:SS"
                                   name="endtime[]"/>

                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <button type="button" class="btn btn-danger hide" onclick="ActivityRecordUtils.deleteRow(this);"><i
                                    class="fa fa-trash-o"></i></button>
                    </td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="5" class="text-center">
                        <button type="button" class="btn btn-default"
                                onclick="ActivityRecordUtils.addRow(this);">
                            <i class="fa fa-plus"></i>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary pull-right">Guardar</button>
        </div>
    </div>
</form>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="modules/grid_view/activity-record-utils.js"></script>
