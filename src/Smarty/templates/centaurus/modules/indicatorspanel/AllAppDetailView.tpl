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
            <a href="#crearblock" data-toggle="modal" class="btn btn-success btn-sm {if $APPCODE eq 'all'}hide{/if}"
               onclick="jQuery('.md-overlay').css({ldelim}opacity: 1, visibility: 'visible'{rdelim});jQuery('#type').val('');jQuery('#appBlockNew').show();jQuery('#titleBlock').html('{$MODSTRING.LBL_CREATE_BLOCK}');">{$MODSTRING.LBL_CREATE_BLOCK}</a>
        </div>
    {/if}
{/block}
{block name="detail_view"}
    {foreach $APPLICATIONS as $keyApp => $itemApp}
        {assign var='BLOCKS' value=$ALL_BOX_SCORE[$keyApp][1]}
        {assign var='BOX_SCORE' value=$ALL_BOX_SCORE[$keyApp][0]}
        {assign var='CALCULATIONS' value=$ALL_BOX_SCORE[$keyApp][2]}
        {assign var='RECORD' value=$ALL_BOX_SCORE[$keyApp][3]}
        {if count($BLOCKS) > 0}
            {include file='modules/indicatorspanel/Objets/IndicatorTableBox.tpl'}
        {/if}
    {/foreach}
{/block}
