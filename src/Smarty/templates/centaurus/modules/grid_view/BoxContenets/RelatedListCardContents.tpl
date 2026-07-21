<div class="clearfix">
    <div class="main-box-body clearfix">
        <div class="pull-left">
            <i class="fa fa-spinner fa-spin"
               id="loading_{$MODULE}_{$RELATED_MODULE|@getTranslatedString:$RELATED_MODULE}"
               style="padding: 0px; display: none;"></i>
        </div>
        <div class="table-responsive related-list-card">
            <table class="table related-list-card-table">
                <thead>
                <tr>
                    {foreach key=index item=_HEADER_FIELD from=$RELATEDLISTDATA.header}
                        <th>{$_HEADER_FIELD}</th>
                    {/foreach}
                    <th style="width: 2em;"></th>
                </tr>
                </thead>
                <tbody data-type="card" data-action="{$ACTION}" data-relation="{$RELATED_ID}" id="{$TARGET}">
                {foreach key=_RECORD_ID item=_RECORD from=$RELATEDLISTDATA.entries}
                    <tr class="related-list-card-row" bgcolor={$_RECORD.color}>
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
                            {if $RELATEDLISTDATA.delete_btn}
                            <button type="button" class="btn btn-danger btn-icon btn-xs" data-current-record="{$ID}"
                                    data-current-module="{$MODULE}" data-related-module="{$RELATED_MODULE}"
                                    data-related-record="{$_RECORD_ID}" title="Eliminar relación"
                                    onclick="RelatedModuleModalUtils.unrelateRecord (this);"><i
                                        class="fa fa-trash-o"></i></button>
                            {else}
                                &nbsp;
                            {/if}
                        </td>
                    </tr>
                    {foreachelse}
                    <tr>
                        <td><i>
                                <small>{$APP.LBL_NONE_INCLUDED}</small>
                            </i></td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
