<?php
/**
 * Script para actualizar el mu-plugin de Flowtitude
 * 
 * Uso: php dev-tools/update-mu-plugin.php
 */

echo "🔄 Actualizando mu-plugin de Flowtitude\n";
echo "=====================================\n\n";

// Definir rutas
$src_file = __DIR__ . '/../inc/mu-plugins/flowtitude-config.php';
$dst_dir = dirname(__DIR__) . '/../../../wp-content/mu-plugins';
$dst_file = $dst_dir . '/flowtitude-config.php';

echo "📁 Archivo fuente: $src_file\n";
echo "📁 Directorio destino: $dst_dir\n";
echo "📁 Archivo destino: $dst_file\n\n";

// Verificar que existe el archivo fuente
if (!file_exists($src_file)) {
    echo "❌ Error: No se encontró el archivo fuente\n";
    echo "   $src_file\n\n";
    exit(1);
}

echo "✅ Archivo fuente encontrado\n";

// Crear directorio destino si no existe
if (!is_dir($dst_dir)) {
    if (mkdir($dst_dir, 0755, true)) {
        echo "✅ Directorio destino creado\n";
    } else {
        echo "❌ Error: No se pudo crear el directorio destino\n";
        echo "   $dst_dir\n\n";
        exit(1);
    }
} else {
    echo "✅ Directorio destino existe\n";
}

// Verificar si el archivo destino existe
$backup_created = false;
if (file_exists($dst_file)) {
    $backup_file = $dst_file . '.backup.' . date('Y-m-d-H-i-s');
    if (copy($dst_file, $backup_file)) {
        echo "✅ Backup creado: " . basename($backup_file) . "\n";
        $backup_created = true;
    } else {
        echo "⚠️  No se pudo crear backup del archivo existente\n";
    }
}

// Copiar el archivo
if (copy($src_file, $dst_file)) {
    echo "✅ Mu-plugin actualizado correctamente\n\n";
    
    if ($backup_created) {
        echo "💡 Se creó un backup del archivo anterior\n";
        echo "   Si necesitas revertir, usa: mv $backup_file $dst_file\n\n";
    }
    
    echo "🔄 Los cambios se aplicarán inmediatamente\n";
    echo "   No necesitas desactivar/reactivar el tema\n\n";
    
    echo "🧪 Para probar, puedes:\n";
    echo "   1. Ir al dashboard de WordPress\n";
    echo "   2. Revisar el archivo debug.log\n";
    echo "   3. Usar: php dev-tools/test-logging.php\n\n";
    
} else {
    echo "❌ Error: No se pudo copiar el archivo\n";
    echo "   Verifica permisos de escritura en: $dst_dir\n\n";
    exit(1);
}
?> 