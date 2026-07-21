{extends file='Home/ActionTabs/Base/TabDeskLayout.tpl'}
{strip}
    {* Headr*}
    {block name = "table_header"}
        {foreach $ACTION_TABLE_HEADER as  $label => $data}
            <th class="{$data.class} table-th-align table-th-width table-th-vertical"
                scope="col"
                colspan="{$data.colspan}">
                <div class="table-th-flex">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link">
                            <span>{$label}</span>
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {* Body*}
    {block name = "table_body"}
        {if $ROWS neq NULL}
            {foreach $ROWS as $row}
                <tr>
                    {html_row_table fields=$ROWS_FIELDS row_data=$row url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}
{/strip}