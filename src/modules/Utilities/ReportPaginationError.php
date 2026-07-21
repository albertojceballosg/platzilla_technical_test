<?php
/*+**********************************************************************************
 * Reporte automático de errores de paginación en ListView
 * Registra en error_log y opcionalmente envía correo al administrador
 * Compatible con PHP 5.6+
 ************************************************************************************/

global $current_user, $adb;

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'unknown';
$userId = isset($current_user->id) ? $current_user->id : 0;

// Recopilar datos del error
$data = array(
    'type' => $type,
    'fecha' => date('Y-m-d H:i:s'),
    'usuario' => isset($current_user->user_name) ? $current_user->user_name : 'unknown',
    'usuario_id' => $userId,
    'modulo' => vtlib_purify(isset($_REQUEST['requestedModule']) ? $_REQUEST['requestedModule'] : 'unknown'),
    'responseLength' => intval(isset($_REQUEST['responseLength']) ? $_REQUEST['responseLength'] : 0),
    'htmlLength' => intval(isset($_REQUEST['htmlLength']) ? $_REQUEST['htmlLength'] : 0),
    'rowCount' => intval(isset($_REQUEST['rowCount']) ? $_REQUEST['rowCount'] : 0),
    'url' => vtlib_purify(isset($_REQUEST['requestedUrl']) ? $_REQUEST['requestedUrl'] : ''),
    'htmlSample' => isset($_REQUEST['htmlSample']) ? $_REQUEST['htmlSample'] : '',
    'navegador' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown',
    'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown',
    'servidor' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown'
);

// Siempre registrar en error_log
$logMessage = "[ListView Pagination] Type: {$data['type']} | ";
$logMessage .= "User: {$data['usuario']} | Module: {$data['modulo']} | ";
$logMessage .= "ResponseLen: {$data['responseLength']} | HtmlLen: {$data['htmlLength']} | Rows: {$data['rowCount']}";
error_log($logMessage);

// Si hay muestra de HTML, registrarla también (primeros 500 chars)
if (!empty($data['htmlSample'])) {
    $sampleClean = preg_replace('/\s+/', ' ', substr($data['htmlSample'], 0, 500));
    error_log("[ListView Pagination] HTML Sample: " . $sampleClean);
}

// Solo enviar correo para errores críticos (short_response después de reintentos)
if ($type === 'short_response') {
    // Control de frecuencia: máximo 1 correo cada 5 minutos por usuario
    $cacheKey = 'pagination_error_' . $userId;
    $lastReport = isset($_SESSION[$cacheKey]) ? $_SESSION[$cacheKey] : 0;
    $now = time();

    if (($now - $lastReport) < 300) {
        echo 'rate_limited';
        exit;
    }
    $_SESSION[$cacheKey] = $now;

    // Construir cuerpo del correo
    $body = "Se ha detectado un error de paginación en ListView (respuesta corta después de reintentos).\n\n";
    $body .= "=== DETALLES ===\n";
    $body .= "Fecha/Hora: {$data['fecha']}\n";
    $body .= "Servidor: {$data['servidor']}\n";
    $body .= "Usuario: {$data['usuario']} (ID: {$data['usuario_id']})\n";
    $body .= "Módulo: {$data['modulo']}\n";
    $body .= "IP: {$data['ip']}\n\n";
    $body .= "=== RESPUESTA ===\n";
    $body .= "Tamaño total: " . number_format($data['responseLength']) . " bytes\n";
    $body .= "Tamaño HTML: " . number_format($data['htmlLength']) . " bytes\n";
    $body .= "Filas detectadas: {$data['rowCount']}\n\n";
    $body .= "=== MUESTRA HTML ===\n";
    $body .= substr($data['htmlSample'], 0, 500) . "\n\n";
    $body .= "=== URL ===\n{$data['url']}\n";

    $to = 'ggranados@timemanagement.es';
    $subject = '[Platzilla] Error paginación - ' . $data['modulo'];
    $headers = "From: noreply@{$data['servidor']}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    
    @mail($to, $subject, $body, $headers);
}

echo 'logged';
