{strip}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css" />
<style type="text/css">
{literal}
	.action > label {
		display:   inline-block;
		max-width: none;
	}
	.action > label > input[type="checkbox"] {
		display:        inline-block;
		height:         1.25rem;
		margin:         0 5px 0 0;
		vertical-align: middle;
		width:          auto;
	}
	.action > label > span {
		display:        inline-block;
		vertical-align: middle;
	}
{/literal}
</style>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript">
{literal}
	J (document).ready (function () {
		J ('#proTabList').tableDnD ();
	});
{/literal}
</script>
<div id="formid" class="form-container">
	<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso4" data-dialog="#texto{$ID_DLG_CREACION_MODULOS}">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" id="action" value="" />
		<input type="hidden" name="Ajax" value="true" />
		<div class="wizard" id="myWizard">
			<div class="wizard-inner">
				<ul class="steps">
					<li class="complete"><span class="badge badge-success">1</span>Paso 1<span class="chevron"></span></li>
					<li class="complete"><span class="badge badge-success">2</span>Paso 2<span class="chevron"></span></li>
					<li class="complete"><span class="badge badge-success">3</span>Paso 3<span class="chevron"></span></li>
					<li class="active"><span class="badge badge-primary">4</span>Paso 4<span class="chevron"></span></li>
				</ul>
				<div class="actions">
					<button type="button" class="btn btn-default btn-mini btn-prev" onclick="WizardUtils.goBackToStep3 ();">
						<i class="icon-arrow-left"></i>
						{$MOD.LBL_ANTERIOR}
					</button>
					&nbsp;
					<button data-last="Finish" id="button_next" class="btn btn-success btn-mini" type="button" onclick="WizardUtils.createModule ();">
						{$MOD.LBL_SIGUIENTE}
						<i class="icon-arrow-right"></i>
					</button>
				</div>
			</div>
			<div class="step-content">
				<div class="main-box clearfix">
					<header class="main-box-header clearfix">
						<h2 class="pull-left">{$MOD.LBL_PROPIEDADES_AVANZADAS_DEL_MODULO}</h2>
					</header>
					<div class="main-box-body clearfix">
						<div class="table-responsive">
							<table class="table" id="proTabList">
								<tr style="height:25px">
									<td width="20%" class="dvtCellLabel" align="right">
										<span style="color: red;">*</span> {$MOD.LBL_CAMPO_IDENTIFICADOR_DEL_MODULO}
									</td>
									<td width="30%" align="left" class="dvtCellInfo">
										<select id="campoIdentificador" name="campoIdentificador" class="form-control identifier-field" title="">
											<option value="">{$MOD.LBL_SELECCIONAR}</option>
{foreach $AVAILABLE_FIELDS as $availableField}
	{* No queremos que el campo "Código" aparezca en el listado de campos idetntificadores, saltamos la primera iteración *}
	{if ($availableField@index == 0)}{continue}{/if}
											<option value="{$availableField.value}"{if (($SELECTED_IDENTIFIER) && ($SELECTED_IDENTIFIER == $availableField.value)) || ((!$SELECTED_IDENTIFIER) && ($availableField@index == 1))} selected="selected"{/if}>{$availableField.text}</option>
{/foreach}
										</select>
									</td>
								</tr>
								<tr style="height:25px">
									<td width="20%" class="dvtCellLabel" align="right">{$MOD.LBL_IS_REPORT}</td>
									<td width="30%" align="left" class="dvtCellInfo">
										<input type="checkbox" id="check_reportAvailable" name="reportAvailable" placeholder=""{if (isset ($SELECTED_REPORT_AVAILABILITY))} checked="checked"{/if} />
									</td>
								</tr>
								<tr>
									<td class="detailedViewHeader" colspan="2">
										<div style="float:left">
											<b>{$MOD.LBL_COLUMNAS_FILTRO}</b>
										</div>
									</td>
								</tr>
								<tr>
									<td class="dvtCellInfo" colspan="2">
{assign var='totalFilters' value=8}
{assign var='totalColumns' value=4}
{assign var='column' value=0}
										<table width="100%" cellspacing="0" cellpadding="0" border="0" class="table" id="filtrosCampos">
{for $i=1 to floor ($totalFilters / $totalColumns)}
											<tr>
	{for $j=1 to $totalColumns}
												<td class="lvtColData">
													<select id="columnasFiltro{$column}" name="columnasFiltro[]" class="form-control view-column" title="">
														<option value="">{$MOD.LBL_SELECCIONAR}</option>
		{foreach $AVAILABLE_FIELDS as $availableField}
														<option value="{$availableField.value}"{if (isset ($SELECTED_FILTERS[$column])) && ($SELECTED_FILTERS[$column] == $availableField.value)} selected="selected"{/if}>{$availableField.text}</option>
		{/foreach}
													</select>
												</td>
		{assign var='column' value=$column + 1}
	{/for}
											</tr>
{/for}
										</table>
									</td>
								</tr>
								<tr>
									<td class="detailedViewHeader" colspan="2">
										<div style="float:left">
											<b>{$MOD.LBL_LISTAS_RELACIONADAS}</b>
										</div>
										<div class="filter-block pull-right">
											<a href="#" class="btn btn-primary pull-right" onclick="WizardUtils.addRelatedList (this); return false;">
												<i class="fa fa-plus-circle fa-lg"></i>
												{$MOD.LBL_ADD_LISTA}
											</a>
										</div>
									</td>
								</tr>
								<tr>
									<td class="dvtCellInfo" colspan="2">
										<table width="100%" cellspacing="0" cellpadding="0" border="0" class="lvt small related-lists">
											<thead>
											<tr>
												<th class="lvtCol" width="27%">{$MOD.LBL_LABEL}</th>
												<th class="lvtCol" width="27%">{$MOD.LBL_MODULE}</th>
												<th class="lvtCol" width="41%">{$MOD.LBL_ACTIONS}</th>
												<th class="lvtCol" width="5%"></th>
											</tr>
											</thead>
											<tbody>
{foreach $SELECTED_MODULES as $selectedRelatedModule}
	{include
		file='Settings/ModuleManager/WizardStep4RelatedLists.tpl'
		SELECTED_LABEL=$SELECTED_LABELS[$selectedRelatedModule@index]
		SELECTED_MODULE=$SELECTED_MODULES[$selectedRelatedModule@index]
		SELECTED_INSERT=$SELECTED_INSERTS[$selectedRelatedModule@index]
		SELECTED_SELECT=$SELECTED_SELECTS[$selectedRelatedModule@index]
		SELECTED_PATTERN=$SELECTED_PATTERNS[$selectedRelatedModule@index]
	}
{/foreach}
											</tbody>
										</table>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<div id="mensaje" class="message-container" style="display: none; text-align: center; width: 100%;">
	<img src="themes/images/loading.gif" />
</div>
<script type="text/html" id="related-list-template">
{include
	file='Settings/ModuleManager/WizardStep4RelatedLists.tpl'
	SELECTED_LABEL=null
	SELECTED_MODULE=null
	SELECTED_INSERT=null
	SELECTED_SELECT=null
	SELECTED_PATTERN=null
}
</script>
{/strip}