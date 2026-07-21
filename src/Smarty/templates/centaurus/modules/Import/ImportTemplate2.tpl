<div class="row">
	<div class="col-lg-12">
		<h1>{$APP.LBL_IMPORT} {$MODULE|@getTranslatedString:$MODULE}</h1>
	</div>
</div>

<form name="massimport" method="POST" id="massdelete" enctype="multipart/form-data" onsubmit="VtigerJS_DialogBox.block();" action="index.php?module=Import&action=importFileModule&return_module={$FOR_MODULE}&return_action=index">
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>
						{if $ERROR_MESS eq ''}
							{'LBL_SUCCESS_IMPORT'|@getTranslatedString:$MODULE_IMPORT}
						{else}
							{$ERROR_MESS}
						{/if}
					</h2>
				</header>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<div class="pull-right">
						<button type="button" class="btn btn-success btn-mini" onclick="location.href='index.php?module={$MODULE}&action=index'"><i class="icon-arrow-left"></i>{'LBL_BACK_BUTTON_LABEL'|@getTranslatedString:$MODULE_IMPORT}</button>
					</div>
				</header>
			</div>
		</div>
	</div>

</form>