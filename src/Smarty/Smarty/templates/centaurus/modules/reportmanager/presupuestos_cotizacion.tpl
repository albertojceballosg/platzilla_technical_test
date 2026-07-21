<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="logo" style="max-width: 130px; overflow: hidden;text-align: center">
{if $IMAGE_PATH neq NULL}
                <img {if $IMAGE_PROP gt 0}width="{$IMAGE_PROP}%" height="{$IMAGE_PROP}%" {/if} src="{$IMAGE_PATH}" class="img-responsive">
{/if}
            </div>
        </div>
        <div class="col-md-12" style="margin-top: 15px; height: 10px"></div>
        <div class="col-md-12" style="margin-bottom: 6px">
            <p style="text-align: right;margin: 1px 0px;font-size: 10px">FECHA DE CREACIÓN:&nbsp;{if $DT_CREATED eq ''}{$TODAY}{else}{$DT_CREATED}{/if}</p>
            <p style="text-align: right;margin: 1px 0px;font-size: 10px">FECHA DE EMISIÓN:&nbsp;{if $DT_ISSUE eq ''}{$TODAY}{else}{$DT_ISSUE}{/if}</p>
            <p style="text-align: right;margin: 1px 0px;font-size: 10px">FECHA DE VENCIMIENTO:&nbsp;{if ($DT_EXPIRATION eq '') || ($DT_EXPIRATION == 0)}S/F{else}{$DT_EXPIRATION}{/if}</p>
        </div>
        <div class="col-md-12" style="background-color: #F2F2F2;border-style: solid; border-color: #000000">
            <h4 style="text-align: center;margin: 2px 10px">Cotizaci&#243;n</h4>
        </div>
        <div class="col-md-12" style="margin-top: 6px; height: 3px"></div>
        <div class="col-md-12" style="background-color: #F2F2F2;border-style: solid; border-color: #C6C6C6">
            <table style="width: 100%;margin: 12px 6px">
                <tr>
                    <td width="50%" align="left" style="font-size: 12px">
                        <p>CLIENTE:&nbsp;{if $CUSTOMER_NAME eq ''}Cliente de muestra{else}{$CUSTOMER_NAME}{/if}</p>
                        <p>Número de Identificación Fiscal:&nbsp;{$FISCAL_CODE}</p>
                        <p>DIRECCIÓN:&nbsp;{$CUSTOMER_ADDRESS}</p>
                    </td>
                    <td width="50%" align="right" style="font-size: 12px">
                        <P>{$NAME_ORGANIZATION}</P>
                        <p>Número de Identificación Fiscal:&nbsp;{$ORGANIZATION_CFICAL}</p>
                        <p>{$ADDRESS_ORGANIZATION}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-12" style="margin-top: 15px; height: 10px"></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div>
                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                    <tr class="active">
                        <th width="22%" align="center" style="height: 35px;font-size: 11px">&nbsp;ART&#205;CULO</th>
                        <th width="14%" align="center" style="font-size: 11px">CANTIDAD</th>
                        <th width="14%" align="center" style="font-size: 11px">PRECIO</th>
                        <th width="14%" align="center" style="font-size: 11px">DESCUENTO&nbsp;(%)</th>
                        <th width="14%" align="center" style="font-size: 11px">SUBTOTAL</th>
                        <th width="14%" align="center" style="font-size: 11px">IMPUESTO</th>
                        <th width="14%" align="center" style="font-size: 11px">TOTAL</th>
                    </tr>
                    </thead>
                    <tbody>
                    {assign var="totalDataGrid" value=$CUSTOMER_ORDER|@count}
                    {if $totalDataGrid gt 0}
                        {assign var="tax" value=0}
                        {foreach $CUSTOMER_ORDER as $row}
                        <tr>
                            <td width="22%" align="left" style="vertical-align: top;text-align: justify;padding-top: 1px">{$row.articulo}</td>
                            <td width="14%" align="center" style="vertical-align: top">{$row.cantidad}</td>
                            <td class="invoice-cell-numbre" width="14%" align="right" style="padding-right: 12px;vertical-align: top">{$row.precio|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;vertical-align: top">{$row.descuento|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;vertical-align: top">{$row.subtotal|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;vertical-align: top">{$row.valor_impuesto|number_format:2:",":"."}<br><span style="font-size:9px">({$row.impuesto|number_format:2:",":"."}&nbsp;%)</span></td>
                            <td class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;vertical-align: top">{$row.total|number_format:2:",":"."}</td>
                        </tr>
                    {/foreach}
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 12px;height: 35px;font-size: 11px">
                                SUBTOTAL</th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;;font-size: 11px">
                                {$SUBTOTAL_AMOUNT|number_format:2:",":"."}</th>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 12px;height: 35px;font-size: 11px">
                                IMPUESTOS</th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;;font-size: 11px">
                                {$TOTAL_TAX|number_format:2:",":"."}</th>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 12px;height: 35px;font-size: 11px">
                                TOTAL GENERAL
                            </th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;;font-size: 11px">
                                {$TOTAL_AMOUNT|number_format:2:",":"."}</th>
                        </tr>
                    {else}
                        <tr>
                            <td width="22%" align="left">&nbsp; Art&#237;culo de muestra</td>
                            <td width="14%" align="center">1</td>
                            <td class="invoice-cell-numbre" width="14%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="14%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="14%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="14%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="14%" align="right">99,99</td>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 12px;height: 35px;font-size: 12px">
                                SUBTOTAL</th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;;font-size: 12px">
                                99.99</th>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 12px;height: 35px;font-size: 12px">
                                IMPUESTOS</th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;;font-size: 12px">
                                99.99</th>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 12px;height: 35px;font-size: 12px">
                                TOTAL GENERAL
                            </th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 12px;font-size: 12px">
                                99.99</th>
                        </tr>
                    {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <p style="font-weight: bold; margin: 1px 0px; padding: 1px">Condiciones y forma de pago</p>
            <p style="font-size: 11px">{$PAYMENT_CONDITIONS}</p>
        </div>
        <div class="col-md-4"></div>
    </div>
    <div class="row">
        <div class="col-md-8" style="margin-top: 12px">
            <p style="font-weight: bold; margin: 1px 0px; padding: 1px">Observaciones</p>
            <p style="font-size: 11px">{$OBSERVATIONS}</p>
        </div>
        <div class="col-md-4"></div>
    </div>
</div>