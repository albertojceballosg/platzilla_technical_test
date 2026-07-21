{strip}
{if (isset ($PRODUCT))}
	{assign var='productBasePrice' value=$PRODUCT->getBasePrice ()}
	{assign var='productId' value=$PRODUCT->getId ()}
	{assign var='productName' value=$PRODUCT->getName ()}
	{assign var='productType' value=$PRODUCT->getType ()}
{else}
	{assign var='productBasePrice' value=null}
	{assign var='productId' value=null}
	{assign var='productName' value=null}
	{assign var='productType' value=null}
{/if}
<style type="text/css">
	.required {
		color: #FF0000;
	}
	label {
		font-size:   inherit;
		font-weight: 300;
	}
	.main-box > .main-box-header {
		padding: 20px;
	}
	.action-bar .btn {
		margin-left: 5px;
	}
</style>
<form method="post" action="index.php" onsubmit="return ProductUtils.validateForm (this);">
	<input type="hidden" name="module" value="Products" />
	<input type="hidden" name="action" value="Save" />
	<input type="hidden" name="record" value="{$productId}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Products&action=ListView&parenttab=Settings">Producto o servicio</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Products&action=ListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
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
										<label for="productname">Nombre <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="text" id="productname" name="productname" value="{$productName}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="type">Tipo <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<select id="type" name="type" class="form-control">
										<option value=""></option>
										<option value="{Product::TYPE_PRODUCT}"{if ($productType == Product::TYPE_PRODUCT)} selected="selected"{/if}>{$MOD[Product::TYPE_PRODUCT]}</option>
										<option value="{Product::TYPE_SERVICE}"{if ($productType == Product::TYPE_SERVICE)} selected="selected"{/if}>{$MOD[Product::TYPE_SERVICE]}</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="baseprice">Precio base <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="number" id="baseprice" name="baseprice" min="0" step="0.01" class="form-control" value="{$productBasePrice}" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/Products/Products.js"></script>
{/strip}