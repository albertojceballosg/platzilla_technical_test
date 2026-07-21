{extends file='Home/ActionTabs/Base/ActionTabsLayout.tpl'}
{strip}
    {block name="css"}
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/list-view.css?v=1.1"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css?v=1.0"/>
        <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css?v=1.0"/>
    {/block}

    {* To be process module Orders *}
    {block name = "action_buttons-pedidos"}
        {assign var="fromModule" value='pedidos'}
        {assign var="totalRecords" value=$ORDERS_TOTAL_ROWS}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$ORDERS_TAB_ID}
        {assign var='ajaxFuntion' value='ORDERS_TO_PROCESSED'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="otherButtonTitle" value=''}
        {assign var="otherButtonToltip" value='Buscar en el calendario'}
        {assign var="otherButtonAction" value='DataViewUtils.showInCalendar'}
        {assign var="otherButtonClass" value='btn btn-primary'}
        {assign var="otherButtonIcon" value='fa-calendar'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}
    {block name = "table_header-pedidos"}
        {foreach $ORDERS_TABLE_HEADER as  $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align}"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                            <span>{$label}</span>
                            {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {block name = "table_body-pedidos"}
        {if $ORDERS_TO_PROCESSED neq NULL}
            {foreach $ORDERS_TO_PROCESSED as $order}
                <tr>
                    {html_row_table fields=$ORDERS_TABLE_ROW row_data=$order url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}
    {block name="page-data-pedidos"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$ORDERS_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-pedidos"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$ORDERS_TAB_ID}" class="pagination">
                {if $ORDERS_PAGER neq NULL}
                    {$ORDERS_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}
            </ul>
        </div>
    {/block}

    {* To be process module Issues *}
    {block name = "action_buttons-incidencias"}
        {assign var="fromModule" value='incidencias'}
        {assign var="totalRecords" value=$ISSUES_TOTAL_ROWS}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$ISSUES_TAB_ID}
        {assign var='ajaxFuntion' value='ISSUES_TO_PROCESSED'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="otherButtonTitle" value=''}
        {assign var="otherButtonToltip" value='Buscar en el calendario'}
        {assign var="otherButtonAction" value='DataViewUtils.showInCalendar'}
        {assign var="otherButtonClass" value='btn btn-primary'}
        {assign var="otherButtonIcon" value='fa-calendar'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}
    {block name = "table_header-incidencias"}
        {foreach $ISSUES_TABLE_HEADER as  $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align};"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                            <span>{$label}</span>
                            {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {block name = "table_body-incidencias"}
        {*$ISSUES_TO_PROCESSED|@var_dump*}
        {if $ISSUES_TO_PROCESSED neq NULL}
            {foreach $ISSUES_TO_PROCESSED as $issue}
                <tr>
                    {html_row_table fields=$ISSUES_TABLE_ROW row_data=$issue url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}
    {block name="page-data-incidencias"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$ISSUES_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-incidencias"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$ISSUES_TAB_ID}" class="pagination">
                {if $ISSUES_PAGER neq NULL}
                    {$ISSUES_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}
            </ul>
        </div>
    {/block}

    {* To be process module  Opportunities *}
    {block name = "action_buttons-oportunidades"}
        {assign var="fromModule" value='oportunidades'}
        {assign var="totalRecords" value=$OPPORTUNITIES_TOTAL_ROWS}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$OPPORTUNITIES_TAB_ID}
        {assign var='ajaxFuntion' value='OPPORTUNITIES_TO_PROCESSED'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="otherButtonTitle" value=''}
        {assign var="otherButtonToltip" value='Buscar en el calendario'}
        {assign var="otherButtonAction" value='DataViewUtils.showInCalendar'}
        {assign var="otherButtonClass" value='btn btn-primary'}
        {assign var="otherButtonIcon" value='fa-calendar'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}
    {block name = "table_header-oportunidades"}
        {foreach $OPPORTUNITIES_TABLE_HEADER as  $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align};"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                            <span>{$label}</span>
                            {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {block name = "table_body-oportunidades"}
        {if $OPPORTUNITIES_TO_PROCESSED neq NULL}
            {foreach $OPPORTUNITIES_TO_PROCESSED as $row}
                <tr>
                    {html_row_table fields=$OPPORTUNITIES_TABLE_ROW row_data=$row url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}
    {block name="page-data-oportunidades"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$OPPORTUNITIES_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-oportunidades"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$OPPORTUNITIES_TAB_ID}" class="pagination">
                {if $OPPORTUNITIES_PAGER neq NULL}
                    {$OPPORTUNITIES_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}
            </ul>
        </div>
    {/block}
    {block name="js"}
        <script type="text/javascript" src="themes/centaurus/js/modernizr.custom.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/snap.svg-min.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/notificationFx.js"></script>
        <script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="include/js/list-view.js?v=1.1"></script>
        <script type="text/javascript" src="include/js/mass-actions-utils.js?v=1.1"></script>
        <script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js?v=1.0"></script>
    {/block}
{/strip}