{*
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ *}
<script language="JavaScript" type="text/javascript" src="include/js/customview.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/general.js"></script>
<script language="JavaScript" type="text/javascript" src="modules/gestion_module/fieldValidationsAjax.js"></script>



<script language="JavaScript">
{literal}
function check(){
	var blocklabel = document.getElementById('blocklabel');
	var val = trim(blocklabel.value);
	if(val == "")
	{
		alert(alert_arr.BLOCK_NAME_CANNOT_BE_BLANK);
		return false;
	}
	return true;
}
{/literal}</script>
<script language="javascript">

function getCustomFieldList(customField)
{ldelim}
	var modulename = customField.options[customField.options.selectedIndex].value;
	$('module_info').innerHTML = '{$MOD.LBL_CUSTOM_FILED_IN} "'+modulename+'" {$APP.LBL_MODULE}';
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&fld_module='+modulename+'&parenttab=gestion_module&ajax=true',
			onComplete: function(response) {ldelim}
				$("cfList").update(response.responseText);
			{rdelim}
		{rdelim}
	);
{rdelim}

function changeFieldorder(what_to_do,fieldid,blockid,modulename)
{ldelim}
	$('vtbusy_info').style.display = "block";
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=changeOrder&fld_module='+modulename+'&parenttab=gestion_module&what_to_do='+what_to_do+'&fieldid='+fieldid+'&blockid='+blockid+'&ajax=true',
			onComplete: function(response) {ldelim}
				$("cfList").update(response.responseText);
				$('vtbusy_info').style.display = "none";
			{rdelim}
		{rdelim}
	);
{rdelim}


function changeShowstatus(tabid,blockid,modulename)
{ldelim}
	var display_status = $('display_status_'+blockid).value;
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=changeOrder&fld_module='+modulename+'&parenttab=gestion_module&what_to_do='+display_status+'&tabid='+tabid+'&blockid='+blockid+'&ajax=true',
			onComplete: function(response) {ldelim}
				$("cfList").update(response.responseText);
			{rdelim}
		{rdelim}

	);
{rdelim}




function changeBlockorder(what_to_do,tabid,blockid,modulename)
{ldelim}
	$('vtbusy_info').style.display = "block";
		new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=changeOrder&fld_module='+modulename+'&parenttab=gestion_module&what_to_do='+what_to_do+'&tabid='+tabid+'&blockid='+blockid+'&ajax=true',
			onComplete: function(response) {ldelim}
				$("cfList").update(response.responseText);
				$('vtbusy_info').style.display = "none";
			{rdelim}
		{rdelim}

	);
{rdelim}

<!-- end of tanmoy on 6/09/2007-->

function availableReport(tabid)
{ldelim}
	var opcion = $('check_reportAvailable').checked;
	new Ajax.Request(
                'index.php',
                {ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                        method: 'post',
                        postBody: 'module=gestion_module&action=gestion_moduleAjax&file=ChangeReport&tabid='+tabid+'&availableReport='+opcion,
                        onComplete: function(response) {ldelim}
                        		if(response.responseText == 'status_change'){ldelim}
									location.reload();
                        		{rdelim}else{ldelim}
                        			console.log (response);
                        			alert("ERROR");
                        		{rdelim}

                        {rdelim}
                {rdelim}
        );

{rdelim}


{literal}
function deleteCustomField(id, fld_module, colName, uitype)
{
       if(confirm(alert_arr.ARE_YOU_SURE_YOU_WANT_TO_DELETE)){

       	if(id == null || id == 'undefined' || id == ''){
       		id = jQuery('#fieldid').val();
       	}

       	if(fld_module == null || fld_module == 'undefined' || fld_module == ''){
       		fld_module = jQuery('#fldmodule').val();
       	}

       	if(colName == null || colName == 'undefined' || colName == ''){
       		colName = jQuery('#colName').val();
       	}

       	if(uitype == null || uitype == 'undefined' || uitype == ''){
       		uitype = jQuery('#uitype').val();
       	}       	

        $('vtbusy_info').style.display = "block";
			new Ajax.Request(
				'index.php',
				{queue: {position: 'end', scope: 'command'},
					method: 'post',
					postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=deleteCustomField&ajax=true&fld_module='+fld_module+'&fld_id='+id+'&colName='+colName+'&uitype='+uitype,
					onComplete: function(response) {
						$("cfList").update(response.responseText);
						gselected_fieldtype = '';
						$('vtbusy_info').style.display = "none";

						alert(alert_arr.FIELD_DELETE_SUCCESS);
						//jQuery('.md-overlay').css({opacity: 0.0, visibility: 'hidden'});
						location.reload();
					}
				}
			);
		}else{
		fninvsh('editfield_'+id);

		}
}

function deleteCustomBlock(module,blockid,no){

	jQuery('.md-overlay').css({opacity: 1, visibility: 'visible'});
	if(no > 0){
		alert(alert_arr.PLEASE_MOVE_THE_FIELDS_TO_ANOTHER_BLOCK);
		location.reload();

	}else{
		if(confirm(alert_arr.ARE_YOU_SURE_YOU_WANT_TO_DELETE_BLOCK)){
			$('vtbusy_info').style.display = "block";
			new Ajax.Request(
				'index.php',
				{queue : {position : 'end', scope: 'command'},
				method : 'post',
				postBody: 'module=gestion_module&action=gestion_moduleAjax&fld_module='+module+'&file=LayoutBlockList&sub_mode=deleteCustomBlock&ajax=true&blockid='+blockid,
				onComplete: function(response) {
					$("cfList").update(response.responseText);
					$('vtbusy_info').style.display = "none";
					alert(alert_arr.BLOCK_DELETE_SUCCESS);
					location.reload();
				}
				}
			);
		}
	}
}


function getCreateCustomBlockForm(modulename,mode)
{	
	var checlabel = check();
	if(checlabel == false)
		return false;
	var blocklabel = document.getElementById('blocklabel');
	var val = trim(blocklabel.value);
	var blockid = document.getElementById('after_blockid').value;
	var blockType = document.getElementById('block_type').value;
	var relmodule = document.getElementById('relmodule').value;
	var relfieldname = document.getElementById('relfieldname').value;
	var update_parentfield = document.getElementById('update_parentfield').value;
	var oncomplete_value = document.getElementById('oncomplete_value').value;
	var onprogress_value = document.getElementById('onprogress_value').value;

	var url = 'module=Settings&action=SettingsAjax&file=LayoutBlockList&sub_mode=addBlock&fld_module='+modulename+'&parenttab=Settings&ajax=true&mode='+mode+'&blocklabel='+
			encodeURIComponent(val)+'&after_blockid='+blockid+'&block_type='+blockType+'&relmodule='+relmodule+'&relfieldname='+relfieldname+'&update_parentfield='+update_parentfield+'&oncomplete_value='+oncomplete_value+'&onprogress_value='+onprogress_value;

	$('vtbusy_info').style.display = "block";
			new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: url,
			onComplete: function(response) {
				$('vtbusy_info').style.display = "none";
				var str = response.responseText;
				if(str == 'ERROR'){
					alert(alert_arr.LABEL_ALREADY_EXISTS);
					return false;
				}else if(str == 'LENGTH_ERROR'){
					alert(alert_arr.LENGTH_OUT_OF_RANGE);
					return false;
				}else{
					$("cfList").update(str);
				}
				gselected_fieldtype = '';
				/* Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla  
				Recargar pagina al guardar */
				window.location.reload();
			}
		}
	);


}

function getCondicionalFieldUrl(fieldid,module,sub_mode){
	var condicional_field = $('condicional_field_'+fieldid).value;
	var condicional_condition = $('condicional_condition_'+fieldid).value;
	var condicional_field2 = $('condicional_field2_'+fieldid).value;
	var condicional_value = $('condicional_value_'+fieldid).value;
	var condicional_color = $('condicional_color_'+fieldid).value;
	var condicional_style = $('condicional_style_'+fieldid).value;
	if(condicional_field==''){
		return null;
	}
	if(condicional_field!='' && condicional_condition==''){
		alert('Debe seleccionar una condicion!');
		return false;
	}
	var url='&condicional_field='+condicional_field+'&condicional_condition='+encodeURIComponent(condicional_condition)+'&condicional_field2='+condicional_field2+'&condicional_value='+encodeURIComponent(condicional_value)+'&condicional_color='+condicional_color+'&condicional_style='+condicional_style;
	return url;
}

// Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
// Guarda las opciones seleccionadas por el usuario 
function saveFieldInfo(module){
	urlstring = '';
	var mandatory_check = document.getElementById('mandatory_check').checked;	
	var presence_check = document.getElementById('presence_check').checked;
	var quickcreate_check = document.getElementById('quickcreate_check').checked;
	var massedit_check = document.getElementById('massedit_check').checked;
	var defaultvalue_check = document.getElementById('defaultvalue_check').checked;
	var typeofdata = document.getElementById('typedata').value;
	var fieldid = document.getElementById('fieldid').value;

	var defaultvalue = document.getElementById('defaultvalue').value;
		
	if(defaultvalue_check == true) {		
		var typeinfo = typeofdata.split('~');
		var inputtype = typeinfo[0];
		if(inputtype == 'C') {
			defaultvalue = (defaultvalue_check == true)?'1':'0';
		}
		if(validateInputData(defaultvalue, alert_arr['LBL_DEFAULT_VALUE_FOR_THIS_FIELD'], typeofdata) == false) {
			document.getElementById('defaultvalue').focus();
			return false;
		}
	} else {
		defaultvalue = '';
	}
	
	urlstring = '&ismandatory=' + mandatory_check + '&isPresent=' + presence_check + '&quickcreate=' + quickcreate_check + '&massedit=' + massedit_check + '&defaultvalue=' + encodeURIComponent(defaultvalue);

	new Ajax.Request(
		'index.php',
		{queue : {position: 'end',scope:'command'},
			method:'post',
			postBody:'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=updateFieldProperties&parenttab=gestion_module&fieldid='+fieldid+'&fld_module='+module+'&ajax=true'+urlstring,
			onComplete: function(response) {
				$("cfList").update(response.responseText);
				$('vtbusy_info').style.display = "none";
				//fnvshNrm('editfield_+"fieldid"');
				
				alert('Se realizó el cambio solicitado');

				location.reload();
			}
		}
	);
	
}


function enableDisableCheckBox(obj, elementName) {

	var ele = $(elementName);
	if (obj == null || ele == null) return;
	if (obj.checked == true) {
		ele.checked = true;
		ele.disabled = true;
	} else {
		ele.disabled = false;
	}
}

function showHideTextBox(obj, elementName) {
	var ele = $(elementName);
	if (obj == null || ele == null) return;
	if (obj.checked == true) {
		ele.disabled = false;
	} else {
		ele.disabled = true;
	}
}


function getCreateCustomFieldForm(modulename,blockid,mode)
{

   var check = validate(blockid);   
   if(check == false){
   	return false;

   }else{


	   var type = document.getElementById("fieldType_"+blockid).value;
	   var label = document.getElementById("fldLabel_"+blockid).value;
	   var name = document.getElementById("fldName_"+blockid).value;
	   var fldLength = document.getElementById("fldLength_"+blockid).value;
	   var fldDecimal = document.getElementById("fldDecimal_"+blockid).value;
	   var fldPickList = encodeURIComponent(document.getElementById("fldPickList_"+blockid).value);
	   var fldRelatedModule = document.getElementById("fldRelatedModule_"+blockid).value;
	   var fldProgressMin = document.getElementById("fldProgressMin_"+blockid).value;
	   var fldProgressMax = document.getElementById("fldProgressMax_"+blockid).value;
	   var fldProgressIni = document.getElementById("fldProgressIni_"+blockid).value;
	   var fldProgressOrd = document.getElementById("fldProgressOrd_"+blockid).value;
	   VtigerJS_DialogBox.block();
		
		if (document.getElementById("flduniquevalue_"+blockid).checked)
			var uniquevalue = 1;
		
		new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
				method: 'post',
				postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=addCustomField&fld_module='+modulename+'&ajax=true&blockid='+blockid+'&fieldType='+type+'&fldName='+name+'&fldLabel='+label+'&fldLength='+fldLength+'&fldDecimal='+fldDecimal+'&fldPickList='+fldPickList+'&fldRelatedModule='+fldRelatedModule+'&fldProgressMin='+fldProgressMin+'&fldProgressMax='+fldProgressMax+'&fldProgressIni='+fldProgressIni+'&fldProgressOrd='+fldProgressOrd,
				onComplete: function(response) {
					VtigerJS_DialogBox.unblock();

					var str = response.responseText;
					if(str == 'ERROR'){
						alert(alert_arr.LABEL_ALREADY_EXISTS);
						return false;
					}
					if(str == 'MAX_ERROR'){
						alert(alert_arr.MAX_LABEL_ERROR);
						return false;
					}
					if(str == 'INI_ERROR'){
						alert(alert_arr.INI_LABEL_ERROR);
						return false;
					}else{
						if (uniquevalue){
							alert("entra");
							var validationtype = 'U';
							insertValidationField(modulename, name, validationtype);
						}
						
						$("cfList").update(str);
						
						gselected_fieldtype = '';
									
						alert(alert_arr.FIELD_CREATED_SUCCESS);
						//jQuery('.md-overlay').css({opacity: 0.0, visibility: 'hidden'});
						location.reload();

					}


			}

		});

   }


}

function makeFieldSelected(oField,fieldid,blockid)
{
	if(gselected_fieldtype != '')
	{
		$(gselected_fieldtype).className = 'customMnu';
	}
	oField.className = 'customMnuSelected';
	gselected_fieldtype = oField.id;
	selFieldType(fieldid,'','',blockid);
	document.getElementById('selectedfieldtype_'+blockid).value = fieldid;
}

function show_move_hiddenfields(modulename,tabid,blockid,sub_mode){

	if(sub_mode == 'showhiddenfields'){
	var selectedfields = document.getElementById('hiddenfield_assignid_'+blockid);
	var selectedids_str = '';
	for(var i=0; i<selectedfields.length; i++) {
		if (selectedfields[i].selected == true) {
			selectedids_str = selectedids_str + selectedfields[i].value + ":";
		}
	}
	}else{
		var selectedfields = document.getElementById('movefield_assignid_'+blockid);
		var selectedids_str = '';
		for(var i=0; i<selectedfields.length; i++) {
			if (selectedfields[i].selected == true) {
				selectedids_str = selectedids_str + selectedfields[i].value + ":";
			}
		}
	}
	$('vtbusy_info').style.display = "block";
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode='+sub_mode+'&fld_module='+modulename+'&parenttab=gestion_module&ajax=true&tabid='+tabid+'&blockid='+blockid+'&selected='+selectedids_str,
			onComplete: function(response) {
				$("cfList").update(response.responseText);
				$('vtbusy_info').style.display = "none";
				}
			}
		);
}

function changeRelatedListorder(what_to_do,tabid,sequence,id,module)
{
	$('vtbusy_info').style.display = "block";
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=changeRelatedInfoOrder&sequence='+sequence+'&fld_module='+module+'&parenttab=gestion_module&what_to_do='+what_to_do+'&tabid='+tabid+'&id='+id+'&ajax=true',
			onComplete: function(response) {
			$("relatedlistdiv").innerHTML=response.responseText;
			$('vtbusy_info').style.display = "none";
			}
		}

	);
}

function callRelatedList(module){
	// Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
	//$('vtbusy_info').style.display = "block";
	jQuery('#relatedlistdiv').hide();
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=getRelatedInfoOrder&parenttab=gestion_module&formodule='+module+'&ajax=true',
			onComplete: function(response) {
				jQuery("#relatedlistdiv").html(response.responseText); //Cargar cuerpo del modal
				var scriptTags = $('relatedlistdiv').getElementsByTagName("script");
				for(var i = 0; i< scriptTags.length; i++){
					var scriptTag = scriptTags[i];
					var script = document.createElement("script");
					script.type = "text/javascript";
					var head = document.getElementsByTagName("head")[0];
					if (scriptTag.src == '') {
						script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
						head.appendChild(script);
					}
				}

				if(jQuery('#num').length > 0 && jQuery('#num').val() == 2){
					alert('Las listas fueron agregadas satisfactoriamente');
					window.location.reload();
				}else{
					jQuery('#num').val('2');
					jQuery("#relatedlistdiv").show();
				}

				
			}
		}

	);
}

function agregarCampoGrid(module){
	/* Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla */
	//$('vtbusy_info').style.display = "block";
	jQuery('#camposGrid').hide();
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=agregarCampoGrid&parenttab=gestion_module&formodule='+module+'&ajax=true',
			onComplete: function(response) {				
				jQuery("#camposGrid").html(response.responseText); //Cargar cuerpo del modal
				var scriptTags = $("camposGrid").getElementsByTagName("script");
				for(var i = 0; i< scriptTags.length; i++){
					var scriptTag = scriptTags[i];
					var script = document.createElement("script");
					script.type = "text/javascript";
					var head = document.getElementsByTagName("head")[0];
					if (scriptTag.src == '') {
						script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
						head.appendChild(script);
					}
				}
				jQuery("#camposGrid").show();
			}
		}

	);
}

function actualizarPropiedadesAvanzadas(module){
	jQuery("#propiedadesAvanzadas").hide();
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=actualizarPropiedadesAvanzadas&parenttab=gestion_module&formodule='+module+'&ajax=true',
			onComplete: function(response) {
				jQuery("#propiedadesAvanzadas").html(response.responseText); //Cargar cuerpo del modal
				var scriptTags = $("propiedadesAvanzadas").getElementsByTagName("script");
				for(var i = 0; i< scriptTags.length; i++){
					var scriptTag = scriptTags[i];
					var script = document.createElement("script");
					script.type = "text/javascript";
					var head = document.getElementsByTagName("head")[0];
					if (scriptTag.src == '') {
						script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
						head.appendChild(script);
					}
				}
				jQuery("#propiedadesAvanzadas").show();
			}
		}

	);
}

function addFieldMatrix(module){
	$('vtbusy_info').style.display = "block";
	jQuery('#fieldMatrix').hide();
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&sub_mode=addFieldMatrix&parenttab=gestion_module&formodule='+module+'&ajax=true',
			onComplete: function(response) {
				$("fieldMatrix").innerHTML=response.responseText;
				var scriptTags = $('fieldMatrix').getElementsByTagName("script");
				for(var i = 0; i< scriptTags.length; i++){
					var scriptTag = scriptTags[i];
					var script = document.createElement("script");
					script.type = "text/javascript";
					var head = document.getElementsByTagName("head")[0];
					if (scriptTag.src == '') {
						script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
						head.appendChild(script);
					}
				}
				fnvshNrm('fieldMatrix');
				$('vtbusy_info').style.display = "none";
			}
		}

	);
}

function showProperties(field,man,pres,quickc,massed){
	var str='<table class="small" cellpadding="2" cellspacing="0" border="0"><tr><th>'+field+'</th></tr>';
	if (man == 0 || man == 2)
 		str = str+'<tr><td>'+alert_arr.FIELD_IS_MANDATORY+'</td></tr>';
	if (pres == 0 || pres == 2)
 		str = str+'<tr><td>'+alert_arr.FIELD_IS_ACTIVE+'</td></tr>';
	if (quickc == 0 || quickc == 2)
		str = str+'<tr><td>'+alert_arr.FIELD_IN_QCREATE+'</td></tr>';
	if(massed == 0 || massed == 1)
		str = str+'<tr><td>'+alert_arr.FIELD_IS_MASSEDITABLE+'</td></tr>';
	str = str + '</table>';
	return str;
}

var gselected_fieldtype = '';
{/literal}
</script>

<div id = "layoutblock">
<br>
<div id="fieldMatrix" style="display:none; position: absolute; width: 500px; left: 100px; top: 300px;"></div>
<br>
<br>

{assign var=entries value=$CFENTRIES}
{if $CFENTRIES.0.tabpresence eq '0' }
<div id="vtlib_modulemanager" style="display:block;position:absolute;width:100%;"></div>
	<div id="email-box" class="clearfix">
		{*
		<div class="col-left-nano-content" style="float:left;width:30%;">
		 {include file='SetMenu.tpl'}
		</div>
		*}
		<div class="col-lg-12" style="">
			<!--table class="settingsSelUITopLine" border="0" cellpadding="5" cellspacing="0" width="100%"-->
			<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width:30px;padding:0px;">
					<i class="fa fa-tasks yellow-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li><a href="index.php?module=gestion_module&action=ModuleManager&parenttab=gestion_module">{$MOD.LBL_MY_MODULES}</a></li>
						<li class="active">{$MOD.LBL_LAYOUT_EDITOR}</li>
					</ol>
				</td>
				<!--  Modificado por Johana Romero Pedido [TT11132] Fallas Editor Disposición - Platzilla. Quitar VOLVER A
				<td class="heading2" valign="bottom">
				Volver a <a href="index.php?module={$RETURN_MODULE}&action=ListView"><h1>{$RETURN_MODULE|getTranslatedString:$RETURN_MODULE}</h1>
				</td>-->
			</tr>

			<tr>
				<td class="small" colspan="3" valign="top">{$MOD.LBL_LAYOUT_EDITOR_DESCRIPTION}</td>
			</tr>
			</table>

			<!-- Standard gray button with gradient -->
			<!-- Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
			A cada boton se le agrego data-modal con su respectivo ID, la clase md-trigger y en onclick solo se dejo la funcion que imprime en el modal
			-->
			<button type="button" class="md-trigger btn btn-default" data-modal="propiedadesAvanzadas" onclick="actualizarPropiedadesAvanzadas('{$CFENTRIES.0.module}');">{$MOD.LBL_ACTUALIZAR_PROPIEDADES_AVANZADAS}</button>
			<button type="button" class="md-trigger btn btn-primary" data-modal="camposGrid" onclick="agregarCampoGrid('{$CFENTRIES.0.module}');">{$MOD.LBL_AGREGAR_CAMPO_MATRIZ}</button>
			<button type="button" class="md-trigger btn btn-warning" data-modal="relatedlistdiv" onclick="callRelatedList('{$CFENTRIES.0.module}');">{$MOD.ARRANGE_RELATEDLIST}</button>
			<button type="button" class="md-trigger btn btn-info" data-modal="camposGrid" onclick="agregarCampoGrid('{$CFENTRIES.0.module}');">{$MOD.LBL_AGREGAR_CAMPO_GRID}</button>
			<a data-toggle="modal" href="#myModal" class="btn btn-success">{$MOD.LBL_AVAILABLE_REPORT}</a>
			<!--
					ESTE BOTON ESTAN REPETIDOS			
			<button type="button" class="md-trigger btn btn-warning" onclick="callRelatedList('{$CFENTRIES.0.module}');">{$MOD.ARRANGE_RELATEDLIST}</button>-->
			<button type="button" class="md-trigger btn btn-danger" data-modal="addblock">
			<!-- Fin de modificacion Johana R -->

			{$MOD.ADD_BLOCK}</button>
			<div class="btn-group">
				<button type="button" class="btn btn-success">{$MOD.LBL_EDIT_LABELS}</button>
				<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
					{foreach name=lang key=code item=name from=$LISTLANGUAGUES}
					<li><a href="index.php?module=gestion_module&action=editLabels&fld_module={$CFENTRIES.0.module}&lang={$code}">{$name}</a></li>
					{/foreach}
				</ul>
			</div>

			<div id="cfList">
                {include file="gestion_module/LayoutBlockEntries.tpl"}
            </div>

			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<td class="small" align="right" nowrap="nowrap"><a href="#top">{$MOD.LBL_SCROLL}</a></td>
				</tr>
			</table>
		</div>
   </div>

		<!-- End of Display for field -->
{else}

	<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>
	<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>
	<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
		<tbody><tr>
		<td rowspan='2' width='11%'><img src="{'denied.gif'|@vtiger_imageurl:$THEME}" ></td>
		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>{$APP.LBL_PERMISSION}</span></td>
		</tr>
		<tr>
		<td class='small' align='right' nowrap='nowrap'>
		<a href='javascript:window.history.back();'>{$MOD.LBL_GO_BACK}</a><br>								   						     </td>
		</tr>
		</tbody></table>
		</div>
		</td></tr></table>
{/if}

</div>
<div id="vtbusy_info" style="display:none"></div>
<!-- Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
Modal Botones -->
<div class="md-modal md-effect-1" id="propiedadesAvanzadas" style="min-width: 800px;max-width: 1000px;"></div>
<div class="md-modal md-effect-1" id="camposGrid" style="min-width: 800px;max-width: 1000px;"></div>
<div class="md-modal md-effect-1" id="relatedlistdiv" style="min-width: 800px;max-width: 1000px;"></div>
<div class="md-overlay"></div><!-- the overlay element -->

<div id="myModal" class="modal fade" role="dialog" aria-hidden="true" >
  <div class="modal-dialog">   
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{$MOD.LBL_AVAILABLE_REPORT}</h4>
      </div>
      <div class="modal-body">
        <p>
        	<div class="form-group">
				<label for="exampleInputReport">{$MOD.LBL_IS_REPORT}</label>
				<input type="checkbox" id="check_reportAvailable" name="reportAvailable" {if $REPORTAVAILABLE eq 1} checked="checked" {/if}>
			</div>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="availableReport('{$CFENTRIES.0.tabid}')">Guardar</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>