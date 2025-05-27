const FlowtitudeSettings = {
    template: `
        <div class="flowtitude-settings-container">
            <div class="tab-navigation">
                <button 
                    :class="['tab-button', { active: activeTab === 'design' }]"
                    @click="activeTab = 'design'"
                >
                    Diseño
                </button>
                <button 
                    :class="['tab-button', { active: activeTab === 'tailwind' }]"
                    @click="activeTab = 'tailwind'"
                >
                    Tailwind
                </button>
            </div>
            
            <div class="tab-content">
                <div v-if="activeTab === 'design'">
                    <router-view></router-view>
                </div>
                <div v-else-if="activeTab === 'tailwind'">
                    <div class="tailwind-settings">
                        <!-- Contenido de configuración de Tailwind -->
                    </div>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            activeTab: 'design'
        };
    },
    watch: {
        activeTab(newTab) {
            if (newTab === 'design') {
                this.$router.push('/design');
            }
        }
    },
    created() {
        // Establecer la pestaña activa basada en la ruta actual
        if (this.$route.path === '/design') {
            this.activeTab = 'design';
        }
    }
};

// Registrar el componente globalmente
window.FlowtitudeSettings = FlowtitudeSettings; 