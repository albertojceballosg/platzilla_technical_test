{strip}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 25px">
            <div class="table-responsive" style="padding-right: 4.5px;max-height: 500px; overflow-y: auto">
            <table class="table table-bordered table-condensed">
                <thead>
                <tr style="">
                    <td>&nbsp;</td>
                    <td colspan="{block name="steps_num"}{/block}"
                        style="text-align: center;vertical-align: center;min-height: 45px;background-color: #6ab1dc"><strong>
                            {$MOD['LBL_QUALITY_STEPS']}</strong>
                    </td>
                </tr>
                <tr style="background-color: #cfe2f3">
                    {block name="steps_names"}{/block}
                </tr>
                </thead>
                <tbody>
                    {block name="steps_data"}{/block}
                </tbody>
            </table>
            </div>
        </div>
    </div>
{/strip}