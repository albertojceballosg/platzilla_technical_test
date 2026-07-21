<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css" />
<script type="text/javascript" src="themes/centaurus/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="modules/Settings/wizard-utils.js"></script>
<style>
	input[type="number"]::-webkit-outer-spin-button,
	input[type="number"]::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}
	input[type="number"] {
		-moz-appearance: textfield;
	}
</style>
<div class="row">
	<div class="col-md-12">
		<h1>
			<a href="index.php?module=calculated_fields&action=index&parenttab=Settings">
			{$MOD.LBL_CONFIG_CALCULATED_FIELDS} </a>
			<small>{$MOD.LBL_CALCULATED_FIELDS_TITLE}</small>
		</h1>
	</div>
	<div class="col-md-12">
		<div class="main-box clearfix">
          <br>
		<div class="wizard    main-box-body clearfix" id="myWizard">
			<div class="wizard-inner" style="padding: 6px 6px">
				<h4 id="equation">Cálculo = a</h4>
				<div class="actions" style="background-color: #ffffff;border-bottom-color: #ffffff;margin-top: 8px">

				</div>
			</div>
		</div>

		</div>
	</div>
</div>
<div class="row" style="margin-top: 25px">
	<div class="col-md-12">
		<div class="main-box clearfix">
			<br>
			<div class="main-box-body clearfix" >
				<p class="text-left" style="padding: 12px  4px">{$MOD.CALCULATED_CREATE_DES}</p>

				<form id="formGridCalculatedField" class="form-horizontal" role="form" method="post">
                    {if isset($TYPE_ELEMENT)}
                        {include file='modules/calculated_fields/gridCalculatedEdit.tpl'}

					{else}
                        {include file='modules/calculated_fields/gridCalculatedTemplate.tpl'}
					{/if}

					<div class="form-group" id="grid-calculated-action">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="button" class="btn btn-success btn-sm" onclick="calculatedGridFieldUtils.saveGridCalculatedField()">{$MOD.NAV_BUTTON_SAVE}</button>
							<a class="btn btn-danger btn-sm" href="index.php?module=calculated_fields&action=index&tab=calculated_field">{$MOD.NAV_BUTTON_CANCEL}</a>
							{if $MODULE_NAME neq false}
								<a class="btn btn-default btn-sm" href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$MODULE_NAME['name']}&return_module={$MODULE_NAME['name']}"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;Campos de {$MODULE_NAME['tablabel']}</a>
                            {/if}
						</div>
					</div>
					<input type="hidden" id="module" name="module" value="calculated_fields">
					<input type="hidden" id="method" name="method" value="SAVE">
					<input type="hidden" id="fieldId" name="fieldId" value="{$FIELD_ID}">
					<input type="hidden" id="subfieldId" name="subfieldId" value="{$SUBFIELD_ID}">
				</form>
			</div>
		</div>
	</div>

</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>
<script type="text/html" id="grid-calculated-group">
    {include file='modules/calculated_fields/gridCalculatedTemplate.tpl' }
</script>
<script type="text/javascript">
    {if isset($TYPE_ELEMENT)}
    var labelIndex = {$OPERATOR_GROUP|@count};
    var calculatedFieldsGroups = [
        {foreach from=$GROUP_NAME key=k item=v}
        '{$v}',
        {/foreach}
    ]
    {else}
    var labelIndex = 1;
    var calculatedFieldsGroups = ['a'];
    {/if}
</script>
<script type="text/javascript" src="modules/calculated_fields/calculatedGridFieldScript.js"></script>
