<?php

/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

require_once('include/utils/utils.php');

global $theme,$current_user;
$theme_path="themes/".$theme."/";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
	<title><?php echo $mod_strings['LBL_EMAIL_TEMPLATES_LIST']; ?></title>
	<link type="text/css" rel="stylesheet" href="<?php echo $theme_path ?>/style.css"/>

	<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_path ?>/css/bootstrap/bootstrap.min.css" />

	<!-- libraries -->
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_path ?>/css/libs/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_path ?>/css/libs/nanoscroller.css" />

	<!-- global styles -->
	<link rel="stylesheet" type="text/css" href="<?php echo $theme_path ?>/css/compiled/theme_styles.css" />

	<!-- this page specific styles -->
	<link rel="stylesheet" href="<?php echo $theme_path ?>/css/libs/select2.css" type="text/css" />

	<!-- google font libraries -->
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

</head>
<body>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">

			<header class="main-box-header clearfix">
				<h1 class="">
					<?php echo $mod_strings['LBL_EMAIL_TEMPLATES']; ?>
				</h1>
				<div class="main-box-body clearfix">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<tbody>
								<tr>
									<td><?php echo $mod_strings['LBL_TEMPLATE_NAME']; ?></td>
									<td><?php echo $mod_strings['LBL_DESCRIPTION']; ?></td>
								</tr>
								<?php
								   $sql = "SELECT * FROM `vtiger_emailmanager_template` WHERE privado = 0 and eventid = 0 ";
								   $result = $adb->pquery($sql, array());
								   $temprow = $adb->fetch_array($result);

									$cnt=1;
									$roundcube = 0;
									if (isset($_REQUEST['roundcube']) && $_REQUEST['roundcube'] == 'true')
									$roundcube = 1;

									require_once('include/utils/UserInfoUtil.php');
									$local_user = clone $current_user;
									require('user_privileges/user_privileges.php');
									do
									{
										$templatename = $temprow["subject"];
										if($is_admin == false)
										{
											$folderName = $temprow['foldername'];
											if($folderName != 'Personal')
											{

												echo "
													<tr>
														<td style='font-size:75%'><a href='javascript:submittemplate(".$temprow['templateid'].", $roundcube);'>".$temprow["subject"]."</a></td>
														<td style='font-size:75%'>".$temprow["subject"]."</td>
													</tr>
												";

											}
										}
										else
										{
											echo "
													<tr>
														<td style='font-size:75%'><a href='javascript:submittemplate(".$temprow['templateid'].", $roundcube);'>".$temprow["subject"]."</a></td>
														<td style='font-size:75%'>".$temprow["subject"]."</td>
													</tr>
												";

										}
									        $cnt++;

									}while($temprow = $adb->fetch_array($result));
									?>
							</tbody>

						</table>
					</div>
				</div>
			</header>
		</div>
	</div>
</div>

</body>
<script>
function submittemplate(templateid, roundcube)
{
	if (roundcube == 1)
		window.document.location.href = 'index.php?module=Users&action=UsersAjax&file=TemplateMergeRC&templateid='+templateid;
	else
		window.document.location.href = 'index.php?module=Users&action=UsersAjax&file=TemplateMerge&templateid='+templateid;
}
</script>
</html>
