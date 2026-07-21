{strip}
<div class="main-box">
	<div class="main-box-body clearfix">
		<div class="row">
			<div class="col-md-3 text-center">
				<figure style="display: inline-block;">
					<img src="{$ORGANIZATION.organization_logopath}/{$ORGANIZATION.logoname}?{$smarty.now}" class="img-responsive" />
					<figcaption class="text-center">{$ORGANIZATION.organizationname}</figcaption>
				</figure>
			</div>
			<div class="col-md-9">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-3">
							<div class="label-input">
								<label for="country-id">Identificación fiscal</label>
							</div>
						</div>
						<div class="form-group col-md-9 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="country-id" value="{$ORGANIZATION.cif}" class="form-control" disabled="disabled" />
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-3">
							<div class="label-input">
								<label for="currency-code">Moneda</label>
							</div>
						</div>
						<div class="form-group col-md-9 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="currency-code" value="{$ORGANIZATION_CURRENCY.currency_name} ({$ORGANIZATION_CURRENCY.currency_symbol})" class="form-control" disabled="disabled" />
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-3">
							<div class="label-input">
								<label for="address">Dirección</label>
							</div>
						</div>
						<div class="form-group col-md-9 field-container">
							<div class="input-group" style="width: 100%;">
								<textarea id="address" class="form-control" disabled="disabled" rows="5">
									{if (!empty ($ORGANIZATION.address))}{$ORGANIZATION.address}{/if}
									{if (!empty ($ORGANIZATION.city))}, {$ORGANIZATION.city}{/if}
									{if (!empty ($ORGANIZATION.state))}, {$ORGANIZATION.state}{/if}
									{if (!empty ($ORGANIZATION.code))}, {$ORGANIZATION.code}{/if}
									{if (!empty ($ORGANIZATION.country))}, {$ORGANIZATION.country}{/if}
								</textarea>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-3">
							<div class="label-input">
								<label for="website">Sitio web</label>
							</div>
						</div>
						<div class="form-group col-md-9 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="website" value="{$ORGANIZATION.website}" class="form-control" disabled="disabled" />
							</div>
						</div>
					</div>
{if (!empty ($ORGANIZATION.default_module))}
	{assign var='moduleLabel' value=$ORGANIZATION.default_module|getTranslatedString: $ORGANIZATION.default_module}
					<div class="col-md-12">
						<div class="col-md-3">
							<div class="label-input">
								<label for="default-module">Iniciar en</label>
							</div>
						</div>
						<div class="form-group col-md-9 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="default-module" value="{$moduleLabel}" class="form-control" disabled="disabled" />
							</div>
						</div>
					</div>
{/if}
				</div>
				<div class="row">
					<div class="col-md-12 text-center">
						<a href="index.php?module=Home&action=EditOrganizationProfile" class="btn btn-primary">Editar</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}