{*<!--
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{if $smarty.request.ajax neq ''}
&#&#&#{$ERROR}&#&#&#
{/if}
<link href="themes/modern/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/morris/morris.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/AdminLTE.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/iCheck/all.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet"/>
<link href="themes/modern/css/timepicker/bootstrap-timepicker.min.css" rel="stylesheet"/>
<link href="themes/modern/css/stars/stars.css" rel="stylesheet"/>
<script src="themes/modern/js/bootstrap.min.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>

<script src="themes/modern/js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<!-- DATA TABES SCRIPT -->
<script src="themes/modern/js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
<style>
{literal}
.image-curso{width:100%;height:160px;background-size: 292px;background-repeat: no-repeat;}
.curso-bottom{position:absolute;bottom:0px;border-top:1px solid #ccc;width: 270px;padding: 10px;color:#9B9B9B;text-align: center;}
input[type="radio"][disabled]{cursor: auto;}
.folder{
    width: 1000px;
	position: fixed;
	left: 50%;
	top: 50%;
	margin-left: -540px;
	margin-top: -300px;
	z-index: 89990;
	height: 600px;
	display: block;
	padding: 20px;
	background-color:#eee;
	display:none;
	overflow-y:auto;
	padding-top:0px;
}
.blur{
	-webkit-transition: all 1s ease;
    -moz-transition: all 1s ease;
    -o-transition: all 1s ease;
    transition: all 1s ease;
	filter: blur(5px);
	-webkit-filter: blur(5px);
}
.display{
	-webkit-transition: all 1s ease;
    -moz-transition: all 1s ease;
    -o-transition: all 1s ease;
    transition: all 1s ease;
	display:block;
}
{/literal}
</style>
{foreach item=curso from=$CURSOS}
	{if !$curso.titulo}
			<div id="folders{$curso[0].formacion_cur_caid}" class="folder shadow-this">
				<div class="box-header">
					<h3 class="box-title">
						{$curso[0].categoria}
					</h3>
					<div class="box-tools pull-right">
					</div>
				</div>
				<a href="javascript:void(0)" class="close-editor" onclick="OpenCloseFolder({$curso[0].formacion_cur_caid})"></a>
				{foreach item=cur key=k from=$curso}
				
				<div class="col-lg-3" style="width: 33%;">
					<!-- Success box -->
					<a href="index.php?module=formacion_cursos&parenttab=Analytics&action=DetailView&record={$cur.formacion_cursosid}" alt="{$cur.titulo}" title="{$cur.titulo}">
					<div class="box box-solid box-{$cur.color}" style="height: 300px;">
						<div class="box-header">
							<h3 class="box-title">
								{$cur.titulo|truncate:30:"...":true}
							</h3>
							<div class="box-tools pull-right">
							</div>
						</div>
						<div class="image-curso" style="background-image:url('{$cur.image}');"></div>
						<div class="box-body" style="font-size: 15px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
							<p>{$cur.descripcion|truncate:80:"...":true}</p>
							<div class="curso-bottom">
								Puntuaci&oacute;n: 
								<div class="starRating">
								  <div>
									<div>
									  <div>
										<div>
										  <input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="1" disabled {if $cur.puntuacion >= 1}checked{/if}>
										  <label for="rating{$cur.formacion_cursosid}"><span>1</span></label>
										</div>
										<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="2" disabled {if $cur.puntuacion >= 2}checked{/if}>
										<label for="rating{$cur.formacion_cursosid}"><span>2</span></label>
									  </div>
									  <input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="3" disabled {if $cur.puntuacion >= 3}checked{/if}>
									  <label for="rating{$cur.formacion_cursosid}"><span>3</span></label>
									</div>
									<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="4" disabled {if $cur.puntuacion >= 4}checked{/if}>
									<label for="rating{$cur.formacion_cursosid}"><span>4</span></label>
								  </div>
								  <input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="5" disabled {if $cur.puntuacion >= 5}checked{/if}>
								  <label for="rating{$cur.formacion_cursosid}"><span>5</span></label>
								</div>
							
							</div>
						</div><!-- /.box-body -->
					</div><!-- /.box -->
					</a>
				</div><!-- /.col -->
				{/foreach}
				
			</div>

	{/if}
{/foreach}

<div class="row" style="width: 1286px;margin: 0px;background-color: #f4f4f4;padding-top: 15px;padding-bottom: 15px;" id="div-cursos">
	
	{foreach item=curso from=$CURSOS}
		{if $curso.titulo}
		<div class="col-lg-3">
			<!-- Success box -->
			<a href="index.php?module=formacion_cursos&parenttab=Analytics&action=DetailView&record={$curso.formacion_cursosid}" alt="{$curso.titulo}" title="{$curso.titulo}">
			<div class="box box-solid box-{$curso.color}" style="height: 300px;">
				<div class="box-header">
					<h3 class="box-title">
						{$curso.titulo|truncate:30:"...":true}
					</h3>
					<div class="box-tools pull-right">
					</div>
				</div>
				<div class="image-curso" style="background-image:url('{$curso.image}');"></div>
				<div class="box-body" style="font-size: 15px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
					<p>{$curso.descripcion|truncate:80:"...":true}</p>
					<div class="curso-bottom">
						Puntuaci&oacute;n: 
						<div class="starRating">
						  <div>
							<div>
							  <div>
								<div>
								  <input id="rating{$curso.formacion_cursosid}" type="radio" name="rating[{$curso.formacion_cursosid}]" value="1" disabled {if $curso.puntuacion >= 1}checked{/if}>
								  <label for="rating{$curso.formacion_cursosid}"><span>1</span></label>
								</div>
								<input id="rating{$curso.formacion_cursosid}" type="radio" name="rating[{$curso.formacion_cursosid}]" value="2" disabled {if $curso.puntuacion >= 2}checked{/if}>
								<label for="rating{$curso.formacion_cursosid}"><span>2</span></label>
							  </div>
							  <input id="rating{$curso.formacion_cursosid}" type="radio" name="rating[{$curso.formacion_cursosid}]" value="3" disabled {if $curso.puntuacion >= 3}checked{/if}>
							  <label for="rating{$curso.formacion_cursosid}"><span>3</span></label>
							</div>
							<input id="rating{$curso.formacion_cursosid}" type="radio" name="rating[{$curso.formacion_cursosid}]" value="4" disabled {if $curso.puntuacion >= 4}checked{/if}>
							<label for="rating{$curso.formacion_cursosid}"><span>4</span></label>
						  </div>
						  <input id="rating{$curso.formacion_cursosid}" type="radio" name="rating[{$curso.formacion_cursosid}]" value="5" disabled {if $curso.puntuacion >= 5}checked{/if}>
						  <label for="rating{$curso.formacion_cursosid}"><span>5</span></label>
						</div>
					
					</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
			</a>
		</div><!-- /.col -->
		{else}
		<div class="col-lg-3">
			<!-- Success box -->
			<a href="javascript:void(0)" onclick="OpenCloseFolder({$curso[0].formacion_cur_caid})" style="cursor:pointer;">
			<div class="box box-solid box-info" style="height: 300px;">
				<div class="box-header">
					<h3 class="box-title">
						Carpeta: {$curso[0].categoria|truncate:30:"...":true}
					</h3>
					<div class="box-tools pull-right" style="background-color: #fff;width: 292px;">
					{foreach item=cur key=k from=$curso}
						
						{if $k<=3}
						<div class="col-lg-3" style="width:50%;">
							<!-- Success box -->
							<div class="box box-solid box-{$cur.color}" style="height: 100px;">
								<div class="box-header">
									<h3 class="box-title" style="font-size: 6px;padding: 5px 0px 5px 10px;">
										{$cur.titulo|truncate:30:"...":true}
									</h3>
									<div class="box-tools pull-right">
									</div>
								</div>
								<div class="image-curso" style="background-image:url('{$cur.image}');height:50px;background-size: 110px;"></div>
								<div class="box-body" style="font-size: 5px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
									<p>{$cur.descripcion|truncate:80:"...":true}</p>
								</div><!-- /.box-body -->
							</div><!-- /.box -->
						</div><!-- /.col -->
						{/if}
					{/foreach}
					</div>
				</div>
			</div>
			</a>
		</div><!-- /.col -->
		{/if}
	{/foreach}

</div>
<script>
{literal}
function OpenCloseFolder(folderid){
	jQuery('#div-cursos').toggleClass('blur');
	jQuery('#folders'+folderid).fadeToggle('display');
	
}
{/literal}
</script>