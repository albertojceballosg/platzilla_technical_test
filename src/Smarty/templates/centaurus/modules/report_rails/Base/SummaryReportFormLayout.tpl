{strip}
    {math equation= rand() assign= "idSummaryReport"}
    {block name="css"}{/block}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <form method="post" action="index.php"  name="{block name='form_name'}{/block}"    id="summary-report-{block name="form_name"}{/block}">
        <input type="hidden" name="module" value="report_rails"/>
        <input type="hidden" name="action" value="{block name="actionFile"}{/block}"/>
        <input type="hidden" name="record" value="{$record}"/>
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
        <input type="hidden" id="index_color" name="index_color" value=""/>
        <input type="hidden" name="tab" value=""/>
        <input type="hidden" id="master_report" name="master_report" value="{$MASTER_REPORT_ID}"/>
        {block name="isAgreement"}{/block}
        {block name="isAjax"}{/block}
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report={$MASTER_REPORT_ID}{block name="selectedTab"}{/block}">{block name="tabName"}{/block}</a>
                </h1>
                <div class="action-bar pull-right">
                    {block name="update_button"}{/block}
                    <button type="button"
                            data-form-id="{$idSummaryReport}"
                            {block name="saveJsAction"}{/block}
                            class="btn btn-info">Guardar
                    </button>
                    <a href="index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report={$MASTER_REPORT_ID}{block name="selectedTab"}{/block}"
                       class="btn btn-warning" style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    {block name="mainBoxHeader"}{/block}
                    <div class="main-box-body">
                        {block name="mainBoxBody"}{/block}
                    </div>
                </div>
            </div>
        </div>
    </form>
    {block name="js"}{/block}
{/strip}