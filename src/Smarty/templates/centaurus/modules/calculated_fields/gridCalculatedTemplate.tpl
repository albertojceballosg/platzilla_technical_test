        <div class="calculateGroup">
            <div class="row form-group">
                <label for="description" class="col-md-2 control-label"><span style="color: red;">&nbsp;a = (&nbsp;</span></label>
                <!-- tipo de operando -->
                <div class="col-md-4">
                    <select class="form-control" data-position="first" name="typeElement[]" required="" onchange="calculatedGridFieldUtils.selectElement(this)">
                        <option value="" selected >{$MOD.SELECT_TYPE}</option>
                        {foreach from=$MOD.CALCULATED_GRID_TYPE key=k item=v}
                            {if $k neq 'r'}
                                <option value="{$k}">{$v}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
                <!--  operando  -->
                <div class="col-md-4  firstElement">
                    <select class="form-control" name="subFieldId[]" >
                        <option value=""  selected >{$MOD.SELECT_COLUMNS}</option>
                        {if $SUBFIELD|@count gt 0}
                        {foreach $SUBFIELD as $row}
                            <option value="{$row.name}@{$row.uitype}">{$row.label}</option>
                        {/foreach}
                        {else}
                            <option value="">{$MOD.NOT_COLUMNS}</option>
                        {/if}
                    </select>
                </div>
                <div class="col-md-4 firstReference hide">
                    <select class="form-control" name="calculatedRefrence[]" >
                        <option value="" selected >{$MOD.SELECT_TYPE}</option>
                    </select>
                </div>
                <div class="col-md-4 firstValue hide">
                    <input type="text" class="form-control operandoValue"   name="elemValue[]"    />
                </div>
            </div>
            <!-- operador -->
            <div class="row form-group">
                <label for="description" class="col-md-2 control-label">&nbsp;</label>
                <div class="col-md-4">
                    <select class="form-control" name="operator[]">
                        {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                            {if $k eq 'x'}{continue}{/if}
                            <option value="{$k}" >{$v}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-md-4">&nbsp;</div>
            </div>

            <div class="row form-group">
                <label for="description" class="col-md-2 control-label"><span style="color: red;">*</span>Tipo:</label>
                <!-- tipo de operando --->
                <div class="col-md-4">
                    <select class="form-control " data-position="second" name="typeElement[]" required="" onchange="calculatedGridFieldUtils.selectElement(this)">
                        <option value=""  selected >{$MOD.SELECT_TYPE}</option>
                        {foreach from=$MOD.CALCULATED_GRID_TYPE key=k item=v}
                            {if $k neq 'r'}
                                <option value="{$k}">{$v}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
                <!--  operando  -->
                <div class="col-md-4  secondElement">
                    <select class="form-control" name="subFieldId[]">
                        <option value="" selected >{$MOD.SELECT_COLUMNS}</option>
                        {if $SUBFIELD|@count gt 0}
                            {foreach $SUBFIELD as $row}
                                <option value="{$row.name}@{$row.uitype}">{$row.label}</option>
                            {/foreach}
                        {else}
                            <option value="">{$MOD.NOT_COLUMNS}</option>
                        {/if}
                    </select>
                </div>
                <div class="col-md-4 secondReference hide">
                    <select class="form-control" name="calculatedRefrence[]" >
                        <option value="" selected>{$MOD.SELECT_TYPE}</option>
                    </select>
                </div>
                <div class="col-md-4  secondValue hide">
                    <input type="text" class="form-control operandoValue"  name="elemValue[]" />
                </div>
            </div>
            <!-- Cierrre del  grupo -->
            <div class="form-group">
                <label for="description" class="col-md-2 control-label"><span style="color: red;">)</span></label>
                <div class="col-md-4">
                    <select class="form-control update-equqtion" id="operator1" name="operatorGroup[]" onchange="calculatedGridFieldUtils.setEquation('formGridCalculatedField')">
                        {foreach from=$MOD.CALCULATED_SYSTEM_OPERATIONS key=k item=v}
                            {if $k eq 'x'}{continue}{/if}
                            <option value="{$k}"  {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-md-4">
                    <button  type="button" class="btn btn-success btn-sm addButton" onclick="calculatedGridFieldUtils.addCalculatedGroup()"><i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm removeButton hide" data-control="a" onclick="calculatedGridFieldUtils.removeCalculatedGroup(this)"><i class="fa fa-minus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>