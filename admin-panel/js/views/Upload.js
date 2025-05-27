window.Upload = {
    data() {
        return {
            activeTab: 'snippets',
            componentsLoaded: false
        };
    },
    computed: {
        tabTitle() {
            return this.activeTab === 'snippets' ? 'Subir Snippets' : 'Subir Componentes';
        },
        UploadSnippetComponent() {
            return window.UploadSnippet || null;
        },
        UploadBricksComponent() {
            return window.UploadBricks || null;
        }
    },
    methods: {
        changeTab(tab) {
            this.activeTab = tab;
            // Actualizar la URL sin recargar la página
            const url = new URL(window.location.href);
            if (tab === 'snippets') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', tab);
            }
            window.history.replaceState({}, '', url);
        },
        checkComponents() {
            if (!this.componentsLoaded) {
                if (window.UploadSnippet && window.UploadBricks) {
                    this.componentsLoaded = true;
                } else {
                    setTimeout(() => this.checkComponents(), 100);
                }
            }
        }
    },
    created() {
        // Verificar si hay un parámetro tab en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam === 'bricks') {
            this.activeTab = 'bricks';
        }
        this.checkComponents();
    },
    template: `
        <div class="admin">
            <div class="section-header">
                <h1 class="section-title">{{ tabTitle }}</h1>
            </div>

            <div v-if="!componentsLoaded" class="loading-message">
                <p>Cargando componentes...</p>
            </div>

            <div v-else class="upload-tabs">
                <div class="tab-buttons">
                    <button 
                        :class="['tab-button', { active: activeTab === 'snippets' }]"
                        @click="changeTab('snippets')"
                    >
                        Snippets
                    </button>
                    <button 
                        :class="['tab-button', { active: activeTab === 'bricks' }]"
                        @click="changeTab('bricks')"
                    >
                        Componentes Bricks
                    </button>
                </div>

                <div class="tab-content">
                    <div v-show="activeTab === 'snippets'">
                        <component :is="UploadSnippetComponent" v-if="UploadSnippetComponent"></component>
                    </div>
                    <div v-show="activeTab === 'bricks'">
                        <component :is="UploadBricksComponent" v-if="UploadBricksComponent"></component>
                    </div>
                </div>
            </div>
        </div>
    `
}; 