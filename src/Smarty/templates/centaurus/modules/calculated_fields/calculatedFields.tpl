<link rel="stylesheet" type="text/css" href="modules/calculated_fields/select2.css" />
<script type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<script type="text/javascript" src="modules/calculated_fields/select2.min.js"></script>
<div id="email-box" class="clearfix">
	<div class="col-lg-12">
		<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="padding: 0; width: 30px;">
						<i class="fa fa-sun-o green-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li>{$MOD.LBL_CONFIG_CALCULATED_FIELDS}</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" colspan="3" valign="top">{$MOD.LBL_CONFIG_CALCULATED_FIELDS_DESCRIPTION}</td>
			</tr>
		</table>
	</div>
	<br />
	<br />
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="col-lg-12">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
{if ($MSG_ERROR != '')}
	<div class="col-lg-12">
		<div class="alert alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<i class="fa fa-times-circle fa-fw fa-lg"></i>
			<strong>ERROR!</strong> {$MSG_ERROR}.
		</div>
	</div>
{/if}
	<div class="row">
  <div class="col-lg-12">
	<div class="main-box clearfix">
				<br />
	   <div class="tabs-wrapper">
		 <ul class="nav nav-tabs" style= "margin:2px 12px">
		 <li {if $TAB == 'system'} class="active" {/if} >
		 		<a href="#tab-2" data-toggle="tab">{$MOD.TAB_CALCULATED_CREATE}</a>
		 	</li>
		 	<li {if ! isset($TAB)} class="active" {/if} >
		 		<a href="#tab-1" data-toggle="tab">{$MOD.TAB_CALCULATED_FIELDS_CREATE}</a>
		 	</li>
			 <li {if $TAB == 'calculated_field'} class="active" {/if}>
				 <a href="#tab-3" data-toggle="tab">{$MOD.TAB_TABLE_CALCULATED_FIELDS_CREATE}</a>
			 </li>
		 </ul>
		 <div class="tab-content">
		 	<div class="tab-pane fade in {if ! isset($TAB)}active {/if} " id="tab-1" style="margin-bottom: 15px;">
				<div class="pull-right" style="margin-right: 20px;padding:25px 0px">
					<a class="btn btn-primary" href="index.php?module=calculated_fields&action=addCalculatedFields">{$MOD.LBL_CALCULATED_FIELDS_CREATE}</a>
				</div>
				<br />
				<div class="main-box-body clearfix">
					<br />
					<div id="claculateElement">
                    {include file='modules/calculated_fields/calculatedFieldsContents.tpl'}
					</div>
				</div>
			</div>
		 <div class="tab-pane fade in {if $TAB == 'system'}active {/if} " id="tab-2" style="margin-bottom: 15px;">
		 	<div class="pull-right" style="margin-right: 20px;padding:25px 0px;">
					<button class="btn btn-primary" onclick="CSUtils.openCreateCalculationModal(this)">{$MOD.LBL_CALCULATED_CREATE}</button>
				</div>
				<br />
				<div class="main-box-body clearfix">
					<br />
					<div id="calculatedSiystem">
                    {include file='modules/calculated_fields/calculatedSystemContents.tpl'}
					</div>
				</div>
		 </div>
			 <div class="tab-pane fade in  {if $TAB == 'calculated_field'}active {/if} " id="tab-3" style="margin-bottom: 15px;">

				 <div class="main-box-body clearfix">
					 <br />
					 <div id="gridCalculatedField">
                         {include file='modules/calculated_fields/gridCalculatedFieldsContents.tpl'}
					 </div>
				 </div>
			 </div>
		</div>
		</div>
	   </div>
	</div>
  </div>
</div>
<div id="editdiv" style="display: none; position: absolute; width: 400px;"></div>
<div class="md-overlay"></div>
<script type="text/javascript" src="modules/calculated_fields/calculatedsystem-init.js"></script>
<script type="text/javascript" src="modules/calculated_fields/calculatedsystem.js"></script>
<script type="text/html" id="new-calculate-modal-template">
    {include file='modules/calculated_fields/CalculateSystemModal.tpl'}
</script>
<script>
{literal}

jQuery('.delete_field').click(function(e) {
    			deleteRel =  jQuery(this).attr('rel').split('@');
    			cod = deleteRel[0];
    			var r = confirm('Eliminar el elemento: '+deleteRel[1]+' y los Cálculos del Sistema asociados ?');
			    if (r == true) {
            	var  url = 'module=calculated_fields&action=calculated_fieldsAjax&file=ajaxOption&ajax=true&id='+cod+'&method=field';
                    new Ajax.Request(
                        'index.php',
                        {asynchronous : false,
                            cache: false,
                            queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody:url,
                            onComplete: function(response) {
                                response.responseText = response.responseText.replace(/^[\s\ufeff\xA0]+|[\s\uFEFF\xA0]+$/g, '');
                                if ( response.responseText != '' ) {
                                    var record;
                                    record = JSON.parse(response.responseText);
                                    location.reload(true);
                                }
                            }
                        }
                    );
              }
                 e.preventDefault();
            });

jQuery('.delete_system').click(function(e) {
    deleteRel =  jQuery(this).attr('rel').split('@');
    cod = deleteRel[0];
    var r = confirm('Eliminar el Cálculo: '+deleteRel[1]+' ?');
    if (r == true) {
        var  url = 'module=calculated_fields&action=calculated_fieldsAjax&file=ajaxOption&ajax=true&id='+cod+'&method=system';
        new Ajax.Request(
            'index.php',
            {asynchronous : false,
                cache: false,
                queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody:url,
                onComplete: function(response) {
                    response.responseText = response.responseText.replace(/^[\s\ufeff\xA0]+|[\s\uFEFF\xA0]+$/g, '');
                    if ( response.responseText != '' ) {
                        var record;
                        record = JSON.parse(response.responseText);
                        jQuery('#system_'+record.cod).addClass('hide')

                    }
                }
            }
        );
    }
    e.preventDefault();
});

jQuery('.active_system').click(function(e) {
    var infoRel =  jQuery(this).attr('rel').split('@'),
		cod = infoRel[0],
		statusActual = jQuery(this).attr('title'),
		$this = jQuery(this);

    var r = confirm( statusActual + ' el Cálculo: '+infoRel[1]+' ?');
    if (r == true) {
        var  url = 'module=calculated_fields&action=calculated_fieldsAjax&file=ajaxOption&ajax=true&id='+cod+'&method=status_system';
        new Ajax.Request(
            'index.php',
            {asynchronous : false,
                cache: false,
                queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody:url,
                onComplete: function(response) {
                    response.responseText = response.responseText.replace(/^[\s\ufeff\xA0]+|[\s\uFEFF\xA0]+$/g, '');
                    console.log(response)
                    if ( response.responseText != '' ) {
                        var record, statusBtn, btnColor;
                        record = JSON.parse(response.responseText);
                        statusBtn = jQuery('#status-'+record.cod);
                        if(record.btnImage == 'ban') {
                            statusBtn.removeClass ('fa-check');
                            statusBtn.addClass ('fa-ban');
                            $this.addClass ('text-warning');
                            $this.removeClass ('text-success');
                            $this.attr('title', 'Desactivar')
                        } else if(record.btnImage == 'check') {
                            statusBtn.removeClass ('fa-ban');
                            statusBtn.addClass ('fa-check');
                            $this.removeClass('text-warning');
                            $this.addClass('text-success');
                            $this.attr('title', 'Activar');
                        } else {
                            alert('Error al ' +statusActual)
                        }
                    }
                }

            }
        );
        $this.blur();
        $this.hideFocus = true;
        $this.style.outline = 'none';
    }
    e.preventDefault();
});
{/literal}
</script>