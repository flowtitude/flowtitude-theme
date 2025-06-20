# Proyecto: Tema Hijo Flowtitude v2

## 🎯 Objetivo del Proyecto

Este tema hijo de WordPress extiende Bricks Builder con:
- Panel de administración moderno en Vue 3 (sin herramientas de build).
- Sistema modular de snippets y configuración avanzada.
- Soporte condicional para Tailwind si se reemplaza el dashboard.
- Estructura optimizada para capas CSS (`@layer`) sin dependencias de build externo.

## 🛠️ Características clave

- **Vue 3** cargado solo en admin (`/admin-panel/js`), sin herramientas de build.
- **Snippets PHP** organizados y activables desde el panel.
- **Capas CSS** bien estructuradas: WordPress, Plugins, Bricks, Theme.
- **Reemplazo de dashboard de WP** opcional usando una plantilla de Bricks.
- Si se usa plantilla Bricks como dashboard: se debe cargar `windpress/cache/tailwind.css` y ajustar las capas CSS.

## ✅ Reglas específicas para IA

```plaintext
- No asumir uso de Tailwind salvo que se use una plantilla Bricks como dashboard.
- Si se usa Bricks como dashboard, asegurar carga de:
    - /wp-content/uploads/windpress/cache/tailwind.css
    - Estilos adicionales por capas con prioridad adecuada.
- Mantener los estilos de WordPress intactos salvo en el dashboard.
- Seguir las normas de WordPress para temas:
    - Usa `get_template_part()` para plantillas.
    - Scripts JS en `/admin-panel/js` como módulos ESM.
    - No usar frameworks JS adicionales.
    - Snippets con comentarios estructurados (título, descripción).
    - Validación con `current_user_can()`, `sanitize_*` y `wp_nonce_*` en funciones PHP.
    - No usar estilos inline ni `<style>` embebidos.
- El código debe funcionar con Bricks activo ya que es un tema hijo de Bricks.

📁 Estructura esperada
flowtitude-theme/
├── admin-panel/
│   └── js/                  # Vue JS sin build
├── inc/
│   ├── core/                # Núcleo del tema
│   ├── features/            # Opcionales
│   ├── security/            # Seguridad avanzada
│   └── settings/            # Configuración vía panel
├── snippets/                # Snippets activables
├── woocommerce/             # Plantillas personalizadas
├── functions.php
├── style.css
├── flowtitude-theme.php

🧠 Contexto adicional para la IA

Este tema no compila Tailwind. Cuando se usa, lo hace WindPress.

Si el dashboard de WP se reemplaza por Bricks, entonces sí se carga Tailwind.

La prioridad de capas es crítica. Las clases de Tailwind deben verse bien, sin romper WP.

En este entorno, el código debe proponer comandos GIT pero no hacer push remoto.