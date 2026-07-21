<div class="row">
	<div class="col-lg-12">
		<h1>{'LBL_IMPORT'|@getTranslatedString:$MODULE} {$FOR_MODULE|@getTranslatedString:$FOR_MODULE}</h1>
	</div>
</div>

<form name="massimport" method="POST" id="massdelete" enctype="multipart/form-data" onsubmit="VtigerJS_DialogBox.block();" action="index.php?module=Import&action=importFileModule&return_module={$FOR_MODULE}&return_action=index">
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>{$MOD.LBL_ROLE_DESCRIPTION}</h2>
				</header>
				<header class="main-box-header clearfix">
					<div class="col-lg-6">
					{*
					{if $FOR_MODULE eq 'proyectos'}
						<div class="form-group">
							<div class="detailedViewTextBox">

								<label for="has_download"><a class="link" href="#"
								onclick="location.href='index.php?module=Import&action=ImportAjax&file=ImportTemplate&activemod={$FOR_MODULE}&typeImport=standar'">
								{'LBL_DOWNLOAD_TEMP_STANDAR'|@getTranslatedString:$MODULE}</a></label>
							</div>
						</div>
						<div class="form-group">
							<div class="detailedViewTextBox">
								<label for="has_download"><a class="link" href="#"
								onclick="location.href='index.php?module=Import&action=ImportAjax&file=ImportTemplate&activemod={$FOR_MODULE}&typeImport=lssi'">
								{'LBL_DOWNLOAD_TEMPLATE_LSSI'|@getTranslatedString:$MODULE}</a></label>
							</div>
						</div>
					{else}
						<div class="form-group">
							<div class="detailedViewTextBox">
								<label for="has_download"><a class="link" href="#"
								onclick="location.href='index.php?module=Import&action=ImportAjax&file=ImportTemplate&activemod={$FOR_MODULE}&typeImport=-'">
								{'LBL_DOWNLOAD_TEMPLATE_STANDAR'|@getTranslatedString:$MODULE}</a></label>
							</div>
						</div>
					{/if}
					*}
						<div class="form-group">
							<label for="import_file">{$MOD.LBL_IMPORT_STEP_1_DESCRIPTION}</label>
							<input type="file" name="import_file" id="import_file">
							<!--span class="help-block">{'LBL_IMPORT_SUPPORTED_FILE_TYPES'|@getTranslatedString:$MODULE}</span-->
						</div>
					</div>
				</header>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<div class="pull-right">
						<button type="button" class="btn btn-warning btn-mini" onclick="location.href='index.php?module={$FOR_MODULE}&action=index'"><i class="icon-arrow-left"></i>{'LBL_CANCEL_BUTTON_LABEL'|@getTranslatedString:$MODULE_IMPORT}</button>
						<button type="button" class="btn btn-success btn-mini" onclick="return validateFilePath();">{'LBL_NEXT_BUTTON_LABEL'|@getTranslatedString:$MODULE_IMPORT}<i class="icon-arrow-right"></i></button>
					</div>
				</header>
			</div>
		</div>
	</div>

</form>

<script>
{literal}

function uploadFilter(elementId, allowedExtensions) {
	var obj = jQuery('#'+elementId);
	if(obj) {
		var filePath = obj.val();
		var fileParts = filePath.toLowerCase().split('.');
		var fileType = fileParts[fileParts.length-1];
		var validExtensions = allowedExtensions.toLowerCase().split('|');

		if(validExtensions.indexOf(fileType) < 0) {
			alert(alert_arr.PLS_SELECT_VALID_FILE+' '+validExtensions);
			obj.focus();
			return false;
		}
	}
	return true;
}


function validateFilePath() {
	var filePath = jQuery('#import_file').val();
	if(jQuery.trim(filePath) == '') {
		alert('Import File '+alert_arr.CANNOT_BE_EMPTY)
		jQuery('#import_file').focus();
		return false;
	}
	if(!uploadFilter("import_file", "xls|xlsx|csv")) {
		return false;
	}else{
 		var theForm = document.forms['massimport'];
     	if (!theForm) {
         	theForm = document.massimport;
     	}
     	theForm.submit();
	}
	return true;
}




{/literal}
</script>