{strip}
    {assign var='groupId' value='__GROUP_ID__'}
    <div class="condition-group list-group filter_goup" id="group-{$groupId}"  data-id="{$groupId}">
        <div class="condition-group-header list-group-item">
            <div class="row">
                <div class="col-xs-4">Variable</div>
                <div class="col-xs-2">Operador</div>
                <div class="col-xs-4">Valor</div>
                <div class="col-xs-1"></div>
                <div class="col-xs-1 text-right">
                    <button type="button" class="btn btn-danger" onclick="CFUtils.eraseFilterGroup (this);" title="Eliminar grupo de condiciones"><i class="fa fa-trash-o"></i></button>
                </div>
            </div>
        </div>
        <div class="condition-group-body list-group-item">
            <ul class="list-group conditions">
            </ul>
        </div>
        <div class="condition-group-footer list-group-item">
            <div class="row text-center">
                <button type="button" class="btn btn-primary" onclick="CFUtils.setFilterRow (this);" title="Agregar condición"><i class="fa fa-plus"></i></button>
            </div>
        </div>
    <div class="condition-group-operator" style="margin-top:4px; margin-bottom: 0px">
        <select name="conditionGroups[]" class="form-control operator hidden" disabled="disabled">
            <option value="AND">&nbsp;&nbsp;&nbsp;y</option>
            <option value="OR">&nbsp;&nbsp;&nbsp;o</option>
        </select>
    </div>
    </div>

{/strip}