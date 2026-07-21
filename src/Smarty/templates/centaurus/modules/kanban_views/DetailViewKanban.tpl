{strip}
<link rel="stylesheet" href="include/dist/jkanban.css">
<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
<style>

	#myKanban {
		overflow-x:  auto;
		padding:     15px 0;
		font-weight: 400;
		font-size:   0.75em;
        scrollbar-width: thin;
	}
    #myKanban-top {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        margin-top: 10px;
        height: 20px;
        scrollbar-width: thin;
    }
    #myKanban::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }

    #myKanban::-webkit-scrollbar-thumb {
        background: #393812;
        -webkit-border-radius: 1ex;
        -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
    }

    #myKanban::-webkit-scrollbar-corner {
        background: #000;
    }
    #myKanban-top::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }

    #myKanban-top::-webkit-scrollbar-thumb {
        background: #393812;
        -webkit-border-radius: 1ex;
        -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
    }

    #myKanban-top::-webkit-scrollbar-corner {
        background: #000;
    }
    .div1 {
        height: 20px;
    }
    .kanba-btn {
        float: right;
        z-index: 100000;
        width: 100%;
        margin-top: 0;
        padding-top: 0;
    }
    .del-kamba {
        float: right;
        cursor: pointer;
        padding: 2px !important;
        margin:  0 1px !important;
        z-index: 10000;
        color: #D8D8D8;
    }
    .change-kamba {
        float: right;
        cursor: pointer;
        padding: 2px !important;
        margin:  0 1px !important;
        z-index: 100000;
        color: #D8D8D8;
    }
    .edit-kamba {
        float: left;
        cursor: pointer;
        padding: 2px !important;
        margin:  0 1px !important;
        z-index: 100000;
        color: #D8D8D8;

    }
    #myKanban-content {
        width: 2000px;
    }
    .kanban-board {
        min-height: 610px !important;
        height: 100% !important;
    }

    .kanban-board .kanban-drag {
        height:     550px !important;
        overflow-y: auto !important;
        padding: 10px !important;
    }
    @media (min-width: 360px) {
        #myKanban {
            overflow-x:  auto;
            padding:     15px 0;
            font-weight: 200;
            font-size:   0.835em;
        }
        .kanban-board {
            min-height: 590px !important;
        }

        .kanban-board .kanban-drag {
            height:     530px !important;
            overflow-y: auto !important;
            padding: 10px !important;
        }
    }

    @media (min-width: 768px) {
        #myKanban {
            overflow-x:  auto;
            padding:     15px 0;
            font-weight: 300;
            font-size:   0.75em;
        }
        .kanban-board {
            min-height: 660px !important;
            height: 100% !important;
        }

        .kanban-board .kanban-drag {
            height:     600px !important;
            overflow-y: auto !important;
            padding: 10px !important;
        }
    }
    @media (min-width: 992px) {
        #myKanban {
            overflow-x:  auto;
            padding:     20px 0;
            font-weight: 400;
            font-size:   0.755em;
        }
        .kanban-board {
            min-height: 850px !important;
        }

        .kanban-board .kanban-drag {
            height:     790px !important;
            overflow-y: auto !important;
            padding: 10px !important;
        }
    }
    @media (min-width: 1200px) {
        #myKanban {
        overflow-x:  auto;
        padding:     15px 0;
        font-weight: 400;
        font-size:   0.875em;
        }

        .kanban-board {
            min-height: 720px !important;
            height: 100% !important;
        }

        .kanban-board .kanban-drag {
            height:     670px !important;
            overflow-y: auto !important;
            padding: 10px !important;
        }

        .kanban-item {
            height: auto !important;
            overflow-wrap: break-word;
            z-index: 100;
        }

    }
    @media (min-width: 1920px) {
        #myKanban {
            overflow-x:  auto;
            padding:     25px 0;
            font-weight: 400;
            font-size:   0.855em;
        }

        .kanban-board {
            min-height: 890px !important;
            height: 100% !important;
        }

        .kanban-board .kanban-drag {
            height:     840px !important;
            overflow-y: auto !important;
            padding: 10px !important;
        }

        .kanban-item {
            height: auto !important;
            overflow-wrap: break-word;
        }

    }
    .row-kanban {
		display: -webkit-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
		-webkit-flex-wrap: wrap;
		-ms-flex-wrap: wrap;
		flex-wrap: wrap;
		margin-right: -15px;
		margin-left: -15px;
	}

	.justify-content-center {
		-webkit-box-pack: center !important;
		-ms-flex-pack:    center !important;
		justify-content:  center !important
	}
    .calculation-data {
        margin: 4px 2px;
    }
    .calculation-data p {
        text-align: center;
        padding: 4px 0;
    }

</style>
{if !empty($RULECOLORS)}
<style>
	{foreach $RULECOLORS as $itemcolor}
	.{$FIELDNAME}{$itemcolor.pickfieldid} {
		background: {$itemcolor.backgroundcolor}{literal};
		color: #fff;
		position: relative;
		border: 1px solid {/literal}{$itemcolor.backgroundcolor}{literal};
	}
	.{/literal}{$FIELDNAME}{$itemcolor.pickfieldid}:after,.{$FIELDNAME}{$itemcolor.pickfieldid}{literal}:before {
		left: 100%;
		top: 50%;
		border: solid transparent;
		content: " ";
		height: 0;
		width: 0;
		position: absolute;
		pointer-events: none;
	}
	.{/literal}{$FIELDNAME}{$itemcolor.pickfieldid}:after {
		border-color: rgba(136, 183, 213, 0);
		border-left-color: {$itemcolor.backgroundcolor}{literal};
		border-width: 30px;
		margin-top: -30px;
	}
	.{/literal}{$FIELDNAME}{$itemcolor.pickfieldid}{literal}:before{
		border-color: rgba(194, 225, 245, 0);
		border-left-color: {/literal}{$itemcolor.backgroundcolor}{literal};
		border-width: 31px;
		margin-top: -31px;
	}
	{/literal}
	{/foreach}
    .main-box-body {
        box-shadow:    0px 0px 0px 0 #FFFFFF !important;
        background-color: #FFFFFF;
        border-radius: 0px !important;
        min-height:    1100px !important;
        margin-left:   -15px !important;
        margin-right:  -15px !important;
        margin-bottom: 0 !important;
    }
    #myKanban {
        min-height: 900px !important;
    }
</style>
{/if}
<div class="main-box-body clearfix">
    <div class="row">
    {if $REQUEST_FROM eq 'kanban'}
		<div class="col-lg-12" >
		</div>
    {elseif $REQUEST_FROM eq 'listView'}
    <div class="col-md-4">
        <div class="input-group" style="margin: 10px 0px 0 15px">
            <div class="input-group-btn">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="height: 34px;">
					<i class="fa fa-filter">&nbsp;</i></i><span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="index.php?module=Settings&action=KanbanViewEditView&parenttab=Settings">{$APP.LNK_KANBAN_CREATEVIEW}</a>
                    </li>
                    <li>
                        <a href="index.php?module=Settings&amp;action=KanbanViewEditView&amp;record={$KANBAN_VIEW}&amp;parenttab=Settings">{$APP.LNK_CV_EDIT}</a>
                    </li>
                </ul>
            </div>
            <select name="viewname" id="viewname" class="form-control" onchange="showDefaultCustomView(this,'{$MODULE}','{$CATEGORY}')" title="">
				<optgroup label="Filtros">
                    {$CUSTOMVIEW_OPTION}
				</optgroup>
                {if $KANBAN_LIST neq 'null'}
                    {assign var='fieldSelected' value=''}
                <optgroup label="Kanban">
                    {foreach $KANBAN_LIST as $kanban}
                        <option value="{$kanban.kanbanviewid}" {if $kanban.kanbanviewid eq $KANBAN_VIEW}  selected {$fieldSelected= $kanban.fieldname} {/if} fieldname="{$kanban.fieldname}">{$kanban.label}</option>
                    {/foreach}
                </optgroup>
                {/if}
            </select>
			<input type="hidden" name="modulename" id="modulename" value="{$MODULE}">
			<input type="hidden" name="fieldname" id="fieldname" value="{$fieldSelected}">
        </div>
    </div>
    {/if}
    </div>
        <div id="myKanban-top" style="display: none">
            <div class="div1"></div>
        </div>
		<div id="myKanban"></div>
</div>
<script src="include/dist/jkanban.js"></script>
<script type="text/javascript">
    {literal}
    var totalBoard = {/literal}{$RULECOLORS|@count}{literal}
    var boardWidth, myScreen = window.screen.availWidth;
    if (totalBoard > 4) {
        boardWidth = Math.floor(((myScreen * (1055 / 1280)) / 5) - 16);
    } else if(totalBoard === 4) {
        boardWidth = Math.floor(((myScreen * (1060 / 1280)) / 4) - 16);
    } else if(totalBoard === 3) {
        boardWidth = Math.floor(((myScreen * (1074 / 1280)) / 3) - 16);
    } else {
        boardWidth = Math.floor(((myScreen * (1092 / 1280)) / 2) - 16);

    }{/literal}
    stringBoradWidth = boardWidth + 'px';
{literal}
    jQuery (function () {
        jQuery('#myKanban-top').on('scroll', function (e) {
            jQuery('#myKanban').scrollLeft(jQuery('#myKanban-top').scrollLeft());
        });
        jQuery('#myKanban').on('scroll', function (e) {
            jQuery('#myKanban-top').scrollLeft(jQuery('#myKanban').scrollLeft());
        });
    });
	var KanbanTest = new jKanban ({
		element:       '#myKanban',
		gutter:        '16px',
		widthBoard:    stringBoradWidth,
		click:         function (el) {
		},
		buttonClick:   function (el, boardId) {
			// create a form to enter element
			var formItem = document.createElement ('form');
			formItem.setAttribute ("class", "itemform");
			formItem.innerHTML = '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="form-group"><button type="submit" class="btn btn-primary btn-xs pull-right">Submit</button><button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancel</button></div>'

			KanbanTest.addForm (boardId, formItem);
			formItem.addEventListener ("submit", function (e) {
				e.preventDefault ();
				var text = e.target[ 0 ].value;
				KanbanTest.addElement (boardId, {
					"title": text,
				});
				formItem.parentNode.removeChild (formItem);
			});
			document.getElementById ('CancelBtn').onclick = function () {
				formItem.parentNode.removeChild (formItem)
			}
		},
		addItemButton: false,
		boards:        [
			{/literal}
			{foreach key=keyBoard item=itemcolor from=$RULECOLORS}
			{literal}
			{
				"id":    "{/literal}{$FIELDNAME}_{$itemcolor.pickfieldid}{literal}",
				"title": "{/literal}{$itemcolor.picklabel}{literal}",
				"class": "{/literal}{$FIELDNAME}{$itemcolor.pickfieldid}{literal}",
            {/literal}
                {if $itemcolor.calculation neq NULL}
            {literal}
                "CalculationTitle": "{/literal}{$itemcolor.operation}{literal}",
                "CalculationField": "{/literal}{$itemcolor.fieldname}{literal}",
                "CalculationValue": "{/literal}{$itemcolor.calculation}{literal}",
            {/literal}
                {else}
                {literal}
                    "CalculationTitle": "",
                {/literal}
                {/if}
            {literal}
				"item":  [
					{/literal}
					{assign var='modname' value=$MODULENAME|cat:'id'}
					{assign var='fieldname' value=$FIELDNAME}
					{foreach key=keyAlert item=item from=$ITEMVIEWS}
					{assign var='title' value='<div class="kanba-btn"><a class="del-kamba" title="Eliminar expediente" onclick="deleteReg(event, this)"><i class="fa fa-trash-o"></i></a>&nbsp;<a class="change-kamba" title="Asignar expediente" onclick="changeOwner(event, this)"><i class="fa fa-refresh"></i></a>&nbsp;<a class="edit-kamba"title="Ver detalles" onclick="detailViewReg(event, this)"><i class="fa fa-eye fa-2x"></i></a></div><br/>'}
					{assign var='todo' value=''}
					{foreach key=keyItem item=itemCont from=$item}
					{if $keyItem == $fieldname}
					{if $itemCont == $itemcolor.picklabel}
					{assign var='todo' value='todo'}
					{else}
					{break}
					{/if}
					{/if}
					{if $keyItem != $fieldname}
					{assign var='title' value=$title|cat:$itemCont|cat:'</br>'}
					{/if}
					{assign var='countcalc' value=$countcalc + 1}
					{/foreach}
					{if $todo == 'todo'}
						{literal}{
						"id":      "{/literal}{$keyAlert}{literal}",
						"title":   "{/literal}{$title|replace:'"':'\''}{literal}",
						"drag":    function (el, source) {
						},
						"dragend": function (el) {
						},
						"drop":    function (el, target) {
							var tid = target.parentNode.dataset.id;
							var toBoardId = tid.split ('_');
							var lBoard = toBoardId.length;
							tid = toBoardId[ lBoard - 1 ];
							updateFieldValue (el.dataset.eid, jQuery ('#fieldname').val (), jQuery ('#modulename').val (), tid);
						},
					},
					{/literal}
					{/if}
					{/foreach}
					{literal}
				]
			},
			{/literal}
			{/foreach}
			{literal}
		]
	});
	function updateFieldValue (recordid, fieldname, tabname, valueid) {
		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=updateFieldView&recordid=' + recordid + '&valueid=' + valueid + '&fieldname=' + fieldname + '&tabname=' + tabname,
					onComplete: function (response) {
					}
				}
		);
	}

    function detailViewReg (event, element) {
        var record = jQuery (element).closest ('div.kanban-item').attr ('data-eid'),
            module = jQuery ('#modulename').val ();
        console.log (module);
       window.open ('index.php?module=' + module + '&action=DetailView&record=' + record, '_blank');
        event.stopPropagation();
        event.preventDefault();
    };
	function deleteReg (event, element) {
        var card   = jQuery(element).closest ('div.kanban-item'),
            record = card.attr ('data-eid'),
            module = jQuery ('#modulename').val (),
            arguments = {
                'module':         module,
                'action':        'Delete',
                'record':         record,
                'return_action': 'KANBAN-DELETE'
            };
        if (!confirm('¿Está seguro que desea eliminar este expediente?')) {
            return
        }

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El expediente ha sido eliminado');
                    card.remove()
                }
            }
            catch (e) {
                alert(e);
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }
    function changeOwner (event, element) {
        var card   = jQuery(element).closest ('div.kanban-item'),
            record = parseInt (card.attr ('data-eid')),
            module = jQuery ('#modulename').val ();
        ekkoLightBox = jQuery('<a href=index.php?module='+module+'&action=ChangeEntityOwner&Ajax=true&record='+record+' data-toggle="lightbox" data-max-width="400" data-title="Asignar expediente">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass('bottom');
                modalBackdrop.removeClass('z-index');
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }
    jQuery(window).on('load', function (e) {
        jQuery('.div1').width (jQuery ('.kanban-container').width() + 50);
    });
{/literal}
</script>
{/strip}