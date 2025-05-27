/**
 * Utilidades para el manejo de colores
 */

window.colorUtils = {
    /**
     * Parsea un string OKLCH a un objeto
     */
    parseOklchString(oklchStr) {
        if (typeof oklchStr !== 'string') return null;
        if (!oklchStr.toLowerCase().startsWith('oklch')) return null;
        
        const match = oklchStr.match(/oklch\(([\d.]+)%?\s+([\d.]+)\s+([\d.]+)\)/i);
        if (!match) return null;
        
        let l = parseFloat(match[1]);
        if (oklchStr.includes('%')) {
            l = l / 100;
        }
        
        return {
            l: l,
            c: parseFloat(match[2]),
            h: parseFloat(match[3])
        };
    },

    /**
     * Convierte sRGB a linear RGB
     */
    sRGBToLinear(x) {
        if (x <= 0.04045) {
            return x / 12.92;
        }
        return Math.pow((x + 0.055) / 1.055, 2.4);
    },

    /**
     * Convierte linear RGB a sRGB
     */
    linearTosRGB(x) {
        if (x <= 0.0031308) {
            return 12.92 * x;
        }
        return 1.055 * Math.pow(x, 1/2.4) - 0.055;
    },

    /**
     * Convierte HEX a RGB (valores entre 0 y 1)
     */
    hexToRGB(hex) {
        const r = parseInt(hex.slice(1, 3), 16) / 255;
        const g = parseInt(hex.slice(3, 5), 16) / 255;
        const b = parseInt(hex.slice(5, 7), 16) / 255;
        return { r, g, b };
    },

    /**
     * Convierte RGB a HEX
     */
    rgbToHex(r, g, b) {
        const toHex = (n) => Math.round(n * 255).toString(16).padStart(2, '0');
        return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
    },

    /**
     * Convierte HEX a OKLCH
     */
    hexToOKLCH(hex) {
        if (!hex || !hex.startsWith('#')) return null;
        
        // Convertir HEX a RGB
        const { r, g, b } = this.hexToRGB(hex);
        
        // Calcular luminosidad percibida
        const perceivedL = 0.2126 * r + 0.7152 * g + 0.0722 * b;
        
        // Convertir a OKLCH
        const oklch = this._rgbToOKLCH(r, g, b);
        
        // Ajustar luminosidad para mantener la percepción
        oklch.l = Math.max(0.01, Math.min(0.99, perceivedL));
        
        return `oklch(${(oklch.l * 100).toFixed(6)}% ${oklch.c.toFixed(6)} ${oklch.h.toFixed(6)})`;
    },

    /**
     * Convierte OKLCH a HEX
     */
    oklchToHex(color) {
        if (!color || color.startsWith('#')) return color;
        
        const oklch = this.parseOklchString(color);
        if (!oklch) return '#000000';
        
        // Preservar la luminosidad original
        const originalL = oklch.l;
        
        // Convertir a RGB
        const rgb = this._oklchToRGB(oklch);
        
        // Ajustar RGB manteniendo la luminosidad relativa
        const currentL = 0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b;
        const factor = originalL / (currentL || 1);
        
        rgb.r = Math.max(0, Math.min(1, rgb.r * factor));
        rgb.g = Math.max(0, Math.min(1, rgb.g * factor));
        rgb.b = Math.max(0, Math.min(1, rgb.b * factor));
        
        return this.rgbToHex(rgb.r, rgb.g, rgb.b);
    },

    /**
     * Genera una escala de color
     */
    generateColorScale(baseColor) {
        const baseHex = this.oklchToHex(baseColor);
        const baseOklch = this.parseOklchString(this.hexToOKLCH(baseHex));
        if (!baseOklch) return {};

        const scale = {
            500: baseHex,
            DEFAULT: baseHex
        };

        const lightnessFactors = {
            50: 0.95,
            100: 0.9,
            200: 0.8,
            300: 0.7,
            400: 0.6,
            600: 0.4,
            700: 0.3,
            800: 0.2,
            900: 0.1,
            950: 0.05
        };

        // Generar escala manteniendo croma y tono
        for (const [key, factor] of Object.entries(lightnessFactors)) {
            const newL = Math.max(0.01, Math.min(0.99, factor));
            const oklchColor = `oklch(${(newL * 100).toFixed(4)}% ${baseOklch.c.toFixed(4)} ${baseOklch.h.toFixed(4)})`;
            scale[key] = this.oklchToHex(oklchColor);
        }

        return scale;
    },

    /**
     * Verifica si un color está en formato OKLCH
     */
    isOklchColor(color) {
        return color && typeof color === 'string' && color.toLowerCase().startsWith('oklch');
    },

    /**
     * Aclara un color por un factor
     * @param {string} color - Color en formato HEX o OKLCH
     * @param {number} factor - Factor de aclarado (0-1)
     * @returns {string} Color aclarado en el mismo formato que el input
     */
    lighten(color, factor) {
        return this.adjustColor(color, factor, 'lighten');
    },

    /**
     * Oscurece un color por un factor
     * @param {string} color - Color en formato HEX o OKLCH
     * @param {number} factor - Factor de oscurecimiento (0-1)
     * @returns {string} Color oscurecido en el mismo formato que el input
     */
    darken(color, factor) {
        return this.adjustColor(color, factor, 'darken');
    },

    /**
     * Ajusta un color
     * @private
     */
    adjustColor(color, factor, mode) {
        const wasOklch = this.isOklchColor(color);
        const oklch = wasOklch ? this.parseOklchString(color) : this.parseOklchString(this.hexToOKLCH(color));
        
        const adjustedL = mode === 'lighten' 
            ? oklch.l + (1 - oklch.l) * factor
            : oklch.l * (1 - factor);

        const adjusted = `oklch(${(adjustedL * 100).toFixed(4)}% ${oklch.c.toFixed(4)} ${oklch.h.toFixed(4)})`;
        return wasOklch ? adjusted : this.oklchToHex(adjusted);
    },

    _rgbToOKLCH(r, g, b) {
        // Convertir a linear RGB
        const r_lin = this.sRGBToLinear(r);
        const g_lin = this.sRGBToLinear(g);
        const b_lin = this.sRGBToLinear(b);
        
        // Matriz de conversión a OKLab
        const l = 0.4122214708 * r_lin + 0.5363325363 * g_lin + 0.0514459929 * b_lin;
        const m = 0.2119034982 * r_lin + 0.6806995451 * g_lin + 0.1073969566 * b_lin;
        const s = 0.0883024619 * r_lin + 0.2817188376 * g_lin + 0.6299787005 * b_lin;

        const l_ = Math.cbrt(l);
        const m_ = Math.cbrt(m);
        const s_ = Math.cbrt(s);

        const L = 0.2104542553 * l_ + 0.7936177850 * m_ - 0.0040720468 * s_;
        const a = 1.9779984951 * l_ - 2.4285922050 * m_ + 0.4505937099 * s_;
        const b_ = 0.0259040371 * l_ + 0.7827717662 * m_ - 0.8086757660 * s_;

        // Convertir a LCH
        const C = Math.sqrt(a * a + b_ * b_);
        let h = Math.atan2(b_, a) * 180 / Math.PI;
        if (h < 0) h += 360;

        return { l: L, c: C, h: h };
    },

    _oklchToRGB(oklch) {
        // Convertir LCH a Lab
        const a = oklch.c * Math.cos(oklch.h * Math.PI / 180);
        const b = oklch.c * Math.sin(oklch.h * Math.PI / 180);
        
        // Matriz inversa de OKLab a LMS
        const l_ = oklch.l + 0.3963377774 * a + 0.2158037573 * b;
        const m_ = oklch.l - 0.1055613458 * a - 0.0638541728 * b;
        const s_ = oklch.l - 0.0894841775 * a - 1.2914855480 * b;

        // Elevar al cubo
        const l = l_ * l_ * l_;
        const m = m_ * m_ * m_;
        const s = s_ * s_ * s_;

        // Matriz inversa de LMS a RGB lineal
        const r_lin = +4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s;
        const g_lin = -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s;
        const b_lin = -0.0041960863 * l - 0.7034186147 * m + 1.7076147010 * s;

        // Convertir a sRGB
        return {
            r: this.linearTosRGB(r_lin),
            g: this.linearTosRGB(g_lin),
            b: this.linearTosRGB(b_lin)
        };
    }
}; 