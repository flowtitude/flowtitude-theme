<?php
if (!defined('ABSPATH')) exit;

/**
 * Escribe mensajes en el log de depuración de WordPress para Flowtitude.
 *
 * @param mixed $message Mensaje o variable a registrar.
 * @param string $type Tipo de mensaje (info, debug, warning, success).
 */
// Función de depuración
if (!defined('FLOWTITUDE_LOG')) define('FLOWTITUDE_LOG', false); // Cambia a true para depuración avanzada
function lowtitude_file_log($message, $type = 'info') {
	if ((defined('WP_DEBUG') && WP_DEBUG) && (defined('FLOWTITUDE_LOG') && FLOWTITUDE_LOG)) {
		if (is_array($message) || is_object($message)) {
			error_log('[Flowtitude ' . strtoupper($type) . '] ' . print_r($message, true));
		} else {
			error_log('[Flowtitude ' . strtoupper($type) . '] ' . $message);
		}
	}
}

// Carga el archivo principal del tema
if (file_exists(__DIR__ . '/flowtitude-v2.php')) {
	require_once __DIR__ . '/flowtitude-v2.php';
} else {
	flowtitude_debug_log('Archivo esencial flowtitude-v2.php no encontrado', 'warning');
}

/**
 * Registra scripts y estilos para el panel de administración Flowtitude.
 *
 * @param string $hook
 * @return void
 */
// Registro de scripts y estilos del panel de administración
function flowtitude_admin_scripts($hook) {
	if ($hook !== 'toplevel_page_flowtitude-settings') return;

	// Cargar Vue y Vue Router
	wp_enqueue_script('vue', 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js', [], '3.0.0', true);
	wp_enqueue_script('vue-router', 'https://cdn.jsdelivr.net/npm/vue-router@4/dist/vue-router.global.js', [], '4.0.0', true);

	// Utilidades
	wp_enqueue_script('flowtitude-error-handler', FLOWTITUDE_URL . '/admin-panel/js/utils/error-handler.js', [], FLOWTITUDE_VERSION, true);
	wp_enqueue_script('flowtitude-notify', FLOWTITUDE_URL . '/admin-panel/js/utils/notify.js', [], FLOWTITUDE_VERSION, true);

	// Componentes
	$components = [];

	foreach ($components as $handle => $config) {
		$name = $config[0];
		$deps = $config[1];
		wp_enqueue_script(
			"flowtitude-{$handle}-component",
			FLOWTITUDE_URL . "/admin-panel/js/components/{$name}.js",
			$deps,
			FLOWTITUDE_VERSION,
			true
		);
	}

	// Vistas
	$views = [
		'home' => 'Home',
		'settings' => 'Settings',
		'snippets' => 'Snippets',
		'upload-snippet' => 'UploadSnippet',
		'upload-bricks' => 'UploadBricks',
		'upload' => 'Upload',
		'security' => 'Security',
		'bricks' => 'Bricks'
	];

	foreach ($views as $handle => $name) {
		wp_enqueue_script(
			"flowtitude-{$handle}-view",
			FLOWTITUDE_URL . "/admin-panel/js/views/{$name}.js",
			['vue','flowtitude-notify'],
			FLOWTITUDE_VERSION,
			true
		);
	}

	// Script principal
	$dependencies = array_merge(
		['vue', 'vue-router', 'flowtitude-error-handler','flowtitude-notify'],
		array_map(function($handle) { return "flowtitude-{$handle}-component"; }, array_keys($components)),
		array_map(function($handle) { return "flowtitude-{$handle}-view"; }, array_keys($views))
	);

	wp_enqueue_script('flowtitude-admin-script', 
		FLOWTITUDE_URL . '/admin-panel/js/admin-main.js',
		$dependencies,
		FLOWTITUDE_VERSION,
		true
	);

	// Estilos del admin
	$admin_css_path = get_stylesheet_directory() . '/admin-panel/css/admin.css';
	$admin_css_ver  = file_exists($admin_css_path) ? filemtime($admin_css_path) : FLOWTITUDE_VERSION;
	wp_enqueue_style(
		'flowtitude-admin-styles',
		FLOWTITUDE_URL . '/admin-panel/css/admin.css',
		[],
		$admin_css_ver
	);

	// Localizar script con datos necesarios
	wp_localize_script('flowtitude-admin-script', 'flowtitude_data', [
		'ajax_nonce' => wp_create_nonce('flowtitude_nonce'),
		'rest_nonce' => wp_create_nonce('wp_rest'),
		'icon_url' => FLOWTITUDE_URL . '/admin-panel/assets/icon.png',
		'theme_url' => FLOWTITUDE_URL,
		'rest_url' => rest_url('flowtitude/v1'),
		'ajaxurl' => admin_url('admin-ajax.php')
	]);
}
add_action('admin_enqueue_scripts', 'flowtitude_admin_scripts');

/**
 * Añade reglas de reescritura para el generador de placeholder.
 *
 * @return void
 */
// Reglas de reescritura para el generador de placeholder
function flowtitude_add_placeholder_rewrite_rule() {
	add_rewrite_rule(
		'^placeholder/([^/]+)/([^/]+)/?$',
		'index.php?placeholder=1&size=$matches[1]&theme=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^placeholder/([^/]+)/?$',
		'index.php?placeholder=1&size=$matches[1]',
		'top'
	);
	add_rewrite_rule(
		'^placeholder/?$',
		'index.php?placeholder=1',
		'top'
	);
}
add_action('init', 'flowtitude_add_placeholder_rewrite_rule');

/**
 * Registra variables de consulta para el generador de placeholder.
 *
 * @param array $vars
 * @return array
 */
function flowtitude_register_placeholder_query_vars($vars) {
	$vars[] = 'placeholder';
	$vars[] = 'size';
	$vars[] = 'width';
	$vars[] = 'height';
	$vars[] = 'theme';
	return $vars;
}
add_filter('query_vars', 'flowtitude_register_placeholder_query_vars');

/**
 * Copia recursivamente un directorio a otro destino.
 * Maneja errores y verifica que ambos parámetros sean rutas válidas.
 *
 * @param string $src Ruta origen
 * @param string $dst Ruta destino
 * @return bool True en caso de éxito, False en caso de error
 */
// Función auxiliar para copiar directorios
function flowtitude_copy_directory($src, $dst) {
	if (!is_dir($src)) {
		flowtitude_debug_log("Directorio de origen no existe: $src", 'warning');
		return false;
	}
	if (!is_dir($dst)) {
		if (!mkdir($dst, 0755, true)) {
			flowtitude_debug_log("No se pudo crear el directorio destino: $dst", 'warning');
			return false;
		}
	}
	$dir = opendir($src);
	if (!$dir) {
		flowtitude_debug_log("No se pudo abrir el directorio de origen: $src", 'warning');
		return false;
	}
	while (($file = readdir($dir)) !== false) {
		if ($file === '.' || $file === '..') continue;
		$srcPath = "$src/$file";
		$dstPath = "$dst/$file";
		if (is_dir($srcPath)) {
			if (!flowtitude_copy_directory($srcPath, $dstPath)) {
				closedir($dir);
				return false;
			}
		} else {
			if (!copy($srcPath, $dstPath)) {
				flowtitude_debug_log("No se pudo copiar $srcPath a $dstPath", 'warning');
				closedir($dir);
				return false;
			}
		}
	}
	closedir($dir);
	return true;
}

// ----------------------------------------------------------------
// Helper: garantiza que un directorio exista y sea escribible.
// ----------------------------------------------------------------
if ( ! function_exists('flowtitude_ensure_dir') ) {
	/**
	 * Crea (si es necesario) un directorio y asegura permisos 0755.
	 *
	 * @param string $path
	 * @return true|WP_Error
	 */
	function flowtitude_ensure_dir($path) {
		if (file_exists($path)) {
			return (is_writable($path) || @chmod($path, 0755))
				   ? true
				   : new WP_Error('dir_not_writable', "El directorio $path no es escribible.");
		}
		return wp_mkdir_p($path)
			   ? true
			   : new WP_Error('mkdir_failed', "No se pudo crear el directorio $path.");
	}
}

