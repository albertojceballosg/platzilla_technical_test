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
					<li class="active"><span class="badge badge-primary">1</span>{$MOD.STEP1_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">2</span>{$MOD.STEP2_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">3</span>{$MOD.STEP3_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">4</span>{$MOD.STEP4_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">5</span>{$MOD.STEP5_TITLE}<span class="chevron"></span></li>
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
				<p class="text-left" style="padding: 12px  4px">{$MOD.SETP1_DES}</p>

				<form id="calculate-field-form" class="form-horizontal" role="form" method="post"  action="?module=calculated_fields&action=addCalculatedFields"  name="index">
					<div id="cf-dv-title" class="form-group">
							<label for="title" class="col-md-2 control-label"><span style="color: red;">*</span>Título</label>
						<div class="col-md-8">
							<input type="text" class="form-control" id="title" name="title"
								   placeholder="Título del cálculo" value="{if  $TITLE}{$TITLE}{/if}" >
							<span id="cf-title" class="help-block"></span>
						</div>
					</div>
					<div id="cf-dv-descrip" class="form-group">
						<label for="description" class="col-md-2 control-label"><span style="color: red;">*</span>Descripción:</label>
						<div class="col-md-8">
							<textarea class="form-control" id="descrition" name="description" rows="3"
								   placeholder="Descripción del cálculo">{if $DESCRIPTION}{$DESCRIPTION}{/if}</textarea>
							<span id="cf-description" class="help-block"></span>
						</div>
					</div>
					<div class="form-group">
						<label for="moduleId" class="col-md-2 control-label">Módulo fuente: <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="Módulo donde se mostrará el resultado (cálculos estándar) o donde está el campo grid (cálculos de grid). Ejemplo estándar: Contactos para ver total facturas. Ejemplo grid: Facturas para sumar líneas del grid." style="color: #007bff; cursor: help;"></i></label>
						<div class="col-md-8">
							<select class="form-control" id="moduleId" name="moduleId" onchange="CFUtils.setCalculatedModule(this);">
                                {foreach from=$TGM key=k item=v}
								<option value="{$v.name}"  {if  $TABID} {if $TABID eq $v.name} selected {/if}  {/if}>{$v.tablabel} ({$v.name})</option>
                                {/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="button" id="btn-follow" data-step="1" class="btn btn-success btn-sm"onclick="CFUtils.validateRepeatData(this);">{$MOD.NAV_BUTTON_FOLLOW}</button>
							<a class="btn btn-default btn-sm" href="index.php?module=calculated_fields&action=index&parenttab=Settings">{$MOD.NAV_BUTTON_CANCEL}</a>
						</div>
					</div>
					<input type="hidden" id="step" name="step" value=1>
					<input type="hidden" id="module" name="module" value="calculated_fields">
					<input type="hidden" id="method" name="method" value="stepOne">
					<input type="hidden" id="action" name="action" value="addFiltroCalculatedFields">
					<input type="hidden" id="inRecord" name="inRecord" value="{$IN_RECORD}">
                    {if isset($PF)}
						<input type="hidden" id="numField" name="numField" value="{$NF}">
                        {section name=key loop=$PF}
							<input type="hidden" name="fieldValue{$smarty.section.key.index+1}" value="{$PF[key].fieldValue}">
							<input type="hidden" name="fieldColumn{$smarty.section.key.index+1}" value="{$PF[key].fieldColumn}">
							<input type="hidden" name="fieldCond{$smarty.section.key.index+1}" value="{$PF[key].fieldCond}">
							<input type="hidden" name="fcon{$smarty.section.key.index+1}" value="{$PF[key].fcon}">
                        {/section}
                    {/if}
					<input type="hidden" id="custonFilter" name="customFilter" value="{$CFF}">
					<input type="hidden" id="operationfieldId" name="operationfieldId" value="{$OPERFID}">
					<input type="hidden" id="operation" name="operation" value="{$OPERID}">
					<input type="hidden" id="period" name="period" value="{$PERIOD}">
					<input type="hidden" id="periodfieldId" name="periodfieldId" value="{$PERIOD_FIELDID}">
                    {if isset($COD)}
						<input type="hidden" id="record" name="record" value="{$COD}">
                    {/if}
				</form>
			</div>
		</div>
	</div>

</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>
<script language="JavaScript" type="text/javascript" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
<script type="text/javascript" src="modules/calculated_fields/calculatedfields.js"></script>
<script>
	lastModuleId = '';
    {if isset($TABID)}
	lastModuleId = '{$TABID}';
    {/if}
</script>
