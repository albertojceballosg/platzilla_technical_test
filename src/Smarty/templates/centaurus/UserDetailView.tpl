
<div class="md-modal md-effect-1" id="modal-1">
<div class="md-content">
<div class="modal-header">
<button class="md-close close">×</button>
<h4 class="modal-title">{$UMOD.LBL_CHANGE_PASSWORD}</h4>
</div>
<div class="modal-body">

<form name="ChangePassword" onsubmit="return validatePass();" method="POST">
<input name='module' type='hidden' value='Users'>
<input name='return_action' type='hidden' value='DetailView'>
<input name='changepassword' type='hidden' value='true'>
<input name='return_id' type='hidden' value='{$ID}'>
<input name='record' type='hidden' value='{$ID}'>
<input name='action' type='hidden' value='Save'>
<div class="col-lg-12">
	<div class="main-box">
		<div class="main-box-body clearfix">
			<div class="row">
			{*
				{if $IS_ADMIN neq 'true' && $IS_ROLESUP neq 'true'}
				<div class="form-group col-lg-12" id="td_productname">
					<font color="red">*</font>
					<label>{$UMOD.LBL_OLD_PASSWORD}</label>
					<input name="old_password" id="old_password" tabindex="" value="" type="password" class="form-control" size="15">
					<input name='is_admin' type='hidden' value='1'>
				</div>
				{else}
					<input name='old_password' type='hidden'><input name='is_admin' type='hidden' value='0'>
				{/if}

			*}

				<div class="form-group col-lg-12">
					<font color="red"></font>
					<label>{$UMOD.LBL_NEW_PASSWORD}</label>
					<input name="new_password" id="new_password" tabindex="" value="" type='password' class="form-control" size="15">
				</div>
				<div class="form-group col-lg-12">
					<font color="red"></font>
					<label>{$UMOD.LBL_CONFIRM_PASSWORD}</label>
					<input name="confirm_new_password" id="confirm_new_password" tabindex="" value="" type='password' class="form-control" size="15">
				</div>
			</div>
		</div>
	</div>
</div>
<button type="submit" class="btn btn-success">{$APP.LBL_SAVE_LABEL}</button>

</form>
</div>
</div>
</div>

				<div style="opacity: 1;" class="row">
						<div class="col-lg-12">

							<div class="row">
								<div class="col-lg-12">
									<ol class="breadcrumb">
										{if $CATEGORY eq 'Settings'}
											<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a></li>
											<li class="active"><a href="index.php?module=Administration&action=index&parenttab=Settings">{$MOD.LBL_USERS}</a></li>
											<li>
												<b class="small">{$MOD.LBL_USERS} "{$USERNAME}"</b>
											</li>
										{else}
											<li>{$APP.LBL_MY_PREFERENCES}</li>
										{/if}
									</ol>
									<h1>Perfil</h1>
								</div>
							</div>

							<div class="row" id="user-profile">
								<div class="col-lg-3 col-md-4 col-sm-4">
									<div class="main-box clearfix">
										<header class="main-box-header clearfix">
											<h2>{$USER.first_name} {$USER.last_name}</h2>
										</header>

										<div class="main-box-body clearfix">
											<div class="profile-status">
												<i class="fa fa-circle"></i> Online
											</div>

											<!--img src="img/samples/scarlet-159.png" alt="" class="profile-img img-responsive center-block"-->

											{if $USER.profileImage neq ''}
												<img src="{$USER.profileImage}"  class="profile-img img-responsive center-block">
											{else}
												<i class="fa fa-user"></i>
											{/if}

											<div class="profile-label">
												<span class="label label-danger">{$USER.rolename}</span>
											</div>

											<div class="profile-stars">
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star-o"></i>
												<span>{$USER.status}</span>
											</div>

											<div class="profile-since">
												Usuario desde: {$USER.date_entered}
											</div>

											<!--div class="profile-details">
												<ul class="fa-ul">
													<li><i class="fa-li fa fa-truck"></i>Orders: <span>456</span></li>
													<li><i class="fa-li fa fa-comment"></i>Posts: <span>828</span></li>
													<li><i class="fa-li fa fa-tasks"></i>Tasks done: <span>1024</span></li>
												</ul>
											</div-->

											<!--div class="profile-message-btn center-block text-center">
												<a href="#" class="btn btn-success">
													<i class="fa fa-envelope"></i>
													Send message
												</a>
											</div-->
											{if $EDIT_DUPLICATE eq 'permitted' || $ID eq $CURRENT_USERID}
											<div class="profile-since">
												<br/>
												<a href="index.php?module=Users&action=EditView&record={$ID}">
												<button type="button" class="btn btn-primary">{$APP.LBL_EDIT_BUTTON}</button>
												</a>
											</div>
											{/if}
										</div>

									</div>
								</div>

								<div class="col-lg-9 col-md-8 col-sm-8">
									<div class="main-box clearfix">
										<div class="tabs-wrapper profile-tabs">
											<ul class="nav nav-tabs">

												{foreach key=header name=blockforeach item=detail from=$BLOCKS}
													{strip}
													<li class="{if $smarty.foreach.blockforeach.iteration eq '1'}active{/if}"><a href="#tab-{$smarty.foreach.blockforeach.iteration}" data-toggle="tab">{$smarty.foreach.blockforeach.iteration}. {$header}</a></li>
													{/strip}
													{assign var=list_numbering value=$smarty.foreach.blockforeach.iteration}
												{/foreach}


												<li class=""><a href="#tab-{$list_numbering+1}" data-toggle="tab">{$list_numbering+1}. {$UMOD.LBL_MY_GROUPS}</a></li>
												{if $IS_ADMIN eq 'true'}
													<li class=""><a href="#tab-{$list_numbering+2}" data-toggle="tab">{$list_numbering+1}. {$UMOD.LBL_LOGIN_HISTORY}</a></li>
												{/if}

												<!--li class=""><a href="#tab-newsfeed" data-toggle="tab">Newsfeed</a></li>
												<li class=""><a href="#tab-activity" data-toggle="tab">Activity</a></li>
												<li class=""><a href="#tab-friends" data-toggle="tab">Friends</a></li>
												<li class=""><a href="#tab-chat" data-toggle="tab">Chat</a></li-->
											</ul>

											<div class="tab-content">

												{foreach key=header name=blockforeach item=detail from=$BLOCKS}
													{strip}
													<div class="tab-pane fade {if $smarty.foreach.blockforeach.iteration eq '1'}active in{/if}" id="tab-{$smarty.foreach.blockforeach.iteration}">
														<div class="table-responsive">
															<ul class="widget-users row">
															<h2>{$smarty.foreach.blockforeach.iteration} | {$header}</h2>
															<br><br>


															{assign var=detailD value=$detail}

															{foreach item=detail from=$detailD}
																	{foreach key=label item=data from=$detail}
																	   {assign var=keyid value=$data.ui}
																	   {assign var=keyval value=$data.value}
																	   {assign var=keytblname value=$data.tablename}
																	   {assign var=keyfldname value=$data.fldname}
																	   {assign var=keyfldid value=$data.fldid}
																	   {assign var=keyoptions value=$data.options}
																	   {assign var=keysecid value=$data.secid}
																	   {assign var=keyseclink value=$data.link}
																	   {assign var=keycursymb value=$data.cursymb}
																	   {assign var=keysalut value=$data.salut}
																	   {assign var=keycntimage value=$data.cntimage}
																	   {assign var=keyadmin value=$data.isadmin}
																	   {if $label ne ''}
																	   <li class="col-md-6">
																			<div class="details">
																				<div class="name">
																					<a href="#">{$label}</a>
																				</div>
																				<div class="time">
																					{include file="DetailViewUI.tpl"}
																				</div>
																			</div>
																		</li>
																	   {else}
																	   <li class="col-md-6"></li>
																	   {/if}
																	{/foreach}
																{/foreach}
																</ul>
														</div>
													</div>
													{assign var=list_numbering value=$smarty.foreach.blockforeach.iteration}
													{/strip}
												{/foreach}


												<div class="tab-pane fade" id="tab-{$list_numbering+1}">
														<div class="table-responsive">
															<ul class="widget-users row">
															<h2>{$list_numbering+1} | {$UMOD.LBL_MY_GROUPS}</h2>
															<br><br>
															   <li class="col-md-6">
																	<div class="img">
																		<img src="img/samples/scarlet.png" alt="">
																	</div>
																	<div class="details">
																		<div class="name">
																			<a href="#">{$list_numbering+1} | {$UMOD.LBL_MY_GROUPS}</a>
																		</div>
																		<div class="time">
																			{if $GROUP_COUNT > 0}
																				<img src="{'showDown.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_EXPAND_COLLAPSE}" title="{$APP.LBL_EXPAND_COLLAPSE}" onClick="fetchGroups_js({$ID});">
																			{else}
																				&nbsp;
																			{/if}
																		</div>
																	</div>
																</li>
															</ul>
														</div>
													</div>

													{if $IS_ADMIN eq 'true'}
													<div class="tab-pane fade" id="tab-{$list_numbering+2}">
														<div class="table-responsive">
															<ul class="widget-users row">
															<h2>{$list_numbering+2} | {$UMOD.LBL_LOGIN_HISTORY}</h2>
															<br><br>
															   <li class="col-md-6">
																	<div class="img">
																		<img src="img/samples/scarlet.png" alt="">
																	</div>
																	<div class="details">
																		<div class="name">
																			<a href="#">{$list_numbering+2} | {$UMOD.LBL_LOGIN_HISTORY}</a>
																		</div>
																		<div class="time">
																			<img src="{'showDown.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_EXPAND_COLLAPSE}" title="{$APP.LBL_EXPAND_COLLAPSE}" onClick="fetchlogin_js({$ID});">
																			<div id="login_history_cont" style="display:none;"></div>
																		</div>
																	</div>
																</li>
															</ul>
														</div>
													</div>
													{/if}












												<!--div class="tab-pane fade active in" id="tab-newsfeed">

													<div id="newsfeed">
														<div class="story">
															<div class="story-user">
																<a href="#">
																	<img src="img/samples/robert-300.jpg" alt="">
																</a>
															</div>

															<div class="story-content">
																<header class="story-header">
																	<div class="story-author">
																		<a href="#" class="story-author-link">
																			Robert Downey Jr.
																		</a>
																		posted a status update
																	</div>
																	<div class="story-time">
																		<i class="fa fa-clock-o"></i> just now
																	</div>
																</header>
																<div class="story-inner-content">
																	Now that we know who you are, I know who I am. I'm not a mistake!
																	It all makes sense! In a comic, you know how you can tell who the
																	arch-villain's going to be? He's the exact opposite of the hero.
																	And most times they're friends, like you and me! I should've known
																	way back when... You know why, David? Because of the kids.
																	They called me Mr Glass.
																</div>
																<footer class="story-footer">
																	<a href="#" class="story-comments-link">
																		<i class="fa fa-comment fa-lg"></i> 8320 Comments
																	</a>
																	<a href="#" class="story-likes-link">
																		<i class="fa fa-heart fa-lg"></i> 82k Likes
																	</a>
																</footer>
															</div>
														</div>

														<div class="story">
															<div class="story-user">
																<a href="#">
																	<img src="img/samples/angelina-300.jpg" alt="">
																</a>
															</div>

															<div class="story-content">
																<header class="story-header">
																	<div class="story-author">
																		<a href="#" class="story-author-link">
																			Angelina Jolie
																		</a>
																		checked in at <a href="#">Place du Casino</a>
																	</div>
																	<div class="story-time">
																		<i class="fa fa-clock-o"></i> 3 Minutes ago
																	</div>
																</header>
																<div class="story-inner-content">

																</div>
																<footer class="story-footer">
																	<a href="#" class="story-comments-link">
																		<i class="fa fa-comment fa-lg"></i> 23k Comments
																	</a>
																	<a href="#" class="story-likes-link">
																		<i class="fa fa-heart fa-lg"></i> 159k Likes
																	</a>
																</footer>
															</div>
														</div>

														<div class="story">
															<div class="story-user">
																<a href="#">
																	<img src="img/samples/ryan-300.jpg" alt="">
																</a>
															</div>

															<div class="story-content">
																<header class="story-header">
																	<div class="story-author">
																		<a href="#" class="story-author-link">
																			Ryan Gossling
																		</a>
																		uploaded 5 new photos to album <a href="#">Bora Bora</a>
																	</div>
																	<div class="story-time">
																		<i class="fa fa-clock-o"></i> 8 Hours ago
																	</div>
																</header>
																<div class="story-inner-content">
																	<div class="story-images clearfix">
																		<a href="img/samples/tahiti-1.jpg" class="story-image-link">
																			<img src="img/samples/tahiti-1.jpg" alt="" class="img-responsive">
																		</a>
																		<a href="img/samples/tahiti-2.jpg" class="story-image-link story-image-link-small">
																			<img src="img/samples/tahiti-2.jpg" alt="" class="img-responsive">
																		</a>
																		<a href="img/samples/tahiti-3.jpg" class="story-image-link story-image-link-small">
																			<img src="img/samples/tahiti-3.jpg" alt="" class="img-responsive">
																		</a>
																		<a href="img/samples/tahiti-3.jpg" class="story-image-link story-image-link-small">
																			<img src="img/samples/tahiti-3.jpg" alt="" class="img-responsive">
																		</a>
																		<a href="img/samples/tahiti-2.jpg" class="story-image-link story-image-link-small hidden-xs">
																			<img src="img/samples/tahiti-2.jpg" alt="" class="img-responsive">
																		</a>
																	</div>
																</div>
																<footer class="story-footer">
																	<a href="#" class="story-comments-link">
																		<i class="fa fa-comment fa-lg"></i> 46 Comments
																	</a>
																	<a href="#" class="story-likes-link">
																		<i class="fa fa-heart fa-lg"></i> 823 Likes
																	</a>
																</footer>
															</div>
														</div>
													</div>

												</div>





												<div class="tab-pane fade" id="tab-activity">

													<div class="table-responsive">
														<table class="table">
															<tbody>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-comment"></i>
																	</td>
																	<td>
																		Scarlett Johansson posted a comment in <a href="#">Avengers Initiative</a> project.
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-truck"></i>
																	</td>
																	<td>
																		Scarlett Johansson changed order status from <span class="label label-primary">Pending</span>
																		to <span class="label label-success">Completed</span>
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-check"></i>
																	</td>
																	<td>
																		Scarlett Johansson posted a comment in <a href="#">Lost in Translation opening scene</a> discussion.
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-users"></i>
																	</td>
																	<td>
																		Scarlett Johansson posted a comment in <a href="#">Avengers Initiative</a> project.
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-heart"></i>
																	</td>
																	<td>
																		Scarlett Johansson changed order status from <span class="label label-warning">On Hold</span>
																		to <span class="label label-danger">Disabled</span>
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-check"></i>
																	</td>
																	<td>
																		Scarlett Johansson posted a comment in <a href="#">Lost in Translation opening scene</a> discussion.
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-truck"></i>
																	</td>
																	<td>
																		Scarlett Johansson changed order status from <span class="label label-primary">Pending</span>
																		to <span class="label label-success">Completed</span>
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
																<tr>
																	<td class="text-center">
																		<i class="fa fa-users"></i>
																	</td>
																	<td>
																		Scarlett Johansson posted a comment in <a href="#">Avengers Initiative</a> project.
																	</td>
																	<td>
																		2014/08/08 12:08
																	</td>
																</tr>
															</tbody>
														</table>
													</div>

												</div>

												<div class="tab-pane clearfix fade" id="tab-friends">
													<ul class="widget-users row">
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/scarlet.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Scarlett Johansson</a>
																</div>
																<div class="time">
																	<i class="fa fa-clock-o"></i> Last online: 5 minutes ago
																</div>
																<div class="type">
																	<span class="label label-danger">Admin</span>
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/kunis.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Mila Kunis</a>
																</div>
																<div class="time online">
																	<i class="fa fa-check-circle"></i> Online
																</div>
																<div class="type">
																	<span class="label label-warning">Pending</span>
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/ryan.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Ryan Gossling</a>
																</div>
																<div class="time online">
																	<i class="fa fa-check-circle"></i> Online
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/robert.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Robert Downey Jr.</a>
																</div>
																<div class="time">
																	<i class="fa fa-clock-o"></i> Last online: Thursday
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/emma.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Emma Watson</a>
																</div>
																<div class="time">
																	<i class="fa fa-clock-o"></i> Last online: 1 week ago
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/george.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">George Clooney</a>
																</div>
																<div class="time">
																	<i class="fa fa-clock-o"></i> Last online: 1 month ago
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/kunis.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Mila Kunis</a>
																</div>
																<div class="time online">
																	<i class="fa fa-check-circle"></i> Online
																</div>
																<div class="type">
																	<span class="label label-warning">Pending</span>
																</div>
															</div>
														</li>
														<li class="col-md-6">
															<div class="img">
																<img src="img/samples/ryan.png" alt="">
															</div>
															<div class="details">
																<div class="name">
																	<a href="#">Ryan Gossling</a>
																</div>
																<div class="time online">
																	<i class="fa fa-check-circle"></i> Online
																</div>
															</div>
														</li>
													</ul>
													<br>
													<a href="#" class="btn btn-success pull-right">View all users</a>
												</div>

												<div class="tab-pane fade" id="tab-chat">
													<div class="conversation-wrapper">
														<div class="conversation-content">
															<div style="position: relative; overflow: hidden; width: auto; height: 340px;" class="slimScrollDiv"><div style="overflow: hidden; width: auto; height: 340px;" class="conversation-inner">

																<div class="conversation-item item-left clearfix">
																	<div class="conversation-user">
																		<img src="img/samples/ryan.png" alt="">
																	</div>
																	<div class="conversation-body">
																		<div class="name">
																			Ryan Gossling
																		</div>
																		<div class="time hidden-xs">
																			September 21, 2013 18:28
																		</div>
																		<div class="text">
																			I don't think they tried to market it to the billionaire, spelunking,
																			base-jumping crowd.
																		</div>
																	</div>
																</div>
																<div class="conversation-item item-right clearfix">
																	<div class="conversation-user">
																		<img src="img/samples/kunis.png" alt="">
																	</div>
																	<div class="conversation-body">
																		<div class="name">
																			Mila Kunis
																		</div>
																		<div class="time hidden-xs">
																			September 21, 2013 12:45
																		</div>
																		<div class="text">
																			Normally, both your asses would be dead as fucking fried chicken, but you
																			happen to pull this shit while I'm in a transitional period so I don't wanna
																			kill you, I wanna help you.
																		</div>
																	</div>
																</div>
																<div class="conversation-item item-right clearfix">
																	<div class="conversation-user">
																		<img src="img/samples/kunis.png" alt="">
																	</div>
																	<div class="conversation-body">
																		<div class="name">
																			Mila Kunis
																		</div>
																		<div class="time hidden-xs">
																			September 21, 2013 12:45
																		</div>
																		<div class="text">
																			Normally, both your asses would be dead as fucking fried chicken, but you
																			happen to pull this shit while I'm in a transitional period so I don't wanna
																			kill you, I wanna help you.
																		</div>
																	</div>
																</div>
																<div class="conversation-item item-left clearfix">
																	<div class="conversation-user">
																		<img src="img/samples/ryan.png" alt="">
																	</div>
																	<div class="conversation-body">
																		<div class="name">
																			Ryan Gossling
																		</div>
																		<div class="time hidden-xs">
																			September 21, 2013 18:28
																		</div>
																		<div class="text">
																			I don't think they tried to market it to the billionaire, spelunking,
																			base-jumping crowd.
																		</div>
																	</div>
																</div>
																<div class="conversation-item item-right clearfix">
																	<div class="conversation-user">
																		<img src="img/samples/kunis.png" alt="">
																	</div>
																	<div class="conversation-body">
																		<div class="name">
																			Mila Kunis
																		</div>
																		<div class="time hidden-xs">
																			September 21, 2013 12:45
																		</div>
																		<div class="text">
																			Normally, both your asses would be dead as fucking fried chicken, but you
																			happen to pull this shit while I'm in a transitional period so I don't wanna
																			kill you, I wanna help you.
																		</div>
																	</div>
																</div>

															</div><div style="background: rgb(0, 0, 0) none repeat scroll 0% 0%; width: 7px; position: absolute; top: 0px; opacity: 0.4; display: block; border-radius: 7px; z-index: 99; right: 1px;" class="slimScrollBar"></div><div style="width: 7px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51) none repeat scroll 0% 0%; opacity: 0.2; z-index: 90; right: 1px;" class="slimScrollRail"></div></div>
														</div>
														<div class="conversation-new-message">
															<form>
																<div class="form-group">
																	<textarea class="form-control" rows="2" placeholder="Enter your message..."></textarea>
																</div>

																<div class="clearfix">
																	<button type="submit" class="btn btn-success pull-right">Send message</button>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
										</div-->

									</div>
								</div>
							</div>

						</div>
					</div>









































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
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/ColorPicker2.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<script src="include/scriptaculous/scriptaculous.js" type="text/javascript"></script>
<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<span id="crmspanid" style="display:none;position:absolute;"  onmouseover="show('crmspanid');">
   <a class="link"  align="right" href="javascript:;">{$APP.LBL_EDIT_BUTTON}</a>
</span>




	</div>
</div>


{if $IS_ADMIN eq 'true'}
{$DLG_SUPPORT_PERMISSIONS}
{/if}
<br>
{$JAVASCRIPT}
<div id="tempdiv" style="display:block;position:absolute;left:350px;top:200px;"></div>
<!-- added for validation -->
<script language="javascript">
  var fieldname = new Array({$VALIDATION_DATA_FIELDNAME});
  var fieldlabel = new Array({$VALIDATION_DATA_FIELDLABEL});
  var fielddatatype = new Array({$VALIDATION_DATA_FIELDDATATYPE});
function ShowHidefn(divid)
{ldelim}
	if($(divid).style.display != 'none')
		Effect.Fade(divid);
	else
		Effect.Appear(divid);
{rdelim}
{literal}
function fetchlogin_js(id)
{
	if($('login_history_cont').style.display != 'none')
		Effect.Fade('login_history_cont');
	else
		fetchLoginHistory(id);

}
function fetchLoginHistory(id)
{
        $("status").style.display="inline";
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=Users&action=UsersAjax&file=ShowHistory&ajax=true&record='+id,
                        onComplete: function(response) {
                                $("status").style.display="none";
                                $("login_history_cont").innerHTML= response.responseText;
				Effect.Appear('login_history_cont');
                        }
                }
        );

}
function fetchGroups_js(id)
{
	if(('user_group_cont').style.display != 'none')
		Effect.Fade('user_group_cont');
	else
		fetchUserGroups(id);
}
function fetchUserGroups(id)
{
        $("status").style.display="inline";
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=Users&action=UsersAjax&file=UserGroups&ajax=true&record='+id,
                        onComplete: function(response) {
                                $("status").style.display="none";
                                $("user_group_cont").innerHTML= response.responseText;
				Effect.Appear('user_group_cont');
                        }
                }
        );

}

function showAuditTrail()
{
	var userid =  document.getElementById('userid').value;
	window.open("index.php?module=Settings&action=SettingsAjax&file=ShowAuditTrail&userid="+userid,"","width=650,height=800,resizable=0,scrollbars=1,left=100");
}

function deleteUser(userid)
{
        $("status").style.display="inline";
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'action=UsersAjax&file=UserDeleteStep1&return_action=ListView&return_module=Users&module=Users&parenttab=Settings&record='+userid,
                        onComplete: function(response) {
                                $("status").style.display="none";
                                $("tempdiv").innerHTML= response.responseText;
                        }
                }
        );
}
function transferUser(del_userid)
{
        $("status").style.display="inline";
        $("DeleteLay").style.display="none";
        var trans_userid=$('transfer_user_id').options[$('transfer_user_id').options.selectedIndex].value;
	window.document.location.href = 'index.php?module=Users&action=DeleteUser&ajax_delete=false&delete_user_id='+del_userid+'&transfer_user_id='+trans_userid;
}
{/literal}
</script>
<script>
function getListViewEntries_js(module,url)
{ldelim}
	$("status").style.display="inline";
        new Ajax.Request(
        	'index.php',
                {ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                	method: 'post',
                        postBody:"module="+module+"&action="+module+"Ajax&file=ShowHistory&record={$ID}&ajax=true&"+url,
			onComplete: function(response) {ldelim}
                        	$("status").style.display="none";
                                $("login_history_cont").innerHTML= response.responseText;
                  	{rdelim}
                {rdelim}
        );
{rdelim}

function validatePass() {ldelim}
	if (jQuery('#new_password').val() == '') {ldelim}
		alert("{$UMOD.ERR_ENTER_NEW_PASSWORD}");
		return false;
	{rdelim}
	if (jQuery('#confirm_new_password').val() == '') {ldelim}
		alert("{$UMOD.ERR_ENTER_CONFIRMATION_PASSWORD}");
		return false;
	{rdelim}
	if (jQuery('#new_password').val() != jQuery('#confirm_new_password').val()) {ldelim}
		alert("{$UMOD.ERR_REENTER_PASSWORDS}");
		return false;
	{rdelim}
	return true;
{rdelim}
</script>
