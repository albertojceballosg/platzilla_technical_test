<style type="text/css">
	{literal}
	ul {
		color: black;
	}
	.drag_Element {
		position:      relative;
		left:          0;
		top:           0;
		padding-left:  5px;
		padding-right: 5px;
		border:        5px dashed #CCCCCC;
		visibility:    hidden;
	}
	#Drag_content {
		position:         absolute;
		left:             0;
		top:              0;
		padding-left:     5px;
		padding-right:    5px;
		background-color: #000066;
		color:            #FFFFFF;
		border:           1px solid #CCCCCC;
		font-weight:      bold;
		display:          none;
	}
	.badge a, .badge a:hover, .badge a:visited {
		color: #ffffff;
	}
	.table {
		margin-bottom: 0;
	}
	{/literal}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width: 30px; padding: 0;">
						<i class="fa fa-sort-amount-asc green-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
		<ol class="breadcrumb">
						<li>
							<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
			</li>
						<li class="active">ARBOL DE ROLES Y JERARQUÍA</li>
		</ol>
				</td>
			</tr>
			<tr>
				<td class="small" valign="top">{$MOD.LBL_ROLE_DESCRIPTION}</td>
			</tr>
		</tbody>
	</table>
	{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
		<div class="row">
			<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
				<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
	{/if}
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
				<div id="nestable-menu"></div>
				<div class="row cf nestable-lists">
					<div class="col-md-12" id='RoleTreeFull' onMouseMove="displayCoords (event)">
{include file='RoleTree.tpl'}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<div id="Drag_content"></div>
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript">
{literal}
	function displayCoords (event) {
		var move_Element = document.getElementById ('Drag_content').style;
		if (!event) {
			move_Element.left = e.pageX + 'px';
			move_Element.top = e.pageY + 10 + 'px';
		}
		else {
			move_Element.left = event.clientX + 'px';
			move_Element.top = event.clientY + 10 + 'px';
		}
	}

	function fnRevert (e) {
		if (e.button == 2) {
			document.getElementById ('Drag_content').style.display = 'none';
			hideAll = false;
			parentId = 'Head';
			parentName = 'DEPARTMENTS';
			childId = 'NULL';
			childName = 'NULL';
		}
	}

	var hideAll = false;
	var parentId = '';
	var parentName = '';
	var childId = 'NULL';
	var childName = 'NULL';

	function get_parent_ID (obj, currObj) {
		var leftSide = findPosX (obj);
		var topSide = findPosY (obj);
		var move_Element = document.getElementById ('Drag_content');
		childName = document.getElementById (currObj).innerHTML;
		childId = currObj;
		move_Element.innerHTML = childName;
		move_Element.style.left = leftSide + 15 + 'px';
		move_Element.style.top = topSide + 15 + 'px';
		move_Element.style.display = 'block';
		hideAll = true;
	}

	function put_child_ID (currObj) {
		var move_Element = $ ('Drag_content');
		parentName = $ (currObj).innerHTML;
		parentId = currObj;
		move_Element.style.display = 'none';
		hideAll = false;
		if (childId == 'NULL') {
			parentId = parentId.replace (/user_/gi, '');
			window.location.href = 'index.php?module=Settings&action=RoleDetailView&parenttab=Settings&roleid=' + parentId;
		}
		else {
			childId = childId.replace (/user_/gi, '');
			parentId = parentId.replace (/user_/gi, '');
			new Ajax.Request (
					'index.php',
					{
						queue:      { position: 'end', scope: 'command' },
						method:     'post',
						postBody:   'module=Users&action=UsersAjax&file=RoleDragDrop&ajax=true&parentId=' + parentId + '&childId=' + childId,
						onComplete: function (response) {
							if (response.responseText != alert_arr.ROLE_DRAG_ERR_MSG) {
								$ ('RoleTreeFull').innerHTML = response.responseText;
								hideAll = false;
								parentId = '';
								parentName = '';
								childId = 'NULL';
								childName = 'NULL';
							}
							else {
								alert (response.responseText);
							}
						}
					}
			);
		}
	}

	function fnVisible (Obj) {
		if (!hideAll) {
			document.getElementById (Obj).style.visibility = 'visible';
		}
	}

	function fnInVisible (Obj) {
		document.getElementById (Obj).style.visibility = 'hidden';
	}

	function showhide (argg, imgId) {
		var harray = argg.split (',');
		var harrlen = harray.length;
		var i;
		for (i = 0; i < harrlen; i++) {
			var x = document.getElementById (harray[ i ]).style;
			if (x.display == 'none') {
				x.display = 'block';
				document.getElementById (imgId).src = {/literal}'{'minus.gif'|@vtiger_imageurl:$THEME}'{literal};
			}
			else {
				x.display = 'none';
				document.getElementById (imgId).src = {/literal}'{'plus.gif'|@vtiger_imageurl:$THEME}'{literal};
			}
		}
	}
{/literal}
</script>
