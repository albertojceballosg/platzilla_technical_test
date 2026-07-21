{strip}
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="{block name="{$tableClass}"}{/block}">
            <tr>
                {block name="{$tableHeader}"}{/block}
            </tr>
            </thead>
            <tbody class="{block name="{$tableClass}"}{/block}">
            {block name="{$tableBody}"}{/block}
            </tbody>
        </table>
    </div>
{/strip}