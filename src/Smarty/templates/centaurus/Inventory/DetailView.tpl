{assign var="action" value=$smarty.request.action}
{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}	




<!--<div style="opacity: 1;" class="row" style="border: 0px solid #ff00c3">-->


	<div class="col-lg-12">

		{include file="Buttons_List.tpl"}

	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					
					<div class="icon-box pull-left">
						<h2></h2>
						{*
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									{$APP.LBL_MORE} {$APP.LBL_INFORMATION} <span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
										<li ><a role="menuitem" tabindex="-1" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}&platdb={$PLATDB}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
									{/foreach}
								</ul>
							</li>
						</ul>
						*}
					</div>
					
				</header>
			</div>
		</div>
	</div>


	<div class="tabs-wrapper">

		<ul class="nav nav-tabs">
			<li class="active">
				<a data-toggle="tab" href="#tab-detail">{$APP.LBL_INFORMATION}</a>
			</li>
			{if $COL_ACCIONES neq 'false'}
				{include file='DetailViewActions.tpl'}
			{/if}
			{if !empty($IS_REL_LIST)}
			<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu" role="menu">
					{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
						<li ><a role="menuitem" tabindex="-1" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}&platdb={$PLATDB}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
					{/foreach}
				</ul>
			</li>
			{/if}	
			<!-- [TT11204] - 07/07/16 - Johana Romero - Pestaña terminos y condiciones -->
			<li>
				<a data-toggle="tab" href="#tab-terms">{$MOD.LBL_TERMS_INFORMATION}</a>
			</li>					
		</ul>

		<div class="tab-content">
			<div id="tab-detail" class="tab-pane fade in active">	

				<form action="index.php" method="post" name="DetailView" id="form">
					{include file='DetailViewHidden.tpl'}
				</form>
			
				<div class="row">
					<div class="col-lg-12">
						<div class="main-box clearfix">
							<header class="main-box-header clearfix">
								<h2 class="pull-left">{$MODULELABEL}: {$INV_INFO.subject}</h2>
									
								<!--div class="filter-block pull-right">
									<a href="#" class="btn btn-primary pull-right">
										<i class="fa fa-plus-circle fa-lg"></i> {$APP.LBL_CREATE_BUTTON_LABEL} {$MODULELABEL}
									</a>
									
									<a href="#" class="btn btn-primary pull-right">
										<i class="fa fa-pencil fa-lg"></i> {$APP.LBL_EDIT_BUTTON} {$MODULELABEL}
									</a>
								</div-->
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
								
								<!--div class="table-responsive">
									<table class="table">
										<thead>
											<tr>
												<th class="text-center"><span>#</span></th>
												<th><span>Name</span></th>
												<th class="text-center"><span>Quantity</span></th>
												<th class="text-center"><span>Unit price</span></th>
												<th class="text-center"><span>Total</span></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="text-center">
													8001
												</td>
												<td>
													iPad Mini 32GB Wifi
												</td>
												<td class="text-center">
													5
												</td>
												<td class="text-center">
													$ 225.20
												</td>
												<td class="text-center">
													$ 1126.00
												</td>
											</tr>
											<tr>
												<td class="text-center">
													8002
												</td>
												<td>
													iPad Mini 64GB Wifi + Cellular
												</td>
												<td class="text-center">
													2
												</td>
												<td class="text-center">
													$ 349.99
												</td>
												<td class="text-center">
													$ 699.98
												</td>
											</tr>
											<tr>
												<td class="text-center">
													8003
												</td>
												<td>
													iPad 2 16GB
												</td>
												<td class="text-center">
													1
												</td>
												<td class="text-center">
													$ 100.00
												</td>
												<td class="text-center">
													$ 100.00
												</td>
											</tr>
											<tr>
												<td class="text-center">
													8004
												</td>
												<td>
													iPad Mini 32GB Wifi
												</td>
												<td class="text-center">
													5
												</td>
												<td class="text-center">
													$ 225.20
												</td>
												<td class="text-center">
													$ 1126.00
												</td>
											</tr>
											<tr>
												<td class="text-center">
													8005
												</td>
												<td>
													MacPro Retina 14
												</td>
												<td class="text-center">
													2
												</td>
												<td class="text-center">
													$ 2249.90
												</td>
												<td class="text-center">
													$ 4499.80
												</td>
											</tr>
										</tbody>
									</table>
								</div-->
								
								<!--div class="invoice-box-total clearfix">
									<div class="row">
										<div class="col-sm-9 col-md-10 col-xs-6 text-right invoice-box-total-label">
											Subtotal
										</div>
										<div class="col-sm-3 col-md-2 col-xs-6 text-right invoice-box-total-value">
											$ 7125.76
										</div>
									</div>
									<div class="row">
										<div class="col-sm-9 col-md-10 col-xs-6 text-right invoice-box-total-label">
											VAT (20%)
										</div>
										<div class="col-sm-3 col-md-2 col-xs-6 text-right invoice-box-total-value">
											$ 1425.15
										</div>
									</div>
									<div class="row grand-total">
										<div class="col-sm-9 col-md-10 col-xs-6 text-right invoice-box-total-label">
											Grand total
										</div>
										<div class="col-sm-3 col-md-2 col-xs-6 text-right invoice-box-total-value">
											$ 8550.91
										</div>
									</div>
								</div-->
								
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
								
								<!--div class="clearfix">
									<a href="#" class="btn btn-success pull-right">
										<i class="fa fa-mail-forward fa-lg"></i> Send invoice
									</a>
								</div-->
								
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- [TT11204] - 07/07/16 - Johana Romero - Contenido terminos y condiciones -->
			<div id="tab-terms" class="tab-pane fade">
				<div class="row">
					<div class="col-lg-12">
						<div class="main-box clearfix" style="">
							<div class="main-box-body clearfix">
								<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-responsive">
									<tr>
										<td  valign=top style="padding:20px">
											{$INV_TERMSANDCONDITIONS} 
										</td> 
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>				
			</div>

		</div>
		<!--<div id="tab-notes" class="tab-pane fade"></div>-->

	</div>
<!--</div>-->
