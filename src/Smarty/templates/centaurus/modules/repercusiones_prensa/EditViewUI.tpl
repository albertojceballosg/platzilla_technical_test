{assign var="uitype" value=$maindata[0][0]}
{assign var="fldlabel" value=$maindata[1][0]}
{assign var="fldlabel_sel" value=$maindata[1][1]}
{assign var="fldlabel_combo" value=$maindata[1][2]}
{assign var="fldlabel_other" value=$maindata[1][3]}
{assign var="fldname" value=$maindata[2][0]|cat:'['|cat:$ID|cat:']'}
{assign var="fldclass" value=$maindata[2][0]|cat: ' other-field'}
{assign var="fldvalue" value=$maindata[3][0]}
{assign var="secondvalue" value=$maindata[3][1]}
{assign var="thirdvalue" value=$maindata[3][2]}
{assign var="typeofdata" value=$maindata[4]}
{assign var="vt_tab" value=$maindata[5][0]}
{assign var="paradic" value=$maindata[6]}
{assign var="fldid" value=$maindata[7]}
{if $typeofdata eq 'M'}
	{assign var="mandatory_field" value="*"}
{else}
	{assign var="mandatory_field" value=""}
{/if}
{* vtlib customization: Help information for the fields *}
{assign var="usefldlabel" value=$fldlabel}
{assign var="fldhelplink" value=""}
{if $FIELDHELPINFO && $FIELDHELPINFO.$fldname}
	{assign var="fldhelplinkimg" value='help_icon.gif'|@vtiger_imageurl:$THEME}
	{assign var="fldhelplink" value='<i class="fa fa-life-saver" onclick="vtlib_field_help_show(this, \'$fldname\');" ></i>'}
	{if $uitype neq '10'}
		{assign var="usefldlabel" value="$fldlabel $fldhelplink"}
	{/if}
{/if}
{* END *}
{* vtlib customization *}
{if $uitype eq '10'}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$fldlabel.displaylabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if count($fldlabel.options) eq 1}
			{assign var="use_parentmodule" value=$fldlabel.options.0}
			<input type='hidden' class='small' name="{$fldname}_type" value="{$use_parentmodule}">
			{else}
			{if $fromlink eq 'qcreate'}
			<select id="{$fldname}_type" class="form-control {$fldclass}" name="{$fldname}_type" onChange='document.QcEditView.{$fldname}_display.value=""; document.QcEditView.{$fldname}.value="";' style="margin-bottom: .5em;">
				{else}
				<select id="{$fldname}_type" class="form-control {$fldclass}" name="{$fldname}_type" onChange='document.EditView.{$fldname}_display.value=""; document.EditView.{$fldname}.value=""; $("qcform").innerHTML=""' style="margin-bottom: .5em;">
					{/if}
					{foreach item=option from=$fldlabel.options}
						<option value="{$option}" {if $fldlabel.selected == $option}selected{/if}>{$option|@getTranslatedString:$option}
						</option>
					{/foreach}
				</select>
				{/if}
				{if $MASS_EDIT eq '1'}
					<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
				{/if}
				{$fldhelplink}
				<div class="input-group" style="width: 100%;">
					<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$fldvalue.entityid}" class="for-filter" />
					<input id="{$fldname}_display" name="{$fldname}_display" id="edit_{$fldname}_display" readonly type="text" class="form-control {$fldclass} {if $OP_MODE eq 'create_view' && $mandatory_field neq ''}placeholderStyle{/if} input-readonly b-right" value="{$fldvalue.displayvalue}" {if $OP_MODE eq 'create_view'} {*placeholder="{$fldlabel.displaylabel}"*} {/if}>
					{if $fromlink eq 'qcreate'}
						<div class="input-group-addon" onclick='return window.open("index.php?module="+ document.QcEditView.{$fldname}_type.value +"&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield={$fldname}&srcmodule={$MODULE}&forrecord={$ID}","test","width=640, height=602, resizable=0, scrollbars=1, top=150, left=200");'>
							<i class="fa fa-plus-circle"></i>
						</div>
					{else}
						<div class="input-group-addon" onclick="return window.open ('index.php?module=' + document.EditView.{$fldname}_type.value + '&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield={$fldname}&srcmodule={$MODULE}&forrecord={$ID}' + serializeNonEmptyFormData ('EditView'), 'test', 'width=640,height=602,resizable=0,scrollbars=1,top=150,left=200');">
							<i class="fa fa-plus-circle"></i>
						</div>
					{/if}
					<div class="input-group-addon" onClick="document.forms.EditView.{$fldname}.value=''; document.forms.EditView.{$fldname}_display.value=''; return false;">
						<i class="fa fa-eraser"></i>
					</div>
				</div>
		</div>
	</div>
	{* END *}

{elseif $uitype eq 2 && $fldname eq 'company'}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
			<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
				<i class="fa fa-building">
				</i>
			</span>
				<input type="text" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass}" size="15" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if}>
			</div>
		</div>
	</div>
{elseif $uitype eq 2}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
				<div class="input-group" style="width: 100%;">
					{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
						<i class="fa"></i>
					</span>*}
					<input type="text" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass}" size="15" {*placeholder="{$usefldlabel}"*}>
				</div>
			{else}
				<input type="text" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass}" size="15" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if}>
			{/if}
		</div>
	</div>
	{elseif $uitype eq 3 || $uitype eq 4}<!-- Non Editable field, only configured value will be loaded -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <label class="required">{$mandatory_field}</label></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
			<span class="input-group-addon span-readonly"><i class="fa fa-cubes"></i>
			</span>
				<input readonly type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" {if $MODE eq 'edit'} value="{$fldvalue}" {else} value="{$usefldlabel} - {$MOD_SEQ_ID}" {/if} class="form-control {$fldclass} input-readonly" style="border-bottom-left-radius: 0 !important;border-top-left-radius: 0 !important;">
			</div>
		</div>
	</div>
{elseif $uitype eq 11}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}" />
			{/if}
			<div class="input-group" style="width: 100%;">
			<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
				{if $fldname eq 'phone'}
				<i class="fa fa-phone">
					{elseif $fldname eq 'mobile' || $fldname eq 'num_cel'}
					<i class="fa fa-mobile">
						{elseif $fldname eq 'fax'}
						<i class="fa fa-fax">
							{else}
							<i class="fa fa-home">
								{/if}
								{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
								&nbsp;<font color="red">{$mandatory_field}</font>
							</i>
			</span>
				{else}
				</i>
				</span>
				{/if}
				<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}" class="form-control {$fldclass}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if} />
			</div>
		</div>
	</div>
{elseif $uitype eq 13 || $uitype eq '104'}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel}<span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
			<span class="input-group-addon">
				<i class="fa fa-envelope">{*if $OP_MODE neq 'create_view' && $mandatory_field neq ''}&nbsp;<font color="red">{$mandatory_field}</font>{/if*}</i>
			</span>
				<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}" class="form-control {$fldclass}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if}>
			</div>
		</div>
	</div>
{elseif $uitype eq 1 || $uitype eq 7 || $uitype eq 102}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			<div class="input-group" style="width: 100%;">
				{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
					<i class="fa"></i>
				</span>*}
				{/if}
				{if $fldname eq 'tickersymbol' && $MODULE eq 'Accounts'}
					<input type="text" name="{$fldname}" tabindex="{$vt_tab}" id="{$fldname}" value="{$fldvalue}" class="form-control {$fldclass}" onBlur="{if $fldname eq 'tickersymbol' && $MODULE eq 'Accounts'}sensex_info(){/if}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*}{/if}>
					<span id="vtbusy_info" style="display:none;">
					<img src="{'vtbusy.gif'|@vtiger_imageurl:$THEME}" border="0">
				</span>
				{elseif $typeofdata eq 'I'}
					<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{if $fldvalue gt 0}{$fldvalue}{else}0{/if}" class="form-control {$fldclass}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if}>
				{else}
					<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}" class="form-control {$fldclass}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if}>
				{/if}
				{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			</div>
			{/if}
		</div>
	</div>
{elseif $uitype eq 9}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel}&nbsp;{$APP.COVERED_PERCENTAGE}
						<span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}

				<label>{$usefldlabel}&nbsp;{$APP.COVERED_PERCENTAGE}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
			<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
				<i class="fa">%
				</i></span>
				<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}" class="form-control {$fldclass}" {*placeholder="{$usefldlabel}&nbsp;{$APP.COVERED_PERCENTAGE}"*} >
			</div>
		</div>
	</div>
{elseif $uitype eq 19 || $uitype eq 20}
	<!-- In Add Comment are we should not display anything -->
	{if $fldlabel eq $MOD.LBL_ADD_COMMENT}
		{assign var=fldvalue value=""}
	{/if}
	<div class="col-md-12">
		{*<div class="col-md-2">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span> </h4></label>
			</div>
		</div>*}
		<div class="form-group col-md-12 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel}</label>
			{elseif $mandatory_field neq ''}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<textarea class="form-control {$fldclass}" tabindex="{$vt_tab}" name="{$fldname}" cols="90" rows="8" {if $OP_MODE eq 'create_view' && $mandatory_field eq ''} {*placeholder="{$usefldlabel}"*} {/if}>{$fldvalue}</textarea>
			{if $fldlabel eq $MOD.Solution}
				<input type="hidden" name="helpdesk_solution" value='{$fldvalue}'>
			{/if}
		</div>
	</div>
{elseif $uitype eq 21 || $uitype eq 24}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel}</label>
			{elseif $mandatory_field neq ''}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<textarea value="{$fldvalue}" name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}" rows=2 {if $OP_MODE eq 'create_view' && $mandatory_field eq ''} {*placeholder="{$usefldlabel}"*} {/if}>{$fldvalue}</textarea>
		</div>
	</div>
{elseif $uitype eq 15 || $uitype eq 16  || $uitype eq '31' || $uitype eq '32' || $uitype eq '404'}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}

			{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			<div class="input-group" style="width: 100%;">
				{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
					<i class="fa"></i>
				</span>*}
				{/if}

				{if $MODULE eq 'Calendar'}
				<select name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
					{elseif $fldname eq 'bill_state' || $fldname eq 'bill_city' || $fldname eq 'birth_state'}
					<select name="{$fldname}" id="{$fldname}" nameText="{$usefldlabel}" tabindex="{$vt_tab}" class="form-control {$fldclass} for-filter" onchange="if (window.onchange_{$fldname}) onchange_list_{$fldname}(this);">
						{else}
						<select name="{$fldname}" id="{$fldname}" nameText="{$usefldlabel}" tabindex="{$vt_tab}" class="form-control {$fldclass} for-filter" onchange="if (window.onchange_{$fldname}) onchange_{$fldname}(this)">
							{/if}
							<option value="" disabled selected>{$usefldlabel}</option>
							{if $fldname eq 'bill_state' || $fldname eq 'bill_city' || $fldname eq 'birth_state'}
							{if $MODE eq 'edit'}
								{if $fldname eq 'bill_state'}
									{assign var="renderOption" value="$billState_option"}
								{/if}
								{if $fldname eq 'bill_city'}
									{assign var="renderOption" value="$billCity_option"}
								{/if}
								{if $fldname eq 'birth_state'}
									{assign var="renderOption" value="$birthState_option"}
								{/if}

							{foreach item=arr1 from=$renderOption}
								<option value="{$arr1[1]}" {$arr1[2]}>{$arr1[0]}</option>
							{/foreach}

							{else}
								<option value="" disabled selected>{$usefldlabel}</option>
							{/if}
								<script>
									jQuery ("#bill_country").change (function () {ldelim}
										var name = jQuery ("#bill_country").val ();
										var namestate = jQuery ("#bill_state").attr ('nameText');
										var nameCity = jQuery ("#bill_city").attr ('nameText');
										var modeT = jQuery ("input[name=mode]").val ();

										if (name == '' || name == 'undefined' || name == '--Ninguno--' || name != 'España') {ldelim}
											var optionList1 = '';
											var optionList2 = '';

											if (modeT == 'edit') {ldelim}
												optionList1 = '<option value="" disabled selected nameText="' + namestate + '">--Ninguno--</option>';
												optionList2 = '<option value="" disabled selected nameText="' + nameCity + '">--Ninguno--</option>';
												{rdelim} else {ldelim}
												optionList1 = '<option value="" disabled selected nameText="' + namestate + '">' + namestate + '</option>';
												optionList2 = '<option value="" disabled selected nameText="' + nameCity + '">' + nameCity + '</option>';
												{rdelim}

											jQuery ("#bill_state").html ('');
											jQuery ("#bill_state").append (optionList1);

											jQuery ("#bill_city").html ('');
											jQuery ("#bill_city").append (optionList2);

											return false;


											{rdelim} else {ldelim}

											new Ajax.Request (
													'index.php',
													{
														ldelim}queue: { ldelim}position: 'end', scope: 'command'{rdelim},
														method:       'post',
														postBody:     'module=candidatos&action=candidatosAjax&file=GeoChange&sub_mode=bill_country&ajax=true&name=' + name + '&nameText=' + namestate + '&mode=' + modeT,
														onComplete:   function (response) {ldelim}

															if (response.responseText == 'ERROR_SUBMODE') {ldelim}

																alert (alert_arr.ERROR_SUBMODE);

																{rdelim} else {ldelim}

																jQuery ("#bill_state").html ('');
																jQuery ("#bill_state").append (response.responseText);

																{rdelim}

															{rdelim}
														{rdelim}
											);

											{rdelim}

										{rdelim});

									jQuery ("#bill_state").change (function () {ldelim}

										var idState = jQuery ("#bill_state option:selected").attr ('target_state');
										var namestate = jQuery ("#bill_state").attr ('nameText');
										var nameCity = jQuery ("#bill_city").attr ('nameText');
										var modeT = jQuery ("input[name=mode]").val ();

										if (idState == '' || idState == 'undefined' || idState == '0') {ldelim}

											alert (alert_arr.PICKLIST_CANNOT_BE_EMPTY);
											return false;

											{rdelim} else {ldelim}
											new Ajax.Request (
													'index.php',
													{
														ldelim}queue: { ldelim}position: 'end', scope: 'command'{rdelim},
														method:       'post',
														postBody:     'module=candidatos&action=candidatosAjax&file=GeoChange&sub_mode=bill_state&ajax=true&idState=' + idState + '&nameText=' + nameCity + '&mode=' + modeT,
														onComplete:   function (response) {ldelim}

															if (response.responseText == 'ERROR_SUBMODE') {ldelim}
																alert (alert_arr.ERROR_SUBMODE);

																{rdelim} else {ldelim}

																jQuery ("#bill_city").html ('');
																jQuery ("#bill_city").append (response.responseText);

																{rdelim}

															{rdelim}
														{rdelim}
											);

											{rdelim}

										{rdelim});

									jQuery ("#birth_country").change (function () {ldelim}

										var name = jQuery ("#birth_country").val ();
										var namestate = jQuery ("#birth_state").attr ('nameText');
										var modeT = jQuery ("input[name=mode]").val ();

										if (name == '' || name == 'undefined' || name == '--Ninguno--' || name != 'España') {ldelim}
											var optionList1 = '';
											var optionList2 = '';

											if (modeT == 'edit') {ldelim}
												optionList1 = '<option value="" disabled selected nameText="' + namestate + '">--Ninguno--</option>';
												{rdelim} else {ldelim}
												optionList1 = '<option value="" disabled selected nameText="' + namestate + '">' + namestate + '</option>';
												{rdelim}

											jQuery ("#birth_state").html ('');
											jQuery ("#birth_state").append (optionList1);

											return false;


											{rdelim} else {ldelim}

											new Ajax.Request (
													'index.php',
													{
														ldelim}queue: { ldelim}position: 'end', scope: 'command'{rdelim},
														method:       'post',
														postBody:     'module=candidatos&action=candidatosAjax&file=GeoChange&sub_mode=birth_country&ajax=true&name=' + name + '&nameText=' + namestate + '&mode=' + modeT,
														onComplete:   function (response) {ldelim}

															if (response.responseText == 'ERROR_SUBMODE') {ldelim}

																alert (alert_arr.ERROR_SUBMODE);

																{rdelim} else {ldelim}

																jQuery ("#birth_state").html ('');
																jQuery ("#birth_state").append (response.responseText);

																{rdelim}

															{rdelim}
														{rdelim}
											);

											{rdelim}

										{rdelim});
								</script>
							{else}
							{foreach item=arr from=$fldvalue}
							{if $arr[0] eq $APP.LBL_NOT_ACCESSIBLE}
								<option value="{$arr[0]}" {$arr[2]}>{$arr[0]}</option>
							{else}
								<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
							{/if}
								{foreachelse}
								<option value=""></option>
								<option value="" style='color: #777777' disabled>{$APP.LBL_NONE}</option>
							{/foreach}
							{/if}
						</select>
						{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			</div>
			{/if}

			{$paradic}
		</div>
	</div>
{elseif $uitype eq 33}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<select MULTIPLE name="{$fldname}[]" size="4" tabindex="{$vt_tab}" class="form-control {$fldclass}">
				{foreach item=arr from=$fldvalue}
					<option value="{$arr[1]}" {$arr[2]}> {$arr[0]} </option>
				{/foreach}
			</select>
		</div>
	</div>
{elseif $uitype eq 53}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for="assigneduser"><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4>
				</label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{assign var=check value=1}
			{foreach key=key_one item=arr from=$fldvalue}
				{foreach key=sel_value item=value from=$arr}
					{if $value ne ''}
						{assign var=check value=$check*0}
					{else}
						{assign var=check value=$check*1}
					{/if}
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
			<input type="hidden" name="assigntype" value="U" />
			<select id="assigneduser" name="{$fldname}" class="form-control {$fldclass}">
				{foreach key=key_one item=arr from=$fldvalue}
					{foreach key=sel_value item=value from=$arr}
						<option value="{$key_one}" {$value}>{$sel_value}</option>
					{/foreach}
				{/foreach}
			</select>
		</div>
	</div>
{elseif $uitype eq 52 || $uitype eq 77 || $uitype eq 407}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$fldlabel}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}

			{if $OP_MODE eq 'create_view' && $mandatory_field neq '' && $uitype neq 407}
			<div class="input-group" style="width: 100%;">
				{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
					<i class="fa"></i>
				</span>*}
				{/if}

				{if $uitype eq 52}
				<select name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
					{elseif $uitype eq 77}
					<select name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
						{elseif $uitype eq 407}
						<select multiple name="{$fldname}[]" id="{$fldname}" size="5" tabindex="{$vt_tab}" class="form-control {$fldclass}">
							{else}
							<select name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
								{/if}

								{if $uitype eq 407}
									{foreach item=arr from=$fldvalue}
										<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
									{/foreach}
								{else}
									{if $OP_MODE eq 'create_view'}
										<option value="" disabled selected>{$usefldlabel}</option>
									{/if}

									{foreach key=key_one item=arr from=$fldvalue}
										{foreach key=sel_value item=value from=$arr}
											<option value="{$key_one}" {$value}>{$sel_value}</option>
										{/foreach}
									{/foreach}
								{/if}
							</select>
							{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			</div>
			{/if}
		</div>
	</div>
{elseif $uitype eq 51}
	{if $MODULE eq 'Accounts'}
		{assign var='popuptype' value = 'specific_account_address'}
	{else}
		{assign var='popuptype' value = 'specific_contact_account_address'}
	{/if}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input readonly name="account_name" class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*}{/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='return window.open("index.php?module=Accounts&action=Popup&popuptype={$popuptype}&form=TasksEditView&form_submit=false&fromlink={$fromlink}&recordid={$ID}","test","width=640,height=602,resizable=0,scrollbars=1");'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.account_id.value=''; document.forms.EditView.account_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 50}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input readonly name="account_name" class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='return window.open("index.php?module=Accounts&action=Popup&popuptype=specific&form=TasksEditView&form_submit=false&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1");'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.account_id.value=''; document.forms.EditView.account_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 73}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input readonly name="account_name" class="form-control {$fldclass} input-readonly b-right" id="single_accountid" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*}{/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='return window.open("index.php?module=Accounts&action=Popup&popuptype=specific_account_address&form=TasksEditView&form_submit=false&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1");'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.account_id.value=''; document.forms.EditView.account_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 75 || $uitype eq 81}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $uitype eq 81}
				{assign var="pop_type" value="specific_vendor_address"}
			{else}{assign var="pop_type" value="specific"}
			{/if}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="vendor_name" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*} {/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='return window.open("index.php?module=Vendors&action=Popup&html=Popup_picker&popuptype={$pop_type}&form=EditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1");'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.vendor_id.value='';document.forms.EditView.vendor_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 57}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="{$fldname}_name" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*} {/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				{if $fromlink eq 'qcreate'}
					<div class="input-group-addon" onclick='selectContact("false","general",document.QcEditView,"{$fldname}","{$fldname}_name")'>
						<i class="fa fa-plus-circle"></i>
					</div>
				{else}
					<div class="input-group-addon" onclick='selectContact("false","general",document.EditView,"{$fldname}","{$fldname}_name")'>
						<i class="fa fa-plus-circle"></i>
					</div>
				{/if}
				<div class="input-group-addon" onClick="document.forms.EditView.{$fldname}.value=''; document.forms.EditView.{$fldname}_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 58}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="campaignname" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{* placeholder="{$usefldlabel}"*} {/if} >
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='return window.open("index.php?module=Campaigns&action=Popup&html=Popup_picker&popuptype=specific_campaign&form=EditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1");'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.campaignid.value=''; document.forms.EditView.campaignname.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 80}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="salesorder_name" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{* placeholder="{$usefldlabel}"*} {/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='selectSalesOrder();'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.salesorder_id.value=''; document.forms.EditView.salesorder_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 78}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="quote_name" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*} {/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='selectQuote();'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.quote_id.value=''; document.forms.EditView.quote_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 76}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="potential_name" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<div class="input-group-addon" onclick='selectPotential();'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.potential_id.value=''; document.forms.EditView.potential_name.value='';return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 17}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			<!--<font color="red">{$mandatory_field}</font>
			<label>{$usefldlabel} </label> -->
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
					<i class="fa fa-wordpress">
					</i>
				</span>
				<!-- JA 29/07/2016 Re-Open Pedido [ TT11103 ] Validaciones Ajax -->
				<input id="{$fldname}" class="form-control {$fldclass}" type="text" tabindex="{$vt_tab}" name="{$fldname}" onkeyup="validateUrl('{$fldname}');" value="{$fldvalue}" {if $OP_MODE eq 'create_view'} {*placeholder="{$usefldlabel}"*}{/if}>
			</div>
		</div>
	</div>
{elseif $uitype eq 85}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<!-- JA 29/07/2016 Re-Open Pedido [ TT11103 ] Validaciones Ajax -->
			<img src="{'skype.gif'|@vtiger_imageurl:$THEME}" alt="Skype" title="Skype" LANGUAGE=javascript align="absmiddle"></img>
			<input id="{$fldname}" class='form-control {$fldclass}' type="text" tabindex="{$vt_tab}" name="{$fldname}" value="{$fldvalue}">
		</div>
	</div>
{elseif $uitype eq 71 || $uitype eq 72}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label {if $OP_MODE eq 'create_view'}style="display:none;"{/if}>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $fldname eq "unit_price" && $fromlink neq 'qcreate'}
				<span id="multiple_currencies">
					<div class="input-group" style="width: 100%;">
						<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
							<i class="fa fa-money"></i>
						</span>
						<input name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" type="text" class="form-control {$fldclass}" onBlur=" updateUnitPrice('unit_price', '{$BASE_CURRENCY}');" style="border-bottom-left-radius: 0 !important; border-top-left-radius: 0 !important;" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
					</div>
					{if $MASS_EDIT neq 1}
						&nbsp;
						{*<a href="javascript:void(0);" onclick="updateUnitPrice('unit_price', '{$BASE_CURRENCY}'); toggleShowHide('currency_class','multiple_currencies');">{$APP.LBL_MORE_CURRENCIES} &raquo;</a>*}
						<a href="javascript:updateUnitPrice('unit_price', '{$BASE_CURRENCY}');" class="md-trigger mrg-b-lg" data-modal="currency_class">{$APP.LBL_MORE_CURRENCIES}</a>
					{/if}
				</span>
				{if $MASS_EDIT neq 1}
					<div id="currency_class" class="md-modal md-effect-10">
						<div class="md-content">
							<div class="modal-header">
								<button class="md-close close" onclick="return false;">&times;</button>
								<h4 class="modal-title">{$MOD.LBL_PRODUCT_PRICES}</h4>
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
									{foreach item=price key=count from=$PRICE_DETAILS}
										<tr>
											{if $price.check_value eq 1 || $price.is_basecurrency eq 1}
												{assign var=check_value value="checked"}
												{assign var=disable_value value=""}
											{else}
												{assign var=check_value value=""}
												{assign var=disable_value value="disabled=true"}
											{/if}

											{if $price.is_basecurrency eq 1}
												{assign var=base_cur_check value="checked"}
											{else}
												{assign var=base_cur_check value=""}
											{/if}
											{if $price.curname eq $BASE_CURRENCY}
												{assign var=call_js_update_func value="updateUnitPrice('$BASE_CURRENCY', 'unit_price');"}
											{else}
												{assign var=call_js_update_func value=""}
											{/if}
											<td align="right">
												<div class="checkbox-nice checkbox-inline">
													<input type="checkbox" name="cur_{$price.curid}_check" id="cur_{$price.curid}_check" class="form-control {$fldclass}" onclick="fnenableDisable(this,'{$price.curid}'); updateCurrencyValue(this,'{$price.curname}','{$BASE_CURRENCY}','{$price.conversionrate}');" {$check_value}>
													<label for="cur_{$price.curid}_check">{*{$price.currencylabel|@getTranslatedCurrencyString} ({$price.currencysymbol})*}</label>
												</div>
											</td>
											<td id="tdinfo_{$fldname}" align="left">
												<input {$disable_value} type="text" size="10" class="form-control {$fldclass}" name="{$price.curname}" id="{$price.curname}" value="{$price.curvalue}" onBlur="{$call_js_update_func} fnpriceValidation('{$price.curname}');">
											</td>
											<td id="tdinfo_{$fldname}" align="left">
												<input disabled=true type="text" size="10" class="form-control {$fldclass}" name="cur_conv_rate{$price.curid}" value="{$price.conversionrate}">
											</td>
											<td id="tdinfo_{$fldname}" align="center">
												<input {$disable_value} type="button" class="btn btn-default" id="cur_reset{$price.curid}" onclick="updateCurrencyValue(this,'{$price.curname}','{$BASE_CURRENCY}','{$price.conversionrate}');" value="{$APP.LBL_RESET}" />
											</td>
											<td id="tdinfo_{$fldname}">
												<div class="radio">
													<input {$disable_value} type="radio" class="form-control {$fldclass}" id="base_currency{$price.curid}" name="base_currency_input" value="{$price.curname}" {$base_cur_check} onchange="updateBaseCurrencyValue()" />
													<label id="base_currency{$price.curid}">&nbsp;</label>
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
					<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!"><i class="fa fa-money"></i></span>
					<input name="{$fldname}" tabindex="{$vt_tab}" type="text" class="form-control {$fldclass}" value="{if $OP_MODE neq 'create_view'}{if $fldvalue gt 0}{$fldvalue}{else}0.00{/if}{/if}" style="border-bottom-left-radius: 0 !important; border-top-left-radius: 0 !important;" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
				</div>
			{/if}
		</div>
	</div>
{elseif $uitype eq 56}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>&nbsp;</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="checkbox-nice">
				{if $fldname eq 'notime' && $ACTIVITY_MODE eq 'Events'}
					{if $fldvalue eq 1}
						<input name="{$fldname}" id="{$fldname}" type="checkbox" tabindex="{$vt_tab}" onclick="toggleTime()" checked>
					{else}
						<input name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" type="checkbox" onclick="toggleTime()">
					{/if}
					<!-- For Portal Information we need a hidden field existing_portal with the current portal value -->
				{elseif $fldname eq 'portal'}
					<input type="hidden" name="existing_portal" value="{$fldvalue}">
					<input name="{$fldname}" id="{$fldname}" type="checkbox" tabindex="{$vt_tab}" {if $fldvalue eq 1}checked{/if}>
				{else}
					{if $fldvalue eq 1}
						<input name="{$fldname}" id="{$fldname}" type="checkbox" tabindex="{$vt_tab}" checked>
					{elseif $fldname eq 'filestatus'&& $MODE eq 'create'}
						<input name="{$fldname}" id="{$fldname}" type="checkbox" tabindex="{$vt_tab}" checked>
					{else}
						<input name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" type="checkbox" {if ( $PROD_MODE eq 'create' &&  $fldname|substr:0:3 neq 'cf_') ||( $fldname|substr:0:3 neq 'cf_' && $PRICE_BOOK_MODE eq 'create' ) || $USER_MODE eq 'create'}checked{/if}>
					{/if}
				{/if}
				<label for="{$fldname}">{*{$usefldlabel}*}</label>
			</div>
		</div>
	</div>
{elseif $uitype eq 23 || $uitype eq 5 || $uitype eq 6 || $uitype eq 123}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			<script type="text/javascript">
				jQuery ('#jscal_field_{$fldname}').datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });
			</script>
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<div class="input-group-addon" style="border: 1px solid #ddd !important">
					<i class="fa fa-calendar" id="jscal_trigger_{$fldname}">
						{*if $OP_MODE eq 'create_view' && $mandatory_field neq '' }
							<font color="red" >&nbsp;{$mandatory_field}</font>
						{/if*}
					</i>
				</div>
				{if $uitype eq 123}
					<input name="{$fldname}" tabindex="{$vt_tab}" id="jscal_field_{$fldname}" type="text" size="15" maxlength="18" value="{$fldvalue}" class="form-control {$fldclass} pull-right">
					<script>
						jQuery ('#jscal_field_{$fldname}').datepicker ({ format: "yy-m-d", language: 'es', weekStart: 1 });
					</script>
				{else}
					<input name="{$fldname}" tabindex="{$vt_tab}" id="jscal_field_{$fldname}" type="text" class="form-control {$fldclass} pull-right input-readonly b-left" size="11" maxlength="18" {if $OP_MODE eq 'create_view'}value="" {else}value="{$date_val}"{/if} readonly="readonly" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
					<script>
						jQuery ('#jscal_field_{$fldname}').datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });
					</script>
					{if $uitype eq 6}
						{*<input name="time_start" tabindex="{$vt_tab}"  size="5" maxlength="5" type="text" class="form-control pull-right" value="{$time_val}">*}
					{/if}

				{if $uitype eq 6 && $QCMODULE eq 'Event'}
				<input name="dateFormat" type="hidden" value="{$dateFormat}" class="form-control {$fldclass} pull-right">
				{/if}
				{if $uitype eq 23 && $QCMODULE eq 'Event'}
				<input name="time_end" size="5" maxlength="5" type="text" value="{$time_val}" class="form-control {$fldclass} pull-right">
				{/if}
				{/if}
			</div>
		</div>
	</div>
{elseif $uitype eq 63}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<input name="{$fldname}" type="text" size="2" value="{$fldvalue}" tabindex="{$vt_tab}">&nbsp;
			<select name="duration_minutes" tabindex="{$vt_tab}" class="form-control {$fldclass}">
				{foreach key=labelval item=selectval from=$secondvalue}
					<option value="{$labelval}" {$selectval}>{$labelval}</option>
				{/foreach}
			</select>
		</div>
	</div>
{elseif $uitype eq 68 || $uitype eq 66 || $uitype eq 62}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$fldlabel_combo[combo]}</h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			<label>
				{if $fromlink eq 'qcreate'}
				<select class="form-control {$fldclass}" name="parent_type" onChange='document.QcEditView.parent_name.value=""; document.QcEditView.parent_id.value=""'>
					{else}
					<select class="form-control {$fldclass}" name="parent_type" onChange='document.EditView.parent_name.value=""; document.EditView.parent_id.value=""'>
						{/if}
						{section name=combo loop=$fldlabel}
							<option value="{$fldlabel_combo[combo]}" {$fldlabel_sel[combo]}>{$fldlabel[combo]} </option>
						{/section}
					</select>
			</label>
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="parent_id_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<input name="parent_name" readonly class="form-control {$fldclass} input-readonly b-right" id="parentid" type="text" value="{$fldvalue}">
				<div class="input-group-addon" onclick='return window.open("index.php?module="+ document.EditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1,top=150,left=200");'>
					<i class="fa fa-plus-circle"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 357}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>To:&nbsp;</h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			<label>To:&nbsp;</label>
			<input name="{$fldname}" type="hidden" value="{$secondvalue}">
			<textarea class="form-control {$fldclass} input-readonly " readonly name="parent_name" rows="2">{$fldvalue}</textarea>&nbsp;
			<select name="parent_type" class="form-control {$fldclass}">
				{foreach key=labelval item=selectval from=$fldlabel}
					<option value="{$labelval}" {$selectval}>{$labelval}</option>
				{/foreach}
			</select>
			&nbsp;
			{if $fromlink eq 'qcreate'}
				<img tabindex="{$vt_tab}" src="{'select.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='return window.open("index.php?module="+ document.QcEditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
				&nbsp;
				<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="document.forms.EditView.parent_id.value=''; document.forms.EditView.parent_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
			{else}
				<img tabindex="{$vt_tab}" src="{'select.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='return window.open("index.php?module="+ document.EditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
				&nbsp;
				<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="document.forms.EditView.parent_id.value=''; document.forms.EditView.parent_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
			{/if}
			<input name="ccmail" type="text" {*placeholder="CC"*} class="form-control {$fldclass}" value="">
			<input name="bccmail" type="text" {*placeholder="BCC"*} class="form-control {$fldclass}" value="">
		</div>
	</div>
{elseif $uitype eq 59}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<input name="product_name" readonly class="form-control {$fldclass} input-readonly b-right" type="text" value="{$fldvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
				<div class="input-group-addon" onclick='return window.open("index.php?module=Products&action=Popup&html=Popup_picker&form=HelpDeskEditView&popuptype=specific&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=1,top=150,left=200");'>
					<i class="fa fa-plus-circle"></i>
				</div>
				<div class="input-group-addon" onClick="document.forms.EditView.product_id.value=''; document.forms.EditView.product_name.value=''; return false;">
					<i class="fa fa-eraser"></i>
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 55 || $uitype eq 255}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1' && $fldvalue neq ''}
				<label>{$APP.Salutation} ss</label>
				<input type="checkbox" name="salutationtype_mass_edit_check" id="salutationtype_mass_edit_check" class="form-control {$fldclass}">
				<br />
			{/if}
			{if $uitype eq 55}
				{if $MASS_EDIT eq '1'}
					{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
						<i class="fa fa-user"></i>
					</span>*}
					<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
				{/if}
			{elseif $uitype eq 255}
				{if $MASS_EDIT eq '1'}
					<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
				{/if}
			{/if}
			{if $fldvalue neq ''}
				<div class="col-md-3" style="padding:0; padding-right:.3em;" name="salutation-element">
					<select name="salutationtype" class="form-control {$fldclass}">
						{foreach item=arr from=$fldvalue}
							<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
						{/foreach}
					</select>
					{*if $MASS_EDIT eq '1'}<br />{/if*}
				</div>
			{/if}

			{if $fldvalue neq ''}
			<div class="col-md-9" style="padding-right:0; padding-left:0;">
				{/if}
				<div class="input-group" style="width: 100%;">
					<input type="text" name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}" value="{$secondvalue}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
				</div>
				{if $fldvalue neq ''}
			</div>
			{/if}
		</div>
	</div>
{elseif $uitype eq 22}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<textarea name="{$fldname}" cols="30" tabindex="{$vt_tab}" rows="2">{$fldvalue}</textarea>
		</div>
	</div>
{elseif $uitype eq 14}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
				 {"LBL_TIMEFIELD"|@getTranslatedString}
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="input-group" style="width: 100%;">
				<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
					<i class="fa fa-tachometer">
						{*if $OP_MODE eq 'create_view' && $mandatory_field neq '' }
							&nbsp;<font color="red">{$mandatory_field}</font>
						{/if*}
					</i>
				</span>
				<input type="text" tabindex="{$vt_tab}" name="{$fldname}" id="{$fldname}" value="{$fldvalue}" class="form-control {$fldclass}">
			</div>
		</div>
	</div>
{elseif $uitype eq 69}
	<div class="col-lg-6">
		<div class="col-xs-12 col-sm-4">
			<div id="img-input" class="label-input">
			</div>
			<div id="displaySize" class="text-muted2"></div>
		</div>
		<div class="col-xs-12 col-sm-8">
			<div class="form-group field-container" id="td_{$fldname}">
				{if $MASS_EDIT eq '1'}
					<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
				{/if}
				{if $MODULE eq 'Products'}
				<input name="del_file_list" id="del_file_list" type="hidden" value="">
				<div id="files_list" style="border: 1px solid grey; width: 500px; padding: 5px; background: rgb(255, 255, 255) none repeat scroll 0%; -moz-background-clip: initial; -moz-background-origin: initial; -moz-background-inline-policy: initial; font-size: x-small">{$APP.Files_Maximum_6} |( Menor a {$UPLOAD_MAXSIZE} MB c/u)
					<input id="my_file_element" type="file" name="file_1" tabindex="{$vt_tab}" onchange="validateFilenameImageProducts(this,'{$UPLOAD_MAXSIZE}');" />
					<div id="displaySize"></div>
					<!--input type="hidden" name="file_1_hidden" value=""/-->
					{assign var=image_count value=0}
					{if $maindata[3].0.name neq '' && $DUPLICATE neq 'true'}
						{foreach name=image_loop key=num item=image_details from=$maindata[3]}
							<div align="center">
								<img src="{$image_details.path}{$image_details.name}" height="50">&nbsp;&nbsp;[{$image_details.orgname}]<input id="file_{$num}" value="Eliminar" type="button" class="crmbutton small delete" onclick='this.parentNode.parentNode.removeChild(this.parentNode);delRowEmt("{$image_details.orgname}")'>
							</div>
							{assign var=image_count value=$smarty.foreach.image_loop.iteration}
						{/foreach}
					{/if}
				</div>
				<script>
					{*<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->*}
					var multi_selector = new MultiSelector (document.getElementById ('files_list'), 6);
					multi_selector.count = {$image_count}
							{*<!-- Pass in the file element -->*}
							multi_selector.addElement (document.getElementById ('my_file_element'));
				</script>
				{elseif $MODULE eq 'Contacts'}
				<div class="row">
					<div class="col-xs-12 col-sm-12">
						<label for=""><h2>{$usefldlabel} <span class="required">{$mandatory_field}</span></h2></label>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-5">
						<div class="fileUpload btn btn-simple" style="width: 9em;">
							<span>Buscar foto</span>
							<input name="{$fldname}" type="file" class="upload" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="previewImage(this,'{$UPLOAD_MAXSIZE}');" />
						</div>
					</div>
					<div class="col-xs-12 col-sm-7" style="padding-right: 2px;">
						<div id="info_image">
							<h4>
								{*<label id="max_size" class="text-muted2">Peso máximo del archivo debe ser menor a {$UPLOAD_MAXSIZE} MB</label>
								<br/>*}
								<label id="type_file" class="text-muted2">
									<span>Tipos de archivo permitidos:</span><br />
									<span>.jpg / .jpeg / .png / .gif </span></label>
							</h4>
						</div>
						<input name="{$fldname}_hidden" type="hidden" value="{$maindata[3].0.name}" />
						<input type="hidden" name="id" value="" />
						{if $maindata[3].0.name != "" && $DUPLICATE neq 'true'}
							<div id="replaceimage">[{$maindata[3].0.orgname}]
								<a href="javascript:;" onClick="delimage({$ID})">Eliminar</a></div>
						{/if}
					</div>
					{else}
					<input name="{$fldname}" id="{$fldname}" type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="validateFilename(this);" />
					<input name="{$fldname}_hidden" type="hidden" value="{$maindata[3].0.name}" />
					<input type="hidden" name="id" value="" />
					{if $maindata[3].0.name != "" && $DUPLICATE neq 'true'}
						<div id="replaceimage">[{$maindata[3].0.orgname}]
							<a href="javascript:;" onClick="delimage({$ID})">Eliminar</a></div>
					{/if}
					<div id="displaySize"></div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{elseif $uitype eq 61}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}" disabled>
			{/if}
			<input name="{$fldname}" id="{$fldname}" type="file" value="{$secondvalue}" tabindex="{$vt_tab}" onchange="validateFilename(this)" />
			<input type="hidden" name="{$fldname}_hidden" value="{$secondvalue}" />
			<input type="hidden" name="id" value="" />{$fldvalue}
		</div>
	</div>
{elseif $uitype eq 156}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}" >{/if}
			{if $fldvalue eq 'on'}
				{if ($secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record) || ($MODE == 'create' && $IS_ADMIN)}
					<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox" checked>
				{else}
					<input name="{$fldname}" type="hidden" value="on">
					<input name="{$fldname}" disabled tabindex="{$vt_tab}" type="checkbox" checked>
				{/if}
			{else}
				{if ($secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record) || ($MODE == 'create' && $IS_ADMIN)}
					<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox">
				{else}
					<input name="{$fldname}" disabled tabindex="{$vt_tab}" type="checkbox">
				{/if}
			{/if}
		</div>
	</div>
	{elseif $uitype eq 98}<!-- Role Selection Popup -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $thirdvalue eq 1 || $PICK_ROLE}
				{*<a href="javascript:openPopup();"><img src="{'select.gif'|@vtiger_imageurl:$THEME}" align="absmiddle" border="0"></a>*}
				<div class="input-group" style="width: 100%;">
					<input name="user_role" id="user_role" value="{$fldvalue}" type="hidden">
					<input name="role_name" id="role_name" readonly class="form-control {$fldclass} input-readonly b-right" tabindex="{$vt_tab}" value="{$secondvalue}" type="text">
					<div class="input-group-addon" onclick="openPopup();">
						<i class="fa fa-plus-circle"></i>
					</div>
					<div class="input-group-addon" onClick="document.forms.EditView.role_name.value='';document.forms.EditView.user_role.value='';return false;">
						<i class="fa fa-eraser"></i>
					</div>
				</div>
			{else}
				<input name="role_name" id="role_name" tabindex="{$vt_tab}" class="form-control {$fldclass} input-readonly" readonly value="{$secondvalue}" type="text">
				&nbsp;
				<input name="user_role" id="user_role" value="{$fldvalue}" type="hidden">
			{/if}
		</div>
	</div>
	{elseif $uitype eq 104}<!-- Mandatory Email Fields -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<input type="text" name="{$fldname}" id="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
		</div>
	</div>
	{elseif $uitype eq 115}<!-- for Status field Disabled for nonadmin -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record}
			<select id="user_status" name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
				{else}
				<select id="user_status" disabled name="{$fldname}" class="form-control {$fldclass}">
					{/if}
					{foreach item=arr from=$fldvalue}
						<option value="{$arr[1]}" {$arr[2]} >{$arr[0]}</option>
					{/foreach}
				</select>
		</div>
	</div>
{elseif $uitype eq 105}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $MODE eq 'edit' && $IMAGENAME neq ''}
				<input name="{$fldname}" id="{$fldname}" type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="validateFilename(this);" />
				<div id="replaceimage">
					[{$IMAGENAME}]&nbsp;<a href="javascript:;" onClick="delUserImage({$ID})">Eliminar</a>
				</div>
				<br>
				{'LBL_IMG_FORMATS'|@getTranslatedString:$MODULE}
				<input name="{$fldname}_hidden" type="hidden" value="{$maindata[3].0.name}" />
			{else}
				<input name="{$fldname}" id="{$fldname}" type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="validateFilename(this);" />
				<br>
				{'LBL_IMG_FORMATS'|@getTranslatedString:$MODULE}
				<input name="{$fldname}_hidden" type="hidden" value="{$maindata[3].0.name}" />
			{/if}
			<input type="hidden" name="id" value="" />
			{$maindata[3].0.name}
		</div>
	</div>
{elseif $uitype eq 256}
	<!-- In Add Comment are we should not display anything -->
	{if $fldlabel eq $MOD.LBL_ADD_COMMENT}
		{assign var=fldvalue value=""}
	{/if}
	<div class="col-md-12">
		<div class="col-md-2">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-10 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<textarea class="form-control {$fldclass} ckeditor" tabindex="{$vt_tab}" name="{$fldname}" cols="90" rows="8">{$fldvalue}</textarea>
			<script src="themes/{$THEME}/js/ckeditor/ckeditor.js"></script>
		</div>
	</div>
{elseif $uitype eq 257 || $uitype eq 258}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel}<span class="required">{$mandatory_field}</span></h4></label>
				{*<small>({php}echo "Tama&ntilde;o máximo: ".ini_get('upload_max_filesize');{/php})</small>*}
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{*<input name="{$fldname}"  type="file" style="max-width:270px;" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" {if $MODULE eq 'formacion_cursos' && ($fldname|strstr:"img" || $fldname|strstr:"imagen")}onchange="validateFilenameImage(this,'{$UPLOAD_MAXSIZE}')"{else}onchange="validateFilename(this);validateFileSize(this,'{$UPLOAD_MAXSIZE}');"{/if} />
			<div id="displaySize"></div>
			<div id="replaceimage{$maindata[3].0.id}">
				[{$IMAGENAME}]&nbsp;<a href="javascript:;" onClick="delImage({$maindata[3].0.id});J('#{$fldname}_id').val('');">Eliminar</a>
			</div>*}
			<div class="row">
				<div class="col-md-12">
					<div class="fileUpload btn btn-simple" style="width: 9em;">
						<span>Examinar</span>
						<input name="{$fldname}" type="file" class="upload" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" {if $MODULE eq 'formacion_cursos' && ($fldname|strstr:"img" || $fldname|strstr:"imagen")}onchange="validateFilenameImage(this,'{$UPLOAD_MAXSIZE}')" {else}onchange="validateFilename(this);validateFileSize(this,'{$UPLOAD_MAXSIZE}');"{/if} />
					</div>
					<div id="info_image">
						<h4 style="margin-top: .4em;">
							<label id="type_file" class="text-muted2"><span>({php}echo "Tama&ntilde;o maximo: ".ini_get('upload_max_filesize');{/php})</label>
						</h4>
					</div>
				</div>
				<div class="col-md-12" style="padding-right: 2px;">
					<input name="{$fldname}_hidden" type="hidden" value="{$maindata[3].0.name}" />
					<input type="hidden" name="id" value="" />
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div id="replaceimage{$maindata[3].0.id}">
						[{$IMAGENAME}]&nbsp;<a href="javascript:;" onClick="delImage({$maindata[3].0.id});J('#{$fldname}_id').val('');">Eliminar</a>
					</div>
				</div>
			</div>
			<input name="{$fldname}_hidden" type="hidden" value="{$maindata[3].0.name}" />
			<input type="hidden" name="{$fldname}_id" id="{$fldname}_id" value="{$maindata[3].0.id}" />
			{$maindata[3].0.name}
		</div>
	</div>
{elseif $uitype eq 103}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}" >{/if}
			<input type="text" name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
		</div>
	</div>
	{elseif $uitype eq 101}<!-- for reportsto field USERS POPUP -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<input id="{$fldname}_display" name="{$fldname}_display" readonly type="text" value="{$fldvalue}" class="form-control {$fldclass} input-readonly " />&nbsp;
			<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}" id="{$fldname}" />
			&nbsp;<input title="{$APP.LBL_CHANGE_TITLE}" accessKey="C" type="button" class="form-control {$fldclass}" value='{$APP.LBL_CHANGE}' name="btn1" onclick='return window.open("index.php?module=Users&action=Popup&html=Popup_picker&form=vtlibPopupView&form_submit=false&fromlink={$fromlink}&recordid={$ID}&forfield={$fldname}","test","width=640,height=603,resizable=0,scrollbars=1");'>
			&nbsp;<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" onClick="document.forms.EditView.{$fldname}.value=''; document.forms.EditView.{$fldname}_display.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		</div>
	</div>
	{elseif $uitype eq 116 || $uitype eq 117}<!-- for currency in users details-->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$fldlabel.displaylabel}</label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			<div class="input-group" style="width: 100%;">
				{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
					<i class="fa"></i>
				</span>*}
				{/if}

				{if $secondvalue eq 1 || $uitype eq 117}
				<select name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
					{else}
					<select disabled name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
						{/if}

						{if $OP_MODE eq 'create_view'}
							<option value="" disabled selected>{$usefldlabel}</option>
						{/if}
						{foreach item=arr key=uivalueid from=$fldvalue}
							{foreach key=sel_value item=value from=$arr}
								<option value="{$uivalueid}" {$value}>{$sel_value|@getTranslatedCurrencyString}</option>
								<!-- code added to pass Currency field value, if Disabled for nonadmin -->
								{if $value eq 'selected' && $secondvalue neq 1}
									{assign var="curr_stat" value="$uivalueid"}
								{/if}
								<!--code ends -->
							{/foreach}
						{/foreach}
					</select>
					{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
			</div>
			{/if}
			<!-- code added to pass Currency field value, if Disabled for nonadmin -->
			{if $curr_stat neq '' && $uitype neq 117}
				<input name="{$fldname}" type="hidden" value="{$curr_stat}">
			{/if}
			<!--code ends -->
		</div>
	</div>
{elseif $uitype eq 106}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{*if $OP_MODE neq 'create_view'}
				<font color="red">{$mandatory_field}</font>
				<label>{$usefldlabel} </label>
			{/if*}
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}

			{if $OP_MODE eq 'create_view' && $mandatory_field neq ''}
				<div class="input-group" style="width: 100%;">
					{*<span class="input-group-addon" style="cursor: default; important! background-color: #eee; important!">
						<i class="fa">
						</i>
					</span>*}
					<input type="text" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" class="form-control {$fldclass}" {*placeholder="{$usefldlabel}"*}>
				</div>
			{else}
				{if $MODE eq 'edit'}
					<input type="text" readonly name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass} input-readonly ">
				{else}
					<input type="text" name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class="form-control {$fldclass}" {*placeholder="{$usefldlabel}"*}>
				{/if}
			{/if}
		</div>
	</div>
{elseif $uitype eq 99}
	{if $MODE eq 'create' || $OP_MODE neq 'create_view'}
		<div class="col-md-6">
			<div class="col-md-4">
				<div class="label-input">
					<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
				</div>
			</div>
			<div class="form-group col-md-8 field-container" id="td_{$fldname}">
				{if $MASS_EDIT eq '1'}
					<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
				{/if}
				<input type="password" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" class="form-control {$fldclass}" {if $OP_MODE eq 'create_view'}{*placeholder="{$usefldlabel}"*}{/if}>
			</div>
		</div>
	{/if}

{elseif $uitype eq 30}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			{assign var=check value=$secondvalue[0]}
			{assign var=yes_val value=$secondvalue[1]}
			{assign var=no_val value=$secondvalue[2]}
			<input type="radio" name="set_reminder" tabindex="{$vt_tab}" value="Yes" {$check}>&nbsp;{$yes_val}&nbsp;
			<input type="radio" name="set_reminder" value="No">&nbsp;{$no_val}&nbsp;
			{foreach item=val_arr from=$fldvalue}
				{assign var=start value="$val_arr[0]"}
				{assign var=end value="$val_arr[1]"}
				{assign var=sendname value="$val_arr[2]"}
				{assign var=disp_text value="$val_arr[3]"}
				{assign var=sel_val value="$val_arr[4]"}
				<select name="{$sendname}" class="form-control {$fldclass}" style="margin-top: .5em;">
					{section name=reminder start=$start max=$end loop=$end step=1 }
						{if $smarty.section.reminder.index eq $sel_val}
							{assign var=sel_value value="SELECTED"}
						{else}
							{assign var=sel_value value=""}
						{/if}
						<OPTION VALUE="{$smarty.section.reminder.index}"
						"{$sel_value}">{$smarty.section.reminder.index}</OPTION>
					{/section}
				</select>
				&nbsp;{$disp_text}
			{/foreach}
		</div>
	</div>
{elseif $uitype eq 26}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="require">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<select name="{$fldname}" tabindex="{$vt_tab}" class="form-control {$fldclass}">
				{foreach item=v key=k from=$fldvalue}
					<option value="{$k}">{$v}</option>
				{/foreach}
			</select>
		</div>
	</div>
{elseif $uitype eq 27}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$fldlabel_other}&nbsp; <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<select class="form-control {$fldclass}" name="{$fldname}" onchange="changeDldType((this.value=='I')? 'file': 'text');">
				{section name=combo loop=$fldlabel}
					<option value="{$fldlabel_combo[combo]}" {$fldlabel_sel[combo]} >{$fldlabel[combo]} </option>
				{/section}
			</select>
			<script>
				function vtiger_{$fldname}Init () {ldelim}
					var d = document.getElementsByName ('{$fldname}')[ 0 ];
					var type = (d.value == 'I') ? 'file' : 'text';

					changeDldType (type, true);
					{rdelim}
				if (typeof window.onload == 'function') {ldelim}
					var oldOnLoad = window.onload;
					document.body.onload = function () {ldelim}
						vtiger_{$fldname}Init ();
						oldOnLoad ();
						{rdelim}
					{rdelim} else {ldelim}
					window.onload = function () {ldelim}
						vtiger_{$fldname}Init ();
						{rdelim}
					{rdelim}
			</script>
		</div>
	</div>
{elseif $uitype eq 28}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}" disabled>
			{/if}
			<script type="text/javascript">
				function changeDldType (type, onInit) {ldelim}
					var fieldname = '{$fldname}';
					if (!onInit) {ldelim}
						var dh = getObj ('{$fldname}_hidden');
						if (dh) dh.value = '';
						{rdelim}

					var v1 = document.getElementById (fieldname + '_E__');
					var v2 = document.getElementById (fieldname + '_I__');
					var msg = document.getElementById ('limitmsg');

					var text = v1.type == "text" ? v1 : v2;
					var file = v1.type == "file" ? v1 : v2;
					var filename = document.getElementById (fieldname + '_value');
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
				<input name="{$fldname}" id="{$fldname}_I__" type="file" value="{$secondvalue}" tabindex="{$vt_tab}" onchange="validateFilename(this);validateFileSize(this,'{$UPLOAD_MAXSIZE}');" style="display: none;" />
				<input type="hidden" name="{$fldname}_hidden" value="{$secondvalue}" />
				<input type="hidden" name="id" value="" />
				<input type="text" id="{$fldname}_E__" name="{$fldname}" class="form-control {$fldclass}" value="{$secondvalue}" /><br>
				<div id="displaySize"></div>
	        <span id="{$fldname}_value" style="display:none;">
			{if $secondvalue neq ''}
				[{$secondvalue}]
			{/if}
			</span>
			</div>
			<span id="limitmsg" style="color:red; display:none;">{'LBL_MAX_SIZE'|@getTranslatedString:$MODULE} {$UPLOADSIZE}{'LBL_FILESIZEIN_MB'|@getTranslatedString:$MODULE}</span>
		</div>
	</div>
{elseif $uitype eq 83} <!-- Handle the Tax in Inventory -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel}</h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{foreach item=tax key=count from=$TAX_DETAILS}
				{if $tax.check_value eq 1}
					{assign var=check_value value="checked"}
					{assign var=show_value value="visible"}
				{else}
					{assign var=check_value value=""}
					{assign var=show_value value="hidden"}
				{/if}
				<div class="row">
					<div class="col-md-12 checkbox-nice checkbox-inline">
						<div class="col-md-1">
							<input type="checkbox" name="{$tax.check_name}" id="{$tax.check_name}" onclick="fnshowHide(this,'{$tax.taxname}')" {$check_value}>
							<label for="{$tax.check_name}">{*{$tax.taxlabel} {$APP.COVERED_PERCENTAGE} *}</label>
						</div>
						<div class="col-md-11">
							<input type="text" class="form-control {$fldclass}" name="{$tax.taxname}" id="{$tax.taxname}" value="{$tax.percentage}" style="visibility:{$show_value};" onBlur="fntaxValidation('{$tax.taxname}')">
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
{elseif $uitype eq 89}
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<input type="text" tabindex="{$vt_tab}" readonly name="{$fldname}" id="{$fldname}" value="{$fldvalue}" maxlength="6" size="6" style="background-color:#{$fldvalue}">
		<span style="cursor:pointer;" id="icp_{$fldname}" name="icp_{$fldname}">
			<img src="include/colorpicker/images/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle">
		</span>
			<script>{literal}
				jQuery ('{/literal}#icp_{$fldname}{literal}').ColorPicker ({
					color:    '{/literal}#{$fldvalue}{literal}',
					onChange: function (hsb, hex, rgb) {
						jQuery ('#{/literal}{$fldname}{literal}').css ('backgroundColor', '#' + hex);
						jQuery ('#{/literal}{$fldname}{literal}').val (hex);
					}
				});
				{/literal}</script>
		</div>
	</div>
	{elseif $uitype eq 108}<!-- for reportsto field USERS POPUP -->
	<div class="col-md-6">
		<div class="col-md-4">
			<div class="label-input">
				<label for=""><h4>{$usefldlabel} <span class="required">{$mandatory_field}</span></h4></label>
			</div>
		</div>
		<div class="form-group col-md-8 field-container" id="td_{$fldname}">
			{if $MASS_EDIT eq '1'}
				<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="form-control {$fldclass}">
			{/if}
			<div class="element-group">
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon" style="cursor: default; important!">Min</span>
					<input type="text" name="periodo_prueba[min]" value="{$fldvalue.min}" class="form-control {$fldclass}" />
				</div>
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon" style="cursor: default; important!">Max</span>
					<input type="text" name="periodo_prueba[max]" value="{$fldvalue.max}" class="form-control {$fldclass}" />
				</div>
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon" style="cursor: default; important!">Ini</span>
					<input type="text" name="periodo_prueba[ini]" value="{$fldvalue.ini}" class="form-control {$fldclass}" />
				</div>
				<div class="input-group" style="width: 100%;">
					<span class="input-group-addon" style="cursor: default; important!">Ord</span>
					<select type="text" name="periodo_prueba[ord]" class="form-control {$fldclass}">
						<option value="asc" {if $fldvalue.ord eq 'asc'}selected{/if}>ASC</option>
						<option value="desc" {if $fldvalue.ord eq 'desc'}selected{/if}>DESC</option>
					</select>
				</div>
			</div>
		</div>
	</div>
{/if}
