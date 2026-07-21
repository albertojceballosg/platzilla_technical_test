        {assign var="indexType" value=0}
        {assign var="indexOperator" value=0}
        {foreach from=$OPERATOR_GROUP key=gIndex item=gOper}
            <div class="calculateGroup">
                <div class="row form-group">
                    <label for="description" class="col-md-2 control-label"><span style="color: red;">&nbsp;{if $GROUP_NAME[$gIndex] neq ''}{$GROUP_NAME[$gIndex]}{else}a{/if} = (&nbsp;</span></label>
                    <div class="col-md-4">
                        <select class="form-control" data-position="first" name="typeElement[]" required="" onchange="calculatedGridFieldUtils.selectElement(this)">
                            <option value="" {if $TYPE_ELEMENT[$indexType] eq ''} selected {/if}>{$MOD.SELECT_TYPE}</option>
                            {foreach from=$MOD.CALCULATED_GRID_TYPE key=k item=v}
                                {if $k neq 'r'}
                                    <option value="{$k}"  {if $TYPE_ELEMENT[$indexType] eq $k} selected {/if} >{$v}</option>
                                {elseif $gIndex gte 1}
                                    <option value="{$k}"  {if $TYPE_ELEMENT[$indexType] eq $k} selected {/if} >{$v}</option>
                                {/if}
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-4  firstElement{if $TYPE_ELEMENT[$indexType] neq 'c'} hide{/if} ">
                        {assign var="fieldData" value= "@"|explode:$SUBFIELD_NAME[$indexType]}
                        <select class="form-control" name="subFieldId[]" >
                            <option value="" {if $SUBFIELD_NAME[$indexType] eq ''} selected {/if}>{$MOD.SELECT_COLUMNS}</option>
                            {if $SUBFIELD|@count gt 0}
                                {foreach $SUBFIELD as $row}
                                    <option value="{$row.name}@{$row.uitype}" {if $fieldData[0] eq $row.name} selected {/if} >{$row.label}</option>
                                {/foreach}
                            {else}
                                <option value="">{$MOD.NOT_COLUMNS}</option>
                            {/if}
                        </select>
                    </div>
                    <div class="col-md-4 firstReference{if $TYPE_ELEMENT[$indexType] neq 'r'} hide{/if} ">
                        <select class="form-control" name="calculatedRefrence[]" >
                            <option value="" selected>{$MOD.SELECT_TYPE}</option>
                            {if $gIndex gt 0}
                            {foreach from=$GROUP_NAME key=k item=v name=referen}
                                {if $v neq '' && $smarty.foreach.referen.iteration lte $gIndex}
                                    <option value="{$v}" {if $REFERENCE[$indexType] eq $v} selected {/if} >{$v}</option>
                                {/if}
                            {/foreach}
                            {/if}
                        </select>
                    </div>

                    <div class="col-md-4 firstValue{if $TYPE_ELEMENT[$indexType] neq 'v'} hide{/if} ">
                        <input type="text" class="form-control operandoValue"   name="elemValue[]"  value="{$ELEMENT_VALUE[$indexType]}"  />
                    </div>
                </div>
                <div class="row form-group">
                    <label for="description" class="col-md-2 control-label">&nbsp;</label>
                    <div class="col-md-4">
                        <select class="form-control" name="operator[]">
                            {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                                {if $k eq 'x'}{continue}{/if}
                                <option value="{$k}" {if $OPERATOR[$indexOperator] eq $k} selected {/if} >{$v}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-4">&nbsp;</div>
                    {$indexOperator = $indexOperator+1}
                </div>
                {$indexType = $indexType+1}
                <div class="row form-group">
                    <label for="description" class="col-md-2 control-label"><span style="color: red;">*</span>Tipo:</label>
                    <div class="col-md-4">
                        <select class="form-control " data-position="second" name="typeElement[]" required="" onchange="calculatedGridFieldUtils.selectElement(this)">
                            <option value="" {if $TYPE_ELEMENT[$indexType] eq ''} selected {/if}>{$MOD.SELECT_TYPE}</option>
                            {foreach from=$MOD.CALCULATED_GRID_TYPE key=k item=v}
                                {if $k neq 'r'}
                                    <option value="{$k}" {if $TYPE_ELEMENT[$indexType] eq $k} selected {/if}  >{$v}</option>
                                {elseif $gIndex gte 1}
                                    <option value="{$k}"  {if $TYPE_ELEMENT[$indexType] eq $k} selected {/if} >{$v}</option>
                                {/if}
                            {/foreach}
                        </select>

                    </div>
                    <div class="col-md-4  secondElement{if $TYPE_ELEMENT[$indexType] neq 'c'} hide{/if} ">
                        {assign var="fieldData" value= "@"|explode:$SUBFIELD_NAME[$indexType]}
                        <select class="form-control" name="subFieldId[]">
                            <option value="" {if $SUBFIELD_NAME[$indexType] eq ''} selected {/if}>{$MOD.SELECT_COLUMNS}</option>
                            {if $SUBFIELD|@count gt 0}
                                {foreach $SUBFIELD as $row}
                                    <option value="{$row.name}@{$row.uitype}" {if $fieldData[0] eq $row.name} selected {/if}  >{$row.label}</option>
                                {/foreach}
                            {else}
                                <option value="">{$MOD.NOT_COLUMNS}</option>
                            {/if}
                        </select>
                    </div>
                    <div class="col-md-4 secondReference{if $TYPE_ELEMENT[$indexType] neq 'r'} hide{/if} ">
                        <select class="form-control" name="calculatedRefrence[]" >
                            <option value="" selected>{$MOD.SELECT_TYPE}</option>
                            {if $gIndex gt 0}
                                {foreach from=$GROUP_NAME key=k item=v name=referen}
                                    {if $v neq '' && $smarty.foreach.referen.iteration lte $gIndex}
                                        <option value="{$v}" {if $REFERENCE[$indexType] eq $v} selected {/if} >{$v}</option>
                                    {/if}
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                    
                    <div class="col-md-4  secondValue{if $TYPE_ELEMENT[$indexType] neq 'v'} hide{/if} ">
                        <input type="text" class="form-control operandoValue"  name="elemValue[]" value="{$ELEMENT_VALUE[$indexType]}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-md-2 control-label"><span style="color: red;">)</span></label>
                    <div class="col-md-4">
                        <select class="form-control update-equqtion" id="operator1" name="operatorGroup[]" onchange="calculatedGridFieldUtils.setEquation('formGridCalculatedField')">
                            {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                                {if $k eq 'x'}{continue}{/if}
                                <option value="{$k}"   {if $gOper eq $k} selected {/if}  >{$v}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button  type="button" class="btn btn-success btn-sm addButton" onclick="calculatedGridFieldUtils.addCalculatedGroup()"><i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-default btn-sm removeButton {if $gIndex eq 0} hide {/if}" data-control="{if $GROUP_NAME[$gIndex] neq ''}{$GROUP_NAME[$gIndex]}{else}a{/if}" onclick="calculatedGridFieldUtils.removeCalculatedGroup(this)"><i class="fa fa-minus" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            {$indexType = $indexType+1}
        {/foreach}