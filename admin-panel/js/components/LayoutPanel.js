window.LayoutPanel = {
    name: 'LayoutPanel',
    data() {
        return {
            loading: true,
            values: {
                ftContainer: '1440px',
                ftPaddingContentX: '2rem',
                ftPaddingContentY: '2rem',
                ftMobileColumns: '1',
                ftTabletColumns: '2',
                ftCard: {
                    xs: '320px',
                    sm: '384px',
                    md: '448px',
                    lg: '512px',
                    xl: '576px'
                }
            },
            _originalValues: null
        }
    },
    template: `
        <div class="layout-panel form-section">
            <div v-if="loading" class="loading">Cargando valores de diseño...</div>
            <template v-else>
                <!-- Container -->
                <div class="form-group">
                    <label>Ancho máximo del contenedor</label>
                    <input 
                        type="text" 
                        :value="values.ftContainer"
                        @input="e => { values.ftContainer = e.target.value; saveLayout(); }"
                        placeholder="Ejemplo: clamp(320px, 90vw, 1440px)"
                    >
                </div>

                <!-- Padding -->
                <div class="form-group">
                    <label>Padding horizontal del contenido</label>
                    <input 
                        type="text" 
                        :value="values.ftPaddingContentX"
                        @input="e => { values.ftPaddingContentX = e.target.value; saveLayout(); }"
                        placeholder="Ejemplo: clamp(1rem, 5vw, 2rem)"
                    >
                </div>

                <div class="form-group">
                    <label>Padding vertical del contenido</label>
                    <input 
                        type="text" 
                        :value="values.ftPaddingContentY"
                        @input="e => { values.ftPaddingContentY = e.target.value; saveLayout(); }"
                        placeholder="Ejemplo: clamp(1rem, 5vw, 2rem)"
                    >
                </div>

                <!-- Columns -->
                <div class="form-group">
                    <label>Columnas en móvil</label>
                    <div class="range-input">
                        <input type="range" :value="values.ftMobileColumns" @input="e => { values.ftMobileColumns = e.target.value; saveLayout(); }" min="1" max="6">
                        <span class="range-value">{{ values.ftMobileColumns }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Columnas en tablet</label>
                    <div class="range-input">
                        <input type="range" :value="values.ftTabletColumns" @input="e => { values.ftTabletColumns = e.target.value; saveLayout(); }" min="1" max="9">
                        <span class="range-value">{{ values.ftTabletColumns }}</span>
                    </div>
                </div>

                <!-- Card Sizes -->
                <div class="card-sizes">
                    <h4>Tamaños de tarjeta</h4>
                    
                    <div class="form-group">
                        <label>XS</label>
                        <input 
                            type="text" 
                            :value="values.ftCard.xs"
                            @input="e => { values.ftCard.xs = e.target.value; saveLayout(); }"
                            placeholder="Ejemplo: clamp(280px, 90vw, 320px)"
                        >
                    </div>

                    <div class="form-group">
                        <label>SM</label>
                        <input 
                            type="text" 
                            :value="values.ftCard.sm"
                            @input="e => { values.ftCard.sm = e.target.value; saveLayout(); }"
                            placeholder="Ejemplo: clamp(320px, 90vw, 384px)"
                        >
                    </div>

                    <div class="form-group">
                        <label>MD</label>
                        <input 
                            type="text" 
                            :value="values.ftCard.md"
                            @input="e => { values.ftCard.md = e.target.value; saveLayout(); }"
                            placeholder="Ejemplo: clamp(384px, 90vw, 448px)"
                        >
                    </div>

                    <div class="form-group">
                        <label>LG</label>
                        <input 
                            type="text" 
                            v-model="values.ftCard.lg"
                            placeholder="Ejemplo: clamp(448px, 90vw, 512px)"
                        >
                    </div>

                    <div class="form-group">
                        <label>XL</label>
                        <input 
                            type="text" 
                            v-model="values.ftCard.xl"
                            placeholder="Ejemplo: clamp(512px, 90vw, 576px)"
                        >
                    </div>
                </div>
            </template>
        </div>
    `,
    async created() {
        await this.loadLayout();
    },
    methods: {
        async loadLayout() {
            try {
                const response = await fetch(flowtitude_data.ajaxurl + '?action=flowtitude_get_theme_layout&nonce=' + flowtitude_data.ajax_nonce);
                if (!response.ok) throw new Error('Error loading layout values');
                
                const result = await response.json();
                if (result.success && result.data) {
                    const data = result.data;
                    this.values = {
                        ftContainer: data.ftContainer || '1440px',
                        ftPaddingContentX: data.ftPaddingContentX || '2rem',
                        ftPaddingContentY: data.ftPaddingContentY || '2rem',
                        ftMobileColumns: String(data.ftMobileColumns || '1'),
                        ftTabletColumns: String(data.ftTabletColumns || '2'),
                        ftCard: {
                            xs: data.ftCard?.xs || '320px',
                            sm: data.ftCard?.sm || '384px',
                            md: data.ftCard?.md || '448px',
                            lg: data.ftCard?.lg || '512px',
                            xl: data.ftCard?.xl || '576px'
                        }
                    };
                    
                    // Guardar una copia de los valores originales
                    this._originalValues = JSON.parse(JSON.stringify(this.values));
                }
            } catch (error) {
                console.error('Error loading layout values:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
        
        async saveLayout() {
            try {
                // Guardar valores actuales antes de validar
                const currentValues = JSON.parse(JSON.stringify(this.values));

                // Validar columnas
                if (this.values.ftMobileColumns < 1 || this.values.ftMobileColumns > 6) {
                    throw new Error('Las columnas en móvil deben estar entre 1 y 6');
                }
                if (this.values.ftTabletColumns < 1 || this.values.ftTabletColumns > 9) {
                    throw new Error('Las columnas en tablet deben estar entre 1 y 9');
                }

                // Validar tamaños y funciones CSS
                const validateSize = (value) => {
                    if (!value) return false;
                    
                    // Permitir funciones CSS
                    const cssFunctions = ['clamp', 'calc', 'min', 'max', 'minmax', 'var'];
                    const hasCssFunction = cssFunctions.some(func => value.includes(`${func}(`));
                    if (hasCssFunction) return true;

                    // Validar valores simples con unidades
                    const units = ['px', 'rem', 'em', 'vw', 'vh', '%'];
                    const hasUnit = units.some(unit => value.endsWith(unit));
                    if (!hasUnit) return false;

                    const numericValue = parseFloat(value);
                    return !isNaN(numericValue) && numericValue > 0;
                };

                // Validar contenedor y padding
                if (!validateSize(this.values.ftContainer)) {
                    throw new Error('El ancho del contenedor debe ser un valor válido con unidad o una función CSS');
                }
                if (!validateSize(this.values.ftPaddingContentX)) {
                    throw new Error('El padding horizontal debe ser un valor válido con unidad o una función CSS');
                }
                if (!validateSize(this.values.ftPaddingContentY)) {
                    throw new Error('El padding vertical debe ser un valor válido con unidad o una función CSS');
                }

                // Validar tamaños de tarjeta
                for (const [size, value] of Object.entries(this.values.ftCard)) {
                    if (!validateSize(value)) {
                        throw new Error(`El tamaño ${size.toUpperCase()} debe ser un valor válido con unidad o una función CSS`);
                    }
                }

                // Preparar los datos para enviar
                const layoutData = {
                    ftContainer: this.values.ftContainer,
                    ftPaddingContentX: this.values.ftPaddingContentX,
                    ftPaddingContentY: this.values.ftPaddingContentY,
                    ftMobileColumns: parseInt(this.values.ftMobileColumns),
                    ftTabletColumns: parseInt(this.values.ftTabletColumns),
                    ftCard: {
                        xs: this.values.ftCard.xs,
                        sm: this.values.ftCard.sm,
                        md: this.values.ftCard.md,
                        lg: this.values.ftCard.lg,
                        xl: this.values.ftCard.xl
                    }
                };

                const formData = new URLSearchParams();
                formData.append('action', 'flowtitude_save_layout');
                formData.append('nonce', flowtitude_data.ajax_nonce);
                formData.append('layout', JSON.stringify(layoutData));

                const response = await fetch(flowtitude_data.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.data || 'Error al guardar la configuración de diseño');
                }

                // Actualizar valores guardados solo si el guardado fue exitoso
                this._originalValues = currentValues;
                this.$emit('saved');
            } catch (error) {
                console.error('Error al guardar el diseño:', error);
                throw error;
            }
        }
    }
}; 