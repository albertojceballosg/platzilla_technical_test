<table  cellpadding="5" cellspacing="0" width="100%" border="0">
<tr><td colspan="7" align="right"><a href="javascript:fnDown('tabSrch');" class="hdr"><?php echo getTranslatedString('LBL_CLOSE'); ?></a></td></tr>
<tr><td width="20%">
			<?PHP echo getTranslatedString('TICKET_TITLE');?><br>
			<input name="search_title" type="text" class="inputTxt" value="">
</td></tr>
<tr><td  align="right"><input name="Search" style="width:100px;" type="submit" value="<?php echo getTranslatedString('LBL_SEARCH'); ?>" class="inputTxt" onclick="fnDown('tabSrch');this.form.module.value='Notifications';this.form.action.value='index';this.form.fun.value='search'"></td></tr>
</table> 
