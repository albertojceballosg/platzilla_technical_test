{strip}
<form action="index.php" method="post" name="tandc" onsubmit="VtigerJS_DialogBox.block ();">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" />
	<input type="hidden" name="inv_terms_mode" />
	<input type="hidden" name="parenttab" value="Settings" />
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
				<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
				<li class="active"><span>{$MOD.INVENTORYTERMSANDCONDITIONS}</li>
			</ol>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box no-header">
				<div class="main-box-body clearfix" id="">
					<div class="col-lg-9 pull-left">{$MOD.LBL_INVEN_TANDC_DESC}</div>
					<div class="col-lg-3 pull-right text-right">
{if ($INV_TERMS_MODE == 'view')}
						<input class="btn btn-primary btn-md" title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" onclick="this.form.action.value='OrganizationTermsandConditions';this.form.inv_terms_mode.value='edit'" type="submit" name="Edit" value="{$APP.LBL_EDIT_BUTTON_LABEL}" />
{else}
						<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary btn-md" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="this.form.action.value='savetermsandconditions';" />
						<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-md" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box no-header clearfix">
				<header>
				</header>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table user-list table-hover">
							<tbody>
							<tr>
{if ($INV_TERMS_MODE == 'view')}
								<td>{$INV_TERMSANDCONDITIONS}</td>
{else}
								<td>Type the text below and click Save button</td>
								<td>
									<textarea class="form-control" name="inventory_tandc" placeholder="">{$INV_TERMSANDCONDITIONS}</textarea>
								</td>
{/if}
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
{/strip}