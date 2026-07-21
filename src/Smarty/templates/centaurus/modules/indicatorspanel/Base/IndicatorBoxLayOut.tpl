{strip}
    <div class="main-box clearfix" style="margin-top: 13px;">
        <div class="main-box-body clearfix">
            {* detail View *}
            <div id="reportrange" class="col-lg-12">
                <form name="EditView@@{$APPCODE}" id="EditView@@{$APPCODE}" method="POST" action="index.php">
                    <input type="hidden" name="module" id="module" value="{$MODULE}">
                    <input type="hidden" name="action" id="action" value="{$URL_ACTION}">
                    <input type="hidden" name="record" id="record" value="{$RECORD}">
                    <input type="hidden" name="appcode" id="appcode" value="{$APPCODE}">
                    <input type="hidden" name="date_from" id="date_from" value="">
                    <input type="hidden" name="date_to" id="date_to" value="">
                    <div class="col-md-3">
                        {block name="moonth_search"}{/block}
                    </div>
                    <div class="col-md-6">&nbsp;</div>
                    <div class="col-md-3" style="padding-right:0px;">
                        {block name="view_scale"}{/block}
                    </div>
                </form>
                {block name="created_block"}{/block}
            </div>
            {include file="modules/indicatorspanel/Objets/CrearBlockModal.tpl"}
        </div>
    <div class="main-box-body clearfix">
        {block name="detail_view"}{/block}
    </div>
    </div>
{/strip}