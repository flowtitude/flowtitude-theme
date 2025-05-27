window.TypographyPanel = {
    name: 'TypographyPanel',
    data() {
        return {
            fontBody: '',
            fontDisplay: '',
            baseSize: '',
            scaleFactor: '',
            scaleRatio: '',
            loading: true,
            cssPath: '/wp-content/uploads/windpress/data/theme/flowtitude.css'
        }
    },
    async created() {
        await this.loadTypography();
    },
    methods: {
        async loadTypography() {
            try {
                const response = await fetch(flowtitude_data.ajaxurl + '?action=flowtitude_get_theme_typography&nonce=' + flowtitude_data.ajax_nonce);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.data || 'Error desconocido al cargar la tipografía');
                }
                
                if (data.data) {
                    this.fontBody = data.data.fontBody || '';
                    this.fontDisplay = data.data.fontDisplay || '';
                    this.baseSize = data.data.ftTextValue || '';
                    this.scaleFactor = data.data.ftTextScale || '';
                    this.scaleRatio = data.data.ftTextFactor || '';
                }
            } catch (error) {
                console.error('Error cargando tipografía:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async saveTypography() {
            try {
                const typography = {
                    fontBody: this.fontBody,
                    fontDisplay: this.fontDisplay,
                    ftTextValue: this.baseSize,
                    ftTextScale: this.scaleFactor,
                    ftTextFactor: this.scaleRatio
                };

                const response = await fetch(flowtitude_data.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'flowtitude_save_typography',
                        nonce: flowtitude_data.ajax_nonce,
                        typography: JSON.stringify(typography)
                    })
                });

                if (!response.ok) throw new Error('Error al guardar la configuración');
                
                const result = await response.json();
                if (!result.success) throw new Error(result.data || 'Error al guardar la configuración');

                return result;
            } catch (error) {
                console.error('Error en saveTypography:', error);
                throw error;
            }
        },
        async init() {
            await this.loadInitialValues();
            return this.render();
        },
        async loadInitialValues() {
            try {
                const response = await fetch(this.cssPath);
                const cssText = await response.text();
                
                // Extraer valores usando regex
                const fontBodyMatch = cssText.match(/--font-body:\s*['"]([^'"]+)['"]/);
                const fontDisplayMatch = cssText.match(/--font-display:\s*['"]([^'"]+)['"]/);
                const ftTextValueMatch = cssText.match(/--ft-text-value:\s*([^;]+)/);
                const textScaleMatch = cssText.match(/--ft-text-scale:\s*([^;]+)/);
                const ftTextFactorMatch = cssText.match(/--ft-text-factor:\s*([^;]+)/);

                this.fontBody = fontBodyMatch ? fontBodyMatch[1] : 'Arial';
                this.fontDisplay = fontDisplayMatch ? fontDisplayMatch[1] : 'Poppins';
                this.baseSize = ftTextValueMatch ? ftTextValueMatch[1].trim() : '1rem';
                this.scaleFactor = textScaleMatch ? textScaleMatch[1].trim() : '1.15';
                this.scaleRatio = ftTextFactorMatch ? ftTextFactorMatch[1].trim() : '1';
            } catch (error) {
                console.error('Error loading initial values:', error);
            }
        },
        render() {
            return `
                <div class="panel-section">
                    <h3>Tipografía</h3>
                    <div class="form-section">
                        <div class="form-group">
                            <label class="form-label">Font Body</label>
                            <input type="text" class="form-input" value="${this.fontBody}" data-setting="fontBody">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Font Display</label>
                            <input type="text" class="form-input" value="${this.fontDisplay}" data-setting="fontDisplay">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tamaño Base</label>
                            <input 
                                type="text" 
                                v-model="baseSize"
                                class="form-input"
                                placeholder="Ejemplo: clamp(1rem, 0.9483rem + 0.6897cqi, 1.25rem)"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Escala</label>
                            <div class="range-wrapper">
                                <input 
                                    type="range" 
                                    v-model="scaleFactor"
                                    min="1"
                                    max="2"
                                    step="0.05"
                                    class="range-input"
                                >
                                <span class="range-value">{{ scaleFactor }}x</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Factor</label>
                            <div class="range-wrapper">
                                <input 
                                    type="range" 
                                    v-model="scaleRatio"
                                    min="0.5"
                                    max="1.5"
                                    step="0.05"
                                    class="range-input"
                                >
                                <span class="range-value">{{ scaleRatio }}x</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    },

    template: `
        <div class="form-section">
            <div v-if="loading">Cargando...</div>
            <template v-else>
                <div class="form-group">
                    <label>Font Body</label>
                    <input 
                        type="text" 
                        :value="fontBody"
                        @input="e => { fontBody = e.target.value; saveTypography(); }"
                        class="form-input"
                        placeholder="Ejemplo: Inter, system-ui, sans-serif"
                    >
                </div>

                <div class="form-group">
                    <label>Font Display</label>
                    <input 
                        type="text" 
                        :value="fontDisplay"
                        @input="e => { fontDisplay = e.target.value; saveTypography(); }"
                        class="form-input"
                        placeholder="Ejemplo: Poppins, Helvetica, sans-serif"
                    >
                </div>

                <div class="form-group">
                    <label>Tamaño Base</label>
                    <input 
                        type="text" 
                        :value="baseSize"
                        @input="e => { baseSize = e.target.value; saveTypography(); }"
                        class="form-input"
                        placeholder="Ejemplo: clamp(1rem, 0.9483rem + 0.6897cqi, 1.25rem)"
                    >
                </div>

                <div class="form-group">
                    <label>Escala</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            :value="scaleFactor"
                            @input="e => { scaleFactor = e.target.value; saveTypography(); }"
                            min="1"
                            max="2"
                            step="0.05"
                            class="range-input"
                        >
                        <span class="range-value">{{ scaleFactor }}x</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Factor</label>
                    <div class="range-wrapper">
                        <input 
                            type="range" 
                            :value="scaleRatio"
                            @input="e => { scaleRatio = e.target.value; saveTypography(); }"
                            min="0.5"
                            max="1.5"
                            step="0.05"
                            class="range-input"
                        >
                        <span class="range-value">{{ scaleRatio }}x</span>
                    </div>
                </div>
            </template>
        </div>
    `
}; 