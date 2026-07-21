{if $USER_WORKING_DAY neq NULL}
    {math equation= rand() assign= "idWorkingDay"}
    <div class="row">
        <div class="form-group  col-lg-6 col-md-6 col-xs-6">
            <label for="regular_working_hours" class="col-lg-5 col-md-5 control-label">Jornada diaria (Hrs):</label>
            <div id="wd-div-regular_working_hours" class="col-lg-7 col-md-7">
               <span class="form-control"
                     style="overflow-x: hidden;width: 100%;">
                   {$USER_WORKING_DAY->getRegularWorkingHours()}
               </span>
            </div>
        </div>
        <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-bottom: 0!important;">
            <label for="ejemplo_email_3" class="col-lg-5 col-md-5 control-label">Descripción de jornada de
                trabajo:</label>
            <div class="col-lg-7 col-md-7">
               <span id="dtlview_{$label}"
                     class="form-control"
                     style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;
                     {if ($USER_WORKING_DAY->getDescription ()|strlen) gt 51}
                             min-height: 70px;
                     {else}
                             min-height: 50px;
                     {/if}line-height: 1.35em !important;">
                   {$USER_WORKING_DAY->getDescription ()}
               </span>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-xs-12 pull-left" style="margin-bottom: 12px">
            Horario regular de la jornada (Horas)
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-3 col-md-3 col-xs-3">
            <label for="regular_hours_day_gai" class="col-lg-4 col-md-4 pull-roght control-label">Inicio:</label>
            <div id="wd-div-regular_hours_day_gai" class="col-lg-8 col-md-8 bootstrap-timepicker">
                <span class="form-control"
                      style="overflow-x: hidden;width: 100%;">
                   {$USER_WORKING_DAY->getMorningStartTime()}
               </span>
            </div>
        </div>
        <div class="form-group  col-lg-3 col-md-3 col-xs-3">
            <label for="regular_hours_day_gaf" class="col-lg-4 col-md-4  pull-roght control-label">Fin:</label>
            <div id="wd-div-regular_hours_day_gaf" class="col-lg-8 col-md-8 bootstrap-timepicker">
                <span class="form-control"
                      style="overflow-x: hidden;width: 100%;">
                   {$USER_WORKING_DAY->getMorningDueTime()}
               </span>
            </div>
        </div>
        <div class="form-group  col-lg-3 col-md-3 col-xs-3">
            <label for="regular_hours_day_gbi" class="col-lg-4 col-md-4  pull-roght control-label">Incio:</label>
            <div id="wd-div-regular_hours_day_gbi" class="col-lg-8 col-md-8 bootstrap-timepicker">
                <span class="form-control"
                      style="overflow-x: hidden;width: 100%;">
                   {$USER_WORKING_DAY->getAfternoonStartTime()}
               </span>
            </div>
        </div>
        <div class="form-group  col-lg-3 col-md-3 col-xs-3">
            <label for="regular_hours_day_gbf" class="col-lg-4 col-md-4  pull-roght  control-label">Fin:</label>
            <div id="wd-div-regular_hours_day_gbf" class="col-lg-8 col-md-8 bootstrap-timepicker">
                <span class="form-control"
                      style="overflow-x: hidden;width: 100%;">
                   {$USER_WORKING_DAY->getAfternoonDueTime()}
               </span>
            </div>
        </div>
    </div>
    <header class="main-box-header clearfix" style="margin-top: 12px;padding-left: 0!important;">
        <p class="text-center pull-left" style="font-weight: bold">Distribución del tiempo en la semana</p>
        {* <h1 class="pull-left" style="padding-left: 0!important;">Distribución del tiempo en la semana</h1> *}
    </header>
    <div class="">
        <table class="table table-hover dataTable no-footer" width="100%" cellspacing="0"
               cellpadding="0" border="0">
            <thead>
            <tr>
                <th aria-controls="table_list">Día de la Semaba</th>
                <th aria-controls="table_list">Horas de la Jornada</th>
                <th aria-controls="table_list">Hora inicio</th>
                <th aria-controls="table_list">Hora fin</th>
                <th aria-controls="table_list">Hora inicio</th>
                <th aria-controls="table_list">Hora fin</th>
            </tr>
            </thead>
            <tbody id="working-days-table-{$idWorkingDay}">
            {if $USER_WORKING_DAY->getWorkingDaysOfWeek() neq NULL}
                {foreach $USER_WORKING_DAY->getWorkingDaysOfWeek() as $dayOfWeek}
                    {include file='Home/TabsContents/WDTableDetailView_template.tpl'}
                {/foreach}
            {/if}
            </tbody>
        </table>
    </div>
{else}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>¡Error!</strong> El tipo de jornada laboral seleccionado no contiene información
            </div>
        </div>
    </div>
{/if}