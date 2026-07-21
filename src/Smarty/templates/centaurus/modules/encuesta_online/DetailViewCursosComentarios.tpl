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

<div class="box-body chat" id="chat-box">
	{foreach key=kc item=comment from=$COMMENTS}
		<!-- chat item -->
		<div class="item">
			<img src="{$comment.imagen}" alt="user image" class="online">
			<p class="message">
				<a href="#" class="name">
					<small class="text-muted pull-right"><i class="fa fa-clock-o"></i> {$comment.tiempo}</small>
					{$comment.usuario}
				</a>
				{$comment.comment}
			</p>
		</div><!-- /.item -->
	{/foreach}
</div>
<div class="box-footer">
	<div class="input-group">
		<form name="form_comment" id="form_comment" action="index.php" onsubmit="return saveComment();" style="330px">
			<input type="hidden" id="record" name="record" value="{$smarty.request.record}">
			<input class="form-control" placeholder="Deje su comentario..." id="typecomm" style="height: 28px;width:290px;float:left;"/>
			<div class="input-group-btn" style="float:left;">
				<button type="submit" class="btn btn-success"><i class="fa fa-plus"></i></button>
			</div>
		</form>
	</div>
</div>
						
<script>
{literal}


{/literal}
</script>