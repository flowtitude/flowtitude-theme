<?php
/**
 * Script de prueba para el sistema de logging de Flowtitude
 * 
 * Uso: php dev-tools/test-logging.php
 */

// Simular entorno WordPress
define('ABSPATH', dirname(__DIR__) . '/');
define('WP_DEBUG', true);
define('WP_CONTENT_DIR', dirname(__DIR__) . '/wp-content');

// Definir constantes de prueba
define('FLOWTITUDE_LOG', false);
define('FLOWTITUDE_LOG_LEVEL', 'error');
define('FLOWTITUDE_LOG_DASHBOARD', false);
define('FLOWTITUDE_LOG_ENDPOINTS', false);
define('FLOWTITUDE_LOG_SNIPPETS', false);
define('FLOWTITUDE_LOG_SECURITY', false);
define('FLOWTITUDE_LOG_BRICKS', false);
define('FLOWTITUDE_LOG_HOOKS', false);

// Función de logging simplificada para pruebas
function flowtitude_debug_log($message, $type = 'info', $context = 'flowtitude') {
    // Verificar si el logging general está activado
    if (!defined('FLOWTITUDE_LOG') || !FLOWTITUDE_LOG) {
        return;
    }
    
    // Verificar nivel de logging
    $levels = ['error' => 1, 'warning' => 2, 'info' => 3, 'debug' => 4];
    $current_level = defined('FLOWTITUDE_LOG_LEVEL') ? FLOWTITUDE_LOG_LEVEL : 'error';
    $message_level = isset($levels[$type]) ? $levels[$type] : 3;
    $max_level = isset($levels[$current_level]) ? $levels[$current_level] : 1;
    
    if ($message_level > $max_level) {
        return;
    }
    
    // Verificar logging específico por módulo
    $module_constants = [
        'dashboard' => 'FLOWTITUDE_LOG_DASHBOARD',
        'endpoints' => 'FLOWTITUDE_LOG_ENDPOINTS',
        'snippets' => 'FLOWTITUDE_LOG_SNIPPETS',
        'security' => 'FLOWTITUDE_LOG_SECURITY',
        'bricks' => 'FLOWTITUDE_LOG_BRICKS',
        'hooks' => 'FLOWTITUDE_LOG_HOOKS'
    ];
    
    // Si el contexto está en la lista de módulos, verificar su constante
    if (isset($module_constants[$context])) {
        $module_constant = $module_constants[$context];
        if (!defined($module_constant) || !constant($module_constant)) {
            return;
        }
    }
    
    // Formatear el mensaje
    $formatted_message = '[Flowtitude ' . strtoupper($type) . '] [' . $context . '] ' . $message;
    
    // Escribir al log
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log($formatted_message);
        echo "📝 LOG: $formatted_message\n";
    }
}

echo "🧪 Probando sistema de logging de Flowtitude\n";
echo "============================================\n\n";

// Mostrar configuración actual
echo "🔧 Configuración actual:\n";
echo "FLOWTITUDE_LOG: " . (defined('FLOWTITUDE_LOG') && FLOWTITUDE_LOG ? 'true' : 'false') . "\n";
echo "FLOWTITUDE_LOG_LEVEL: " . (defined('FLOWTITUDE_LOG_LEVEL') ? FLOWTITUDE_LOG_LEVEL : 'no definido') . "\n";
echo "FLOWTITUDE_LOG_DASHBOARD: " . (defined('FLOWTITUDE_LOG_DASHBOARD') && FLOWTITUDE_LOG_DASHBOARD ? 'true' : 'false') . "\n";
echo "FLOWTITUDE_LOG_ENDPOINTS: " . (defined('FLOWTITUDE_LOG_ENDPOINTS') && FLOWTITUDE_LOG_ENDPOINTS ? 'true' : 'false') . "\n";
echo "FLOWTITUDE_LOG_SNIPPETS: " . (defined('FLOWTITUDE_LOG_SNIPPETS') && FLOWTITUDE_LOG_SNIPPETS ? 'true' : 'false') . "\n";
echo "FLOWTITUDE_LOG_SECURITY: " . (defined('FLOWTITUDE_LOG_SECURITY') && FLOWTITUDE_LOG_SECURITY ? 'true' : 'false') . "\n";
echo "FLOWTITUDE_LOG_BRICKS: " . (defined('FLOWTITUDE_LOG_BRICKS') && FLOWTITUDE_LOG_BRICKS ? 'true' : 'false') . "\n";
echo "FLOWTITUDE_LOG_HOOKS: " . (defined('FLOWTITUDE_LOG_HOOKS') && FLOWTITUDE_LOG_HOOKS ? 'true' : 'false') . "\n\n";

// Función de prueba
function test_logging() {
    echo "📝 Generando logs de prueba...\n\n";
    
    // Probar diferentes niveles y contextos
    flowtitude_debug_log('Este es un mensaje de error', 'error', 'test');
    flowtitude_debug_log('Este es un mensaje de warning', 'warning', 'test');
    flowtitude_debug_log('Este es un mensaje de info', 'info', 'test');
    flowtitude_debug_log('Este es un mensaje de debug', 'debug', 'test');
    
    // Probar diferentes contextos
    flowtitude_debug_log('Mensaje del dashboard', 'info', 'dashboard');
    flowtitude_debug_log('Mensaje de endpoints', 'info', 'endpoints');
    flowtitude_debug_log('Mensaje de snippets', 'info', 'snippets');
    flowtitude_debug_log('Mensaje de security', 'info', 'security');
    flowtitude_debug_log('Mensaje de bricks', 'info', 'bricks');
    flowtitude_debug_log('Mensaje de hooks', 'info', 'hooks');
    
    echo "\n✅ Prueba completada.\n";
    echo "💡 Para activar logs, modifica las constantes en inc/mu-plugins/flowtitude-config.php\n";
    echo "   Ejemplo: define('FLOWTITUDE_LOG', true);\n";
    echo "   Ejemplo: define('FLOWTITUDE_LOG_DASHBOARD', true);\n\n";
}

// Ejecutar prueba
test_logging();
?> 