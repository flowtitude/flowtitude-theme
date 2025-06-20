# Reglas generales: desarrollo de temas en WordPress

Estas reglas aplican a todos los proyectos de desarrollo de temas para WordPress.

## 🎯 Enfoque general

- El tema debe seguir las buenas prácticas de desarrollo de **temas clásicos**, sin depender de herramientas externas como Node o Webpack.
- Se permite usar **Vue.js** para pequeños bloques de interfaz, embebido de forma ligera y controlada (sin entorno de build externo).
- Los scripts de Vue deben cargarse de forma asincrónica si es posible, y nunca bloquear el render inicial.

## 💾 Estructura y convenciones

- Usa `functions.php` para registrar scripts, estilos, zonas de widgets, y soporte a funciones del core.
- Crea archivos separados para lógica de API, clases PHP, helpers, y funciones de utilidad.
- Usa `get_template_part()` y `template-parts/` para secciones reutilizables.
- Estructura semántica: `header.php`, `footer.php`, `single.php`, `page.php`, etc.
- Evita lógica en plantillas. Usa funciones o clases separadas.
- Los archivos `.php` deben empezar con `<?php` y nunca cerrar el bloque si no hay HTML fuera.

## 🧠 Estándares WordPress

- Sigue las guías de codificación de [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- Escapa siempre HTML (`esc_html()`, `esc_attr()`, `wp_kses()`).
- Usa funciones específicas del core para acceder a datos (`get_post_meta()`, `wp_get_post_terms()`, etc.).
- No uses `query_posts()`. Usa `WP_Query` o `get_posts()`.

## 🔒 Seguridad

- Valida y escapa entradas y salidas.
- Usa `nonces` y `current_user_can()` en los endpoints PHP.
- Los endpoints deben estar en archivos separados o cargados desde `functions.php`.

## 💡 Buenas prácticas

- Usa hooks (`add_action`, `add_filter`) para extender funcionalidades.
- Modulariza funciones en archivos como `inc/assets.php`, `inc/api.php`, etc.
- Comenta funciones, explica la lógica y añade tipos en los parámetros.
