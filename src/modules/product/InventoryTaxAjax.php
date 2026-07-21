<?php
	global $theme, $mod_strings, $app_strings;
	$theme_path='themes/'.$theme.'/';

	$productid = vtlib_purify($_REQUEST['productid']);
	$rowid = vtlib_purify($_REQUEST['curr_row']);
	$row_no = $rowid;
	$product_total = vtlib_purify($_REQUEST['productTotal']);

	$tax_details = getTaxDetailsForProduct($productid,'all');//we should pass available instead of all if we want to display only the available taxes.
	$associated_tax_count = count($tax_details);

	$tax_div = '
				<div id="tax_table{'.$row_no.'}" class="clearfix" style="white-space: nowrap;">
					<div style="float:left;padding-left:10px;width:100%;">
						<div class="main-box infographic-box" style="float:left;width:360px;max-width:360px;height:250px;max-height:250px;margin-left:5px;padding:10px;overflow:auto">
							<a onClick="fnHidePopDiv(\'tax_div'.$rowid.'\')" href="javascript:void(0)">x</a><h3 style="margin-top:5px;"></h3>
							<span class="headline" id="tax_div_title'.$rowid.'" style="font-size:0.9em; text-align: left;"><b>'.$app_strings['LABEL_SET_TAX_FOR'].' : '.$product_total.'</b></span>
								<div class="form-group" style="font-size:0.9em; text-align: left;">
									<table width="100%" cellpadding="0" cellspacing="0" class="table">
										<tbody>
			';

	$net_tax_total = 0.00;
	// @codingStandardsIgnoreStart
	for ($i = 0, $j = ($i + 1); $i < $associated_tax_count; $i++, $j++) {
		// @codingStandardsIgnoreEnd
		$tax_name = $tax_details[$i]['taxname'];
		$tax_label = $tax_details[$i]['taxlabel'];
		$tax_percentage = $tax_details[$i]['percentage'];
		$tax_name_percentage = $tax_name.'_percentage'.$rowid;
		$tax_id_name = 'hidden_tax'.$j.'_percentage'.$rowid;//used to store the tax name, used in function callTaxCalc
		$tax_name_total = 'popup_tax_row'.$rowid;
		$tax_total = ($product_total * $tax_percentage / 100.00);

		$net_tax_total += $tax_total;
		$tax_div .= '
            	<tr>
                	<td style="padding: 1px; font-size:0.9em;">
                    	<input type="text" class="form-control" onkeyup="validateDecimalGeneral(\''.$tax_name_percentage.'\')" size="5" name="'.$tax_name_percentage.'" id="'.$tax_name_percentage.'" value="'.$tax_percentage.'" onBlur="calcCurrentTax(\''.$tax_name_percentage.'\',\''.$rowid.'\',\''.$i.'\');calcTotal();">&nbsp;%
                        <input type="hidden" id="'.$tax_id_name.'" value="'.$tax_name_percentage.'">
                    </td>
                    <td style="padding: 1px; font-size:0.9em;">%</td>
                    <td align="center" style="padding: 1px;  font-size:0.9em;">'.$tax_label.'</td>
                    <td align="right" style="padding: 1px;  font-size:0.9em;">
                        <input type="text" class="form-control" size="6" name="'.$tax_name_total.'" id="'.$tax_name_total.'" style="cursor:pointer;" value="'.$tax_total.'" readonly>
                    </td>
                </tr>';
	}

	$tax_div .= '</tbody></table>';


	if($associated_tax_count == 0) {
		$tax_div .= '<div align="left" class="lineOnTop">'.$mod_strings['LBL_NO_TAXES_ASSOCIATED'].'.</div>';
	}

	$tax_div .= '<input type="hidden" id="hdnTaxTotal'.$rowid.'" name="hdnTaxTotal'.$rowid.'" value="'.$net_tax_total.'">';

	$tax_div .= '</div></div></div></div>';

	echo $tax_div;
