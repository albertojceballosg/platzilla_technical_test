{strip}
{if (isset ($PRICEBOOK))}
	{assign var='pricebookId' value=$PRICEBOOK->getId ()}
	{assign var='pricebookName' value=$PRICEBOOK->getName ()}
	{assign var='pricebookDescription' value=$PRICEBOOK->getDescription ()}
	{assign var='pricebookMultiplier' value=$PRICEBOOK->getMultiplier ()}
	{assign var='pricebookIsDefault' value=$PRICEBOOK->isDefault ()}
	{assign var='pricebookConditionGroups' value=$PRICEBOOK->getConditionGroups ()}
{else}
	{assign var='pricebookId' value=null}
	{assign var='pricebookName' value=null}
	{assign var='pricebookDescription' value=null}
	{assign var='pricebookMultiplier' value=null}
	{assign var='pricebookIsDefault' value=false}
	{assign var='pricebookConditionGroups' value=null}
{/if}
<link rel="stylesheet" type="text/css" href="modules/Pricebooks/Pricebooks.css" />
<form method="post" action="index.php" onsubmit="return PricebookUtils.validateForm (this);">
	<input type="hidden" name="module" value="Pricebooks" />
	<input type="hidden" name="action" value="Save" />
	<input type="hidden" name="record" value="{if (!empty ($RECORD))}{$RECORD}{/if}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Pricebooks&action=ListView&parenttab=Settings">Tarifa</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Pricebooks&action=ListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
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
										<label for="pricebookname">Nombre <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="text" id="pricebookname" name="pricebookname" value="{$pricebookName}" maxlength="255" class="form-control" />
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
										<textarea id="description" name="description" class="form-control">{$pricebookDescription}</textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="multiplier">Multiplicador <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="multiplier" name="multiplier" min="0" step="0.01" class="form-control" value="{$pricebookMultiplier}" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<span class="label" style="color: inherit; font-size: inherit; font-weight: 300;">Opciones</span>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="checkbox" style="margin-top: 0;">
										<label><input type="checkbox" id="isdefault" name="isdefault" value="1"{if ($pricebookIsDefault)} checked="checked"{/if} />Tarifa por defecto</label>
									</div>
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
{if (!empty ($pricebookConditionGroups))}
	{foreach $pricebookConditionGroups as $pricebookConditionGroup}
		{include file="modules/Pricebooks/ConditionGroupEditView.tpl" GROUP=$pricebookConditionGroup}
	{/foreach}
{/if}
					</div>
					<div class="action-bar text-center">
						<button type="button" class="btn btn-link" onclick="PricebookUtils.addConditionGroup ();" title="Agregar grupo de condiciones">
							<i class="fa fa-plus"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="condition-template">
	{include file="modules/Pricebooks/ConditionEditView.tpl"}
</script>
<script type="text/html" id="condition-group-template">
	{include file="modules/Pricebooks/ConditionGroupEditView.tpl"}
</script>
<script type="text/javascript" src="modules/Pricebooks/Pricebooks.js"></script>
{/strip}