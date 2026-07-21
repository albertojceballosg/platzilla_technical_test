<div class="row-parley justify-content-center">
    <div class="col-md-11 box_shadow" style="background-color: white;margin: 12px;padding: 24px">
{if $RECORDS|@count gt 0}
        <form  class="" role="form" id="archive-mails-record" name="archive-mails-record" method="POST">
                <input type="hidden" name="idEmail" value="{$MAIL_ID}">
            <input type="hidden" name="moduleName" value="{$MODULE_NAME}">
            <div style="height: 300px; overflow-y: auto;">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Seleccionar</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$RECORDS key=k item=v}
                    <tr>
                        <td>
                            <input type="radio" name="record" {if $k eq 0}checked{/if} value="{$v.{$FIELD_ID}}">
                        </td>
                        <td>{$v.{$FIELD_ID}} - {$v.{$FIELD}}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
            <span style="color: #8A0808" class="help-block"></span>
            <button name="submitSearch" id="emailsSubmitSearch" class="btn btn-primary btn-xn"  type="button">Archivar&nbsp;&raquo;</button>
        </form>
{else}
<h3 style="color: #8A0808; text-align: center">Uops ! No se encontraron registros</h3>
{/if}
    </div>
</div>


