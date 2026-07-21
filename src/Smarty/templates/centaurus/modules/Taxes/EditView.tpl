{strip}
{if (isset ($TAX))}
	{assign var='taxId' value=$TAX->getId ()}
	{assign var='taxName' value=$TAX->getName ()}
	{assign var='taxDescription' value=$TAX->getDescription ()}
	{assign var='taxIsDefault' value=$TAX->isDefault ()}
	{assign var='taxPercentage' value=$TAX->getPercentage ()}
	{assign var='taxConditionGroups' value=$TAX->getConditionGroups ()}
{else}
	{assign var='taxId' value=null}
	{assign var='taxName' value=null}
	{assign var='taxDescription' value=null}
	{assign var='taxPercentage' value=null}
	{assign var='taxIsDefault' value=false}
	{assign var='taxConditionGroups' value=null}
{/if}
<link rel="stylesheet" type="text/css" href="modules/Taxes/Taxes.css" />
<form method="post" action="index.php" onsubmit="return TaxUtils.validateForm (this);">
	<input type="hidden" name="module" value="Taxes" />
	<input type="hidden" name="action" value="Save" />
	<input type="hidden" name="record" value="{if (!empty ($RECORD))}{$RECORD}{/if}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Taxes&action=ListView&parenttab=Settings">Impuesto</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Taxes&action=ListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
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
										<label for="taxname">Nombre <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="text" id="taxname" name="taxname" value="{$taxName}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="description">Descripción</label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<textarea id="description" name="description" class="form-control">{$taxDescription}</textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="percentage">Porcentaje <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="percentage" name="percentage" min="0" step="0.01" class="form-control" value="{$taxPercentage}" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="isdefault">Impuesto por defecto</label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="checkbox" id="isdefault" name="isdefault" value="1"{if ($taxIsDefault)} checked="checked"{/if} />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="title-section main-box-header clearfix">
					<h2>Condiciones</h2>
				</header>
				<div class="main-box-body clearfix">
					<div class="condition-groups">
{if (!empty ($taxConditionGroups))}
	{foreach $taxConditionGroups as $taxConditionGroup}
		{include file="modules/Taxes/ConditionGroupEditView.tpl" GROUP=$taxConditionGroup}
	{/foreach}
{/if}
					</div>
					<div class="action-bar text-center">
						<button type="button" class="btn btn-link" onclick="TaxUtils.addConditionGroup ();" title="Agregar grupo de condiciones">
							<i class="fa fa-plus"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="condition-template">
	{include file="modules/Taxes/ConditionEditView.tpl"}
</script>
<script type="text/html" id="condition-group-template">
	{include file="modules/Taxes/ConditionGroupEditView.tpl"}
</script>
<script type="text/javascript" src="modules/Taxes/Taxes.js"></script>
{/strip}