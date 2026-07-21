{extends file='modules/report_rails/Base/WeeklyPerformanceLayout.tpl'}
{block name="css"}
    <style>
        .row-railis {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
            padding-right: 1px;
            padding-left: 1px;
        }

        .justify-content-between {
            -webkit-box-pack: justify !important;
            -webkit-justify-content: space-between !important;
            -ms-flex-pack: justify !important;
            justify-content: space-between !important;
        }

        .table {
            font-family: "robotoregular";
        }

        .table thead > tr > th {
            font-size: 0.875em;
            padding: 6px 8px !important;
            text-transform: none !important;
            border: 1px solid #ffffff;
        }

        .header-light-gray {
            background-color: #dfe4ec;
        }

        .header-dark-grey {
            background-color: #8093b3;
        }

        .header-dark-grey > span {
            color: #ffffff !important;
        }

        .table-objects > .h-plan-action {
            width: 16% !important;
        }

        .table-objects > .h-initiatives {
            width: 16% !important;
        }

        .table-objects > .h-project {
            width: 16% !important;
        }

        .table-objects > .h-object {
            width: 22% !important;
        }
        .h-object small {
            text-align: left;
            display: block;
            font-weight: bold;
        }
        .table-projects > .h-plan-action {
            width: 24% !important;
        }

        .table-projects > .h-initiatives {
            width: 24% !important;
        }

        .table-projects > .h-project {
            width: 24% !important;
        }

        .h-planned {
            width: 10% !important;
        }

        .h-ejected {
            width: 10% !important;
        }

        .h-progress {
            width: 10% !important;
        }

        .loading {
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
    </style>
{/block}

{* Objects´s table*}
{block name="class_objects"}table-objects{/block}
{block name="header_objects"}
    <th class="h-plan-action header-light-gray" style="width: 16%!important;">Plan de acción</th>
    <th class="h-initiatives header-light-gray" style="width: 16%!important;">Iniciativas</th>
    <th class="h-project header-light-gray" style="width: 16%!important;">Proyecto</th>
    <th class="h-object header-light-gray" style="width: 22%!important;">Objeto</th>
    <th class="h-planned header-dark-grey" style="width: 10%!important;"><span>Hrs. planeadas</span></th>
    <th class="h-ejected header-dark-grey" style="width: 10%!important;"><span>Hrs. ejecutas</span></th>
    <th class="h-progress header-dark-grey" style="width: 10%!important;"><span>% Progreso</span></th>
{/block}
{block name="body_objects"}
    {*$WEEKLY_REPORTS|var_dump*}
    {if $WEEKLY_REPORTS neq NULL}
        {foreach $WEEKLY_REPORTS as $report}
            <tr>
                <td class="h-plan-action">{$report['action_plan']|detail_view_link:$INSTANCE_CODE : 'action_plan'}</td>
                <td class="h-initiatives">{$report['business_initiative']|detail_view_link:$INSTANCE_CODE : 'business_initiatives'}</td>
                <td class="h-project">{$report['project']|detail_view_link:$INSTANCE_CODE : 'proyectos'}</td>
                <td class="h-object"><small>{$report['related_module']|module_label: $ADB}:</small>
                {if isset($report['subject'])}{$report['subject']}{else}{$report['task_subject']}{/if}
                </td>
                <td class="h-planned">{$report['planned_hours']}</td>
                <td class="h-ejected">{$report['execution_hours']}</td>
                <td class="h-progress">{$report['advance_task']}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="7" class="text-center">No se encontraron registros</td>
        </tr>
    {/if}
{/block}

{* Projects's table  *}
{block name="class_project"}table-projects{/block}
{block name="header_project"}
    <th class="h-plan-action header-light-gray" style="width: 24%!important;">Plan de acción</th>
    <th class="h-initiatives header-light-gray" style="width: 23%!important;">Iniciativas</th>
    <th class="h-project header-light-gray" style="width: 23%!important;">Proyecto</th>
    <th class="h-planned header-dark-grey" style="width: 10%!important;"><span>Hrs. planeadas</span></th>
    <th class="h-ejected header-dark-grey" style="width: 10%!important;"><span>Hrs. ejecutas</span></th>
    <th class="h-progress header-dark-grey" style="width: 10%!important;"><span>% Progreso</span></th>
{/block}
{block name="body_project"}
    {if $PROJECT neq NULL}
        {foreach $PROJECT as $crmid =>  $report}
            {if $crmid eq NULL}{continue}{/if}
            <tr>
                <td class="h-plan-action">{$report['action_plan']|detail_view_link:$INSTANCE_CODE : 'action_plan'}</td>
                <td class="h-initiatives">{$report['business_initiative']|detail_view_link:$INSTANCE_CODE : 'business_initiatives'}</td>
                <td class="h-project">{$report|detail_view_link:$INSTANCE_CODE : 'proyectos'}</td>
                <td class="h-planned">{$report['planned_hours']}</td>
                <td class="h-ejected">{$report['execution_hours']}</td>
                <td class="h-progress">{$report['progress']}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="6" class="text-center">No se encontraron proyectos</td>
        </tr>
    {/if}
{/block}

{* Initiatives´s table*}
{block name="class_initiatives"}table-initiatives{/block}
{block name="header_initiatives"}
    <th class="h-plan-action header-light-gray" style="width: 35%!important;">Plan de acción</th>
    <th class="h-initiatives header-light-gray" style="width: 35%!important;">Iniciativas</th>
    <th class="h-planned header-dark-grey" style="width: 10%!important;"><span>Hrs. planeadas</span></th>
    <th class="h-ejected header-dark-grey" style="width: 10%!important;"><span>Hrs. ejecutas</span></th>
    <th class="h-progress header-dark-grey" style="width: 10%!important;"><span>% Progreso</span></th>
{/block}
{block name="body_initiatives"}
    {if $BUSINESS_INITIATIVES neq NULL}
        {foreach $BUSINESS_INITIATIVES as $crmid => $report}
            {if $crmid eq NULL}{continue}{/if}
            <tr>
                <td class="h-plan-action">{$report['action_plan']|detail_view_link:$INSTANCE_CODE : 'action_plan'}</td>
                <td class="h-initiatives">{$report|detail_view_link:$INSTANCE_CODE : 'business_initiatives'}</td>
                <td class="h-planned">{$report['planned_hours']}</td>
                <td class="h-ejected">{$report['execution_hours']}</td>
                <td class="h-progress">{$report['progress' ]}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="5" class="text-center">No se encontraron iniciativas</td>
        </tr>
    {/if}
{/block}

{* Action plan´s table*}
{block name="class_action_plan"}table-action-plan{/block}
{block name="header_action_plan"}
    <th class="h-plan-action header-light-gray" style="width: 70%!important;">Plan de acción</th>
    <th class="h-planned header-dark-grey" style="width: 10%!important;"><span>Hrs. planeadas</span></th>
    <th class="h-ejected header-dark-grey" style="width: 10%!important;"><span>Hrs. ejecutas</span></th>
    <th class="h-progress header-dark-grey" style="width: 10%!important;"><span>% Progreso</span></th>
{/block}
{block name="body_action_plan"}
    {if $ACTION_PLAN neq NULL}
        {foreach $ACTION_PLAN as $crmid => $report}
            {if $crmid eq NULL}{continue}{/if}
            <tr>
                <td class="h-plan-action">{$report|detail_view_link:$INSTANCE_CODE : 'action_plan'}</td>
                <td class="h-planned">{$report['planned_hours']}</td>
                <td class="h-ejected">{$report['execution_hours']}</td>
                <td class="h-progress">{$report['progress']}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="4" class="text-center">No se encontraron planes de acción</td>
        </tr>
    {/if}
{/block}
{block name="js"}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <script type="text/javascript">
        ReportRailesUtils.performaceGraphic ({$DATA_TABLE});
    </script>
{/block}