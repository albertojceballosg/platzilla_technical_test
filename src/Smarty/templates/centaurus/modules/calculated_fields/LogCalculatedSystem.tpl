<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css" />
<link rel="stylesheet" type="text/css" href="modules/calculated_fields/select2.css" />
<script type="text/javascript" src="themes/centaurus/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="modules/Settings/wizard-utils.js"></script>
<script type="text/javascript" src="modules/calculated_fields/select2.min.js"></script>
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
			<a href="?module=calculated_fields&action=index&tab=system">
			{$MOD.LBL_CONFIG_CALCULATED_FIELDS} </a>
			<small>Registro de eventos</small>
		</h1>
	</div>
	<div class="col-md-12">
		<div class="main-box clearfix">
          <br>
		<div class="wizard    main-box-body clearfix" id="myWizard">
			<div class="wizard-inner" style="padding: 6px 6px">
				<h4 id="equation-log">Cálculo: {$CS_NAME}</h4>
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
				<p class="text-left" style="padding: 12px  4px">Eventos:</p>
				<textarea class="form-control" disabled="disabled" placeholder="" style="min-height: 25em;">
				{if (!empty ($LOG_FILE_HANDLE))}
					{while (true)}
						{assign var='line' value=fgets ($LOG_FILE_HANDLE)}
						{if ($line !== false)}
							{$line}
						{else}
							{break}
						{/if}
					{/while}
				{else}
					No hay eventos registrados para el cálculo.
				{/if}
				</textarea>
				<br>
				<div class="text-center">
					<form action="index.php" method="post" onsubmit="return confirm ('Se eliminará el registro de eventos. ¿Estás seguro?');">
						<input name="module" value="calculated_fields" type="hidden">
						<input name="action" value="CalculatedSystemDeleteLog" type="hidden">
						<input name="Ajax" value="true" type="hidden">
						<input name="record" value="{$CS_ID}" type="hidden">
						<button type="submit" class="btn btn-danger">Borrar</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<div class="col-md-12">

</div>
</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>
<script type="text/javascript" src="modules/calculated_fields/calculatedsystem-init.js"></script>
<script type="text/javascript" src="modules/calculated_fields/calculatedsystem.js"></script>
