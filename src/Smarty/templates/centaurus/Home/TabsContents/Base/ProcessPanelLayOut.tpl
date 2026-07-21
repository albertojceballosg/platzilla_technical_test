{strip}
    {math equation= rand() assign= "idPanelProcess"}
    {block name="css"}{/block}
    <div class="main-box-header clearfix" style="padding: 0 1.2em">
        <div class="row">
            {if $MESSAGE eq NULL}
                <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 1px; margin-bottom: 0!important;">
                    <ul class="nav nav-tabs nav-platzilla pull-right" id="MAIN-TAB">
                        {* Time of execution *}
                        <li  class="active" class="">
                            <a data-toggle="tab"
                               href="#TIME_EXECUTION-{$idPanelProcess}">Según tiempo de ejecución{*$MOD['LBL_CONTEXT_THIS_WEEK']*}</a>
                        </li>
                        {* Quality and time *}
                        <li {if ($SELECTED_TAB eq 'QUALITY_TIME')} class="active" {else} class=""{/if}>
                            <a data-toggle="tab" rel=""
                               href="#QUALITY_TIME-{$idPanelProcess}">Según calidad y tiempo{*$MOD['LBL_CONTEXT_BOX_SCORE']*}</a>
                        </li>
                    </ul>
                    <div class="main-box-body clearfix">
                        {* Time of execution *}
                        <div class="tab-content">
                            <div id="TIME_EXECUTION-{$idPanelProcess}" style="padding-top: 15px"
                                 class="tab-pane fade in active">
                                {include file="Home/TabsContents/Objects/timeExecutionTab.tpl"}
                            </div>
                            {* Quality and time *}
                            <div id="QUALITY_TIME-{$idPanelProcess}" style="padding-top: 45px;"
                                 class="tab-pane fade">
                                {include file="Home/TabsContents/Objects/qualityTimeTab.tpl"}
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
        <!-- wa 21-09-24 -->
    </div>
    {block name="js"}{/block}
{/strip}