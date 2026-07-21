{strip}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css" />
<script type="text/javascript" src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript">
{literal}
	J (document).ready (function () {
		J ('#proTabFields').tableDnD ();
	});
{/literal}
</script>
<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso3" data-dialog="#texto{$ID_DLG_CREACION_MODULOS}">
	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="wizard" id="myWizard">
		<div class="wizard-inner">
			<ul class="steps">
				<li class="complete"><span class="badge badge-success">1</span>Paso 1<span class="chevron"></span></li>
				<li class="complete"><span class="badge badge-success">2</span>Paso 2<span class="chevron"></span></li>
				<li class="active"><span class="badge badge-primary">3</span>Paso 3<span class="chevron"></span></li>
				<li><span class="badge">4</span>Paso 4<span class="chevron"></span></li>
			</ul>
			<div class="actions">
				<button type="button" class="btn btn-default btn-mini btn-prev" onclick="WizardUtils.goBackToStep2 ();">
					<i class="icon-arrow-left"></i>
					{$MOD.LBL_ANTERIOR}
				</button>
				&nbsp;
				<button data-last="Finish" id="button_next" class="btn btn-success btn-mini btn-next" type="button" onclick="WizardUtils.goForwardToStep4 ();">
					{$MOD.LBL_SIGUIENTE}
					<i class="icon-arrow-right"></i>
				</button>
			</div>
		</div>
		<div class="step-content">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">{$MOD.LBL_CAMPOS_DEL_MODULO}</h2>
				</header>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table" id="proTabFields">
{assign var='isFirstBlock' value=true}
{foreach $BLOCK_NAMES as $blockName}
	{include file='Settings/ModuleManager/WizardStep3Field.tpl' BLOCK_NAME=$blockName BLOCK_NUMBER=$BLOCK_NUMBERS[$blockName@index] IS_FIRST_BLOCK=$isFirstBlock}
	{assign var='isFirstBlock' value=false}
{/foreach}
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="field-template">
	{include file='Settings/ModuleManager/WizardStep3FieldDetail.tpl'
		BLOCK_NUMBER=''
		FIELD_LABEL=''
		FIELD_LENGTH=''
		FIELD_MODULE=''
		FIELD_NAME=''
		FIELD_PRECISION=''
		FIELD_PREFIX=''
		FIELD_SEQUENCE=''
		FIELD_TYPE=1
		FIELD_VALUE=''
		VISIBLE=true
	}
</script>
{/strip}