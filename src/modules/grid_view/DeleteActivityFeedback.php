<?php
/*************************************************************************************************
 * Platzilla - DeleteActivityFeedback
 * Elimina un feedback de actividad de la base de datos
 *************************************************************************************************/

// Limpiar cualquier salida buffer anterior
if (ob_get_length()) ob_clean();

require_once('include/database/PearDatabase.php');

global $adb, $current_user;

// Verificar que el usuario esté autenticado
if (empty($current_user)) {
    $response = array(
        'success' => false,
        'error' => 'Usuario no autenticado'
    );
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

// Obtener parámetros
$feedbackId = isset($_REQUEST['feedbackid']) ? intval($_REQUEST['feedbackid']) : 0;
$activityId = isset($_REQUEST['activityid']) ? intval($_REQUEST['activityid']) : 0;

// Validar parámetros
if (empty($feedbackId)) {
    $response = array(
        'success' => false,
        'error' => 'ID de feedback no proporcionado'
    );
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

try {
    // Verificar que el feedback existe y obtener el usuario propietario
    $checkSql = "SELECT userid FROM vtiger_activity_feedback WHERE activityfeedbackid = ?";
    $checkResult = $adb->pquery($checkSql, array($feedbackId));
    
    if ($adb->num_rows($checkResult) == 0) {
        $response = array(
            'success' => false,
            'error' => 'Feedback no encontrado'
        );
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
    
    $feedbackUserId = $adb->query_result($checkResult, 0, 'userid');
    
    // Verificar permisos: el usuario debe ser el propietario del feedback o administrador
    $isOwner = ($current_user->id == $feedbackUserId);
    $isAdmin = (isset($current_user->is_admin) && $current_user->is_admin == 'on');
    
    if (!$isOwner && !$isAdmin) {
        $response = array(
            'success' => false,
            'error' => 'No tiene permisos para eliminar este feedback'
        );
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
    
    // Eliminar relaciones del feedback con reportes (si existen)
    $deleteRelationSql = "DELETE FROM vtiger_activity_report2feedback WHERE activityfeedbackid = ?";
    $adb->pquery($deleteRelationSql, array($feedbackId));
    
    // Eliminar el feedback
    $deleteSql = "DELETE FROM vtiger_activity_feedback WHERE activityfeedbackid = ?";
    $adb->pquery($deleteSql, array($feedbackId));
    
    // Respuesta exitosa
    $response = array(
        'success' => true,
        'message' => 'Feedback eliminado exitosamente'
    );
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = array(
        'success' => false,
        'error' => 'Error al eliminar el feedback: ' . $e->getMessage()
    );
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
}

// Asegurar que no haya salida adicional
exit;
