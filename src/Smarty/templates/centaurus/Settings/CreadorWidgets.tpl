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
<!-- global styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_styles.css" />

<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css">
<link rel="stylesheet" href="themes/{$THEME}/css/libs/select2.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-editable.css">
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/hopscotch.css">

{if $SAVED}
<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-12">
			<ol class="breadcrumb">
				<li><a href="index.php?module=Settings&action=index&parenttab=Settings">Settings</a></li>
				<li class="active"><span>Widgets</span></li>
			</ol>
			<h1>Nuevo Widget</h1>
			<div class="icon-box pull-right">
				<input class="btn btn-info btn-sm" onclick="window.location.href='index.php?module=Settings&action=widgets&parenttab=Settings'" type="button" name="button" value="Volver" >
			</div>
		</div>
	</div>
</div>
	
	{$sbmtwidget}
{else}

<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-12">
			<ol class="breadcrumb">
				<li><a href="index.php?module=Settings&action=index&parenttab=Settings">Settings</a></li>
				<li class="active"><span>Widgets</span></li>
			</ol>
			<h1>Nuevo Widget</h1>
		</div>
	</div>
</div>

	<form id="CustomView" name="CustomView" method="post" action="index.php?module=Settings&action=createwidget&parenttab=Settings">
	<input type="hidden" name="widget_typeobj" id="widget_typeobj" value=""/>
	<input type="hidden" name="widget_type" id="widget_type" value=""/>
	<input type="hidden" name="widget_color" id="widget_color" value=""/>
	<input type="hidden" name="widget_class" id="widget_class" value=""/>
	<input type="hidden" name="record" id="record" value=""/>
	<input type="hidden" name="function" id="function" value="savewidget"/>
	<textarea id="sbmt-widget" name="sbmt-widget" style="display:none;"></textarea>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<div class="icon-box pull-right">
						<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="return validateSubmit();" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " >
						<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}  " >
					</div>
				</header>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>Seleccione un tipo Widget <i class="fa fa-spinner fa-spin widgetstatus" style="display:none;"></i></h2>
				</header>

				{include file='Settings/availablewidgets.tpl'}

				
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>Propiedades del Widget <i class="fa fa-spinner fa-spin widgetstatus" style="display:none;"></i></h2>
				</header>

				{include file='Settings/widgetColumnProperties.tpl'}

			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<div class="icon-box pull-right">
						<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success btn-sm" onclick="return validateSubmit();" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " >
						<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning btn-sm" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}  " >
					</div>
				</header>
			</div>
		</div>
	</div>

	</form>
	<!-- this page specific scripts -->
	<script src="themes/{$THEME}/js/wizard.js"></script>
	<!-- this page specific scripts -->
	<script src="themes/{$THEME}/js/hopscotch.js"></script>
	<script>
	{literal}

	
	var widget_type='';
	var widget_color='';
	var widget_class='';
	function  selectitem(id,type){
		jQuery( ".liobj" ).each(function() {
			jQuery(this).removeClass('itmsel');
		});
		jQuery( ".wobjs-shhd" ).each(function() {
			jQuery(this).hide();
		});
		jQuery('#waid_'+id).addClass('itmsel');
		jQuery('#waid'+id+'-shhd').fadeIn();
		jQuery('#widget_type').val(id);
		jQuery('#widget_typeobj').val(type);
		widget_type=id;
		updateColorClass();
	}
	jQuery('.itmul li').click(function(){
		jQuery( ".itmul li" ).each(function() {
			jQuery(this).removeClass('itmsel');
		});
		jQuery(this).addClass('itmsel');
		jQuery('#widget_color').val(jQuery(this).attr('data-color'));
		widget_color=jQuery(this).attr('data-color');
		updateColorClass();
	});
	jQuery('.the-icons i').click(function(){
		jQuery( ".the-icons i" ).each(function() {
			jQuery(this).removeClass('itmsel');
		});
		jQuery('#widget_class').val(jQuery(this).attr('class'));
		widget_class=jQuery(this).attr('class');
		jQuery(this).addClass('itmsel');
		updateColorClass();
	});


	function updateColorClass(){
		//jQuery('.wgraphics').hide();
		jQuery('#waid'+widget_type).show();
		
		jQuery('#waid'+widget_type+'-ico').removeClass();
		
		jQuery('#waid'+widget_type+'-ico').addClass(widget_class+' '+widget_color);
		if(widget_type==1){
			jQuery('#waid'+widget_type+'-val').removeClass();
			var txtcolor=widget_color.replace("-bg", "");
			jQuery('#waid'+widget_type+'-val').addClass('value '+txtcolor);
		}else if(widget_type==2){
			jQuery('#waid'+widget_type).removeClass();
			jQuery('#waid'+widget_type).addClass('main-box small-graph-box '+widget_color);
		}
	}


	jQuery('.icons-scroll').slimScroll({
		height: '120px',
		alwaysVisible: false,
		railVisible: true,
		wheelStep: 5,
		allowPageScroll: false
	});

	jQuery('#widgetav').slimScroll({
		height: '260px',
		alwaysVisible: true,
		railVisible: true,
		wheelStep: 5,
		allowPageScroll: false
	});

	function validateSubmit(){
		if(widget_type<=3 && (widget_class=='' || widget_color=='')){
			alert('Debe seleccionar todos los componentes!');
			return false;
		}
		if(widget_type<=3 && (jQuery('#wmodulo').val()=='' || jQuery('#fieldop').val()=='' || jQuery('#opcolumn').val()=='')){
			alert('Debe seleccionar los operadores!');
			return false;
		}
		jQuery('#sbmt-widget').val(jQuery('#waid'+widget_type+'-shhd').html());
		return true;
	}
	
	function shtools(sh){
		sh ? jQuery('#wtools,#wGraph').fadeOut() : jQuery('#wtools,#wGraph').fadeIn();
		sh ? jQuery('#wtwid').fadeIn() : jQuery('#wtwid').fadeOut();
	}
	
	

	{/literal}
	</script>
{/if}