{strip}
    {if $ROWS neq NULL}
        {foreach $ROWS as $row}
            <tr>
                {html_row_table fields=$ROWS_FIELDS row_data=$row url_avatar=$URL_AVATARS}
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="8" class="text-center" style="padding: 20px;">
                <i class="fa fa-info-circle"></i> No hay datos para mostrar
            </td>
        </tr>
    {/if}
{/strip}
