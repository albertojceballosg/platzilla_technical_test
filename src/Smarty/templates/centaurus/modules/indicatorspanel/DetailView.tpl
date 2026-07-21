{extends file='modules/indicatorspanel/Base/IndicatorBoxLayOut.tpl'}
{block name="moonth_search"}
    {include file='modules/indicatorspanel/Objets/MonthSearchOption.tpl'}
{/block}
{block name="view_scale"}
    {include file='modules/indicatorspanel/Objets/ViewScaleOption.tpl'}
{/block}
{block name="created_block"}
    {if $IS_MOTHER}
        <div class="pull-right " style="padding-left: 15px;vertical-align: bottom;" align="center">
            <a href="#crearblock" data-toggle="modal" class="btn btn-success btn-sm"
               onclick="jQuery('.md-overlay').css({ldelim}opacity: 1, visibility: 'visible'{rdelim});jQuery('#type').val('');jQuery('#appBlockNew').show();jQuery('#titleBlock').html('{$MODSTRING.LBL_CREATE_BLOCK}');">{$MODSTRING.LBL_CREATE_BLOCK}</a>
        </div>
    {/if}
{/block}
{block name="detail_view"}
    {include file='modules/indicatorspanel/Objets/IndicatorTableBox.tpl'}
{/block}
