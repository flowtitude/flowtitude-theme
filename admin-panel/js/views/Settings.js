window.Settings = {
	data() {
		return {
			settings: {
				// General
				move_bricks_menu: false,
				revision_limit: 3,
				disable_autosave: false,
				disable_transients: false,
				wp_cache: false,
				custom_dashboard_template: '',
				
				// Rendimiento
				wp_memory_limit: '40M',
				wp_max_memory_limit: '256M',
				optimize_memory: false,
				disable_heartbeat: false,
				
				// Integración Tailwind
				enable_dark_mode: false,
				intersection_observer: false,
				bricks_layer: false,
				wp_layer: false,
				remove_gutenberg_css: false,
				remove_bricks_css: false,
				remove_bricks_js: false,
			},
			isSaving: false,
			saveTimeout: null,
			openSection: 'General',
			templates: [],
			loading: false,
			error: null,
			bricksMessage: ''
		};
	},
	async created() {
		console.log('[Settings] Componente creado');
		await this.loadSettings();
	},
	methods: {
		async loadSettings() {
			try {
				console.log('[Settings] Cargando ajustes...');
				const response = await fetch('/wp-json/flowtitude/v1/settings', {
					headers: { 'X-WP-Nonce': flowtitude_data.rest_nonce }
				});
				if (!response.ok) {
					throw new Error('Error al cargar los ajustes');
				}
				const data = await response.json();
				console.log('[Settings] Ajustes recibidos:', data);
				this.settings = { ...this.settings, ...data };
				await this.loadBricksTemplates();
			} catch (error) {
				console.error('[Settings] Error al cargar ajustes:', error);
				this.error = error.message;
			}
		},
		async loadBricksTemplates() {
			try {
				const response = await fetch('/wp-json/flowtitude/v1/bricks/templates', {
					headers: { 
						'X-WP-Nonce': flowtitude_data.rest_nonce,
						'Accept': 'application/json'
					}
				});
				if (!response.ok) {
					const errorData = await response.json();
					throw new Error(errorData.message || 'Error al cargar las plantillas');
				}
				const data = await response.json();
				this.templates = data.templates || [];
				this.bricksMessage = data.message || '';
			} catch (error) {
				console.error('Settings: Error al cargar plantillas:', error);
				this.error = error.message;
				this.templates = [];
				this.bricksMessage = 'Error al cargar las plantillas de Bricks';
			}
		},
		async handleSettingChange() {
			this.isSaving = true;
			clearTimeout(this.saveTimeout);
			try {
				console.log('[Settings] Guardando ajustes:', this.settings);
				const res = await fetch('/wp-json/flowtitude/v1/settings', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify(this.settings)
				});
				const result = await res.json();
				console.log('[Settings] Respuesta al guardar:', result);
				if (result.success) {
					window.FlowtitudeNotify.show('Cambios guardados correctamente', 'success');
				} else {
					window.FlowtitudeNotify.show('Error al guardar los cambios', 'error');
				}
			} catch (error) {
				window.FlowtitudeNotify.show('Error al guardar los cambios', 'error');
			}
			this.isSaving = false;
		},
		setOpenSection(section) {
			this.openSection = this.openSection === section ? null : section;
		}
	},
	template: `
		<div class="admin">
			<h1 class="section-title">Ajustes</h1>

			<div class="content-area">
				<details class="toggle-section snippet-group" :open="openSection === 'General'">
					<summary @click.prevent="setOpenSection('General')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>General</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Mover menú de Bricks</div>
							<div class="snippet-desc">Mueve el menú de Bricks a una ubicación más accesible en el panel de administración.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.move_bricks_menu" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Número máximo de revisiones por post</div>
							<div class="snippet-desc">Limita cuántas revisiones se guardan por cada entrada o página. (0 para desactivar revisiones)</div>
						</div>
						<input type="number" v-model="settings.revision_limit" min="0" max="20" style="width: 80px;" @blur="handleSettingChange" />
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar guardado automático</div>
							<div class="snippet-desc">Evita que WordPress guarde borradores automáticamente mientras editas entradas o páginas.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_autosave" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar generación de transients</div>
							<div class="snippet-desc">Evita que WordPress y los plugins guarden nuevos transients en la base de datos.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_transients" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Activar caché de objetos</div>
							<div class="snippet-desc">Activa o desactiva la caché de objetos de WordPress.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.wp_cache" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Plantilla personalizada del dashboard</div>
							<div class="snippet-desc">
								Selecciona una plantilla de Bricks para usar como dashboard personalizado. Si no seleccionas ninguna, se mostrará el dashboard por defecto.<br>
								<strong>¿Qué hace esta opción?</strong> Si eliges una plantilla, el contenido de esa plantilla Bricks (tipo "section") reemplazará el dashboard estándar de WordPress para todos los administradores.
							</div>
							<div v-if="bricksMessage" class="snippet-desc" style="color: #666; margin-top: 8px;">{{ bricksMessage }}</div>
						</div>
						<select v-model="settings.custom_dashboard_template" @change="handleSettingChange" :disabled="templates.length === 0">
							<option value="">Seleccionar plantilla...</option>
							<option v-for="template in templates" :key="template.id" :value="template.id">
								{{ template.title }}
							</option>
						</select>
					</div>
				</details>

				<details class="toggle-section snippet-group" :open="openSection === 'Rendimiento'">
					<summary @click.prevent="setOpenSection('Rendimiento')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>Rendimiento</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Límite de memoria de WordPress</div>
							<div class="snippet-desc">Define el límite de memoria para WordPress. Ejemplo: 40M, 128M, 256M, 512M, 1G</div>
						</div>
						<input type="text" v-model="settings.wp_memory_limit" @blur="handleSettingChange" placeholder="40M" style="width: 100px;" />
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Límite máximo de memoria</div>
							<div class="snippet-desc">Define el límite máximo de memoria para operaciones administrativas. Debe ser mayor que el límite normal.</div>
						</div>
						<input type="text" v-model="settings.wp_max_memory_limit" @blur="handleSettingChange" placeholder="256M" style="width: 100px;" />
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Optimización de memoria</div>
							<div class="snippet-desc">Activa optimizaciones automáticas de memoria y limpieza de recursos no utilizados.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.optimize_memory" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar Heartbeat API</div>
							<div class="snippet-desc">Reduce las peticiones AJAX internas de WordPress, útil para ahorrar recursos.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_heartbeat" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
				</details>

				<details class="toggle-section snippet-group" :open="openSection === 'Tailwind'">
					<summary @click.prevent="setOpenSection('Tailwind')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>Integración Tailwind</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Activar modo oscuro</div>
							<div class="snippet-desc">Habilita el modo oscuro en el frontend usando las clases de Tailwind.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.enable_dark_mode" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Intersection Observer</div>
							<div class="snippet-desc">Optimiza la carga de estilos usando Intersection Observer para elementos visibles.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.intersection_observer" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Encolar CSS de WordPress en capa</div>
							<div class="snippet-desc">Carga los estilos de WordPress y plugins (incluyendo Gutenberg) en una capa CSS separada para mejor rendimiento.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.wp_layer" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Encolar CSS de Bricks en capa</div>
							<div class="snippet-desc">Carga los estilos de Bricks en una capa CSS separada para mejor rendimiento.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.bricks_layer" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desencolar CSS de Gutenberg</div>
							<div class="snippet-desc">Elimina los estilos por defecto de Gutenberg para usar solo Tailwind.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.remove_gutenberg_css" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desencolar CSS de Bricks</div>
							<div class="snippet-desc">Elimina los estilos por defecto de Bricks para usar solo Tailwind.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.remove_bricks_css" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desencolar JS de Bricks</div>
							<div class="snippet-desc">Elimina los scripts por defecto de Bricks para optimizar la carga.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.remove_bricks_js" @change="handleSettingChange" />
							<span class="slider"></span>
						</label>
					</div>
				</details>
			</div>
		</div>
	`
};
