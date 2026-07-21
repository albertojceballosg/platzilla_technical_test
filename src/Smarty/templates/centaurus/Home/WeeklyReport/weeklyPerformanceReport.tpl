{extends file='Home/WeeklyReport/Base/WeeklyContextLayout.tpl'}
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
            width: 18% !important;
        }

        .table-objects > .h-initiatives {
            width: 18% !important;
        }

        .table-objects > .h-project {
            width: 18% !important;
        }

        .table-objects > .h-object {
            width: 18% !important;
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
    {if $UPCOMING_ACTIVITIES neq NULL}
        {foreach $UPCOMING_ACTIVITIES as $report}
            <tr>
                <td class="h-plan-action">{$report['action_plan']|detail_view_link:$INSTANCE_CODE : 'action_plan'}</td>
                <td class="h-initiatives">{$report['business_initiative']|detail_view_link:$INSTANCE_CODE : 'business_initiatives'}</td>
                <td class="h-project">{$report['project']|detail_view_link:$INSTANCE_CODE : 'proyectos'}</td>

                <td class="h-object"><small>{$report['related_module']|module_label: $ADB}:</small>
                    {if isset($report['subject'])}{$report['subject']}{else}{$report['task_subject']}{/if}
                </td>

                {*<td class="h-object">{$report['related_module']|module_label: $ADB}</td>*}
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
    <th class="h-business header-dark-grey" style="width: 50%"><span>Asunto</span></th>
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
    <th class="h-business header-dark-grey"  style="width: 50%"><span>Acciones correctivas</span></th>
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
            <td colspan="4" class="loading">No hay acciones correctivas pendientes</td>
        </tr>
    {/if}
{/block}
{block name="js"}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
    <script type="text/javascript">
        {literal}
        jQuery('.panel-collapse').on('shown.bs.collapse', function() {
            var idPanel    = jQuery(jQuery(this).parent().children()[1]).attr('id'),
                loading    = '<i class="fa fa-spinner fa-spin fa-fw"></i>',
                panelTitle = jQuery(jQuery(this).parent().children()[0]).find('a'),
                title      = panelTitle.html(),
                panel = jQuery('#' + idPanel);
            if (idPanel === 'panel-execution_progress-') {
                panelTitle.html(loading + '&nbsp;'+ title)
                if (panel.attr('data-graphic') === 'false') {
                    ReportRailesUtils.performaceGraphic ({/literal}{$DATA_TABLE}{literal});
                    var view = setTimeout(function () {
                        panel.attr ('data-graphic', 'true');
                        panelTitle.html (title);
                        clearTimeout(view);
                    }, 3000);

                } else {
                    panelTitle.html (title);
                }

            }
        });
        {/literal}
        </script>
{/block}