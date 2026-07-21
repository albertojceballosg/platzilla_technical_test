<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css?v=1.0" />
{assign var="uitype" value=$maindata[0][0]}
{assign var="fldlabel" value=$maindata[1][0]}
{assign var="fldlabel_sel" value=$maindata[1][1]}
{assign var="fldlabel_combo" value=$maindata[1][2]}
{assign var="fldlabel_other" value=$maindata[1][3]}
{assign var="fldname" value=$maindata[2][0]}
{* helpField array (idHelp => help field id, title => title for toolTip*}
{assign var="helpField" value=$maindata[2][1]}
{assign var="fldvalue" value=$maindata[3][0]}
{assign var="secondvalue" value=$maindata[3][1]}
{assign var="thirdvalue" value=$maindata[3][2]}
{assign var="typeofdata" value=$maindata[4]}
{assign var="vt_tab" value=$maindata[5][0]}
{assign var="paradic" value=$maindata[6]}
{assign var="fldid" value=$maindata[7]}
{assign var="fldchoices" value=$maindata[8]}
{assign var="referencedModuleParameters" value=$maindata[9]}
{assign var="usefldlabel" value=$fldlabel}
{assign var="fldhelplink" value=""}
{if ($typeofdata == 'M')}
	{assign var="mandatory_field" value="*"}
{else}
	{assign var="mandatory_field" value=""}
{/if}
{if ($FIELDHELPINFO) && ($FIELDHELPINFO.$fldname)}
	{assign var="fldhelplinkimg" value='help_icon.gif'|@vtiger_imageurl:$THEME}
	{assign var="fldhelplink" value='<i class="fa fa-life-saver" onclick="vtlib_field_help_show(this, \'$fldname\');" ></i>'}
	{if ($uitype != 10)}
		{assign var="usefldlabel" value="$fldlabel $fldhelplink"}
	{/if}
{/if}

{if ($uitype == 1) || ($uitype == 102)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}"
					class="animate__animated ">{*<span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
							style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				<div class="input-group" style="width: 100%;">
				{/if}
				{if ($typeofdata == 'I')}
					<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}"
						value="{$fldvalue}" class="form-control" />
				{else}
					<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}"
						class="form-control" />
				{/if}
				{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				</div>
			{/if}
		</div>
	</div>
	{if ($uitype == 102)}
		<script type="text/javascript">
			jQuery ('#{$fldname}').datetimepicker ({ldelim}timeFormat: "HH:mm", timeOnly: true, controlType: 'select'{rdelim});
		</script>
	{/if}
{elseif ($uitype == 2) && ($fldname == 'company')}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; background-color: #eee;"><i
						class="fa fa-building"></i></span>
				<input type="text" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}"
					class="form-control" size="15" />
			</div>
		</div>
	</div>
{elseif ($uitype == 2)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				<div class="input-group" style="width: 100%;">
					<input type="text" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}"
						class="form-control" size="15" />
				</div>
			{else}
				<input type="text" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}"
					class="form-control" size="15" />
			{/if}
		</div>
	</div>
{elseif ($uitype == 3) || ($uitype == 4)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon span-readonly"><i class="fa fa-cubes"></i></span>
				<input type="text" id="{$fldname}" name="{$fldname}"
					value="{if $MODE eq 'edit'}{$fldvalue}{else}{$usefldlabel} - {$MOD_SEQ_ID}{/if}"
					class="form-control input-readonly"
					style="border-bottom-left-radius: 0 !important;border-top-left-radius: 0 !important;"
					tabindex="{$vt_tab}" readonly="readonly" />
			</div>
		</div>
	</div>
{elseif ($uitype == 5) || ($uitype == 6) || ($uitype == 23)}
	{if ($OP_MODE == 'create_view')}
		{if (isset ($smarty.get[$fldname]))}
			{assign var='dateValue' value=$smarty.get[$fldname]}
		{elseif ($fldvalue != '')}
			{assign var='dummy' value=key($fldvalue)}
			{if (!empty ($dummy))}
				{assign var='dateValue' value=$dummy}
			{else}
				{assign var='dateValue' value=null}
			{/if}
		{else}
			{assign var='dateValue' value=null}
		{/if}
	{else}
		{assign var='dummy' value=key($fldvalue)}
		{if (!empty ($dummy))}
			{assign var='dateValue' value=$dummy}
		{else}
			{assign var='dateValue' value=null}
		{/if}
	{/if}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{* Determinar el placeholder según el formato de fecha del usuario *}
			{if $USER_DATE_FORMAT eq 'dd-mm-yyyy'}
				{assign var="dateFormatPlaceholder" value="DD-MM-AAAA"}
			{elseif $USER_DATE_FORMAT eq 'dd/mm/yyyy'}
				{assign var="dateFormatPlaceholder" value="DD/MM/AAAA"}
			{elseif $USER_DATE_FORMAT eq 'mm-dd-yyyy'}
				{assign var="dateFormatPlaceholder" value="MM-DD-AAAA"}
			{elseif $USER_DATE_FORMAT eq 'mm/dd/yyyy'}
				{assign var="dateFormatPlaceholder" value="MM/DD/AAAA"}
			{elseif $USER_DATE_FORMAT eq 'yyyy/mm/dd'}
				{assign var="dateFormatPlaceholder" value="AAAA/MM/DD"}
			{else}
				{assign var="dateFormatPlaceholder" value="AAAA-MM-DD"}
			{/if}
			<div class="input-group" style="width: 100%;">
				<div class="input-group-addon" style="border: 1px solid #ddd !important">
					<i class="fa fa-calendar" id="jscal_trigger_{$fldname}"></i>
				</div>
				<input type="text" id="jscal_field_{$fldname}" name="{$fldname}" value="{$dateValue}"
					class="form-control pull-right input-readonly b-left" tabindex="{$vt_tab}" size="11" maxlength="18"
					readonly="readonly" placeholder="{$dateFormatPlaceholder}" />
				<script type="text/javascript">
					{literal}
					(function() {
						var fieldId = 'jscal_field_{/literal}{$fldname}{literal}';
						var dateFormat = '{/literal}{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}{literal}';
						var userLang = '{/literal}{php}echo isset($_SESSION['authenticated_user_language']) ? $_SESSION['authenticated_user_language'] : 'es_es';{/php}{literal}';
						var theme = '{/literal}{$THEME}{literal}';
						
						function initDatepicker() {
							jQuery('#' + fieldId).datepicker({
								format: dateFormat,
								language: userLang,
								weekStart: 1
							});
						}
						
						// Si el idioma ya está cargado, inicializar inmediatamente
						if (jQuery.fn.datepicker.dates && jQuery.fn.datepicker.dates[userLang]) {
							initDatepicker();
						} else {
							// Cargar el archivo de idioma
							var langFile = 'themes/' + theme + '/js/bootstrap-datepicker.' + userLang + '.js';
							
							jQuery.getScript(langFile)
								.done(function() {
									initDatepicker();
								})
								.fail(function() {
									// Intentar con español
									jQuery.getScript('themes/' + theme + '/js/bootstrap-datepicker.es.js')
										.done(function() {
											userLang = 'es';
											initDatepicker();
										})
										.fail(function() {
											// Si todo falla, inicializar sin idioma específico
											initDatepicker();
										});
								});
						}
					})();
					{/literal}
				</script>
				{if ($uitype == 6) && ($QCMODULE == 'Event')}
					<input type="hidden" name="dateFormat" value="{$dateFormat}" class="form-control pull-right" />
				{elseif ($uitype == 23) && ($QCMODULE == 'Event')}
					<input type="text" name="time_end" value="{$time_val}" class="form-control pull-right" size="5"
						maxlength="5" placeholder="" />
				{/if}
			</div>
		</div>
	</div>
{elseif ($uitype == 7)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<input type="text" id="{$fldname}" name="{$fldname}"
					placeholder="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}9.999.999,99{else}9,999,999.99{/if}"
					value="{$fldvalue}" data-number-format="{$NUMBERING_FORMAT}" class="form-control" tabindex="{$vt_tab}"
					onkeyup="var field = jQuery (this); field.val (field.val ().replace (/[^\d.,-]/g, ''));" />
			</div>
		</div>
	</div>
{elseif ($uitype == 9)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}&nbsp;{$APP.COVERED_PERCENTAGE}{if ($mandatory_field)} <span
						class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; background-color: #eee;"><i class="fa">%</i></span>
				<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}"
					placeholder="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}99,99{else}99.99{/if}"
					data-number-format="{$NUMBERING_FORMAT}" class="form-control" tabindex="{$vt_tab}" />
			</div>
		</div>
	</div>
{elseif ($uitype == 10)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* id="helpEV{$uitype}{if (count($fldlabel.options) == 1)}_{$fldlabel.options.0}{/if}" name="help" style="font-size:0.2em;"  *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$fldlabel.displaylabel}{if ($mandatory_field)} <span
						class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if (count($fldlabel.options) == 1)}
				{assign var="use_parentmodule" value=$fldlabel.options.0}
				<input type="hidden" id="{$fldname}_type" name="{$fldname}_type" value="{$use_parentmodule}" class="small" />
			{else}
				<select id="{$fldname}_type" class="form-control" name="{$fldname}_type"
					onchange="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#{$fldname}_display').val (''); fieldContainer.find ('#{$fldname}').val ('');"
					style="margin-bottom: .5em;" title="">
					{foreach item=option from=$fldlabel.options}
						<option value="{$option}" {if $fldlabel.selected == $option} selected{/if}>
							{$option|@getTranslatedString:$option}</option>
					{/foreach}
				</select>
			{/if}
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{$fldhelplink}
			<div class="input-group" style="width: 100%;">
				<input type="hidden" id="{$fldname}" name="{$fldname}" value="{$fldvalue.entityid}"
					class="for-filter module-reference" />
				<input type="text" id="edit_{$fldname}_display" name="{$fldname}_display" value="{$fldvalue.displayvalue}"
					class="form-control input-readonly b-right{if ($OP_MODE == 'create_view') && ($mandatory_field != '')} placeholderStyle{/if}"
					readonly="readonly" placeholder="" />
				<div class="input-group-addon" data-current-module="{$MODULE}"
					data-display-field-id="edit_{$fldname}_display" data-field-id="{$fldname}"
					data-referenced-module="{$use_parentmodule}" data-title="{$fldlabel.displaylabel}"
					{if (isset ($referencedModuleParameters['fieldnames']))}
						data-filter-field-names="{str_replace('"', "'", json_encode($referencedModuleParameters['fieldnames']))}"
						{/if}{if (isset ($referencedModuleParameters['description']))}
					data-filter-description="{$referencedModuleParameters['description']}" {/if}
					onclick="RelatedModuleModalUtils.openModal (this);">
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon"
					onClick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_{$fldname}_display').val (''); fieldContainer.find ('#{$fldname}').val (''); return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif ($uitype == 11)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; background-color: #eee;">
					<i
						class="fa fa-{if ($fldname == 'phone')}phone{elseif ($fldname == 'mobile') || ($fldname == 'num_cel')}mobile{elseif ($fldname == 'fax')}fax{else}home{/if}">{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}{/if}</i>
				</span>
				<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
					tabindex="{$vt_tab}" />
			</div>
		</div>
	</div>
{elseif ($uitype == 13) || ($uitype == 104)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
				<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
					tabindex="{$vt_tab}" />
			</div>
		</div>
	</div>
{elseif ($uitype == 14)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; background-color: #eee"><i
						class="fa fa-tachometer"></i></span>
				<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
					tabindex="{$vt_tab}" />
			</div>
		</div>
	</div>
{elseif ($uitype == 15) || ($uitype == 31) || ($uitype == 32) || ($uitype == 404)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}{*<span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
				<label for="{$fldname}" class="animate__animated ">
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					<span id="p1_{$usefldlabel|replace:' ':'_'}"></span>&nbsp;{$usefldlabel}{if ($mandatory_field)}<span
						class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				<div class="input-group" style="width: 100%;">
				{/if}
				{if $MODULE eq 'Calendar'}
					<select id="{$fldname}" name="{$fldname}" class="form-control" tabindex="{$vt_tab}">
					{elseif ($fldname == 'bill_state') || ($fldname == 'bill_city') || ($fldname == 'birth_state')}
						<select id="{$fldname}" name="{$fldname}" class="form-control for-filter" tabindex="{$vt_tab}"
							onchange="if (window.onchange_{$fldname}) onchange_list_{$fldname}(this);">
						{else}
							<select id="{$fldname}" name="{$fldname}" class="form-control for-filter" tabindex="{$vt_tab}"
								onchange="if (window.onchange_{$fldname}) onchange_{$fldname}(this)">
							{/if}
							<option value="" disabled="disabled" selected="selected">{$usefldlabel}</option>
							{foreach $fldvalue as $arr}
								{if ($arr[0] == $APP.LBL_NOT_ACCESSIBLE)}
									<option value="{$arr[1]}" {$arr[2]}> {$arr[0]}</option>
								{else if ($arr[0]|strstr:"span" !== false)}
									<option value="{$arr[1]}" {$arr[2]} disabled>{$arr[0] nofilter}</option>
								{else}
									<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
								{/if}
							{/foreach}
						</select>
						{* ... *}
						{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				</div>
			{/if}
		</div>
	</div>
{elseif ($uitype == 16)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				<div class="input-group" style="width: 100%;">
				{/if}
				{if (isset ($maindata[8])) && ($maindata[8])}
					<div id="{$fldname}" class="form-control" style="height: 7em; overflow-x: hidden;">
						{foreach $fldvalue as $index => $arr}
							<div class="checkbox" style="margin-bottom: 0; margin-top: 0;">
								<label
									style="display: inline-block; font-size: 1em; line-height: 20px; padding: 0; vertical-align: middle;">
									<input type="checkbox" id="{$fldname}-{$index}" name="{$fldname}[]" value="{$arr[0]}"
										class="form-control for-filter" tabindex="{$vt_tab}" {if (!empty ($arr[2]))}
										checked="checked" {/if} onchange="if (window.onchange_{$fldname}) onchange_{$fldname}(this)"
										style="display: inline-block; height: auto; line-height: 20px; margin: 0 0.5em 0 0; position: relative; vertical-align: middle; width: auto;">{$arr[0]}
								</label>
							</div>
						{/foreach}
					</div>
				{else}
					<select id="{$fldname}" name="{$fldname}" class="form-control for-filter" tabindex="{$vt_tab}"
						onchange="if (window.onchange_{$fldname}) onchange_{$fldname}(this)">
						<option value=""></option>
						{foreach $fldvalue as $arr}
							<option value="{$arr[0]}" {$arr[2]}>{$arr[0]}</option>
						{/foreach}
					</select>
				{/if}
				{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				</div>
			{/if}
			{$paradic}
		</div>
	</div>
{elseif ($uitype == 17)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; background-color: #eee;"><i
						class="fa fa-wordpress"></i></span>
				<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
					tabindex="{$vt_tab}" onkeyup="validateUrl('{$fldname}');" />
			</div>
		</div>
	</div>
{elseif ($uitype == 19)}
	{if ($fldlabel == $MOD.LBL_ADD_COMMENT)}
		{assign var=fldvalue value=""}
	{/if}
	<div class="col-md-12">
		<div class="form-group col-md-12 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<textarea name="{$fldname}" class="form-control" tabindex="{$vt_tab}" cols="90" rows="8"
				placeholder="">{$fldvalue}</textarea>
			{if $fldlabel eq $MOD.Solution}
				<input type="hidden" name="helpdesk_solution" value='{$fldvalue}'>
			{/if}
		</div>
	</div>
{elseif ($uitype == 21)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}"
					class="animate__animated">{*<span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<textarea id="{$fldname}" name="{$fldname}" class="form-control" tabindex="{$vt_tab}"
				rows="2">{$fldvalue}</textarea>
		</div>
	</div>
{elseif ($uitype == 22)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<textarea id="{$fldname}" name="{$fldname}" cols="30" tabindex="{$vt_tab}" rows="2">{$fldvalue}</textarea>
		</div>
	</div>
{elseif ($uitype == 26)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<select id="{$fldname}" name="{$fldname}" class="form-control" tabindex="{$vt_tab}">
				{foreach item=v key=k from=$fldvalue}
					<option value="{$k}">{$v}</option>
				{/foreach}
			</select>
		</div>
	</div>
{elseif ($uitype == 27)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$fldlabel_other}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<select id="{$fldname}" name="{$fldname}" class="form-control"
				onchange="changeDldType((this.value=='I')? 'file': 'text');">
				{section name=combo loop=$fldlabel}
					<option value="{$fldlabel_combo[combo]}" {$fldlabel_sel[combo]}>{$fldlabel[combo]} </option>
				{/section}
			</select>
			<script type="text/javascript">
				function vtiger_{$fldname}Init () {
				var d = document.getElementsByName ('{$fldname}')[ 0 ];
				var type = (d.value == 'I') ? 'file' : 'text';
				changeDldType(type, true);
				}
				if (typeof window.onload == 'function') {
					var oldOnLoad = window.onload;
					document.body.onload = function() {
						vtiger_{$fldname}Init ();
						oldOnLoad();
					};
				} else {
					window.onload = function() {
						vtiger_{$fldname}Init ();
					};
				}
			</script>
		</div>
	</div>
{elseif ($uitype == 28)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}_I__" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<script type="text/javascript">
				function changeDldType (type, onInit) {ldelim}
				var fieldname = '{$fldname}';
				if (!onInit) {ldelim}
				var dh = getObj ('{$fldname}_hidden');
				if (dh) dh.value = '';
				{rdelim}

				var v1 = document.getElementById(fieldname + '_E__');
				var v2 = document.getElementById(fieldname + '_I__');
				var msg = document.getElementById('limitmsg');

				var text = v1.type == "text" ? v1 : v2;
				var file = v1.type == "file" ? v1 : v2;
				var filename = document.getElementById(fieldname + '_value');
				{literal}
					if (type == 'file') {
						// Avoid sending two form parameters with same key to server
						file.name = fieldname;
						text.name = '_' + fieldname;

						file.style.display = '';
						text.style.display = 'none';
						text.value = '';
						filename.style.display = '';
						msg.style.display = '';
					} else {
						// Avoid sending two form parameters with same key to server
						text.name = fieldname;
						file.name = '_' + fieldname;

						file.style.display = 'none';
						text.style.display = '';
						file.value = '';
						filename.style.display = 'none';
						filename.innerHTML = "";
						msg.style.display = 'none';
					}
				{/literal}
				{rdelim}
			</script>
			<div>
				<input type="hidden" name="{$fldname}_hidden" value="{$secondvalue}" />
				<input type="hidden" name="id" value="" />
				<input type="file" id="{$fldname}_I__" name="{$fldname}" value="{$secondvalue}" tabindex="{$vt_tab}"
					onchange="validateFilename(this);validateFileSize(this,'{$UPLOAD_MAXSIZE}');" style="display: none;" />
				<input type="text" id="{$fldname}_E__" name="{$fldname}" value="{$secondvalue}" class="form-control"
					placeholder="" /><br>
				<div id="displaySize"></div>
				<span id="{$fldname}_value" style="display:none;">{if $secondvalue neq ''}[{$secondvalue}]{/if}</span>
			</div>
			<span id="limitmsg" style="color: red; display:none;">{'LBL_MAX_SIZE'|@getTranslatedString:$MODULE}
				{$UPLOADSIZE}{'LBL_FILESIZEIN_MB'|@getTranslatedString:$MODULE}</span>
		</div>
	</div>
{elseif ($uitype == 30)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{assign var=check value=$secondvalue[0]}
			{assign var=yes_val value=$secondvalue[1]}
			{assign var=no_val value=$secondvalue[2]}
			<input type="radio" name="set_reminder" tabindex="{$vt_tab}" value="Yes" placeholder=""
				{$check} />&nbsp;{$yes_val}&nbsp;
			<input type="radio" name="set_reminder" value="No" placeholder="" />&nbsp;{$no_val}&nbsp;
			{foreach item=val_arr from=$fldvalue}
				{assign var=start value="$val_arr[0]"}
				{assign var=end value="$val_arr[1]"}
				{assign var=sendname value="$val_arr[2]"}
				{assign var=disp_text value="$val_arr[3]"}
				{assign var=sel_val value="$val_arr[4]"}
				<select name="{$sendname}" class="form-control" style="margin-top: .5em;" title="">
					{section name=reminder start=$start max=$end loop=$end step=1 }
						{if $smarty.section.reminder.index eq $sel_val}{assign var=sel_value value="SELECTED"}{else}{assign var=sel_value value=""}{/if}
						<option value="{$smarty.section.reminder.index}" {$sel_value}>{$smarty.section.reminder.index}</option>
					{/section}
				</select>
				&nbsp;{$disp_text}
			{/foreach}
		</div>
	</div>
{elseif ($uitype == 33)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<select id="{$fldname}" name="{$fldname}[]" class="form-control" size="4" tabindex="{$vt_tab}"
				multiple="multiple">
				{foreach $fldvalue as $arr}
					<option value="{$arr[1]}" {$arr[2]}> {$arr[0]} </option>
				{/foreach}
			</select>
		</div>
	</div>
{elseif ($uitype == 52) || ($uitype == 407)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '') && ($uitype != 407)}
				<div class="input-group" style="width: 100%;">
				{/if}
				{if ($uitype == 52)}
					<select id="{$fldname}" name="{$fldname}" class="form-control" tabindex="{$vt_tab}">
					{elseif ($uitype == 407)}
						<select id="{$fldname}" name="{$fldname}[]" class="form-control" size="5" tabindex="{$vt_tab}"
							multiple="multiple">
						{else}
							<select name="{$fldname}" class="form-control" tabindex="{$vt_tab}" title="">
							{/if}
							{if ($uitype == 407)}
								{foreach $fldvalue as $arr}
									<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
								{/foreach}
							{else}
								{if ($OP_MODE == 'create_view')}
									<option value="" disabled="disabled" selected="selected">{$usefldlabel}</option>
								{/if}
								{foreach $fldvalue as $key_one => $arr}
									{foreach $arr as $sel_value => $value}
										<option value="{$key_one}" {$value}>{$sel_value}</option>
									{/foreach}
								{/foreach}
							{/if}
						</select>
						{if ($OP_MODE == 'create_view') && ($mandatory_field != '') && ($uitype != 407)}
				</div>
			{/if}
		</div>
	</div>
{elseif ($uitype == 53)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="assigneduser" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{assign var=check value=1}
			{foreach key=key_one item=arr from=$fldvalue}
				{foreach key=sel_value item=value from=$arr}
					{if $value ne ''}{assign var=check value=$check*0}{else}{assign var=check value=$check*1}{/if}
				{/foreach}
			{/foreach}
			{if $check eq 0}
				{assign var=select_user value='checked'}
				{assign var=style_user value='display:block'}
				{assign var=style_group value='display:none'}
			{else}
				{assign var=select_group value='checked'}
				{assign var=style_user value='display:none'}
				{assign var=style_group value='display:block'}
			{/if}
			<input type="radio" id="assigntype-u" name="assigntype" value="U" tabindex="{$vt_tab}" {$select_user}
				onclick="toggleAssignType(this.value)" placeholder=""
				style="margin-right: 0.5em; margin-top: 0; vertical-align: middle;" /><label for="assigntype-u"
				style="margin-bottom: 0; vertical-align: middle;">{$APP.LBL_USER}</label>
			{if $secondvalue neq ''}
				<input type="radio" id="assigntype-g" name="assigntype" value="T" {$select_group}
					onclick="toggleAssignType(this.value)"
					style="margin-left: 1.2em; margin-right: 0.5em; margin-top: 0; vertical-align: middle;"
					placeholder="" /><label for="assigntype-g"
					style="margin-bottom: 0; vertical-align: middle;">{$APP.LBL_GROUP}</label>
			{/if}
			<span id="assign_user" style="{$style_user}">
				<select name="{$fldname}" class="form-control" style="margin-top: .5em;" title="">
					{foreach key=key_one item=arr from=$fldvalue}
						{foreach key=sel_value item=value from=$arr}
							<option value="{$key_one}" {$value}>{$sel_value}</option>
						{/foreach}
					{/foreach}
				</select>
			</span>
			{if $secondvalue neq ''}
				<span id="assign_team" style="{$style_group}">
					<select name="assigned_group_id" class="form-control" style="margin-top: .5em;" title="">
						{foreach key=key_one item=arr from=$secondvalue}
							{foreach key=sel_value item=value from=$arr}
								<option value="{$key_one}" {$value}>{$sel_value}</option>
							{/foreach}
						{/foreach}
					</select>
				</span>
			{/if}
		</div>
	</div>
{elseif ($uitype == 56)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{*<span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="checkbox-nice">
				{if ($fldname == 'notime') && ($ACTIVITY_MODE == 'Events')}
					<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" onclick="toggleTime()"
						{if ($fldvalue == 1)} checked="checked" {/if} />
				{elseif ($fldvalue == 1) || (($fldname == 'filestatus') && ($MODE == 'create'))}
					<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" checked="checked" />
				{else}
					<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}"
						{if ( $PROD_MODE eq 'create' &&  $fldname|substr:0:3 neq 'cf_') ||( $fldname|substr:0:3 neq 'cf_' && $PRICE_BOOK_MODE eq 'create' ) || $USER_MODE eq 'create'}
						checked="checked" {/if} />
				{/if}
				<label for="{$fldname}"></label>
			</div>
		</div>
	</div>
{elseif ($uitype == 63)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<input type="text" name="{$fldname}" value="{$fldvalue}" size="2" tabindex="{$vt_tab}" placeholder="">&nbsp;
			<select id="{$fldname}" name="duration_minutes" tabindex="{$vt_tab}" class="form-control">
				{foreach $secondvalue as $labelval => $selectval}
					<option value="{$labelval}" {$selectval}>{$labelval}</option>
				{/foreach}
			</select>
		</div>
	</div>
{elseif ($uitype == 71)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($fldname == "unit_price") && ($fromlink != 'qcreate')}
				<span id="multiple_currencies">
					<div class="input-group" style="width: 100%;">
						<span class="input-group-addon" style="cursor: default; background-color: #eee;"><i
								class="fa fa-money"></i></span>
						<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
							tabindex="{$vt_tab}" onBlur="updateUnitPrice('unit_price', '{$BASE_CURRENCY}');"
							style="border-bottom-left-radius: 0 !important; border-top-left-radius: 0 !important;" />
					</div>
					{if ($MASS_EDIT != '1')}
						&nbsp;<a href="javascript:updateUnitPrice('unit_price', '{$BASE_CURRENCY}');" class="md-trigger mrg-b-lg"
							data-modal="currency_class">{$APP.LBL_MORE_CURRENCIES}</a>
					{/if}
				</span>
				{if ($MASS_EDIT != '1')}
					<div id="currency_class" class="md-modal md-effect-10">
						<div class="md-content">
							<div class="modal-header">
								<button class="md-close close" onclick="return false;">&times;</button>
								<h4 class="modal-title">{$MOD.LBL_PRODUCT_PRICES}
							</div>
							<div class="modal-body">
								<input type="hidden" name="base_currency" id="base_currency" value="{$BASE_CURRENCY}" />
								<input type="hidden" name="base_conversion_rate" id="base_currency" value="{$BASE_CURRENCY}" />
								<table class="table table-bordered" style="font-size: small;">
									<tr>
										<th>{$APP.LBL_CURRENCY}</th>
										<th>{$APP.LBL_PRICE}</th>
										<th>{$APP.LBL_CONVERSION_RATE}</th>
										<th>{$APP.LBL_RESET_PRICE}</th>
										<th>{$APP.LBL_BASE_CURRENCY}</th>
									</tr>
									{foreach $PRICE_DETAILS as $count => $price}
										<tr>
											{if ($price.check_value == 1) || ($price.is_basecurrency == 1)}
												{assign var=check_value value="checked"}
												{assign var=disable_value value=""}
											{else}
												{assign var=check_value value=""}
												{assign var=disable_value value="disabled=true"}
											{/if}
											{if ($price.is_basecurrency == 1)}
												{assign var=base_cur_check value="checked"}
											{else}
												{assign var=base_cur_check value=""}
											{/if}
											{if ($price.curname == $BASE_CURRENCY)}
												{assign var=call_js_update_func value="updateUnitPrice('$BASE_CURRENCY', 'unit_price');"}
											{else}
												{assign var=call_js_update_func value=""}
											{/if}
											<td align="right">
												<div class="checkbox-nice checkbox-inline">
													<input type="checkbox" name="cur_{$price.curid}_check" id="cur_{$price.curid}_check"
														class="form-control"
														onclick="fnenableDisable(this,'{$price.curid}'); updateCurrencyValue(this,'{$price.curname}','{$BASE_CURRENCY}','{$price.conversionrate}');"
														{$check_value}>
													<label for="cur_{$price.curid}_check"></label>
												</div>
											</td>
											<td id="tdinfo_{$fldname}" align="left">
												<input type="text" id="{$price.curname}" name="{$price.curname}"
													value="{$price.curvalue}" class="form-control" size="10"
													onblur="{$call_js_update_func} fnpriceValidation('{$price.curname}');"
													placeholder="" {$disable_value} />
											</td>
											<td id="tdinfo_{$fldname}" align="left">
												<input type="text" name="cur_conv_rate{$price.curid}" value="{$price.conversionrate}"
													class="form-control" size="10" placeholder="" disabled="disabled" />
											</td>
											<td id="tdinfo_{$fldname}" align="center">
												<input type="button" id="cur_reset{$price.curid}" value="{$APP.LBL_RESET}"
													class="btn btn-default"
													onclick="updateCurrencyValue(this,'{$price.curname}','{$BASE_CURRENCY}','{$price.conversionrate}');"
													{$disable_value} />
											</td>
											<td id="tdinfo_{$fldname}">
												<div class="radio">
													<input type="radio" id="base_currency{$price.curid}" name="base_currency_input"
														value="{$price.curname}" class="form-control"
														onchange="updateBaseCurrencyValue()" {$base_cur_check} {$disable_value} />
													<label for="base_currency{$price.curid}">&nbsp;</label>
												</div>
											</td>
										</tr>
									{/foreach}
								</table>
							</div>
						</div>
					</div>
				{/if}
			{else}
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon" style="cursor: default; background-color: #eee;"><i
							class="fa fa-money"></i></span>
					<input type="text" id="{$fldname}" name="{$fldname}"
						placeholder="{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}9.999.999,99{else}9,999,999.99{/if}"
						value="{$fldvalue}" data-number-format="{$NUMBERING_FORMAT}"
						onkeyup="var field = jQuery (this); field.val (field.val ().replace (/[^\d.,-]/g, ''));"
						class="form-control"
						style="border-bottom-left-radius: 0 !important; border-top-left-radius: 0 !important;"
						tabindex="{$vt_tab}" />
				</div>
			{/if}
		</div>
	</div>
{elseif ($uitype == 85)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=" "
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<img src="{'skype.gif'|@vtiger_imageurl:$THEME}" alt="Skype" title="Skype" align="absmiddle" />
			<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
				tabindex="{$vt_tab}" />
		</div>
	</div>
{elseif ($uitype == 98)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="role_name">{$usefldlabel}{if ($mandatory_field)} <span
						class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($thirdvalue == 1) || ($PICK_ROLE)}
				<div class="input-group" style="width: 100%;">
					<input type="hidden" id="user_role" name="user_role" value="{$fldvalue}" />
					<input type="text" id="role_name" name="role_name" value="{$secondvalue}"
						class="form-control input-readonly b-right" tabindex="{$vt_tab}" readonly="readonly" />
					<div class="input-group-addon" onclick="openPopup();"><i class="fa fa-plus-circle"></i></div>
					<div class="input-group-addon"
						onclick="document.forms.EditView.role_name.value='';document.forms.EditView.user_role.value='';return false;">
						<i class="fa fa-eraser"></i>
					</div>
				</div>
			{else}
				<input type="hidden" id="user_role" name="user_role" value="{$fldvalue}" />
				<input type="text" id="role_name" name="role_name" value="{$secondvalue}" class="form-control input-readonly"
					tabindex="{$vt_tab}" readonly="readonly" />
			{/if}
		</div>
	</div>
{elseif ($uitype == 99)}
	{if ($MODE == 'create') || ($OP_MODE != 'create_view')}
		<div class="col-md-6">
			<div class="col-md-4">
				<div class="label-input">
					{include file='ContextualHelp.tpl'}
					<label for="{$fldname}" class="animate__animated ">
						{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
						{if $helpField neq NULL}
							<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
								data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
								{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
						{/if}
						{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
				</div>
			</div>
			<div class="form-group col-md-8 field-container" id="td_{$fldname}">
				{if ($MASS_EDIT == '1')}
					<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
						placeholder="" />
				{/if}
				<input type="password" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
					tabindex="{$vt_tab}" />
			</div>
		</div>
	{/if}
{elseif ($uitype == 101)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}_display" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<input type="hidden" id="{$fldname}" name="{$fldname}" value="{$secondvalue}" />
			<input type="text" id="{$fldname}_display" name="{$fldname}_display" value="{$fldvalue}"
				class="form-control input-readonly" readonly="readonly" />&nbsp;
			&nbsp;<input type="button" name="btn1" value="{$APP.LBL_CHANGE}" class="form-control"
				title="{$APP.LBL_CHANGE_TITLE}" accessKey="C"
				onclick='return window.open("index.php?module=Users&action=Popup&html=Popup_picker&form=vtlibPopupView&form_submit=false&fromlink={$fromlink}&recordid={$ID}&forfield={$fldname}","test","width=640,height=603,resizable=0,scrollbars=1");' />
			&nbsp;<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}"
				title="{$APP.LBL_CLEAR}"
				onclick="document.forms.EditView.{$fldname}.value=''; document.forms.EditView.{$fldname}_display.value=''; return false;"
				align="absmiddle" style="cursor: pointer;" />
		</div>
	</div>
{elseif ($uitype == 104)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
				tabindex="{$vt_tab}" />
		</div>
	</div>
{elseif ($uitype == 105)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="{$fldname}_hidden" value="{$maindata[3][0].name}" />
			<input type="file" id="{$fldname}" name="{$fldname}" value="{$maindata[3][0].name}" tabindex="{$vt_tab}"
				onchange="validateFilename(this);" />
			<div id="replaceimage">[{$IMAGENAME}]&nbsp;<a href="javascript:;" onClick="delUserImage({$ID})">Eliminar</a>
			</div>
			<br />
			{'LBL_IMG_FORMATS'|@getTranslatedString:$MODULE}
			{$maindata[3][0].name}
		</div>
	</div>
{elseif ($uitype == 106)}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				{include file='ContextualHelp.tpl'}
				<label for="{$fldname}" class="animate__animated ">
					{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
					{if $helpField neq NULL}
						<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
							data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
							{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
					{/if}
					{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
				<div class="input-group" style="width: 100%;">
					<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" class="form-control"
						tabindex="{$vt_tab}" />
				</div>
			{else}

				<input type="text" id="{$fldname}" name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}"
					class="form-control{if ($MODE == 'edit')} input-readonly{/if}" {if ($MODE == 'edit')} readonly="readonly"
				{/if} />
		{/if}
	</div>
</div>
{elseif ($uitype == 115)}
<div class="col-md-6">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="user_status" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
				placeholder="" />
		{/if}
		<select id="user_status" name="{$fldname}" class="form-control"
			{if ($secondvalue == 1) && ($CURRENT_USERID != $smarty.request.record)} tabindex="{$vt_tab}" 
			{else}
			disabled="disabled" {/if}>
			{foreach $fldvalue as $arr}
				<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
			{/foreach}
		</select>
	</div>
</div>
{elseif ($uitype == 116) || ($uitype == 117)}
<div class="col-md-6">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="{$fldname}" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
				placeholder="" />
		{/if}
		{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
			<div class="input-group" style="width: 100%;">
			{/if}
			<select id="{$fldname}" name="{$fldname}" class="form-control" tabindex="{$vt_tab}"
				{if ($secondvalue != 1) && ($uitype != 117)} disabled="disabled" {/if}>
				{if ($OP_MODE == 'create_view')}
					<option value="" selected="selected" disabled="disabled">{$usefldlabel}</option>
				{/if}
				{foreach $fldvalue as $uivalueid => $arr}
					{foreach $arr as $sel_value => $value}
						{if ($value == 'selected') && ($secondvalue != 1)}{assign var="curr_stat" value="$uivalueid"}{/if}
						<option value="{$uivalueid}" {$value}>{$sel_value|@getTranslatedCurrencyString}</option>
					{/foreach}
				{/foreach}
			</select>
			{if ($OP_MODE == 'create_view') && ($mandatory_field != '')}
			</div>
		{/if}
		{if ($curr_stat != '') && ($uitype != 117)}
			<input name="{$fldname}" type="hidden" value="{$curr_stat}">
		{/if}
	</div>
</div>
{elseif ($uitype == 156)}
<div class="col-md-6">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="{$fldname}" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
				placeholder="" />
		{/if}
		{if ($fldvalue == 'on')}
			{if (($secondvalue == 1) && ($CURRENT_USERID != $smarty.request.record)) || (($MODE == 'create') && ($IS_ADMIN))}
				<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" checked="checked" />
			{else}
				<input type="hidden" name="{$fldname}" value="on" />
				<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" checked="checked"
					disabled="disabled" />
			{/if}
		{else}
			{if (($secondvalue == 1) && ($CURRENT_USERID != $smarty.request.record)) || (($MODE == 'create') && ($IS_ADMIN))}
				<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" />
			{else}
				<input type="checkbox" id="{$fldname}" name="{$fldname}" tabindex="{$vt_tab}" disabled="disabled" />
			{/if}
		{/if}
	</div>
</div>
{elseif ($uitype == 256)}
{if $fldlabel eq $MOD.LBL_ADD_COMMENT}
	{assign var=fldvalue value=""}
{/if}
<div class="col-md-12">
	<div class="col-md-2">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="{$fldname}" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
	</div>
	<div class="form-group col-md-10 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
				placeholder="" />
		{/if}
		<textarea id="{$fldname}" name="{$fldname}" class="form-control ckeditor" tabindex="{$vt_tab}" cols="90"
			rows="8">{$fldvalue}</textarea>
		<script src="themes/{$THEME}/js/ckeditor/ckeditor.js"></script>
	</div>
</div>
{elseif ($uitype == 257) || ($uitype == 258)}
<div class="col-md-6">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="" class="animate__animated ">
				{*  <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}<span class="required">{$mandatory_field}</span> </label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
				placeholder="" />
		{/if}
		<input type="hidden" name="{$fldname}_hidden" value="{$maindata[3][0].name}" />
		<input type="hidden" name="{$fldname}_id" id="{$fldname}_id" value="{$maindata[3][0].id}" />
		<input type="hidden" name="id" value="" />
		<div class="row">
			<div class="fileUpload btn btn-simple" style="width: 9em;">
				<span>Examinar</span>
				<input type="file" name="{$fldname}" class="upload" value="{$maindata[3][0].name}" tabindex="{$vt_tab}"
					{if $MODULE eq 'formacion_cursos' && ($fldname|strstr:"img" || $fldname|strstr:"imagen")}
					onchange="validateFilenameImage(this,'{$UPLOAD_MAXSIZE}')" {else}
					onchange="validateFilename(this); validateFileSize(this,'{$UPLOAD_MAXSIZE}');" {/if} />
			</div>
			<div id="info_image">
				<h4 style="margin-top: .4em;">
					<label id="type_file" class="text-muted2"><span>({php}echo "Tama&ntilde;o maximo:
							".ini_get('upload_max_filesize');{/php})</label>

			</div>
			<div class="col-md-12" style="padding-right: 2px;"></div>
		</div>
		{$maindata[3][0].name}
	</div>
</div>
{elseif ($uitype eq 2202)}
{*<span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
{if $helpField neq NULL}
	<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
		data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class="" {*icon-Boton-color*}
		style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
{/if}{$CAMPOS_TIPO_GRID[$fldid]}
{elseif ($uitype == 2206)}
<div class="col-md-6">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="{$fldname}" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input data-all=" aquí 2" type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check"
				class="form-control" placeholder="" />
		{/if}
		{* Formatear el valor según el formato numérico del usuario *}
		{if $fldvalue && is_numeric($fldvalue)}
			{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}
				{assign var="formattedCalcValue" value=$fldvalue|number_format:2:',':'.'}
			{else}
				{assign var="formattedCalcValue" value=$fldvalue|number_format:2:'.':','}
			{/if}
		{else}
			{assign var="formattedCalcValue" value=$fldvalue}
		{/if}
		<div class="input-group" data-all=" aquí" style="width: 100%;">
			<span class="input-group-addon span-readonly"><i class="fa fa-subscript fa-fw fa-ed"></i></span>
			<input id="{$fldname}" name="{$fldname}" value="{if $formattedCalcValue}{$formattedCalcValue}{/if}"
				class="form-control input-readonly" placeholder=" Cálculo - GEN-AUTO AL GUARDAR "
				style="border-bottom-left-radius: 0 !important;border-top-left-radius: 0 !important;" tabindex=""
				readonly="readonly" type="text">
		</div>
	</div>
</div>
{elseif ($uitype == 2208)}
{include file='TableFieldEditView.tpl'}
{elseif ($uitype == 4096)}
<div class="col-md-6 attachments-field">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span>*}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}<span class="required">{$mandatory_field}</span></label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control"
				placeholder="" />
		{/if}
		<div class="row">
			<div class="col-md-12">
				<div class="col-md-12 drop-zone"
					style="background-color: #ffffff; border: 1px dashed #DDDDDD; height: 34px; line-height: 34px; position: relative; text-align: center;">
					<input type="file" multiple="multiple"
						onchange="AttachmentsUtils.addAttachments (event || window.event);"
						style="bottom: 0; cursor: pointer; left: 0; opacity: 0; position: absolute; top: 0; width: 100%;" />
					<span class="title">Arrastra archivos o clic aquí (Máx {$UPLOAD_MAXSIZE / (1024 * 1024)}MB)</span>
				</div>
				<ul class="col-md-12 attachments-container" style="list-style: none; margin-bottom:0; margin-top: 3px;"
					data-field-name="{$fldname}" data-maximum-file-size="{$UPLOAD_MAXSIZE / (1024 * 1024)}">
					{if (!empty ($FIELD_ATTACHMENTS[$fldname]))}
						{foreach $FIELD_ATTACHMENTS[$fldname] as $attachment}
							<li class="col-md-3 attachment"
								style="border: 1px solid #DDDDDD; margin-bottom: 3px; position: relative; width: 100%;">
								<button type="button" class="btn btn-close" onclick="AttachmentsUtils.deleteAttachment (this);"
									style="background-color: transparent; border: 0; bottom: 0; line-height: 1; right: 0; padding: 0 5px 2px 5px; position: absolute; text-transform: uppercase; z-index: 1000;">X</button>
								<div class="attachment-container">
									<a href="{$attachment.uri}" title="{$attachment.name}" target="_blank">
										<span class="attachment-name">{$attachment.name}</span><span class="attachment-size">
											({number_format ($attachment.size, 2, '.', '')} KB)</span>
									</a>
								</div>
								<input type="hidden" name="{$fldname}[-{$attachment.attachmentsid}][data]"
									class="attachment-data" />
								<input type="hidden" name="{$fldname}[-{$attachment.attachmentsid}][filename]"
									value="{$attachment.name}" class="attachment-filename" />
							</li>
						{/foreach}
					{/if}
				</ul>
			</div>
		</div>
	</div>
</div>
{elseif ($uitype == 8192) && (!empty ($fldchoices))}
<div class="col-md-12">
	{strip}
		<div class="col-md-12">
			{include file='ContextualHelp.tpl'}
			<label for="{$fldname}"
				style="font-weight: 300; font-size: 1.1em; line-height: 1.1; margin-bottom: 1px; margin-top: 1px;"
				class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)}<span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
		<div class="form-group col-md-12 field-container" id="td_{$fldname}">
			{if ($MASS_EDIT == '1')}
				<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
					placeholder="" />
			{/if}
			<div class="input-group pipeline-container" style="width: 100%;" data-pipeline-field="{$fldname}">
				<input type="hidden" id="{$fldname}" name="{$fldname}" class="pipeline-value" value="{$fldvalue}"
					onchange="if (window.onchange_{$fldname}) onchange_{$fldname}(this)" />
				{assign var='dummy' value=array_search($fldvalue, $fldchoices)}
				{if ($dummy === false)}
					{assign var='selectedChoicePosition' value=-1}
				{else}
					{assign var='selectedChoicePosition' value=$dummy}
				{/if}
				<div class="pipeline-chart">
					{foreach $fldchoices as $index => $choice}
						<button type="button" class="pipeline-element{if ($selectedChoicePosition >= $index)} selected{/if}"
							style="width: calc((100% - 16px) / {count ($fldchoices)});" data-index="{$index}"
							data-choice="{trim($choice)}" data-pipeline-value="{trim($choice)}"
							onclick="PipelineUtils.setValue (this);" title="{trim($choice)}">{trim($choice)}</button>
					{/foreach}
				</div>
			</div>
		</div>
	{/strip}
</div>
<script type="text/javascript" src="include/js/pipeline.js"></script>
<script type="text/javascript">
	(function(jQuery) {
		// Inyectar relaciones picklist->pipeline desde backend (vtiger_picklist2pipeline)
		window.picklistPipelineRelationships = {$PICKLIST_PIPELINE_RELATIONSHIPS};

		jQuery(function() {
			if ((typeof PipelineUtils === 'undefined') || (typeof PipelineUtils.initFilters !== 'function')) {
				return;
			}
			PipelineUtils.initFilters(window.picklistPipelineRelationships);
		});
	}(jQuery));
</script>
{elseif ($uitype == 5006)}
{* new video field*}
{math equation= rand() assign= "idVideo"}
<div class="col-md-6">
	<div class="col-md-4">
		<div class="label-input">
			{include file='ContextualHelp.tpl'}
			<label for="{$fldname}" class="animate__animated ">
				{* <span id="helpEV{$uitype}_{$fldname}" name="help" style="font-size:0.2em;"></span> *}
				{if $helpField neq NULL}
					<span id="{$idHelp}-{$fldname}" title="{$helpField['title']}" data-help-id="{$helpField['idHelp']}"
						data-module="{{$helpField['module']}}" onclick="HelpUtils.showHelpByField(this)" class=""
						{*icon-Boton-color*} style="font-size:1.2em;display: none;">&#10067;&#65038;</span>
				{/if}
				{$usefldlabel}{if ($mandatory_field)} <span class="required">{$mandatory_field}</span>{/if}</label>
		</div>
	</div>
	<div class="form-group col-md-8 field-container" id="td_{$fldname}">
		{if ($MASS_EDIT == '1')}
			<input type="checkbox" id="{$fldname}_mass_edit_check" name="{$fldname}_mass_edit_check" class="form-control"
				placeholder="http://www." />
		{/if}
		<div class="input-group" style="width: 100%;">
			<span class="input-group-addon"><i class="fa fa-file-video-o" aria-hidden="true" style="cursor: pointer"
					title="preview video" onclick="preview_{$idVideo}('{$fldname}')"></i></span>
			<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}"
				class="form-control" placeholder="http://www." />
		</div>
	</div>
	<script type="text/javascript">
		function isValidUrlVideo_{$idVideo} (value) {
		{literal}
			if ((value === null) || (value === undefined) || (value.trim() === '')) {
				return false;
			} else {
				return /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test (value);
			}
			}
		{/literal}
		function preview_{$idVideo}(fiedName) {
		var urlVideo = jQuery('#' + fiedName).val(),
			page     = "index.php?module={$MODULE}&action=PreviewVideo&Ajax=true&url=";
			if (isValidUrlVideo_{$idVideo} (urlVideo)) {
			ekkoLightBox = jQuery('<a href="' + page + urlVideo + '" data-process="NO" data-toggle="lightbox">&nbsp;</a>');
		ekkoLightBox.ekkoLightbox({ loadingMessage: "Cargando..." });
		}
		else {
			alert('Introduzca una url de video')
		}

		}
	</script>
</div>
{elseif ($uitype == 5010)}
{$fldvalue nofilter}
{/if}