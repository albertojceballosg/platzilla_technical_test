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
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery.knob.js"></script>
<script src="themes/{$THEME}/js/raphael-min.js"></script>
<script src="themes/{$THEME}/js/morris.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/flot/excanvas.min.js"></script><![endif]-->
<script src="themes/{$THEME}/js/flot/jquery.flot.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.pie.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.stack.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.resize.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.time.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.orderBars.js"></script>
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery-ui.custom.min.js"></script>

<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">Settings</a></li>
					<li class="active"><span>Widgets</span></li>
				</ol>
				<h1 class="pull-left">Widgets</h1>
				<div class="col-lg-8 icon-box pull-right">
					<a href="index.php?module=Settings&action=createwidget&parenttab=Settings" class="btn btn-primary pull-right">
						<i class="fa fa-plus-circle fa-lg" title=""></i> Nuevo Widget
					</a>
				</div>
			</div>
		</div>
		
	</div>
	<div class="row">
		{foreach item=WDG key=mdlname from=$WIDGETS}
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>Dashboard en {$WIDGETSTABS[$mdlname]}</h2>
				</header>
				<div class="col-lg-12" id="elsortable_{$mdlname}">
				{foreach item=WD from=$WDG}
					<div id="wid_{$WD.panelid}" class="col-lg-12 pull-left drgbl">
						<button type="button" class="close pull-left" title="Borrar Widget!" onclick="deleteWidget({$WD.panelid})">&times;</button>
						{$WD.widget}
					</div>
				{/foreach}
				</div>
			</div>
		{/foreach}
	</div>
</div>
<script>
{foreach item=WDG key=mdlname from=$WIDGETS}
	var elsortable_{$mdlname}=jQuery('#elsortable_{$mdlname}').sortable();
{/foreach}

{literal}

function deleteWidget(panelid){
	if(!confirm('Esta seguro que desea eliminar el Widget?'))
		return false;
	jQuery('#status').show();
	jQuery.ajax({
		type: 'POST',
		url: 'index.php',
		dataType:'JSON',
		data: { module: 'Settings', action: 'createwidget', function: 'deleteWidget', Ajax: 'true', panelid: panelid }
	}).done(function(r) { 
		if(r.success=='true'){
			jQuery('#wid_'+panelid).slideUp("normal", function() { jQuery(this).remove(); } );
		}
		jQuery('#status').hide();
	});
}
{/literal}
</script>