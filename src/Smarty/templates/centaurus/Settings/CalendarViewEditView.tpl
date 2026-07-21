{strip}
<link rel="stylesheet" href="include/colorpicker/css/colorpicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css" />
<style type="text/css">
{literal}
	.required {
		color: #FF0000;
	}
	label {
		font-size: inherit;
		font-weight: 300;
	}
	.color {
		border:        1px solid #DDDDDD;
		border-radius: 3px;
		cursor:        pointer;
		height:        34px;
	}
	.field-container > label > .form-radio {
		margin-bottom:  0;
		margin-top:     0;
		padding-bottom: 0;
		padding-top:    0;
	}
	.col-constraints > .form-control {
		display:      inline-block;
		margin-right: 5px;
		width:        auto;
	}
	.col-constraints > .glue[disabled="disabled"] {
		display: none;
	}
	.col-actions {
		text-align: center;
		width: 110px;
	}
	.col-actions .btn {
		display:    inline-block;
	}
	.btn.btn-icon {
		font-size:   12px;
		line-height: 1.5;
		padding:     3px 7px;
	}
	.main-box > .main-box-header {
		padding: 20px;
	}
	.action-bar .btn {
		margin-left: 5px;
	}
	.rule-group {
		border-top: 2pt solid black!important;
	}
{/literal}
</style>
<div class="row">
	<div class="col-xs-12">
		<h1>
			<a href="index.php?module=Settings&action=CalendarViewListView&parenttab=Settings">Vistas de calendario</a>
		</h1>
	</div>
</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
<div class="row">
	<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
<form method="post" action="index.php" name="CalendarView" onsubmit="return CalendarUtils.validateForm (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="CalendarViewSaveView" />
	<input type="hidden" name="record" value="{if (!empty ($RECORD))}{$RECORD}{/if}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
					<div class="action-bar pull-right">
						<button type="submit" class="btn btn-info">Guardar</button>
						<a href="index.php?module=Settings&action=CalendarViewListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
					</div>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="modulename">Módulo <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<select id="modulename" name="modulename" class="form-control" onchange="CalendarUtils.reload (this);">
										<option value=""></option>
{foreach $AVAILABLE_MODULES as $module}
										<option value="{$module.name}"{if ((!empty ($MODULE_NAME)) && ($MODULE_NAME == $module.name)) || ((isset ($VIEW)) && ($VIEW.modulename == $module.name))} selected="selected"{/if}>{$module.tablabel}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="label">Nombre <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<input type="text" id="label" name="label" value="{if (isset ($VIEW))}{$VIEW.label}{/if}" maxlength="50" class="form-control" />
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="fromfieldname">Fecha de inicio <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="hidden" name="frommodulename" value="{if (isset ($VIEW))}{$VIEW.frommodulename}{/if}" class="fieldmodulename" />
									<select id="fromfieldname" name="fromfieldname" class="form-control datefield" onchange="CalendarUtils.setModuleNameField (this);">
										<option value=""></option>
{foreach $AVAILABLE_DATE_FIELDS as $moduleLabel => $fields}
										<optgroup label="{$moduleLabel}">
	{foreach $fields as $field}
											<option value="{$field.fieldname}" data-modulename="{$field.modulename}"{if ($VIEW.fromfieldname == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
	{/foreach}
										</optgroup>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="tofieldname">Fecha de fin</label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="hidden" name="tomodulename" value="{if (isset ($VIEW))}{$VIEW.tomodulename}{/if}" class="fieldmodulename" />
									<select id="tofieldname" name="tofieldname" class="form-control" onchange="CalendarUtils.setModuleNameField (this);">
										<option value=""></option>
{foreach $AVAILABLE_DATE_FIELDS as $moduleLabel => $fields}
										<optgroup label="{$moduleLabel}">
	{foreach $fields as $field}
											<option value="{$field.fieldname}" data-modulename="{$field.modulename}"{if ($VIEW.tofieldname == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
	{/foreach}
										</optgroup>
{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="titlefieldname">Título <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="hidden" name="titlemodulename" value="{if (isset ($VIEW))}{$VIEW.titlemodulename}{/if}" class="fieldmodulename" />
									<select id="titlefieldname" name="titlefieldname" class="form-control" onchange="CalendarUtils.selectTitleView (this);">
										<option value=""></option>
{foreach $AVAILABLE_FIELDS as $moduleLabel => $fields}
										<optgroup label="{$moduleLabel}">
	{foreach $fields as $field}
											<option value="{$field.fieldname}" data-modulename="{$field.modulename}"{if ($VIEW.titlefieldname == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
	{/foreach}
										</optgroup>
{/foreach}
									</select>
								</div>
                                {* Subtitulo *}
								<div class="col-md-4 text-right">
									<label for="titlefieldname">Subtítulo</label>
								</div>
								<div class="form-group col-md-8 field-container">
									<input type="hidden" name="subtitlemodulename" value="{if (isset ($VIEW))}{$VIEW.subtitlemodulename}{/if}" class="fieldmodulename" />
									<select id="subtitlefieldname" name="subtitlefieldname" class="form-control" onchange="CalendarUtils.setModuleNameField (this);">
										<option value=""></option>
                                        {foreach $AVAILABLE_FIELDS as $moduleLabel => $fields}
											<optgroup label="{$moduleLabel}">
                                                {foreach $fields as $field}
                                                    {if $field.uitype eq 10}{continue}{/if}
													<option value="{$field.fieldname}" data-modulename="{$field.modulename}"
                                                            {if ($VIEW.subtitlefieldname == $field.fieldname)} selected="selected"{/if}
                                                            {if isset ($VIEW)}
                                                                {if $field.modulename neq $VIEW.titlemodulename}disabled {/if}
                                                            {else}disabled
                                                            {/if} >{$field.fieldlabel}</option>
                                                {/foreach}
											</optgroup>
                                        {/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="backgroundcolor">Color de fondo</label>
								</div>
								<div class="form-group col-md-8 field-container">
{if (!empty ($VIEW.backgroundcolor))}
	{assign var='backgroundColor' value=$VIEW.backgroundcolor}
{else}
	{assign var='backgroundColor' value='#FFFFFF'}
{/if}
									<input type="text" id="backgroundcolor" name="backgroundcolor" value="{$backgroundColor}" class="color" readonly="readonly" size="6" style="background-color: {$backgroundColor}; color: {$backgroundColor};" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-4 text-right">
									<label for="applicationcodes">Disponible para las aplicaciones <span class="required">*</span></label>
								</div>
								<div class="form-group col-md-8 field-container">
									<select id="applicationcodes" name="applicationcodes[]" class="form-control" multiple="multiple">
{if (!empty ($AVAILABLE_APPLICATIONS))}
	{foreach $AVAILABLE_APPLICATIONS as $application}
										<option value="{$application.app_code}"{if (!empty ($VIEW.applicationcodes)) && (in_array ($application.app_code, $VIEW.applicationcodes))} selected="selected"{/if}>{$application.app_name}</option>
	{/foreach}
{/if}
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Reglas</h2>
				</header>
				<div class="main-box-body" id="rules">
					<div class="row">
						<div class="table-responsive">
							<table data-ruler="{if $RULES neq NULL}{$RULES|count}{else}0{/if}" class="table rules">
								<thead>
								<tr>
									<th class="col-color">Color</th>
									<th class="col-constraints">Condiciones</th>
									<th class="col-actions">Acciones</th>
								</tr>
								</thead>
								<tbody>
{if $RULES neq NULL}
	{foreach $RULES as $indexKey => $theRules}
		{assign var="cicle" value=true}
		{assign var="totalRules" value=($theRules|count) - 1}
		{foreach $theRules as $key => $rule}
			{include file='Settings/CalendarViewEditRule.tpl' RULE=$rule INDEX_KEY=$indexKey KEY=$key cicle=$cicle totalRules=$totalRules}
            {assign var="cicle" value=false}
		{/foreach}
	{/foreach}
{/if}
								</tbody>
							</table>
						</div>
					</div>
					<div class="action-bar text-center">
						<button type="button" class="btn btn-link" title="Agregar regla" onclick="CalendarUtils.addRule ();"><i class="fa fa-plus"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="rule-template">
{include file='Settings/CalendarViewRule.tpl'}
</script>
<script type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="modules/Settings/calendar-view.js"></script>
{/strip}