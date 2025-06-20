# 🌀 Flowtitude v2 - Tema Hijo WordPress con Panel de Administración Avanzado

**Versión:** 2.0.0  
**Última actualización:** 2025-01-27  
**Autor:** Ángel Julián

---

## 🎯 Descripción General

Flowtitude v2 es un **tema hijo para WordPress y Bricks Builder** que integra un completo **panel de administración en Vue 3**, manejo dinámico de **snippets** y soporte para características como **modo oscuro**, optimización de capas CSS y mejoras de rendimiento.

---

## 🚀 Características Principales

### Snippets Personalizados
- Sistema de snippets organizados por carpetas
- Activación/desactivación desde el panel de administración
- Soporte para snippets del sistema y personalizados
- Generador de placeholders integrado

### Integración con Bricks Builder
- Componentes personalizados organizados por categorías:
  - Elementos personalizados
  - Etiquetas dinámicas
  - Condiciones
- Sistema de activación/desactivación de componentes
- Carga optimizada de recursos

### WindPress Integration
- Soporte completo para Tailwind CSS
- Sistema de diseño personalizado
- Capas CSS para mejor organización:
  - Capa WordPress
  - Capa Plugins
  - Capa Bricks
  - Capa Theme

### Características Adicionales
- Modo oscuro integrado
- Soporte para Intersection Observer
- Panel de administración moderno con Vue.js
- Sistema de gestión de errores mejorado
- Optimización de rendimiento

## 📋 Requisitos

- WordPress 6.0 o superior
- PHP 8.0 o superior
- Bricks Builder 1.5 o superior
- WindPress (opcional, para integración con Tailwind)

## 🔧 Instalación

1. Asegúrate de tener instalado y activado Bricks Builder
2. Descarga el tema y súbelo a `/wp-content/themes/`
3. Activa el tema desde el panel de WordPress
4. Ve a Apariencia > Flowtitude para acceder al panel de administración

## 📊 Compatibilidad

### Versiones Soportadas
- **WordPress**: 6.0 - 6.4+ (recomendado: última versión estable)
- **PHP**: 8.0 - 8.3+ (recomendado: 8.2 o superior)
- **Bricks Builder**: 1.5+ (recomendado: última versión)
- **WindPress**: 1.0+ (opcional, para Tailwind CSS)

### Navegadores Soportados
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Plugins Compatibles
- WooCommerce 7.0+
- Contact Form 7 5.7+
- Yoast SEO 20.0+
- Elementor (si está activo)
- ACF 6.0+
- Jetpack 11.0+

---

## 📦 Ejemplo de uso rápido

```php
// Añadir un snippet personalizado
do_action('flowtitude_add_snippet', 'nombre_snippet', function() {
    // Tu código aquí
});
```

---

## 🧑‍💻 Guía para desarrolladores

### Estructura de carpetas
- `admin-panel/` — Panel de administración en Vue 3
- `inc/` — Funcionalidad principal y módulos PHP
- `snippets/` — Snippets del sistema y personalizados
- `woocommerce/` — Integraciones específicas para WooCommerce
- `dev-tools/` — Herramientas para desarrollo y pruebas
- `docs/` — Documentación adicional

### Buenas prácticas
- Haz commits pequeños y descriptivos.
- Usa ramas para nuevas funcionalidades.
- Añade comentarios y PHPDoc en funciones complejas.
- Sigue las convenciones de WordPress y PSR-12.

---

## 🐞 Troubleshooting
- Si el panel no carga, revisa la consola del navegador y asegúrate de que Vue.js se está cargando correctamente.
- Comprueba los requisitos de PHP y WordPress en el panel de administración.
- Usa la función `flowtitude_debug_log()` para depuración avanzada.

---

## Pruebas y validación

Antes de publicar o actualizar Flowtitude, valida las siguientes áreas para asegurar robustez y seguridad:

### Checklist de robustez y seguridad

- [ ] **Activación y carga general**: El tema se activa sin errores ni advertencias. No aparecen errores fatales en frontend ni backend. Los logs registran eventos clave al activar el tema.
- [ ] **Panel de administración y scripts**: El panel de administración carga correctamente. Scripts y estilos se encolan y cargan sin errores. Las vistas personalizadas funcionan.
- [ ] **Seguridad de login**: Los mensajes de error en el login son genéricos. Tras 5 intentos fallidos desde la misma IP, el acceso se bloquea y se registra en los logs. Bloqueos y logins exitosos quedan registrados.
- [ ] **Protección y ocultación de información**: La versión de WordPress no aparece en el código fuente. XML-RPC está desactivado. Los logs confirman ambas protecciones.
- [ ] **Subida y gestión de snippets**: Se pueden subir archivos PHP válidos. Los intentos de subida no permitidos son bloqueados y registrados. La eliminación de snippets funciona y queda registrada.
- [ ] **Funciones frontend y demo**: El modo oscuro y otras funciones visuales funcionan sin errores. El handler de demo solo es accesible para usuarios autorizados y muestra el contenido esperado.
- [ ] **Auditoría de logs**: Todos los eventos importantes quedan registrados en los logs de depuración, sin exponer información sensible al usuario final.
- [ ] **Compatibilidad y extensibilidad**: El tema no interfiere con plugins de seguridad comunes y puede ser extendido mediante hooks y filtros.

---

## ❓ FAQ

**¿Puedo usarlo sin WindPress?**
Sí, pero algunas funciones avanzadas de Tailwind no estarán disponibles.

**¿Cómo añado un snippet personalizado?**
Coloca tu archivo en la carpeta `snippets/custom/` y actívalo desde el panel.

**¿Cómo reporto un bug?**
Abre un issue en el repositorio o contacta al autor.

---

### Configuración Inicial

1. **Verificación de Requisitos**
   - El tema verificará automáticamente PHP 8.0+ y WordPress 6.0+
   - Se mostrarán avisos si no se cumplen los requisitos mínimos

2. **Snippets del Sistema**
   - Los snippets base se activan automáticamente
   - Puedes gestionar los snippets desde el panel de Flowtitude > Snippets

3. **Integración con Bricks**
   - Los componentes de Bricks se registran automáticamente
   - Verifica en Flowtitude > Bricks que los componentes estén activos

4. **WindPress (opcional)**
   - Si usas WindPress, activa la integración en Flowtitude > Home
   - La configuración de Tailwind se cargará automáticamente

5. **Sistema de Capas CSS**
   - El tema incluye un sistema completo de capas CSS
   - Los estilos se organizan por prioridad: WordPress < Plugins < Bricks < Theme < Custom

### Verificación de la Instalación

Para verificar que todo está funcionando correctamente:

1. Revisa que no hay errores en la consola del navegador
2. Verifica que puedes acceder al panel de Flowtitude
3. Comprueba que los snippets base están funcionando
4. Si usas WindPress, verifica que Tailwind está activo
5. Testea la edición con Bricks Builder

### Solución de Problemas

Si encuentras algún problema:

1. **Verificación de Requisitos**
   - Asegúrate de tener PHP 8.0+ y WordPress 6.0+
   - Verifica que Bricks Builder esté activo y actualizado

2. **Problemas de CSS**
   - Verifica que la carpeta `/assets/css/` existe
   - Revisa la consola del navegador para errores 404
   - Comprueba que el sistema de capas CSS está funcionando

3. **Problemas del Panel de Administración**
   - Verifica que Vue.js se está cargando correctamente
   - Revisa la consola del navegador para errores JavaScript
   - Comprueba los permisos de usuario (manage_options)

4. **Problemas con Bricks**
   - Asegúrate de que Bricks Builder esté activo
   - Verifica que no hay conflictos con otros plugins
   - Comprueba que los componentes de Bricks están registrados

5. **Problemas de Rendimiento**
   - Revisa los logs de WordPress para errores
   - Verifica que no hay plugins conflictivos
   - Comprueba la configuración del servidor

6. **Problemas de Tailwind (si usas WindPress)**
   - Verifica que WindPress esté activo y configurado
   - Comprueba que el archivo `/uploads/windpress/cache/tailwind.css` existe
   - Revisa la configuración de Tailwind en WindPress

### Logs de Depuración

El tema incluye un sistema de logging detallado. Para activarlo:

1. Añade esto a tu `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. Los logs se guardarán en `/wp-content/debug.log`
3. Busca entradas con el prefijo "Flowtitude" para información específica del tema

## 💻 Uso

### Panel de Administración
El panel de administración se encuentra en `WordPress Admin > Flowtitude` y ofrece las siguientes secciones:

- **Home**: Vista general del estado del tema
- **Snippets**: Gestión de snippets personalizados
- **Security**: Configuración de seguridad
- **Settings**: Ajustes generales
- **Bricks**: Gestión de componentes
- **Uploads**: Subida de archivos
- **Design**: Configuración de diseño (requiere WindPress)

### Snippets

Los snippets **solo se pueden subir a través de la página de uploads del panel de administración** (no se colocan manualmente en carpetas).

#### Para crear y subir un snippet:
1. Ve a la sección "Snippets" en el panel de administración
2. Haz clic en "Subir Snippet"
3. Selecciona el archivo PHP
4. Elige la carpeta de destino
5. Activa el snippet desde el panel

#### Requisitos del archivo de snippet:
Cada snippet debe comenzar con **dos comentarios obligatorios**:
- El primer comentario es el **título** del snippet
- El segundo comentario es la **descripción**

**Ejemplo de snippet válido:**
```php
// Título: Mostrar saludo personalizado
// Descripción: Añade un saludo al inicio de cada entrada para usuarios logueados.

add_action('the_content', function($content) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $content = '<p>¡Hola, ' . esc_html($user->display_name) . '!</p>' . $content;
    }
    return $content;
});
```

Si el snippet no incluye ambos comentarios, no podrá ser activado correctamente.

### Componentes de Bricks
Para añadir un nuevo componente:
1. Ve a la sección "Bricks"
2. Haz clic en "Subir Componente"
3. Selecciona el archivo PHP
4. El componente se clasificará automáticamente según sus comentarios

## 🛠️ Desarrollo

### Estructura de Archivos
```
flowtitude-v2/
├── admin-panel/         # Panel de administración
├── assets/             # Recursos del tema
│   └── css/           # Sistema de capas CSS
├── inc/                 # Funcionalidades principales
│   ├── core/           # Núcleo del tema
│   ├── features/       # Características adicionales
│   ├── security/       # Funciones de seguridad
│   └── settings/       # Configuración y endpoints
├── snippets/           # Snippets del sistema
```

### Sistema de Capas CSS

El tema utiliza un sistema de capas CSS moderno con `@layer` para organizar los estilos por prioridad:

```css
/* Orden de especificidad (de menor a mayor) */
@layer wordpress-layer, plugins-layer, bricks, theme, base, layouts, components, utilities, custom;
```

**Capas disponibles:**
- **wordpress-layer**: Estilos de WordPress Core
- **plugins-layer**: Estilos de plugins
- **bricks**: Estilos de Bricks Builder
- **theme**: Estilos del tema Flowtitude
- **base**: Estilos base y reset
- **layouts**: Estilos de layout
- **components**: Componentes reutilizables
- **utilities**: Clases de utilidad
- **custom**: Estilos personalizados (máxima prioridad)

### Creación de Componentes

Los componentes de Bricks deben seguir esta estructura de comentarios:
```php
// Título del Componente
// Descripción del Componente
// custom-elements|dynamic-tags|conditionals
```

### Buenas Prácticas

- Haz commits pequeños y descriptivos
- Usa ramas para nuevas funcionalidades
- Añade comentarios y PHPDoc en funciones complejas
- Sigue las convenciones de WordPress y PSR-12
- Respeta el orden de capas CSS al añadir estilos
- Usa las variables CSS del tema para consistencia

## 🔒 Seguridad

- Validación de nonce en todas las peticiones
- Sanitización de datos
- Verificación de permisos
- Protección contra inyección de código
- Límite de tamaño en archivos subidos

## 📝 Notas Adicionales

- Los snippets y componentes se cargan después de la inicialización de WordPress
- Las rutas de archivos mantienen su estructura de carpetas
- Los componentes de Bricks se registran automáticamente
- El sistema de diseño requiere WindPress activo

## 🤝 Soporte

Para soporte técnico o reportar problemas, visita:
https://webyblog.es/docs/flowtitude

---

## 📁 Estructura del Tema

```txt
flowtitude-v2/
│
├── admin-panel/              # Panel de administración
│   ├── assets/             # Recursos estáticos
│   ├── css/               # Estilos del panel
│   └── js/               # Scripts del panel
│       ├── components/   # Componentes Vue
│       └── views/       # Vistas del panel
│
├── assets/                   # Recursos del tema
│   └── css/               # Sistema de capas CSS
│       ├── wordpress.css  # Estilos de WordPress Core
│       ├── plugins.css    # Estilos de plugins
│       ├── bricks.css     # Estilos de Bricks Builder
│       ├── theme.css      # Estilos del tema
│       ├── base.css       # Estilos base y reset
│       ├── layouts.css    # Estilos de layout
│       ├── components.css # Componentes reutilizables
│       ├── utilities.css  # Clases de utilidad
│       └── custom.css     # Estilos personalizados
│
├── inc/                    # Funcionalidades principales
│   ├── core/            # Núcleo del tema
│   ├── features/       # Características
│   ├── security/      # Seguridad
│   └── settings/     # Configuración
│
├── snippets/              # Snippets del sistema
│   └── placeholder.php   # Generador de placeholders
│
├── woocommerce/          # Plantillas WooCommerce
│
├── functions.php       # Funciones del tema
├── flowtitude-v2.php   # Archivo principal
├── style.css          # Estilos principales
├── theme.json        # Configuración del tema
└── screenshot.png    # Imagen del tema
```

## 📝 Estructura de Comentarios

### Para Snippets
Los snippets deben incluir estos comentarios al inicio del archivo:
```php
// Título del Snippet
// Descripción del Snippet
```

### Para Componentes de Bricks
Los componentes deben seguir esta estructura de comentarios:
```php
// Título del Componente
// Descripción del Componente
// custom-elements|dynamic-tags|conditionals
```

## 🧠 Funcionalidades Principales

### 🔧 Panel de Administración (Vue 3)
- Interfaz moderna con Vue Router
- Secciones colapsables reutilizables (con toggles y switches)
- Guardado AJAX vía REST API (con notificaciones visuales)
- Uso de variables CSS personalizadas (modo claro/oscuro)

### 🧩 Snippets Dinámicos
- Agrupados en carpetas (`frontend`, `security`, `bricks`, etc.)
- Activación/desactivación visual desde el panel
- Los activos se almacenan en `flowtitude_active_snippets`
- Se cargan con seguridad vía `loader.php`
- Metadata extraída desde los comentarios del archivo

### ⚙️ Sistema de Ajustes
- Almacenamiento estructurado en `flowtitude_settings` y `flowtitude_security_settings`
- Opción para:
  - Limitar revisiones
  - Mover el menú de Bricks
  - Eliminar CSS/JS de Gutenberg o Bricks
  - Cargar estilos con capas (`@layer`)
  - Habilitar modo oscuro o Intersection Observer JS
- Separación por categorías dentro del panel (General, CSS, Integraciones...)

### 🌗 Modo Oscuro
- Toggle SVG en el frontend
- Persistencia mediante `localStorage`
- Compatible con Tailwind y Nightwind

### ⚙️ Integración con Tailwind
- Configuración y generación de variables CSS dinámicas
- Compatibilidad con `@layer` y separación de estilos por origen
- Archivos generados bajo `/assets/css/generated/`

### 🖼️ Placeholder Generator
Disponible en `/snippets/utils/placeholder.php`, permite generar imágenes SVG para pruebas:

```txt
https://tusitio.local/?placeholder=1&width=800&height=400&theme=dark
```

---

## 🚀 Extensibilidad

- Puedes añadir nuevos ajustes fácilmente desde `inc/features/`
- Los snippets nuevos se colocan en la carpeta `/snippets/` según su grupo
- La lógica de administración es modular y está desacoplada del diseño

---

## 🧪 Debug y Seguridad

- Desactivado el editor de archivos desde el admin
- Protección contra ejecución directa de archivos
- Validación y saneamiento de entradas vía REST
- Protección XSS y CSRF mediante Nonces

---

## 🧑‍💻 Autor

**Ángel Julian Mena**  
[https://flowtitude.com](https://flowtitude.com)

---

## Panel de Seguridad: Grupos y Funcionalidades

El panel de Seguridad ahora está organizado en **secciones colapsables** (igual que Settings, Snippets y Bricks) y permite gestionar opciones avanzadas para entornos de desarrollo, staging y migración.

### 1. General
- **Desactivar REST API para visitantes**
- **Ocultar versión de WordPress**
- **Desactivar XML-RPC**
- **Mejorar seguridad del login**
- **Permitir acceso solo desde estas IPs**: lista blanca editable (separadas por coma o salto de línea)
- **Desactivar autenticación de dos factores**: desactiva plugins/métodos comunes de 2FA para pruebas
- **Desactivar restricciones de subida de archivos**: permite subir cualquier tipo de archivo en desarrollo

### 2. Caché y rendimiento
- **Activar caché de objetos** (`WP_CACHE`)
- **Desactivar generación de transients** (impide que WP/plugins guarden nuevos transients)
- **Limpiar transients** (botón manual)
- **Desactivar Heartbeat API** (reduce peticiones AJAX internas)
- **Desactivar guardado automático** (autosave)
- **Número máximo de revisiones por post** (0 para desactivar revisiones)

### 3. Opciones de debug de WordPress
- **Activar modo debug** (`WP_DEBUG`)
- **Mostrar errores en pantalla** (`WP_DEBUG_DISPLAY`)
- **Escribir errores en log** (`WP_DEBUG_LOG` y ruta editable)
- **Forzar scripts no minificados** (`SCRIPT_DEBUG`)
- **Guardar queries SQL** (`SAVEQUERIES`)
- **Desactivar cron interno** (`DISABLE_WP_CRON`)
- **Registrar hooks y acciones**: guarda en `wp-content/debug-hooks.log` todos los hooks ejecutados (solo para desarrolladores avanzados)

### 4. Datos y migraciones
- **Activar modo de migración**: muestra avisos y habilita herramientas especiales
- **Desactivar plugins de producción**: lista de slugs a desactivar automáticamente en entornos de desarrollo
- **Reemplazar URLs en la base de datos**: formulario seguro para cambiar URLs tras migrar el sitio
  - **Backup automático**: antes de reemplazar, se crea un backup SQL en `wp-content/backups/`
  - **README.txt**: instrucciones para restaurar el backup manualmente si pierdes el acceso
  - **Advertencia**: si pierdes el acceso al admin, descarga el backup y restáuralo desde tu hosting o WP-CLI

---

## Ejemplo de uso: Reemplazo de URLs en migración
1. Activa el modo migración en el panel.
2. Introduce la URL antigua y la nueva en el formulario.
3. Haz clic en "Reemplazar URLs en la base de datos".
4. Se creará un backup SQL en `wp-content/backups/` y se mostrará la ruta.
5. Si algo sale mal, sigue las instrucciones del `README.txt` para restaurar el backup.

---

## Advertencias y buenas prácticas
- **Haz siempre backup antes de cambios críticos** (el sistema lo hace automáticamente en migración).
- **Lee el README.txt** en la carpeta de backups para restaurar si pierdes el acceso.
- **No uses opciones avanzadas en producción** salvo que sepas lo que haces.
- **El log de hooks puede crecer rápido**: úsalo solo para debugging puntual.

---

## Otras mejoras
- Todas las opciones se gestionan desde el panel, sin tocar archivos manualmente.
- El mu-plugin se copia automáticamente al activar el tema.
- Los cambios se aplican de forma inmediata y segura.

---

Para dudas, soporte o sugerencias, contacta con el equipo de Flowtitude.

## Características Principales

### Integración Tailwind
- Modo oscuro automático
- Optimización de rendimiento con Intersection Observer
- Capas CSS para WordPress y Bricks
- Opciones para desencolar estilos por defecto

### Optimizaciones de Rendimiento
- Límites de memoria configurables
- Desactivación de autoguardado
- Límite de revisiones
- Desactivación de transients
- Caché de WordPress

### Seguridad
- Desactivación de REST API para visitantes
- Ocultación de versión de WordPress
- Desactivación de XML-RPC
- Seguridad mejorada en login
- Restricción por IP
- Autenticación de dos factores

### Herramientas de Desarrollo
- Modo debug configurable
- Registro de hooks y acciones
- Modo desarrollo
- Modo migración
- Herramientas de reemplazo de URLs

## Instalación

1. Sube el tema a la carpeta `wp-content/themes/`
2. Activa el tema desde el panel de WordPress
3. Accede a "Flowtitude" en el menú de administración
4. Configura las opciones según tus necesidades

## Requisitos

- WordPress 6.0 o superior
- PHP 8.0 o superior
- Bricks Builder (opcional, pero recomendado)

## Configuración

### Integración Tailwind
- Activa el modo oscuro para el frontend
- Configura las capas CSS para optimizar el rendimiento
- Desencola los estilos por defecto que no necesites

### Rendimiento
- Ajusta los límites de memoria según tu servidor
- Configura el límite de revisiones
- Activa/desactiva el autoguardado
- Gestiona los transients

### Seguridad
- Configura las restricciones de acceso
- Activa las medidas de seguridad
- Gestiona las IPs permitidas

### Desarrollo
- Activa el modo debug cuando sea necesario
- Usa el modo desarrollo para pruebas
- Utiliza las herramientas de migración con precaución

## Soporte

Para soporte y actualizaciones, visita [flowtitude.com](https://flowtitude.com)

## Licencia

Este tema está licenciado bajo GPL v2 o posterior.

