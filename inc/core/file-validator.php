<?php
if (!defined('ABSPATH')) exit;

/**
 * Sistema de validación de archivos para Flowtitude
 * Centraliza todas las validaciones de seguridad antes de cargar archivos
 */
class Flowtitude_File_Validator {
    
    /**
     * Tipos de archivos permitidos
     */
    private static $allowed_extensions = ['php'];
    
    /**
     * Directorios permitidos para carga de archivos
     */
    private static $allowed_directories = [];
    
    /**
     * Inicializa los directorios permitidos
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
     * Actualiza los directorios permitidos con directorios personalizados
     * Se debe llamar después de que las funciones helper estén disponibles
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
     * Valida un archivo antes de cargarlo
     * 
     * @param string $file_path Ruta del archivo
     * @param string $context Contexto para logging
     * @param bool $strict Si es true, solo permite archivos PHP
     * @return array ['valid' => bool, 'error' => string|null, 'real_path' => string|null]
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
        
        // Obtener ruta real
        $real_path = realpath($file_path);
        if (!$real_path) {
            $result['error'] = 'Archivo no encontrado';
            self::log_validation_error($file_path, $result['error'], $context);
            return $result;
        }
        
        $result['real_path'] = $real_path;
        
        // Verificar que es un archivo
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
     * @param string $file_path Ruta real del archivo
     * @return bool
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
     * @param string $file_path Ruta del archivo
     * @return array ['valid' => bool, 'error' => string|null]
     */
    private static function validate_php_content($file_path) {
        $result = ['valid' => false, 'error' => null];
        
        // Leer las primeras líneas para validación básica
        $content = file_get_contents($file_path, false, null, 0, 1024);
        if ($content === false) {
            $result['error'] = 'No se pudo leer el contenido del archivo';
            return $result;
        }
        
        // Verificar que contiene <?php
        if (strpos($content, '<?php') === false && strpos($content, '<?=') === false) {
            $result['error'] = 'Archivo PHP inválido (falta <?php)';
            return $result;
        }
        
        // Verificar que no contiene funciones peligrosas (básico)
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
     * Carga un archivo de forma segura
     * 
     * @param string $file_path Ruta del archivo
     * @param string $context Contexto para logging
     * @param bool $strict Si es true, valida estrictamente
     * @return bool True si se cargó correctamente
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
     * Carga un archivo de forma segura con require_once
     * 
     * @param string $file_path Ruta del archivo
     * @param string $context Contexto para logging
     * @param bool $strict Si es true, valida estrictamente
     * @return bool True si se cargó correctamente
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
     * Registra errores de validación
     */
    private static function log_validation_error($file_path, $error, $context) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log("Validación de archivo fallida: $file_path - $error", 'warning', $context);
        }
    }
    
    /**
     * Registra validaciones exitosas
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
 * Función helper para cargar archivos de forma segura
 * 
 * @param string $file_path Ruta del archivo
 * @param string $context Contexto para logging
 * @return bool
 */
function flowtitude_safe_include($file_path, $context = 'unknown') {
    return Flowtitude_File_Validator::safe_include($file_path, $context);
}

/**
 * Función helper para cargar archivos requeridos de forma segura
 * 
 * @param string $file_path Ruta del archivo
 * @param string $context Contexto para logging
 * @return bool
 */
function flowtitude_safe_require($file_path, $context = 'unknown') {
    return Flowtitude_File_Validator::safe_require($file_path, $context);
} 