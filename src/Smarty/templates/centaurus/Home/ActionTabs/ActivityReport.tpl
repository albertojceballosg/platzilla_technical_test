{assign var="extend_class" value="Home/ActionTabs/Base/"|cat:$EXTENDS_CLASS|cat:".tpl"}
{extends file=$extend_class}
{strip}
    {block name="css"}
        <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
        <link rel="stylesheet" type="text/css" href="modules/Home/daily_matrix.css"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
    {/block}
    {* Search buttons for activity report *}
    {block name = "action_buttons-activty-report"}
        {assign var="fromModule" value='Home'}
        {assign var="totalRecords" value=0}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$ACTIVITY_TAB_ID}
        {assign var='ajaxFuntion' value='ACTIVITY_REPORT'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="reportedDays" value=$REPORTED_DAYS}
        {assign var="searchTitle" value='Buscar tareas'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="otherButtonTitle" value=' &nbsp;Parte de trabajo'}
        {assign var="otherButtonToltip" value='Ver Partes de Trabajo'}
        {assign var="otherButtonAction" value='DataViewUtils.goToPartWork'}
        {assign var="otherButtonClass" value='btn-default'}
        {assign var="otherButtonIcon" value='fa-file-text-o'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}
    {* Daily report buttons *}
    {block name= "daily-report"}
        <div id="date-group-{$ACTIVITY_TAB_ID}" class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle"
                    style="height: 34px;margin-right: 1px"
                    data-toggle="dropdown">
                <i class="fa fa-file-o" aria-hidden="true"></i> Crear informe diario <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <li>
                    <a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp={$REPORT_TODAY}"
                       data-date="{$TODAY}"
                       onclick="DailyMatrixUtls.goReportDate(this, '{$ACTIVITY_TAB_ID}', event)">Para hoy</a>
                </li>
                <li>
                    <a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp={$REPORT_YESTERDAY}"
                       data-date="{$YESTERDAY}"
                       onclick="DailyMatrixUtls.goReportDate(this, '{$ACTIVITY_TAB_ID}', event)">Para ayer</a>
                </li>
                <li>
                    <a id="other-date-{$ACTIVITY_TAB_ID}" href="#" rel="{$USER_ID}" data-date=""
                       onclick="DailyMatrixUtls.createReportDate(this, '{$ACTIVITY_TAB_ID}', event)">Otra fecha</a>
                </li>
                <li class="hide other-date">
                    <input rel="{$USER_ID}" class="form-control pull-right input-readonly b-left col-md-3"
                           placeholder="Seleccione fecha"
                           onclick="DailyMatrixUtls.createReportDate(this, '{$ACTIVITY_TAB_ID}', event)"
                           value=""
                           type="text" id="report-date-{$ACTIVITY_TAB_ID}" readonly="readonly">
                </li>
            </ul>
        </div>
        <a href="index.php?module=daily_report&action=index" class="btn btn-primary"
           style="height: 34px;margin-right: 1px"
           title="Informe diario"><i class="fa fa-file-text-o" aria-hidden="true"></i></a>
        <a href="index.php?module=Calendar&action=index&calendar_main=1" class="btn btn-warning"
           style="height: 34px;"
           title="Ir al calendario"><i class="fa fa-calendar"></i></a>
        <a href="index.php?module=views_diagrams&action=index" class="btn btn btn-default"
           style="height: 34px;"
           title="Ir a la vista de tareas"><span class="glyphicon glyphicon-indent-left"></span></a>
    {/block}
    {block name="part_work"}
        {*
        <br/><div id="date-work-group-{$ACTIVITY_TAB_ID}" class="btn-group pull-right" style="margin-top: 0.15em">
            <button type="button" class="btn btn-primary"
                    onclick="DataViewUtils.goToPartWork (event, this, '{$actionId}')"
                    style="height: 34px;margin-right: 1px">
                <i class="fa fa-file-o" aria-hidden="true"></i>&nbsp;Parte de trabajo&nbsp;
            </button>
            <a href="index.php?module=views_diagrams&action=index" class="btn btn btn-default"
               style="height: 34px;"
               title="Ir a la vista de tareas"><span class="glyphicon glyphicon-indent-left"></span></a>
        <input type="hidden" name="invitees_id" value="">
        </div>
        *}
    {/block}
    {* Daily Matrix by quadrants*}
    {block name="daily-matrix-quadrants"}
        {assign var="workModule" value='orden_de_trabajo'}
        {assign var="activityModule" value='Calendar'}
        <p class="text-left" style="margin-left: 10px;margin-bottom: 1px">
            <small style="font-weight: bold">{$MOD.LBL_ACTIVITY_REPORT}</small></p>
        <ul id="rtl_func">
            {foreach $QUADRANTS as $quadrant}
                <li class="list_root col-xs-6"
                    id="f_{$quadrant}">
                    <div class="row">
                        <div class="col-md-10 col-xs-10 col-sm-10" style="text-align: center">
                            <span>{$MOD[$quadrant]}</span></div>
                        <div class="col-md-2 col-xs-2 col-sm-2">
                            <span class="pull-right"></span>
                        </div>
                    </div>
                    <ul id="c_{$quadrant}" class="quadrant">
                        {foreach $ACTIVITY_WORKS[$quadrant] as $jobData}
                            <li class="list_child" >
                                {* only works *}
                                {if ($jobData['tab_name'] eq $workModule)}
                                    <a class="panel-title"
                                       href="index.php?module={$workModule}&parenttab=&action=DetailView&record={$jobData['entity_id']}"
                                       target="_blank"
                                       title="{$jobData['title']}"
                                       style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">
                                        <i class="fa fa-check-square" aria-hidden="true"></i>&nbsp;{$jobData['title']}
                                    </a>
                                {* only activities *}
                                {elseif ($jobData['tab_name'] eq $activityModule && $jobData['module_name'] eq NULL)}
                                    <a class="panel-title"
                                       href="index.php?module={$activityModule}&parenttab=&action=DetailView&record={$jobData['entity_id']}"
                                       target="_blank"
                                       title="{$jobData['title']}"
                                       style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">
                                        <i class="fa fa-check-square" aria-hidden="true"></i>&nbsp;<b>Acción:&nbsp;</b>
                                        {$jobData['title']}
                                    </a>
                                {else}
                                <a class="panel-title"
                                   href="index.php?module={$jobData['module_name']}&parenttab=&action=DetailView&record={$jobData['tab_id']}"
                                   target="_blank"
                                   title="{$jobData['title']}"
                                   style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">
                                    <i class="fa fa-check-square" aria-hidden="true"></i>&nbsp;<b>Accion:&nbsp;</b>
                                    {$jobData['title']}&nbsp;({$jobData['tab_label']})
                                </a>
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    {/block}
    {* Daily Matrix Graphics *}
    {block name="daily-matrix-graphics"}
        {include file='Home/ActionTabs/dailyGraphicsData.tpl'}
    {/block}
    {* Achievements *}
    {block name="daily-report-achievements"}
        {if $ACHIEVEMENTS neq NULL}
            <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
                {math equation= rand() assign= "idPanel"}
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                        <h4 class="panel-title" style="text-decoration: none!important;">
                            <a style="text-decoration: none!important; text-underline: none"
                               aria-controls="panel-info-{$idPanel}"
                               aria-expanded="true"
                               data-parent="#accordion"
                               class="collapsed"
                               data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">Logros conseguidos
                            </a>
                        </h4>
                    </div>
                    <div aria-labelledby="heading1" class="panel-collapse collapse" id="panel-info-{$idPanel}"
                         role="tabpanel">
                        <div class="panel-body" style="padding-top: 1px!important;">
                            <ul class="list-group">
                                {foreach $ACHIEVEMENTS as $achievement}
                                    <li class="{*list-group-item*}" style="margin-bottom: 0.05em"><span style="font-weight: bold">
                                            {$achievement['title']}:&nbsp;</span>{$achievement['description']}
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    {/block}
    {* Problems detected *}
    {block name="daily-report-problems-detected"}
        {if $PROBLEMS neq NULL}
            <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist"
                 style="margin-bottom: 1px!important;">
                    {math equation= rand() assign= "idPanel"}
                    <div class="panel panel-default">
                        <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                            <h4 class="panel-title" style="text-decoration: none!important;">
                                <a style="text-decoration: none!important; text-underline: none"
                                   aria-controls="panel-info-{$idPanel}"
                                   aria-expanded="true"
                                   data-parent="#accordion"
                                        {if $isCollapsed neq NULL}
                                            class="collapsed"
                                        {/if}
                                   data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">
                                    Posibles problemas detectadors
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="heading1"
                             class="panel-collapse collapse {if $isCollapsed eq NULL}in {/if}"
                             id="panel-info-{$idPanel}" role="tabpanel">
                            <div class="panel-body" style="padding-top: 1px!important;">
                                <ul class="list-group" style="margin-bottom: 0!important;">
                                    {foreach $PROBLEMS as $rowTitle => $rows }
                                        <li class="" style="margin-bottom: 0.05em"><span style="font-weight: bold">{$rowTitle}</span>
                                            <ul class="list-group" style="margin-bottom: 0!important;">
                                                {foreach $rows as $row}
                                                    <li class="{*list-group-item*}" style="margin-bottom: 0.05em"><span style="font-weight: bold">{$row['title']}:&nbsp;</span>{$row['description']}
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                    {assign var="isCollapsed" value="collapsed"}
            </div>
        {/if}
    {/block}
    {* Suggestions and news *}
    {block name="daily-report-suggestions-news"}
        {if $SUGGESTIONS_NEWS neq NULL}
            <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist"
                 style="margin-bottom: 1px!important;">
                    {math equation= rand() assign= "idPanel"}
                    <div class="panel panel-default">
                        <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                            <h4 class="panel-title" style="text-decoration: none!important;">
                                <a style="text-decoration: none!important; text-underline: none"
                                   aria-controls="panel-info-{$idPanel}"
                                   aria-expanded="true"
                                   data-parent="#accordion"
                                        {if $isCollapsed neq NULL}
                                            class="collapsed"
                                        {/if}
                                   data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">
                                    Sugerencias y noticias
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="heading1"
                             class="panel-collapse collapse {if $isCollapsed eq NULL}in {/if}"
                             id="panel-info-{$idPanel}" role="tabpanel">
                            <div class="panel-body" style="padding-top: 1px!important;">
                                <ul class="list-group" style="margin-bottom: 0!important;">
                                    {foreach $SUGGESTIONS_NEWS as $rowTitle => $rows}
                                        <li class="" style="margin-bottom: 0.05em"><span style="font-weight: bold">{$rowTitle}</span>
                                            <ul class="list-group" style="margin-bottom: 0!important;">
                                                {foreach $rows as $row}
                                                    <li class="{*list-group-item*}" style="margin-bottom: 0.05em"><span style="font-weight: bold">{$row['title']}:&nbsp;</span>{$row['description']}
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                    {assign var="isCollapsed" value="collapsed"}

            </div>
        {/if}
    {/block}
    {block name="js"}
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
        <script type="text/javascript" src="modules/Home/daily-matriz-utils.js"></script>
        <script type="text/javascript">
            DailyMatrixUtls.initTask('{$ACTIVITY_TAB_ID}', {$TOTALS_QUADRANTS[0]}, {$TOTALS_QUADRANTS[1]}, {$TOTALS_QUADRANTS[2]}, {$TOTALS_QUADRANTS[3]}, {$TOTALS_QUADRANTS[4]});
            DailyMatrixUtls.initEstimated({$TOTALS_ESTIMATED[0]}, {$TOTALS_ESTIMATED[1]}, {$TOTALS_ESTIMATED[2]}, {$TOTALS_ESTIMATED[3]}, {$TOTALS_ESTIMATED[5]}, {$TOTALS_ESTIMATED[4]});
        </script>
    {/block}
{/strip}