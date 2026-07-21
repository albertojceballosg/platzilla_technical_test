{strip}
    {if ($STAGE neq NULL)}
        {assign var='id' value=$STAGE->getId()}
        {assign var='description' value=$STAGE->getDescription()}
        {assign var='name' value=$STAGE->getName()}
    {else}
        {assign var='id' value=null}
        {assign var='description' value=null}
        {assign var='name' value=null}
    {/if}
    <style>
        .row-how-use {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
            padding-right: 1px;
            padding-left: 1px;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="modules/News/News.css"/>
    <form class="form-horizontal" name="company-sector-form" role="form" method="post" action="index.php">
        <input type="hidden" name="module" value="Settings"/>
        <input type="hidden" name="action" value="SaveQuestionStage"/>
        <input type="hidden" name="record" value="{$id}"/>
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
        <input type="hidden" name="return_module" value="{$RETURN_MODULE}"/>
        {* <input type="hidden" name="Ajax" value="true"/> *}
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=Settings&action=QuestionnaireDataListView&parenttab=Settings&tab=stages">{$MOD['LBL_CONFIG_QUESTIONNAIRE']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=Settings&action=QuestionnaireDataListView&parenttab=Settings&tab=stages"
                       class="btn btn-warning"
                       style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Cuestionarios: Etapas</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Sector Name *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Etapa:</label>
                                <div id="ct-div-name" class="col-md-7">
                                    <input type="text" class="form-control" id="basic_name" name="name"
                                           value="{$name}"
                                           title="El nombre de la etapa"
                                           placeholder="Nombre del fundamento">
                                    <span id="ct-name" class="help-block"></span>
                                </div>
                            </div>
                            {* Sector description *}
                            <div class="form-group">
                                <label for="sector_description" class="col-md-3 control-label">Descripción:</label>
                                <div id="ct-div-description" class="col-md-7">
                                    <textarea class="form-control" name="description" id="sector_description"
                                              rows="3"
                                              placeholder="Breve descripción de la etapa">{$description}</textarea>
                                    <span id="ct-description" class="help-block"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="modules/how_use/how-use-utils.js"></script>
{/strip}