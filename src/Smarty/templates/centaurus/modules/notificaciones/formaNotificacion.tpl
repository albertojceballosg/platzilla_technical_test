<form method="POST" action="index.php" enctype="multipart/form-data" id="formanotificacion" onsubmit="return notificationFrmValidate();">
	<input type="hidden" name="TextoMensaje" id="TextoMensaje" value=""/>
	<input type="hidden" name="module" id="module" value="notificaciones">
	<input type="hidden" name="action" id="action" value="index">
	<input type="hidden" name="conversacionid" id="conversacionid" value="{$CONVERSACIONID}">
	<input type="hidden" name="Funcion" id="Funcion" value="RegistrarNotificacion">
	<input type="hidden" name="module" id="module" value="notificaciones">
	<input type="hidden" name="ticketid" id="ticketid" value="{$TICKETID}">
	<input type="hidden" name="relcrmid" id="relcrmid" value="">
	<div class="row form-group">
		<label for="exampleInpSubject" class="col-md-2">{$MOD.LBL_ACCOUNT_NAME}</label>
		<div class="col-md-10">
			{$SELECT_ACCOUNTS}
		</div>
	</div>
	<div class="row form-group">
		<label for="exampleInpSubject" class="col-md-2">{$MOD.LBL_CONTACTS}</label>
		<div class="col-md-10" id="listaContactos">
			{$LIST_CONTACTS}
		</div>
	</div>
	<div class="row form-group">
		<label for="exampleInpSubject" class="col-md-2">{$MOD.LBL_SUBJECT}</label>
		<div class="col-md-10">
			<input type="text" placeholder="Indique el asunto" id="subject" name="subject" class="form-control" value="{$SUBJECT}">
		</div>
	</div>
	{$RECORD_ASSOCIATED}
	<div class="row form-group">
		<label for="exampleInpSubject" class="col-md-2">{$MOD.LBL_DOCUMENTATION}</label>
		<div class="col-md-10">
			<button type="button" class="btn btn-primary" onclick="agregarDocumentacionNotificacion('listaArchivosDoc2');">{$MOD.LBL_ADD_DOCUMENTATION}</button>
			<table id="listaArchivosDoc2">
			<tr>
				<td>
				</td>
			</tr>
			</table>
		</div>
	</div>
	<div class="row form-group">
		<label for="exampleInpSubject" class="col-md-12">{$MOD.LBL_MESSAGE}</label>
	</div>
	<div class="row">
		<label class="visible-xs">Content:</label>
		
		<div class="col-md-12">
			<div class="main-box-body clearfix">
				<div id="alerts"></div>
				<div class="btn-toolbar editor-toolbar" data-role="editor-toolbar" data-target="#editor">
					<div class="btn-group">
						<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Font"><i class="fa fa-font"></i><b class="caret"></b></a>
						<ul class="dropdown-menu">
						</ul>
					</div>
					<div class="btn-group">
						<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Font Size"><i class="fa fa-text-height"></i>&nbsp;<b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a data-edit="fontSize 5"><font size="5">Huge</font></a></li>
							<li><a data-edit="fontSize 3"><font size="3">Normal</font></a></li>
							<li><a data-edit="fontSize 1"><font size="1">Small</font></a></li>
						</ul>
					</div>
					<div class="btn-group">
						<a class="btn btn-default" data-edit="bold" title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold"></i></a>
						<a class="btn btn-default" data-edit="italic" title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic"></i></a>
						<a class="btn btn-default" data-edit="strikethrough" title="Strikethrough"><i class="fa fa-strikethrough"></i></a>
						<a class="btn btn-default" data-edit="underline" title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline"></i></a>
					</div>
					<div class="btn-group">
						<a class="btn btn-default" data-edit="insertunorderedlist" title="Bullet list"><i class="fa fa-list-ul"></i></a>
						<a class="btn btn-default" data-edit="insertorderedlist" title="Number list"><i class="fa fa-list-ol"></i></a>
						<a class="btn btn-default" data-edit="outdent" title="Reduce indent (Shift+Tab)"><i class="fa fa-dedent"></i></a>
						<a class="btn btn-default" data-edit="indent" title="Indent (Tab)"><i class="fa fa-indent"></i></a>
					</div>
					<div class="btn-group">
						<a class="btn btn-default" data-edit="justifyleft" title="Align Left (Ctrl/Cmd+L)"><i class="fa fa-align-left"></i></a>
						<a class="btn btn-default" data-edit="justifycenter" title="Center (Ctrl/Cmd+E)"><i class="fa fa-align-center"></i></a>
						<a class="btn btn-default" data-edit="justifyright" title="Align Right (Ctrl/Cmd+R)"><i class="fa fa-align-right"></i></a>
						<a class="btn btn-default" data-edit="justifyfull" title="Justify (Ctrl/Cmd+J)"><i class="fa fa-align-justify"></i></a>
					</div>
					<div class="btn-group">
						<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Hyperlink"><i class="fa fa-link"></i></a>
						<div class="dropdown-menu input-append">
							<input class="span2" placeholder="URL" type="text" data-edit="createLink"/>
							<button class="btn" type="button">Add</button>
						</div>
						<a class="btn btn-default" data-edit="unlink" title="Remove Hyperlink"><i class="fa fa-cut"></i></a>
					</div>
					
					<div class="btn-group">
						<a class="btn btn-default" title="Insert picture (or just drag & drop)" id="pictureBtn"><i class="fa fa-picture-o"></i></a>
						<input type="file" data-role="magic-overlay" data-target="#pictureBtn" data-edit="insertImage" />
					</div>
					<div class="btn-group">
						<a class="btn btn-default" data-edit="undo" title="Undo (Ctrl/Cmd+Z)"><i class="fa fa-undo"></i></a>
						<a class="btn btn-default" data-edit="redo" title="Redo (Ctrl/Cmd+Y)"><i class="fa fa-repeat"></i></a>
					</div>
					<input type="text" data-edit="inserttext" id="voiceBtn" x-webkit-speech="">
				</div>

				<div id="editor" class="wysiwyg-editor"></div>
			</div>
		</div>
	</div>
</form>
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery.hotkeys.js"></script>
<script src="themes/{$THEME}/js/bootstrap-wysiwyg.js"></script>
<script src="modules/notificaciones/notificaciones.js"></script>
<script>
{literal}
	jQuery(document).ready(function(){
		function initToolbarBootstrapBindings() {
			var fonts = ['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 
						'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
						'Times New Roman', 'Verdana'],
				fontTarget = jQuery('[title=Font]').siblings('.dropdown-menu');
			
			jQuery.each(fonts, function (idx, fontName) {
				fontTarget.append(jQuery('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
			});
			jQuery('a[title]').tooltip({container:'body'});
			jQuery('.dropdown-menu input').click(function() {return false;})
				.change(function () {jQuery(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
				.keydown('esc', function () {this.value='';jQuery(this).change();});

			jQuery('[data-role=magic-overlay]').each(function () { 
				var overlay = jQuery(this), target = jQuery(overlay.data('target')); 
				overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
			});
			if ("onwebkitspeechchange"	in document.createElement("input")) {
				var editorOffset = jQuery('#editor').offset();
				jQuery('#voiceBtn').css('position','absolute').offset({top: editorOffset.top, left: editorOffset.left+jQuery('#editor').innerWidth()-35});
			} else {
				jQuery('#voiceBtn').hide();
			}
		};
		function showErrorAlert (reason, detail) {
			var msg='';
			if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
			else {
				console.log("error uploading file", reason, detail);
			}
			jQuery('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+ 
			 '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
		};
		
		initToolbarBootstrapBindings();	
		
		jQuery('#editor').wysiwyg({ fileUploadError: showErrorAlert} );
	});
{/literal}
</script>