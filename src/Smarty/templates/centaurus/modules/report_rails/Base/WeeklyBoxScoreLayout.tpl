{strip}
    {math equation= rand() assign= "idPerformanceReport"}
    {block name="css"}{/block}
    <div class="row">
        {if $MESSAGE eq NULL}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <input type="hidden" id="report-id-{$RAND_ID}" value="{$REPORT_ID}">
                <input type="hidden" id="flmodule-{$RAND_ID}" value="{$FLMODULE}">
                <input type="hidden" id="period-{$RAND_ID}" value="{$PERIOD_TIME}">
                <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
                    {if $CATEGORIES neq NULL}
                        {foreach $CATEGORIES as $category}
                            <!-- {$category['app_name']} -->
                            <div class="panel panel-info">
                                <div class="panel-heading" id="{$category['app_code']}" role="tab">
                                    <h3 class="panel-title" style="border-bottom:0!important;">
                                        <a style="text-decoration: none!important; text-underline: none"
                                           aria-controls="panel-{$category['app_code']}"
                                           aria-expanded="true"
                                           data-parent="#accordion"
                                           class=""
                                           data-script='{$category['indicators_script']}'
                                           rel="{$RAND_ID}"
                                           data-graphic="false"
                                           onclick="ReportRailesUtils.loadGraphics(this, event)"
                                           data-toggle="collapse" href="#panel-{$category['app_code']}"
                                           role="button">
                                            <strong>{$category['app_name']}</strong></a></h3>
                                </div>
                                <div aria-labelledby="heading1" class="panel-collapse collapse"
                                     id="panel-{$category['app_code']}" role="tabpanel">
                                    <div class="panel-body" style="padding: 2px 2px!important;">
                                        <div class="root justify-content-center">
                                            <div class="col-sm-12 col-m-12 col-lg-12">
                                                <div class="row">
                                                    <div class="col-sm-6 col-md-6 col-lg-6">
                                                        <h3 style="color: #31708f" >{$MOD['LBL_GRAPHIC_WEEK']}</h3>
                                                    </div>
                                                </div>
                                                {include file="modules/report_rails/Objects/GraphicBoxScore.tpl" graphic='WEEK'}
                                            </div>
                                            <div class="col-sm-12 col-m-12 col-lg-12">
                                                <div class="row">
                                                    <div class="col-sm-6 col-md-6 col-lg-6">
                                                        <h3 style="color: #31708f" >{$MOD['LBL_GRAPHIC_MONTH']}</h3>
                                                    </div>
                                                </div>
                                                {include file="modules/report_rails/Objects/GraphicBoxScore.tpl" graphic='MONTH'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    {else}
                        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                            <div class="alert alert-info" role="alert">
                                <strong>¡Atención!</strong> No hay categorías definidas.
                            </div>
                        </div>
                    {/if}
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