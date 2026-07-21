{strip}
<style type="text/css">
	.required {
		color: #ff0000;
	}
	.table th {
		font-weight: bold;
	}
	.col-name > .description {
		font-size: 0.85em;
		font-style: italic;
	}
	.col-number {
		text-align: center;
		width: 8em;
	}
	.col-price {
		text-align: right;
		width: 8em;
	}
	.col-actions {
		width: 7em;
	}
	.action {
		display:    inline-block;
		list-style: none;
	}
	.action .btn {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-credit-card green-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">{$MOD.LBL_BILLING_PLANS_NAME|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_BILLING_PLANS_DESCRIPTION}</td>
		</tr>
		</tbody>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<ul class="nav nav-tabs">
		<li{if (empty ($SELECTED_TAB)) || ($SELECTED_TAB == 'plans')} class="active"{/if}><a data-toggle="tab" href="#plans">Planes</a></li>
		<li{if ($SELECTED_TAB == 'module-limits')} class="active"{/if}><a data-toggle="tab" href="#module-limits">Límites</a></li>
	</ul>
	<div class="tab-content">
		<div id="plans" class="tab-pane fade{if (empty ($SELECTED_TAB)) || ($SELECTED_TAB == 'plans')} in active{/if}">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Planes de suscripción</h2>
					<div class="pull-right">
						<a href="index.php?module=Settings&action=PlatformBillingPlanEditView&parenttab=Settings" class="btn btn-primary">
							<i class="fa fa-plus-circle"></i> Crear plan
						</a>
					</div>
				</header>
				<div class="main-box-body clearfix" id="ListViewContents">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th class="col-name">Nombre</th>
								<th class="col-number">Aplicaciones incluidas</th>
								<th class="col-number">Usuarios incluidos</th>
								<th class="col-number">Espacio en disco incluido</th>
								<th class="col-price">Precio base</th>
								<th class="col-actions">Acciones</th>
							</tr>
							</thead>
							<tbody>
{if (count ($PLANS) > 0) }
	{foreach $PLANS as $plan}
		{assign var='totalApplications' value=$plan->getTotalApplications ()}
		{assign var='totalDiskSpace' value=$plan->getTotalDiskSpace ()}
		{assign var='totalUsers' value=$plan->getTotalUsers ()}
							<tr class="lvtColData">
								<td class="col-name">
									<a href="index.php?module=Settings&action=PlatformBillingPlanEditView&record={$plan->getId ()}">{$plan->getName ()}</a>
									<p class="description">{$plan->getDescription ()}</p>
								</td>
								<td class="col-number">{if ($totalApplications != -1)}{$totalApplications}{else}Ilimitadas{/if}</td>
								<td class="col-number">{if ($totalUsers != -1)}{$totalUsers}{else}Ilimitados{/if}</td>
								<td class="col-number">{if ($totalDiskSpace != -1)}{$totalDiskSpace}{else}Ilimitado{/if}</td>
								<td class="col-price">{number_format($plan->getProduct ()->getBasePrice (), 2, ',', '.')}</td>
								<td class="col-actions">
									<ul class="actions">
										<li class="action">
											<form method="post" action="index.php" onsubmit="return PlatformBillingPlanUtils.deletePlan ('{$plan->getName ()}');">
												<input type="hidden" name="module" value="Settings" />
												<input type="hidden" name="action" value="PlatformBillingPlanDelete" />
												<input type="hidden" name="record" value="{$plan->getId ()}" />
												<input type="hidden" name="Ajax" value="true" />
												<button class="btn btn-danger" type="submit" title="Eliminar"><i class="fa fa-trash-o"></i></button>
											</form>
										</li>
									</ul>
								</td>
							</tr>
	{/foreach}
{else}
							<tr class="lvtColData">
								<td colspan="6" class="text-center">No hay planes registrados</td>
							</tr>
{/if}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div id="module-limits" class="tab-pane fade{if ($SELECTED_TAB == 'module-limits')} in active{/if}">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Límites del plan gratuito</h2>
				</header>
				<div class="main-box-body clearfix" id="ListViewContents">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th class="col-name">Módulo</th>
								<th class="col-number">Registros</th>
								<th class="col-actions">Acciones</th>
							</tr>
							</thead>
							<tbody>
{if (count ($LIMITS) > 0) }
	{foreach $LIMITS as $limit}
							<tr class="lvtColData">
								<td class="col-name">{$limit->getModuleLabel ()}</td>
								<td class="col-number">{$limit->getMaxRecords ()}</td>
								<td class="col-actions">
									<ul class="actions">
										<li class="action">
											<button type="button" class="btn btn-primary" data-module-name="{$limit->getModuleName ()}" data-module-label="{$limit->getModuleLabel ()}" data-max-records="{$limit->getMaxRecords ()}" onclick="return PlatformBillingPlanUtils.openUpdateLimitModal (this);"><i class="fa fa-pencil"></i></button>
										</li>
									</ul>
								</td>
							</tr>
	{/foreach}
{else}
							<tr class="lvtColData">
								<td colspan="3" class="text-center">No hay límites registrados</td>
							</tr>
{/if}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="change-module-limits-modal-template">
	<div class="modal fade" id="change-module-limits-modal" tabindex="-1" role="dialog" aria-hidden="false">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form method="post" action="index.php" class="form" onsubmit="return PlatformBillingPlanUtils.validateLimitForm (this);">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="PlatformFreeBillingPlanLimitSave" />
					<input type="hidden" id="change-module-limits-modal-module-name" name="modulename" value="" />
					<input type="hidden" name="Ajax" value="true" />
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Módulo <span id="change-module-limits-modal-module-label"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="label-input">
									<label for="change-module-limits-modal-max-records">
										Registros: <span class="required">*</span>
										<span style="display: block; font-size: 0.9em; font-style: italic;">(-1 = ilimitados)</span>
									</label>
								</div>
							</div>
							<div class="form-group col-md-4 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="number" id="change-module-limits-modal-max-records" name="maxrecords" class="form-control" min="-1" />
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Cambiar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</script>
<script type="text/javascript" src="modules/Settings/platform-billing-plans.js"></script>
{/strip}