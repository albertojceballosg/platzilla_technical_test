{math equation= rand() assign= "idJobDetailView"}
{*$WORKS_VIEW_DATA|var_dump*}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css" />
<style>
    {literal}
    #main-{/literal}{$idJobDetailView}{literal} .form-control {
        display: inline-block;
        border: 1px solid #dee2e6 !important;
        width: 92% !important;
        margin-right: 0.1em!important;
    }

    .add_button {
        margin: 10px 0px 10px 0px;
    }

    .badge {
        padding: 0.4em!important;
        vertical-align: top!important;
    }
    .badge small {
        vertical-align: center!important;
    }
    .car-task {
        padding: 1.2em;
        margin-bottom: 1.2em;
    }

    .completed_item {
        text-decoration: line-through;
    }

    .text_holder {
        max-width: 100%;
        word-wrap: break-word;
    }

    #main-{/literal}{$idJobDetailView}{literal} {
        margin-top: 0;
        border-radius: 5px;
        width: 100%;
    }

    .flex-container {
        padding: 0;
        margin: 0;
        list-style: none;
        -ms-box-orient: horizontal;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -moz-flex;
        display: -webkit-flex;
        display: flex;
    }

    .nowrap {
        -webkit-flex-wrap: nowrap;
        flex-wrap: nowrap;
    }

    .wrap {
        -webkit-flex-wrap: wrap;
        flex-wrap: wrap;
    }

    .flex-start {
        justify-content: flex-start;
    }

    .flex-end {
        justify-content: flex-end;
    }

    .space-evenly {
        justify-content: space-evenly;
    }

    .space-between {
        justify-content: space-between;
    }

    .flex-item {
        padding: 5px;
        width: 100px;
        height: 100px;
        margin: 10px;
        line-height: 100px;
    }

    .items-align-baseline {
        align-items: baseline;
    }

    .items-align-star {
        align-items: flex-start;
    }

    .item-date {
        font-size: small;
        font-style: italic;
    }

    .list-form {
        display: none;
    }

    .list-btn-header {
        text-align: center;
        font-weight: bold;
        font-size: small;
        background-color: #F6F6F6;
        margin-top: -5px;
        margin-bottom: -9px;
        padding-bottom: 0.3em;
    }
    .task-group-header  {
        font-weight: bold;
        border-bottom: none!important;
        margin: 0.4em 0!important;
    }
    .input-group-addon {
        color:#555555;background-color:#eeeeee;border-color:#cccccc!important;}
    }
    {/literal}
</style>
<section class="">
    <div class="container" id="main-{$idJobDetailView}">
        <div class="card rounded car-task" style="margin-bottom: 2px!important;padding 0.25em 1.2em!important;">
            {if $HAS_GANTT || $HAS_KANBAN}
            <ul class="nav nav-tabs" id="task-tabs-{$idJobDetailView}">
                {if $HAS_GANTT}
                    <li class="{if $HAS_GANTT}active{/if}">
                        <a data-toggle="tab" href="#gantt-task-tab-{$idJobDetailView}">Gantt de trabajos</a>
                    </li>
                {/if}
                {if $HAS_KANBAN}
                    <li class="{if !$HAS_GANTT}active{/if}">
                        <a data-toggle="tab" href="#kanban-task-tab-{$idJobDetailView}">Kanban de trabajos</a>
                    </li>
                {/if}
            </ul>
            {else}
                <div class="alert alert-info">Las vistas estan deshabilitadas!</div>
            {/if}
        </div>
        {if $HAS_GANTT || $HAS_KANBAN}
        <div class="tab-content" style="padding: 0!important;" >
        {if $HAS_GANTT}
            <div id="gantt-task-tab-{$idJobDetailView}" class="tab-pane fade in {if $HAS_GANTT}active{/if}">
                <div class="card rounded car-task" style="margin-bottom: 2.5px!important;">
                    {if $WORKS_GANTT neq NULL}
                        {include file="modules/proyectos/job_project/GanttJobs.tpl"}
                    {else}
                        <div class="alert alert-info">No hay Trabajos!</div>
                    {/if}
                </div>
            </div>
        {/if}
        {if $HAS_KANBAN}
            <div id="kanban-task-tab-{$idJobDetailView}" class="tab-pane fade {if !$HAS_GANTT}in active{/if}">
                <div class="card rounded car-task" style="margin-bottom: 2.5px!important;">
                    {if $KANBAN_BLOCKS neq NULL}
                        {include file="modules/proyectos/job_project/KanbanJobDiagram.tpl"}
                    {else}
                        <div class="alert alert-info">No hay kanban!</div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
        {/if}
    </div>
</section>