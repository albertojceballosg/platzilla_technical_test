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

{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * Edited by Timemanagement.
   * Developer EV - 2015.05.26
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>{$MODULE_NAME|@getTranslatedString:$MODULE_NAME} - {$USER} - {$APP.LBL_BROWSER_TITLE}</title>
	
	<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/bootstrap/bootstrap.min.css" />
	
	<!-- RTL support - for demo only -->
	<script src="themes/{$THEME}/js/demo-rtl.js"></script>
	<!-- 
	If you need RTL support just include here RTL CSS file <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-rtl.min.css" />
	And add "rtl" class to <body> element - e.g. <body class="rtl"> 
	-->
	
	<!-- libraries -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nifty-component.css"  />

	<!-- global styles -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_styles.css" />
		

	<!-- this page specific styles -->
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.css" type="text/css" />
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.print.css" type="text/css" media="print" />
    <link rel="stylesheet" href="themes/{$THEME}/css/compiled/calendar.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/morris.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/jquery-jvectormap-1.2.2.css" type="text/css" />
	
	<!-- Favicon -->
	<link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
<link rel="manifest" href="favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">

	<!-- google font libraries -->
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

	<!--[if lt IE 9]>
		<script src="themes/{$THEME}/js/html5shiv.js"></script>
		<script src="themes/{$THEME}/js/respond.min.js"></script>
	<![endif]-->
	
	<!-- global scripts -->
	
	<script src="themes/{$THEME}/js/jquery.js"></script>
	<script src="themes/{$THEME}/js/bootstrap.js"></script>
	<script src="themes/{$THEME}/js/jquery.nanoscroller.min.js"></script>	
	
	<!-- Scripts -->
	<!-- header-vtiger crm name & RSS -->
	<script language="JavaScript" type="text/javascript" src="include/js/json.js"></script>
	<script language="JavaScript" type="text/javascript" src="include/js/general.js?v={$VERSION}"></script>
	<!-- vtlib customization: Javascript hook -->
	<script language="JavaScript" type="text/javascript" src="include/js/vtlib.js?v={$VERSION}"></script>
	<!-- END -->
	<script language="JavaScript" type="text/javascript" id="_current_language_" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
	<script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.compatible.js"></script>

<!-- libraries -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />


<link href="modules/video/html5player/video-js.css" rel="stylesheet" type="text/css">
<script src="modules/video/html5player/video.js"></script>
</head>

<body class="pace-done theme-whbl">
<div id="theme-wrapper">
	<div id="page-wrapper" class="container{$NAV_SMALL}">
		<div class="row">
			<div id="content-wrapper">
				<div class="row">
					<div class="col-lg-12">
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
												<a href="#" fn="set-value" video-file="{$video.file}" video-desc="{$video.titulo}{if !$video.titulo}{$video.description}{/if}" video-ext="{$video.ext}"  lv="{$video.idvideo}" style="width: 95%;">
													<span class="img">
														<i class="fa fa-youtube-play" style="font-size: 65px;"></i>
													</span>
													
													<span class="product clearfix">
														<span class="name">
															{$video.titulo} {if !$video.titulo}{$video.file}{/if} 
														</span>
														<span class="warranty">
															{if $video.titulo}Archivo: {$video.file}{/if} 
														</span>
													</span>
												</a>
											</li>
											{/foreach}
										</ul>
										
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

<div class="md-overlay"></div>
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>


<script type="text/javascript" language="javascript">
{literal}

function setOpenervalue(desc,idvideo){
	window.opener.document.EditView.videoid_display.value=desc;
	window.opener.document.EditView.videoid.value=idvideo;
	window.close();
}
function openUVideo(idv){
	var left = (screen.width/2)-(650/2);
	var top = (screen.height/2)-(400/2);
	//window.open('https://<?=$serverPath?>modules/video/player/index.php?idv='+idv,'Video','width=650,height=400,top='+top+',left='+left);
	window.open('index.php?module=video&action=play&idv='+idv,'Video','width=650,height=400,top='+top+',left='+left);
}


function add_data_to_relatedlist(entity_id,recordid,mod, popupmode, callback) {
	var return_module = document.getElementById('return_module').value;
	if(popupmode == 'ajax') {
		VtigerJS_DialogBox.block();
		new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
             method: 'post',
             postBody: "module="+return_module+"&action="+return_module+"Ajax&file=updateRelations&destination_module="+mod+"&entityid="+entity_id+"&parentid="+recordid+"&mode=Ajax",
             onComplete: function(response) {
					VtigerJS_DialogBox.unblock();
					var res = JSON.parse(response.responseText);
					if(typeof callback == 'function') {
						callback(res);
					}
                }
			}
		);
		return false;
	} else {
		
        opener.document.location.href="index.php?module=Emails&action=updateRelations&destination_module="+mod+"&entityid="+entity_id+"&parentid="+recordid+"&return_module=Emails&return_action=&parenttab=Sales";
		window.close();
		
	}
}


var image_pth = '';

function showAllRecords()
{
        modname = document.getElementById("relmod").name;
        idname= document.getElementById("relrecord_id").name;
        var locate = location.href;
        url_arr = locate.split("?");
        emp_url = url_arr[1].split("&");
        for(i=0;i< emp_url.length;i++)
        {
                if(emp_url[i] != '')
                {
                        split_value = emp_url[i].split("=");
                        if(split_value[0] == modname || split_value[0] == idname )
                                emp_url[i]='';
                        else if(split_value[0] == "fromPotential" || split_value[0] == "acc_id")
                                emp_url[i]='';

                }
        }
        correctUrl =emp_url.join("&");
        Url = "index.php?"+correctUrl;
        return Url;
}

//function added to get all the records when parent record doesn't relate with the selection module records while opening/loading popup.
function redirectWhenNoRelatedRecordsFound()
{
        var loadUrl = showAllRecords();
        window.location.href = loadUrl;
}

/*******************************************/

jQuery("[fn='set-value']").click(function(e) {
	e.preventDefault();
	videoid=jQuery(this).attr('lv');
	videodesc=jQuery(this).attr('video-file');
	setOpenervalue(videodesc,videoid);
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
</body>
</html>