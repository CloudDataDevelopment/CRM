<?php

namespace app\components;

use Yii;
use yii\web\Response;
use yii\db\Exception as DbException;
use yii\base\Exception;

class ErrorManager
{
    // Tipos de errores
    const TYPE_DATABASE = 'database';
    const TYPE_VALIDATION = 'validation';
    const TYPE_NOT_FOUND = 'not_found';
    const TYPE_PERMISSION = 'permission';
    const TYPE_CONNECTION = 'connection';
    const TYPE_GENERAL = 'general';
    
    // Mensajes por defecto
    private static $messages = [
        'database' => 'Error de base de datos. Contacta al administrador.',
        'validation' => 'Error de validación. Revisa los datos ingresados.',
        'not_found' => 'El elemento solicitado no existe.',
        'permission' => 'No tienes permisos para realizar esta acción.',
        'connection' => 'Error de conexión. Verifica tu conexión a internet.',
        'general' => 'Ocurrió un error inesperado. Intenta nuevamente.',
    ];
    
    /**
     * Clasificar y manejar error
     */
    public static function handle($exception, $customMessage = null, $redirect = null)
    {
        // Registrar en log
        self::log($exception);
        
        // Clasificar el error
        $type = self::classify($exception);
        
        // Obtener mensaje
        $message = $customMessage ?? self::getMessage($type, $exception);
        
        // Mostrar al usuario
        self::display($type, $message);
        
        // Si se especifica redirección, redirigir
        if ($redirect) {
            Yii::$app->response->redirect($redirect)->send();
            return null;
        }
        
        // Retornar para usar en AJAX
        return self::getResponse($type, $message);
    }
    
    /**
     * Clasificar el tipo de error
     */
    private static function classify($exception)
    {
        $message = $exception->getMessage();
        
        // Errores de base de datos
        if ($exception instanceof DbException ||
            strpos($message, 'SQLSTATE') !== false ||
            strpos($message, 'database') !== false ||
            strpos($message, 'MySQL') !== false ||
            strpos($message, 'PDO') !== false) {
            return self::TYPE_DATABASE;
        }
        
        // Errores de conexión
        if (strpos($message, 'connection') !== false ||
            strpos($message, 'conexión') !== false ||
            strpos($message, 'host') !== false ||
            strpos($message, 'timeout') !== false ||
            strpos($message, 'respond') !== false) {
            return self::TYPE_CONNECTION;
        }
        
        // Errores de validación
        if (strpos($message, 'validation') !== false ||
            strpos($message, 'validate') !== false ||
            strpos($message, 'required') !== false ||
            strpos($message, 'invalid') !== false) {
            return self::TYPE_VALIDATION;
        }
        
        // Errores de "no encontrado"
        if ($exception->getCode() == 404 ||
            strpos($message, 'not found') !== false ||
            strpos($message, 'no encontrado') !== false) {
            return self::TYPE_NOT_FOUND;
        }
        
        // Errores de permisos
        if ($exception->getCode() == 403 ||
            strpos($message, 'permission') !== false ||
            strpos($message, 'access') !== false ||
            strpos($message, 'denied') !== false) {
            return self::TYPE_PERMISSION;
        }
        
        return self::TYPE_GENERAL;
    }
    
    /**
     * Obtener mensaje según el tipo
     */
    private static function getMessage($type, $exception)
    {
        // En desarrollo mostrar mensaje completo
        if (YII_DEBUG) {
            return $exception->getMessage() . ' (Línea: ' . $exception->getLine() . ')';
        }
        
        // En producción mostrar mensaje amigable
        $baseMessage = self::$messages[$type] ?? self::$messages['general'];
        
        switch ($type) {
            case self::TYPE_DATABASE:
                return 'Error de base de datos: ' . $baseMessage;
            case self::TYPE_CONNECTION:
                return 'No se pudo conectar al servidor. ' . $baseMessage;
            case self::TYPE_VALIDATION:
                return $baseMessage;
            default:
                return $baseMessage;
        }
    }
    
    /**
     * Registrar en el log
     */
    private static function log($exception)
    {
        $type = self::classify($exception);
        $message = "[{$type}] " . $exception->getMessage();
        Yii::error($message . "\n" . $exception->getTraceAsString(), 'app\error');
    }
    
    /**
     * Mostrar mensaje al usuario
     */
    private static function display($type, $message)
    {
        // Si es petición AJAX, retornar JSON
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'success' => false,
                'type' => $type,
                'message' => $message,
            ];
            Yii::$app->response->send();
            return;
        }
        
        // Colores según tipo
        $flashType = 'error';
        $icon = 'exclamation-triangle';
        
        if ($type == self::TYPE_DATABASE) {
            $flashType = 'danger';
            $icon = 'database';
        } elseif ($type == self::TYPE_VALIDATION) {
            $flashType = 'warning';
            $icon = 'check-circle';
        } elseif ($type == self::TYPE_NOT_FOUND) {
            $flashType = 'info';
            $icon = 'search';
        } elseif ($type == self::TYPE_PERMISSION) {
            $flashType = 'danger';
            $icon = 'lock';
        } elseif ($type == self::TYPE_CONNECTION) {
            $flashType = 'danger';
            $icon = 'wifi';
        }
        
        // Guardar en sesión para mostrar en la vista
        Yii::$app->session->setFlash($flashType, $message);
        Yii::$app->session->set('error_type', $type);
        Yii::$app->session->set('error_icon', $icon);
    }
    
    /**
     * Obtener respuesta para AJAX/JSON
     */
    private static function getResponse($type, $message)
    {
        if (Yii::$app->request->isAjax) {
            return [
                'success' => false,
                'type' => $type,
                'message' => $message,
            ];
        }
        
        return null;
    }
    
    /**
     * Ejecutar función con manejo de errores
     */
    public static function execute($callback, $customMessage = null, $redirect = null)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return self::handle($e, $customMessage, $redirect);
        }
    }
    
    /**
     * Ejecutar función con manejo de errores y retorno de valor
     */
    public static function try($callback, $default = null, $customMessage = null, $redirect = null)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            self::handle($e, $customMessage, $redirect);
            return $default;
        }
    }
    
    /**
     * Mostrar mensaje de error en la vista
     */
    public static function showError()
    {
        $type = Yii::$app->session->get('error_type');
        $icon = Yii::$app->session->get('error_icon', 'exclamation-triangle');
        
        if ($type) {
            $message = Yii::$app->session->getFlash('error');
            if ($message) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo '<i class="fas fa-' . $icon . '"></i> ';
                echo $message;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
            }
        }
    }
}