<?php
/**
 * Script para verificar que el mu-plugin de Flowtitude se carga correctamente
 * 
 * Uso: php dev-tools/verify-mu-plugin.php
 */

echo "🔍 Verificando carga del mu-plugin de Flowtitude\n";
echo "===============================================\n\n";

// Verificar si WordPress está cargado
if (!defined('ABSPATH')) {
    echo "❌ WordPress no está cargado. Este script debe ejecutarse desde WordPress.\n";
    echo "💡 Para probar las constantes, usa: php dev-tools/test-logging.php\n\n";
    exit;
}

echo "✅ WordPress está cargado\n";

// Verificar si el mu-plugin está cargado
if (!function_exists('flowtitude_debug_log')) {
    echo "❌ La función flowtitude_debug_log no está disponible\n";
    echo "💡 El mu-plugin no se ha cargado correctamente\n\n";
    exit;
}

echo "✅ La función flowtitude_debug_log está disponible\n";

// Verificar constantes
$constants_to_check = [
    'FLOWTITUDE_LOG',
    'FLOWTITUDE_LOG_LEVEL',
    'FLOWTITUDE_LOG_DASHBOARD',
    'FLOWTITUDE_LOG_ENDPOINTS',
    'FLOWTITUDE_LOG_SNIPPETS',
    'FLOWTITUDE_LOG_SECURITY',
    'FLOWTITUDE_LOG_BRICKS',
    'FLOWTITUDE_LOG_HOOKS'
];

echo "\n🔧 Estado de las constantes:\n";
echo "----------------------------\n";

foreach ($constants_to_check as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        $status = $value ? '✅ true' : '❌ false';
        echo "$constant: $status\n";
    } else {
        echo "$constant: ❌ no definida\n";
    }
}

echo "\n🧪 Probando función de logging...\n";
echo "--------------------------------\n";

// Probar la función
flowtitude_debug_log('Prueba de verificación del mu-plugin', 'info', 'test');

echo "✅ Función de logging probada\n";
echo "📁 Revisa el archivo debug.log para ver si se generó el mensaje\n\n";

echo "💡 Para activar logs, modifica las constantes en:\n";
echo "   - inc/mu-plugins/flowtitude-config.php (mu-plugin)\n";
echo "   - wp-config.php (si copias las constantes ahí)\n\n";

echo "🔄 Los cambios en el mu-plugin se reflejan inmediatamente\n";
echo "   No necesitas desactivar/reactivar el tema\n\n";
?> 