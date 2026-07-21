{strip}
    {block name="css"}{/block}
    {block name="js"}{/block}
    {math equation= rand() assign= "idDiagnosticReport"}
    <div id="card-view-register-container">
        <div class="col-lg-12 col-md-12 col-sm-12 card rounded" style="margin-bottom: 6px;background-color: #c0c0c0">
            {* Imagen o mapa de la situación actual *}
            <img ID="current-status-{$idDiagnosticReport}"
                 src="data:image/png;base64,{block name='current_status'}{/block}"
                 style="{*height: 95%;width: 95%;margin-bottom: 4px*}"
                 class="img-responsive center-block">
        </div>
        {*
        <div class="card rounded">
            {block name='test'}{/block}
        </div>
        *}
        <div class="card rounded">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8">
                    {*Texto explicativo de la situación actual*}
                    {block name='current_situation'}{/block}
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="row  well well-sm" style="margin: 8px">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {* Datos de la empresa o del prospecto *}
                            {block name='diagnostic_data'}{/block}
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {* Video informativo *}
                            {block name='informative_video'}{/block}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="platzilla-card-header" style="padding-left: 10px">
                        <p class="text-center pull-left"
                           style="font-weight: bold">{$MOD['LBL_MODULE_BLOCK_FUNCTION']}</p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">&nbsp;</div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    {* Video informativo *}
                    {block name='function_result'}{/block}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="platzilla-card-header" style="padding-left: 10px">
                        <p class="pull-left text-justify"
                           style="font-weight: bold">{$MOD['LBL_IMPROVEMENT_OPPORTUNITIES']}</p>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    {*  no show this text for now
                    <p class="pull-left text-justify text-danger" style="padding-left: 10px">
                        {$MOD['LBL_OPPORTUNITIES_MOTTO']}</p>
                    *}
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    {* Improvement Oportunities *}
                    {block name='improvement_opportunities'}{/block}
                </div>
            </div>
            {* foot page*}
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12" style="min-height: 60px">&nbsp;</div>
            </div>
        </div>
    </div>
{/strip}