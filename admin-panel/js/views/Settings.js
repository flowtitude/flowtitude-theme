window.Settings = {
	data() {
		return {
			settings: {
				revision_limit: 3,
				move_bricks_menu: false,
				remove_gutenberg_css: false,
				remove_bricks_css: false,
				remove_bricks_js: false,
				bricks_layer: false,
				wp_layer: false,
				intersection_observer: false,
				enable_dark_mode: false,
			},
			message: '',
			isSaving: false,
			saveTimeout: null
		};
	},
	created() {
		fetch('/wp-json/flowtitude/v1/settings', {
			headers: { 'X-WP-Nonce': flowtitude_data.rest_nonce }
		})
		.then(res => res.json())
		.then(data => {
			this.settings = { ...this.settings, ...data };
		});
	},
	methods: {
		async saveSettings() {
			this.isSaving = true;

			const res = await fetch('/wp-json/flowtitude/v1/settings', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': flowtitude_data.rest_nonce
				},
				body: JSON.stringify(this.settings)
			});

			const result = await res.json();
			if (result.success) {
				window.FlowtitudeNotify.show("Ajustes guardados correctamente.", 'success');
			}

			this.isSaving = false;
		},
		handleSettingChange() {
			// Limpiar timeout anterior si existe
			if (this.saveTimeout) {
				clearTimeout(this.saveTimeout);
			}

			// Establecer nuevo timeout para guardar
			this.saveTimeout = setTimeout(() => {
				this.saveSettings();
			}, 500); // 500ms de debounce
		}
	},
	template: `
		<div class="admin">
			<h1 class="section-title">Ajustes del sistema</h1>

			<div class="content-area">
				<details class="toggle-section snippet-group" open>
					<summary style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>General</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>

					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Mover menú de Bricks al final</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.move_bricks_menu" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
				</details>

				<!-- Panel: CSS y rendimiento -->
				<details class="toggle-section snippet-group">
					<summary style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>CSS y rendimiento</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>

					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Eliminar CSS de Gutenberg</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.remove_gutenberg_css" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desencolar CSS de Bricks</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.remove_bricks_css" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desencolar JS de Bricks</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.remove_bricks_js" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Cargar CSS de Bricks en su propia capa</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.bricks_layer" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Cargar CSS de WP en su propia capa</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.wp_layer" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
				</details>

				<!-- Panel: Integraciones -->
				<details class="toggle-section snippet-group">
					<summary style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>Integraciones</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>

					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Activar Intersect.js</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.intersection_observer" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Mostrar modo oscuro en frontend</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" v-model="settings.enable_dark_mode" @change="handleSettingChange" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
				</details>

				<p v-if="message" class="notice-popup show" :style="{ color: 'white' }">{{ message }}</p>
			</div>
		</div>
	`
};
