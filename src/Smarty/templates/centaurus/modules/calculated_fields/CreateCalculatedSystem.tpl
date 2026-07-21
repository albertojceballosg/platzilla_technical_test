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
			<a href="index.php?module=calculated_fields&action=index&parenttab=Settings">
			{$MOD.LBL_CONFIG_CALCULATED_FIELDS} </a>
			<small>Crear Cálculo en el Sistema</small>
		</h1>
	</div>
	<div class="col-md-12">
		<div class="main-box clearfix">
          <br>
		<div class="wizard    main-box-body clearfix" id="myWizard">
			<div id="myEquation"  class="wizard-inner" style="padding: 6px 6px">
				<h4 id="equation">Cálculo = a</h4>
				<div id="legend" class="hide">donde..</div>
				<div id="contentGroup"></div>
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

				<form id="formElement" class="form-horizontal" role="form" method="post"  action="?module=calculated_fields&action=addCalculatedSystem">
					<div class="form-group">
							<label for="title" class="col-md-2 control-label"><span style="color: red;">*</span>Título</label>
						<div id="cs-div-title" class="col-md-8">
							<input type="text" class="form-control" id="title" name="title"
								   placeholder="Título del cálculo" value="{if  $TITLE}{$TITLE}{/if}" >
							<span id="cs-title" class="help-block"></span>
						</div>
					</div>
					<div class="form-group">
						<label for="description" class="col-md-2 control-label"><span style="color: red;">*</span>Descripción:</label>
						<div id="cs-div-description" class="col-md-8">
							<input type="text" class="form-control" id="description" name="description"
								   placeholder="Descripción del cálculo" value="{if  $DESCRIPTION}{$DESCRIPTION}{/if}">
							<span id="cs-description" class="help-block"></span>
						</div>
					</div>
					<div class="form-group">
						<label for="moduleId" class="col-md-2 control-label">Módulo fuente: <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="Selecciona el módulo que contiene los datos sobre el que harás la operación de cálculo" style="color: #007bff; cursor: help;"></i></label>
						<div id="cs-div-modulename" class="col-md-8">
							<select class="form-control" id="module-name" name="modulename" onchange="CSUtils.getCalculatedField (this);">
                               <option value="" >Seleccione</option>
                                {if $MWNF}
                                    {foreach from=$MWNF key=k item=v}
                                        <option value="{$v.name}"  {if  $MODULE_NAME} {if $MODULE_NAME eq $v.name} selected {/if}  {/if}>
                                            {$v.tablabel}
                                            {if $v.has_numeric_fields == 1}📊{/if}
                                            {if $v.has_calculated_elements == 1}🧮{/if}
                                            {if $v.has_system_calculations == 1}⚙️{/if}
                                        </option>
                                    {/foreach}
                                {else}
                                    <option value="" disabled>No hay módulos disponibles</option>
                                    <!-- DEBUG: MWNF variable not set or empty -->
                                {/if}
							</select>
							<span id="cs-modulename" class="help-block"></span>
							<div class="help-block" style="font-size: 11px; color: #666; margin-top: 5px;">
								<strong>Indicadores:</strong> 
								📊 = Campos numéricos | 
								🧮 = Elementos calculados | 
								⚙️ = Cálculos del sistema
							</div>
						</div>
					</div>
                    {if (isset ($CALCULATED_DATA) && !empty ($CALCULATED_DATA['typeFirstElement']) && $CALCULATED_DATA['typeFirstElement']|@count gt 0)}
                        {include file='modules/calculated_fields/CalculatedSystemEditTemplate.tpl' GROUP_NUMBER='1'}
					{else}
                    	{include file='modules/calculated_fields/CalculatedSystemTemplate.tpl' GROUP_NUMBER='1'}
					{/if}
					<div id="more-groups" class="form-group text-center">
						<button type="button" class="btn btn-success" data-group="0"   onclick="CSUtils.addGroup (this);" title="Agregar grupo de condiciones">
							<i class="fa fa-plus"></i></button>&nbsp;
						<button type="button" class="btn btn-primary" data-group="0"   onclick="CSUtils.lookEquation ();" title="Ver fórmula">
							<i class="fa fa-refresh"></i></button>
					</div>
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="button" class="btn btn-success btn-sm" onclick="CSUtils.validateForm();">{if isset($RECORD_ID)}{$MOD.NAV_BUTTON_EDIT}{else}{$MOD.NAV_BUTTON_SAVE}{/if}</button>
							<a class="btn btn-default btn-sm" href="index.php?module=calculated_fields&action=index&tab=system">{$MOD.NAV_BUTTON_CANCEL}</a>
						</div>
					</div>
					<input type="hidden" id="numField" name="numField" value="{if isset($PF)}{$NF}{else}0{/if}">
					<input type="hidden" id="module" name="module" value="calculated_fields">
					<input type="hidden" id="action" name="action" value="addCalculatedSystem">
					<input type="hidden" id="calculatedGroup" name="calculatedGroup" value="a">
					<input type="hidden" id="calculatedEquation" name="calculatedEquation" value="">
					<input type="hidden" id="relatedModule" name="relatedModules" value="">
					{if isset($RECORD_ID)}
						<input type="hidden" id="recordId" name="recordId" value="{$RECORD_ID}">
						<input type="hidden" id="equationId" name="equationId" value="{$EQUATION_ID}">
						<input type="hidden" id="method" name="method" value="EDIT">
					{else}
						<input type="hidden" id="method" name="method" value="SAVE">
					{/if}
				</form>
			</div>
		</div>
	</div>

</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>
<script type="text/javascript" src="modules/calculated_fields/calculatedsystem.js"></script>
<script type="text/html" id="condition-group">
    {include file='modules/calculated_fields/CalculatedSystemTemplate.tpl' GROUP_NUMBER='2'}
</script>
