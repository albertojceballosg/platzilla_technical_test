<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css" />
<script type="text/javascript" src="themes/centaurus/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="modules/Settings/wizard-utils.js"></script>
<div class="row">
	<div class="col-md-12">
		<h1>
			<a href="index.php?module=calculated_fields&action=index&parenttab=Settings">
			{$MOD.LBL_CONFIG_CALCULATED_FIELDS} </a>
			<small>{$MOD.LBL_CONFIG_CALCULATED_FIELDS_SUB}</small>
		</h1>
	</div>
	<div class="col-md-12">
		<div class="main-box clearfix">
          <br>
		<div class="wizard    main-box-body clearfix" id="myWizard">
			<div class="wizard-inner">
				<ul class="steps">
					<li><span class="badge">1</span>{$MOD.STEP1_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">2</span>{$MOD.STEP2_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">3</span>{$MOD.STEP3_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">4</span>{$MOD.STEP4_TITLE}<span class="chevron"></span></li>
					<li  class="active"><span class="badge  badge-primary">4</span>{$MOD.STEP5_TITLE}<span class="chevron"></span></li>
				</ul>
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
							<div class="alert alert-success">
								{if isset($EDIT)}{$MOD.STEP4_DES_EDIT}{else}{$MOD.STEP5_DES}{/if}
							</div>
						</div>
					</div>
					<div class="col-md-12">{$MOD.STEP4_ELEMENT}&nbsp;{$CEDES}</div>
					<div class="col-md-12">{$MOD.STEP4_RESULT}&nbsp;{$CE}</div>
					<div class="col-md-12">
							<br /><br />
							<a class="btn btn-success btn-sm" href="index.php?module=calculated_fields&action=addCalculatedFields">{$MOD.NAV_BUTTON_NEW}</a>
							<a class="btn btn-default btn-sm" href="index.php?module=calculated_fields&action=index&parenttab=Settings">{$MOD.NAV_BUTTON_CANCEL}</a>
							<br /><br />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>