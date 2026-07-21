{math equation= rand() assign= "idHelp"}
{if (isset ($HOW_TO))}
        {assign var='howToEntites' value=$HOW_TO->getEntity()}
        {assign var='howToHtml' value=$HOW_TO->getHtml()}
        {assign var='howToId' value=$HOW_TO->getId()}
        {assign var='howToImage' value=$HOW_TO->getImage()}
        {assign var='howToStatus' value=$HOW_TO->getStatus()}
        {assign var='howToTitle' value=$HOW_TO->getTitle()}
        {assign var='howToVideo' value=$HOW_TO->getVideo()}
        {assign var='howToTypeVideo' value=$HOW_TO->getVideoType()}
        {assign var="isEdit" value=true}
    {else}
        {assign var='howToEntites' value=null}
        {assign var='howToHtml' value=null}
        {assign var='howToId' value=null}
        {assign var='howToImage' value=null}
        {assign var='howToStatus' value=null}
        {assign var='howToTitle' value=null}
        {assign var='howToVideo' value=null}
        {assign var='howToTypeVideo' value=null}
        {assign var="isEdit" value=false}
    {/if}
{block name="css"}{/block}
<form name="form-{$idHelp}" method="post" enctype="multipart/form-data" id="how-to-form-{$idHelp}">
    <input type="hidden" name="module" value="Settings"/>
    <input type="hidden" name="action" value="SaveHowTo"/>
    <input type="hidden" name="tab" value="how_to">
    <input type="hidden" name="record" value="{$RECORD}"/>
    {block name="howto_header"}{/block}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="row">
        <div class="col-xs-12">
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2 class="pull-left">{$MOD.LBL_HOW_TO_CONFIG_BLOCK}</h2>
                </header>
                <div class="main-box-body">
                    {block name="how_to_detail"}{/block}
                </div>
            </div>
        </div>
        <div class="col-xs-12" style="margin-top: 2px">
            <div class="main-box">
                <header class="main-box-header clearfix">
                    <h2 class="pull-left">{$MOD.LBL_HOW_TO_LINKS_BLOCK}</h2>
                </header>
                <div class="main-box-body">
                    {block name="how_to_assign"}{/block}
                </div>
            </div>
        </div>
    </div>
</form>
{block name="js"}{/block}
<script type="text/html" id="how-to-assign-template-{$idHelp}">
    {include file='Settings/include/assign_row_howto_template.tpl'}
</script>