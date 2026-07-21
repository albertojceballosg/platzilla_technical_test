<div class="col-md-12" id="divPick">
    <div class="col-md-6">
        <label class="control-label">{$MOD.LBL_MOSTRAR_CAMPOS}</label>        
        <input value="&nbsp;&rsaquo;&rsaquo;&nbsp;" type="button" class="btn btn-primary" style="width:100%" onClick="addColumn()">         
        <select multiple="" class="form-control" id="availList" name="availList" size="6">
            {foreach item=entries key=id from=$FIELDS_VISIBLES}                
                {foreach item=value from=$entries.field name=fields}                    
                    {if $FIELD_SELECTED eq $value.fieldselect}
                        {php}continue;{/php}
                    {else}
                        <option value="{$value.fieldselect}">{$value.label}</option>    
                    {/if}                    
                {/foreach}
            {/foreach}
        </select>
    </div>

    <div class="col-md-6">
        <label class="control-label">{$MOD.LBL_OCULTAR_CAMPOS}</label>
        <input type="button" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" class="btn btn-danger" onClick="delColumn()" style="width:100%">

        <select multiple="" class="form-control" id="notAvailList" name="notAvailList" size="6">
            {foreach item=entries key=id from=$FIELDS_NO_VISIBLES}
                {foreach item=value from=$entries.field name=fields}
                    <option value="{$value.fieldselect}">{$value.label}</option>    
                {/foreach}
            {/foreach}
            
        </select>
    </div>
</div>