# Configuración del Mu-Plugin de Flowtitude

## Descripción

El mu-plugin de Flowtitude (`inc/mu-plugins/flowtitude-config.php`) es un sistema unificado que maneja todas las configuraciones del tema de forma centralizada. Se carga automáticamente antes que cualquier plugin o tema, permitiendo configurar Flowtitude sin modificar `wp-config.php`.

## Características Principales

### 🔧 Sistema de Logging Unificado
- **Control centralizado**: Todas las configuraciones de logging se manejan desde el panel de administración
- **Múltiples niveles**: error, warning, info, debug
- **Archivo personalizable**: Ruta de log configurable desde el panel
- **Activación selectiva**: Solo se activa cuando es necesario
- **Optimizado por defecto**: Solo registra errores críticos hasta que se active el logging completo

### 🛡️ Configuraciones de Seguridad
- **Ocultar versión de WordPress**: Elimina metadatos de versión
- **Desactivar XML-RPC**: Previene ataques de fuerza bruta
- **REST API restringida**: Control de acceso para visitantes
- **Restricción por IP**: Lista blanca de IPs permitidas
- **Desactivar transients**: Optimización de base de datos
- **Heartbeat API**: Control de peticiones AJAX internas

### 🐛 Configuraciones de Debug
- **WP_DEBUG**: Activa el modo debug de WordPress
- **WP_DEBUG_DISPLAY**: Muestra errores en pantalla
- **WP_DEBUG_LOG**: Escribe errores en archivo
- **SCRIPT_DEBUG**: Carga scripts no minificados
- **SAVEQUERIES**: Guarda consultas SQL
- **DISABLE_WP_CRON**: Desactiva cron interno
- **Logging de hooks**: Registra todos los hooks ejecutados

### 🚀 Configuraciones de Rendimiento
- **Límites de memoria**: Configuración personalizable
- **Límite de revisiones**: Control de versiones de posts
- **Desactivar autosave**: Optimización para desarrollo
- **Caché de objetos**: Activación de WP_CACHE

### 🏷️ Badges y Banners
- **Modo desarrollo**: Badge azul en admin bar y banners
- **Modo migración**: Badge rojo para entornos de migración
- **Visibilidad selectiva**: Solo para administradores

## Comportamiento por Defecto

### 🔇 Logging Silencioso
Por defecto, el sistema de logging está configurado para ser **completamente silencioso**:

- **Nivel por defecto**: Solo errores críticos (`error`)
- **Sin logs de inicialización**: No genera logs automáticos al cargar
- **Logs condicionales**: Solo registra información cuando el logging está explícitamente activado
- **Optimizado para producción**: No afecta el rendimiento en entornos de producción

### 🎯 Cuándo se Generan Logs
Los logs solo se generan cuando:

1. **Se activa explícitamente** desde el panel de administración
2. **Ocurre un error crítico** que requiere atención
3. **Se está en modo debug** y se solicita información específica

### 📊 Control Granular
- **Error**: Solo errores críticos (por defecto)
- **Warning**: Errores y advertencias
- **Info**: Información general
- **Debug**: Todo el detalle (solo para depuración)

## Configuración desde el Panel de Administración

### 1. Acceder al Panel
1. Ve a `WordPress Admin > Flowtitude > Security`
2. Las configuraciones están organizadas en secciones colapsables

### 2. Sección Debug
- **Activar modo debug**: Habilita WP_DEBUG
- **Mostrar errores en pantalla**: Habilita WP_DEBUG_DISPLAY
- **Escribir errores en log**: Habilita WP_DEBUG_LOG
- **Ruta del archivo de log**: Define ubicación personalizada
- **Forzar scripts no minificados**: Habilita SCRIPT_DEBUG
- **Guardar queries SQL**: Habilita SAVEQUERIES
- **Desactivar cron interno**: Habilita DISABLE_WP_CRON
- **Registrar hooks y acciones**: Logging detallado de hooks

### 3. Sección General (Seguridad)
- **Desactivar REST API para visitantes**: Bloquea acceso no autenticado
- **Ocultar versión de WordPress**: Elimina metadatos de versión
- **Desactivar XML-RPC**: Previene ataques
- **Mejorar seguridad del login**: Límites de intentos
- **Permitir acceso solo desde estas IPs**: Lista blanca
- **Desactivar autenticación de dos factores**: Para desarrollo
- **Desactivar restricciones de subida**: Para desarrollo

### 4. Sección Migraciones
- **Activar modo desarrollo**: Badge azul y banners
- **Activar modo de migración**: Badge rojo y banners
- **Desactivar plugins de producción**: Lista de plugins a desactivar

## Configuración Manual (Opcional)

Si necesitas configuraciones específicas que no están disponibles en el panel, puedes definir constantes en `wp-config.php`:

```php
// Configuraciones del tema
define('FLOWTITUDE_VERSION', '2.0.0');
define('FLOWTITUDE_MIN_WP_VERSION', '6.0');
define('FLOWTITUDE_MIN_PHP_VERSION', '8.0');

// Directorios personalizados
define('FLOWTITUDE_SNIPPETS_DIR', 'snippets');
define('FLOWTITUDE_BRICKS_DIR', 'bricks');
```

## Archivos de Log

### Ubicación por Defecto
- **Log principal**: `/wp-content/debug.log`
- **Log personalizado**: Configurable desde el panel de administración

### Formato de Entradas
```
[2024-01-15 10:30:45] [Flowtitude INFO] [init] Mu-plugin unificado de Flowtitude cargado correctamente
[2024-01-15 10:30:46] [Flowtitude INFO] [security] Versión de WordPress oculta
[2024-01-15 10:30:47] [Flowtitude DEBUG] [hooks] Hook ejecutado: wp_loaded
```

## Activación de Logs

### Para Desarrollo
1. Ve a `Flowtitude > Security > Debug`
2. Activa "Activar modo debug"
3. Activa "Escribir errores en log"
4. Opcional: Define ruta personalizada del log

### Para Depuración Avanzada
1. Activa "Registrar hooks y acciones"
2. Activa "Guardar queries SQL"
3. Activa "Forzar scripts no minificados"

## Ventajas del Sistema Unificado

### ✅ Centralización
- Todas las configuraciones en un solo lugar
- No hay conflictos entre diferentes sistemas
- Fácil mantenimiento y actualización

### ✅ Flexibilidad
- Configuración desde panel de administración
- Configuración manual opcional
- Activación selectiva de funcionalidades

### ✅ Seguridad
- Validación de todas las configuraciones
- Sanitización de entradas
- Control de acceso granular

### ✅ Rendimiento
- Carga temprana de configuraciones
- Logging inteligente (solo cuando es necesario)
- Optimizaciones automáticas

## Troubleshooting

### Los logs no aparecen
1. Verifica que el logging esté activado en el panel
2. Comprueba permisos de escritura en el directorio de logs
3. Revisa la ruta del archivo de log

### Las configuraciones no se aplican
1. Limpia la caché de WordPress
2. Verifica que el mu-plugin esté en la ubicación correcta
3. Comprueba que no haya conflictos con otros plugins

### Error de permisos
1. Verifica que el archivo mu-plugin tenga permisos 644
2. Comprueba que el directorio tenga permisos 755
3. Asegúrate de que el servidor web pueda leer el archivo

## Notas Importantes

- **No modificar wp-config.php**: El mu-plugin está diseñado para evitar modificaciones del archivo principal
- **Carga temprana**: Se ejecuta antes que cualquier plugin o tema
- **Compatibilidad**: Funciona con cualquier configuración de WordPress
- **Seguridad**: Todas las configuraciones se validan y sanitizan

## Soporte

Para problemas específicos o configuraciones avanzadas, consulta la documentación oficial en [webyblog.es/docs/flowtitude](https://webyblog.es/docs/flowtitude).

## Constantes de Logging del Tema

### 🔧 Control Granular por Módulos

El tema incluye constantes específicas para controlar el logging por módulos. Estas constantes se definen en `inc/mu-plugins/flowtitude-config.php`:

```php
// Logging general del tema
define('FLOWTITUDE_LOG', false);           // Activar/desactivar logging general
define('FLOWTITUDE_LOG_LEVEL', 'error');   // Nivel: error, warning, info, debug

// Logging específico por módulos
define('FLOWTITUDE_LOG_DASHBOARD', false); // Logs del dashboard personalizado
define('FLOWTITUDE_LOG_ENDPOINTS', false); // Logs de endpoints REST API
define('FLOWTITUDE_LOG_SNIPPETS', false);  // Logs de gestión de snippets
define('FLOWTITUDE_LOG_SECURITY', false);  // Logs de configuraciones de seguridad
define('FLOWTITUDE_LOG_BRICKS', false);    // Logs de integración con Bricks
define('FLOWTITUDE_LOG_HOOKS', false);     // Logs de hooks y acciones
```

### 🎯 Ejemplos de Uso

#### Activar solo logs de errores del dashboard:
```php
define('FLOWTITUDE_LOG', true);
define('FLOWTITUDE_LOG_LEVEL', 'error');
define('FLOWTITUDE_LOG_DASHBOARD', true);
```

#### Activar logs completos de snippets:
```php
define('FLOWTITUDE_LOG', true);
define('FLOWTITUDE_LOG_LEVEL', 'debug');
define('FLOWTITUDE_LOG_SNIPPETS', true);
```

#### Activar logs de seguridad y endpoints:
```php
define('FLOWTITUDE_LOG', true);
define('FLOWTITUDE_LOG_LEVEL', 'info');
define('FLOWTITUDE_LOG_SECURITY', true);
define('FLOWTITUDE_LOG_ENDPOINTS', true);
```

### 📊 Niveles de Logging

- **error**: Solo errores críticos
- **warning**: Errores y advertencias
- **info**: Información general
- **debug**: Información detallada de depuración

### 🔄 Prioridad de Configuración

1. **Panel de administración** (máxima prioridad)
2. **Constantes del mu-plugin** (si no hay configuración del panel)
3. **Configuración por defecto** (silencioso)

### 🚀 Pruebas Recomendadas

1. **Inicio**: Todas las constantes en `false`
2. **Paso 1**: Activar `FLOWTITUDE_LOG = true` y `FLOWTITUDE_LOG_LEVEL = 'error'`
3. **Paso 2**: Activar módulos específicos según necesidad
4. **Paso 3**: Aumentar nivel de logging si es necesario 