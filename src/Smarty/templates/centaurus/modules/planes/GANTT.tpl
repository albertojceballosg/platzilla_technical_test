<script src="include/js/dhtmlxgantt/codebase/dhtmlxgantt.js" type="text/javascript" charset="utf-8"></script>
<script src="include/js/dhtmlxgantt/codebase/ext/dhtmlxgantt_tooltip.js"></script>
<link rel="stylesheet" href="include/js/dhtmlxgantt/codebase/dhtmlxgantt.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="include/js/dhtmlxgantt/codebase/skins/dhtmlxgantt_skyblue.css" type="text/css" media="screen" title="no title" charset="utf-8">


{if $GANTT_LANG neq 'en'}
<script src="include/js/dhtmlxgantt/codebase/locale/locale_{$GANTT_LANG}.js" charset="utf-8"></script> 
{/if}
<style type="text/css">
{literal}
	html, body{ height:100%; padding:0px; margin:0px;}
	.gantt_add {display:none}
	.gantt_grid_head_cell.gantt_grid_head_add.gantt_last_cell {display:none}
	.gantt_tooltip {max-width:300px}
	.drop_mnu{
		position:absolute;
		left:0px;
		top:0px;
		z-index:1000000001;
		border-left:1px solid #d3d3d3;
		border-right:1px solid #d3d3d3;
		border-bottom:1px solid #d3d3d3;
		display:none;
		padding:0px;
		text-align:left;
		background-color:#ffffcc;
		margin-top: 0px; /* added */
	}
	.gantt_grid_data{
		min-height: 400px;
	}
{/literal}
</style>

{$GANTT}