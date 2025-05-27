window.DesignSettings = {
    name: 'DesignSettings',
    props: {
        activeSection: {
            type: String,
            default: 'colors'
        },
        demo: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            currentSection: this.activeSection,
            selectedDemo: 'landing',
            demos: [
                { id: 'landing', label: 'Landing Page' },
                { id: 'blog', label: 'Blog' },
                { id: 'design', label: 'Design System' }
            ],
            sections: [
                { 
                    id: 'colors',
                    label: 'Colores',
                    component: 'ColorPanel'
                },
                {
                    id: 'typography',
                    label: 'Tipografía',
                    component: 'TypographyPanel'
                },
                {
                    id: 'spacing',
                    label: 'Espacios',
                    component: 'SpacingPanel'
                },
                {
                    id: 'layout',
                    label: 'Layout',
                    component: 'LayoutPanel'
                }
            ]
        };
    },
    methods: {
        setActiveSection(section) {
            this.currentSection = section;
        },
        navigateToSection(sectionId) {
            this.currentSection = sectionId;
            this.$router.push({ 
                name: 'design',
                query: { section: sectionId }
            });
        },
        changeDemo(demoId) {
            this.selectedDemo = demoId;
            this.loadDemo(demoId);
        },
        loadDemo(demoId) {
            const iframe = this.$refs.demoFrame;
            if (iframe) {
                iframe.src = `${flowtitude_data.theme_url}/admin-panel/previews/${demoId}.html`;
            }
        },
        initializeFromRoute() {
            const section = this.$route.query.section;
            if (section && this.sections.some(s => s.id === section)) {
                this.currentSection = section;
            }
        },
        showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `ft-notification ft-notification-${type}`;
            notification.textContent = message;
            
            // Estilos inline para asegurar que se muestren
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 12px 24px;
                border-radius: 4px;
                color: white;
                font-size: 14px;
                z-index: 9999;
                box-shadow: 0 3px 6px rgba(0,0,0,0.16);
                transition: opacity 0.3s ease;
                white-space: pre-line;
            `;

            // Establecer color de fondo según el tipo
            if (type === 'success') {
                notification.style.backgroundColor = '#10b981';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#ef4444';
            } else if (type === 'info') {
                notification.style.backgroundColor = '#3b82f6';
            }

            document.body.appendChild(notification);

            // Eliminar después de la duración especificada
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, type === 'error' ? 5000 : 3000);
        },
        async saveChanges() {
            try {
                const panelsToSave = [
                    { ref: 'colorsPanel', method: 'saveColors', label: 'colores' },
                    { ref: 'typographyPanel', method: 'saveTypography', label: 'tipografía' },
                    { ref: 'spacingPanel', method: 'saveSpacing', label: 'espaciado' },
                    { ref: 'layoutPanel', method: 'saveLayout', label: 'layout' }
                ];

                const savePromises = [];
                const failedPanels = [];

                for (const panel of panelsToSave) {
                    const panelRef = this.$refs[panel.ref];
                    
                    if (!panelRef || !panelRef[0]) {
                        console.warn(`Panel ${panel.label} no encontrado`);
                        continue;
                    }

                    try {
                        if (typeof panelRef[0][panel.method] !== 'function') {
                            throw new Error(`Método ${panel.method} no encontrado en el panel de ${panel.label}`);
                        }
                        
                        savePromises.push(
                            panelRef[0][panel.method]()
                                .catch(error => {
                                    failedPanels.push({ 
                                        panel: panel.label, 
                                        error: error.message || `Error al guardar ${panel.label}`
                                    });
                                    return null;
                                })
                        );
                    } catch (error) {
                        failedPanels.push({ 
                            panel: panel.label, 
                            error: error.message || `Error al guardar ${panel.label}`
                        });
                    }
                }

                // Esperar a que todos los guardados se completen
                await Promise.all(savePromises);

                // Manejar resultados
                if (failedPanels.length > 0) {
                    const errorMessages = failedPanels
                        .map(f => `${f.panel}: ${f.error}`)
                        .join('\n');
                    throw new Error(`Error al guardar los siguientes paneles:\n${errorMessages}`);
                }

                // Mostrar notificación de éxito
                this.showNotification('Todos los cambios han sido guardados correctamente', 'success');

                // Emitir evento de guardado exitoso
                this.$emit('save-success');

                // Recargar el iframe de demostración si existe
                const iframe = this.$refs.demoFrame;
                if (iframe) {
                    iframe.contentWindow.location.reload();
                }

            } catch (error) {
                console.error('Error al guardar los cambios:', error);
                
                // Mostrar notificación de error
                this.showNotification(error.message || 'Error al guardar los cambios', 'error');

                // Emitir evento de error
                this.$emit('save-error', error);
            }
        }
    },
    watch: {
        activeSection(newValue) {
            this.currentSection = newValue;
        },
        currentSection(newValue) {
            if (newValue !== this.$route.query.section) {
                this.$router.push({
                    query: { ...this.$route.query, section: newValue }
                });
            }
        },
        '$route'(to) {
            this.initializeFromRoute();
        }
    },
    mounted() {
        this.initializeFromRoute();
        this.loadDemo(this.selectedDemo);
    },
    template: `
        <div class="design-settings">
            <div class="design-settings-layout">
                <div class="design-settings-sidebar">
                    <div class="accordion">
                        <div v-for="section in sections" :key="section.id" class="accordion-item">
                            <button 
                                class="accordion-header"
                                :class="{ active: currentSection === section.id }"
                                @click="navigateToSection(section.id)"
                            >
                                {{ section.label }}
                                <span class="accordion-icon">
                                    {{ currentSection === section.id ? '−' : '+' }}
                                </span>
                            </button>
                            <div 
                                class="accordion-content"
                                :class="{ active: currentSection === section.id }"
                                v-show="currentSection === section.id"
                            >
                                <component 
                                    :is="section.component"
                                    :ref="section.id + 'Panel'"
                                ></component>
                            </div>
                        </div>
                    </div>
                    <button class="save-btn" @click="saveChanges" title="Guardar cambios">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Guardar cambios
                    </button>
                </div>

                <!-- Área de previsualización -->
                <div class="design-settings-preview">
                    <div class="demo-selector">
                        <select v-model="selectedDemo" @change="changeDemo($event.target.value)">
                            <option v-for="demo in demos" :key="demo.id" :value="demo.id">
                                {{ demo.label }}
                            </option>
                        </select>
                    </div>
                    <div class="demo-frame-container">
                        <iframe 
                            ref="demoFrame"
                            class="demo-frame"
                            frameborder="0"
                            title="Preview"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    `
};