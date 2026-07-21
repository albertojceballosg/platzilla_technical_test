{strip}
    {block name="css"}{/block}
    {math equation= rand() assign= "idReportBuilder"}
    {if ($DIAGNOSTIC_BUILDER neq NULL)}
        {assign var='drbId' value=$DIAGNOSTIC_BUILDER->getId ()}
        {assign var='drbName' value=$DIAGNOSTIC_BUILDER->getName ()}
        {assign var='questionnaireId' value=$DIAGNOSTIC_BUILDER->getQuestionnaireId ()}
        {assign var='reportsToAnswer' value=$DIAGNOSTIC_BUILDER->getReportsToAnswer ()}
        {assign var="isEdit" value=true}
    {else}
        {assign var='drbId' value=null}
        {assign var='drbName' value=null}
        {assign var='questionnaireId' value=null}
        {assign var='reportsToAnswer' value=null}
        {assign var="isEdit" value=false}
    {/if}
    <div class="row">
        <div class="col-xs-12">
            <h1>
                <a href="{block name="panel_url"}{/block}">
                    {block name="view_title"}{/block}
                </a>
            </h1>
        </div>
    </div>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <form {block name="form_parameter"}{/block}>
        {block name="from_hidden"}{/block}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Información general</h2>
                        <div class="action-bar pull-right">
                            <button type="button"
                                    onclick="DiagnosticRerportBuilderUtls.saveDiagnosticBuilder(this,  {$idReportBuilder})"
                                    class="btn btn-info">Guardar</button>
                            <a href="{block name="panel_url"}{/block}"
                               class="btn btn-warning">Cancelar</a>
                        </div>
                    </header>
                    <div class="main-box-body">
                        {block name="main-box-body"}{/block}
                    </div>
                    <header class="main-box-header clearfix">
                        <div class="action-bar pull-right">
                            <button type="button"
                                    onclick="DiagnosticRerportBuilderUtls.saveDiagnosticBuilder(this,  {$idReportBuilder})"
                                    class="btn btn-info">Guardar</button>
                            <a href="{block name="panel_url"}{/block}"
                               class="btn btn-warning">Cancelar</a>
                        </div>
                    </header>
                </div>
            </div>
        </div>
    </form>
    {block name="questionnaire_block"}{/block}
    {block name="answere_block"}{/block}
    {block name="js"}{/block}
{/strip}