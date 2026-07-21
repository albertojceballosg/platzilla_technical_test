{strip}
    {if ($SERIE neq NULL)}
        {assign var='id' value=$SERIE->getId()}
        {assign var='name' value=$SERIE->getName()}
        {assign var='status' value=$SERIE->getStatus()}
    {else}
        {assign var='id' value=null}
        {assign var='name' value=null}
        {assign var='status' value=null}
    {/if}
    <style>
        .row-course {
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
    <form class="form-horizontal" name="company-sector-form" role="form" method="post" action="index.php">
        <input type="hidden" name="module" value="Settings"/>
        <input type="hidden" name="action" value="SaveCourseSerie"/>
        <input type="hidden" name="tab" value="courses_series"/>
        <input type="hidden" name="record" value="{$id}"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=Settings&action=CourseListView&parenttab=Settings">{$MOD['LBL_COURSE']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=Settings&action=CourseListView&parenttab=Settings&tab=courses_series"
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
                        <h2 class="pull-left">Serie de cursos: Información general</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Sector Name *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Nombre:</label>
                                <div id="cs-div-name" class="col-md-7">
                                    <input type="text" class="form-control" id="sector_name" name="name"
                                           value="{$name}"
                                           title="El nombre de la serie"
                                           placeholder="Definir serie">
                                    <span id="cs-name" class="help-block"></span>
                                </div>
                            </div>
                            {* Sector description *}
                            <div class="form-group">
                                <label for="sector_description" class="col-md-3 control-label">Estado:</label>
                                <div id="cs-div-description" class="col-md-7">
                                    <select class="form-control" name="status" id="status_view">
                                        <option value="ENABLED" {if $status eq 'ENABLED'} selected=""{/if}> Activo</option>
                                        <option value="DISABLED" {if $status eq 'DISABLED'} selected=""{/if}> No activo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
{/strip}