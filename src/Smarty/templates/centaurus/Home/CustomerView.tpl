{strip}
    <link rel="stylesheet" type="text/css" href="modules/Home/subscriptions.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-toggle.min.css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css" />
    <style type="text/css">
        {literal}
        label {
            font-size: 1.11em;
            font-weight: 300;
        }

        .main-box {
            margin-top: 20px;
        }

        .main-box > .main-box-body {
            padding-top: 20px;
        }

        .btn.btn-icon {
            font-size: 14px;
            height: 27px;
            line-height: 27px;
            margin: 0 5px 0 0;
            padding: 0;
            text-align: center;
            width: 27px;
        }
        .lesson-toggle {
            cursor: pointer;
        }
        {/literal}
    </style>
    <div class="col-lg-12">
        <div class="main-box clearfix no-header" style="margin-top: 0;">
            <div class="main-box-body clearfix" style="padding-top: 0;">
                {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
                    <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                        <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                    </div>
                {/if}
                <div class="tabs-wrapper">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#user-profile" data-toggle="tab">{$APP.LBL_INFORMATION_USER}</a></li>
                        <li class=""><a href="#user-usage-history" data-toggle="tab">{$APP.LBL_USAGE_HISTORY}</a></li>
                        <li class=""><a href="#user-doc-donwload" data-toggle="tab">{$APP.LBL_DOCUMENT_DONWLOAD}</a></li>
                        <li class=""><a href="#user-collaborator-data" data-toggle="tab">{$APP.LBL_COLLABORATOR_DATA}</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="user-profile">
                            {include file='Home/CustomerViewUserProfile.tpl'}
                        </div>
                        {* Historial de uso  *}
                        <div class="tab-pane fade" id="user-usage-history" style="margin-top: 12px">
                            <div class="tabs-wrapper">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#usage-history-course" data-toggle="tab">Course</a></li>
                                    <li class=""><a href="#usage-history-system" data-toggle="tab">Uso del sistema</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="usage-history-course">
                                        {* Courses *}
                                        <div class="main-box-body clearfix">
                                            <h2 class="text-center" style="font-weight: bold; margin: 1em auto;">
                                                Formación
                                            </h2>
                                            <div class="row">
                                                <div class="col-xs-12 col-md-12 col-lg-12">
                                                    {if $COURSE_SEEN neq NULL}
                                                        <table class="table no-footer" width="100%"
                                                        {foreach $COURSE_SEEN as $course}
                                                            {if $course->getCourseSeen () eq NULL}{continue}{/if}
                                                            {assign var='courseId' value=$course->getCourseSeen ()->getId ()}
                                                            {assign var='courseLessons' value=$course->getLessons ()}
                                                            {assign var='courseName' value=$course->getCourseSeen ()->getName ()}
                                                            {assign var='courseImage' value=$course->getCourseSeen ()->getImageCourse ()}
                                                            {assign var='courseImageType' value=$course->getCourseSeen ()->getImageType ()}
                                                            {assign var="courseDateStar"  value=$course->getSeenDate ()}
                                                            {assign var="courseLastTime"  value=$course->getLastTime ()}
                                                            <tr>
                                                                <td>
                                                                    <table class="table table-borderless">
                                                                        <thead>
                                                                        <tr>
                                                                            <th scope="col"></th>
                                                                            <th scope="col">Curso</th>
                                                                            <th scope="col">Fecha inicio</th>
                                                                            <th scope="col">Visto hace</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        <tr>
                                                                            <td style=";padding: 0;margin: 0; width: 10%">
                                                                                {if !empty ($courseImage)}
                                                                                    <img id="course-photo-{$courseId}"
                                                                                         src="data:{$courseImageType}; base64, {$courseImage}"
                                                                                         class="center-block  img-responsive">
                                                                                {else}
                                                                                    <img class="img-responsive"
                                                                                         src='data:image/png;base64,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'/>
                                                                                {/if}
                                                                            </td>
                                                                            <td>
                                                                                <a href="index.php?module=Courses&action=CourseView&record={$courseId}"
                                                                                   style="text-align: left">{$courseName}</a>
                                                                            </td>
                                                                            <td>{$courseDateStar}</td>
                                                                            <td>{$courseLastTime}</td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    {if !empty($courseLessons)}
                                                                        <table class="table table-hover dataTable no-footer"
                                                                               width="100%"
                                                                               cellspacing="0"
                                                                               cellpadding="0" border="0"
                                                                                style="margin: 0 0!important;">
                                                                            <thead>
                                                                            <tr>
                                                                                <th class="lesson-toggle" aria-controls="table_list" title="Ocultar lecciones">Lecciones&nbsp;
                                                                                </th>
                                                                                <th class="lesson-date" aria-controls="table_list">Fecha&nbsp;
                                                                                    inicio
                                                                                </th>
                                                                                <th class="lesson-seen" aria-controls="table_list">Vista hace&nbsp;
                                                                                </th>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            {foreach $courseLessons as $lesson}
                                                                                {assign var='courseId' value=$lesson->getCourseId ()}
                                                                                {assign var='lessonId' value=$lesson->getLessonSeen()->getId ()}
                                                                                {assign var='lessonName' value=$lesson->getLessonSeen()->getName ()}
                                                                                {assign var="lessonDateStar" value=$lesson->getSeenDate ()}
                                                                                {assign var="lessonLastTime" value=$lesson->getLastTime ()}
                                                                                <tr>
                                                                                    <td>
                                                             <a href="index .php?module=Courses&action=LessonView&course={$courseId}&record={$lessonId}">{$lessonName}</a>
                                                                                    </td>
                                                                                    <td>{$lessonDateStar}</td>
                                                                                    <td>{$lessonLastTime}</td>
                                                                                </tr>
                                                                            {/foreach}
                                                                            </tbody>
                                                                            <tfoot class="hide total-lessons" style="margin: 1px 0!important;">
                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <p class="text-center" style="color: #cccccc">
                                                                                        lecciones vistas:&nbsp;{$courseLessons|count}
                                                                                    </p>
                                                                                </td>
                                                                            </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    {/if}
                                                                </td>
                                                            </tr>
                                                        {/foreach}
                                                        </table>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                        {* /Courses *}
                                    </div>
                                    <div class="tab-pane fade" id="usage-history-system">
                                        {* System use *}
                                        <div class="main-box-body clearfix">
                                            <h2 class="text-center"
                                                style="font-weight: bold; margin: 1em auto;">{$MOD.LB_SUBSCRIPTION_TITLE}</h2>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <ul id="rtl_func">
                                                        {foreach $CATEGORIES as $category}
                                                            {assign var='color' value=$colors[($category@index % 4)]}
                                                            {if (empty ($AVAILABLE_APPLICATIONS[$category.parenttab_label]))}
                                                                {continue}
                                                            {/if}
                                                            <li style="background-color: #ffffff" class="list_root"
                                                                id="f_{$category.catappid}">{$category.parenttab_label}
                                                                <ul id="c_{$category.catappid}">
                                                                    {foreach $category.modules as $data}
                                                                        <li class="list_child">
                                                                            <div class="panel-heading">
                                                                                <p class="panel-title"
                                                                                   style="display:inline-block;width: 70%;vertical-align: middle; margin: 0 auto">{$data.tablabel}</p>
                                                                                <span class="pull-right"
                                                                                      style="margin: 2px; display: inline-block;width: 15%;">
                                                                                    <input class="status-task" id="chck-{$data.name}-{$category.app_code}"
                                                                                           data-status="{$data.presence}"
                                                                                           data-modulerel="{$data.modulerel}" title="{$data.tablabel}"
                                                                                           type="checkbox" {if $data.presence neq "-1"}checked="checked"{/if}
                                                                                           data-toggle="toggle" data-on="On" data-off="Off" data-offstyle="danger"
                                                                                           data-onstyle="success"
                                                                                           data-size="small">
                                                                                </span>
                                                                            </div>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        {* System use *}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {* /Historial de uso  *}
                        {* Historial de descargas  *}
                        <div class="tab-pane fade" id="user-doc-donwload">
                            {* Documents *}
                            <header class="main-box-header clearfix"></header>
                            <div class="main-box-body clearfix">
                                <div class="table-responsive">
                                    <table class="table table-hover dataTable no-footer" width="100%" cellspacing="0"
                                           cellpadding="0" border="0">
                                        <thead>
                                        <tr>
                                            <th aria-controls="table_list">Documento</th>
                                            <th aria-controls="table_list">Descripción</th>
                                            <th aria-controls="table_list">Descargas totales</th>
                                            <th aria-controls="table_list">Disponible desde</th>
                                            <th aria-controls="table_list">Descargado hace</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {if $DONLOADED_FILES neq NULL}
                                        {foreach $DONLOADED_FILES as $file}
                                            {if $file->getLastTime() eq NULL}{continue}{/if}
                                            <tr>
                                                <td>{if $file->getDocument() neq NULL}{$file->getDocument()->getName ()}{/if}</td>
                                                <td>{if $file->getDocument() neq NULL}{$file->getDocument()->getDescription()}{/if}</td>
                                                <td>{if $file->getDocument() neq NULL}{$file->getDocument()->getViewed ()}{/if}</td>
                                                <td>{if $file->getDocument() neq NULL}{$file->getDocument()->getCreateTime()}{/if}</td>
                                                <td>{$file->getLastTime()}</td>
                                            </tr>
                                        {/foreach}
                                        {else}
                                            <tr>
                                              <td colspan="5">
                                                  <div class="alert alert-info">Aún no has descargado ningún documento</div>
                                              </td>
                                            </tr>
                                        {/if}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                           {* Documents *}
                        </div>
                        {* /Historial de descargas  *}
                        {* Datos como calaborador *}
                        <div class="tab-pane fade" id="user-collaborator-data" style="margin-top: 12px">
                            <div class="tabs-wrapper">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#working-day" data-toggle="tab">Jornada de trabajo</a></li>
                                    {if $WORKING_DAYS_HISTORY neq NULL}
                                        <li class=""><a href="#working-day-history" data-toggle="tab">Historial de Jornadas</a></li>
                                    {/if}
                                    {if $IS_ADMIN}
                                    <li class=""><a href="#working-day-create" data-toggle="tab">Crear/Modificar tipos de Jornadas</a></li>
                                    {/if}
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="working-day">
                                        {math equation= rand() assign= "idWorkingDay"}
                                        <form class="form-horizontal" role="form" >
                                        <div class="main-box-body clearfix">
                                                <header class="main-box-header clearfix" style="margin-top: 12px;padding-left: 0!important;padding-right: 0!important;">
                                                    <p class="text-center pull-left" style="font-weight: bold">Información sobre Jornada de Trabajo</p>
                                                    <div class="action-bar pull-right">
                                                        <button type="button" onclick="WorkingDayUtils.setWorkingType(this, '{$idWorkingDay}')"
                                                                class="btn btn-success">Seleccionar jornada laboral</button>
                                                    </div>
                                                </header>
                                                <div class="row">
                                                    <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-left: 25px">
                                                        <label for="working_day_type" class="col-lg-5 col-md-5 control-label">Tipo de jornada de trabajo:</label>
                                                        <div id="wd-div-working_day_type" class="col-lg-7 col-md-7">
                                                            <select class="form-control"
                                                                    id="working-day-types-{$idWorkingDay}"
                                                                    onchange="WorkingDayUtils.getWorkingType(this, '{$idWorkingDay}')">
                                                                <option value="">Seleccionar tipo de jornada</option>
                                                                {if $AVAILABLE_WORKING_DAYS neq NULL}
                                                                    {foreach $AVAILABLE_WORKING_DAYS as $dayWorking}
                                                                        {if $dayWorking->getWorkingDayStatus() eq 'DISABLED'}{continue}{/if}
                                                                        <option value="{$dayWorking->getId ()}"
                                                                                {if $USER_WORKING_DAY neq NULL}
                                                                                    {if $USER_WORKING_DAY->getId eq $dayWorking->getId ()}
                                                                                selected="selected"
                                                                                    {/if}
                                                                                {/if}
                                                                        >{$dayWorking->getWorkingDayName()}</option>
                                                                    {/foreach}
                                                                {/if}
                                                            </select>
                                                            <span id="wd-working_day_type" class="help-block" style="color: red;"></span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-lg-6 col-md-6 col-xs-6">&nbsp;</div>
                                                </div>
                                            <div id="working-day-selected-{$idWorkingDay}">
                                            {if $USER_WORKING_DAY neq NULL}
                                                <div class="row">
                                                    <div class="form-group  col-lg-6 col-md-6 col-xs-6">
                                                        <label for="regular_working_hours" class="col-lg-5 col-md-5 control-label">Jornada diaria (Hrs):</label>
                                                        <div id="wd-div-regular_working_hours" class="col-lg-7 col-md-7">
                                                            <span  class="form-control" style="overflow-x: hidden;width: 100%;">
                                                                {$USER_WORKING_DAY->getRegularWorkingHours()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-bottom: 0!important;">
                                                        <label for="ejemplo_email_3" class="col-lg-5 col-md-5 control-label">Descripción de jornada de trabajo:</label>
                                                        <div class="col-lg-7 col-md-7">
                                                            <span id="dtlview_{$label}"
                                                                  class="form-control"
                                                                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;
                                                                  {if ($USER_WORKING_DAY->getDescription ()|strlen) gt 51} min-height: 70px;
                                                                  {else}
                                                                          min-height: 50px;
                                                                  {/if}line-height: 1.35em !important;">
                                                                {$USER_WORKING_DAY->getDescription ()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-xs-12 pull-left" style="margin-bottom: 12px">Horario regular de la jornada (Horas)</div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-lg-3 col-md-3 col-xs-3">
                                                        <label for="regular_hours_day_gai" class="col-lg-5 col-md-5 pull-roght control-label">Inicio:</label>
                                                        <div id="wd-div-regular_hours_day_gai" class="col-lg-7 col-md-7 bootstrap-timepicker">
                                                            <span class="form-control"
                                                                  style="overflow-x: hidden;width: 100%;">
                                                               {$USER_WORKING_DAY->getMorningStartTime()}
                                                           </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group  col-lg-3 col-md-3 col-xs-3">
                                                        <label for="regular_hours_day_gaf" class="col-lg-5 col-md-5  pull-roght control-label">Fin:</label>
                                                        <div id="wd-div-regular_hours_day_gaf" class="col-lg-7 col-md-7 bootstrap-timepicker">
                                                            <span class="form-control"
                                                                  style="overflow-x: hidden;width: 100%;">
                                                               {$USER_WORKING_DAY->getMorningDueTime()}
                                                           </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group  col-lg-3 col-md-3 col-xs-3">
                                                        <label for="regular_hours_day_gbi" class="col-lg-5 col-md-5  pull-roght control-label">Incio:</label>
                                                        <div id="wd-div-regular_hours_day_gbi" class="col-lg-7 col-md-7 bootstrap-timepicker">
                                                            <span class="form-control"
                                                                  style="overflow-x: hidden;width: 100%;">
                                                               {$USER_WORKING_DAY->getAfternoonStartTime()}
                                                           </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group  col-lg-3 col-md-3 col-xs-3">
                                                        <label for="regular_hours_day_gbf" class="col-lg-5 col-md-5  pull-roght  control-label">Fin:</label>
                                                        <div id="wd-div-regular_hours_day_gbf" class="col-lg-7 col-md-7 bootstrap-timepicker">
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
                                                            <th aria-controls="table_list">Día de la Semana</th>
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
                                                    <div class="col-lg-12 col-md-12 col-xs-12" >
                                                        <div class="alert alert-info alert-dismissable">
                                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                                            <h2>Aún no seleccionas un tipo de jornada laboral.</h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            </div>
                                        </div>
                                        </form>
                                    </div>
                                    {* /working-day*}
                                    {* working-day-create *}
                                    {if $IS_ADMIN}
                                    <div class="tab-pane fade" id="working-day-create">
                                        {math equation= rand() assign= "idEditWorkingDay"}
                                        <form class="form-horizontal" role="form" name="form_workig_day-{$idEditWorkingDay}"  id="form-workig-day-{$idEditWorkingDay}">
                                            <input name="module" type="hidden" value="Home"/>
                                            <input name="Ajax" type="hidden" value="true"/>
                                            <input name="action" type="hidden" value="SaveWorkingDay"/>
                                        <div class="main-box-body clearfix">
                                        <header class="main-box-header clearfix" style="margin-top: 12px;padding-left: 0!important;padding-right: 0!important;">
                                            <p class="text-center pull-left" style="font-weight: bold">Tipos de Jornadas de Trabajo</p>
                                            {*<h1 class="pull-left" style="padding-left: 0!important;">Información sobre Jornada de Trabajo</h1> *}
                                            <div class="action-bar pull-right">
                                                <button type="button" onclick="WorkingDayUtils.saveWorkingType(this, '{$idEditWorkingDay}', '{$idWorkingDay}')"
                                                        class="btn btn-info save-{$idEditWorkingDay}">Guardar</button>
                                                <button type="button" onclick="WorkingDayUtils.cancelWorkingDay('{$idEditWorkingDay}')"
                                                        class="btn btn btn-warning">Cancelar</button>
                                            </div>
                                        </header>
                                            {* / edit WorkingDate *}
                                            <div class="row">
                                                <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-left: 25px">
                                                    <label for="working_day_type" class="col-lg-5 col-md-5 control-label">Modificar jornada de trabajo:</label>
                                                    <div id="wd-div-working_day_type" class="col-lg-7 col-md-7">
                                                        <select class="form-control"
                                                                name="record"
                                                                id="working-day-edit-types-{$idEditWorkingDay}"
                                                                onchange="WorkingDayUtils.editWorkingType(this, '{$idEditWorkingDay}')">
                                                            <option value="">Seleccionar tipo de jornada a modificar</option>
                                                            {if $AVAILABLE_WORKING_DAYS neq NULL}
                                                                {foreach $AVAILABLE_WORKING_DAYS as $dayWorking}
                                                                    <option value="{$dayWorking->getId ()}"
                                                                            {if $USER_WORKING_DAY neq NULL}
                                                                                {if $USER_WORKING_DAY->getId eq $dayWorking->getId ()}
                                                                                    selected="selected"
                                                                                {/if}
                                                                            {/if}
                                                                    >{$dayWorking->getWorkingDayName()}</option>
                                                                {/foreach}
                                                            {/if}
                                                        </select>
                                                        <span id="wd-working_day_type" class="help-block" style="color: red;"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group col-lg-6 col-md-6 col-xs-6">&nbsp;</div>
                                            </div>
                                            <header class="main-box-header clearfix" style="margin-top: 12px;padding-left: 0!important;">
                                                <p class="text-center pull-left" style="font-weight: bold">Información sobre Jornada de Trabajo</p>
                                                {* <h1 class="pull-left" style="padding-left: 0!important;">Distribución del tiempo en la semana</h1> *}
                                            </header>
                                            {* / edit WorkingDate *}
                                            <div class="" id="working-day-edit-selected-{$idEditWorkingDay}">
                                                <div class="row">

                                                    <div class="form-group col-lg-6 col-md-6 col-xs-6" style="margin-left: 25px">
                                                        <label for="working_day_type" class="col-lg-5 col-md-5 control-label">Tipo de jornada de trabajo:</label>
                                                        <div id="wd-div-working_day_type" class="col-lg-7 col-md-7">
                                                            <input type="text" class="form-control col-lg-7 col-md-7"
                                                                   name="working_day_type"
                                                                   title="Tipo de jornada de trabajo"
                                                                   value=""
                                                                   id="working_day_type"
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
                                                                   value=""
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
                                                                  placeholder="Descripción breve del tipo de jornada"
                                                                  rows="3"></textarea>
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
                                                                    <option value="{$status}">{$key}</option>
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
                                                                   id="regular_hours_day_gbf"
                                                                   placeholder="Horas">
                                                            <span id="wd-regular_hours_day_gbf" class="help-block" style="color: red;"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <header class="main-box-header clearfix" style="margin-top: 12px;padding-left: 0!important;">
                                                    <p class="text-center pull-left" style="font-weight: bold">Distribución del tiempo en la semana</p>
                                                    <div class="action-bar pull-right">
                                                        <button type="button" onclick="WorkingDayUtils.extendWorkingHours(this, '{$idEditWorkingDay}')"
                                                                class="btn btn-primary clone-{$idEditWorkingDay}"><i class="fa fa-hand-o-down" aria-hidden="true"></i>
                                                            &nbsp;Extender horario regular</button>
                                                    </div>
                                                </header>
                                                <table class="table table-hover dataTable no-footer" width="100%" cellspacing="0"
                                                       cellpadding="0" border="0">
                                                    <thead>
                                                    <tr>
                                                        <th aria-controls="table_list">Día de la Semana</th>
                                                        <th aria-controls="table_list">Horas de la Jornada</th>
                                                        <th aria-controls="table_list">Hora inicio</th>
                                                        <th aria-controls="table_list">Hora fin</th>
                                                        <th aria-controls="table_list">Hora inicio</th>
                                                        <th aria-controls="table_list">Hora fin</th>
                                                        <th aria-controls="table_list">&nbsp;</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="working-days-table-{$idEditWorkingDay}">
                                                    {if $DAYS_WEEK neq NULL}
                                                        {foreach $DAYS_WEEK as $day}
                                                            {include file='Home/TabsContents/WorkingDaysTable_template.tpl'}
                                                        {/foreach}
                                                    {/if}
                                                    </tbody>
                                                </table>
                                            </div>
                                            {* buttons *}
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12 col-xs-12" style="margin-bottom: 12px">
                                                    <div class="action-bar text-center">
                                                        <button type="button" onclick="WorkingDayUtils.saveWorkingType(this, '{$idEditWorkingDay}', '{$idWorkingDay}')"
                                                                class="btn btn-info save-{$idEditWorkingDay}">Guardar</button>
                                                        <button type="button" onclick="WorkingDayUtils.cancelWorkingDay('{$idEditWorkingDay}')"
                                                                class="btn btn btn-warning">Cancelar</button>
                                                    </div>
                                                </div>
                                            </div>
                                            {* /buttons *}
                                        </div>
                                        </form>
                                    </div>
                                    {/if}
                                    {* /working-day-create *}
                                    {if $WORKING_DAYS_HISTORY neq NULL}
                                    <div class="tab-pane fade" id="working-day-history">
                                        <div class="main-box-body clearfix" style="padding-top: 20px" >
                                            <div class="table-responsive">
                                                <table class="table table-hover dataTable no-footer" width="100%" cellspacing="0"
                                                       cellpadding="0" border="0">
                                                    <thead>
                                                    <tr>
                                                        <th aria-controls="table_list">Tipo de jornada de trabajo</th>
                                                        <th aria-controls="table_list">Jornada diaria (Hrs)</th>
                                                        <th aria-controls="table_list">Fecha de Inicio</th>
                                                        <th aria-controls="table_list">Estado</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    {if $WORKING_DAYS_HISTORY neq NULL}
                                                        {foreach $WORKING_DAYS_HISTORY as $workingDayHistory}
                                                            <tr>
                                                                <td title="{$workingDayHistory->getDescription()}">{$workingDayHistory->getWorkingDayName()}</td>
                                                                <td>{$workingDayHistory->getRegularWorkingHours()}</td>
                                                                <td>{$workingDayHistory->dateCreated}</td>
                                                                <td>{$workingDayHistory->status}</td>
                                                            </tr>
                                                        {/foreach}
                                                    {else}
                                                        <tr>
                                                            <td colspan="5">
                                                                <div class="alert alert-info">Aún no seleccionas un tipo de jornada laboral.</div>
                                                            </td>
                                                        </tr>
                                                    {/if}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="md-modal md-effect-1" id="modal-1" style="z-index: 100000!important;">
        <div class="md-content" style="background-color: white">
            <div class="modal-header">
                <button class="md-close close">×</button>
                <h4 class="modal-title">Cambiar contraseña</h4>
            </div>
            <div class="modal-body">
                <form name="ChangePassword" onsubmit="return CustomerViewUtils.validatePassword ();" method="POST">
                    <input name="module" type="hidden" value="Users"/>
                    <input name="return_module" type="hidden" value="Home"/>
                    <input name="return_action" type="hidden" value="CustomerView"/>
                    <input name="changepassword" type="hidden" value="true"/>
                    <input name="record" type="hidden" value="{$USER->getId ()}"/>
                    <input name="action" type="hidden" value="Save"/>
                    <div class="col-lg-12">
                        <div class="main-box">
                            <div class="main-box-body clearfix">
                                <div class="row">
                                    <div class="form-group col-lg-12">
                                        <label for="new_password">Nueva contraseña</label>
                                        <input name="new_password" id="new_password" tabindex="" value=""
                                               type="password" class="form-control" size="15"/>
                                    </div>
                                    <div class="form-group col-lg-12">
                                        <label for="confirm_new_password">Confirmar contraseña</label>
                                        <input name="confirm_new_password" id="confirm_new_password" tabindex=""
                                               value="" type="password" class="form-control" size="15"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">{$APP.LBL_SAVE_LABEL}</button>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/modalEffects.js"></script>
    <script type="text/javascript" src="modules/Home/customer-view-utils.js"></script>
    <script type="text/javascript" src="modules/how_use/how-use-utils.js"></script>
    <script type="text/javascript" src="modules/Home/workyng-day-utls.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-toggle.min.js"></script>
    <script type="text/html" id="working-days-table_template-{$idEditWorkingDay}">
        {if $DAYS_WEEK neq NULL}
            {foreach $DAYS_WEEK as $day}
                {include file='Home/TabsContents/WorkingDaysTable_template.tpl'}
            {/foreach}
        {/if}
    </script>
    <script type="text/javascript">
        jQuery(document).on('ready', function () {
            jQuery('input[id ^= chck-]').bootstrapToggle();
        });

        jQuery ('.lesson-toggle').on('click', function () {
            var myTable = jQuery (this).closest ("table"),
                myTbody = myTable.find ("tbody");
            myTbody.toggle();
            if (myTbody.is(':visible')) {
                jQuery (this).attr('title', 'Ocultar lecciones');
                myTable.find ('.lesson-date').removeClass ('hide');
                myTable.find ('.lesson-seen').removeClass ('hide');
                myTable.find ('.total-lessons').addClass ('hide');
            } else {
                jQuery (this).attr('title', 'Mostrar lecciones');
                myTable.find ('.lesson-date').addClass ('hide');
                myTable.find ('.lesson-seen').addClass ('hide');
                myTable.find ('.total-lessons').removeClass ('hide');
            }
        });

        jQuery(function () {
            var resetButton = function (obj) {
                var check = jQuery(obj);

                if (check.parent().hasClass('off')) {
                    check.parent().removeClass('off');
                    check.parent().removeClass('btn-danger');
                    check.parent().addClass('btn-success');
                }
            };

            jQuery('input[id ^= chck-]').change(function (e) {
                var check = jQuery(this),
                    status = check.attr('data-status'),
                    idArr = check.attr('id').split('-'),
                    moduleRel = check.attr('data-modulerel').split(';'),
                    message = 'El módulo ' + check.attr('title') + ' esta vinculado con los siguientes módulos :\n- ',
                    resp = true,
                    arguments = {
                        'module': 'Settings',
                        'action': 'UpdatePresenceModule',
                        'tabname': idArr [1],
                        'appcod': idArr [2],
                        'presence': status,
                        'Ajax': 'true'
                    };

                if ((moduleRel[0] !== '') && (status !== '-1')) {
                    message += moduleRel.join('\n- ') + '\n Al desactivarlo pudiera afectar el funcionamiento de esos módulos ¿Desea continuar?';
                    resp = confirm(message);
                }
                check.bootstrapToggle('disable');
                if (resp) {
                    jQuery.post('index.php', arguments, function (data) {
                        var response,
                            alertMess = 'El módulo ' + check.attr('title') + ' ha sido';
                        alertMess += (status !== '-1') ? ' Desactivado!' : ' Activado!';
                        alertMess += '\nSe recargará esta pagina para actualizar el menú';
                        try {
                            response = JSON.parse(JSON.stringify(data));
                            if (response.error !== 'OK') {
                                throw response.error;
                            } else {
                                alert(alertMess);
                                check.attr('data-status', ((status !== '-1') ? '-1' : '0'));
                                check.bootstrapToggle('enable');
                                if (location.href.indexOf('tab=sistema') === -1) {
                                    location.href += '&tab=sistema';
                                } else {
                                    location.reload();
                                }
                            }
                        }
                        catch (e) {
                            if (e.indexOf('<div') !== -1) {
                                alert('Acceso denegado, contactar al administrador!');
                            }
                            alert(e);
                            resetButton(check);
                            check.bootstrapToggle('enable');
                        }
                    });
                } else {
                    resetButton(check);
                    check.bootstrapToggle('enable');
                }
            })
        });
    </script>
{/strip}
