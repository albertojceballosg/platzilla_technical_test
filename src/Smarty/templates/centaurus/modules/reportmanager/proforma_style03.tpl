<div class="container">
    <div class="row">
        <div class="col-md-12  rounded-color">
            <p class="invoice-company" style="text-align:center">FACTURA PROFORMA</p>
        </div>
        <div class="col-md-12" style="margin-top: 25px; height: 15px"></div>
        <div class="col-md-12">
            <table class="table table-condensed" width="100%">
                <tbody class="no-bordered">
                <tr class="no-bordered">
                    <td width="80%"  align="left" style="vertical-align: top">
                        <p class="invoice-company">{$NAME_ORGANIZATION}</p>
                        <p>{$ADDRESS_ORGANIZATION}</p>
                        <p>{$ORGANIZATION_PCODE}</p>
                    </td>
                    <td width="0%" align="left">
                        &nbsp;
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div>
                <table class="table table-condensed " >
                    <tbody class="no-bordered">
                    <tr class="no-bordered" style="vertical-align: top">
                        <td width="35%"  align="left" class="custumer-adrress">
                            <p style="font-weight: bold">Facturar a:</p>
                            <p>{if $CUSTOMER_NAME eq ''}Cliente de muestra{else}{$CUSTOMER_NAME}{/if}</p>
                            <p>{$CUSTOMER_ADDRESS}</p>
                            <p>{$POSTAL_CODE}</p>
                        </td>
                        <td width="30%" align="left">&nbsp;
                            <div style="width: 100%"></div>
                        </td>
                        <td colspan="2" width="25%" align="left">
                            <table class="table-invoice-info">
                                <tbody>
                                <tr style="margin: 1px; padding: 0px">
                                    <td width="60%" align="left"> <p style="text-align: left; margin: 1px 2px; padding: 1px">N&#186; de Factura:</p></td>
                                    <td width="40%" align="left"> <p style="text-align: left; margin: 1px 2px; padding: 1px">{$NUM_INVOICE}</p></td>
                                </tr>
                                <tr style="margin: 1px; padding: 0px">
                                    <td width="60%" align="left"> <p style="text-align: left">N&#186; de Orden:</p></td>
                                    <td width="40%" align="left"> <p style="text-align: left">{if ($NUM_ORDER eq '') || ($NUM_ORDER == 0)}S/N{else}{$NUM_ORDER}{/if}</p></td>
                                </tr>
                                <tr style="margin: 1px; padding: 0px">
                                    <td width="60%" align="left"> <p style="text-align: left">Fecha Creaci&#243;n:</p></td>
                                    <td width="40%" align="left"> <p style="text-align: left">{if $DT_CREATED eq ''}{$TODAY}{else}{$DT_CREATED}{/if}</p></td>
                                </tr>
                                <tr style="margin: 1px; padding: 0px">
                                    <td width="60%" align="left"> <p style="text-align: left">Fecha Emisi&#243;n:</p></td>
                                    <td width="40%" align="left"> <p style="text-align: left">{if $DT_ISSUE eq ''}{$TODAY}{else}{$DT_ISSUE}{/if}</p></td>
                                </tr>
                                <tr>
                                    <td width="60%" align="left"> <p style="text-align: left">Fecha vencimiento:</p></td>
                                    <td width="40%" align="left"> <p style="text-align: left">{if ($DT_EXPIRATION eq '') || ($DT_EXPIRATION == 0)}S/F{else}{$DT_EXPIRATION}{/if}</p></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-12" style="margin-top: 15px; height: 10px"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div>
                <table bgcolor="#220044" class="table ">
                    <tbody>
                    <tr class="active table-bordered">
                        <td width="55%" align="left">&nbsp;ART&#205;CULO</td>
                        <td width="5%" align="center">CANT.</td>
                        <td width="20%" align="center">PRECIO UNITARIO</td>
                        <td width="20%" align="center">DESCUENTO</td>
                        <td width="20%" align="center">IMPORTE</td>
                    </tr>
                    {assign var="totalDataGrid" value=$CUSTOMER_ORDER|@count}
                    {if $totalDataGrid gt 0}
                        {assign var="tax" value=0}
                        {foreach from=$CUSTOMER_ORDER['description'] key=k item=v}
                        <tr>
                            <td width="55%" align="left">&nbsp;{$v}</td>
                            <td width="5%" align="center">{$CUSTOMER_ORDER['quantity'][$k]}</td>
                            <td class="invoice-cell-numbre" width="20%" align="right">{$CUSTOMER_ORDER['tax'][$k]|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" width="20%" align="right">{$CUSTOMER_ORDER['discount'][$k]|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" width="20%" align="right">{$CUSTOMER_ORDER['amount'][$k]|number_format:2:",":"."}</td>
                        </tr>
                            {$tax = ((($CUSTOMER_ORDER['quantity'][$k] * $CUSTOMER_ORDER['price'][$k]) - ($CUSTOMER_ORDER['amount'][$k] + $CUSTOMER_ORDER['discount'][$k]) ) + $tax)}
                        {/foreach}
                        <tr>
                            <td colspan="4" width="80%" align="right">&nbsp;Subtotal</td>
                            <td class=" invoice-cell-numbre" width="20%" align="right">{$TOTAL_AMOUNT|number_format:2:",":"."}</td>
                        </tr>
                        <tr>
                            <td colspan="4" width="80%" align="right">&nbsp;Impuesto</td>
                            <td class=" invoice-cell-numbre" width="20%" align="right">{$tax}</td>
                        </tr>
                        <tr>
                            <td  class="no-bordered" colspan="4" width="80%" align="right"><p class="invoice-company">TOTAL</p></td>
                            <td class="active  invoice-cell-numbre" width="20%">{($TOTAL_AMOUNT  + $tax)|number_format:2:",":"."}</td>
                        </tr>
                    {else}
                        <tr>
                            <td width="55%" align="left">&nbsp; Art&#237;culo de muestra</td>
                            <td width="5%" align="center">1</td>
                            <td class="invoice-cell-numbre" width="20%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="20%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="20%" align="right">99,99</td>
                        </tr>
                        <tr>
                            <td colspan="4" width="80%" align="right">&nbsp;Subtotal</td>
                            <td class=" invoice-cell-numbre" width="20%" align="right">99,99</td>
                        </tr>
                        <tr>
                            <td colspan="4" width="80%" align="right">&nbsp;Impuesto</td>
                            <td class=" invoice-cell-numbre" width="20%" align="right">99,99</td>
                        </tr>
                        <tr>
                            <td  class="no-bordered" colspan="4" width="80%" align="right"><p class="invoice-company">TOTAL</p></td>
                            <td class="active  invoice-cell-numbre" width="20%">99,99</td>
                        </tr>
                    {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <p style="font-weight: bold; margin: 1px 2px; padding: 1px">Condiciones y forma de pago</p>
            <p>{$PAYMENT_CONDITIONS}</p>
        </div>
        <div class="col-md-4"></div>
    </div>
</div>