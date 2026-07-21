{strip}
    <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
        <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
            {* general_information *}
            <div class="panel panel-primary">
                <div class="panel-heading" id="general_information-{$idPerformanceReport}" role="tab">
                    <h3 class="panel-title" style="border-bottom:0!important;">
                        <a style="text-decoration:none!important; text-underline:none!important;"
                           aria-controls="panel-general_information-{$idPerformanceReport}"
                           aria-expanded="true"
                           data-parent="#accordion"
                           class=""
                           data-toggle="collapse" href="#panel-general_information-{$idPerformanceReport}"
                           role="button">
                            <strong>{$MOD['LBL_LEVEL_OBJECTS']}</strong></a></h3>
                </div>
                <div aria-labelledby="heading1" class="panel-collapse collapse in"
                     id="panel-general_information-{$idPerformanceReport}" role="tabpanel">
                    <div class="panel-body" style="padding: 2px 2px!important;">
                        {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_objects' tableBody='body_objects' tableClass='class_objects'}
                    </div>
                </div>
            </div>
            {* execution vs progress *}
            <div class="panel panel-info">
                <div class="panel-heading" id="pexecution_progress-{$idPerformanceReport}" role="tab">
                    <h3 class="panel-title" style="border-bottom:0!important;">
                        <a style="text-decoration: none!important; text-underline: none"
                           aria-controls="panel-execution_progress-{$idPerformanceReport}"
                           aria-expanded="true"
                           data-parent="#accordion"
                           class=""
                           data-toggle="collapse" href="#panel-execution_progress-{$idPerformanceReport}"
                           role="button">
                            <strong>{$MOD['LBL_LEVEL_EXECUTION_PROGRESS']}</strong></a></h3>
                </div>
                <div aria-labelledby="heading1" class="panel-collapse collapse"
                     id="panel-execution_progress-{$idPerformanceReport}" data-graphic="{if $DATA_TABLE neq NULL}false{else}true{/if}" role="tabpanel">
                    <div class="panel-body" style="padding: 2px 2px!important;">
                        <div class="row justify-content-center">
                            <div class="col-lg-1 col-md-1 col-xs-1">&nbsp;</div>
                            <div class="col-lg-10 col-md-10 col-xs-10" style="padding-top: 10px;padding-left: 10px">
                                {if $DATA_TABLE eq NULL}<h3 style="text-align: center">No hay datos a graficar</h3>{/if}
                                <div class="center-block" id="columnchart_values"
                                     {if $DATA_TABLE neq NULL}style="width:100%; height: 560px;" {/if}></div>
                            </div>
                            <div class="col-lg-1 col-md-1 col-xs-1">&nbsp;</div>
                        </div>
                    </div>
                </div>

            </div>
            {* project *}
            <div class="panel panel-info">
                <div class="panel-heading" id="project-{$idPerformanceReport}" role="tab">
                    <h3 class="panel-title" style="border-bottom:0!important;">
                        <a style="text-decoration: none!important; text-underline: none"
                           aria-controls="panel-project-{$idPerformanceReport}"
                           aria-expanded="true"
                           data-parent="#accordion"
                           class=""
                           data-toggle="collapse" href="#panel-project-{$idPerformanceReport}" role="button">
                            <strong>{$MOD['LBL_LEVEL_PROJECT']}</strong></a></h3>
                </div>
                <div aria-labelledby="heading1" class="panel-collapse collapse"
                     id="panel-project-{$idPerformanceReport}" role="tabpanel">
                    <div class="panel-body" style="padding: 2px 2px!important;">
                        {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_project' tableBody='body_project' tableClass='class_project'}
                    </div>
                </div>
            </div>
            {* business_initiatives *}
            <div class="panel panel-info">
                <div class="panel-heading" id="business_initiatives-{$idPerformanceReport}" role="tab">
                    <h3 class="panel-title" style="border-bottom:0!important;">
                        <a style="text-decoration: none!important; text-underline: none"
                           aria-controls="panel-business_initiatives-{$idPerformanceReport}"
                           aria-expanded="true"
                           data-parent="#accordion"
                           class=""
                           data-toggle="collapse" href="#panel-business_initiatives-{$idPerformanceReport}"
                           role="button">
                            <strong>{$MOD['LBL_LEVEL_INITIATIVES']}</strong></a></h3>
                </div>
                <div aria-labelledby="heading1" class="panel-collapse collapse"
                     id="panel-business_initiatives-{$idPerformanceReport}" role="tabpanel">
                    <div class="panel-body" style="padding: 2px 2px!important;">
                        {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_initiatives' tableBody='body_initiatives' tableClass='class_initiatives'}
                    </div>
                </div>

            </div>
            {* action_plan *}
            <div class="panel panel-info">
                <div class="panel-heading" id="action_plan-{$idPerformanceReport}" role="tab">
                    <h3 class="panel-title" style="border-bottom:0!important;">
                        <a style="text-decoration: none!important; text-underline: none"
                           aria-controls="panel-action_plan-{$idPerformanceReport}"
                           aria-expanded="true"
                           data-parent="#accordion"
                           class=""
                           data-toggle="collapse" href="#panel-action_plan-{$idPerformanceReport}"
                           role="button">
                            <strong>{$MOD['LBL_LEVEL_ACTION_PLAN']}</strong></a></h3>
                </div>
                <div aria-labelledby="heading1" class="panel-collapse collapse"
                     id="panel-action_plan-{$idPerformanceReport}" role="tabpanel">
                    <div class="panel-body" style="padding: 2px 2px!important;">
                        {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_action_plan' tableBody='body_action_plan' tableClass='class_action_plan'}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}