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

<form action="index.php" method="post" name="AuditTrail" id="form" onsubmit="VtigerJS_DialogBox.block();">
<input type='hidden' name='module' value='Settings'>
<input type='hidden' name='action' value='AuditTrail'>
<input type='hidden' name='return_action' value='ListView'>
<input type='hidden' name='return_module' value='Settings'>
<input type='hidden' name='parenttab' value='Settings'>


<div class="row">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
		</ol>
		<h1>{$MOD.LBL_AUDIT_TRAIL}</h1>
	</div>
</div>


<div class="col-lg-12">
							
	<div class="row">
		<div class="main-box no-header clearfix" style="">
				
			<div class="col-lg-6 pull-left">
				<h2>{$MOD.LBL_AUDIT_TRAIL_DESC}</h2>
			</div>
		    <div class="col-lg-3 pull-right">		
				<input title="{$MOD.LBL_VIEW_AUDIT_TRAIL}" class="btn btn-primary btn-sm pull-right" onclick="showAuditTrail();" type="button" name="button" value="{$MOD.LBL_VIEW_AUDIT_TRAIL}" style="margin-bottom: 20px;margin-left:10px;">
			</div>
		</div>
	</div>

</div>


<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
				<p>{$MOD.LBL_AUDIT_TRAIL} <span id="audit_info" class="crmButton small cancel" style="display:none;"></span></p>
				
			
				<table border=0 cellspacing=0 cellpadding=0 width=100% class="table">
					
            		<tr>
                        <th width="20%" nowrap >{$MOD.LBL_ENABLE_AUDIT_TRAIL}</th>
                        <td width="80%" >
						{if $AuditStatus eq 'enabled'}
							<input type="checkbox" checked name="enable_audit" onclick="auditenabled(this)"></input>
						{else}
							<input type="checkbox" name="enable_audit" onclick="auditenabled(this)"></input>
						{/if}
						</td>
            		</tr>
            		<tr valign="top">
                        <th nowrap>{$MOD.LBL_USER_AUDIT}</th>
                        <td >
						<select name="user_list" id="user_list" class="form-control">
							{$USERLIST}
						</select>	
					    </td>
           			</tr>
                        
                        
                </table>	
				
			</div>
		</div>
	</div>
</div>

</form>




{literal}
<script>

function auditenabled(ochkbox)
{
	if(ochkbox.checked == true)
	{
	     var status='enabled';
	$('audit_info').innerHTML = 'Audit Trail Enabled';
	     $('audit_info').style.display = 'block';		
		
			
	}
	else
	{
	    var status = 'disabled';	
	     $('audit_info').innerHTML = 'Audit Trail Disabled';
	     $('audit_info').style.display = 'block';		
	
	}
             $("status").style.display="block";
	     new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=Settings&action=SettingsAjax&file=SaveAuditTrail&ajax=true&audit_trail='+status,
                        onComplete: function(response) {
                                $("status").style.display="none";
                        }
                }
        );
			
	setTimeout("hide('audit_info')",3000);
}

function showAuditTrail()
{
	
	var userid = $('user_list').options[$('user_list').selectedIndex].value;
	
	window.open("index.php?module=Settings&action=SettingsAjax&file=ShowAuditTrail&userid="+userid,"","width=600,height=400,resizable=0,scrollbars=1,left=100");
	

}
</script>
{/literal}
