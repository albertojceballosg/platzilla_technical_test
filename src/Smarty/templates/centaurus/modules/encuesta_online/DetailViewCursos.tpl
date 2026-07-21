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
<link href="themes/modern/css/stars/stars.css" rel="stylesheet"/>
<link href="themes/modern/css/AdminLTE.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/morris/morris.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/iCheck/all.css" rel="stylesheet" type="text/css" />
<link href="themes/modern/css/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet"/>
<link href="themes/modern/css/timepicker/bootstrap-timepicker.min.css" rel="stylesheet"/>
<script src="themes/modern/js/bootstrap.min.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script src="themes/modern/js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>

<script src="themes/modern/js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<script src="themes/modern/js/AdminLTE/app.js" type="text/javascript"></script>

<link href="modules/video/html5player/video-js.css" rel="stylesheet" type="text/css">
<script src="modules/video/html5player/video.js"></script>

<style>
{literal}
.image-curso{width:100%;height:400px;background-size: 930px;background-repeat: no-repeat;}
.curso-bottom{border-top:1px solid #ccc;width: 930px;padding: 10px;color:#9B9B9B;text-align: center;}
input[type="radio"][disabled]{cursor: auto;}
.lec-link a{text-decoration:none;}
{/literal}
</style>

<div class="row" style="width: 930px;margin: 0px;background-color: #fff;">
	<div class="box box-solid box-success">
		<div class="box-header">
			<h3 class="box-title">
				{$FIELDS.titulo}
			</h3>
			<div class="box-tools pull-right">
			</div>
		</div>
		<div class="image-curso" style="background-image:url('{$FIELDS.image}');"></div>
		<div class="box-body" style="font-size: 15px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
			<p>{$FIELDS.descripcion}</p>
			<center>
				<button class="btn btn-success" panel="open">Empezar</button>
			</center>
		</div><!-- /.box-body -->
		<div class="curso-bottom" style="color:#444;">
			<h3 class="box-title">Encuesta</h3>
			<table class="table table-striped">
				<tr>
					<th width="1%">#</th>
					<th width="20%">Titulo</th>
					<th>Descripci&oacute;n</th>
					<th>Categoria</th>
					<th width="1%">&nbsp;</th>
				</tr>
				{foreach key=num item=leccion from=$LECCIONES}
					<tr>
						<td>{$num+1}</td>
						<td align="left">{$leccion.titulo}</td>
						{*<td align="left">{$leccion.contenido|html_entity_decode}</td>*}
						<td align="left">{$leccion.descripcion}</td>
						<td align="left">{$leccion.titulo1}</td>
						<td class="lec-link">
							
								<a href="javascript:void(0)" onclick="openCursos();setcontenido({$leccion.encuestaid})">
									<span class="label label-success" style="font-size: 12px;"><i class="fa fa-fw fa-list-alt"></i>Ver</span>
								</a>
						
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
		<div class="curso-bottom">
			Puntuaci&oacute;n: 
			<div class="starRating">
			  <div>
				<div>
				  <div>
					<div>
					  <input id="rating1" type="radio" name="rating[1]" value="1" disabled {if $FIELDS.puntuacion >= 1}checked{/if}>
					  <label for="rating1"><span>1</span></label>
					</div>
					<input id="rating2" type="radio" name="rating[2]" value="2" disabled {if $FIELDS.puntuacion >= 2}checked{/if}>
					<label for="rating2"><span>2</span></label>
				  </div>
				  <input id="rating3" type="radio" name="rating[3]" value="3" disabled {if $FIELDS.puntuacion >= 3}checked{/if}>
				  <label for="rating3"><span>3</span></label>
				</div>
				<input id="rating4" type="radio" name="rating[4]" value="4" disabled {if $FIELDS.puntuacion >= 4}checked{/if}>
				<label for="rating4"><span>4</span></label>
			  </div>
			  <input id="rating5" type="radio" name="rating[5]" value="5" disabled {if $FIELDS.puntuacion >= 5}checked{/if}>
			  <label for="rating5"><span>5</span></label>
			</div>
		</div>
	</div><!-- /.box -->
</div>

<style>
{literal}
.image-curso{width:100%;height:400px;background-size: 930px;background-repeat: no-repeat;}
.curso-bottom{border-top:1px solid #ccc;width: 930px;padding: 10px;color:#9B9B9B;text-align: center;}
input[type="radio"][disabled]{cursor: auto;}

#curso-completo{
	background-color: #f9f9f9;
	width: 100%;
	height: 100%;
	position: absolute;
	left: 0;
	top: 0;
	z-index: 100;
	display:none;
	z-index: 99999999;
}
.noscroll { position: fixed; overflow-y:hidden }
.examenes{position:absolute;width:1015px;height: 630px;background-color:#f9f9f9;padding: 20px 15px;display:none;overflow-y: scroll;overflow-y: scroll;overflow-x: hidden;}
#curso-completo p{font-family: 'Source Sans Pro', sans-serif;}
#curso-completo h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6{font-family: 'Source Sans Pro', sans-serif;}
.lab_check{cursor: pointer;}
.lab_check:hover{color: #888;}
.hthead{
display: inline-block;
padding: 10px 0px 10px 10px;
margin: 0;
font-size: 20px;
font-weight: 400;
float: left;
cursor: default;
}
{/literal}
</style>

<div id="curso-completo" class="skin-blue wysihtml5-supported  pace-done">
	<header class="header">
		<a href="#" class="logo h3" panel="close" style="width: 350px;text-decoration: none;font-family: 'Source Sans Pro', sans-serif;">Volver al detalle de la encuesta</a>
		<nav class="navbar navbar-static-top" role="navigation" style="margin-left: 350px;">
			<!--a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a-->
		</nav>
	</header>
	<div class="wrapper row-offcanvas row-offcanvas-left" style="background-color:#000;">
		<aside class="left-side sidebar-offcanvas" style="width: 350px;">
			<section class="sidebar" style="overflow-y:auto">
				<div class="box-group" id="accordion">
					<div class="panel box" style="margin-bottom: 0px;">
						<div class="box-header" >
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
								<i class="fa fa-users hthead" style="cursor: pointer;"></i>
								<h3 class="box-title hthead" style="cursor: pointer;">Encuesta</h3>
							</a>
						</div>
						<div id="collapseOne" class="panel-collapse collapse in">
							<ul class="sidebar-menu">
								{foreach key=num item=leccion from=$LECCIONES}
									{if $leccion.videoid}
										<li {if $num eq 0} class="active"{/if} lv="{$leccion.videoid}">
											<a href="#" fn="set-play" video-file="{$leccion.file}" video-ext="{$leccion.ext}" leccion="{$leccion.formacion_leccionesid}" lv="{$leccion.videoid}"><i class="fa fa-play"></i> {$leccion.titulo}</a>
											<div style="position:absolute;margin-left: 280px;margin-top: -25px;">
												<span class="label label-success" {if $num neq 0}style="display:none;"{/if} id="video_time_{$leccion.videoid}">00:00:00</span>
											</div>
										</li>
									{elseif $leccion.materiales && $leccion.ext_arch eq 'pdf'}
										<li {if $num eq 0} class="active"{/if} lv="{$leccion.materiales}" ext="{$leccion.ext_arch}">
											<a href="#" fn="set-pdf" file="{$leccion.material}" file-ext="{$leccion.ext_arch}" leccion="{$leccion.formacion_leccionesid}" lv="{$leccion.materiales}"><i class="fa fa-file-text-o"></i> {$leccion.titulo}</a>
											<!--div style="position:absolute;margin-left: 250px;margin-top: -25px;">
												<span class="label label-success" {if $num neq 0}style="display:none;"{/if} id="video_time_{$leccion.videoid}">00:00:00</span>
											</div-->
										</li>
									{else}
										<li {if $num eq 0} class="active"{/if} >
											<a href="javascript:setcontenido({$leccion.formacion_leccionesid})"><i class="fa fa-list-alt"></i> {$leccion.titulo}</a>
											<div id="contenido{$leccion.formacion_leccionesid}" style="display:none">
												{$leccion.contenido|html_entity_decode}
												{if $leccion.materiales}
													<div align="center" style="margin-top:20px">
														<a class="btn btn-app" href="{$leccion.material}" title="Descargar {$leccion.archivo}" download="{$leccion.archivo}">
															<i class="fa fa-cloud-download"></i>{$leccion.archivo}
														</a>
													</div>
												{/if}
											</div>
										</li>
									{/if}
									{if $leccion.eval}
										{foreach key=ne item=eval from=$leccion.eval}
										<li lv_eval="{$eval.formacion_pruebasid}">
											<a href="javascript:void(0)" style="padding-left: 40px;" fn='set-eval' onclick="set_eval({$eval.formacion_pruebasid},'')" evalid="{$eval.formacion_pruebasid}" step=""><i class="fa fa-edit"></i> {$eval.titulo}</a>
										</li>
										{/foreach}
									{/if}
								{/foreach}
							</ul>
						</div>
					</div>
					<div class="panel box" style="margin-bottom: 0px;">
						<div class="box-header">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" fn="update-comm" record="{$ID}">
								<i class="fa fa-comments hthead" style="cursor: pointer;"></i>
								<h3 class="box-title hthead" style="cursor: pointer;">Comentarios</h3>
							</a>
							
						</div>
						<div id="collapseTwo" class="panel-collapse collapse" >
							<img src="themes/images/status.gif" id="ajaxstatus_comm" style="position: absolute;z-index: 999;right: 20px;margin-top:-45px;display:none;">
							<div id="comentarios"></div>
						</div>
					</div>
				</div>
			</section>
		</aside>
		<aside class="right-side" style="margin-left: 350px;background-color:#000;">
			<img src="themes/images/status.gif" id="ajaxstatus" style="position: absolute;z-index: 999;left: 34%;margin-top: 20px;display:none;">
			<div id="strat-layer" style="text-align: center;background-color: #fff;height:850px;width:1100px;position: absolute;z-index: 10;margin-top:-10px;padding-top: 200px;">
				<h3>Encuestas</h3><br>
				{assign var="nlogo" value="$PLAT_CODE/test/logo/$LOGO"}
				{if file_exists($nlogo)}
					<img src="{$nlogo}" alt="{$LOGO}" title="{$LOGO}" border=0 style="height: 60px;">
				{else}
					<img src="test/logo/{$LOGO}" alt="{$LOGO}" title="{$LOGO}" border=0 style="height: 60px;">
				{/if}
			</div>
			<video id="example_video_1" class="video-js vjs-default-skin" style="margin-left: 20px;margin-top:10px;{if !$LECCIONES[0].file}display:none;{/if}" controls preload="none" width="950" height="600">
				<source id="set-video" src="storage/video_uploads/{$LECCIONES[0].file}" type='video/{$LECCIONES[0].ext}'>
				novideo
			</video>
			<div id="object_pdf" class="examenes" style="padding:0px;overflow-y: hidden;{if !$LECCIONES[0].file && $LECCIONES[0].material && $LECCIONES[0].ext_arch eq 'pdf'}display:block;{/if}">
				<object id="objid" data="{$LECCIONES[0].material}" type="application/pdf" width="1015" height="630">
				alt : <a href="{$LECCIONES[0].material}">{$LECCIONES[0].material}</a>
				</object>
			</div>
			<div id="examenes" class="examenes" style="{if !$LECCIONES[0].file && $LECCIONES[0].ext_arch neq 'pdf'}display:block;{/if}">
				{if !$LECCIONES[0].file && $LECCIONES[0].ext_arch neq 'pdf'}
					{$LECCIONES[0].contenido|html_entity_decode}
					{if $LECCIONES[0].materiales}
						<div align="center" style="margin-top:20px">
							<a class="btn btn-app" href="{$LECCIONES[0].material}" title="Descargar {$LECCIONES[0].archivo}" download="{$LECCIONES[0].archivo}">
								<i class="fa fa-cloud-download"></i>{$LECCIONES[0].archivo}
							</a>
						</div>
					{/if}
				{/if}
			</div>
		</aside>
	</div>
	
	
</div>

<script>
var tiempo;
var this_record='{$RECORD}';
{if $smarty.request.ob eq '1'}
	tiempo=setTimeout("updatetime()",500);
	openCursos();
{/if}
var lecciones={$LECCIONES_OBJ};
var videoid="{$LECCIONES[0].videoid}";
var videos_vistos=0;
{literal}
videojs.options.flash.swf = "modules/video/html5player/video-js.swf";
jQuery("[panel='close']").click(function(e) {
	e.preventDefault();
	jQuery('body').removeClass('noscroll');
	jQuery('#curso-completo').fadeOut();
	myPlayer.pause();
	OCStratLayer('o');
});

jQuery("[fn='set-play']").click(function(e) {
	e.preventDefault();
	OCStratLayer('o');
	jQuery('#example_video_1').show();
	jQuery('#examenes').hide();
	jQuery('#object_pdf').hide();
	jQuery(".sidebar-menu li").each(function(){
		jQuery(this).removeClass('active');
		jQuery("#video_time_"+jQuery(this).attr('lv')).hide();
	});
	jQuery(this).parent().addClass('active');
	var video_file="storage/video_uploads/"+jQuery(this).attr('video-file');
	videoid=jQuery(this).attr('lv');
	jQuery("#video_time_"+videoid).show();
	jQuery("#set-video").attr("type","video/"+jQuery(this).attr('video-ext'));
	tiempo=setTimeout("updatetime()",500);
	myPlayer.src(video_file);
	myPlayer.play();
	setTimeout("OCStratLayer('c')",1000);
});

jQuery("[fn='set-eval']").click(function(e) {
	e.preventDefault();
	jQuery(".sidebar-menu li").each(function(){
		jQuery(this).removeClass('active');
		jQuery("#video_time_"+jQuery(this).attr('lv')).hide();
	});
	jQuery(this).parent().addClass('active');
});

jQuery("[fn='set-pdf']").click(function(e) {
	e.preventDefault();
	jQuery(".sidebar-menu li").each(function(){
		jQuery(this).removeClass('active');
		jQuery("#video_time_"+jQuery(this).attr('lv')).hide();
	});
	jQuery(this).parent().addClass('active');
	if(myPlayer.src())
		myPlayer.pause();
	jQuery('#example_video_1').hide();
	jQuery('#examenes').hide();
	jQuery('#objid').attr("src",jQuery(this).attr('file'));
	jQuery('#object_pdf').fadeIn();
	OCStratLayer('c');
});


function set_eval(evalid,step){
	jQuery(".sidebar-menu li").each(function(){
		jQuery(this).removeClass('active');
		jQuery("#video_time_"+jQuery(this).attr('lv')).hide();
	});
	jQuery(this).parent().addClass('active');
	if(myPlayer.src())
		myPlayer.pause();
	jQuery('#example_video_1').hide();
	jQuery('#object_pdf').hide();
	jQuery('#examenes').fadeIn();
	getEvaluacion(evalid,step);
}

function setcontenido(id){
	jQuery(".sidebar-menu li").each(function(){
		jQuery(this).removeClass('active');
		jQuery("#video_time_"+jQuery(this).attr('lv')).hide();
	});
	jQuery(this).parent().addClass('active');
	if(myPlayer.src())
		myPlayer.pause();
	jQuery('#example_video_1').hide();
	jQuery('#object_pdf').hide();
	jQuery('#examenes').fadeIn();
	jQuery('#examenes').html(jQuery('#contenido'+id).html());
}

jQuery("[fn='eval-steps']").click(function(e) {
	e.preventDefault();	
	jQuery('#ajaxstatus').show();
	var evalid=jQuery(this).attr('evalid');
	var step=jQuery(this).attr('step');
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "evaluaciones", action: "evaluacionesAjax",file: "rendir_por_pasos",record: evalid }
	}).done(function( response ) {
		//console.log(response);
		jQuery('#ajaxstatus').hide();
		jQuery("#examenes").html(response);
		OCStratLayer('c');
	});
	
});

jQuery("[fn='update-comm']").click(function(e) {
	e.preventDefault();	
	jQuery('#ajaxstatus_comm').show();
	var record=jQuery(this).attr('record');
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "encuesta_online", action: "encuesta_onlineAjax",file: "comments",record: record }
	}).done(function( response ) {
		jQuery('#ajaxstatus_comm').hide();
		jQuery("#comentarios").html(response);
		jQuery('#chat-box').slimScroll({
			height: '450px'
		});
	});
	
});

function saveComment(){
	jQuery('#ajaxstatus_comm').show();
	var typecomm=jQuery('#typecomm').val();
	if(typecomm==''){
		alert('El comentario no puede estar vacio!!');
		return false;
	}
	var record=jQuery('#record').val();
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "encuesta_online", action: "encuesta_onlineAjax",file: "comments",save: 'true',comm:typecomm,record:record }
	}).done(function( response ) {
		console.log(response);
		jQuery('#ajaxstatus_comm').hide();
		jQuery("#comentarios").html(response);
		jQuery('#chat-box').slimScroll({
			height: '450px'
		});
	});
	return false;
}

jQuery("[fn='set-open-play']").click(function(e) {
	e.preventDefault();
	OCStratLayer('o');
	jQuery('#object_pdf').hide();
	jQuery('#examenes').hide();
	jQuery('#example_video_1').show();
	openCursos();
	jQuery(".sidebar-menu li").each(function(){
		jQuery(this).removeClass('active');
		jQuery("#video_time_"+jQuery(this).attr('lv')).hide();
	});
	jQuery(this).parent().addClass('active');
	var video_file="storage/video_uploads/"+jQuery(this).attr('video-file');
	videoid=jQuery(this).attr('lv');
	jQuery("#video_time_"+videoid).show();
	tiempo=setTimeout("updatetime()",500);
	myPlayer.src(video_file);
	myPlayer.play();
	setTimeout("OCStratLayer('c')",1000);
});

jQuery("[panel='open']").click(function(e) {
	e.preventDefault();
	openCursos();
});


function getEvaluacion(evalid,step){
	jQuery('#ajaxstatus').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "evaluaciones", action: "evaluacionesAjax",file: "rendir_por_pasos",record: evalid,step:step }
	}).done(function( response ) {
		//console.log(response);
		jQuery('#ajaxstatus').hide();
		jQuery("#examenes").html(response);
		OCStratLayer("c");
	});
}

function nextStep(step){
	jQuery('#ajaxstatus').show();
	var data=jQuery('#evaluacion').serialize();
	jQuery.ajax({
		type: "POST",
		url: "index.php?"+data,
		data: { module: "evaluaciones", action: "evaluacionesAjax",file: "rendir_por_pasos",step:step }
	}).done(function( response ) {
		//console.log(response);
		jQuery('#ajaxstatus').hide();
		jQuery("#examenes").html(response);
		OCStratLayer("c");
	});
}

function openCursos(){
	//jQuery('#examenes').hide();
	//jQuery('#example_video_1').show();
	var sidebarscroll=jQuery(window).height() - 30;
	jQuery('.sidebar').height(sidebarscroll)
	jQuery('body').css('top', -(document.documentElement.scrollTop) + 'px').addClass('noscroll');
	jQuery('#curso-completo').fadeIn();
}


function updatetime(){
	var t=myPlayer.currentTime();
	t=toHHMMSS(t);
	jQuery("#video_time_"+videoid).html(t);
	tiempo=setTimeout("updatetime()",500);
	if(myPlayer.ended()){
		videos_vistos++;
		// console.log(videos_vistos);
		var porciento=(videos_vistos*100)/lecciones.length;
		clearTimeout(tiempo);
		savePorcentaje(porciento);
	}
	if(myPlayer.currentTime<=0){
		OCStratLayer('o');
	}
}

function savePorcentaje(porciento){
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "encuesta_online", action: "encuesta_onlineAjax",file: "comments",save:'cursovisto',porciento:porciento,record:this_record }
	}).done(function( response ) {
		if(response=='success'){
			alert("Ha completado el curso con éxito!\n\rPuede continuar con sus labores diarias");
		}
		
	});
	return false;
}

function toHHMMSS(tim) {
    var sec_num = parseInt(tim, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    var time    = hours+':'+minutes+':'+seconds;
    return time;
}

function OCStratLayer(oc){
	if(oc=="o")
	jQuery("#strat-layer").fadeIn();
	else
	jQuery("#strat-layer").fadeOut();
}

var myPlayer = videojs('example_video_1');

{/literal}
</script>