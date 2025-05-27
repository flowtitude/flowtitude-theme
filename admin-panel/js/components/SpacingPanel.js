window.SpacingPanel = {
    name: 'SpacingPanel',
    data() {
        return {
            loading: true,
            values: {
                ftSpaceValue: '',
                ftSpaceScale: '',
                ftSpaceFactor: '',
                blockFactor: 0.666,  // Factor específico para bloques
                columnsFactor: 1,    // Factor específico para columnas
                sectionFactor: 2.666 // Factor específico para secciones
            },
            _originalValues: null
        }
    },
    computed: {
        spacingBlock() {
            return this.values.blockFactor;
        },
        spacingColumns() {
            return this.values.columnsFactor;
        },
        spacingSection() {
            return this.values.sectionFactor;
        }
    },
    template: `
        <div class="form-section">
            <div v-if="loading" class="loading">Cargando valores de espaciado...</div>
            <template v-else>
                <!-- Controles base -->
                <div class="form-group">
                    <label>Tamaño base</label>
                    <input 
                        type="text" 
                        :value="values.ftSpaceValue"
                        @input="e => { values.ftSpaceValue = e.target.value; saveSpacing(); }"
                        class="form-input"
                        placeholder="Ejemplo: clamp(1.125rem, 0.9483rem + 0.6897cqi, 1.5rem)"
                    >
                </div>

                <div class="form-group">
                    <label>Escala</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            :value="values.ftSpaceScale"
                            @input="e => { values.ftSpaceScale = e.target.value; saveSpacing(); }"
                            min="1"
                            max="2"
                            step="0.05"
                            class="range-input"
                        >
                        <span class="range-value">{{ values.ftSpaceScale }}x</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Factor</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            :value="values.ftSpaceFactor"
                            @input="e => { values.ftSpaceFactor = e.target.value; saveSpacing(); }"
                            min="0.5"
                            max="1.5"
                            step="0.05"
                            class="range-input"
                        >
                        <span class="range-value">{{ values.ftSpaceFactor }}x</span>
                    </div>
                </div>

                <div class="separator"></div>

                <!-- Controles específicos -->
                <div class="form-group">
                    <label>Espacio de bloque</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            :value="values.blockFactor"
                            @input="e => { values.blockFactor = e.target.value; saveSpacing(); }"
                            min="0.25"
                            max="2"
                            step="0.001"
                            class="range-input"
                        >
                        <span class="range-value">{{ values.blockFactor }}x</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Espacio de columnas</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            :value="values.columnsFactor"
                            @input="e => { values.columnsFactor = e.target.value; saveSpacing(); }"
                            min="0.25"
                            max="2"
                            step="0.001"
                            class="range-input"
                        >
                        <span class="range-value">{{ values.columnsFactor }}x</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Espacio de sección</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            v-model="values.sectionFactor"
                            min="0.5"
                            max="4"
                            step="0.001"
                            class="range-input"
                        >
                        <span class="range-value">{{ values.sectionFactor }}x</span>
                    </div>
                </div>
            </template>
        </div>
    `,
    async created() {
        await this.loadSpacing();
    },
    methods: {
        async loadSpacing() {
            try {
                const response = await fetch(flowtitude_data.ajaxurl + '?action=flowtitude_get_theme_spacing&nonce=' + flowtitude_data.ajax_nonce);
                if (!response.ok) throw new Error('Error loading spacing values');
                
                const result = await response.json();
                if (result.success && result.data) {
                    const data = result.data;
                    
                    // Función auxiliar para extraer el multiplicador de la fórmula calc()
                    const extractMultiplier = (value) => {
                        if (!value) return null;
                        const match = value.match(/calc\(var\(--ft-space-value\)\s*\*\s*([\d.]+)\)/);
                        return match ? parseFloat(match[1]) : null;
                    };

                    this.values = {
                        ftSpaceValue: data.ftSpaceValue || '',
                        ftSpaceScale: data.ftSpaceScale || '',
                        ftSpaceFactor: data.ftSpaceFactor || '',
                        blockFactor: extractMultiplier(data.spacingBlock) || 0.666,
                        columnsFactor: extractMultiplier(data.spacingColumns) || 1,
                        sectionFactor: extractMultiplier(data.spacingSection) || 2.666
                    };

                    // Guardar una copia de los valores originales
                    this._originalValues = JSON.parse(JSON.stringify(this.values));
                }
            } catch (error) {
                console.error('Error loading spacing values:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
        
        async saveSpacing() {
            try {
                // Validar y sanitizar los valores antes de enviar
                const sanitizedValues = {
                    ftSpaceValue: this.values.ftSpaceValue?.trim() || '',
                    ftSpaceScale: parseFloat(this.values.ftSpaceScale) || 1,
                    ftSpaceFactor: parseFloat(this.values.ftSpaceFactor) || 1,
                    spacingBlock: parseFloat(this.values.blockFactor) || 0.666,
                    spacingColumns: parseFloat(this.values.columnsFactor) || 1,
                    spacingSection: parseFloat(this.values.sectionFactor) || 2.666
                };

                // Validar que los valores estén dentro de rangos aceptables
                if (sanitizedValues.ftSpaceScale < 1 || sanitizedValues.ftSpaceScale > 2) {
                    throw new Error('La escala debe estar entre 1 y 2');
                }
                if (sanitizedValues.ftSpaceFactor < 0.5 || sanitizedValues.ftSpaceFactor > 1.5) {
                    throw new Error('El factor debe estar entre 0.5 y 1.5');
                }

                const response = await fetch(flowtitude_data.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'flowtitude_save_spacing',
                        nonce: flowtitude_data.ajax_nonce,
                        spacing: JSON.stringify(sanitizedValues)
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Error en la respuesta del servidor al guardar el espaciado');
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.data || 'Error al guardar los valores de espaciado');
                }

                // Actualizar los valores originales después de un guardado exitoso
                this._originalValues = JSON.parse(JSON.stringify(sanitizedValues));

                // Actualizar los valores locales manteniendo los factores como números
                this.values = {
                    ftSpaceValue: sanitizedValues.ftSpaceValue,
                    ftSpaceScale: sanitizedValues.ftSpaceScale,
                    ftSpaceFactor: sanitizedValues.ftSpaceFactor,
                    blockFactor: sanitizedValues.spacingBlock,
                    columnsFactor: sanitizedValues.spacingColumns,
                    sectionFactor: sanitizedValues.spacingSection
                };

                return result;
            } catch (error) {
                // En caso de error, restaurar los valores originales
                if (this._originalValues) {
                    this.values = JSON.parse(JSON.stringify(this._originalValues));
                }
                console.error('Error en saveSpacing:', error);
                throw error;
            }
        }
    }
}; 