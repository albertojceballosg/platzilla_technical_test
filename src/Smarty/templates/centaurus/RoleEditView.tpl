
<form name="newRoleForm" action="index.php" method="post" onSubmit="if(validate()) {ldelim} VtigerJS_DialogBox.block();{rdelim} else {ldelim} return false;{rdelim} ">
	<input type="hidden" name="module" value="Settings">
	<input type="hidden" name="action" value="SaveRole">
	<input type="hidden" name="parenttab" value="Settings">
	<input type="hidden" name="returnaction" value="{$RETURN_ACTION}">
	<input type="hidden" name="roleid" value="{$ROLEID}">
	<input type="hidden" name="mode" value="{$MODE}">
	<input type="hidden" name="parent" value="{$PARENT}">



<div style="opacity: 1;" class="row">



	<div class="col-lg-12">

		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a></li>
					<li class="active"><span><a href="index.php?module=Settings&action=listroles&parenttab=Settings">{$CMOD.LBL_ROLES}</span></a></li>
				</ol>
			</div>
		</div>


		<!-- botones superiores -->
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header clearfix">
					<div class="main-box-body clearfix">
						<div class="row">
							<div class="col-md-6">
								<b>
	                            	{if $MODE eq 'edit'}
										{$MOD.LBL_EDIT} {$ROLE_NAME}
									{else}
										{$CMOD.LBL_CREATE_NEW_ROLE}
									{/if}
								</b>
							</div>

							<div class="col-md-6 text-right">

								<input type="button" class="btn btn-primary" name="add" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " onClick="return validate()">

								<input type="button" class="btn btn-default" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" onClick="window.history.back()">


							</div>
						</div>

					</div>
				</div>
			</div>
		</div>



		<div class="row">
			<div class="col-lg-12">
				<div class="main-box clearfix" style="">
					<header class="main-box-header clearfix">
						<h2>
							{if $MODE eq 'edit'}
								{$MOD.LBL_EDIT} {$ROLE_NAME}
							{else}
								{$CMOD.LBL_CREATE_NEW_ROLE}
							{/if}
						</h2>
					</header>

					<div class="main-box-body clearfix">

						<div class="form-group">
							<label for="rolename">{$CMOD.LBL_ROLE_NAME}</label>
							<input id="rolename" name="roleName" type="text" value="{$ROLENAME}" class="form-control"placeholder="{$CMOD.LBL_ROLE_NAME}"  value="{$ROLENAME}">
						</div>
						<div class="form-group">
							<label for="rolename">{$CMOD.LBL_REPORTS_TO}</label>
							<span class="label label-success">{$PARENTNAME}</span>
						</div>
						<div class="row form-group">
								<div class="col-md-6">
									<label>{$CMOD.LBL_PROFILES_AVLBL}</label>

									<input type="hidden" name="selectedColumnsString"/>
									<input name="Button" value="&nbsp;&rsaquo;&rsaquo;&nbsp;" type="button" class="btn btn-primary" style="width:100%" onClick="addColumn()">

									<select multiple="" class="form-control" id="availList" name="availList" size="6">
										{foreach item=element from=$PROFILELISTS}
											<option value="{$element.0}">{if ($element.0 == 1)}Administrador de todas las aplicaciones{else}{$element.1}{/if}</option>
										{/foreach}
									</select>
								</div>

								<div class="col-md-6">
									<label>{$CMOD.LBL_ASSIGN_PROFILES}</label>

									<input type="button" name="Button1" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" class="btn btn-danger" onClick="delColumn()" style="width:100%">

									<select multiple="" class="form-control" id="selectedColumns" name="selectedColumns" size="6">
										{foreach item=element from=$SELPROFILELISTS}
											<option value="{$element.0}">{if ($element.0 == 1)}Administrador de todas las aplicaciones{else}{$element.1}{/if}</option>
										{/foreach}
									</select>
								</div>


						</div>







					</div>
				</div>
			</div>
		</div>






	</div>
</div>



</form>












{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script language="javascript">
function dup_validation()
{ldelim}
	var rolename = $('rolename').value;
	var mode = getObj('mode').value;
	var roleid = getObj('roleid').value;
	if(mode == 'edit')
		var urlstring ="&mode="+mode+"&roleName="+rolename+"&roleid="+roleid;
	else
		var urlstring ="&roleName="+rolename;
	//var status = CharValidation(rolename,'namespace');
	//if(status)
	//{ldelim}
	new Ajax.Request(
                'index.php',
                {ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                                method: 'post',
                                postBody: 'module=Settings&action=SettingsAjax&file=SaveRole&ajax=true&dup_check=true'+urlstring,
                                onComplete: function(response) {ldelim}
					if(response.responseText.indexOf('SUCCESS') > -1)
						document.newRoleForm.submit();
					else
						alert(response.responseText);
                                {rdelim}
                        {rdelim}
                );
	//{rdelim}
	//else
	//	alert(alert_arr.NO_SPECIAL+alert_arr.IN_ROLENAME)

{rdelim}
function validate()
{ldelim}
	formSelectColumnString();
	if( !emptyCheck("roleName", "Role Name", "text" ) )
		return false;

	if(document.newRoleForm.selectedColumnsString.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length==0)
	{ldelim}

		alert('{$APP.ROLE_SHOULDHAVE_INFO}');
		return false;
	{rdelim}
	dup_validation();return false
{rdelim}
</script>

<script language="JavaScript" type="text/JavaScript">
        var moveupLinkObj,moveupDisabledObj,movedownLinkObj,movedownDisabledObj;
        function setObjects()
        {ldelim}
            availListObj=getObj("availList")
            selectedColumnsObj=getObj("selectedColumns")

        {rdelim}

        function addColumn()
        {ldelim}
            for (i=0;i<selectedColumnsObj.length;i++)
            {ldelim}
                selectedColumnsObj.options[i].selected=false
            {rdelim}

            for (i=0;i<availListObj.length;i++)
            {ldelim}
                if (availListObj.options[i].selected==true)
                {ldelim}
                	var rowFound=false;
                	var existingObj=null;
                    for (j=0;j<selectedColumnsObj.length;j++)
                    {ldelim}
                        if (selectedColumnsObj.options[j].value==availListObj.options[i].value)
                        {ldelim}
                            rowFound=true
                            existingObj=selectedColumnsObj.options[j]
                            break
                        {rdelim}
                    {rdelim}

                    if (rowFound!=true)
                    {ldelim}
                        var newColObj=document.createElement("OPTION")
                        newColObj.value=availListObj.options[i].value
                        if (browser_ie) newColObj.innerText=availListObj.options[i].innerText
                        else if (browser_nn4 || browser_nn6) newColObj.text=availListObj.options[i].text
                        selectedColumnsObj.appendChild(newColObj)
                        availListObj.options[i].selected=false
                        newColObj.selected=true
                        rowFound=false
                    {rdelim}
                    else
                    {ldelim}
                        if(existingObj != null) existingObj.selected=true
                    {rdelim}
                {rdelim}
            {rdelim}
        {rdelim}

        function delColumn()
        {ldelim}
            for (i=selectedColumnsObj.options.length;i>0;i--)
            {ldelim}
                if (selectedColumnsObj.options.selectedIndex>=0)
                selectedColumnsObj.remove(selectedColumnsObj.options.selectedIndex)
            {rdelim}
        {rdelim}

        function formSelectColumnString()
        {ldelim}
            var selectedColStr = "";
            for (i=0;i<selectedColumnsObj.options.length;i++)
            {ldelim}
                selectedColStr += selectedColumnsObj.options[i].value + ";";
            {rdelim}
            document.newRoleForm.selectedColumnsString.value = selectedColStr;
        {rdelim}
	setObjects();
</script>
