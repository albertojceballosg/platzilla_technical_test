{assign var="totalButtons" value=$CUSTOM_BUTTONS|@count}
{if $IS_MODAL}
    <form action="index.php" method="post" name="DetailView" id="form">
        {include file='DetailViewHidden.tpl'}
        {if $MODULE eq 'formacion_cursos'}
            <div>{include file="modules/formacion_cursos/DetailViewCursos.tpl"}</div>
        {/if}
        {assign var="countCol" value=0}
        {assign var="totalBlocks" value=(1700 - (($BLOCKS|count) * 150))}
        {foreach key=header item=detail from=$BLOCKS name=mainBlock}
            {assign var="keyArray" value=key($detail[0])}
            {assign var="uiArray" value=$detail[0][$keyArray]['ui']}
            {assign var="uitype" value=$uiArray[2]}
            {assign var="idField" value=$detail[0][$keyArray]['fldid']}
            {assign var="fieldName" value=$detail[0][$keyArray]['fldname']}
            {assign var="isEmpty" value=true}
            {assign var=detailD value=$detail}
            {if $uitype eq 5010 || $uitype eq 2202}
                {* uitype 5010 y 2202 siempre se muestran *}
                {assign var="isEmpty" value=false}
            {else}
                {foreach item=detail from=$detailD}
                    {foreach key=label item=data from=$detail}
                        {if ((!empty ($data.value) && $uitype neq 2202) || ($FIELD_ATTACHMENTS[$fieldName] neq NULL && $uitype eq 4096)) }
                            {assign var="isEmpty" value=false}
                        {/if}
                    {/foreach}
                {/foreach}
                {if ($isEmpty)}
                    {continue}
                {/if}
            {/if}
            {if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
                <div class="row">
                    <div class="main-box"
                        {if $smarty.foreach.mainBlock.index > 0}style="border-top:1px solid #D8D8D8!important;"
                    {/if}>
                    <header class="title-section main-box-header clearfix">
                        <h2>{$MOD.LBL_COMMENT_INFORMATION} {$idField}</h2>
                    </header>
                    <div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">{$COMMENT_BLOCK}</div>
                </div>
            </div>
        {else}
            <div class="row" id="row-StandardDetailView">
                <div class="main-box" {if $smarty.foreach.mainBlock.index > 0} id="main-box-StandardDetailView"
                    {/if}>
                    <header class="title-section main-box-header clearfix">
                        <h2>{$header}</h2>
                    </header>
                    <div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
                        {if $uitype eq 2202}
                            {if isset($CAMPOS_TIPO_GRID[$idField])}
                                {$CAMPOS_TIPO_GRID[$idField]}
                            {else}
                                <div class="alert alert-warning">Grid field {$fieldName} (ID: {$idField}) not found in CAMPOS_TIPO_GRID
                                </div>
                            {/if}
                        {elseif $uitype eq 5010}
                            {$detail[0][$keyArray]['value']}
                        {else}
                            {* Otros uitypes: renderizar normalmente *}
                            {assign var=detailD value=$detail}

                            {foreach item=detail from=$detailD}
                                {foreach key=label item=data from=$detail  name=detailBlock}
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
                                    {assign var=keyaccess value=$data.notaccess}
                                    {assign var=keycntimage value=$data.cntimage}
                                    {assign var=keyadmin value=$data.isadmin}
                                    {assign var=display_type value=$data.displaytype}
                                    {assign var=_readonly value=$data.readonly}
                                    {if $label ne ''}
                                        {if $keycntimage ne ''}
                                            <input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin} />{$keycntimage}
                                        {elseif $keyid eq '14'}
                                            <input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin} />
                                        {/if}
                                        {if $keyid neq 5010 && $keyid neq 2202}
                                            {include file="DetailViewUI.tpl"}
                                        {/if}
                                    {/if}

                                {/foreach}
                            {/foreach}
                        {/if}
                    </div>
                </div>
            </div>
        {/if}
    {/foreach}
    {if $CAMPOS_TIPO_GRID}
        <script type="text/javascript" src="include/js/gridFormValidate.js"></script>
    {/if}
    {block name="content-after-blocks"}{/block}
</form>
{else}
{* new detailView here *}
<div class="row">
    {* Detail record *}
    <div class="{if ($gridPosition eq 'SIDE') ||($gridPosition eq MULL)}col-md-8{else}col-md-12{/if}">
        <div id="card-view-register-container"> {*div with scroll*}
            <div class="row">
                <div class="col-md-12">
                    {* Tarjeta aquí *}
                    <div class="card rounded">
                        <div class="card-header platzilla-card-header rounded">
                            <div class="row">
                                <div class="col-md-5">
                                    <p class="text-center pull-left" style="font-weight: bold">Detalle del Registro</p>
                                </div>
                                <div class="col-md-7">


                                    <div class="pull-right">

                                        <!-- inicio de custom buttons PEBG 16/10/2025-->
                                        {foreach key=keyCB item=CB from=$CUSTOM_BUTTONS}
                                            {assign var=_link value=$CB.link|default:''}
                                            {if $_link ne ''}
                                                <a class="btn btn-{$CB.style} btn-circle btn-xs" href="{eval $_link}"
                                                    title="{$CB.description}" title="{$CB.description|default:$CB.label}">
                                                    <span class="fa {$CB.faicon}"></span>
                                                </a>


                                            {/if}
                                        {/foreach}
                                        <!-- Fin de custom buttons PEBG 16/10/2025-->


                                        {if $HOW_TO_ID neq NULL}
                                            <a class="btn btn-info btn-circle btn-xs" data-width="950"
                                                data-toggle="lightbox" data-parent="" data-gallery="remoteload"
                                                data-title="¡Aprende como!"
                                                href="index.php?module={$MODULE}&action=AjaxDetailViewUtils&record={$HOW_TO_ID}&function=GET-HOW-TO&Ajax=true"
                                                title="¡Aprende como!"><i class="bi bi-question-square"></i></a>
                                        {/if}
                                        {* printer button *}
                                        {if in_array($MODULE, $btnPrinter)}
                                            <button type="button" id="pinterButton" class="btn btn-info btn-circle btn-xs"
                                                title="Imprime el documento actual" onclick="print_invoice('{$MODULE}')">
                                                <i class="fa fa-print" aria-hidden="true"></i>
                                            </button>
                                        {/if}
                                        {* /printer button *}
                                        {* share button *}
                                        <a class="btn btn-success btn-circle btn-xs" id="shareButton"
                                            href="javascript:;"
                                            onclick="DataSharingUtils.openSharingModal ('{$MODULE}', '{$ID}');"><i
                                                class="fa fa-share"></i></a>
                                        {if ($EDIT_DUPLICATE == 'permitted')}
                                            {* duplicate button *}
                                            <a class="btn btn-warning btn-circle btn-xs" id="duplicateButton"
                                                href="javascript:void(0)"
                                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');"><i
                                                    class="fa fa-files-o"></i></a>
                                        {/if}
                                        {if ($EDIT_PERMISSION == 'yes')}
                                            {* edit button*}
                                            <a href="javascript:void(0)"
                                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
                                                id="editButton" class="btn btn-default btn-circle btn-xs">
                                                <span class="fa fa-pencil"></span>
                                            </a>
                                        {/if}
                                        {* expediente button - solo orden_de_trabajo *}
                                        {if $MODULE eq 'orden_de_trabajo'}
                                            <a class="btn btn-primary btn-circle btn-xs"
                                                href="javascript:void(0)"
                                                onclick="sjvModalOpen('{$ID}')"
                                                title="Ver expediente completo del trabajo">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            {* Modal Expediente *}
                                            <div class="modal fade" id="sjv-modal" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document" style="width:95%; max-width:1400px; margin:20px auto;">
                                                    <div class="modal-content" style="height:90vh;">
                                                        <div class="modal-header" style="padding:8px 15px;">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title"><i class="fa fa-eye"></i> Expediente del Trabajo</h4>
                                                        </div>
                                                        <div class="modal-body" style="padding:0; height:calc(90vh - 52px);">
                                                            <iframe id="sjv-iframe" src="" frameborder="0"
                                                                style="width:100%; height:100%; border:none;"></iframe>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                            function sjvModalOpen(recordId) {ldelim}
                                                var url = 'index.php?module=orden_de_trabajo&action=SpecialJobView&record=' + recordId + '&Ajax=true';
                                                document.getElementById('sjv-iframe').src = url;
                                                jQuery('#sjv-modal').modal({ldelim}backdrop: true, keyboard: true{rdelim});
                                                jQuery('#sjv-modal').modal('show');
                                            {rdelim}
                                            jQuery('#sjv-modal').on('hidden.bs.modal', function() {ldelim}
                                                document.getElementById('sjv-iframe').src = '';
                                            {rdelim});
                                            </script>
                                        {/if}
                                        {if ($DELETE == 'permitted')}
                                            {* delete button *}
                                            <a class="btn btn-danger btn-circle btn-xs" href="javascript:void(0)"
                                                id="deleteButton" tagModule="{$MODULE}"
                                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='index'; var confirmMsg = '{$APP.NTC_DELETE_CONFIRMATION}'; submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);"><span
                                                    class="fa fa-trash-o"></span></a>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Card body wa 07-02 -->
                            {* Register *}
                            <form action="index.php" method="post" name="DetailView" id="form">
                                {include file='DetailViewHidden.tpl'}
                                {if $MODULE eq 'formacion_cursos'}
                                    <div>{include file="modules/formacion_cursos/DetailViewCursos.tpl"}</div>
                                {/if}
                                {assign var="countCol" value=0}
                                {assign var="totalBlocks" value=(1700 - (($BLOCKS|count) * 150))}
                                {foreach key=header item=detail from=$BLOCKS name=mainBlock}
                                    {assign var="keyArray" value=key($detail[0])}
                                    {assign var="uiArray" value=$detail[0][$keyArray]['ui']}
                                    {assign var="uitype" value=$uiArray[2]}
                                    {assign var="idField" value=$detail[0][$keyArray]['fldid']}
                                    {assign var="fieldName" value=$detail[0][$keyArray]['fldname']}
                                    {assign var="isEmpty" value=true}
                                    {assign var=detailD value=$detail}
                                    {if $uitype eq 5010 || $uitype eq 2202}
                                        {* uitype 5010 y 2202 siempre se muestran *}
                                        {assign var="isEmpty" value=false}
                                    {else}
                                        {foreach item=detail from=$detailD}
                                            {foreach key=label item=data from=$detail}
                                                {if ((!empty ($data.value) && $uitype neq 2202) || ($FIELD_ATTACHMENTS[$fieldName] neq NULL && $uitype eq 4096)) }
                                                    {assign var="isEmpty" value=false}
                                                {/if}
                                            {/foreach}
                                        {/foreach}
                                        {if ($isEmpty)}
                                            {continue}
                                        {/if}
                                    {/if}
                                    {if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
                                        <div class="row">
                                            <!-- (1) wa 18/08/2020 -->
                                            <div class="main-box"
                                                {if $smarty.foreach.mainBlock.index > -1}style="border:1px solid #D8D8D8!important;margin-top: 12px{if $IS_MODAL}height: 100%!important;;{/if}"
                                            {else}style="height: 100%!important;" 
                                            {/if}>
                                            <header class="title-section main-box-header clearfix">
                                                <h2>{$MOD.LBL_COMMENT_INFORMATION} {$idField}</h2>
                                            </header>
                                            <div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
                                                {$COMMENT_BLOCK} </div>
                                        </div>
                                    </div>
                                {else}

                                    <div class="row" style="margin-top: -4px">
                                        <!-- (2) wa 18/08/2020 -->
                                        {if $smarty.foreach.mainBlock.index > 0 && !sw_process}
                                            <div class="col-md-12 border-bottom" style="margin-bottom: 6px;margin-top: 16px;">
                                                <p class="text-left" style="font-weight: bold;">{*$header*}</p>
                                            </div>
                                        {/if}
                                        {* main-box *}

                                        <div class="main-box"
                                        {*if $smarty.foreach.mainBlock.index > -1}style="border:    1px solid #D8D8D8 !important;margin-top: 20px;" {/if*}>
                                        {*
                                                    <header class="title-section main-box-header clearfix">
                                                        <h2>$header</h2>
                                                    </header> *}

                                        {* /body *}
                                        <!-- (3) wa 18/08/2020 -->
                                        <div class="main-box-body clearfix" style="padding: 0 2px;!important;"
                                            id="tbl{$header|replace:' ':''}">
                                            <!-- {$smarty.foreach.mainBlock.index} -->
                                            {if ($sw_process) && $PROCESS_CASE neq NULL}
                                                {assign var='sw_process' value=false}
                                                {$PROCESS_CASE}
                                            {/if}
                                            {if $uitype eq 2202}
                                                {if isset($CAMPOS_TIPO_GRID[$idField])}
                                                    {$CAMPOS_TIPO_GRID[$idField]}
                                                {else}
                                                    <div class="alert alert-warning">Grid field {$fieldName} (ID: {$idField})
                                                        not found in CAMPOS_TIPO_GRID</div>
                                                {/if}
                                            {elseif $uitype eq 5010}
                                                {$detail[0][$keyArray]['value']}
                                            {else}
                                                {* Otros uitypes: renderizar normalmente *}
                                                {assign var=detailD value=$detail}
                                                {foreach item=detail from=$detailD}
                                                    {foreach key=label item=data from=$detail  name=detailBlock}
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
                                                        {assign var=keyaccess value=$data.notaccess}
                                                        {assign var=keycntimage value=$data.cntimage}
                                                        {assign var=keyadmin value=$data.isadmin}
                                                        {assign var=display_type value=$data.displaytype}
                                                        {assign var=_readonly value=$data.readonly}
                                                        {if $label neq ''}
                                                            {if $keycntimage neq ''}
                                                                <input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin} />{$keycntimage}
                                                            {elseif $keyid eq '14'}
                                                                <input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin} />
                                                            {/if}
                                                            {if $keyid neq 5010 && $keyid neq 2202}
                                                                {include file="DetailViewUI.tpl"}
                                                            {/if}
                                                        {/if}

                                                    {/foreach}
                                                {/foreach}
                                            {/if}
                                        </div>{*/ body *}
                                    </div>

                                </div>
                                {/if}
                                {assign var='sw_process' value=false}
                                {/foreach}
                                {if $CAMPOS_TIPO_GRID}
                                    <script type="text/javascript" src="include/js/gridFormValidate.js"></script>
                                {/if}
                                {block name="content-after-blocks"}{/block}
                            </form>
                            {if ($EDIT_PERMISSION == 'yes')}
                                <script type="text/javascript" src="themes/centaurus/js/bootstrap-editable.js"></script>
                                {loadEditableFiels arrayBlocs=$BLOCKS}
                            {/if}
                            {* /Register *}
                        </div> {* card body main-box *}
                    </div> {*card *}
                    {* Tarjeta aquí *}
                </div>
                <div class="col-md-12" style="margin-top: 20px">
                    {*  related information *}
                    {*include file='modules/grid_view/RelatedListCardView.tpl'*}
                    {*  /related information *}
                </div>
            </div>
        </div>{*div with scroll*}
    </div>
    {* cards gridPosition *}
    <div class="{if ($gridPosition eq 'SIDE') ||($gridPosition eq MULL)}col-md-4{else}col-md-12{/if}">
        {include file='modules/grid_view/DetailCardView.tpl'}
    </div>
    {* /cards *}
</div>
{* new detailView here *}
{/if}