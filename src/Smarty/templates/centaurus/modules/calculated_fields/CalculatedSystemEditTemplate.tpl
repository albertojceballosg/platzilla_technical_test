
{assign var="Groups" value=$CALCULATED_DATA['typeFirstElement']}
{assign var="firstField" value=$CALCULATED_DATA['firstField']}
{assign var="firstElement" value=$CALCULATED_DATA['firstElement']}
{assign var="firstValue" value=$CALCULATED_DATA['firstValue']}
{assign var="firstReference" value=$CALCULATED_DATA['firstReference']}
{assign var="operator" value=$CALCULATED_DATA['operator']}
{assign var="typeSecondElement" value=$CALCULATED_DATA['typeSecondElement']}
{assign var="secondField" value=$CALCULATED_DATA['secondField']}
{assign var="secondElement" value=$CALCULATED_DATA['secondElement']}
{assign var="secondValue" value=$CALCULATED_DATA['secondValue']}
{assign var="secondReference" value=$CALCULATED_DATA['secondReference']}
{assign var="operatorGroup" value=$CALCULATED_DATA['operatorGroup']}
{assign var="totalGroup" value=$CALCULATED_DATA['typeFirstElement']|@count}
{assign var="labels" value='abcdefghijklmnopqrstuvwxyz'}

{section name=CSGroup start=0 loop=$totalGroup}
{$GROUP_NUMBER = $smarty.section.CSGroup.index}
{$OPERID       = $Groups[$smarty.section.CSGroup.index]}
{$field        = $firstField[$smarty.section.CSGroup.index]}
{assign var="fieldData" value= "@"|explode:$field}
{$element      = $firstElement[$smarty.section.CSGroup.index]}
{$value        = $firstValue[$smarty.section.CSGroup.index]}
{$reference    = $firstReference[$smarty.section.CSGroup.index]}
{$operElement  = $operator[$smarty.section.CSGroup.index]}
<div id="Group-{$GROUP_NUMBER + 1}" class="cs-condicion-group">
<div class="form-group">}
    <label for="typeFirstElement" class="col-md-2 control-label"><span style="color: red;fonsize:14px">&nbsp;(&nbsp;</span></label>
    <div class="col-md-4 cs-dv-typeFirst">
        <select class="form-control type-data"  name="typeFirstElement[]" data-template="first" onchange="CSUtils.selectOperator(this)">
            {foreach from=$MOD.CALCULATED_SYSTEM_TYPE key=k item=v}
                {if $k ne 'r'}
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                {elseif ($GROUP_NUMBER gt 0) }
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                {/if}
            {/foreach}
        </select>
        <span class="help-block cs-typeFirst"></span>
    </div>
    <div class="col-md-4 cs-dv-firstField {if $field eq ''} hide{/if}">
        <select class="form-control"  name="firstField[]">
            <option value="" selected >Seleccionar</option>
            {assign var="lastModule" value=''}
            {foreach $MODULE_FIELD as $row}
            {assign var="optionValue" value="{$row.tablename}{'.'}{$row.fieldname}"}
            {if $lastModule eq ''}
            <optgroup label="{$row.module}">
                {$lastModule = $row.module}
                <option value="{$row.tablename}.{$row.fieldname}@{$row.uitype}" {if $fieldData[0] eq $optionValue} selected {/if} >{$row.label}</option>
                {elseif $row.module eq $lastModule}
                <option value="{$row.tablename}.{$row.fieldname}@{$row.uitype}" {if $fieldData[0] eq $optionValue} selected {/if} >{$row.label}</option>
                {else}
            </optgroup>
            <optgroup label="{$row.module}">
                {$lastModule = $row.module}
                <option value="{$row.tablename}.{$row.fieldname}@{$row.uitype}" {if $fieldData[0] eq $optionValue} selected {/if} >{$row.label}</option>
                {/if}
            {/foreach}
        </select>
        <span class="help-block cs-firstField"></span>
    </div>

    <div class="col-md-4 cs-dv-firstElement {if $element eq ''} hide{/if}">

        <select class="form-control search-element"  name="firstElement[]" style="width: 100%">
            <option value="" {if $element eq ''}selected{/if} ></option>
            {foreach $ACF as $row}
                <option value="{$row->getElementName ()}" {if $element eq $row->getElementName () }selected="selected" {/if} >{$row->getName ()}</option>
            {/foreach}
        </select>
        <span class="help-block cs-firstElement"></span>
    </div>

    <div class="col-md-4 {if $value eq ''} hide{/if} cs-dv-firstValue">
        <input type="text" class="form-control"   name="firstValue[]"  value="{$value}"       onkeydown="CSUtils.checkValue(event)" />
        <span class="help-block cs-firstValue"></span>
    </div>
    <div class="col-md-4 {if $reference eq ''} hide{/if} cs-dv-firstReference">
        <select class="form-control group-referen" id="firstReference" name="firstReference[]" >
            <option value="" {if $reference eq ''}selected{/if}>{$MOD.SELECT_FILTER}</option>
            <option value="{$reference}" {if $reference neq ''}selected{/if} >grupo {$reference}</option>
        </select>
        <span class="help-block cs-firstRreference"></span>
    </div>
</div>

<div class="form-group">
    <label for="description" class="col-md-2 control-label">&nbsp;</label>
    <div class="col-md-4">
        <select class="form-control" id="operator" name="operator[]"  onchange="CSUtils.setOperator(this)">
            {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                <option value="{$k}" {if $operElement eq $k} selected {/if} >{$v}</option>
            {/foreach}
        </select>

    </div>
</div>
{$OPERID        = $typeSecondElement[$smarty.section.CSGroup.index]}
{$field         = $secondField[$smarty.section.CSGroup.index]}
{assign var="fieldData" value= "@"|explode:$field}
{$element       = $secondElement[$smarty.section.CSGroup.index]}
{$value         = $secondValue[$smarty.section.CSGroup.index]}
{$reference     = $secondReference[$smarty.section.CSGroup.index]}
{$operGroup     = $operatorGroup[$smarty.section.CSGroup.index]}
<div class="form-group">
    <label for="description" class="col-md-2 control-label"><span style="color: red;">*</span>Tipo:</label>
    <div class="col-md-4 cs-dv-typeSecond">
        <select class="form-control "  name="typeSecondElement[]" data-template="second" onchange="CSUtils.selectOperator(this)">
            {foreach from=$MOD.CALCULATED_SYSTEM_TYPE key=k item=v}
                {if $k ne 'r'}
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if} {if ($operElement eq 'x') && ($k neq 'v')} disabled="disabled" {/if}>{$v}</option>
                {elseif ($GROUP_NUMBER gt 0) }
                    <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if} {/if}{if ($operElement eq 'x') && ($k neq 'v')} disabled="disabled" {/if}>{$v}</option>
                {/if}
            {/foreach}
        </select>
        <span class="help-block cs-typeSecond"></span>
    </div>
    <div class="col-md-4 cs-dv-secondField  {if $field eq ''} hide{/if}">
        <select class="form-control " name="secondField[] ">
            <option value="" selected >Seleccionar</option>
            {assign var="lastModule" value=''}
            {foreach $MODULE_FIELD as $row}
            {assign var="optionValue" value="{$row.tablename}{'.'}{$row.fieldname}"}
            {if $lastModule eq ''}
            <optgroup label="{$row.module}">
                {$lastModule = $row.module}
                <option value="{$row.tablename}.{$row.fieldname}@{$row.uitype}" {if $fieldData[0] eq $optionValue} selected {/if} >{$row.label}</option>
                {elseif $row.module eq $lastModule}
                <option value="{$row.tablename}.{$row.fieldname}@{$row.uitype}" {if $fieldData[0] eq $optionValue} selected {/if} >{$row.label}</option>
                {else}
            </optgroup>
            <optgroup label="{$row.module}">
                {$lastModule = $row.module}
                <option value="{$row.tablename}.{$row.fieldname}@{$row.uitype}" {if $fieldData[0] eq $optionValue} selected {/if} >{$row.label}</option>
                {/if}
            {/foreach}
        </select>
        <span class="help-block cs-secondField"></span>
    </div>
    <div class="col-md-4 cs-dv-secondElement {if $element eq ''} hide{/if}">
        <select class="form-control search-element"  name="secondElement[]" style="width: 100%">
            <option value="" {if $element eq ''}selected{/if} ></option>
            {foreach $ACF as $row}
                <option value="{$row->getElementName ()}" {if $element eq $row->getElementName () }selected="selected" {/if} >{$row->getName ()}</option>
            {/foreach}
        </select>
        <span class="help-block cs-secondElemen"></span>
    </div>
    <div class="col-md-4 {if $value eq ''} hide{/if} cs-dv-secondValue">
        <input type="text" class="form-control"  name="secondValue[]" value="{$value}" {if $operElement eq 'x'} readonly {/if} onkeydown="CSUtils.checkValue(event)"   />
        <span class="help-block cs-secondValue"></span>
    </div>
    <div class="col-md-4 {if $reference eq ''} hide {/if} cs-dv-secondReference">
        <select class="form-control group-referen"  name="secondReference[]">
            <option value="" {if $reference eq ''}selected{/if}>{$MOD.SELECT_FILTER}</option>
            <option value="{$reference}"{$reference}" {if $reference neq ''}selected{/if}>grupo {$reference}</option>
        </select>
        <span class="help-block cs-secondReference"></span>
    </div>
    <span style="color: red;">)</span>
</div>
<div class="form-group join-condition form-control {if $smarty.section.CSGroup.index gt ($totalGroup-2)}hide{/if} " style="background-color: #FAFAFA; padding: 6px 0px; height: 50px;width: 76%; margin-left: 10%">
    <label for="description" class="col-md-2 control-label"></label>
    <div class="col-md-4">
        <select class="form-control"  name="operatorGroup[]" onchange="CSUtils.upDateCalculatedGroup()">
            {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                <option value="{$k}" {if $operGroup eq $k} selected {/if}>{$v}</option>
            {/foreach}
        </select>
    </div>
    <div class="col-md-4">
        <button type="button" class="btn btn-default btn-sm removeButton" onclick="CSUtils.eraseGroup(this)"><i class="fa fa-minus" aria-hidden="true"></i>
        </button>

    </div>
</div>
<hr>
</div>
{/section}