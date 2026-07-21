{strip}
{if (isset ($PLAN))}
	{assign var='planBasePrice' value=$PLAN->getProduct ()->getBasePrice ()}
	{assign var='planDescription' value=$PLAN->getDescription ()}
	{assign var='planName' value=$PLAN->getName ()}
	{assign var='planStatus' value=$PLAN->getStatus ()}
	{assign var='planTotalApplications' value=$PLAN->getTotalApplications ()}
	{assign var='planTotalDiskSpace' value=$PLAN->getTotalDiskSpace ()}
	{assign var='planTotalUsers' value=$PLAN->getTotalUsers ()}
{else}
	{assign var='planBasePrice' value=null}
	{assign var='planDescription' value=null}
	{assign var='planName' value=null}
	{assign var='planStatus' value=null}
	{assign var='planTotalApplications' value=null}
	{assign var='planTotalDiskSpace' value=null}
	{assign var='planTotalUsers' value=null}
{/if}
<style type="text/css">
	.required {
		color: #FF0000;
	}
	label {
		font-size: inherit;
		font-weight: 300;
	}
	.main-box > .main-box-header {
		padding: 20px;
	}
	.action-bar .btn {
		margin-left: 5px;
	}
</style>
<form method="post" action="index.php" onsubmit="return PlatformBillingPlanUtils.validatePlanForm (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="PlatformBillingPlanSave" />
	<input type="hidden" name="record" value="{if (!empty ($RECORD))}{$RECORD}{/if}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=Settings&action=PlatformBillingPlanListView&parenttab=Settings">{$MOD.LBL_BILLING_PLAN_NAME}</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=PlatformBillingPlanListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="planname">Nombre <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="text" id="planname" name="planname" value="{$planName}" maxlength="25" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="description">Descripción <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<textarea id="description" name="description" class="form-control" maxlength="255">{$planDescription}</textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="total-applications">
										Aplicaciones incluidas <span class="required">*</span>
										<span style="display: block; font-size: 0.9em; font-style: italic;">(-1 = ilimitadas)</span>
									</label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="total-applications" name="totalapplications" value="{$planTotalApplications}" class="form-control" min="-1" style="width: 7em;" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="total-users">
										Usuarios incluidos <span class="required">*</span>
										<span style="display: block; font-size: 0.9em; font-style: italic;">(-1 = ilimitados)</span>
									</label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="total-users" name="totalusers" value="{$planTotalUsers}" class="form-control" min="-1" style="width: 7em;" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="total-disk-space">
										Espacio incluido (MB) <span class="required">*</span>
										<span style="display: block; font-size: 0.9em; font-style: italic;">(-1 = ilimitado)</span>
									</label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="total-disk-space" name="totaldiskspace" value="{$planTotalDiskSpace}" class="form-control" min="-1" style="width: 7em;" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="baseprice">Precio base (EUR) <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="baseprice" name="baseprice" value="{$planBasePrice}" class="form-control" min="0" step="0.01" style="width: 7em;" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="status">Status <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<select id="status" name="status" class="form-control">
{foreach $AVAILABLE_STATUSES as $status}
										<option value="{$status}"{if ($planStatus == $status)} selected="selected"{/if}>{$MOD[$status]}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/Settings/platform-billing-plans.js"></script>
{/strip}