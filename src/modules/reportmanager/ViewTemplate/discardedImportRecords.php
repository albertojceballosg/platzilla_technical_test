<?php
	$tamplateName  = date('YmdHis').'_discarded_report.pdf';
	$donwLoadField = $tamplateName;
	$_SESSION ['pdf_html'] = '';
	include 'modules/Import/index.php';
	$html = $_SESSION ['pdf_html'];
	unset($_SESSION['pdf_html']);