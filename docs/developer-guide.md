# 🧑‍💻 Guía para Desarrolladores - Flowtitude v2

## Descripción General

Esta guía está diseñada para desarrolladores que quieran trabajar con el tema Flowtitude v2, ya sea para extenderlo, personalizarlo o contribuir al proyecto.

## 📋 Requisitos de Desarrollo

### Herramientas Necesarias
- **PHP**: 8.0 o superior (recomendado: 8.2+)
- **WordPress**: 6.0 o superior
- **Bricks Builder**: 1.5 o superior
- **Node.js**: 16+ (para desarrollo del panel de administración)
- **Git**: Para control de versiones

### Entorno de Desarrollo Recomendado
- **Servidor local**: XAMPP, MAMP, o similar
- **Editor de código**: VS Code, PHPStorm, o similar
- **Herramientas de debugging**: Xdebug (opcional)

## 🏗️ Arquitectura del Tema

### Estructura de Directorios

```
flowtitude-theme v2/
├── admin-panel/           # Panel de administración en Vue 3
│   ├── assets/           # Recursos estáticos
│   ├── js/              # JavaScript del panel
│   └── views/           # Componentes Vue
├── assets/              # Recursos del frontend
│   └── css/            # Estilos organizados por capas
├── inc/                # Funcionalidad principal del tema
│   ├── admin/          # Funciones del panel de administración
│   ├── core/           # Funciones core del tema
│   ├── features/       # Características específicas
│   ├── settings/       # Configuraciones y endpoints
│   └── security/       # Módulos de seguridad
├── snippets/           # Snippets del sistema
├── dev-tools/          # Herramientas de desarrollo
└── docs/              # Documentación
```

### Flujo de Carga del Tema

1. **Verificación de requisitos** (`flowtitude-v2.php`)
2. **Carga del core** (`inc/core/`)
3. **Sistema de validación** (`inc/core/file-validator.php`)
4. **Panel de administración** (`inc/admin/`)
5. **Características del tema** (`inc/features/`)
6. **Configuraciones** (`inc/settings/`)

## 🔒 Sistema de Seguridad

### Validación de Archivos

El tema incluye un sistema robusto de validación que se ejecuta automáticamente:

```php
// Carga segura de archivos
flowtitude_safe_include('ruta/al/archivo.php', 'contexto');
flowtitude_safe_require('ruta/al/archivo.php', 'contexto');

// Validación manual
$validation = Flowtitude_File_Validator::validate_file($file_path, $context);
```

**Validaciones implementadas:**
- Verificación de existencia del archivo
- Validación de permisos de lectura
- Verificación de extensión (.php)
- Validación de directorios permitidos
- Detección de funciones peligrosas

### Directorios Permitidos

Solo se pueden cargar archivos desde estas ubicaciones:
- `/inc/` - Funcionalidad del tema
- `/snippets/` - Snippets del sistema
- `/admin-panel/` - Panel de administración
- `/uploads/flowtitude/snippets/` - Snippets personalizados
- `/uploads/flowtitude/bricks/` - Componentes de Bricks

## 📝 Sistema de Logging

### Configuración de Logs

El sistema de logging se maneja desde el panel de administración:

```php
// Logging básico
flowtitude_debug_log('Mensaje', 'nivel', 'contexto');

// Niveles disponibles: error, warning, info, debug, success
// Contextos recomendados: init, core, admin, features, security
```

### Activación de Logs

1. Ve a `WordPress Admin > Flowtitude > Security > Debug`
2. Activa las opciones de logging necesarias
3. Los logs se guardan en `/wp-content/debug.log`

## 🎨 Desarrollo del Panel de Administración

### Estructura Vue 3

```
admin-panel/js/
├── main.js              # Punto de entrada
├── utils/              # Utilidades JavaScript
│   ├── error-handler.js
│   └── notify.js
└── views/              # Componentes Vue
    ├── Home.js
    ├── Settings.js
    ├── Security.js
    └── ...
```

### Añadir Nuevas Vistas

1. Crea el componente en `admin-panel/js/views/`
2. Regístralo en `admin-panel/js/main.js`
3. Añade la ruta en el router
4. Crea el endpoint PHP correspondiente

### Ejemplo de Componente

```javascript
// MiComponente.js
export default {
    name: 'MiComponente',
    data() {
        return {
            datos: []
        }
    },
    methods: {
        async cargarDatos() {
            try {
                const response = await this.$http.get('/wp-json/flowtitude/v1/mi-endpoint');
                this.datos = response.data;
            } catch (error) {
                this.$notify.error('Error al cargar datos');
            }
        }
    },
    mounted() {
        this.cargarDatos();
    }
}
```

## 🔧 Desarrollo de Características

### Añadir Nuevas Features

1. **Crear el archivo** en `inc/features/`
2. **Usar carga segura**:
   ```php
   if (flowtitude_safe_require(FLOWTITUDE_DIR . '/inc/features/mi-feature.php', 'features')) {
       flowtitude_debug_log('Feature cargada: mi-feature', 'success', 'init');
   }
   ```
3. **Añadir logging** apropiado
4. **Documentar** la funcionalidad

### Estructura de una Feature

```php
<?php
/**
 * Mi Feature - Descripción
 * 
 * @package Flowtitude
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// Verificar que las funciones necesarias estén disponibles
if (!function_exists('flowtitude_debug_log')) {
    return;
}

/**
 * Función principal de la feature
 */
function mi_feature_init() {
    // Tu código aquí
    flowtitude_debug_log('Mi feature inicializada', 'success', 'features');
}

// Hook de inicialización
add_action('init', 'mi_feature_init');
```

## 🌐 Desarrollo de Endpoints REST API

### Crear Nuevos Endpoints

1. **Registrar el endpoint** en `inc/settings/api-endpoints.php`
2. **Implementar la lógica** en un archivo separado
3. **Añadir validación** de permisos
4. **Documentar** el endpoint

### Ejemplo de Endpoint

```php
// Registrar endpoint
add_action('rest_api_init', function() {
    register_rest_route('flowtitude/v1', '/mi-endpoint', [
        'methods' => 'GET',
        'callback' => 'mi_endpoint_callback',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ]);
});

// Implementar callback
function mi_endpoint_callback($request) {
    try {
        $datos = obtener_mi_datos();
        return new WP_REST_Response($datos, 200);
    } catch (Exception $e) {
        flowtitude_debug_log('Error en endpoint: ' . $e->getMessage(), 'error', 'api');
        return new WP_Error('error', 'Error interno', ['status' => 500]);
    }
}
```

## 🧪 Testing y Debugging

### Herramientas de Desarrollo

El tema incluye herramientas en `dev-tools/`:

- `test-logging.php` - Probar el sistema de logging
- `test-paths.php` - Verificar rutas del tema
- `test-file-validation.php` - Probar validación de archivos
- `clean-logs.php` - Limpiar logs de desarrollo

### Debugging Recomendado

1. **Activar logs** desde el panel de administración
2. **Usar `flowtitude_debug_log()`** para debugging
3. **Revisar logs** en `/wp-content/debug.log`
4. **Usar herramientas de desarrollo** del navegador

### Testing de Seguridad

```php
// Probar validación de archivos
$test_file = FLOWTITUDE_DIR . '/test-dangerous.php';
file_put_contents($test_file, '<?php eval("echo \'malicious\';"); ?>');

$validation = Flowtitude_File_Validator::validate_file($test_file, 'test');
// Debería fallar por contener eval()

unlink($test_file); // Limpiar
```

## 📦 Gestión de Snippets

### Crear Snippets del Sistema

1. **Crear archivo** en `snippets/`
2. **Usar carga segura** en el loader
3. **Añadir activación/desactivación** desde el panel

### Estructura de Snippet

```php
<?php
/**
 * Mi Snippet - Descripción
 * 
 * @package Flowtitude
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// Verificar que el snippet esté activo
if (!flowtitude_is_snippet_active('mi-snippet')) {
    return;
}

// Tu código aquí
add_action('wp_head', function() {
    echo '<meta name="mi-snippet" content="activo">';
});

flowtitude_debug_log('Snippet mi-snippet cargado', 'success', 'snippets');
```

## 🎯 Buenas Prácticas

### Código

- **Siempre usa** `flowtitude_safe_include()` o `flowtitude_safe_require()`
- **Añade logging** apropiado para debugging
- **Valida entradas** de usuario
- **Usa constantes** del tema (FLOWTITUDE_DIR, etc.)
- **Documenta** funciones complejas con PHPDoc

### Seguridad

- **Nunca confíes** en datos de entrada
- **Valida archivos** antes de cargarlos
- **Usa nonces** para formularios
- **Verifica permisos** de usuario
- **Sanitiza** datos antes de guardarlos

### Rendimiento

- **Carga condicional** de características
- **Usa caché** cuando sea apropiado
- **Minimiza** consultas a la base de datos
- **Optimiza** recursos CSS/JS

## 🚀 Deployment

### Preparación para Producción

1. **Desactivar logs** de debugging
2. **Optimizar recursos** (minificar CSS/JS)
3. **Verificar permisos** de archivos
4. **Probar** todas las funcionalidades
5. **Actualizar documentación**

### Checklist de Deployment

- [ ] Logs de debugging desactivados
- [ ] Recursos optimizados
- [ ] Permisos correctos (644 para archivos, 755 para directorios)
- [ ] Funcionalidades probadas
- [ ] Documentación actualizada
- [ ] Backup realizado

## 📚 Recursos Adicionales

### Documentación
- [README.md](../README.md) - Documentación principal
- [mu-plugin-configuration.md](mu-plugin-configuration.md) - Configuración avanzada
- [WordPress Developer Handbook](https://developer.wordpress.org/)

### Herramientas
- [PHP Documentation](https://www.php.net/docs.php)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)

### Comunidad
- [WordPress Support Forums](https://wordpress.org/support/)
- [Bricks Builder Community](https://community.bricksbuilder.io/)

## 🤝 Contribuir

### Cómo Contribuir

1. **Fork** el repositorio
2. **Crea una rama** para tu feature
3. **Desarrolla** siguiendo las buenas prácticas
4. **Prueba** exhaustivamente
5. **Documenta** los cambios
6. **Envía un Pull Request**

### Estándares de Código

- **PSR-12** para PHP
- **ESLint** para JavaScript
- **Comentarios** en español
- **Nombres descriptivos** para variables y funciones
- **Logging** apropiado

### Reportar Bugs

1. **Verifica** que no sea un problema de configuración
2. **Revisa los logs** para información de debugging
3. **Proporciona** información del entorno
4. **Describe** los pasos para reproducir
5. **Incluye** capturas de pantalla si es relevante

---

**Nota**: Esta guía se actualiza regularmente. Para la versión más reciente, consulta el repositorio oficial. 