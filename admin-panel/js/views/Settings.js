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
				this.message = "✅ Ajustes guardados correctamente.";
				setTimeout(() => this.message = '', 3000);
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
						<svg class="arrow-icon" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
						</svg>
					</summary>

					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Número máximo de revisiones</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<input type="number" v-model="settings.revision_limit" min="0" max="20" style="width: 80px;" @blur="handleSettingChange" />
						</div>
					</div>

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
						<svg class="arrow-icon" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
						</svg>
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
						<svg class="arrow-icon" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
						</svg>
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

				<p v-if="message" class="notice-popup show">{{ message }}</p>
			</div>
		</div>
	`
};
