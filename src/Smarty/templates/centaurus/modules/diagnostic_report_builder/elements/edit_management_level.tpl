{math equation= rand() assign= "idTopic"}
{*$RANGES|var_dump*}
{if $RANGES neq NULL}
    {assign var='operation' value=$RANGES['oper'][0]}
    {assign var='minimos'  value=$RANGES.min}
    {assign var='maximos'  value=$RANGES.max}
    {assign var='names'  value=$RANGES.name}
    {assign var="totalNames" value=$names|@count}
{else}
    {assign var='operation' value=null}
    {assign var 'minimos'  value=null}
    {assign var 'maximos'  value=null}
    {assign var 'names'  value=null}
    {assign var="totalNames" value=0}
{/if}
<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <div class="row">
            <div class="col-md-4 text-right">
                <label for="fromfieldname">Tipo de Operación:</label>
            </div>
            <div id="div-drb-name" class="form-group col-md-6 field-container" style="margin-bottom: 1px!important;">
                <select class="form-control"
                        id="block-field-{$FIELD_ID}"
                        name="block[{$idRowBuilder}][element-field][oper][]"
                        title="Operación">
                    {if isset($TOPICS_OPERATIONS) && $TOPICS_OPERATIONS neq NULL}
                        <option value="">Seleccionar operación</option>
                        {foreach $TOPICS_OPERATIONS as $key => $values}
                            <option value="{$key}" {if $operation eq $key}selected{/if}>{$values}</option>
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
                    {for $index = 0 to ($totalNames - 1)}
                    <tr>
                        <td>
                            <div class="col-xs-5">
                            <input type="text"
                                   class="form-control input-sm"
                                   name="block[{$idRowBuilder}][element-field][min][]"
                                    value="{$minimos[$index]}">
                            </div>
                        </td>
                        <td>
                            <div class="col-xs-5">
                                <input type="text"
                                       class="form-control input-sm"
                                       name="block[{$idRowBuilder}][element-field][max][]"
                                       value="{$maximos[$index]}">
                            </div>
                        </td>
                        <td>
                            <div class="col-xs-9">
                                <input type="text"
                                       class="form-control input-sm topics_name"
                                       rel="{$idRowBuilder}"
                                       name="block[{$idRowBuilder}][element-field][name][]"
                                       value="{$names[$index]}">
                            </div>
                        </td>
                        <td>
                            <button type="button"
                                    onclick="Topic{$idTopic}Utls.delRow(this)"
                                    title="Eliminar rango"
                                    id="delButton-{$idTopic}"
                                    class="btn btn-danger delbutton {if $index eq 0}hide{/if}">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                    {/for}
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