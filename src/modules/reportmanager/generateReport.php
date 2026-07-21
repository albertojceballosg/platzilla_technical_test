<?php

require_once('snappy/vendor/autoload.php');

use Knp\Snappy\Pdf;

$snappy = new Pdf('C:/"Program Files"/wkhtmltopdf/bin/wkhtmltopdf');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="testPDF.pdf"');
echo $snappy->getOutput(header("Location: index.php?module=reportmanager&action=View&parenttab=Settings&idview=template_myinvoice&page=1"));


?>
