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
<script type="text/javascript" src="include/js/highcharts/js/highcharts.js"></script>
<script type="text/javascript" src="include/js/highcharts/js/modules/funnel.js"></script>
<script type="text/javascript" src="include/js/highcharts/js/modules/exporting.js"></script>
{if $MODULENAME eq 'Home'}
<div class="row">
	<div class="col-lg-12">
		<!--ol class="breadcrumb">
			<li><a href="#">Home</a></li>
			<li class="active"><span>Dashboard</span></li>
		</ol-->
		
		<h1>Hola {$FIRST_NAME}</h1>
	</div>
</div>


<div class="col-lg-12" id="salute">
	<div class="main-box clearfix">
		<div class="conversation-wrapper">
			<div class="modal-header">
				<button class="md-close close" type="button" onclick="hiddenDivSalute()">×</button>
			</div>
			<div class="conversation-content">
				<div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: auto;">
					<div class="conversation-inner" style="overflow: hidden; width: auto; height: auto;">
						<div class="conversation-item item-left clearfix">
							<div style="float:left;">
							<img src="themes/{$THEME}/img/platzillaman.png" style="height:60px;width:auto"/>
							</div>
							<div class="conversation-body" style="margin-left: 30px;float:left;">
								<div class="text">
								¡Al fin encontraste el CRM sencillo y práctico que andabas buscando! Creado con todo el poder de <a target="_blank" href="http://www.gestionar-facil.com/que-es-platzilla/">Platzilla</a><br/>
								Antes de empezar, te recomiendo hagas un <a href="#" onclick="hopscotch.startTour(tour);">Tour por la aplicación.</a><br/>
								Recuerda que podemos ayudarte a personalizar esta aplicación. <a href="#" onclick="jQuery('#modal-10').addClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 1.0, visibility: 'visible'{rdelim});jQuery('.md-overlay').addClass('md-show');">Habla con nosotros </a>
								</div>
							</div>
							{if $TESTDATA neq 'false'}
							<div style="float:left;max-width:250px;margin-left:30px;font-size:80%;text-align:center;">
								<a href="index.php?module=Home&action=index&mode=deldata">
								<button type="button" class="btn btn-primary">Borrar datos de PRUEBA</button>
								</a><br/><br/>
								Así podrás poner tu propia información
							</div>
							{/if}
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/if}

<div class="row">
	<div class="col-lg-12" id="elsortable">
	{foreach item=WD from=$WIDGETS}
		{$WD.widget}
	{/foreach}
	</div>
</div>
<script>
{literal}

var elsortable=jQuery('#elsortable').sortable();

jQuery(document).ready(function() {
	jQuery('#content-wrapper').css('background-color','#f1f3f7');
});

function hiddenDivSalute(){
	jQuery('#salute').hide();
}
{/literal}
</script>
<!-- this page specific scripts -->
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="themes/{$THEME}/js/flot/excanvas.min.js"></script><![endif]-->
<script src="themes/{$THEME}/js/flot/jquery.flot.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.pie.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.stack.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.resize.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.time.min.js"></script>
<script src="themes/{$THEME}/js/flot/jquery.flot.orderBars.js"></script>
