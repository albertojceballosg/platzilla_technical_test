<?php
require_once('modules/Courses/lib/CoursesInterface.php');

/**
 * Class ExerciseTracker
 * 
 * Esta clase gestiona el seguimiento de los ejercicios de los usuarios, incluyendo el registro de visitas, la actualización del estado de los ejercicios y la gestión de archivos adjuntos.
 * 
 * @author GGC / Winsurf
 * @copyright Todos los derechos reservados. Time Management
 * @since Versión: 1.0  Creada: 02-03-2025
 * @version documentación: 1.0 Revisión: 02-03-2025
 */
class ExerciseTracker implements CoursesInterface {
	
    /**function registerExerciseVisit
     * Registra que un usuario ha visitado un ejercicio
     * 
     * @param object $adb Conexión a la base de datos
     * @param integer $lessonId ID de la lección
     * @param integer $exerciseId ID del ejercicio
     * @param integer $userId ID del usuario
     * @return boolean Estado de éxito
     * 
     * @author GGC / Winsurf
     * @copyright Todos los derechos reservados. Time Management
     * @since Versión: 1.0
     * @version documentación: 1.0 Revisión: 02-03-2025
     */
    public static function registerExerciseVisit($adb, $lessonId, $exerciseId, $userId) {
        try {
            $currentTime = date('Y-m-d H:i:s');
            
            // Check if record already exists
            $result = $adb->pquery('SELECT id, status FROM vtiger_lesson_exercise2user 
                        WHERE lessonid = ? AND userid = ? AND exerciseid = ?', 
                        array($lessonId, $userId, $exerciseId));
            
            if ($result && $adb->num_rows($result) > 0) {
                // Record exists, only update if not already EXERCISE_DONE
                $row = $adb->fetch_array($result);
                if ($row && $row['status'] !== 'EXERCISE_DONE') {
                    $adb->pquery('UPDATE vtiger_lesson_exercise2user 
                                SET modifiedtime = ? 
                                WHERE lessonid = ? AND userid = ? AND exerciseid = ?', 
                                array($currentTime, $lessonId, $userId, $exerciseId));
                }
            } else {
                // Create new record
                $adb->pquery('INSERT INTO vtiger_lesson_exercise2user 
                            (lessonid, exerciseid, userid, status, createdtime, modifiedtime) 
                            VALUES (?, ?, ?, ?, ?, ?)', 
                            array($lessonId, $exerciseId, $userId, 'EXERCISE_VISITED', $currentTime, $currentTime));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in registerExerciseVisit: " . $e->getMessage());
            return false;
        }
    }

    /**checkAndUpdateExerciseStatus
     * Verifica si un ejercicio tiene archivos adjuntos y actualiza el estado si es necesario
     * 
     * @param object $adb Conexión a la base de datos
     * @param integer $exerciseId ID del ejercicio
     * @param integer $lessonId ID de la lección
     * @param integer $userId ID del usuario
     * @return boolean Estado de éxito
     * 
     * @author GGC / Winsurf
     * @copyright Todos los derechos reservados. Time Management
     * @since Versión: 1.0  Creada: 02-03-2025
     * @version documentación: 1.0 Revisión: 02-03-2025
     */
    public static function checkAndUpdateExerciseStatus($adb, $exerciseId, $lessonId, $userId) {
        try {
            // Check if exercise has attachments
            $result = $adb->pquery('SELECT COUNT(*) as count FROM vtiger_attachments2exercises 
                             WHERE lessonid = ? AND userid = ?', 
                             array($lessonId, $userId));
            
            if (!$result) {
                error_log("Error querying attachments");
                return false;
            }

            $hasAttachments = (int)$adb->query_result($result, 0, 'count') > 0;

            if ($hasAttachments) {
                $currentTime = date('Y-m-d H:i:s');
                
                // Update exercise status to DONE
                $result1 = $adb->pquery('UPDATE vtiger_lesson_exercise2user 
                             SET status = ?, modifiedtime = ? 
                             WHERE lessonid = ? AND userid = ? AND exerciseid = ?', 
                             array('EXERCISE_DONE', $currentTime, $lessonId, $userId, $exerciseId));

                // Obtener el ID del último registro de la lección para este usuario
                $result = $adb->pquery(
                    'SELECT MAX(lesson2userid) as latest_id FROM vtiger_lessons2user 
                     WHERE lessonid = ? AND userid = ?',
                    array($lessonId, $userId)
                );
                $latestId = $adb->query_result($result, 0, 'latest_id');

                // Update only the latest lesson record
                $result2 = $adb->pquery(
                    'UPDATE vtiger_lessons2user 
                     SET status = ?, end_date = ? 
                     WHERE lesson2userid = ?', 
                    array('LESSON_PASSED', $currentTime, $latestId)
                );
                
                if (!$result1 || !$result2) {
                    error_log("Error updating exercise or lesson status");
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in checkAndUpdateExerciseStatus: " . $e->getMessage());
            return false;
        }
    }

    /**getExerciseStatus
     * Obtiene el estado actual de un ejercicio para un usuario
     * 
     * @param object $adb Conexión a la base de datos
     * @param integer $lessonId ID de la lección
     * @param integer $userId ID del usuario
     * @return string Código de estado o null si no se encuentra
     * 
     * @author GGC / Winsurf
     * @copyright Todos los derechos reservados. Time Management
     * @since Versión: 1.0 Creada: 02-03-2025
     * @version documentación: 1.0 Revisión: 02-03-2025
     */
    public static function getExerciseStatus($adb, $lessonId, $userId) {
        try {
            $result = $adb->pquery('SELECT status FROM vtiger_lesson_exercise2user 
                    WHERE lessonid = ? AND userid = ?', 
                    array($lessonId, $userId));
            
            if ($result && $adb->num_rows($result) > 0) {
                return $adb->query_result($result, 0, 'status');
            }
            return null;
        } catch (Exception $e) {
            error_log("Error in getExerciseStatus: " . $e->getMessage());
            return null;
        }
    }

    /**saveExercisesAttachment
     * Guarda un archivo adjunto de ejercicio y actualiza el estado del ejercicio
     * 
     * @param PearDatabase $masterAdb Conexión a la base de datos maestra
     * @param PearDatabase $adb Conexión a la base de datos secundaria
     * @param integer $exercisesId ID del ejercicio
     * @param integer $attachmentId ID del archivo adjunto
     * @param integer $userId ID del usuario
     * @throws Exception
     * 
     * @author GGC / Winsurf
     * @copyright Todos los derechos reservados. Time Management
     * @since Versión: 1.0  Creada: 02-03-2025
     * @version documentación: 1.0 Revisión: 02-03-2025
     */
    public static function saveExercisesAttachment($masterAdb, $adb, $exercisesId, $attachmentId, $userId) {
        // Obtener información del ejercicio
        $result = $masterAdb->pquery(
            'SELECT lessonid FROM vtiger_lessons2exercises WHERE lesson2exercisesid=?',
            array($exercisesId)
        );
        if (!$masterAdb->num_rows($result)) {
            throw new Exception('imposible obtener el id de la lección asociada al ejercicio');
        }
        $row = $masterAdb->fetchByAssoc($result, -1, false);
        $lessonId = $row['lessonid'];
        DatabaseUtils::closeResult($result);
        $result = null;

        // Guardar el archivo adjunto
        $adb->pquery(
            'INSERT INTO vtiger_attachments2exercises (exercisesid, lessonid, attachmentsid, userid, dt_created) VALUES (?, ?, ?, ?, NOW())',
            array($exercisesId, $lessonId, $attachmentId, $userId)
        );

        $currentTime = date('Y-m-d H:i:s');

        // Verificar si ya existe un registro para este ejercicio
        $result = $adb->pquery(
            'SELECT status, createdtime FROM vtiger_lesson_exercise2user 
             WHERE lessonid = ? AND userid = ? AND exerciseid = ?',
            array($lessonId, $userId, $exercisesId)
        );

        if ($adb->num_rows($result) == 0) {
            // Si no existe, crear el registro
            $adb->pquery(
                'INSERT INTO vtiger_lesson_exercise2user 
                 (lessonid, exerciseid, userid, status, createdtime, modifiedtime) 
                 VALUES (?, ?, ?, ?, ?, ?)',
                array($lessonId, $exercisesId, $userId, 'EXERCISE_DONE', $currentTime, $currentTime)
            );
        } else {
            // Si existe, actualizar el estado y la fecha de modificación
            $row = $adb->fetch_array($result);
            $adb->pquery(
                'UPDATE vtiger_lesson_exercise2user 
                 SET status = ?, modifiedtime = ? 
                 WHERE lessonid = ? AND userid = ? AND exerciseid = ?',
                array('EXERCISE_DONE', $currentTime, $lessonId, $userId, $exercisesId)
            );
        }

        // Verificar si todos los ejercicios de la lección están completados
        $result = $masterAdb->pquery(
            'SELECT COUNT(*) as total FROM vtiger_lessons2exercises WHERE lessonid = ?',
            array($lessonId)
        );
        $totalExercises = $masterAdb->query_result($result, 0, 'total');

        $result = $adb->pquery(
            'SELECT COUNT(*) as completed FROM vtiger_lesson_exercise2user 
             WHERE lessonid = ? AND userid = ? AND status = ?',
            array($lessonId, $userId, 'EXERCISE_DONE')
        );
        $completedExercises = $adb->query_result($result, 0, 'completed');

        // Si todos los ejercicios están completados, actualizar el estado de la lección
        if ($totalExercises == $completedExercises) {
            // Obtener el ID del último registro de la lección para este usuario
            $result = $adb->pquery(
                'SELECT MAX(lesson2userid) as latest_id FROM vtiger_lessons2user 
                 WHERE lessonid = ? AND userid = ?',
                array($lessonId, $userId)
            );
            $latestId = $adb->query_result($result, 0, 'latest_id');

            // Actualizar solo el último registro
            $adb->pquery(
                'UPDATE vtiger_lessons2user SET status = ? WHERE lesson2userid = ?',
                array('LESSON_PASSED', $latestId)
            );
        }
    }
}
