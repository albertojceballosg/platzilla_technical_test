<div id="Group-{$GROUP_NUMBER}" class="cs-condicion-group">
<div class="form-group">
    <label for="typeFirstElement" class="col-md-2 control-label"><span style="color: red;fonsize:14px">&nbsp;(&nbsp;</span></label>
    <div class="col-md-4 cs-dv-typeFirst">
        <select class="form-control type-data"  name="typeFirstElement[]" data-template="first" onchange="CSUtils.selectOperator(this)">
            {foreach from=$MOD.CALCULATED_SYSTEM_TYPE key=k item=v}
                {if $k ne 'r'}
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                {elseif ($GROUP_NUMBER gt 1) }
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                {/if}
            {/foreach}
        </select>
        <span class="help-block cs-typeFirst"></span>
    </div>

    <div class="col-md-4 cs-dv-firstField">
        <select class="form-control"  name="firstField[]">
            <option value="" selected >Seleccionar</option>
            {if isset($MODULE_FIELD)}
                {foreach $MODULE_FIELD as $row}
                    <option value="{$row.fieldname}" {if $field eq $row.fieldname} selected {/if} >{$row.label}</option>
                {/foreach}
            {/if}
        </select>
        <span class="help-block cs-firstField"></span>
    </div>
    <div class="col-md-4 hide cs-dv-firstElement">
        <select class="form-control search-element"  name="firstElement[]" style="width: 100%">
            <option value="" selected ></option>
            {foreach $ACF as $row}
                <option value="{$row->getElementName ()}">{$row->getName ()}</option>
            {/foreach}
        </select>
        <span class="help-block cs-firstElement"></span>
    </div>
    <div class="col-md-4 hide cs-dv-firstValue">
        <input type="text" class="form-control"   name="firstValue[]" onkeydown="CSUtils.checkValue(event)" />
        <span class="help-block cs-firstValue"></span>
    </div>
    <div class="col-md-4 hide cs-dv-firstReference">
        <select class="form-control group-referen" id="firstReference" name="firstReference[]" >
            <option value="" selected >{$MOD.SELECT_FILTER}</option>
            <option value="a">a</option>
        </select>
        <span class="help-block cs-firstReference"></span>
    </div>
</div>

<div class="form-group">
    <label for="description" class="col-md-2 control-label">&nbsp;</label>
    <div class="col-md-4">
        <select class="form-control" id="operator" name="operator[]" onchange="CSUtils.setOperator(this)">
            {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                <option value="{$k}" >{$v}</option>
            {/foreach}
        </select>

    </div>
</div>

<div class="form-group">
    <label for="description" class="col-md-2 control-label"><span style="color: red;">*</span>Tipo:</label>
    <div class="col-md-4 cs-dv-typeSecond">
        <select class="form-control "  name="typeSecondElement[]" data-template="second" onchange="CSUtils.selectOperator(this)">
            {foreach from=$MOD.CALCULATED_SYSTEM_TYPE key=k item=v}
                {if $k ne 'r'}
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                {elseif ($GROUP_NUMBER gt 1) }
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                {/if}
            {/foreach}
        </select>
        <span class="help-block cs-typeSecond"></span>
    </div>
    <div class="col-md-4 cs-dv-secondField">
        <select class="form-control" name="secondField[]">
            <option value="" selected >Seleccionar</option>
            {if isset($MODULE_FIELD)}
                {foreach $MODULE_FIELD as $row}
                    <option value="{$row.fieldname}" {if $field eq $row.fieldname} selected {/if} >{$row.label}</option>
                {/foreach}
            {/if}
        </select>
        <span class="help-block cs-secondField"></span>
    </div>
    <div class="col-md-4 hide cs-dv-secondElement">
        <select class="form-control search-element"   name="secondElement[]" style="width: 100%">
            <option value=""  selected >{$MOD.SELECT_FILTER}</option>
            {foreach $ACF as $row}
                <option value="{$row->getElementName ()}">{$row->getName ()}</option>
            {/foreach}
        </select>
        <span class="help-block cs-secondElemen"></span>
    </div>
    <div class="col-md-4 hide cs-dv-secondValue">
        <input type="text" class="form-control"  name="secondValue[]" onkeydown="CSUtils.checkValue(event)"   />
        <span class="help-block cs-secondValue"></span>
    </div>
    <div class="col-md-4 hide cs-dv-secondReference">
        <select class="form-control group-referen"  name="secondReference[]">
            <option value="" selected >{$MOD.SELECT_FILTER}</option>
            <option value="a" >a</option>
        </select>
        <span class="help-block cs-secondReference"></span>
    </div>
    <span style="color: red;">)</span>
</div>
<div class="form-group join-condition hide form-control" style="background-color: #FAFAFA; padding: 6px 0px; height: 50px;width: 76%; margin-left: 10%">
   <label for="description" class="col-md-2 control-label"></label>
    <div class="col-md-4">
        <select class="form-control"  name="operatorGroup[]" onchange="CSUtils.upDateCalculatedGroup()">
            {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS_GROUP key=k item=v}
                <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
            {/foreach}
        </select>
    </div>
    <div class="col-md-4">
        <button type="button" class="btn btn-default btn-sm removeButton" onclick="CSUtils.eraseGroup(this)"><i class="fa fa-minus" aria-hidden="true"></i>
        </button>

    </div>
</div>
</div>
