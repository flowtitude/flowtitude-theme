## 🐛 Sistema de Logging

El tema incluye un **sistema de logging unificado** que se maneja completamente desde el panel de administración. No es necesario modificar `wp-config.php`.

### Activación de Logs

1. Ve a `WordPress Admin > Flowtitude > Security > Debug`
2. Activa las opciones de logging que necesites:
   - **Activar modo debug**: Habilita WP_DEBUG
   - **Escribir errores en log**: Habilita WP_DEBUG_LOG
   - **Registrar hooks y acciones**: Logging detallado de hooks

### Ubicación de Logs

- **Por defecto**: `/wp-content/debug.log`
- **Personalizada**: Configurable desde el panel de administración

### Formato de Entradas

```
[2024-01-15 10:30:45] [Flowtitude INFO] [init] Mu-plugin unificado cargado
[2024-01-15 10:30:46] [Flowtitude INFO] [security] Versión de WordPress oculta
[2024-01-15 10:30:47] [Flowtitude DEBUG] [hooks] Hook ejecutado: wp_loaded
```

### Configuración Avanzada

Para configuraciones específicas, consulta la documentación completa del mu-plugin en `docs/mu-plugin-configuration.md`. 