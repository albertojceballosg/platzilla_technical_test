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
<!-- libraries -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />


<link href="modules/video/html5player/video-js.css" rel="stylesheet" type="text/css">
<script src="modules/video/html5player/video.js"></script>

{include file='Buttons_List.tpl'}
									

<!-- PUBLIC CONTENTS STARTS-->
<div id="ListViewContents">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2>Videos</h2>
			</header>
			
			<div class="main-box-body clearfix">
				<ul class="widget-products ">
					{foreach item=video from=$VIDEOS}
					<li class="col-lg-12" id="record_{$video.idvideo}">
						<a class="pull-left" href="#" fn="set-play" video-file="{$video.file}" video-ext="{$video.ext}"  lv="{$video.idvideo}" style="width: 95%;">
							<span class="img">
								<i class="fa fa-youtube-play" style="font-size: 65px;"></i>
							</span>
							
							<span class="product clearfix">
								<span class="name">
									{$video.titulo} {if !$video.titulo}{$video.file}{/if} 
								</span>
								<span class="price" id="desc_{$video.idvideo}">
									{$video.description}
								</span>
								<span class="warranty">
									{if $video.titulo}Archivo: {$video.file}{/if} 
								</span>
							</span>
						</a>
						<div class="widget-todo pull-right" style="  padding-top: 33px;">
							<div class="actions">
								<a href="index.php?module=video&action=EditView&record={$video.idvideo}" class="table-link">
									<i class="fa fa-pencil"></i>
								</a>
								<a href="javascript:delvideo({$video.idvideo})" class="table-link danger">
									<i class="fa fa-trash-o"></i>
								</a>
							</div>
						</div>
					</li>
					{/foreach}
				</ul>
				
			</div>
		</div>
	</div>
</div>

<div class="md-modal md-effect-11" id="modal-11">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close" onclick="myPlayer.pause();jQuery('#modal-11').removeClass('md-show');">&times;</button>
			<h4 class="modal-title">{$VIDEOS[0].description}</h4>
		</div>
		<div class="modal-body">
			<video id="example_video_1" class="video-js vjs-default-skin"  controls preload="none" width="600" height="400">
				<source id="set-video" src="storage/video_uploads/{$VIDEOS[0].file}" type='video/{$VIDEOS[0].ext}'>
				novideo
			</video>
		</div>
	</div>
</div>
<div class="md-overlay"></div>
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>


<script type="text/javascript" language="javascript">
{literal}

jQuery("[fn='set-play']").click(function(e) {
	e.preventDefault();
	jQuery('#modal-11').addClass('md-show');
	var video_file="storage/video_uploads/"+jQuery(this).attr('video-file');
	videoid=jQuery(this).attr('lv');
	jQuery("#set-video").attr("type","video/"+jQuery(this).attr('video-ext'));
	myPlayer.src(video_file);
	myPlayer.play();
});

function openUVideo(idv){
	//var left = (screen.width/2)-(650/2);
	//var top = (screen.height/2)-(400/2);
	//window.open('https://<?=$serverPath?>modules/video/player/index.php?idv='+idv,'Video','width=650,height=400,top='+top+',left='+left);
	//window.open('index.php?module=video&action=play&idv='+idv,'Video','width=650,height=400,top='+top+',left='+left);
}
function delvideo(idv){
	if(confirm('Esta seguro que desea eliminar el video?')){
		//window.location.href='index.php?module=video&action=Delete&record='+idv;
		jQuery.ajax({
			type: "POST",
			url: "index.php",
			dataType:"json",
			data: { module: "video", action: "Delete",record: idv }
		}).done(function( response ) {
			jQuery('#record_'+response.record).slideUp("normal", function() { jQuery(this).remove(); } );
		});
	}
}
function editvideo(idv){
		window.open('index.php?module=video&action=index&edvi='+idv,'_self');
}
var myPlayer = videojs('example_video_1');
{/literal}
</script>