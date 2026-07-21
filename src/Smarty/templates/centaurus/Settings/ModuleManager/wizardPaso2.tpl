{strip}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css" />
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso2" id="wizardPaso2" data-dialog="#texto{$ID_DLG_CREACION_MODULOS}">
	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="wizard" id="myWizard">
		<div class="wizard-inner">
			<ul class="steps">
				<li class="complete"><span class="badge badge-success">1</span>Paso 1<span class="chevron"></span></li>
				<li class="active"><span class="badge badge-primary">2</span>Paso 2<span class="chevron"></span></li>
				<li><span class="badge">3</span>Paso 3<span class="chevron"></span></li>
				<li><span class="badge">4</span>Paso 4<span class="chevron"></span></li>
			</ul>
			<div class="actions">
				<button type="button" class="btn btn-default btn-mini btn-prev" onclick="WizardUtils.goBackToStep1 ();">
					<i class="icon-arrow-left"></i>
					{$MOD.LBL_ANTERIOR}
				</button>
				&nbsp;
				<button data-last="Finish" class="btn btn-success btn-mini btn-next" id="button_next" type="button" onclick="WizardUtils.goForwardToStep3 ();">
					{$MOD.LBL_SIGUIENTE}
					<i class="icon-arrow-right"></i>
				</button>
			</div>
		</div>
		<div class="step-content">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">{$MOD.LBL_BLOQUES_DE_CAMPOS}</h2>
					<div class="filter-block pull-right">
						<button type="button" class="btn btn-primary pull-right" onclick="WizardUtils.addBlock ();">
							<i class="fa fa-plus-circle fa-lg"></i>
							{$MOD.LBL_ADD_BLOQUES}
						</button>
					</div>
				</header>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table" id="proTab">
							<thead>
							<tr>
								<th class="detailedViewHeader" width="60%">
									<b>{$MOD.LBL_NOMBRE_DEL_BLOQUE}</b>
								</th>
								<th class="detailedViewHeader" width="30%">
									<b>{$MOD.LBL_VISIBILIDAD}</b>
								</th>
								<th class="detailedViewHeader" width="10%">
								</th>
							</tr>
							</thead>
							<tbody>
{assign var=row value=1}
{foreach $BLOCKS as $blockName => $visibility}
	{include file='Settings/ModuleManager/WizardStep2Block.tpl' ROW=$row BLOCK_NAME=$blockName VISIBILITY=$visibility}
	{assign var=row value=$row + 1}
{foreachelse}
	{include file='Settings/ModuleManager/WizardStep2Block.tpl' ROW=1 BLOCK_NAME='' VISIBILITY=1}
{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="block-template">
	{include file='Settings/ModuleManager/WizardStep2Block.tpl' ROW='' BLOCK_NAME='' VISIBILITY=1}
</script>
{/strip}