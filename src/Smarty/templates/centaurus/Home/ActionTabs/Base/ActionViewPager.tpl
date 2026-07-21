{strip}
    <ul class="pagination pull-right">
        <li class="disabled">
            <button type="button"
                    onclick="DataViewUtils.openPage ('Calendar', 1, '', '', 'oportunidades', '72658', 'OPPORTUNITIES');">
                <i class="fa fa-step-backward"></i></button>
        </li>
        <li class="disabled">
            <button type="button"
                    onclick="DataViewUtils.openPage ('Calendar', 0, '', '', 'oportunidades', '72658', 'OPPORTUNITIES');">
                <i class="fa fa-chevron-left"></i></button>
        </li>
        <li><span class="pagination-search"><input type="text" id="pagenum" name="pagenum"
                                                   class="form-control" value="1" data-actual-page="1"
                                                   data-total-pages="1" placeholder=""
                                                   onchange="DataViewUtils.openSelectedPage (this, 'Calendar', '', '', 'oportunidades', '72658', 'OPPORTUNITIES');"> de 1</span>
        </li>
        <li class="disabled">
            <button type="button"
                    onclick="DataViewUtils.openPage ('Calendar', 2, '', '', 'oportunidades', '72658', 'OPPORTUNITIES');">
                <i class="fa fa-chevron-right"></i></button>
        </li>
        <li class="disabled">
            <button type="button"
                    onclick="DataViewUtils.openPage ('Calendar', 1, '', '', 'oportunidades', '72658', 'OPPORTUNITIES');">
                <i class="fa fa-step-forward"></i></button>
        </li>
    </ul>
{/strip}