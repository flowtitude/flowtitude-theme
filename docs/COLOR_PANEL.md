# Panel de Personalización de Colores

## Objetivo
Implementar un sistema de personalización de colores que permita:
1. Modificar colores primarios y secundarios
2. Añadir colores personalizados
3. Generar escalas de color automáticamente
4. Previsualización en tiempo real
5. Guardado de configuraciones

## Estructura de Datos

### Variables de Color en flowtitude.css
```css
/* Colores Base */
--color-primary-500: #000000;
--color-secondary-500: #000000;
--color-background: #ffffff;
--color-text: #000000;

/* Colores Personalizados */
--color-custom-1-500: #000000;
--color-custom-2-500: #000000;
```

### Estructura de Datos en JavaScript
```javascript
const colorConfig = {
  primary: {
    base: '#000000',
    scale: {
      50: 'oklch(...)',
      100: 'oklch(...)',
      // ... hasta 950
    }
  },
  secondary: {
    base: '#000000',
    scale: {
      50: 'oklch(...)',
      100: 'oklch(...)',
      // ... hasta 950
    }
  },
  background: '#ffffff',
  text: '#000000',
  custom: [
    {
      name: 'custom-1',
      base: '#000000',
      scale: {
        50: 'oklch(...)',
        100: 'oklch(...)',
        // ... hasta 950
      }
    }
  ]
}
```

## Funciones Generales Necesarias

### 1. Lectura de Variables CSS
```javascript
/**
 * Lee las variables CSS del archivo flowtitude.css
 * @returns {Object} Objeto con las variables CSS
 */
function readCSSVariables() {
  // Implementación
}
```

### 2. Guardado de Variables CSS
```javascript
/**
 * Guarda las variables CSS en flowtitude.css
 * @param {Object} variables - Objeto con las variables a guardar
 */
function saveCSSVariables(variables) {
  // Implementación
}
```

### 3. Conversión de Colores
```javascript
/**
 * Convierte HEX a OKLCH
 * @param {string} hex - Color en formato HEX
 * @returns {string} Color en formato OKLCH
 */
function hexToOKLCH(hex) {
  // Implementación
}

/**
 * Convierte OKLCH a HEX
 * @param {string} oklch - Color en formato OKLCH
 * @returns {string} Color en formato HEX
 */
function oklchToHex(oklch) {
  // Implementación
}
```

### 4. Generación de Escalas
```javascript
/**
 * Genera una escala de color basada en un color base
 * @param {string} baseColor - Color base en formato HEX
 * @returns {Object} Escala de colores del 50 al 950
 */
function generateColorScale(baseColor) {
  // Implementación
}
```

### 5. Inyección de Estilos
```javascript
/**
 * Inyecta estilos en el preview
 * @param {Object} styles - Objeto con los estilos a inyectar
 */
function injectStyles(styles) {
  // Implementación
}
```

## Flujo de Trabajo

1. **Inicialización**
   - Leer variables CSS existentes
   - Cargar en el estado de la aplicación
   - Inicializar controles con valores actuales

2. **Modificación**
   - Usuario modifica color base
   - Se genera nueva escala
   - Se actualiza preview
   - Se guardan cambios

3. **Guardado**
   - Validar cambios
   - Convertir a formato CSS
   - Guardar en archivo
   - Actualizar preview

## Componentes Vue Necesarios

1. **ColorPicker**
   - Selector de color
   - Preview del color
   - Input HEX/OKLCH

2. **ColorScalePreview**
   - Muestra escala completa
   - Preview de uso en componentes

3. **ColorControls**
   - Controles de color base
   - Selector de tipo (primary/secondary/custom)
   - Botones de acción

## Validaciones

1. **Formato de Color**
   - Validar formato HEX
   - Validar formato OKLCH
   - Manejar errores de conversión

2. **Contraste**
   - Validar contraste con fondo
   - Validar contraste con texto
   - Sugerir ajustes si es necesario

3. **Accesibilidad**
   - Cumplir WCAG 2.1
   - Mantener ratios de contraste
   - Proporcionar alternativas

## Notas Técnicas

1. **Rendimiento**
   - Optimizar conversiones de color
   - Minimizar actualizaciones DOM
   - Usar debounce/throttle

2. **Compatibilidad**
   - Fallbacks para navegadores antiguos
   - Polyfills necesarios
   - Detección de soporte

3. **Persistencia**
   - Guardado automático
   - Historial de cambios
   - Sistema de backup 