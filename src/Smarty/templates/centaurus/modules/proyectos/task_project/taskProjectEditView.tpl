{math equation= rand() assign= "idTaskProject"}
<link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
<div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 20px"{/if}>
    {if ($VIEW neq NULL) && ($RELATED_TASK neq NULL)}
    <div class="row card-header platzilla-card-header" style="padding-left: 0!important;">
        <div class="col-md-5">
            <p class="text-center pull-left" style="font-weight: bold">Tareas</p>
        </div>
        <div class="col-md-7">&nbsp;</div>
    </div>
    {/if}
    <div class="table-responsive field-container">
        <input type="hidden" id="usr" value="{$CURRENT_USER_ID}">
            <table id="task-project-table-{$idDailyReport}" class="table table-bordered tablegridvalidate">
                {if $VIEW eq NULL}
                <thead>
                <tr>
                    <td colspan="9" style="text-align: left; background-color:#f9f8f7"><strong>Tareas:</strong></td>
                </tr>
                <tr valign="top">
                    <td style="" width="11%"><span style="">Etapa</span></td>
                    <td style="" width="23%"><span style="">Tarea</span></td>
                    <td style="" width="11%"><span style="">Tipo</span></td>
                    <td style="" width="9%"><span style="">Inicio</span></td>
                    <td style="" width="9%"><span style="">Fin</span></td>
                    <td style="" width="8%"><span style="">Duración (hrs)</span></td>
                    <td style="" width="11%"><span style="">Asignado</span></td>
                    <td style="" width="8%"><span style="">% avance</span></td>
                    <td class="text-center"
                        {if $VIEW eq NULL}width="12%"{/if}>{if $VIEW eq NULL}Acciones{else}&nbsp;{/if}
                    </td>
                </tr>
                {/if}
                </thead>
                <tbody id="task-project-{$idTaskProject}" rowtotal="0">
                {if $VIEW eq NULL}
                    {if $RELATED_TASK neq NULL}
                        {foreach $RELATED_TASK as $key => $relatedTask}
                        {math equation= rand() assign= "idRow"}
                            {include file='modules/proyectos/task_project/taskProjectEdit_template.tpl'}
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="9" style="text-align: center"></td>
                        </tr>
                    {/if}
                {else}
                    {if ($RELATED_TASK neq NULL) && ($PROJECT_STAGES neq NULL)}
                        {foreach $PROJECT_STAGES as $projectStage}
                                {if in_array($projectStage->id,$RELATED_STAGES)}
                                    <tr>
                                        <td colspan="8" style="text-align: left;"><strong>{$projectStage->stage}:</strong></td>
                                    </tr>
                                    <tr valign="top">
                                        <td colspan="2" style="background-color:#f9f8f7" width="23%"><span style="">Tarea</span></td>
                                        <td style="background-color:#f9f8f7" width="11%"><span style="">Tipo</span></td>
                                        <td style="background-color:#f9f8f7" width="9%"><span style="">Inicio</span></td>
                                        <td style="background-color:#f9f8f7" width="9%"><span style="">Fin</span></td>
                                        <td style="background-color:#f9f8f7" width="8%"><span style="">Duración (hrs)</span></td>
                                        <td style="background-color:#f9f8f7" width="11%"><span style="">Asignado</span></td>
                                        <td style="background-color:#f9f8f7" width="8%"><span style="">% avance</span></td>
                                    </tr>
                                {/if}
                            {foreach $RELATED_TASK as $key => $relatedTask}
                                {if $relatedTask['stage'] neq $projectStage->id}{continue}{/if}
                                {math equation= rand() assign= "idRow"}
                                {include file='modules/proyectos/task_project/taskProjectDetailView_template.tpl'}
                            {/foreach}
                        {/foreach}
                    {/if}
                {/if}
                </tbody>
                <tfoot id="tfoot-{$idTaskProject}" data-field-name="planned_activities" data-summary-row=""
                       data-operation-row="">
                {if $VIEW eq NULL}
                    <tr>
                        <td colspan="9" class="text-center">
                            <button type="button" data-id-linkage="{$idTaskProject}" class="btn btn-primary"
                                    data-sequence="{($key + 1)}"
                                    onclick="TaskProjectUtls.addRowToTable (this, 'task-project-{$idTaskProject}', '{$idTaskProject}');">
                                <i class="fa fa-plus"></i></button>
                        </td>
                    </tr>
                {/if}
                </tfoot>
            </table>
        {if $VIEW eq NULL}
            <script type="text/html" id="task-project-template-{$idTaskProject}">
                {include file='modules/proyectos/task_project/taskProject_template.tpl'}
            </script>
            <script type="text/html" id="task-project-tr-{$idTaskProject}">
                <tr>
                    <td colspan="9" style="text-align: center"></td>
                </tr>
            </script>
        {/if}
    </div>
</div>
<script src="modules/proyectos/task-project-utls.js?v=20260606d"></script>
{if $RELATED_TASK neq NULL}
<script type="text/javascript">
    TaskProjectUtls.setCalendar ('#task-project-table-{$idDailyReport}')
</script>
{/if}