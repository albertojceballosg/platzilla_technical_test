{if $WORKING_DAY neq NULL}
    {assign var='afternoonDueTime' value=$WORKING_DAY->getAfternoonDueTime ()}
    {assign var='afternoonStartTime' value=$WORKING_DAY->getAfternoonStartTime ()}
    {assign var='description' value=$WORKING_DAY->getDescription()}
    {assign var='workingdayid' value=$WORKING_DAY->getId()}
    {assign var='morningDueTime' value=$WORKING_DAY->getMorningDueTime()}
    {assign var='morningStartTime' value=$WORKING_DAY->getMorningStartTime()}
    {assign var='workingDayName' value=$WORKING_DAY->getWorkingDayName()}
    {assign var='regularWorkingHours' value=$WORKING_DAY->getRegularWorkingHours()}
    {assign var='workingDayStatus' value=$WORKING_DAY->getWorkingDayStatus()}
    {assign var="workingDaysOfWeek" value=$WORKING_DAY->getWorkingDaysOfWeek()}
    {assign var="idWorkingDay" value=$ID}
    {assign var="timeClass" value="timeEditClass"}
        <div class="row">
            <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-left: 25px">
                <label for="working_day_type" class="col-lg-5 col-md-5 control-label">Tipo de jornada de trabajo:</label>
                <div id="wd-div-working_day_type" class="col-lg-7 col-md-7">
                    <input type="text" class="form-control col-lg-7 col-md-7"
                           name="working_day_type"
                           title="Tipo de jornada de trabajo"
                           id="working_day_type"
                           value="{$workingDayName}"
                           placeholder="nombre de la jormada">
                    <span id="wd-working_day_type" class="help-block" style="color: red;"></span>
                </div>
            </div>
            <div class="form-group  col-lg-6 col-md-6 col-xs-6">
                <label for="regular_working_hours" class="col-lg-5 col-md-5 control-label">Jornada diaria (Hrs):</label>
                <div id="wd-div-regular_working_hours" class="col-lg-7 col-md-7">
                    <input type="text" class="form-control col-lg-7 col-md-7"
                           name="regular_working_hours"
                           title="Jornada diaria"
                           id="regular_working_hours"
                           value="{$regularWorkingHours}"
                           onkeydown="WorkingDayUtils.normalizeWorkingTime (this, event);"
                           placeholder="Horas">
                    <span id="wd-regular_working_hours" class="help-block" style="color: red;"></span>
                </div>
            </div>
            {* </div>
             <div class="row"> *}
            <div class="col-lg-12 col-md-12 col-xs-12" style="margin-bottom: 12px">
                <label for="ejemplo_email_3" class="col-lg-3 col-md-3 control-label">Descripción de jornada de trabajo:</label>
                <div class="col-lg-9 col-md-9">
                    <textarea name="description_working_day"
                              id="description_working_day"
                              class="form-control"
                              placeholder="Descripción breve del tipo de jornada" rows="3">{$description}</textarea>
                </div>
            </div>
            <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-left: 25px">
                <label for="working_day_type" class="col-lg-5 col-md-5 control-label">Estado de la jornada de  trabajo:</label>
                <div id="wd-div-working_day_status" class="col-lg-7 col-md-7">
                    <select  name="working_day_status"
                             title="Estado de jornada de trabajo"
                             id="working_day_status" class="form-control col-lg-7 col-md-7">
                        {if $WORKING_DAY_STATUS neq NULL}
                            {foreach $WORKING_DAY_STATUS as $status => $key}
                                <option value="{$status}" {if $workingDayStatus eq $status}selected="selected"{/if}>{$key}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <span id="wd-working_day_status" class="help-block" style="color: red;"></span>
                </div>
            </div>
            <div class="form-group  col-lg-6 col-md-6 col-xs-6">&nbsp;</div>
            <div class="col-lg-12 col-md-12 col-xs-12 pull-left" style="margin-bottom: 12px">Horario regular de la jornada (Horas)</div>
        </div>
        <div class="row">
            <div class="form-group col-lg-3 col-md-3 col-xs-3" >
                <label for="regular_hours_day_gai" class="col-lg-5 col-md-5 pull-roght control-label">Inicio:</label>
                <div id="wd-div-regular_hours_day_gai" class="col-lg-7 col-md-7 bootstrap-timepicker">
                    <input type="text" class="form-control wd-timepicker" id="regular_hours_day_gai"
                           title="Horario regular de la jornada"
                           name="regular_hours_day[0]"
                           value="{$morningStartTime}"
                           placeholder="Hora inicio">
                    <span id="wd-regular_hours_day_gai" class="help-block" style="color: red;"></span>
                </div>
            </div>
            <div class="form-group  col-lg-3 col-md-3 col-xs-3">
                <label for="regular_hours_day_gaf" class="col-lg-5 col-md-5  pull-roght control-label">Fin:</label>
                <div id="wd-div-regular_hours_day_gaf" class="col-lg-7 col-md-7 bootstrap-timepicker">
                    <input type="text" class="form-control wd-timepicker"
                           title="Horario regular de la jornada"
                           name="regular_hours_day[1]"
                           value="{$morningDueTime}"
                           id="regular_hours_day_gaf"
                           placeholder="Horas">
                    <span id="wd-regular_hours_day_gaf" class="help-block" style="color: red;"></span>
                </div>
            </div>
            <div class="form-group  col-lg-3 col-md-3 col-xs-3">
                <label for="regular_hours_day_gbi" class="col-lg-5 col-md-5  pull-roght control-label">Incio:</label>
                <div id="wd-div-regular_hours_day_gbi" class="col-lg-7 col-md-7 bootstrap-timepicker">
                    <input type="text" class="form-control wd-timepicker"
                           name="regular_hours_day[2]"
                           title="Horario regular de la jornada"
                           id="regular_hours_day_gbi"
                           value="{$afternoonStartTime}"
                           placeholder="Horas">
                    <span id="wd-regular_hours_day_gbi" class="help-block" style="color: red;"></span>
                </div>
            </div>
            <div class="form-group  col-lg-3 col-md-3 col-xs-3">
                <label for="regular_hours_day_gbf" class="col-lg-5 col-md-5  pull-roght  control-label">Fin:</label>
                <div id="wd-div-regular_hours_day_gbf"  class="col-lg-7 col-md-7 bootstrap-timepicker">
                    <input type="text" class="form-control wd-timepicker"
                           name="regular_hours_day[3]"
                           title="Horario regular de la jornada"
                           value="{$afternoonDueTime}"
                           id="regular_hours_day_gbf"
                           placeholder="Horas">
                    <span id="wd-regular_hours_day_gbf" class="help-block" style="color: red;"></span>
                </div>
            </div>
        </div>
        <header class="main-box-header clearfix" style="margin-top: 12px;padding-left: 0!important;">
            <p class="text-center pull-left" style="font-weight: bold">Distribución del tiempo en la semana</p>
            {* <h1 class="pull-left" style="padding-left: 0!important;">Distribución del tiempo en la semana</h1> *}
        </header>

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
                <th aria-controls="table_list">&nbsp;</th>
            </tr>
            </thead>
            <tbody id="working-days-table-{$idWorkingDay}">
            {if $workingDaysOfWeek neq NULL}
                {foreach $workingDaysOfWeek as $day}
                    {include file='Home/TabsContents/WorkingDaysTableEditView.tpl'}
                {/foreach}
            {/if}
            {if $DAYS_WEEK neq NULL}
                {foreach $DAYS_WEEK as $day}
                    {include file='Home/TabsContents/WorkingDaysTable_template.tpl'}
                {/foreach}
            {/if}
            </tbody>
        </table>
{else}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>¡Error!</strong> El tipo de jornada laboral seleccionado no contiene información o ha sido eliminado
            </div>
        </div>
    </div>
{/if}