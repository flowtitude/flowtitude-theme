
---

## 📄 `README.prompt.md`

```markdown
# 🧠 Flujo de trabajo con IA para el desarrollo del tema Flowtitude

Este documento resume cómo usar el asistente de IA en este proyecto (`flowtitude-theme`), qué reglas debe seguir y cómo comenzar una revisión o desarrollo asistido.

## 📌 Qué sabe la IA sobre este proyecto

- Es un tema hijo para Bricks Builder con funcionalidades avanzadas.
- Usa Vue 3 para un panel de administración moderno sin herramientas de build.
- Integra con WindPress solo si se usa un dashboard Bricks.
- Gestiona capas CSS (WordPress, Plugins, Bricks, Theme) con prioridad correcta.
- Snippets activables y organizados desde el panel.
- Toda la lógica sigue estándares de WordPress y estructura modular.

## 🧭 Cómo empezar una tarea con la IA

Puedes pedirle cosas como:

- `Revísame la estructura del tema y dime si está bien organizada.`
- `Ayúdame a mejorar este snippet, tiene problemas de seguridad.`
- `¿Dónde debería colocar este componente Vue?`
- `Necesito un endpoint REST para guardar esta opción del panel.`

## ✅ Buenas prácticas al interactuar

- Indícale qué archivo estás editando o en qué carpeta estás.
- Si estás creando algo nuevo, dile dónde debe ir según la estructura.
- Pregunta por seguridad, rendimiento, compatibilidad con WordPress.
- Puedes pedirle sugerencias para nombres de funciones o comentarios.

---

Este proyecto está conectado a OpenRouter con el modelo GPT-4o. Las reglas generales están definidas en `prompts/global-rules.md` y las reglas específicas del proyecto en `prompts/project-rules.md`.
