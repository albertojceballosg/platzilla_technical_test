<?
/**$fp = fsockopen("localhost", 8080, $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = "GET / HTTP/1.1\r\n";
    $out .= "Host: localhost\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
}**/
?>
<iframe width="100%" height="1024" src="http://www.timemanagement.es:8081/nuxeo/" frameborder="1">

</iframe>