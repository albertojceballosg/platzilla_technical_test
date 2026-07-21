{extends file='modules/report_rails/Base/WeeklyUpcomingLayout.tpl'}
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

        .table-next-activity > .h-plan-action {
            width: 18% !important;
        }

        .table-next-activity > .h-initiatives {
            width: 18% !important;
        }

        .table-next-activity > .h-project {
            width: 18% !important;
        }

        .table-unfinished-business > .h-business {
            width: 50% !important;
        }

        .table-unfinished-business > .h-business-date {
            width: 15% !important;
        }

        .table-unfinished-business > .h-business-states {
            width: 15% !important;
        }

        .table-unfinished-business > .h-business-user {
            width: 20% !important;
        }

        .h-planned {
            width: 10% !important;
        }

        .h-ejected {
            width: 9% !important;
        }

        .h-progress {
            width: 9% !important;
        }

        .loading {
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
        .h-object small {
            text-align: left;
            display: block;
            font-weight: bold;
        }
    </style>
{/block}

{* activities next week´s table*}
{block name="class_next_week"}table-next-activity{/block}

{block name="header_next_week"}
    <th class="h-plan-action header-light-gray" style="width: 17%!important;">Plan de acción</th>
    <th class="h-initiatives header-light-gray" style="width: 17%!important;">Iniciativas</th>
    <th class="h-project header-light-gray" style="width: 17%!important;">Proyecto</th>
    <th class="h-object header-light-gray" style="width: 24%!important;">Objeto</th>
    <th class="h-planned header-dark-grey" style="width: 10%!important;"><span>Hrs. planeadas</span></th>
    <th class="h-progress header-dark-grey" style="width: 10%!important;"><span>% Progreso</span></th>
{/block}

{block name="body_next_week"}
    {*$UPCOMING_ACTIVITIES|var_dump*}
    {if $UPCOMING_ACTIVITIES neq NULL}
        {foreach $UPCOMING_ACTIVITIES as $report}
            <tr>
                <td class="h-plan-action">{$report['action_plan']|detail_view_link:$INSTANCE_CODE : 'action_plan'}</td>
                <td class="h-initiatives">{$report['business_initiative']|detail_view_link:$INSTANCE_CODE : 'business_initiatives'}</td>
                <td class="h-project">{$report['project']|detail_view_link:$INSTANCE_CODE : 'proyectos'}</td>
                <td class="h-object"><small>{$report['related_module']|module_label: $ADB}:</small>
                                {if isset($report['subject'])}{$report['subject']}{else}{$report['task_subject']}{/if}
                </td>
                <td class="h-planned">{$report['planned_hours']}</td>
                <td class="h-progress">{$report['advance_task']}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="6" class="loading">No hay actividades próximas</td>
        </tr>
    {/if}
{/block}

{* Unfinished business's table  *}
{block name="class_unfinished_business"}table-unfinished-business{/block}
{block name="header_unfinished_business"}
    <th class="h-business  header-dark-grey"><span>Asunto</span></th>
    <th class="h-business-date header-dark-grey"><span>Fecha</span></th>
    <th class="h-business-states header-dark-grey"><span>Estado</span></th>
    <th class="h-business-user header-dark-grey"><span>Asignado a</span></th>
{/block}

{block name="body_unfinished_business"}
    {if $AFFAIRS neq NULL}
        {foreach $AFFAIRS as $report}
            {if $report['crmid'] eq NULL}{continue}{/if}
            <tr>
                <td class="h-plan-action">{$report['link']|detail_view_link:$INSTANCE_CODE : 'affairs'}</td>
                <td class="h-initiatives">{$report['record_date']|date_es_format}</td>
                <td class="h-planned">{$report['record_state']}</td>
                <td class="h-ejected">{$report['username']}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="4" class="loading">No hay asuntos pendientes</td>
        </tr>
    {/if}
{/block}

{* Corrective actions´s table*}
{block name="class_corrective_actions"}table-unfinished-business{/block}

{block name="header_corrective_actions"}
    <th class="h-business header-dark-grey"><span>Acciones correctivas</span></th>
    <th class="h-business-date header-dark-grey"><span>Fecha</span></th>
    <th class="h-business-states header-dark-grey"><span>Estado</span></th>
    <th class="h-business-user header-dark-grey"><span>Asignado a</span></th>
{/block}

{block name="body_corrective_actions"}
    {if $CORRECTIVE_ACTIONS neq NULL}
        {foreach $CORRECTIVE_ACTIONS as $report}
            {if $report['crmid'] eq NULL}{continue}{/if}
            <tr>
                <td class="h-plan-action">{$report['link']|detail_view_link:$INSTANCE_CODE : 'affairs'}</td>
                <td class="h-initiatives">{$report['record_date']|date_es_format}</td>
                <td class="h-planned">{$report['record_state']}</td>
                <td class="h-ejected">{$report['username']}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="4" class="loading">No hay acciones pendientes</td>
        </tr>
    {/if}
{/block}