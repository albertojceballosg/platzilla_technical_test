<!-- 
	Template: wizardPlataforma.tpl
	Objetivo: Presentar el dialogo donde se indica el nombre codigo de la plataforma
	Fecha: 2015-04-10
	Desarrollador: Mario Eduardo Villa (MEV)
	
	-->

<link rel="stylesheet" type="text/css" href="include/jquery/jquery-easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="include/jquery/jquery-easyui/themes/icon.css">

<script type="text/javascript" src="include/jquery/jquery-easyui/jquery.easyui.min.js"></script>

<link rel="stylesheet" media="all" type="text/css" href="include/colorpicker/css/colorpicker.css">
<script language="JavaScript" type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>

<script>
	function irPaso(form,action)
	{ldelim}
		form.action.value = action;
			var datas=[];
			jQuery('#tt').tree('getChecked',['checked','unchecked']);
			var datas2=jQuery('#tt').tree('getRoots');
			datas2=datas2[0].children;
			jQuery.each(datas2,function(idx,obj) {ldelim}
				var ob=[];
				if(obj.children)
				jQuery.each(obj.children,function(idx2,obj2) {ldelim}
					var ob2=[];
					if(obj2.children)
					jQuery.each(obj2.children,function(idx3,obj3) {ldelim}
						//console.log(obj3);


						var ob3=[];
						if(obj3.children)
						jQuery.each(obj3.children,function(idx4,obj4) {ldelim}
							//console.log(obj3);
							ob3.push({ldelim}tabid:obj4.tabid,checked:obj4.checked,text:obj3.text{rdelim});
						{rdelim});




						ob2.push({ldelim}id:obj3.id,tabid:obj3.tabid,checked:obj3.checked,text:obj3.text,children:ob3{rdelim});
					{rdelim});
					if(obj2.tabid)
						ob.push({ldelim}tabid:obj2.tabid,checked:obj2.checked,text:obj2.text{rdelim});
					else
						ob.push({ldelim}id:obj2.id,checked:obj2.checked,text:obj2.text,children:ob2{rdelim});
						
				{rdelim});
				var elcolor=jQuery('#'+obj.domId+' span.tree-title').attr('title-color');
				datas.push({ldelim}id:obj.id,checked:obj.checked,text:obj.text,color:elcolor,children:ob{rdelim});
				
			{rdelim});
			
			jQuery.ajax({ldelim}
				type: "POST",
				url: 'index.php?' + jQuery('#dlgAdminParentModules2').serialize(),
				data: {ldelim}data:datas{rdelim}
			{rdelim}).done(function( response ) {ldelim}
				jQuery('#texto{$ID_DLG_ADMIN_PARENT_MODULES}').html(response);
				//OpenClosecortina();window.location.reload(true);
				// Evaluate all the script tags in the response text.
				var scriptTags = $('texto{$ID_DLG_ADMIN_PARENT_MODULES}').getElementsByTagName("script");
				for(var i = 0; i< scriptTags.length; i++){ldelim}
					var scriptTag = scriptTags[i];
					var script = document.createElement("script");
					script.type = "text/javascript";
					var head = document.getElementsByTagName("head")[0];
					if (scriptTag.src == '') {ldelim}
						script.appendChild(document.createTextNode(scriptTag.innerHTML));//txt is the code
						head.appendChild(script);
					{rdelim}
				{rdelim}
			{rdelim});
			
	{rdelim}
	var treedata={$PARENT_MODULES};
	J(document).ready(function() {ldelim}
		
		jQuery('#tt').tree({ldelim}
			url:'index.php?module=Settings&action=SettingsAjax&file=dlgAdminParentModules&getJSON=tree',
			method: 'post',
			dnd:true,
			checkbox:true,
			//onlyLeafCheck:true,
			onDblClick: function(node){ldelim}
				jQuery(this).tree('beginEdit',node.target);
			{rdelim},
			onLoadSuccess: function(node,data){ldelim}
				jQuery(this).tree('getChecked',['checked','unchecked']);
				jQuery.each(data[0].children,function(idx,menu) {ldelim}
					//console.log(menu);
					if(menu.color){ldelim}
						jQuery('#'+menu.domId+' span.tree-title').css('backgroundColor', menu.color);
						jQuery('#'+menu.domId+' span.tree-title').attr('title-color', menu.color);
					{rdelim}
				{rdelim});
			{rdelim},
			onContextMenu: function(e,node){ldelim}
                    e.preventDefault();
                    jQuery(this).tree('select',node.target);
                    jQuery('#mm').menu('show',{ldelim}
                        left: e.pageX,
                        top: e.pageY
                    {rdelim});
		   {rdelim}
		{rdelim});
		
		
	{rdelim});

{literal}	

function append(){
	var t = jQuery('#tt');
	var node = t.tree('getSelected');
	t.tree('append', {
		parent: (node?node.target:null),
		data: [{
			text: 'Nuevo Parenttab'
		}]
	});
}

function removeit(){
	var node = jQuery('#tt').tree('getSelected');
	jQuery('#tt').tree('remove', node.target);
}
function edit(){
	var node = jQuery('#tt').tree('getSelected');
	jQuery('#tt').tree('beginEdit',node.target);
}
var nodetarget='';
function color(){
	var node = jQuery('#tt').tree('getSelected');
	nodetarget=node.target.id;
	jQuery('#icp_colorpickerfield').ColorPicker({
		color:  node.color?node.color:'#',
		onChange: function (hsb, hex, rgb) {
			jQuery('#'+nodetarget+' span.tree-title').css('backgroundColor', '#' + hex);
			jQuery('#'+nodetarget+' span.tree-title').attr('title-color', '#' + hex);
		}
	});

	//jQuery('#tt').tree('beginEdit',node.target);
}

{/literal}	

</script>


<div id="formid">

<form method="post" action="index.php" onsubmit="irPaso(this); return false;" name="dlgAdminParentModules2" id="dlgAdminParentModules2">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" value="dlgAdminParentModules" />
<input type="hidden" name="Ajax" value="true" />
<input type="hidden" name="moduleChild" value="" id="moduleChild"/>
<input type="hidden" name="saveTree" value="true" />
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small" id="proTab">

<tr>
	<td class="detailedViewHeader" colspan="2">
		<b>{$MOD.LBL_ORDER_PARENTTABS}</b>
	</td>
</tr>

<tr class="moduleChild" >
	<td class="settingsTabList" style="padding:5px;">
		
		<div  style="padding:5px;width:700px;">
			<ul id="tt">

			</ul>
			<div id="mm" class="easyui-menu" style="width:120px;">
				<div onclick="edit()" data-options="iconCls:'icon-edit'">Edit</div>
				<div onclick="append()" data-options="iconCls:'icon-add'">Append</div>
				<div onclick="removeit()" data-options="iconCls:'icon-remove'">Remove</div>
				<div onclick="color()" data-options="iconCls:'icon-color'" id="icp_colorpickerfield">Color</div>
				<div class="menu-sep"></div>
			</div>
		</div>
		
		
		
	</td>
</tr>


</table>

<div id="mensaje"></div>
<input type="button" class="crmbutton small edit" value='{$MOD.LBL_ORDER}' title='{$MOD.LBL_ORDER}' onclick="activaMensaje();irPaso(document.dlgAdminParentModules2,'dlgAdminParentModules');this.disbled='disabled';"/>
	