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
			<small>Crear cálculos en el Sistema</small>
		</h1>
	</div>
	<div class="col-md-12">
		<div class="main-box clearfix">
          <br>
		<div class="wizard    main-box-body clearfix" id="myWizard">

			<div class="wizard-inner" style="padding: 6px 6px">
                {$EQ_STRING|urldecode}
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

				<div class="row">
					<div class="col-md-10" style="margin=20px auto">
						<div class="center-block">
							<div class="alert alert-success">{if $METHOD eq 'SAVE'}{$MOD.CALCULATED_SAVE_RESULTS_DES}{else}{$MOD.CALCULATED_EDIT_RESULTS_DES}{/if}</div>
						</div>
					</div>
					<div class="col-md-12">{$MOD.CALCULATED_RESULTS}&nbsp;{$CSDES}</div>
					<div class="col-md-12">{$MOD.STEP4_RESULT}&nbsp;{$CS|number_format:2:',':'.'}</div>
					<div class="col-md-12">
						<br /><br />
						<a class="btn btn-success btn-sm" href="index.php?module=calculated_fields&action=addCalculatedSystem">{$MOD.NAV_BUTTON_NEW}</a>
						<a class="btn btn-default btn-sm" href="index.php?module=calculated_fields&action=index&tab=system">{$MOD.NAV_BUTTON_CANCEL}</a>
						<br /><br />

					</div>
				</div>


			</div>
		</div>
	</div>

</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>


