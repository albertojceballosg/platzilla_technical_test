{*<!--/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/ -->*}	
		<form action="index.php" method="post" name="form" onsubmit="VtigerJS_DialogBox.block();">
		<input type="hidden" name="fld_module" value="{$MODULE}">
		<input type="hidden" name="module" value="gestion_module">
		<input type="hidden" name="parenttab" value="gestion_module">
		{assign var=entries value=$CFENTRIES}
		<br>
		<br>
		<div class="md-content" style="font-size:90%">
			<div class="modal-header">
				<h4 class="modal-title" id="labelDiv">{$MOD.LBL_RELATED_LIST}</h4>
			</div>
			<div class="modal-body" style="max-height:320px;overflow: auto;">
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
						{foreach item=related from=$RELATEDLIST name=relinfo}
						<tr>
							<td>{$related.label}
							</td>
							{if $smarty.foreach.relinfo.first}
							<td align="right" >
								<img src="{'blank.gif'|@vtiger_imageurl:$THEME}" style="width:16px;height:16px;" border="0" />&nbsp;&nbsp;
							</td>
							<td align="right" valign="middle">
								<a href="#" style="cursor:pointer;" onclick="changeRelatedListorder('move_down','{$related.tabid}','{$related.sequence}','{$related.id}','{$MODULE}'); " alt="{$MOD.DOWN}" title="{$MOD.DOWN}">
									<i class="fa fa-sort-desc"></i>
								</a>
							</td>

						{elseif  $smarty.foreach.relinfo.last}
						<td align="right" valign="middle">
							<a href="#" style="cursor:pointer;" onclick="changeRelatedListorder('move_up','{$related.tabid}','{$related.sequence}','{$related.id}','{$MODULE}'); " alt="{$MOD.UP}" title="{$MOD.UP}">
							<i class="fa fa-sort-asc"></i>
							</a>
						</td>
						<td align="right" >
							<img src="{'blank.gif'|@vtiger_imageurl:$THEME}" style="width:16px;height:16px;" border="0" />&nbsp;&nbsp;
						</td>
						{else }
						<td align="right" valign="middle">
							<a href="#" style="cursor:pointer;" onclick="changeRelatedListorder('move_up','{$related.tabid}','{$related.sequence}','{$related.id}','{$MODULE}'); " alt="{$MOD.UP}" title="{$MOD.UP}">
							<i class="fa fa-sort-asc"></i>
							</a>
						</td>
						<td align="right" valign="middle">
							<a href="#" style="cursor:pointer;" onclick="changeRelatedListorder('move_down','{$related.tabid}','{$related.sequence}','{$related.id}','{$MODULE}') " alt="{$MOD.DOWN}" title="{$MOD.DOWN}">
							<i class="fa fa-sort-desc"></i>
							</a>
						</td>
						{/if}
					</tr>
					{/foreach}		
				</table>		
			</div>
		<div class="modal-footer">
		</div>	
	</div>
</form>	

<script type="text/javascript">
var lstNumRows = new Array();
 

function addRowOtherOperationsList(tableid) {ldelim}
	ctrlTable = document.getElementById(tableid);
	if (ctrlTable) {ldelim}
		if (lstNumRows[tableid]) 
			lstNumRows[tableid]++;
		else
			lstNumRows[tableid] = (ctrlTable.rows.length);
		
		var row=ctrlTable.insertRow(ctrlTable.rows.length);
		var x1=row.insertCell(0);
		var x2=row.insertCell(1);
		var x3=row.insertCell(2);
		var x4=row.insertCell(3);
		row.id = 'row_'+tableid+'_'+lstNumRows[tableid];
		row.className = 'lvtColData';
	
		str = document.getElementById('td_'+tableid+'labelModulos1').innerHTML;
		x1.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'listaModulos1').innerHTML;
		x2.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'listaAcciones1').innerHTML;
		x3.innerHTML=str.replace(/1/g,lstNumRows[tableid]);
		str = document.getElementById('td_'+tableid+'actionCampo1').innerHTML;
		x4.innerHTML=str.replace(',1',','+lstNumRows[tableid]);
		x1.id= 'td_'+tableid+'labelModulos'+lstNumRows[tableid];
		x2.id= 'td_'+tableid+'listaModulos'+lstNumRows[tableid];
		x3.id= 'td_'+tableid+'listaAcciones'+lstNumRows[tableid];
		x4.id= 'td_'+tableid+'actionCampo'+lstNumRows[tableid];
		x1.className = 'dvtCellInfo';
		x2.className = 'dvtCellInfo';
		x3.className = 'dvtCellInfo';
		x4.className = 'dvtCellInfo';
	{rdelim}
	
{rdelim}

function showHideElement(id) {ldelim}
	ctrl = document.getElementById(id);
	
	if (ctrl) {ldelim}
		if (ctrl.style.display == 'none')
			ctrl.style.display = '';
		else
			ctrl.style.display = 'none';
	{rdelim}
{rdelim}

function deleteOtherOperationList(tableid,iNumRow) {ldelim}
	ctrlTable = document.getElementById(tableid);
	
	if (ctrlTable) {ldelim}
		var x = document.getElementById ('row_'+tableid+'_'+iNumRow);
		var tablepadre = x.parentNode;
		tablepadre.removeChild(x);
	{rdelim}
{rdelim}

function irPaso(form,action)
{ldelim}
	form.action.value = action;
		new Ajax.Request('index.php', {ldelim}
			method: form.method,
			postBody: Form.serialize(form),
			onComplete: function(response) {ldelim}
								callRelatedList("{$_FLD_MODULE}");
						{rdelim}
		{rdelim});
{rdelim}

function loadFieldsModule(checkCtrl,value,picklist,picklist2) {ldelim}
	if (checkCtrl.checked) {ldelim}
		ctrl = document.getElementById(value);
		
		if (ctrl) {ldelim}
			jQuery.post('index.php?module=gestion_module&action=gestion_moduleAjax&file=updateFields',{ldelim} 'relmodule': '{$_FLD_MODULE}', 'fieldorcolumn':'field'  {rdelim}, function(data) {ldelim}
				jQuery('#'+picklist).empty();
				for(i=0;i<data.fields.length;i++) {ldelim}
					jQuery('#'+picklist).append(new Option(data.fields[i][1], data.fields[i][0], true, true));

				{rdelim}
			{rdelim}, "json");
		{rdelim}
		if (ctrl) {ldelim}
			jQuery.post('index.php?module=gestion_module&action=gestion_moduleAjax&file=updateFields',{ldelim} 'relmodule': ctrl.value, 'fieldorcolumn':'column'  {rdelim}, function(data) {ldelim}
				jQuery('#'+picklist2).empty();
				for(i=0;i<data.fields.length;i++) {ldelim}
					jQuery('#'+picklist2).append(new Option(data.fields[i][1], data.fields[i][0], true, true));

				{rdelim}
			{rdelim}, "json");
		{rdelim}
	{rdelim}
{rdelim}

function onChangeModulos(id,value)
{ldelim}
	ctrl = document.getElementById('listaAccionRelacionAutomatica'+id);
	
	
	if (ctrl) {ldelim}
		if (value != '-') {ldelim}
			ctrl.disabled = false;
			if (ctrl.checked) {ldelim}
				showHideElement('relatedFields'+id);
				
			{rdelim}
		{rdelim}
		else {ldelim}
			ctrl.disabled = true;
			showHideElement('relatedFields'+id);
		{rdelim}
	{rdelim}
{rdelim}

</script>

<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso4">
<input type="hidden" name="module" value="gestion_module" />
<input type="hidden" name="fld_module" value="{$_FLD_MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<input type="hidden" name="rootaction" id="rootaction" value="relatedlist" />
<div class="md-content" style="font-size:90%">
	<div class="modal-header">
		<h4 class="modal-title" id="labelDiv">{$MOD.LBL_LISTAS_RELACIONADAS}</h4>
	</div>
	<div class="modal-body" style="max-height:100px;overflow: auto;">
		{$_LISTADOS_RELACIONADOS}
	</div>
	<div class="modal-footer">
		<button name="addRow" onclick="addRowOtherOperationsList('table1')" class="btn btn-warning">{$MOD.LBL_ADD_LISTA}</button>
		<button class="btn btn-primary" onclick="irPaso(document.wizardPaso4,'ListasRelacionadas');">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
		<!-- Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
		jQuery('#relatedlistdiv') : cerrar el modal correspondiente a este ID-->
		<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#relatedlistdiv').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
	</div>
</div>
</form>