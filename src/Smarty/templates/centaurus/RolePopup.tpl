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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<link rel="stylesheet" type="text/css" href="{$THEME}/css/bootstrap/bootstrap.min.css">
<!-- libraries -->
<link rel="stylesheet" type="text/css" href="{$THEME}/css/libs/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="{$THEME}/css/libs/nanoscroller.css" />
<!-- global styles -->
<link rel="stylesheet" type="text/css" href="{$THEME}/css/compiled/theme_styles.css" />
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="{$THEME}/css/libs/dataTables.fixedHeader.css">
<link rel="stylesheet" type="text/css" href="{$THEME}/css/libs/dataTables.tableTools.css">

<style type="text/css">
	a.x {ldelim}
		color:black;
		text-align:center;
		text-decoration:none;
		padding:5px;
		font-weight:bold;
	{rdelim}
	
	a.x:hover {ldelim}
		color:#333333;
		text-decoration:underline;
		font-weight:bold;
	{rdelim}

	li {ldelim}
		background:transparent;
		padding:0px;
		margin:0px 0px 0px 0px;
	{rdelim}

	ul li{ldelim}
		margin-top:5px;
		margin-left:5px;
	{rdelim}

	ul {ldelim}color:black;{rdelim}	 

</style>
<script type="text/javascript" src="include/js/general.js"></script>
</head>
<body marginheight="0" marginwidth="0" leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0">



<div style="opacity: 1; width: 95%;margin: 20px auto 0 auto;" class="row">
		
	<div class="col-lg-12" style="">

		<div class="row">
			<div class="col-lg-12">
			<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h1>
					{$CMOD.LBL_ASSIGN_ROLE}
				</h1>
			</header>

			<div class="main-box-body clearfix">
				<div id="nestable-menu">
					<!--button type="button" class="btn btn-primary" data-action="expand-all">Expand All</button>
					<button type="button" class="btn btn-danger" data-action="collapse-all">Collapse All</button-->
				</div>
		
				<div class="row cf nestable-lists">
					<div class="col-md-12" id='RoleTreeFull'  onMouseMove="displayCoords(event)"> 
        			    {$ROLETREE}
                	</div>
				</div>
			</div>
		</div>
	</div>


<script>
function showhide(argg,imgId)
{ldelim}
	var harray=argg.split(",");
	var harrlen = harray.length;
	var i;
	for(i=0; i<harrlen; i++)
	{ldelim}
        	var x=document.getElementById(harray[i]).style;
        	if (x.display=="none")
        	{ldelim}
            		x.display="block";
					document.all[imgId].src = "themes/images/minus.gif";
        	{rdelim}
        	else
			{ldelim}
            			x.display="none";
						document.all[imgId].src = "themes/images/plus.gif";
            {rdelim}
	{rdelim}
{rdelim}

function loadValue(currObj,roleid)
{ldelim}
		window.opener.document.getElementById('role_name').value = convert_lt_gt(document.getElementById(currObj).innerHTML);
		window.opener.document.getElementById('user_role').value = roleid;
		window.close();
{rdelim}
function convert_lt_gt(str)
{ldelim}
	str = str.replace(/(&lt;)/g,'<');
	str = str.replace(/(&gt;)/g,'>');
	str = str.replace(/(&amp;)/g,'&');
	return str;
{rdelim}		
</script>
</body>
</html>
