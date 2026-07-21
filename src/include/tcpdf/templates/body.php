<?php
// watermark based on status
// this is the postion of the watermark before the rotate
//TM Evito que se imprima el Watermark

//$waterMarkPositions=array("30","180");
// this is the rotate amount (todo)
//$waterMarkRotate=array("45","50","180");
//$pdf->watermark( $status, $waterMarkPositions, $waterMarkRotate );

include("include/fpdf/pdfconfig.php");

require_once('include/database/PearDatabase.php');

global $adb;
$sql="select vtiger_currency_info.currency_symbol from vtiger_currency_info  where defaultid = '-11'";

$result = $adb->pquery($sql);
$currency_symbol = $adb->query_result($result,0,'currency_symbol');

// blow a bubble around the table
$Bubble=array("10",$body_top,"170","$bottom");
$pdf->tableWrapper($Bubble);

/* ************ Begin Table Setup ********************** */
// Each of these arrays needs to have matching keys
// "key" => "Length"
// total of columns needs to be 190 in order to fit the table
// correctly
$prodTable=array("10","60");
//added for value field allignment
//contains the x angle starting point of the value field
$space=array("4"=>"191","5"=>"189","6"=>"187","7"=>"186","8"=>"184","9"=>"182","10"=>"180","11"=>"179","12"=>"177","13"=>"175");
//if taxtype is individual
if($focus->column_fields["hdnTaxType"] == "individual")
{
	$colsAlign["Product Name"] = "L";
	$colsAlign["Description"] = "L";
	$colsAlign["Qty"] = "R";
	$colsAlign["Price"] = "R";
	//$colsAlign["Discount"] = "R";
	$colsAlign["Tax"] = "R";
	$colsAlign["Total"] = "R";
//	$cols["Product Code"] = "30";
//	$cols["Product Name"] = "65";
	$cols["Product Name"] = "80";
	$cols["Qty"] = "13";
	$cols["Price"] = "35";
	//$cols["Discount"] = "15";
	$cols["Tax"] = "35";
	$cols["Total"] = "25";
}
else
{
	//if taxtype is group
	$colsAlign["Product Name"] = "L";
	$colsAlign["Description"] = "L";
	$colsAlign["Qty"] = "R";
	$colsAlign["Price"] = "R";
//	$colsAlign["Discount"] = "R";
	$colsAlign["Total"] = "R";
//	$cols["Product Code"] = "30";
//	$cols["Product Name"] = "65";
	$cols["Product Name"] = "115";
	$cols["Qty"] = "15";
//	$cols["Price"] = "30";
	$cols["Price"] = "25";
//	$cols["Discount"] = "20";
	$cols["Total"] = "30";
}


$pdf->addCols( $cols,$prodTable,$bottom, $focus->column_fields["hdnTaxType"]);
$pdf->addLineFormat( $colsAlign);

/* ************** End Table Setup *********************** */



/* ************* Begin Product Population *************** */
$ppad=3;
$y    = $body_top+10;

for($i=0;$i<count($line);$i++)
{

	if ($line[$i][Total] != ''){
		//$line[$i][Price] .= " €";
		$line[$i][Price] .= " ".$currency_symbol;
		//$line[$i][Total] .= " €";
		$line[$i][Total] .= " ".$currency_symbol;
	}
	$size = $pdf->addProductLine( $y, $line[$i] );
	$y   += $size+$ppad;
}

/* ******************* End product population ********* */


/* ************* Begin Totals ************************** */
$t=$bottom+56;
$pad=6;
for($i=0;$i<count($total);$i++)
{
	$size = $pdf->addProductLine( $t, $total[$i], $total[$i] );
	$t   += $pad;
}

//Set the x and y positions to place the NetTotal, Discount, S&H charge
//if taxtype is not individual ie., group tax
if($focus->column_fields["hdnTaxType"] != "individual")
{
	//$lineData=array("105",$bottom+37,"94");
	$lineData=array("125",$bottom+37 + 20,"74");
	$pdf->drawLine($lineData);
	$data= $app_strings['LBL_NET_TOTAL'].":\n";//                                                                  ".$price_subtotal."";
	$pdf->SetXY( 127 , ($nettotal_y+(0*$next_y)) + 20 );
	$pdf->SetFont( "Helvetica", "", 10);
	$pdf->MultiCell(110, 4, $data);

//Added for value field alignment
	$pdf->SetXY( $space[strlen($price_subtotal)] -7, ($nettotal_y+(0*$next_y)) + 20 );
	$pdf->SetFont( "Helvetica", "", 10);
	$pdf->MultiCell(110, 4, $price_subtotal." $currency_symbol\n");


	/*$lineData=array("125",$bottom+43,"94");
	$pdf->drawLine($lineData);*/

	//For alignment
	/*if($final_price_discount_percent != '')
		$data= $app_strings['LBL_DISCOUNT'].":   $final_price_discount_percent";//                                                ".$price_discount."";
	else
	$data= $app_strings['LBL_DISCOUNT'].":";//                                                                  ".$price_discount."";
	$pdf->SetXY( 105 , ($nettotal_y+(1*$next_y)) );
	$pdf->SetFont( "Helvetica", "", 10);
	$pdf->MultiCell(110, 4, $data);
	*/

//Added for value field alignment
    /*$pdf->SetXY( $space[strlen($price_discount)] , ($nettotal_y+(1*$next_y)) );
    $pdf->SetFont( "Helvetica", "", 10);
    $pdf->MultiCell(110, 4, $price_discount);*/

	$lineData=array("125",$bottom+49 + 16,"74");
	$pdf->drawLine($lineData);
	$data= $app_strings['LBL_TAX']." ($group_total_tax_percent %):\n";//                                                                  ".$price_salestax."";
	$pdf->SetXY( 127 , ($nettotal_y+(2*$next_y)) + 16 );
	$pdf->SetFont( "Helvetica", "", 10);
	$pdf->MultiCell(110, 4, $data);

	//Added for value field alignment
	$pdf->SetXY( $space[strlen($price_salestax)] -7 , ($nettotal_y+(2*$next_y)) + 16 );
	$pdf->SetFont( "Helvetica", "", 10);
	$pdf->MultiCell(110, 4, "$price_salestax $currency_symbol\n");

	/*$lineData=array("105",$bottom+55,"94");
	$pdf->drawLine($lineData);
	$data = $app_strings['LBL_SHIPPING_AND_HANDLING_CHARGES'].":";//                                  ".$price_shipping;
	$pdf->SetXY( 105 , ($nettotal_y+(3*$next_y)) );
	$pdf->SetFont( "Helvetica", "", 10);
	$pdf->MultiCell(110, 4, $data);*/

//Added for value field alignment
       /*$pdf->SetXY( $space[strlen($price_shipping)] , ($nettotal_y+(3*$next_y)) );
       $pdf->SetFont( "Helvetica", "", 10);
       $pdf->MultiCell(110, 4, $price_shipping);*/
}
else
{
	

}

//TM - Pinto una linea vertical para continuar el hueco dejado por los subtotales
$x=140;
$y=167;
$lon = 20;
$pdf->Line($x,$y,$x,$y + $lon);

//Esta solución no es para nada elegante pero trato de evitar dañar el cambio
if (isset($_SESSION['plat']) && ($_SESSION['plat'] == 'time' || $_SESSION['plat'] == 'empresafacil')) {
	$x=165;
	$y=167;
	$lon = 20;
	$pdf->Line($x,$y,$x,$y + $lon);


	$lineData=array("125",$bottom+73,"74");
} else {
	$x=138;
	$y=173;
	$lon = 30;
	$pdf->Line($x,$y,$x,$y + $lon);


	$lineData=array("10",$bottom+73,"189");
}
$pdf->drawLine($lineData);
$data = $app_strings['LBL_GRAND_TOTAL'].": ";//                                                    ".$price_total;
$pdf->SetXY( 127 , ($nettotal_y+(6*$next_y)) );
$pdf->SetFont( "Helvetica", "", 10);
$pdf->MultiCell(110, 4, $data);



//Added for value field alignment
$pdf->SetXY( $space[strlen($price_total)] -7 , ($nettotal_y+(6*$next_y)) );
$pdf->SetFont( "Helvetica", "", 10);
$pdf->MultiCell(110, 4, /*$price_total . */"$price_total $currency_symbol\n");

/* ************** End Totals *********************** */


?>
