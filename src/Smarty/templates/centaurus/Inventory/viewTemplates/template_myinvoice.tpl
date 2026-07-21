{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
<div style="opacity: 1;" class="row" style="border: 0px solid #ff00c3">

	<div class="tabs-wrapper">
				<div class="row">
					<div class="col-lg-5">
						<div class="main-box clearfix">
							<header class="main-box-header clearfix">
								<h2 class="pull-left">{$MODULELABEL}: {$INV_INFO.subject}</h2>
							</header>
						
						<div class="main-box-body clearfix">
							<div id="invoice-companies" class="row">
								<div class="col-sm-4 invoice-box">
									<div class="invoice-icon hidden-sm">
										<i class="fa fa-home"></i> 
									</div>
									<div class="invoice-company">
										<h4>Facturación</h4>
										<p>
											{$INV_INFO.ship_street},<br>
											{$INV_INFO.ship_state}<br>
											{$INV_INFO.ship_city}<br>
											{$INV_INFO.ship_country}
										</p>
									</div>
								</div>
								<div class="col-sm-4 invoice-box">
									<div class="invoice-icon hidden-sm">
										<i class="fa fa-truck"></i> 
									</div>
									<div class="invoice-company">
										<h4>Envío</h4>
										<p>
											{$INV_INFO.bill_street},<br>
											{$INV_INFO.bill_state}<br>
											{$INV_INFO.bill_city}<br>
											{$INV_INFO.bill_country}
										</p>
									</div>
								</div>
								<div class="col-sm-4 invoice-box invoice-box-dates">
									<div class="invoice-dates">
										<div class="invoice-number clearfix">
											<strong> {$MODULELABEL}</strong>
											<span class="pull-right">{$INV_INFO.invoice_no}</span>
										</div>
										<div class="invoice-date clearfix">
											<strong> Fecha {$MODULELABEL}:</strong>
											<span class="pull-right">{$INV_INFO.invoicedate}</span>
										</div>
										<div class="invoice-date invoice-due-date clearfix">
											<strong>Vencimiento:</strong>
											<span class="pull-right">{$INV_INFO.duedate}</span>
										</div>
									</div>
								</div>
							</div>

							{$ASSOCIATED_PRODUCTS}
							<div class="invoice-summary row">
								<div class="col-md-3 col-sm-6 col-xs-12">
									<div class="invoice-summary-item">
										<span>Account No.</span>
										<div>{$INV_INFO.account_id}</div>
									</div>
								</div>
								<div class="col-md-3 col-sm-6 col-xs-12">
									<div class="invoice-summary-item">
										<span>{$MODULELABEL} </span>
										<div>{$INV_INFO.invoice_no}</div>
									</div>
								</div>
								<div class="col-md-3 col-sm-6 col-xs-12">
									<div class="invoice-summary-item">
										<span>Fecha de vencimiento</span>
										<div>{$INV_INFO.duedate}</div>
									</div>
								</div>
								<div class="col-md-3 col-sm-6 col-xs-12">
									<div class="invoice-summary-item">
										<span>Total</span>
										<div>{$INV_INFO.total}</div>
									</div>
								</div>
							</div>
						
						</div>
					</div>
				</div>
			</div>
			<div id="tab-notes" class="tab-pane fade"></div>

		</div>
	</div>