<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css">
	



<form action="index.php" method="post" name="profileform" id="form" onSubmit="if(rolevalidate()) {ldelim} VtigerJS_DialogBox.block();return true;{rdelim} else {ldelim} return false; {rdelim} ">
    <input type="hidden" name="module" value="Settings">
    <input type="hidden" name="mode" value="{$MODE}">
    <input type="hidden" name="action" value="profilePrivileges">
    <input type="hidden" name="parenttab" value="Settings">
    <input type="hidden" name="parent_profile" value="{$PARENT_PROFILE}">
    <input type="hidden" name="radio_button" value="{$RADIO_BUTTON}">


<div class="col-lg-12">
							
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
				<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></a></li>
				<li class="active"><span><a href="index.php?module=Settings&action=ListProfiles&parenttab=Settings">{$CMOD.LBL_PROFILE_PRIVILEGES}</a></span></li>
			</ol>
			
			<h1>{$CMOD.LBL_PROFILE_PRIVILEGES}</h1>
		</div>
	</div>
	
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix" style="">
				<header class="main-box-header clearfix">
					<h2>{$MOD.LBL_PROFILE_DESCRIPTION}</h2>
				</header>
				
				<div class="main-box-body clearfix">
				
					<div id="myWizard" class="wizard">
						<div class="wizard-inner">
							<ul style="margin-left: 0" class="steps">
								<li data-target="#step1" class="active"><span class="badge badge-primary">1</span>Paso 1<span class="chevron"></span></li>
								<li class="" data-target="#step2"><span class="badge">2</span>Paso 2<span class="chevron"></span></li>
								<!--li class="" data-target="#step3"><span class="badge">3</span>Step 3<span class="chevron"></span></li>
								<li class="" data-target="#step4"><span class="badge">4</span>Step 4<span class="chevron"></span></li-->
							</ul>
							<div class="actions">
								<button type="button" class="btn btn-success btn-mini btn-next" data-last="Finish"  onClick="return rolevalidate();">{$APP.LNK_LIST_NEXT}<i class="icon-arrow-right"></i></button>
								<button type="button" class="btn btn-default btn-mini" data-last="Finish" onClick="window.history.back();">{$APP.LBL_CANCEL_BUTTON_LABEL}<i class="icon-arrow-right"></i></button>

							</div>
						</div>
						<div class="step-content">
							<div class="step-pane active" id="step1">
								<br>

								<div class="row">
									<div class="col-md-12" style="padding-bottom: 5px;">
										<div class="col-md-4 text-right">
											{$CMOD.LBL_NEW_PROFILE_NAME}
										</div>
										<div class="col-md-8">
											<input type="text" name="profile_name" id="pobox" value="{$PROFILENAME}" class="form-control" />
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12" style="padding-bottom: 5px;">
										<div class="col-md-4 text-right">
											{$CMOD.LBL_DESCRIPTION}
										</div>
										<div class="col-md-8">
											<textarea name="profile_description" class="form-control">{$PROFILEDESC}</textarea>
										</div>
									</div>
								</div>

								<div class="row">

									<div class="col-lg-12">
										<div class="col-md-6">
											{if  $RADIO_BUTTON neq 'newprofile'}
												<input name="radiobutton" checked type="radio" value="baseprofile" />
											{else}
												<input name="radiobutton" type="radio"  value="baseprofile" />
											{/if}
											{$CMOD.LBL_BASE_PROFILE_MESG}

											<br>
											{$CMOD.LBL_BASE_PROFILE}
											<select name="parentprofile" class="importBox">
											{foreach item=combo from=$PROFILE_LISTS}
											{if $PARENT_PROFILE eq $combo.1}
												<option  selected value="{$combo.1}">{$combo.0}</option>
											{else}
												<option value="{$combo.1}">{$combo.0}</option>
											{/if}
											{/foreach}
										</select>
										</div>
										<div class="col-md-6">

											{if  $RADIO_BUTTON eq 'newprofile'}
												<input name="radiobutton" checked type="radio" value="newprofile" />
											{else}
												<input name="radiobutton" type="radio" value="newprofile" />
											{/if}
											{$CMOD.LBL_BASE_PROFILE_MESG_ADV}

										</div>
									</div>
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


<script>
var profile_err_msg='{$MOD.LBL_ENTER_PROFILE}';
function rolevalidate()
{ldelim}
    var profilename = document.getElementById('pobox').value;
    profilename = trim(profilename);
    if(profilename != '')
	dup_validation(profilename);
    else
    {ldelim}
        alert(profile_err_msg);
        document.getElementById('pobox').focus();
	return false
    {rdelim}
    return false
{rdelim}


function dup_validation(profilename)
{ldelim}
	//var status = CharValidation(profilename,'namespace');
	//if(status)
	//{ldelim}
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module=Users&action=UsersAjax&file=CreateProfile&ajax=true&dup_check=true&profile_name='+profilename,
			onComplete: function(response) {ldelim}
					if(response.responseText.indexOf('SUCCESS') > -1)
						document.profileform.submit();
					else
						alert(response.responseText);
				{rdelim}
		{rdelim}
	);
	//{rdelim}
	//else
	//	alert(alert_arr.NO_SPECIAL+alert_arr.IN_PROFILENAME)
{rdelim}
</script>
