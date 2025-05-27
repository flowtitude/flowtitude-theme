window.ColorPanel = {
    name: 'ColorPanel',
    data() {
        return {
            primaryColor: '',
            secondaryColor: '',
            customColors: [],
            newColorName: '',
            newColorValue: '',
            showColorForm: false,
            loading: true,
            ftColorText: '',
            ftColorBackground: '',
            _originalColors: null,
            _lastSavedColors: null,
            cssPath: flowtitude_data.upload_url + '/windpress/data/theme/flowtitude.css'
        }
    },
    async created() {
        await this.loadColors();
    },
    methods: {
        async loadColors() {
            try {
                const response = await fetch(flowtitude_data.ajaxurl + '?action=flowtitude_get_theme_colors&nonce=' + flowtitude_data.ajax_nonce);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.data || 'Error desconocido al cargar los colores');
                }
                
                if (data.data) {
                    const colors = data.data;
                    
                    // Función auxiliar para convertir colores usando culori
                    const convertColor = (color) => {
                        if (!color) return '';
                        // Si ya es HEX, devolverlo tal cual
                        if (color.startsWith('#')) return color;
                        // Si es OKLCH, convertirlo a HEX usando culori
                        const hex = window.colorUtils.oklchToHex(color);
                        return hex || color; // Si falla la conversión, mantener el color original
                    };

                    // Convertir colores principales
                    const primary = colors.primary && colors.primary['500'];
                    const secondary = colors.secondary && colors.secondary['500'];
                    
                    this.primaryColor = primary ? convertColor(primary) : '';
                    this.secondaryColor = secondary ? convertColor(secondary) : '';
                    this.ftColorText = colors.text ? convertColor(colors.text) : '';
                    this.ftColorBackground = colors.background ? convertColor(colors.background) : '';

                    // Convertir colores personalizados
                    this.customColors = Object.entries(colors.customColors || {})
                        .map(([name, value]) => ({
                            name: name.charAt(0).toUpperCase() + name.slice(1),
                            baseColor: value && value['500'] ? convertColor(value['500']) : ''
                        }));

                    // Guardar los colores originales
                    this._originalColors = {
                        primary: this.primaryColor,
                        secondary: this.secondaryColor,
                        text: this.ftColorText,
                        background: this.ftColorBackground,
                        custom: this.customColors.map(c => ({ ...c }))
                    };
                    
                    // Guardar una copia para restauración
                    this._lastSavedColors = JSON.parse(JSON.stringify(this._originalColors));
                }
            } catch (error) {
                console.error('Error cargando los colores:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
        
        isHexColor(color) {
            return color && color.startsWith('#');
        },
        
        isOklchColor(color) {
            return color && color.toLowerCase().startsWith('oklch');
        },

        convertColorToHex(color) {
            if (!color) return '';
            if (this.isHexColor(color)) return color;
            if (this.isOklchColor(color)) return window.colorUtils.oklchToHex(color);
            return color;
        },

        convertColorToOklch(color) {
            if (!color) return '';
            if (this.isOklchColor(color)) return color;
            if (this.isHexColor(color)) return window.colorUtils.hexToOKLCH(color);
            return color;
        },

        expandShortHex(hex) {
            if (hex.length === 4) {
                return '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
            }
            return hex;
        },

        prepareColorsForSave() {
            const prepareColor = (hex) => {
                if (!hex) return null;
                
                // Asegurarnos de que el color base esté en formato HEX
                const baseHex = this.convertColorToHex(hex);
                if (!baseHex) return null;
                
                return {
                    DEFAULT: baseHex,
                    500: baseHex,
                    ...window.colorUtils.generateColorScale(baseHex)
                };
            };

            const colors = {
                primary: prepareColor(this.primaryColor),
                secondary: prepareColor(this.secondaryColor),
                text: this.ftColorText ? this.convertColorToHex(this.ftColorText) : null,
                background: this.ftColorBackground ? this.convertColorToHex(this.ftColorBackground) : null,
                customColors: {}
            };

            // Preparar colores personalizados
            this.customColors.forEach(color => {
                if (color.name && color.baseColor) {
                    colors.customColors[color.name.toLowerCase()] = prepareColor(color.baseColor);
                }
            });

            return colors;
        },

        async saveColors() {
            try {
                const themeColors = this.prepareColorsForSave();
                const response = await fetch(flowtitude_data.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'flowtitude_save_theme_colors',
                        nonce: flowtitude_data.ajax_nonce,
                        colors: JSON.stringify(themeColors)
                    })
                });

                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.data || 'Error al guardar los colores');
                }

                // Actualizar los últimos colores guardados
                this._lastSavedColors = {
                    primary: this.primaryColor,
                    secondary: this.secondaryColor,
                    text: this.ftColorText,
                    background: this.ftColorBackground,
                    custom: this.customColors.map(c => ({ ...c }))
                };

                return result;
            } catch (error) {
                // En caso de error, restaurar los últimos colores guardados
                if (this._lastSavedColors) {
                    this.primaryColor = this._lastSavedColors.primary;
                    this.secondaryColor = this._lastSavedColors.secondary;
                    this.ftColorText = this._lastSavedColors.text;
                    this.ftColorBackground = this._lastSavedColors.background;
                    this.customColors = this._lastSavedColors.custom.map(c => ({ ...c }));
                }
                console.error('Error en saveColors:', error);
                throw error;
            }
        },

        addNewColor() {
            if (this.newColorName && this.newColorValue) {
                this.customColors.push({
                    name: this.newColorName,
                    baseColor: this.newColorValue
                });
                this.newColorName = '';
                this.newColorValue = '';
                this.showColorForm = false;
            }
        },

        removeColor(index) {
            this.customColors.splice(index, 1);
        },

        updateColors() {
            // Solo emitir el evento de cambio
            this.$emit('colors-updated');
        }
    },
    template: `
        <div class="color-panel">
            <div v-if="loading" class="loading">Cargando colores...</div>
            <template v-else>
                <div class="form-group">
                    <label>Primary</label>
                    <div>
                        <input type="color" v-model="primaryColor" @input="updateColors" style="margin-right: 2px;">
                        <button class="btn-remove disabled" disabled title="No se puede eliminar el color primario">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Secondary</label>
                    <div>
                        <input type="color" v-model="secondaryColor" @input="updateColors" style="margin-right: 2px;">
                        <button class="btn-remove disabled" disabled title="No se puede eliminar el color secundario">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Colores personalizados -->
                <div v-for="(color, index) in customColors" :key="color.name" class="form-group" style="margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <input 
                        type="text" 
                        v-model="color.name"
                        class="color-label"
                        style="flex: 1; margin-right: 8px;"
                        @input="updateColors"
                    >
                    <div>
                        <input 
                            type="color" 
                            v-model="color.baseColor" 
                            @input="updateColors"
                            style="margin-right: 2px;"
                        >
                        <button 
                            @click="removeColor(index)"
                            class="btn-remove"
                            title="Eliminar color"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Formulario para añadir nuevo color -->
                <div v-if="showColorForm" class="form-group">
                    <input 
                        type="text" 
                        v-model="newColorName"
                        placeholder="Nombre del color"
                        class="color-label"
                    >
                    <div>
                        <input 
                            type="color" 
                            v-model="newColorValue"
                            style="margin-right: 2px;"
                        >
                        <button 
                            @click="addNewColor"
                            :disabled="!newColorName || !newColorValue"
                            class="btn-add"
                            title="Añadir color"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Botón para mostrar formulario -->
                <button 
                    v-if="!showColorForm"
                    @click="showColorForm = true"
                    class="btn-panel"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Añadir nuevo color
                </button>

                <!-- Separador -->
                <div style="height: 1px; background-color: #e5e7eb; margin: 16px 0;"></div>

                <!-- Colores base -->
                <div class="form-group">
                    <label>Color de texto</label>
                    <div>
                        <input 
                            type="color" 
                            v-model="ftColorText" 
                            @input="updateColors"
                            style="margin-right: 2px;"
                        >
                        <button class="btn-remove disabled" disabled title="No se puede eliminar el color de texto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Color de fondo</label>
                    <div>
                        <input 
                            type="color" 
                            v-model="ftColorBackground" 
                            @input="updateColors"
                            style="margin-right: 2px;"
                        >
                        <button class="btn-remove disabled" disabled title="No se puede eliminar el color de fondo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    `
}; 