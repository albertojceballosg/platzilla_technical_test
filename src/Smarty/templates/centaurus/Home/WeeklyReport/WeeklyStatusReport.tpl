{extends file='Home/WeeklyReport/Base/SummayReportLayout.tpl'}
{*$MASTER_REPORT|var_dump*}
{if $MASTER_REPORT neq NULL}
    {assign var='status' value=$MASTER_REPORT->getDescription ()}
    {assign var='codeInstance' value=$MASTER_REPORT->getCodeInstance ()}
{else}
    {assign var='status' value=null}
    {assign var='codeInstance' value=''}
{/if}
{strip}
    {block name="css"}
        <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>
    {/block}
    {block name="status"}
        {if $status neq NULL}
            <div class="text-justify" style="vert-align: top">
                {str_replace('<br />', "", str_replace('<br>', "", $status))}
            </div>
        {else}
            <div class="alert alert-info">Análisis de la situación actual no definido!</div>
        {/if}
    {/block}
    {block name="performance"}
        {if $PERFORMANCES neq NULL}
            <div class="row">
                <div class="col-lg-1 col-md-1 col-sm-1">
                    <div id="p-div-performance_iconpath" class="form-group field-container">
                        <div id="iconPath" class="center" style="background-color:{$PERFORMANCES->getIndexColor ()}">
                            <p>{$PERFORMANCES->getIconPath ()}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-11 col-md-11 col-sm-11">
                    <div class="text-justify" style="vert-align: top">
                        {$PERFORMANCES->getDescription ()}
                        {*str_replace('<br />', "", str_replace('<br>', "", $PERFORMANCES->getDescription ()))*}
                    </div>
                </div>
            </div>
        {/if}
    {/block}
    {block name="hide_panel_discussions"}style="display: none"{/block}
    {block name="debates"}{/block}
    {block name="agreement"}
        {if $AGREEMENTS neq NULL}
            {include file='Home/WeeklyReport/Base/AgreementsTableLayout.tpl'}
        {/if}
    {/block}
    {block name="js"}{/block}
{/strip}