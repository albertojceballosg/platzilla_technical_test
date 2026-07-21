<link rel="stylesheet" type="text/css" href="include/jquery/jquery-easyui/themes/default/easyui.css" />
<link rel="stylesheet" type="text/css" href="include/jquery/jquery-easyui/themes/icon.css" />
<link rel="stylesheet" media="all" type="text/css" href="include/colorpicker/css/colorpicker.css" />
{strip}
<div class="row">
	<div class="col-lg-12">
		<div class="main-box no-header">
			<div class="main-box-body clearfix" id="">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
					</li>
					<li class="active">
						<span><a href="index.php?module=Settings&action=MenuEditor&parenttab=Settings">{$MOD.LBL_MENU_EDITOR}</a></span>
					</li>
				</ol>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box no-header">
			<div class="main-box-body clearfix">
				<div id="formid">
					<form method="post" action="index.php" onsubmit="irPaso (this); return false;" name="dlgAdminParentModules2" id="dlgAdminParentModules2">
						<input type="hidden" name="module" value="Settings" />
						<input type="hidden" name="action" value="dlgAdminParentModules" />
						<input type="hidden" name="Ajax" value="true" />
						<input type="hidden" name="moduleChild" value="" id="moduleChild" />
						<input type="hidden" name="saveTree" value="true" />
						<ul class="easyui-tree" id="tt"></ul>
						<div class="easyui-panel" style="padding:5px; width:700px; border: 0 solid #ffffff">
							<div id="mm" class="easyui-menu" style="width:120px;">
								<div onclick="edit ()" data-options="iconCls:'icon-edit'">Edit</div>
								<div onclick="append ()" data-options="iconCls:'icon-add'">Append</div>
								<div onclick="removeit ()" data-options="iconCls:'icon-remove'">Remove</div>
								<div onclick="color ()" data-options="iconCls:'icon-color'" id="icp_colorpickerfield">Color</div>
								<div class="menu-sep"></div>
							</div>
						</div>
						<div id="mensaje"></div>
						<input type="button" class="btn btn-primary" value='{$MOD.LBL_ORDER}' title='{$MOD.LBL_ORDER}' onclick="activaMensaje();irPaso(document.dlgAdminParentModules2,'dlgAdminParentModules');this.disbled='disabled';" />
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<script type="text/javascript" src="include/jquery/jquery-easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript">
{literal}
	function irPaso (form, action) {
		form.action.value = action;
		var datas = [],
			tt    = jQuery ('#tt');
		tt.tree ('getChecked', [ 'checked', 'unchecked' ]);
		var datas2 = tt.tree ('getRoots');
		datas2 = datas2[ 0 ].children;
		jQuery.each (datas2, function (idx, obj) {
			var ob = [];
			if (obj.children) {
				jQuery.each (obj.children, function (idx2, obj2) {
					var ob2 = [];
					if (obj2.children) {
						jQuery.each (obj2.children, function (idx3, obj3) {
							var ob3 = [];
							if (obj3.children) {
								jQuery.each (obj3.children, function (idx4, obj4) {
									ob3.push ({ tabid: obj4.tabid, checked: obj4.checked, text: obj3.text });
								});
							}
							ob2.push ({ id: obj3.id, tabid: obj3.tabid, checked: obj3.checked, text: obj3.text, children: ob3 });
						});
					}
					if (obj2.tabid) {
						ob.push ({ tabid: obj2.tabid, checked: obj2.checked, text: obj2.text });
					} else {
						ob.push ({ id: obj2.id, checked: obj2.checked, text: obj2.text, children: ob2 });
					}
				});
			}
			var elcolor = jQuery ('#' + obj.domId + ' span.tree-title').attr ('title-color');
			datas.push ({ id: obj.id, checked: obj.checked, text: obj.text, color: elcolor, children: ob });
		});

		jQuery.ajax ({
			type: "POST",
			url:  'index.php?' + jQuery ('#dlgAdminParentModules2').serialize (),
			data: { data: datas }
		}).done (function (response) {
			alert ("guardado");
		});
	}

	jQuery (document).ready (function () {
		jQuery ('#tt').tree ({
			url:           'index.php?module=Settings&action=SettingsAjax&file=dlgAdminParentModules&getJSON=tree',
			method:        'post',
			dnd:           true,
			animate:       true,
			checkbox:      true,
			onLoadSuccess: function (node, data) {
				if (data[ 0 ].children) {
					jQuery.each (data[ 0 ].children, function (idx, menu) {
						if (menu.color) {
							jQuery ('#' + menu.domId + ' span.tree-title').css ('backgroundColor', menu.color);
						}
					});
				}
			},
			onDblClick:    function (node) {
				jQuery (this).tree ('beginEdit', node.target);
			},
			onContextMenu: function (e, node) {
				e.preventDefault ();
				jQuery (this).tree ('select', node.target);
				jQuery ('#mm').menu ('show', {
					left: e.pageX,
					top:  e.pageY
				});
			}
		});
	});

	function append () {
		var t = jQuery ('#tt');
		var node = t.tree ('getSelected');
		t.tree ('append', {
			parent: (node ? node.target : null),
			data:   [{
				text: 'Nuevo Parenttab'
			}]
		});
	}

	function removeit () {
		var tt = jQuery ('#tt'),
			node = tt.tree ('getSelected');
		tt.tree ('remove', node.target);
	}

	function edit () {
		var tt = jQuery ('#tt'),
			node = tt.tree ('getSelected');
		tt.tree ('beginEdit', node.target);
	}

	var nodetarget = '';
	function color () {
		var node = jQuery ('#tt').tree ('getSelected');
		nodetarget = node.target.id;
		jQuery ('#icp_colorpickerfield').ColorPicker ({
			color:    node.color ? node.color : '#',
			onChange: function (hsb, hex) {
				var title = jQuery ('#' + nodetarget + ' span.tree-title');
				title.css ('backgroundColor', '#' + hex);
				title.attr ('title-color', '#' + hex);
			}
		});
	}
{/literal}
</script>
{/strip}