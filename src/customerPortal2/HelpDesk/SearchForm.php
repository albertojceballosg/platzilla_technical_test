<?php
	global $default_language;
	setPortalCurrentLanguage();
	$default_language = getPortalCurrentLanguage();
	require_once("language/".$default_language.".lang.php");
?>
<input name="login_language" type="hidden" value="<?php echo $default_language;?>">
<table  cellpadding="5" cellspacing="0" width="100%" border="0">
<tr><td colspan="7" align="right"><a href="javascript:fnDown('tabSrch');" class="hdr"><?php echo getTranslatedString('LBL_CLOSE'); ?></a></td></tr>
<tr><?php /* <td width="25%">
			<?PHP echo getTranslatedString('TICKETID');?><br>
			<input name="search_ticketid" type="text" class="inputTxt" value="">
</td> */ ?><td width="20%">
			<?PHP echo getTranslatedString('TICKET_TITLE');?><br>
			<input name="search_title" type="text" class="inputTxt" value="">
</td><td width="20%">
			<?PHP echo getTranslatedString('TICKET_STATUS');?><br>
			<?php
				$status_array = getPicklist('ticketstatus');
				echo getComboList('search_ticketstatus',$status_array,' ');
			?>
</td><?php /*  <td width="10%">
			<?PHP echo getTranslatedString('TICKET_PRIORITY');?><br>
			<?php
				$priority_array = getPicklist('ticketpriorities');
				echo getComboList('search_ticketpriority',$priority_array,' ');
			?>
</td><td width="10%">
			<?PHP echo getTranslatedString('TICKET_CATEGORY');?> <br>
			<?php
				$category_array = getPicklist('ticketcategories');
				echo getComboList('search_ticketcategory',$category_array,' ');
			?>
</td> */ ?><td width="15%">
			<?PHP echo getTranslatedString('TICKET_MATCH');?> <br>
			<select name="search_match">
				<option value="all"><?php echo getTranslatedString('LBL_ALL'); ?></option>
				<option value="any"><?php echo getTranslatedString('LBL_ANY'); ?></option>
			</select>			
			</td>
			<td width="15%">
			<?PHP echo getTranslatedString('ANYO');?> <br>
			<select name="search_ticketyear">
				<option value=""><?php echo getTranslatedString('SELECCIONE'); ?></option>
			<?php
				for($i = 2010;$i < date('Y')+1;$i++) {
			?>
					<option value="<?php echo $i;?>"><?php echo $i;?></option>
			<?php
				}
			?>
			</select>			
</td>
</tr>
<tr><td colspan="2">
		&nbsp;
</td><td  align="right"><input name="Search" type="submit" value="<?php echo getTranslatedString('LBL_SEARCH'); ?>" class="inputTxt" onclick="fnDown('tabSrch');this.form.module.value='HelpDesk';this.form.action.value='index';this.form.fun.value='search'"></td></tr>
</table> 
