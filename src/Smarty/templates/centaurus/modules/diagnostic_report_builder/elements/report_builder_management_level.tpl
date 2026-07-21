{math equation= rand() assign= "idTopic"}
<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <div class="row">
            <div class="col-md-4 text-right">
                <label for="fromfieldname">Tipo de Operación:</label>
            </div>
            <div id="div-drb-name" class="form-group col-md-6 field-container" style="margin-bottom: 1px!important;">
                <select class="form-control"
                        id="block-field-{$FIELD_ID}"
                        name="block[{$ID}][element-field][oper][]"
                        title="Operación">
                    {if isset($TOPICS_OPERATIONS) && $TOPICS_OPERATIONS neq NULL}
                        <option value="">Seleccionar operación</option>
                        {foreach $TOPICS_OPERATIONS as $key => $values}
                            <option value="{$key}">{$values}</option>
                        {/foreach}
                    {else}
                        <option value="">Upoo! no hay operaciones disponibles</option>
                    {/if}
                </select>
                <span id="help-field-{$FIELD_ID}" class="help-block" style="color: red"></span>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <table class="table" id="table-topic-{$idTopic}">
                    <thead>
                    <tr>
                        <th colspan="3" style="text-align: center">Rangos de valores</th>
                    </tr>
                    <tr>
                        <th>Mínimo</th>
                        <th>Máximo</th>
                        <th>Nombre</th>
                        <th>&nbsp;-&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <div class="col-xs-5">
                            <input type="text"
                                   class="form-control input-sm"
                                   name="block[{$ID}][element-field][min][]"
                                    value="">
                            </div>
                        </td>
                        <td>
                            <div class="col-xs-5">
                                <input type="text"
                                       class="form-control input-sm"
                                       name="block[{$ID}][element-field][max][]"
                                       value="">
                            </div>
                        </td>
                        <td>
                            <div class="col-xs-9">
                                <input type="text"
                                       class="form-control input-sm topics_name"
                                       rel="{$ID}"
                                       name="block[{$ID}][element-field][name][]"
                                       value="">
                            </div>
                        </td>
                        <td>
                            <button type="button"
                                    onclick="Topic{$idTopic}Utls.delRow(this)"
                                    title="Eliminar rango"
                                    id="delButton-{$idTopic}"
                                    class="btn btn-danger delbutton hide">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: center">
                            <button type="button"
                                    onclick="Topic{$idTopic}Utls.addRow(this)"
                                    title="Insertar rango"
                                    id="addButton-{$idTopic}"
                                    class="btn btn-success addButton">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4">
            </div>
        </div>
    </div>
</div>
{literal}
<script type="text/javascript">
    (function (jQuery) {
        var addRow = function (obj) {
            var tr     = jQuery (obj).closest ('tr');
            var allTrs = tr.closest ('table').find ('tr');
            var lastTr = allTrs[allTrs.length-2];
            var clone = jQuery (lastTr).clone ();

            clone.find('input:text').val('');
            clone.find('button').eq(0).removeClass('hide');
            tr.closest('table').append(clone);
        }
        var delRow = function (obj) {
            var tr     = jQuery (obj).closest ('tr');
            tr.remove();
        }
        window.Topic{/literal}{$idTopic}{literal}Utls = {
            addRow: addRow,
            delRow: delRow
        }
    }(jQuery));

</script>
{/literal}