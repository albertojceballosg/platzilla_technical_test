<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="logo" style="max-width: 130px; overflow: hidden;text-align: left">
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
            <h4 style="text-align: center;margin: 2px 10px">Factura&nbsp;{$NUM_INVOICE}</h4>
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
                        <th width="30%" align="center" style="height: 35px;font-size: 11px">&nbsp;ART&#205;CULO</th>
                        <th width="10%" align="center" style="font-size: 11px">CANT.</th>
                        <th width="13%" align="center" style="font-size: 11px">PRECIO ({$CURRENCY})</th>
                        <th width="10%" align="center" style="font-size: 11px">DESC.&nbsp;(%)</th>
                        <th width="12%" align="center" style="font-size: 11px">SUBTOTAL ({$CURRENCY})</th>
                        <th width="11%" align="center" style="font-size: 11px">IMP. ({$CURRENCY})</th>
                        <th width="14%" align="center" style="font-size: 11px">TOTAL</th>
                    </tr>
                    </thead>
                    <tbody>
                    {assign var="totalDataGrid" value=$CUSTOMER_ORDER|@count}
                    {if $totalDataGrid gt 0}
                        {assign var="tax" value=0}
                        {foreach $CUSTOMER_ORDER as $row}
                        <tr >
                            <td style="font-size:11px; width:30%; text-align:left; vertical-align: top;text-align:left;padding-right: 4px;">{$row.articulo}</td>
                            <td style="width:10%; text-align:center; vertical-align: top; font-size:11px;padding-right: 8px;">{$row.cantidad}</td>
                            <td class="invoice-cell-numbre" style="width:13%; text-align:right; padding-right: 8px;vertical-align: top;font-size:11px;">{$row.precio|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" style="width:10%; text-align:right; padding-right:8px; vertical-align: top; font-size:11px;">{$row.descuento_|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" style="width:12%; text-align:right; padding-right: 8px; vertical-align: top; font-size:11px;">{$row.subtotal|number_format:2:",":"."}</td>
                            <td class="invoice-cell-numbre" style="width:11%; text-align:right; padding-right: 8px;vertical-align: top;font-size:11px;">{$row.valor_impuesto|number_format:2:",":"."}<br><span style="font-size:8px">({$row.impuesto|number_format:2:",":"."}&nbsp;%)</span></td>
                            <td class="invoice-cell-numbre" style="width:14%; text-align:right; padding-right:8px; vertical-align: top;font-size:11px; ">{$row.total|number_format:2:",":"."}</td>
                        </tr>
                    {/foreach}
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 8px;height: 35px;font-size: 11px"> SUBTOTAL</th>
                            <th width="14%" align="right"  style="padding-right: 8px; font-size: 11px">
                                {$SUBTOTAL_AMOUNT|number_format:2:",":"."}</th>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 8px;height: 35px;font-size: 11px">
                                IMPUESTOS</th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 8px;font-size: 11px">
                                {$TOTAL_TAX|number_format:2:",":"."}</th>
                        </tr>
                        <tr>
                            <th class="invoice-cell-numbre" width="86%" align="right" colspan="6" style="padding-right: 8px;height: 35px;font-size: 11px">
                                TOTAL GENERAL ({$CURRENCY})
                            </th>
                            <th class="invoice-cell-numbre" width="14%" align="right"  style="padding-right: 8px;font-size: 11px">
                                {$TOTAL_AMOUNT|number_format:2:",":"."}</th>
                        </tr>
                    {else}
                        <tr>
                            <td width="30%" align="left">&nbsp; Art&#237;culo de muestra</td>
                            <td width="10%" align="center">1</td>
                            <td class="invoice-cell-numbre" width="13%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="10%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="12%" align="right">99,99</td>
                            <td class="invoice-cell-numbre" width="11%" align="right">99,99</td>
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
                                TOTAL GENERAL ({$CURRENCY})
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
		<table style="width: 100%;margin: 12px 6px; font-size:12px;">
			<thead>
				<tr>
                  <th style="width:49%; text-align:left; height: 3em;font-size: 1.0em; font-weight:bold; vertical-align:middle;border-color:#DFDFDF; border-width:thin; border-style:solid;padding: 0.5em;"> Descripci&#243;n </th>
				  <th style="width:2%; border-width:0px;border-color:transparent;"> </th>
				  <th style="width:49%; text-align:left; height: 3em;font-size: 1.0em; font-weight:bold; vertical-align:middle;border-color:#DFDFDF; border-width:thin; border-style:solid; padding: 0.5em;">T&#233;rminos y condiciones</th>
				</tr>
			</thead>
			<tr>
				<td style="width:49%; vertical-align:top;border-color:#DFDFDF; border-width:thin; border-style:solid; padding: 0.5em;">
					<p style="font-size: 11px; vertical-align:top;">{$OBSERVATIONS}</p>
				</td>
				<td> </td>
				<td style="width:49%;vertical-align:top; border-color:#DFDFDF; border-width:thin; border-style:solid; padding: 0.5em;">
					<p style="font-size: 11px">{$PAYMENT_CONDITIONS}</p><br>
					<table style="border-width:0px"><tr><td style="vertical-align:top;">
						<p style="font-size: 10px; margin-bottom:0px; padding-bottom:0px; vertical-align:top;"> En caso de incurrir en el impago de esta factura, esta empresa  se reserva el derecho de incluirle en el Fichero de morosidad de ASNEF Empresas. </p></td>
						<td style="vertical-align:bottom;"><img src="/appef27186/storage/2025/March/week4/74630_SELLO-cliente-adherido-ASNEF.png" style="width:15%;"/></td></tr></table>
				</td>
			</tr>
		</table>
    </div>
</div>