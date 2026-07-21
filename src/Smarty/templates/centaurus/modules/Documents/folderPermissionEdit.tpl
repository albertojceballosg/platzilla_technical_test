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
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/dropzone.css">
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/magnific-popup.css">

{*<!-- module header -->*}
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/search.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/Merge.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>

<script language="javascript" type="text/javascript">

</script>
<script language="JavaScript" type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>

<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-6 pull-left">
			<h1>Carpetas 
				<small id="path-carpetas"> <!--Secondary headline--><!--!--Secondary--></small>
			</h1>
		</div>
</div>





<form name="EditView" method="POST" action="index.php" onsubmit="VtigerJS_DialogBox.block();" role="form">
    <input type="hidden" name="module" value="{$MODULE}">
    <input type="hidden" name="action" value="FolderPermissions">
    <input type="hidden" name="folderid" value="{$FOLDER.folderid}">
    <input type="hidden" name="profileid" value="{$PERMISOS.profileid}">


<div class="row">

<div class="col-lg-12 col-md-12 col-sm-12">
      <div class="main-box clearfix project-box emerald-box">
        <div class="main-box-body clearfix">
          <div class="project-box-header emerald-bg">
            <div class="name">
              <a href="#">
                {$FOLDER.foldername} 
              </a>
            </div>
          </div>

          <div class="table-responsive" style="width:90%;margin:0 auto 0 auto;">

            <table class="table table-striped table-hover" width="90%" align="center">


	 		  <tr>
                <td colspan="3">{$FOLDER.description} </td>
              </tr>
              
              <tr>
              <tr>
                <td>
                	<div class="checkbox-nice">
						<input type="checkbox" id="read_act" name="read_act" {if $PERMISOS.read_act eq 1}checked="checked"{/if} />
						<label for="read_act">
							{$MOD.LBL_READ_ACT_DOCUMENTS}
						</label>
					</div>
                </td>
                <td>
                	<div class="checkbox-nice">
						<input type="checkbox" id="edit_act" name="edit_act" {if $PERMISOS.edit_act eq 1}checked="checked"{/if} />
						<label for="edit_act">
							{$MOD.LBL_EDIT_ACT_DOCUMENTS}
						</label>
					</div>
                </td>
                <td>
                	<div class="checkbox-nice">
						<input type="checkbox" id="delete_act" name="delete_act" {if $PERMISOS.delete_act eq 1}checked="checked"{/if} />
						<label for="delete_act">
							{$MOD.LBL_DELETE_ACT_DOCUMENTS}
						</label>
					</div>
                </td>
              </tr>

              <tr>
                <td colspan="3">
                	<div class="checkbox-nice">
						<input type="checkbox" id="apply_subfolders" name="apply_subfolders" checked="" />
						<label for="apply_subfolders">
							{$MOD.LBL_APPY_CHANGES_DOCUMENTS}
						</label>
					</div>

                </td>
              </tr>
              
              
            </table>

          </div>
          
        
          <div class="project-box-ultrafooter clearfix text-center">
            	<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-primary" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="" >
				<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-warning" onclick="javascript:Volver();" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
          </div>
        </div>
      </div>
    </div>
</div>















</form>























<div class="md-overlay"></div><!-- the overlay element -->
<script language="JavaScript" type="text/javascript" src="modules/Documents/Documents.js"></script>
<!-- this page specific scripts -->
<script src="themes/centaurus/js/modernizr.custom.js"></script>
<script src="themes/centaurus/js/classie.js"></script>
<script src="themes/centaurus/js/modalEffects.js"></script>
<script src="themes/centaurus/js/jquery.countTo.js"></script>
<!-- this page specific scripts -->
<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/dropzone.js"></script>
<script src="themes/centaurus/js/jquery.magnific-popup.min.js"></script>

<script>

function Volver(){ldelim}
	window.location.href = 'index.php?module=Documents&action=profileToFolder&folderid={$FOLDERPADRE}';
{rdelim}

</script>