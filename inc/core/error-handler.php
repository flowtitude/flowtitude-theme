<?php
if (!defined('ABSPATH')) exit;

/**
 * Clase singleton para el manejo centralizado de errores en Flowtitude.
 *
 * Uso recomendado:
 * $handler = Flowtitude_Error_Handler::get_instance();
 * $response = $handler->handle_error($error, 'Contexto', 400);
 * // Para REST: return new WP_Error('flowtitude_error', $response['message'], ['context' => $response['debug'] ?? null]);
 */
class Flowtitude_Error_Handler {
    private static $instance = null;
    private $debug_mode;

    /**
     * Constructor privado. Inicializa el modo debug.
     */
    private function __construct() {
        $this->debug_mode = defined('WP_DEBUG') && WP_DEBUG;
    }

    /**
     * Devuelve la instancia singleton de la clase.
     *
     * @return Flowtitude_Error_Handler
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Maneja un error y retorna una respuesta formateada
     */
    /**
     * Maneja un error y retorna una respuesta formateada y segura.
     *
     * @param mixed $error El error (string, Exception, WP_Error)
     * @param string $context Contexto adicional
     * @param int $status Código de estado HTTP sugerido
     * @return array Respuesta estructurada para API o UI
     */
    public function handle_error($error, $context = '', $status = 400) {
        // Registrar el error
        $this->log_error($error, $context);

        // Determinar el mensaje de error
        $message = $this->get_error_message($error);

        // En modo debug, incluir más información
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($this->debug_mode) {
            $response['debug'] = [
                'context' => $context,
                'error' => $error instanceof WP_Error ? $error->get_error_messages() : $error,
                'timestamp' => current_time('mysql')
            ];
        }

        return $response;
    }

    /**
     * Registra un error en el log
     */
    /**
     * Registra un error en el log de Flowtitude usando flowtitude_debug_log.
     *
     * @param mixed $error
     * @param string $context
     * @return void
     */
    private function log_error($error, $context = '') {
        $error_message = $this->get_error_message($error);
        $log_message = sprintf(
            '[Flowtitude Error][%s] %s | Context: %s',
            current_time('mysql'),
            $error_message,
            $context
        );

        if ($error instanceof Exception) {
            $log_message .= "\nStack Trace: " . $error->getTraceAsString();
        }

        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log($log_message, 'error');
        } else {
            error_log($log_message);
        }
    }

    /**
     * Obtiene un mensaje seguro y legible del error recibido.
     * Nunca expone rutas internas ni información sensible.
     *
     * @param mixed $error
     * @return string
     */
    private function get_error_message($error) {
        if ($error instanceof WP_Error) {
            $messages = $error->get_error_messages();
            $safe_messages = array_map(function($msg) {
                // Elimina rutas absolutas y datos sensibles
                return preg_replace('/\/[\w\-\.\/]+/', '[ruta_oculta]', $msg);
            }, $messages);
            return implode(' | ', $safe_messages);
        } elseif ($error instanceof Exception) {
            // No mostrar trace ni rutas internas
            return preg_replace('/\/[\w\-\.\/]+/', '[ruta_oculta]', $error->getMessage());
        } elseif (is_string($error)) {
            return preg_replace('/\/[\w\-\.\/]+/', '[ruta_oculta]', $error);
        } else {
            return 'Error desconocido';
        }
    }

    /**
     * Verifica si una operación es segura
     *
     * @param string $type Tipo de operación
     * @param array $data Datos adicionales para la operación
     * @return bool|WP_Error Verdadero si la operación es segura, WP_Error en caso contrario
     */
    public function verify_operation($type, $data = []) {
        switch ($type) {
            case 'file_operation':
                return $this->verify_file_operation($data);
            case 'api_request':
                return $this->verify_api_request($data);
            default:
                return new WP_Error('invalid_operation', 'Tipo de operación no válido');
        }
    }

    /**
     * Verifica una operación con archivos
     */
    private function verify_file_operation($data) {
        if (empty($data['path'])) {
            return new WP_Error('missing_path', 'Ruta no especificada');
        }

        // Verificar que la ruta está dentro del directorio permitido
        $allowed_dirs = [
            WP_CONTENT_DIR . '/uploads/flowtitude',
            get_template_directory() . '/snippets',
            get_template_directory() . '/bricks'
        ];

        $real_path = realpath($data['path']);
        $is_allowed = false;

        foreach ($allowed_dirs as $dir) {
            if (strpos($real_path, realpath($dir)) === 0) {
                $is_allowed = true;
                break;
            }
        }

        if (!$is_allowed) {
            return new WP_Error('invalid_path', 'Ruta no permitida');
        }

        return true;
    }

    /**
     * Verifica una petición API
     */
    private function verify_api_request($data) {
        if (!check_ajax_referer('flowtitude_nonce', 'nonce', false)) {
            return new WP_Error('invalid_nonce', 'Nonce inválido');
        }

        if (!current_user_can('manage_options')) {
            return new WP_Error('insufficient_permissions', 'Permisos insuficientes');
        }

        return true;
    }
} 