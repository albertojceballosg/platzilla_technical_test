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
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
{literal}
<style>
DIV.fixedLay{
	border:3px solid #CCCCCC;
	background-color:#FFFFFF;
	width:500px;
	position:fixed;
	left:250px;
	top:200px;
	display:block;
}
</style>
{/literal}
{literal}
<!--[if lte IE 6]>
<STYLE type=text/css>
DIV.fixedLay {
	POSITION: absolute;
}
</STYLE>
<![endif]-->

{/literal}






<div class="col-lg-12">				
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
		
			 <li>{$MOD.NOTIFICATIONSCHEDULERS}</li>
			
		</ol>
			
		</div>
	</div>
</div>


<div class="col-lg-12">
							
	<div class="row">
		<div class="main-box no-header clearfix" style="">
				
			
			<div class="col-lg-6 pull-left">



				<h2>{$MOD.LBL_NOTIF_SCHED_DESCRIPTION}</h2>
				
			
			
			</div>
		    
		   
		    
		    
			
		
	
	
		</div>
	
	</div>

</div>


<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
				<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table">
                  <tr >

                    <td  style="padding-left:5px;margin-top:20px;">{$MOD.NOTIFICATIONSCHEDULERS}</td>
                    <td align="right">&nbsp;</td>
                  </tr>
			  </table>
			  
			  <div id="notifycontents">
	{include file='Settings/EmailNotificationContents.tpl'}
	</div>
			</div>
		</div>
	</div>
</div>



<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
{literal}
<script>
function fetchSaveNotify(id)
{
	$("editdiv").style.display="none";
	$("status").style.display="inline";
	var active = $("notify_status").options[$("notify_status").options.selectedIndex].value;
	var subject = $("notifysubject").value;
        var body = $("notifybody").value;
	new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'action=SettingsAjax&module=Settings&file=SaveNotification&active='+active+'&notifysubject='+subject+'&notifybody='+body+'&record='+id,
                        onComplete: function(response) {
					if(response.responseText.indexOf(":#:FAILURE") > -1)
					{
						alert(alert_arr.VALID_DATA);
					}else
					{
						$("notifycontents").innerHTML=response.responseText;
					}
                                $("status").style.display="none";
                        }
                }
        );
}

function fetchEditNotify(id)
{
	$("editdiv").style.display="inline";
	
	new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'action=EditNotification&module=Settings&Ajax=true&record='+id,
                        
                        onComplete: function(response) {
                               
                                $("editdiv").innerHTML=response.responseText;
                                
                                
                        }
                }
        );
}
</script>
{/literal}
