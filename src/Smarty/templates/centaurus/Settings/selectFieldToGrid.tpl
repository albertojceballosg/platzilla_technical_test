{if $SELECT_FIELD}
<select name="{$SELECT_FIELD_NAME}[]" id="{$SELECT_FIELD_ID}" class="form-control" style="min-width:80px; margin: 0px" >
    {html_options options=$SELECT_FIELD}
</select>
{else}
    <input autocomplete="off" value="" name="{$SELECT_FIELD_NAME}[]" id="{$SELECT_FIELD_ID}" class="form-control" style="min-width:80px" type="text">
{/if}