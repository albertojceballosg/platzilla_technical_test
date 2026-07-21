<?php
/**
 * CancelCreate.php
 * Maneja la cancelación de creación de registros
 * Evalúa notificaciones CANCEL_RECORD y redirige apropiadamente
 */

require_once('include/database/PearDatabase.php');
require_once('modules/notifications/lib/NotificationUtils.class.php');
require_once('include/platzilla/Objects/NotificationInterface.php');

global $adb, $current_user, $currentModule;

$returnAction = isset($_REQUEST['return_action']) ? vtlib_purify($_REQUEST['return_action']) : 'ListView';
$returnModule = isset($_REQUEST['return_module']) ? vtlib_purify($_REQUEST['return_module']) : $currentModule;

// Preparar datos para buscar notificaciones CANCEL_RECORD
$notificationDataModal = array(
	'module'   => $currentModule,
	'user'     => $current_user,
	'view'     => Notification::EDIT_VIEW,
	'style'    => Notification::STYLE_MODAL,
	'recordId' => null, // No hay record en modo creación
	'mode'     => 'cancel',
	'platform' => $_SESSION['plat'],
);

// Buscar notificación CANCEL_RECORD aplicable
$modalId = NotificationUtils::fetchApplicableOnScreenNotificationsModal($adb, $notificationDataModal);

if (!empty($modalId)) {
	// Hay notificación CANCEL_RECORD: Redirigir a una vista que la muestre
	// Usamos una página intermedia que mostrará el modal y luego redirigirá
	header("Location: index.php?module=Vtiger&action=CancelCreateView&formodule={$currentModule}&return_action={$returnAction}&return_module={$returnModule}&modalId={$modalId}");
} else {
	// No hay notificación: Redirigir directamente
	header("Location: index.php?module={$returnModule}&action={$returnAction}");
}
exit();
