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
					<li  class="active"><span class="badge  badge-primary">3</span>{$MOD.STEP3_TITLE}<span class="chevron"></span></li>
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
				<p class="text-left" style="padding: 12px  4px">{$MOD.STEP3_DES}</p>

				<form id="calculate-field-form" class="form-horizontal" role="form" method="post"  action="?module=calculated_fields&action=addCalculatedFields">
					<div class="form-group">
						<label for="description" class="col-md-2 control-label">Columna:</label>
						<div class="col-md-8">
							<select class="form-control" id="operationfieldId" name="operationfieldId">
								{if (isset ($FIELD_LIST)) && (!empty ($FIELD_LIST))}
									{foreach $FIELD_LIST as $field}
									{if $field.typeofdata neq ''}
										{assign var="optionValue" value="{$field.tablename}{'.'}{$field.fieldname}"}
											{if $lastModule eq ''}
											<optgroup label="{$MODULES_LABELS[$field.module]}">
												{$lastModule = $field.module}
												<option value="{$field.tablename}.{$field.fieldname}" data-type="{$field.typeofdata}" {if ($OPERFID eq $optionValue)} {$selectedTypeOfData = $field.typeofdata}  selected="selected"{/if}>{$field.label}</option>
												{elseif $field.module eq $lastModule}
												<option value="{$field.tablename}.{$field.fieldname}" data-type="{$field.typeofdata}" {if ($OPERFID eq $optionValue)} {$selectedTypeOfData = $field.typeofdata}  selected="selected"{/if}>{$field.label}</option>
											{else}
											</optgroup>
											<optgroup label="{$MODULES_LABELS[$field.module]}">
												{$lastModule = $field.module}
												<option value="{$field.tablename}.{$field.fieldname}" data-type="{$field.typeofdata}" {if ($OPERFID eq $optionValue)} {$selectedTypeOfData = $field.typeofdata}  selected="selected"{/if}>{$field.label}</option>
											{/if}
										{/if}
                                    {/foreach}
								{/if}
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="description" class="col-md-2 control-label">Operación:</label>
						<div class="col-md-8">
							<select class="form-control" id="operation" name="operation">
                                {foreach from=$MOD.CALCULATED_FIELDS_OPERATIONS key=k item=v}
                                    {if ($IS_GRID neq NULL) && ($k neq $IS_GRID)}
                                        {continue}
                                    {/if}
								<option value="{$k}" {if  isset($OPERID)} {if $OPERID eq $k} selected {/if}  {/if}>{$v}</option>
                                {/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="button" id="btn-back" data-step="3" class="btn btn-danger btn-sm" onclick="CFUtils.goPrevStep(this);">{$MOD.NAV_BUTTON_BACK}</button>
							<button type="button" id="btn-follow" data-step="2" class="btn btn-success btn-sm" onclick="CFUtils.validateRepeatData(this);">{$MOD.NAV_BUTTON_FOLLOW}</button>
							<a class="btn btn-default btn-sm" href="index.php?module=calculated_fields&action=index&parenttab=Settings">{$MOD.NAV_BUTTON_CANCEL}</a>
						</div>
					</div>
					<input type="hidden" id="step" name="step" value=3>
					<input type="hidden" id="module" name="module" value="calculated_fields">
					<input type="hidden" id="method" name="method" value="stepThree">
					<input type="hidden" id="action" name="action" value="addPeriodCalculatedFields">
					<input type="hidden" id="inRecord" name="inRecord" value="{$IN_RECORD}">
					<input type="hidden" id="operationfieldLabel" name="operationfieldLabel" value="">
                    {foreach from=$DFSO key=k item=v}
						<input type="hidden" id="{$k}" name="{$k}" value="{$v}">
                    {/foreach}
					{if isset($PF)}
						<input type="hidden" id="numField" name="numField" value="{$NF}">
                    {/if}
					<input type="hidden" id="custonFilter" name="customFilter" value="{$CFF}">
                    {if isset($COD)}
						<input type="hidden" id="record" name="record" value="{$COD}">
                    {/if}
					<input type="hidden" id="period" name="period" value="{$PERIOD}">
					<input type="hidden" id="periodfieldId" name="periodfieldId" value="{$PERIOD_FIELDID}">
				</form>
			</div>
		</div>
	</div>

</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>
<script language="JavaScript" type="text/javascript" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
<script type="text/javascript" src="modules/calculated_fields/calculatedfields.js"></script>
