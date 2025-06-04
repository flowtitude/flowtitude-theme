<?php
/**
 * Flowtitude API Endpoints
 *
 * Advertencia de seguridad:
 * - Todas las rutas y nombres de archivo recibidos deben ser validados y saneados.
 * - Nunca exponer rutas internas o información sensible en respuestas de error.
 * - Revisar y mantener actualizadas las capacidades requeridas en cada endpoint.
 */
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/defaults.php';

/**
 * === REGISTRO DE RUTAS REST ===
 */
add_action('rest_api_init', function () {

	// Ajustes del tema
	register_rest_route('flowtitude/v1', '/settings', [
		'methods'  => 'GET',
		'callback' => 'flowtitude_get_settings',
		'permission_callback' => function () {
			return is_user_logged_in() && current_user_can('edit_theme_options');
		},
	]);

	register_rest_route('flowtitude/v1', '/settings', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_save_settings',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);

	// Snippets
	register_rest_route('flowtitude/v1', '/snippets', [
		'methods'  => 'GET',
		'callback' => 'flowtitude_get_available_snippets',
		'permission_callback' => fn() => current_user_can('edit_theme_options'),
	]);

	register_rest_route('flowtitude/v1', '/snippets', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_save_active_snippets',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);

	// Archivos de snippets
	register_rest_route('flowtitude/v1', '/snippet-files', [
		'methods'  => 'GET',
		'callback' => 'flowtitude_list_snippet_files',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);

	register_rest_route('flowtitude/v1', '/snippet-files/move', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_move_snippet_file',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);

	register_rest_route('flowtitude/v1', '/snippet-files/delete', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_delete_snippet_file',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);

	// Subida de snippets
	register_rest_route('flowtitude/v1', '/upload-snippet', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_upload_snippet',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);
	
	// (El registro de /security se define al final del archivo para incluir defaults adecuadamente)

	register_rest_route('flowtitude/v1', '/upload-bricks-component', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_upload_bricks_component',
		'permission_callback' => fn() => current_user_can('manage_options')
	]);

	// Endpoint para eliminar componentes Bricks (alias legacy)
	register_rest_route('flowtitude/v1', '/bricks/delete', [
		'methods'  => 'POST',
		'callback' => 'flowtitude_delete_bricks_component',
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);
});


/**
 * === AJUSTES DEL TEMA ===
 */
function flowtitude_get_settings() {
	// Obtener valores por defecto
	$defaults = flowtitude_get_settings_defaults();
 
	// Obtener configuraciones guardadas o usar array vacío si no existen
	$stored = get_option('flowtitude_settings', []);
	$visual = get_option('flowtitude_design_settings', []);

	// Valores por defecto para características específicas
	$default_features = [
		'dark_mode_enabled'      => false,
		'intersect_enabled'      => false,
		'active_snippets_count'  => 0,
		'active_bricks_count'    => 0,
		'bricks_layer'           => false,
		'wp_layer'               => false
	];

	// Combinar con valores guardados
	$features = [
		'dark_mode_enabled'      => isset($stored['enable_dark_mode']) ? (bool) $stored['enable_dark_mode']       : $default_features['dark_mode_enabled'],
		'intersect_enabled'      => isset($stored['intersection_observer']) ? (bool) $stored['intersection_observer'] : $default_features['intersect_enabled'],
		'active_snippets_count'  => 0,
		'active_bricks_count'    => 0,
		'bricks_layer'           => isset($stored['bricks_layer']) ? (bool) $stored['bricks_layer'] : $default_features['bricks_layer'],
		'wp_layer'               => isset($stored['wp_layer']) ? (bool) $stored['wp_layer']         : $default_features['wp_layer']
	];

	// Debug para verificar los valores guardados
	error_log('Stored settings: ' . print_r($stored, true));
	error_log('Features: ' . print_r($features, true));
	error_log('Merged settings: ' . print_r(array_merge($defaults, $stored, $features), true));

	// Combinar todo y asegurar que todos los valores por defecto estén presentes
	$merged = array_merge($defaults, $stored, $features);
	$merged['tailwind_status'] = $features['tailwind_status']; // Asegurar que siempre se incluye el estado actual

	return rest_ensure_response($merged);
}

function flowtitude_save_settings($request) {
	$params = $request->get_json_params();
 
	if (!is_array($params)) {
		error_log('Invalid data format received in flowtitude_save_settings');
		return new WP_Error('invalid_data', 'Formato incorrecto. Se esperaba un array de parámetros.', ['status' => 400]);
	}

	// Obtener configuraciones actuales
	$current = get_option('flowtitude_settings', []);
 
	// Sanitizar y guardar todas las preferencias
	$sanitized = [
		'revision_limit'           => isset($params['revision_limit']) ? intval($params['revision_limit']) : ($current['revision_limit'] ?? 3),
		'move_bricks_menu'         => isset($params['move_bricks_menu']) ? !empty($params['move_bricks_menu']) : ($current['move_bricks_menu'] ?? false),
		'remove_gutenberg_css'     => isset($params['remove_gutenberg_css']) ? !empty($params['remove_gutenberg_css']) : ($current['remove_gutenberg_css'] ?? false),
		'remove_bricks_css'        => isset($params['remove_bricks_css']) ? !empty($params['remove_bricks_css']) : ($current['remove_bricks_css'] ?? false),
		'remove_bricks_js'         => isset($params['remove_bricks_js']) ? !empty($params['remove_bricks_js']) : ($current['remove_bricks_js'] ?? false),
		'bricks_layer'             => isset($params['bricks_layer']) ? !empty($params['bricks_layer']) : ($current['bricks_layer'] ?? false),
		'wp_layer'                 => isset($params['wp_layer']) ? !empty($params['wp_layer']) : ($current['wp_layer'] ?? false),
		'intersection_observer'    => isset($params['intersection_observer']) ? !empty($params['intersection_observer']) : ($current['intersection_observer'] ?? false),
		'enable_dark_mode'         => isset($params['enable_dark_mode']) ? !empty($params['enable_dark_mode']) : ($current['enable_dark_mode'] ?? false)
	];
 
	// Guardar las preferencias
	$result = update_option('flowtitude_settings', $sanitized);
	
	if ($result === false) {
		error_log('Error saving flowtitude settings');
		return new WP_Error('save_failed', 'Error al guardar las preferencias.', ['status' => 500]);
	}
 
	return rest_ensure_response(['success' => true]);
}

/**
 * === SNIPPETS ===
 */
function flowtitude_get_available_snippets() {
	$custom_dir = flowtitude_get_custom_dir('snippets');
	$active = get_option('flowtitude_active_snippets', []);
	if (!is_array($active)) {
		$active = [];
	}
	$groups = [];

	// Solo cargar snippets personalizados
	if (file_exists($custom_dir)) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($custom_dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				$relative_path = str_replace($custom_dir . '/', '', $file->getPathname());
				$group = dirname($relative_path);
				if ($group === '.') $group = '/';

				$meta = flowtitude_extract_snippet_meta($file->getPathname());

				$groups[$group][] = [
					'file' => $relative_path,
					'title' => $meta['title'],
					'description' => $meta['description'],
					'active' => in_array($relative_path, $active)
				];
			}
		}
	}

	return rest_ensure_response($groups);
}

function flowtitude_extract_snippet_meta($file_path) {
	$lines = file($file_path);
	$title = '';
	$desc  = '';

	foreach ($lines as $line) {
		$line = trim($line);
		if (str_starts_with($line, '//')) {
			$line = trim(substr($line, 2));
			if (!$title) $title = $line;
			elseif (!$desc) { $desc = $line; break; }
		}
		if (str_starts_with($line, '/*')) {
			$line = trim(preg_replace('/[\/\*\s]+/', '', $line));
			if (!$title) $title = $line;
			elseif (!$desc) { $desc = $line; break; }
		}
	}

	return [
		'title'       => $title ?: 'Snippet sin título',
		'description' => $desc  ?: '',
	];
}

function flowtitude_save_active_snippets($request) {
	$params = $request->get_json_params();
	if (!is_array($params)) {
		error_log('Invalid data format received in flowtitude_save_active_snippets');
		return new WP_Error('invalid_data', 'Lista no válida', ['status' => 400]);
	}

	// Obtener snippets activos actuales
	$current = get_option('flowtitude_active_snippets', []);
	if (!is_array($current)) {
		$current = [];
	}

	// Filtrar solo snippets personalizados
	$safe_files = array_map('sanitize_text_field', array_filter($params, function($file) {
		$path = flowtitude_get_snippet_path($file, false);
		return $path && strpos($path, flowtitude_get_custom_dir('snippets')) === 0;
	}));

	// Asegurar que se guarda como array
	$result = update_option('flowtitude_active_snippets', array_values($safe_files));
	
	if ($result === false) {
		error_log('Error saving active snippets');
		return new WP_Error('save_failed', 'Error al guardar los snippets activos.', ['status' => 500]);
	}

	return rest_ensure_response(['success' => true]);
}


/**
 * === GESTIÓN DE ARCHIVOS DE SNIPPETS ===
 */
function flowtitude_list_snippet_files() {
	// Usar directamente la ruta del directorio de uploads para evitar problemas
	$upload_dir = wp_upload_dir();
	$custom_dir = $upload_dir['basedir'] . '/flowtitude/snippets';
	$files = [];
	
	// Registrar información de depuración
	error_log('Flowtitude: Listando archivos de snippets desde: ' . $custom_dir);
	
	// Verificar si el directorio existe
	if (!file_exists($custom_dir)) {
		error_log('Flowtitude: El directorio de snippets no existe, creándolo');
		wp_mkdir_p($custom_dir);
		return rest_ensure_response([
			'message' => 'No hay snippets disponibles. Sube tu primer snippet para comenzar.',
			'files' => [],
			'debug' => [
				'custom_dir' => $custom_dir,
				'exists' => false
			]
		]);
	}
	
	// Verificar permisos del directorio
	if (!is_readable($custom_dir)) {
		error_log('Flowtitude: El directorio de snippets no tiene permisos de lectura: ' . $custom_dir);
		return rest_ensure_response([
			'message' => 'Error: No se puede leer el directorio de snippets.',
			'files' => [],
			'debug' => [
				'custom_dir' => $custom_dir,
				'readable' => false
			]
		]);
	}

	try {
		// Escanear directamente cada subdirectorio para evitar problemas con RecursiveIteratorIterator
		$total_files = 0;
		$php_files = 0;
		
		// Primero listar los archivos en el directorio raíz
		foreach (new DirectoryIterator($custom_dir) as $item) {
			if ($item->isDot()) continue;
			
			if ($item->isFile() && $item->getExtension() === 'php') {
				$total_files++;
				$php_files++;
				$rel_path = $item->getFilename();
				
				$files[] = [
					'file' => $rel_path,
					'name' => $rel_path,
					'folder' => '/',
					'path' => $item->getPathname()
				];
				
				error_log('Flowtitude: Snippet encontrado en raíz: ' . $rel_path);
			} elseif ($item->isDir() && $item->getFilename() !== '.DS_Store') {
				// Escanear subdirectorios
				$subdir_name = $item->getFilename();
				$subdir_path = $custom_dir . '/' . $subdir_name;
				
				error_log('Flowtitude: Escaneando subdirectorio: ' . $subdir_path);
				
				if (is_readable($subdir_path)) {
					foreach (new DirectoryIterator($subdir_path) as $subitem) {
						if ($subitem->isDot()) continue;
						
						if ($subitem->isFile() && $subitem->getExtension() === 'php') {
							$total_files++;
							$php_files++;
							$rel_path = $subdir_name . '/' . $subitem->getFilename();
							
							$files[] = [
								'file' => $rel_path,
								'name' => $subitem->getFilename(),
								'folder' => $subdir_name,
								'path' => $subitem->getPathname()
							];
							
							error_log('Flowtitude: Snippet encontrado en ' . $subdir_name . ': ' . $subitem->getFilename());
						}
					}
				} else {
					error_log('Flowtitude: No se puede leer el subdirectorio: ' . $subdir_path);
				}
			}
		}
		
		error_log('Flowtitude: Total de archivos escaneados: ' . $total_files . ', archivos PHP: ' . $php_files);
	} catch (Exception $e) {
		error_log('Flowtitude: Error al listar snippets: ' . $e->getMessage());
		return rest_ensure_response([
			'message' => 'Error al listar snippets: ' . $e->getMessage(),
			'files' => [],
			'debug' => [
				'custom_dir' => $custom_dir,
				'error' => $e->getMessage()
			]
		]);
	}

	if (empty($files)) {
		error_log('Flowtitude: No se encontraron archivos PHP en el directorio de snippets');
		return rest_ensure_response([
			'message' => 'No hay snippets disponibles. Sube tu primer snippet para comenzar.',
			'files' => [],
			'debug' => [
				'custom_dir' => $custom_dir,
				'exists' => true,
				'readable' => true,
				'total_files' => $total_files,
				'php_files' => $php_files
			]
		]);
	}

	return rest_ensure_response([
		'files' => $files,
		'debug' => [
			'custom_dir' => $custom_dir,
			'count' => count($files),
			'total_files' => $total_files,
			'php_files' => $php_files
		]
	]);
}

/**
 * Mueve un archivo de snippet de una carpeta a otra, validando y saneando rutas.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function flowtitude_move_snippet_file($request) {
	$src_input = $request->get_param('src');
	if(empty($src_input)){
		$json = $request->get_json_params();
		$src_input = $json['file'] ?? '';
	}
	$src_rel = flowtitude_clean_relative_path($src_input);
	$dst_input = $request->get_param('dst');
	if(empty($dst_input)){
		$json = $json ?? $request->get_json_params();
		$dst_input = $json['to'] ?? '';
	}
	$dst_rel = flowtitude_clean_relative_path($dst_input);
	if(!$src_rel || !$dst_rel) return new WP_Error('bad_path','Ruta no válida',['status'=>400]);
	// Si solo viene la carpeta, añade el mismo nombre de archivo del origen
	if($dst_rel && strpos($dst_rel,'/')===false){
		$dst_rel = rtrim($dst_rel,'/').'/'.basename($src_rel);
	}
	$src_path = flowtitude_get_snippet_path($src_rel);
	$dst_path = flowtitude_get_snippet_path($dst_rel,false,false);
	flowtitude_debug_log("Move src_path: $src_path",'info');
	flowtitude_debug_log("Move dst_path: $dst_path",'info');
	if(!$src_path || !file_exists($src_path)) return new WP_Error('not_found','Origen no existe',['status'=>404]);
	$dir_ok = flowtitude_ensure_dir(dirname($dst_path));
	if(is_wp_error($dir_ok)) return $dir_ok;
	if(file_exists($dst_path)) return new WP_Error('exists','Ya hay un archivo con ese nombre',['status'=>409]);
	if(!rename($src_path,$dst_path)) return new WP_Error('move_failed','No se pudo mover el archivo',['status'=>500]);
	flowtitude_debug_log("Snippet movido: $src_rel → $dst_rel",'success');
	return rest_ensure_response(['success'=>true]);
}

/**
 * Elimina un archivo de snippet, validando el nombre y la existencia del archivo.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function flowtitude_delete_snippet_file($request) {
	$file_input = $request->get_param('file');
	if($file_input===null){
		$json = $request->get_json_params();
		$file_input = $json['file'] ?? '';
	}
	$rel = flowtitude_clean_relative_path($file_input);
	if(!$rel) return new WP_Error('bad_path','Ruta no válida',['status'=>400]);
	$path = flowtitude_get_snippet_path($rel);
	if(!$path || !file_exists($path)) return new WP_Error('not_found','El archivo no existe',['status'=>404]);
	if(!unlink($path)) return new WP_Error('delete_failed','No se pudo eliminar el archivo',['status'=>500]);
	flowtitude_debug_log("Snippet eliminado: $rel",'success');
	return rest_ensure_response(['success'=>true]);
}


/**
 * === UPLOAD DE SNIPPETS ===
 */
/**
 * Garantiza que un directorio exista y sea escribible.
 */
function flowtitude_ensure_dir($path){
    if (file_exists($path)) {
        if (!is_writable($path) && !@chmod($path, 0755)) {
            return new WP_Error('dir_not_writable', "El directorio $path no es escribible.");
        }
        return true;
    }
    if (!wp_mkdir_p($path)) {
        return new WP_Error('mkdir_failed', "No se pudo crear el directorio $path.");
    }
    return true;
}

/**
 * Devuelve la ruta absoluta a un snippet con control de seguridad.
 *
 * @param string  $file        Nombre o ruta relativa.
 * @param bool    $is_system   TRUE ⇒ carpeta del tema; FALSE ⇒ carpeta de uploads.
 * @param bool    $must_exist  TRUE ⇒ el archivo debe existir, FALSE ⇒ solo construye la ruta.
 */
function flowtitude_get_snippet_path($file, $is_system = false, $must_exist = true) {
	$base = $is_system ? flowtitude_get_system_dir('snippets') : flowtitude_get_custom_dir('snippets');
	$relative = flowtitude_clean_relative_path($file);
	if(!$relative) return false;
	$full = trailingslashit($base).$relative;
	if(strpos(wp_normalize_path($full), wp_normalize_path($base)) !== 0) return false;
	return ($must_exist && !file_exists($full)) ? false : $full;
}

function flowtitude_upload_snippet($request) {
	$f = $request->get_file_params()['file'] ?? null;
	if(!$f) return new WP_Error('no_file','No se recibió archivo',['status'=>400]);
	$filename = preg_replace('/[^A-Za-z0-9\-_\.]/','',$f['name']);
	if(strtolower(pathinfo($filename,PATHINFO_EXTENSION))!=='php')
		return new WP_Error('invalid_ext','Solo .php',['status'=>400]);
	$folder = trim($request->get_param('folder') ?? '');
	if($folder==='' || $folder==='/' || $folder==='.') $folder='custom';
	$folder = preg_replace('/[^A-Za-z0-9\-_]/','',$folder);
	$relative = ($folder ? $folder.'/' : '').$filename;
	$dest = flowtitude_get_snippet_path($relative,false,false);
	if(!$dest) return new WP_Error('bad_path','Ruta destino inválida',['status'=>400]);
	if(is_wp_error($e = flowtitude_ensure_dir(dirname($dest)))) return $e;
	if(!move_uploaded_file($f['tmp_name'],$dest))
		return new WP_Error('upload_failed','move_uploaded_file falló',['status'=>500]);
	flowtitude_debug_log("Snippet subido a $relative",'success');
	return rest_ensure_response(['success'=>true]);
}

/**
 * === DISEÑO VISUAL - GUARDADO Y GENERACIÓN CSS ===
 */
function flowtitude_save_design_settings($request) {
	$params = $request->get_json_params();
	if (!is_array($params)) {
		return new WP_Error('invalid_data', 'Formato incorrecto', ['status' => 400]);
	}

	update_option('flowtitude_design_settings', $params);

	if (function_exists('flowtitude_generate_design_css')) {
		flowtitude_generate_design_css();
	}

	return rest_ensure_response(['success' => true]);
}

function flowtitude_generate_design_css() {
	$settings = get_option('flowtitude_design_settings', []);
	if (!is_array($settings)) return;

	$css = "@layer base {\n  :root {\n";

	foreach ($settings as $key => $value) {
		$sanitized_key = preg_replace('/[^a-zA-Z0-9\-\_]/', '', $key);
		$css .= "    {$sanitized_key}: {$value};\n";
	}

	$css .= "  }\n}";

	$path = get_stylesheet_directory() . '/assets/css/generated/flowtitude-vars.css';

	if (!file_exists(dirname($path))) {
		mkdir(dirname($path), 0755, true);
	}

	file_put_contents($path, $css);
}

// === SEGURIDAD & OPTIMIZACIÓN ===
add_action('rest_api_init', function () {
	register_rest_route('flowtitude/v1', '/security', [
		'methods' => 'GET',
		'callback' => function () {
			$defaults = [
				'disable_wp_api' => false,
				'hide_wp_version' => false,
				'disable_xmlrpc' => false,
				'secure_login' => false,
			];
			$stored = get_option('flowtitude_security_settings', []);
			return rest_ensure_response(array_merge($defaults, (array) $stored));
		},
		'permission_callback' => fn() => current_user_can('edit_theme_options'),
	]);

	register_rest_route('flowtitude/v1', '/security', [
		'methods' => 'POST',
		'callback' => function ($request) {
			$params = $request->get_json_params();
			if (!is_array($params)) {
				return new WP_Error('invalid_data', 'Formato incorrecto', ['status' => 400]);
			}

			// Lista blanca de campos permitidos para guardar
			$sanitized = [
				'disable_wp_api'   => !empty($params['disable_wp_api']),
				'hide_wp_version'  => !empty($params['hide_wp_version']),
				'disable_xmlrpc'   => !empty($params['disable_xmlrpc']),
				'secure_login'     => !empty($params['secure_login']),
			];

			update_option('flowtitude_security_settings', $sanitized);

			return rest_ensure_response(['success' => true]);
		},
		'permission_callback' => fn() => current_user_can('manage_options'),
	]);
});

function flowtitude_save_security_settings($request) {
	$data = $request->get_json_params();
	if (!is_array($data)) {
		return new WP_Error('invalid_data', 'Formato incorrecto', ['status' => 400]);
	}

	update_option('flowtitude_security_settings', $data);
	return rest_ensure_response(['success' => true]);
}

function flowtitude_upload_bricks_component($request) {
	if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
		return new WP_Error('missing_file', 'No se envió ningún archivo.', ['status' => 400]);
	}

	$file = $_FILES['file'];
	
	// Verificar que sea un archivo PHP
	if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'php') {
		return new WP_Error('invalid_type', 'Solo se permiten archivos PHP.', ['status' => 400]);
	}

	// Verificar tamaño del archivo
	if ($file['size'] > 51200) {
		return new WP_Error('file_too_large', 'Archivo demasiado grande (máx. 50KB).', ['status' => 400]);
	}

	// Leer el contenido del archivo
	$contents = file_get_contents($file['tmp_name']);
	
	// Verificar código malicioso
	if (preg_match('/(eval|base64_decode|shell_exec|system|exec)/i', $contents)) {
		return new WP_Error('unsafe_code', 'El archivo contiene funciones peligrosas.', ['status' => 400]);
	}

	// Extraer la carpeta del tercer comentario
	$folder = 'custom-elements'; // Valor por defecto
	
	// Buscar en comentarios de una línea
	$lines = explode("\n", $contents);
	$comment_count = 0;
	
	foreach ($lines as $line) {
		$line = trim($line);
		if (preg_match('/^\/\/\s*(.+)$/', $line, $matches)) {
			$comment_count++;
			if ($comment_count === 3) {
				$folder = trim($matches[1]);
				break;
			}
		} else if ($comment_count > 0 && !empty($line) && strpos($line, '//') !== 0) {
			// Si ya empezamos a contar comentarios y encontramos una línea que no es comentario, terminamos
			break;
		}
	}
	
	// Si no se encuentra en comentarios de una línea, intentar con DocBlock
	if ($comment_count < 3) {
		if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\s*\n\s*\*\s*(.+?)\s*\n\s*\*\s*(.+?)\s*\n/s', $contents, $matches)) {
			if (isset($matches[3])) {
				$folder = trim($matches[3]);
			}
		}
	}

	// Validar que la carpeta sea una de las permitidas
	if (!in_array($folder, ['custom-elements', 'conditionals', 'dynamic-tags'])) {
		return new WP_Error('invalid_folder', 'Carpeta no permitida en el tercer comentario. Debe ser una de: custom-elements, conditionals, dynamic-tags', ['status' => 400]);
	}
	
	// Registrar información de depuración
	error_log('Flowtitude: Subiendo componente Bricks a la carpeta: ' . $folder);

	$bricks_dir = flowtitude_get_custom_dir('bricks') . '/' . $folder;
	if (!file_exists($bricks_dir)) {
		wp_mkdir_p($bricks_dir);
	}

	$filename = sanitize_file_name($file['name']);
	$destination = trailingslashit($bricks_dir) . $filename;

	if (!move_uploaded_file($file['tmp_name'], $destination)) {
		return new WP_Error('move_failed', 'Error al guardar el archivo.', ['status' => 500]);
	}

	return rest_ensure_response(['success' => true, 'message' => 'Componente subido correctamente.']);
}

// === FUNCIONES DE RUTAS ===
function flowtitude_get_custom_dir($type = 'snippets') {
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'] . '/flowtitude';
    
    if (!file_exists($base_dir)) {
        wp_mkdir_p($base_dir);
    }
    
    $type_dir = $base_dir . '/' . $type;
    if (!file_exists($type_dir)) {
        wp_mkdir_p($type_dir);
    }
    
    return $type_dir;
}

function flowtitude_get_system_dir($type = 'snippets') {
    return get_stylesheet_directory() . '/' . $type;
}

function flowtitude_get_bricks_path($file) {
    return realpath(flowtitude_get_custom_dir('bricks') . '/' . $file);
}

function flowtitude_load_snippets() {
	// Primero cargar snippets del sistema
	$theme_dir = get_stylesheet_directory() . '/snippets';
	if (file_exists($theme_dir)) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($theme_dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				require_once $file->getPathname();
			}
		}
	}

	// Luego cargar snippets personalizados activos
	$active_snippets = get_option('flowtitude_active_snippets', []);
	if (!is_array($active_snippets)) {
		$active_snippets = [];
	}

	$custom_dir = flowtitude_get_custom_dir('snippets');
	if (file_exists($custom_dir)) {
		foreach ($active_snippets as $file) {
			$path = flowtitude_get_snippet_path($file, false);
			if ($path && file_exists($path) && strpos($path, $custom_dir) === 0) {
				require_once $path;
			}
		}
	}
}

// Helper: limpia ruta relativa manteniendo jerarquía y extensión .php
function flowtitude_clean_relative_path($path){
	$path = str_replace('\\','/',$path);
	$path = preg_replace('#/+#','/', ltrim($path,'/'));
	if (preg_match('#(^|/)\.+(/|$)#',$path)) return false;
	$segments = explode('/', $path);
	$file = array_pop($segments);
	$file = preg_replace('/[^A-Za-z0-9\-_\.]/','',$file);
	$segments[] = $file;
	return implode('/', array_filter($segments,'strlen')); 
}

// Eliminación de componentes Bricks
function flowtitude_delete_bricks_component($request){
	$file_rel = flowtitude_clean_relative_path($request->get_param('file') ?? '');
	if(!$file_rel) return new WP_Error('invalid_file','Archivo no especificado',['status'=>400]);
	$path = flowtitude_get_custom_dir('bricks').'/'.$file_rel;
	if(!file_exists($path)) return new WP_Error('not_found','No existe',['status'=>404]);
	if(!unlink($path)) return new WP_Error('delete_failed','No se pudo eliminar',['status'=>500]);
	flowtitude_debug_log("Componente Bricks eliminado: $file_rel",'success');
	return rest_ensure_response(['success'=>true]);
}

