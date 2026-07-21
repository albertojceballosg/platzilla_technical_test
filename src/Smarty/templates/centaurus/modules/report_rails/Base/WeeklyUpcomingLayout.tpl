{strip}
    {math equation= rand() assign= "idPerformanceReport"}
    {block name="css"}{/block}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
            <div class="row-railis justify-content-center">
                <div class="col-lg-8 col-md-8 col-sm-8">
                    <div class="col-lg-5 col-md-5 col-sm-5 justify-content-center">
                        <select class="form-control" name="available_reports"
                                onchange="ReportRailesUtils.selectedUpcomingReport (this, '{$RAND_ID}')"
                                id="available_upcoming-{$idPerformanceReport}">
                            <option value="">Reportes creados</option>
                            {foreach $AVAILABLE_REPORTS as $report}
                                <option value="{$report['weekly_report_code']}" {if $report['weekly_report_code'] eq $REPORT_ID}selected="selected"{/if}
                                        data-status="{$report['status']}">{$report['dates']} - {$MOD[$report['status']]}</option>
                            {/foreach}
                        </select>
                        <span class="help-block" style="color: red"></span>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <div class="btn-group  pull-right">
                            <button name="submitSearch" id="submitSearch" class="btn btn-primary"
                                    data-report-id="{$REPORT_ID}"
                                    data-report-type="{if $UPCOMING_ACTIVITIES neq NULL}UPCOMING{/if}{if ($AFFAIRS neq NULL || $CORRECTIVE_ACTIONS neq NULL)}@UPCOMING_TAB{/if}"
                                    onclick="ReportRailesUtils.publishUpcomingReport (this)"
                                    title="Publicar el informe semanal actual"
                                    type="button">
                                <i class="fa fa-share-alt-square"></i>&nbsp;Publicar el informe actual
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {if $MESSAGE eq NULL}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
                    {* Activities for the next week *}
                    <div class="panel panel-primary">
                        <div class="panel-heading" id="activities_next_week-{$idPerformanceReport}" role="tab">
                            <h3 class="panel-title" style="border-bottom:0!important;">
                                <a style="text-decoration:none!important; text-underline:none!important;"
                                   aria-controls="panel-activities_next_week-{$idPerformanceReport}"
                                   aria-expanded="true"
                                   data-parent="#accordion"
                                   class=""
                                   data-toggle="collapse" href="#panel-activities_next_week-{$idPerformanceReport}"
                                   role="button">
                                    <strong>{$MOD['LBL_ACTIVITIES_NEXT_WEEK']}</strong></a></h3>
                        </div>
                        <div aria-labelledby="heading1" class="panel-collapse collapse in"
                             id="panel-activities_next_week-{$idPerformanceReport}" role="tabpanel">
                            <div class="panel-body" style="padding: 2px 2px!important;">
                                {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_next_week' tableBody='body_next_week' tableClass='class_next_week'}
                            </div>
                        </div>
                    </div>
                    {* Unfinished_business *}
                    <div class="panel panel-info">
                        <div class="panel-heading" id="unfinished_business-{$idPerformanceReport}" role="tab">
                            <h3 class="panel-title" style="border-bottom:0!important;">
                                <a style="text-decoration: none!important; text-underline: none"
                                   aria-controls="panel-unfinished_business-{$idPerformanceReport}"
                                   aria-expanded="true"
                                   data-parent="#accordion"
                                   class=""
                                   data-toggle="collapse" href="#panel-unfinished_business-{$idPerformanceReport}" role="button">
                                    <strong>{$MOD['LBL_UNFINISHED_BUSINESS']}</strong></a></h3>
                        </div>
                        <div aria-labelledby="heading1" class="panel-collapse collapse"
                             id="panel-unfinished_business-{$idPerformanceReport}" role="tabpanel">
                            <div class="panel-body" style="padding: 2px 2px!important;">
                                {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_unfinished_business' tableBody='body_unfinished_business' tableClass='class_unfinished_business'}
                            </div>
                        </div>
                    </div>
                    {* corrective actions *}
                    <div class="panel panel-info">
                        <div class="panel-heading" id="corrective_actions-{$idPerformanceReport}" role="tab">
                            <h3 class="panel-title" style="border-bottom:0!important;">
                                <a style="text-decoration: none!important; text-underline: none"
                                   aria-controls="panel-corrective_actions-{$idPerformanceReport}"
                                   aria-expanded="true"
                                   data-parent="#accordion"
                                   class=""
                                   data-toggle="collapse" href="#panel-corrective_actions-{$idPerformanceReport}"
                                   role="button">
                                    <strong>{$MOD['LBL_CORRECTIVE_ACTIONS']}</strong></a></h3>
                        </div>
                        <div aria-labelledby="heading1" class="panel-collapse collapse"
                             id="panel-corrective_actions-{$idPerformanceReport}" role="tabpanel">
                            <div class="panel-body" style="padding: 2px 2px!important;">
                                {include file='modules/report_rails/Base/TableLabelsLayout.tpl' tableHeader='header_corrective_actions' tableBody='body_corrective_actions' tableClass='class_corrective_actions'}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        {else}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <div class="alert alert-info" role="alert">
                    <strong>¡Atención!</strong> {$MESSAGE}.
                </div>
            </div>
        {/if}
    </div>
    {block name="js"}{/block}

{/strip}
