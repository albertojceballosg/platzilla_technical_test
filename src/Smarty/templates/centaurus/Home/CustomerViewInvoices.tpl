{strip}
<div class="main-box-body clearfix">
	<div class="table-responsive">
		<table class="table table-hover dataTable no-footer" width="100%" cellspacing="0" cellpadding="0" border="0">
			<thead>
			<tr>
				<th aria-controls="table_list">Nº Factura</th>
				<th aria-controls="table_list">Fecha de pago</th>
				<th aria-controls="table_list">Descripción</th>
				<th aria-controls="table_list">Total</th>
				<th aria-controls="table_list">Factura PDF</th>
			</tr>
			</thead>
			<tbody>
{foreach $INVOICES as $invoice}
			<tr>
				<td>{$invoice->getNumber ()}</td>
				<td>{$invoice->getDueDate ()|date_format: 'd/m/Y'}</td>
				<td>{$invoice->getSubject ()}</td>
				<td>{$invoice->getTotal ()|number_format:2:'.':','}</td>
				<td>
					<a href="index.php?module=Home&action=ViewInvoice&record={$invoice->getId ()}&Popup=true" target="_blank"><i class="fa fa-file-pdf-o" title="{$APP.LBL_PDF_BUTTON_LABEL}"></i> {$APP.LBL_PDF_BUTTON_LABEL}</a>
				</td>
			</tr>
{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/strip}