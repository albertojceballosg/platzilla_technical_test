{foreach key=_RECORD_ID item=_RECORD from=$RELATEDLISTDATA.entries}
    <tr bgcolor={$_RECORD.color}>
        {assign var="cant" value="0"}
        {foreach key=index item=_RECORD_DATA from=$_RECORD.records}
            {assign var="cant" value=$cant+1}
            {* vtlib customization: Trigger events on listview cell *}
            <td {if $RELATEDLISTDATA.header|@count eq $cant }nowrap {/if}>
                <small>{$_RECORD_DATA}</small>
            </td>
            {* END *}
        {/foreach}
        <td>
            <button type="button" class="btn btn-danger btn-icon btn-xs" data-current-record="{$ID}"
                    data-current-module="{$MODULE}" data-related-module="{$RELATED_MODULE}"
                    data-related-record="{$_RECORD_ID}" title="Eliminar relación"
                    onclick="RelatedModuleModalUtils.unrelateRecord (this);"><i
                        class="fa fa-trash-o"></i></button>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td><i>
                <small>{$APP.LBL_NONE_INCLUDED}</small>
            </i></td>
    </tr>
{/foreach}
