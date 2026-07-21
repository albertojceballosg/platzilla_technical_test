{strip}
    {assign var='idActivity' value=$TAB_HOME_ID}
    {*$TASKS_VIEW_DATA|var_dump*}
    <div class="row" style="margin-top: 6px">
        <div class="col-xs-8">
            <p class="text-left" style="margin-left: 10px;margin-bottom: 1px"><small style="font-weight: bold">{$MOD.LBL_MATRIX_TITLE}</small></p>
            <ul id="rtl_func">
                {foreach $QUADRANTS as $quadrant}
                    {assign var="quadrants" value=$quadrant|replace:'-':';'}
                    <li class="list_root col-xs-6"
                        id="f_{$quadrant}">
                        <div class="row">
                            <div class="col-md-10 col-xs-10 col-sm-10" style="text-align: center"><span>{$MOD[$quadrant]}</span></div>
                            <div class="col-md-2 col-xs-2 col-sm-2">
                                <span class="pull-right">
                                <button type="button"
                                        style="background-color: transparent!important;"
                                        onclick="CalendarWizard.open (null, null, null, 'index.php?module=Home&amp;action=index&amp;tab=DAILY_MATRIX', 'Activity;{$quadrants};');"
                                        title="Crear tarea:&nbsp;{$MOD[$quadrant]|replace:'-':'y'}" class="btn btn-circle btn-xs"><i class="fa fa-plus"></i></button></span>
                            </div>
                        </div>
                        <ul id="c_{$quadrant}" class="quadrant">
                            {foreach $TASKS_VIEW_DATA[$quadrant] as $data}
                                {if $data['tab_name'] eq 'Calendar'}
                                    {assign var="crmId"  value=$data['crmid']}
                                {else}
                                    {assign var="crmId"  value=$data['related_id']}
                                {/if}
                                <li class="list_child">
                                    {if ($data['tab_name'] neq 'Calendar') && ($data['tab_name'] neq 'Tarea') && ($data['related_id'] neq '0')}
                                        <a class="panel-title"
                                           href="index.php?module={$data['tab_name']}&parenttab=&action=DetailView&record={$data['related_id']}&tab=task-list"
                                           target="_blank"
                                           title="{$data.description}"
                                           style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">{$data.subject}</a>
                                    {else}
                                        <a class="panel-title"
                                           href="#"
                                           onclick="CalendarWizard.open ('{$data['tab_name']}', '{$crmId}', '{$data['modulename']}', 'index.php?module=Home&action=index&tab=DAILY_MATRIX', '{$data['parameters']}');"
                                           title="{$data.description}"
                                           style="display:inline-block;width: 100%;vertical-align: middle; margin: 0 auto;font-size: 0.875em;">{$data.subject}</a>
                                    {/if}
                                </li>
                            {/foreach}
                        </ul>
                    </li>
                {/foreach}
            </ul>
        </div>
        <div class="col-xs-4" style="height: 100%; vertical-align: bottom;text-align: center">
            <div class="row">
                {if $PROGRESS_BAR_OVER}
                    <style>
                        progress::-webkit-progress-bar {
                            background-color: red;
                            /*border: 0;
                            height: 8px;
                            border-radius: 9px;*/
                        }
                    </style>
                {/if}
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <small style="font-weight: bold">Tiempo planificado y usado (%)
                        {if $PROGRESS_BAR_OVER}<br>La ejecución ha excedido el tiempo planificado en&nbsp;{$OVER_TIME}%{/if}
                    </small>
                    <div>
                        <progress id="file" max="{$PROGRESS_BAR_MAX}" value="{$PROGRESS_BAR_WIDTH}" style="width: 98%;{if $PROGRESS_BAR_OVER}background-color: red{/if}"> {$PROGRESS_BAR_WIDTH}% </progress>
                    </div>
                    <div class="text-left">
                        <small>Horas laborables totales para el período:&nbsp;{$WORKED_HOURS}</small><br>
                        <small>Horas reportadas como trabajadas:&nbsp;{$REPORTED_HOURS}</small><br>
                        <small>Horas extras:&nbsp;{$EXTRA_HOURS}</small>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <small style="font-weight: bold" id="piechart_3d_title"></small>
                    <div class="center-block" id="piechart_3d" style="border: 1px solid #ccc;padding-left: 30%"></div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12"style="margin-top: 2px">
                    <small style="font-weight: bold" id="piechart_3d_estimated_title"></small>
                    <div id="piechart_3d_estimated" class="center-block" style="border: 1px solid #ccc;width:100%;padding-left: 30%"></div>
                </div>
            </div>
        </div>
    </div>
    {* Adicional information *}
    {*$ADITIONAL_INFO|var_dump*}
    {if $ADITIONAL_INFO neq NULL || $ACHIEVEMENTS neq NULL}
    <div class="row"  style="margin-top: 6px">
        <div class="col-lg-12 col-md-12 col-ms-12">
            {if $ADITIONAL_INFO neq NULL}
            <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist" style="margin-bottom: 1px!important;">
                {foreach $ADITIONAL_INFO as $rowTitle => $rows}
                    {math equation= rand() assign= "idPanel"}
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                        <h4 class="panel-title" style="text-decoration: none!important;"><a style="text-decoration: none!important; text-underline: none" aria-controls="panel-info-{$idPanel}" aria-expanded="true" data-parent="#accordion" data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">{$rowTitle}&nbsp; del período</a></h4>
                    </div>
                    <div aria-labelledby="heading1" class="panel-collapse collapse in" id="panel-info-{$idPanel}" role="tabpanel">
                        <div class="panel-body" style="padding-top: 1px!important;padding-bottom: 1px!important;">
                            <ul class="list-group" style="margin-bottom: 0!important;">
                                {foreach $rows as $row}
                                <li class="list-group-item"><span style="font-weight: bold">{$row['title']}:&nbsp;</span>{$row['description']}</li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>
            {/if}
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
                                   data-toggle="collapse" href="#panel-info-{$idPanel}" role="button">Logros del día
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="heading1" class="panel-collapse collapse" id="panel-info-{$idPanel}" role="tabpanel">
                            <div class="panel-body" style="padding-top: 1px!important;">
                                <ul class="list-group">
                                    {foreach $ACHIEVEMENTS as $achievement}
                                        <li class="list-group-item"><span style="font-weight: bold">{$achievement['title']}:&nbsp;</span>{$achievement['description']}</li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
    {/if}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <script type="text/javascript" src="modules/Home/daily-matriz-utils.js"></script>
    <script type="text/javascript">
        DailyMatrixUtls.initTask('{$idActivity}', {$TOTALS_QUADRANTS[0]}, {$TOTALS_QUADRANTS[1]}, {$TOTALS_QUADRANTS[2]}, {$TOTALS_QUADRANTS[3]}, {$TOTALS_QUADRANTS[4]});
        DailyMatrixUtls.initEstimated({$TOTALS_ESTIMATED[0]}, {$TOTALS_ESTIMATED[1]}, {$TOTALS_ESTIMATED[2]}, {$TOTALS_ESTIMATED[3]}, {$TOTALS_ESTIMATED[4]}, {$TOTAL_TIMES});
    </script>
{/strip}