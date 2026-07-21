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
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
<script language="JavaScript" type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script language="JavaScript" type="text/javascript" src="modules/Settings/Settings.js"></script>

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


<div class="row">
	  <div class="col-lg-12">
	  		<div class="col-lg-9 pull-left">
	      		<h1><a href="index.php?module=Settings&action=kpisBoxscore&parenttab=Settings">{$MOD.LBL_KPIS_BOXSCORE} </a></h1>
	      	</div>
	      	<div class="col-lg-3 pull-right text-right">
		      	<a class="btn btn-primary" type="submit" href="index.php?module=Settings&action=EditKpisBoxscore&record={$KPI.kpisboxscoreid}">{$MOD.Edit}</a>
		      	<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=kpisBoxscore">{$MOD.LBL_KPIS_BOXSCORE_BACK}</a>
	      	</div>
	  </div>
</div>




<div class="row">
  	<div class="col-lg-12"> 
    	<div class="main-box">

	    	<header class="title-section main-box-header clearfix">
				<h2>Detalles de {$MOD.LBL_KPIS_BOXSCORE}</h2>
			</header>

		    <div class="main-box-body clearfix" id="">

		    	<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_KPIS_BOXSCORE_TITLE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_TITLE}">
								{$KPI.name}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_KPIS_BOXSCORE_DESCRIPCION}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_DESCRIPCION}">
								{$KPI.description}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_KPIS_BOXSCORE_ACTIVE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_ACTIVE}">
								{$KPI.active}
							</span>
						</div>
					</div>
				</div>

		    	<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_KPIS_BOXSCORE_MODULE}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_MODULE}">
								{$KPI.module}
							</span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_KPIS_BOXSCORE_QUERY}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<!--span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_QUERY}">
								{$KPI.querykpi}
							</span-->
							<textarea class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_QUERY}">{$KPI.querykpi}</textarea>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="col-md-4">
						<div class="label-input">
							<label for=""><h4>{$MOD.LBL_KPIS_BOXSCORE_QUERY_SEMANAL}</h4></label>
						</div>
					</div>
					<div class="form-group col-md-8">
						<div class="input-group" style="width: 100%;">
							<!--span class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_QUERY}">
								{$KPI.querykpi}
							</span-->
							<textarea class="form-control" readonly="" data-toggle="tooltip" data-original-title="" title="{$MOD.LBL_KPIS_BOXSCORE_QUERY_SEMANAL}">{$KPI.querykpisemanal}</textarea>
						</div>
					</div>
				</div>
				

		    </div>
    	</div>
  	</div>
</div>







<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>

<div class="md-overlay"></div><!-- the overlay element -->
	

<script language="javascript">

jQuery(document).ready(function() {ldelim}


{rdelim});

</script>

