<?php
/**
 * Sistema de Validación de Archivos para Flowtitude
 * 
 * Este archivo implementa un sistema robusto de validación de seguridad
 * que verifica cada archivo PHP antes de cargarlo, previniendo la ejecución
 * de código malicioso y asegurando que solo se carguen archivos desde
 * ubicaciones permitidas.
 * 
 * @package Flowtitude
 * @version 2.0.0
 * @author Ángel Julián
 * @since 2025-01-27
 */

if (!defined('ABSPATH')) exit;

/**
 * Clase principal para la validación de archivos
 * 
 * Proporciona métodos para validar archivos PHP antes de cargarlos,
 * detectar funciones peligrosas y asegurar que los archivos estén
 * en directorios permitidos.
 */
class Flowtitude_File_Validator {
    
    /**
     * Tipos de archivos permitidos para carga
     * 
     * @var array
     */
    private static $allowed_extensions = ['php'];
    
    /**
     * Directorios permitidos para carga de archivos
     * 
     * @var array
     */
    private static $allowed_directories = [];
    
    /**
     * Inicializa los directorios permitidos para la validación
     * 
     * Se ejecuta automáticamente al cargar la clase. Los directorios
     * personalizados se añaden después de que las funciones helper
     * estén disponibles.
     * 
     * @return void
     */
    public static function init() {
        if (defined('FLOWTITUDE_DIR')) {
            self::$allowed_directories = [
                FLOWTITUDE_DIR . '/inc',
                FLOWTITUDE_DIR . '/snippets',
                FLOWTITUDE_DIR . '/admin-panel'
            ];
            
            // Añadir directorios personalizados si las funciones están disponibles
            if (function_exists('flowtitude_get_custom_dir')) {
                self::$allowed_directories[] = flowtitude_get_custom_dir('snippets');
                self::$allowed_directories[] = flowtitude_get_custom_dir('bricks');
            }
        }
    }
    
    /**
     * Valida un archivo antes de cargarlo
     * 
     * Realiza todas las validaciones de seguridad necesarias:
     * - Verifica que el archivo existe
     * - Comprueba permisos de lectura
     * - Valida la extensión del archivo
     * - Verifica que está en un directorio permitido
     * - Detecta funciones peligrosas en el contenido
     * 
     * @param string $file_path Ruta del archivo a validar
     * @param string $context Contexto para logging (ej: 'core', 'snippets', 'admin')
     * @param bool $strict Si es true, solo permite archivos PHP
     * @return array Array con resultado de la validación:
     *               - 'valid' => bool: Si el archivo es válido
     *               - 'error' => string|null: Mensaje de error si no es válido
     *               - 'real_path' => string|null: Ruta real del archivo
     */
    public static function validate_file($file_path, $context = 'unknown', $strict = true) {
        $result = [
            'valid' => false,
            'error' => null,
            'real_path' => null
        ];
        
        // Validación básica de entrada
        if (empty($file_path) || !is_string($file_path)) {
            $result['error'] = 'Ruta de archivo inválida';
            self::log_validation_error($file_path, $result['error'], $context);
            return $result;
        }
        
        // Obtener ruta real (resuelve enlaces simbólicos)
        $real_path = realpath($file_path);
        if (!$real_path) {
            $result['error'] = 'Archivo no encontrado';
            self::log_validation_error($file_path, $result['error'], $context);
            return $result;
        }
        
        $result['real_path'] = $real_path;
        
        // Verificar que es un archivo (no directorio)
        if (!is_file($real_path)) {
            $result['error'] = 'No es un archivo válido';
            self::log_validation_error($file_path, $result['error'], $context);
            return $result;
        }
        
        // Verificar permisos de lectura
        if (!is_readable($real_path)) {
            $result['error'] = 'Archivo no legible';
            self::log_validation_error($file_path, $result['error'], $context);
            return $result;
        }
        
        // Validar extensión si es modo estricto
        if ($strict) {
            $extension = strtolower(pathinfo($real_path, PATHINFO_EXTENSION));
            if (!in_array($extension, self::$allowed_extensions)) {
                $result['error'] = "Extensión no permitida: $extension";
                self::log_validation_error($file_path, $result['error'], $context);
                return $result;
            }
        }
        
        // Validar que está en directorio permitido
        if (!self::is_in_allowed_directory($real_path)) {
            $result['error'] = 'Archivo fuera de directorios permitidos';
            self::log_validation_error($file_path, $result['error'], $context);
            return $result;
        }
        
        // Validación de contenido básica (solo para archivos PHP)
        if ($strict && pathinfo($real_path, PATHINFO_EXTENSION) === 'php') {
            $content_validation = self::validate_php_content($real_path);
            if (!$content_validation['valid']) {
                $result['error'] = $content_validation['error'];
                self::log_validation_error($file_path, $result['error'], $context);
                return $result;
            }
        }
        
        $result['valid'] = true;
        self::log_validation_success($file_path, $context);
        return $result;
    }
    
    /**
     * Verifica si un archivo está en un directorio permitido
     * 
     * Compara la ruta real del archivo con la lista de directorios
     * permitidos para asegurar que no se carguen archivos desde
     * ubicaciones no autorizadas.
     * 
     * @param string $file_path Ruta real del archivo
     * @return bool True si el archivo está en un directorio permitido
     */
    private static function is_in_allowed_directory($file_path) {
        foreach (self::$allowed_directories as $allowed_dir) {
            $real_allowed_dir = realpath($allowed_dir);
            if ($real_allowed_dir && strpos($file_path, $real_allowed_dir) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Valida el contenido básico de un archivo PHP
     * 
     * Lee las primeras líneas del archivo para realizar validaciones
     * básicas de seguridad:
     * - Verifica que contiene <?php
     * - Detecta funciones peligrosas
     * 
     * @param string $file_path Ruta del archivo
     * @return array Array con resultado de la validación:
     *               - 'valid' => bool: Si el contenido es válido
     *               - 'error' => string|null: Mensaje de error si no es válido
     */
    private static function validate_php_content($file_path) {
        $result = ['valid' => false, 'error' => null];
        
        // Leer las primeras líneas para validación básica (1KB)
        $content = file_get_contents($file_path, false, null, 0, 1024);
        if ($content === false) {
            $result['error'] = 'No se pudo leer el contenido del archivo';
            return $result;
        }
        
        // Verificar que contiene <?php o <?=
        if (strpos($content, '<?php') === false && strpos($content, '<?=') === false) {
            $result['error'] = 'Archivo PHP inválido (falta <?php)';
            return $result;
        }
        
        // Verificar que no contiene funciones peligrosas
        $dangerous_functions = ['eval', 'exec', 'system', 'shell_exec', 'passthru'];
        foreach ($dangerous_functions as $func) {
            if (preg_match('/\b' . preg_quote($func) . '\s*\(/', $content)) {
                $result['error'] = "Archivo contiene función peligrosa: $func";
                return $result;
            }
        }
        
        $result['valid'] = true;
        return $result;
    }
    
    /**
     * Carga un archivo de forma segura usando include_once
     * 
     * Valida el archivo antes de cargarlo y registra el resultado
     * en los logs. Si la validación falla, no carga el archivo.
     * 
     * @param string $file_path Ruta del archivo
     * @param string $context Contexto para logging
     * @param bool $strict Si es true, valida estrictamente
     * @return bool True si se cargó correctamente, False en caso contrario
     */
    public static function safe_include($file_path, $context = 'unknown', $strict = true) {
        $validation = self::validate_file($file_path, $context, $strict);
        
        if (!$validation['valid']) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log("No se pudo cargar archivo: {$validation['error']}", 'error', $context);
            }
            return false;
        }
        
        try {
            include_once $validation['real_path'];
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log("Archivo cargado correctamente: {$validation['real_path']}", 'success', $context);
            }
            return true;
        } catch (Exception $e) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log("Error al cargar archivo: " . $e->getMessage(), 'error', $context);
            }
            return false;
        }
    }
    
    /**
     * Carga un archivo de forma segura usando require_once
     * 
     * Similar a safe_include pero usa require_once. Si la validación
     * falla, no carga el archivo. Si el archivo es requerido pero
     * no se puede cargar, se registra un error crítico.
     * 
     * @param string $file_path Ruta del archivo
     * @param string $context Contexto para logging
     * @param bool $strict Si es true, valida estrictamente
     * @return bool True si se cargó correctamente, False en caso contrario
     */
    public static function safe_require($file_path, $context = 'unknown', $strict = true) {
        $validation = self::validate_file($file_path, $context, $strict);
        
        if (!$validation['valid']) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log("No se pudo cargar archivo requerido: {$validation['error']}", 'error', $context);
            }
            return false;
        }
        
        try {
            require_once $validation['real_path'];
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log("Archivo requerido cargado correctamente: {$validation['real_path']}", 'success', $context);
            }
            return true;
        } catch (Exception $e) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log("Error al cargar archivo requerido: " . $e->getMessage(), 'error', $context);
            }
            return false;
        }
    }
    
    /**
     * Actualiza los directorios permitidos con directorios personalizados
     * 
     * Se debe llamar después de que las funciones helper estén disponibles
     * para añadir los directorios de snippets y bricks personalizados
     * a la lista de directorios permitidos.
     * 
     * @return void
     */
    public static function update_allowed_directories() {
        if (function_exists('flowtitude_get_custom_dir')) {
            $custom_snippets = flowtitude_get_custom_dir('snippets');
            $custom_bricks = flowtitude_get_custom_dir('bricks');
            
            if (!in_array($custom_snippets, self::$allowed_directories)) {
                self::$allowed_directories[] = $custom_snippets;
            }
            if (!in_array($custom_bricks, self::$allowed_directories)) {
                self::$allowed_directories[] = $custom_bricks;
            }
        }
    }
    
    /**
     * Registra errores de validación en el sistema de logging
     * 
     * @param string $file_path Ruta del archivo que falló la validación
     * @param string $error Mensaje de error
     * @param string $context Contexto de la validación
     * @return void
     */
    private static function log_validation_error($file_path, $error, $context) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log("Validación de archivo fallida: $file_path - $error", 'warning', $context);
        }
    }
    
    /**
     * Registra validaciones exitosas en el sistema de logging
     * 
     * @param string $file_path Ruta del archivo validado
     * @param string $context Contexto de la validación
     * @return void
     */
    private static function log_validation_success($file_path, $context) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log("Archivo validado correctamente: $file_path", 'debug', $context);
        }
    }
}

// Inicializar el validador solo con directorios básicos
Flowtitude_File_Validator::init();

/**
 * Función helper para cargar archivos de forma segura usando include_once
 * 
 * Wrapper de la función safe_include de la clase Flowtitude_File_Validator
 * para facilitar el uso en el código del tema.
 * 
 * @param string $file_path Ruta del archivo
 * @param string $context Contexto para logging
 * @return bool True si se cargó correctamente
 */
function flowtitude_safe_include($file_path, $context = 'unknown') {
    return Flowtitude_File_Validator::safe_include($file_path, $context);
}

/**
 * Función helper para cargar archivos de forma segura usando require_once
 * 
 * Wrapper de la función safe_require de la clase Flowtitude_File_Validator
 * para facilitar el uso en el código del tema.
 * 
 * @param string $file_path Ruta del archivo
 * @param string $context Contexto para logging
 * @return bool True si se cargó correctamente
 */
function flowtitude_safe_require($file_path, $context = 'unknown') {
    return Flowtitude_File_Validator::safe_require($file_path, $context);
} 