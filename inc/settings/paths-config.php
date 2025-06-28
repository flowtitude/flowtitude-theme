<?php
if (!defined('ABSPATH')) exit;

/**
 * Configuración centralizada de rutas dinámicas para Flowtitude
 * 
 * Este archivo centraliza todas las rutas del tema para evitar hardcodeo
 * y facilitar la portabilidad entre diferentes instalaciones.
 */
class Flowtitude_Paths_Config {
    
    private static $instance = null;
    private $paths = [];
    private $cache = [];
    
    /**
     * Constructor privado - inicializa las rutas por defecto
     */
    private function __construct() {
        $this->initialize_default_paths();
    }
    
    /**
     * Obtiene la instancia singleton
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializa las rutas por defecto del sistema
     */
    private function initialize_default_paths() {
        $upload_dir = wp_upload_dir();
        
        $this->paths = [
            // Rutas de uploads
            'uploads' => [
                'base' => $upload_dir['basedir'],
                'base_url' => $upload_dir['baseurl'],
                'flowtitude' => $upload_dir['basedir'] . '/flowtitude',
                'flowtitude_url' => $upload_dir['baseurl'] . '/flowtitude',
                'windpress' => $upload_dir['basedir'] . '/windpress',
                'windpress_url' => $upload_dir['baseurl'] . '/windpress',
                'windpress_cache' => $upload_dir['basedir'] . '/windpress/cache',
                'windpress_cache_url' => $upload_dir['baseurl'] . '/windpress/cache',
                'windpress_data' => $upload_dir['basedir'] . '/windpress/data',
                'windpress_data_url' => $upload_dir['baseurl'] . '/windpress/data',
                'windpress_theme' => $upload_dir['basedir'] . '/windpress/data/theme',
                'windpress_theme_url' => $upload_dir['baseurl'] . '/windpress/data/theme',
            ],
            
            // Rutas del tema
            'theme' => [
                'root' => get_template_directory(),
                'root_url' => get_template_directory_uri(),
                'snippets' => get_template_directory() . '/snippets',
                'snippets_url' => get_template_directory_uri() . '/snippets',
                'admin_panel' => get_template_directory() . '/admin-panel',
                'admin_panel_url' => get_template_directory_uri() . '/admin-panel',
                'assets' => get_template_directory() . '/assets',
                'assets_url' => get_template_directory_uri() . '/assets',
                'css' => get_template_directory() . '/assets/css',
                'css_url' => get_template_directory_uri() . '/assets/css',
                'js' => get_template_directory() . '/assets/js',
                'js_url' => get_template_directory_uri() . '/assets/js',
            ],
            
            // Rutas de Bricks (si está activo)
            'bricks' => [
                'root' => WP_CONTENT_DIR . '/plugins/bricks',
                'root_url' => content_url('plugins/bricks'),
                'includes' => WP_CONTENT_DIR . '/plugins/bricks/includes',
                'includes_url' => content_url('plugins/bricks/includes'),
                'dynamic_data' => WP_CONTENT_DIR . '/plugins/bricks/includes/integrations/dynamic-data',
                'dynamic_data_url' => content_url('plugins/bricks/includes/integrations/dynamic-data'),
                'parser' => WP_CONTENT_DIR . '/plugins/bricks/includes/integrations/dynamic-data/dynamic-data-parser.php',
            ],
            
            // Rutas de WordPress
            'wp' => [
                'content' => WP_CONTENT_DIR,
                'content_url' => content_url(),
                'admin' => admin_url(),
                'includes' => ABSPATH . 'wp-includes',
                'includes_url' => includes_url(),
                'mu_plugins' => defined('WPMU_PLUGIN_DIR') ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins',
                'mu_plugins_url' => content_url('mu-plugins'),
            ],
            
            // Rutas de archivos específicos
            'files' => [
                'tailwind_css' => $upload_dir['basedir'] . '/windpress/cache/tailwind.css',
                'tailwind_css_url' => $upload_dir['baseurl'] . '/windpress/cache/tailwind.css',
                'flowtitude_css' => $upload_dir['basedir'] . '/windpress/data/theme/flowtitude.css',
                'flowtitude_css_url' => $upload_dir['baseurl'] . '/windpress/data/theme/flowtitude.css',
                'tailwind_config' => get_template_directory() . '/tailwind.config.js',
            ]
        ];
        
        // Verificar si Bricks está activo y ajustar rutas
        $this->adjust_bricks_paths();
    }
    
    /**
     * Ajusta las rutas de Bricks según la instalación real
     */
    private function adjust_bricks_paths() {
        // Verificar si Bricks está activo
        if (!class_exists('Bricks\Database')) {
            return;
        }
        
        // Intentar diferentes ubicaciones comunes de Bricks
        $possible_bricks_paths = [
            WP_CONTENT_DIR . '/plugins/bricks',
            WP_CONTENT_DIR . '/plugins/bricks-builder',
            get_template_directory() . '/../bricks',
            ABSPATH . 'wp-content/plugins/bricks',
        ];
        
        foreach ($possible_bricks_paths as $path) {
            if (file_exists($path . '/bricks.php') || file_exists($path . '/includes/integrations/dynamic-data/dynamic-data-parser.php')) {
                $this->paths['bricks']['root'] = $path;
                $this->paths['bricks']['root_url'] = str_replace(WP_CONTENT_DIR, content_url(), $path);
                $this->paths['bricks']['includes'] = $path . '/includes';
                $this->paths['bricks']['includes_url'] = str_replace(WP_CONTENT_DIR, content_url(), $path . '/includes');
                $this->paths['bricks']['dynamic_data'] = $path . '/includes/integrations/dynamic-data';
                $this->paths['bricks']['dynamic_data_url'] = str_replace(WP_CONTENT_DIR, content_url(), $path . '/includes/integrations/dynamic-data');
                $this->paths['bricks']['parser'] = $path . '/includes/integrations/dynamic-data/dynamic-data-parser.php';
                break;
            }
        }
    }
    
    /**
     * Obtiene una ruta específica
     * 
     * @param string $path_key Clave de la ruta (ej: 'uploads.flowtitude', 'theme.snippets')
     * @param bool $url Si es true, devuelve la URL en lugar del path
     * @return string|null La ruta solicitada o null si no existe
     */
    public function get($path_key, $url = false) {
        $cache_key = $path_key . '_' . ($url ? 'url' : 'path');
        
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        $keys = explode('.', $path_key);
        $current = $this->paths;
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return null;
            }
            $current = $current[$key];
        }
        
        // Si se solicita URL, buscar la versión URL
        if ($url && is_array($current)) {
            $url_key = array_keys($current)[0] . '_url';
            if (isset($current[$url_key])) {
                $current = $current[$url_key];
            } else {
                // Convertir path a URL
                $current = str_replace(WP_CONTENT_DIR, content_url(), $current);
            }
        }
        
        $this->cache[$cache_key] = $current;
        return $current;
    }
    
    /**
     * Verifica si una ruta existe
     * 
     * @param string $path_key Clave de la ruta
     * @param bool $url Si es true, verifica la URL
     * @return bool
     */
    public function exists($path_key, $url = false) {
        $path = $this->get($path_key, $url);
        return $path !== null && ($url || file_exists($path));
    }
    
    /**
     * Crea un directorio si no existe
     * 
     * @param string $path_key Clave de la ruta del directorio
     * @return bool True si se creó o ya existía
     */
    public function ensure_directory($path_key) {
        $path = $this->get($path_key);
        if (!$path) {
            return false;
        }
        
        if (!file_exists($path)) {
            return wp_mkdir_p($path);
        }
        
        return is_dir($path);
    }
    
    /**
     * Obtiene todas las rutas disponibles
     * 
     * @return array Array con todas las rutas configuradas
     */
    public function get_all_paths() {
        return $this->paths;
    }
    
    /**
     * Actualiza una ruta específica
     * 
     * @param string $path_key Clave de la ruta
     * @param string $new_path Nueva ruta
     * @return bool True si se actualizó correctamente
     */
    public function update_path($path_key, $new_path) {
        $keys = explode('.', $path_key);
        $current = &$this->paths;
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        
        $current = $new_path;
        $this->cache = []; // Limpiar caché
        return true;
    }
    
    /**
     * Registra las rutas en el sistema de logging si está activo
     */
    public function log_paths() {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Rutas configuradas: ' . json_encode($this->paths, JSON_PRETTY_PRINT), 'debug', 'paths');
        }
    }
}

/**
 * Función helper para obtener rutas fácilmente
 * 
 * @param string $path_key Clave de la ruta
 * @param bool $url Si es true, devuelve la URL
 * @return string|null
 */
function flowtitude_get_path($path_key, $url = false) {
    return Flowtitude_Paths_Config::get_instance()->get($path_key, $url);
}

/**
 * Función helper para verificar si una ruta existe
 * 
 * @param string $path_key Clave de la ruta
 * @param bool $url Si es true, verifica la URL
 * @return bool
 */
function flowtitude_path_exists($path_key, $url = false) {
    return Flowtitude_Paths_Config::get_instance()->exists($path_key, $url);
}

/**
 * Función helper para asegurar que un directorio existe
 * 
 * @param string $path_key Clave de la ruta del directorio
 * @return bool
 */
function flowtitude_ensure_directory($path_key) {
    return Flowtitude_Paths_Config::get_instance()->ensure_directory($path_key);
} 