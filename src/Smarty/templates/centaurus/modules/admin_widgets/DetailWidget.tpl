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
<script language="JavaScript" type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>

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

{/literal}

<!--<form action="index.php?module={$MODULE}&action=SaveEditCustomButtons" method="post" id="SaveEditCustomButtons" name="index" onsubmit="">-->

<div class="row">
	<div class="col-lg-12">
  		<div class="col-lg-9 pull-left">
      		<h1><a href="index.php?module={$MODULE}&action=index">{$MOD.LBL_WIDGET} </a></h1>
      	</div>
      	<div class="col-lg-3 pull-right text-right">
	      	<a class="btn btn-primary" type="submit" href="index.php?module={$MODULE}&action=EditWidgets&record={$DETAILWIDGET.widgetid}">{$MOD.LBL_EDIT_WIDGET}</a>
	      	<a class="btn btn-warning" type="submit" href="index.php?module={$MODULE}&action=index">{$MOD.LBL_BACK_WIDGET}</a>
      	</div>
	</div>
</div>
<!--</form>-->

<div class="row">
  	<div class="col-lg-12"> 
    	<div class="main-box">
	    	<header class="title-section main-box-header clearfix">
				<h2>Detalles del {$MOD.LBL_WIDGET}</h2>
			</header>
		    <div class="main-box-body clearfix" id="">
		    	<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_TEXT_WIDGET}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_TEXT_WIDGET}">
								{$DETAILWIDGET.texto}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_WIDGETLIST_MODULE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_WIDGETLIST_MODULE}">
								{$DETAILWIDGET.module}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6" {if $DETAILWIDGET.campofecha eq ''} style="display:none" {/if}>
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_WIDGETLIST_FECHA_CAMPO}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_WIDGETLIST_FECHA_CAMPO}">
								{$DETAILWIDGET.campofechaTraducido}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6" {if $DETAILWIDGET.campofecha eq ''} style="display:none" {/if}>
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_WIDGETLIST_FECHA_TIEMPO}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_WIDGETLIST_FECHA_TIEMPO}">
								{$DETAILWIDGET.tiempofecha}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6" {if $DETAILWIDGET.campofecha eq ''} style="display:none" {/if}>
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_WIDGETLIST_FECHA_DESDE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_WIDGETLIST_FECHA_DESDE}">
								{$DETAILWIDGET.fechadesde}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6" {if $DETAILWIDGET.campofecha eq ''} style="display:none" {/if}>
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_WIDGETLIST_FECHA_HASTA}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_WIDGETLIST_FECHA_HASTA}">
								{$DETAILWIDGET.fechahasta}
							</span>
						</div>
					</div>
				</div>

		    	<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_FIELD_OPERATION}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_FIELD_OPERATION}">
								{$DETAILWIDGET.fieldO}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_TIPO_CALCULO}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_TIPO_CALCULO}">
								{$DETAILWIDGET.operacion}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_CAMPO_GROUP}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_CAMPO_GROUP}">
								{$DETAILWIDGET.fieldgrouping}
							</span>
						</div>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_ICONO_WIDGET}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_ICONO_WIDGET}">
								<i class="{$DETAILWIDGET.icono}"></i>
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_COLOR_WIDGET}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control {$DETAILWIDGET.color}" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_COLOR_WIDGET}" >
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_STATUS}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_STATUS}" >{$DETAILWIDGET.status}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_WIDGET_STYLEBUTTON}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">							
							<div class="row">									
								<div class="col-md-12">
									<div class="main-box infographic-box">
										<i class="{$DETAILWIDGET.icono} {$DETAILWIDGET.color}"></i>
										<span class="value {$DETAILWIDGET.colorValue}" style="text-align: left;">{if $DETAILWIDGET.valor.variablegraficar == NULL}0{else}{$DETAILWIDGET.valor.variablegraficar}{/if}</span>
										<span class="headline" style="text-align: left;">{$DETAILWIDGET.texto}</span>										
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