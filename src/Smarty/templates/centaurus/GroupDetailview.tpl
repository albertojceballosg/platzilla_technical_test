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

<form action="index.php" method="post" name="new" id="form" onsubmit="VtigerJS_DialogBox.block();">
				<input type="hidden" name="module" value="Settings">
				<input type="hidden" name="action" value="createnewgroup">
				<input type="hidden" name="groupId" value="{$GROUPID}">
				<input type="hidden" name="mode" value="edit">
				<input type="hidden" name="parenttab" value="Settings">
<div class="col-lg-12">				
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
			<li> <a href="index.php?module=Settings&action=listgroups&parenttab=Settings">{$CMOD.LBL_GROUPS}</a></li>
			 <li>{$CMOD.LBL_VIEWING} </li>
			
		</ol>
			
		</div>
	</div>
</div>

<div class="col-lg-12">
							
	<div class="row">
		<div class="main-box no-header clearfix" style="">
				
			
			<div class="col-lg-6 pull-left">



				<h2>{$CMOD.LBL_VIEWING} {$CMOD.LBL_PROPERTIES} &quot;{$GROUPINFO.0.groupname} </h2>
				
			
			
			</div>
		    
		    <div class="col-lg-3 pull-right">
				<input value="   {$APP.LBL_EDIT_BUTTON_LABEL}   " title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="btn btn-primary btn-sm pull-right" type="submit" name="Edit" style="margin-bottom: 20px;margin-left:10px;">
				
			
			</div>
		    
		    
			
		
	
	
		</div>
	
	</div>

</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
									<table width="100%"  border="0" cellspacing="0" cellpadding="5" class="table-responsive" style="margin-top:20px;">
                      <tr>
                        <th width="15%">{$CMOD.LBL_GROUP_NAME}</th>
                        <td width="85%" >{$GROUPINFO.0.groupname}</td>
                      </tr>
                      <tr >
                        <th>{$CMOD.LBL_DESCRIPTION}</th>
                        <td>{$GROUPINFO.0.description}</td>
                      </tr>
                      <tr>
                      <td width="10%">&nbsp;</td>
                        <th>{$CMOD.LBL_MEMBER}</th>
                        <td>
						<table width="70%"  border="0" cellspacing="0" cellpadding="5" class="table">
                          <tr>
                  		{foreach key=type item=details from=$GROUPINFO.1} 
				{if $details.0 neq ''}		
					{if $type == "User"}
                            		<td colspan="2">
						<div align="left"><strong>{$MOD.LBL_USERS}</strong></div>
					</td>
					{/if}	
					{if $type == "Role"}
                            		<td colspan="2">
						<div align="left"><strong>{$MOD.LBL_ROLES}</strong></div>
					</td>
					{/if}	
					{if $type == "Role and Subordinates"}
                            		<td colspan="2">
						<div align="left"><strong>{$type}</strong></div>
					</td>
					{/if}	
					{if $type == "Group"}
                            		<td colspan="2" ">
						<div align="left"><strong>{$CMOD.LBL_GROUPS}</strong></div>
					</td>
					{/if}	
                            </tr>
                          <tr >

                            <td width="16"><div align="center"></div></td>
                            <td>
					{foreach item=element from=$details}
						{if $element.memberaction == "GroupDetailView"}
						<a href="index.php?module=Settings&action={$element.memberaction}&{$element.actionparameter}={$element.memberid}">{$element.membername}</a><br />
						{/if}
						{if $element.memberaction == "RoleDetailView"}	
						<a href="index.php?module=Settings&action={$element.memberaction}&{$element.actionparameter}={$element.memberid}">{$element.membername}</a><br />
						{/if}
						{if $element.memberaction == "DetailView"}	
						<a href="index.php?module=Users&action={$element.memberaction}&{$element.actionparameter}={$element.memberid}">{$element.membername}</a><br />
						{/if}
					{/foreach}
			    </td>  	 
                          </tr>
				{/if}
				{/foreach}
                        </table></td>
                      </tr>
                    </table>
			</div>
		</div>
	</div>
</div>


</form>