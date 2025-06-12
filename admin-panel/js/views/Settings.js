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
			saveTimeout: null,
			openSection: 'General',
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
		async handleSettingChange() {
			this.isSaving = true;
			clearTimeout(this.saveTimeout);
			try {
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
					this.message = 'Cambios guardados correctamente';
					// Si se ha cambiado la opción de mover el menú de Bricks, forzar recarga del menú lateral
					if (typeof window.wp !== 'undefined' && window.wp && window.wp.admin && window.wp.admin.menu) {
						if (typeof window.wp.admin.menu.refresh === 'function') {
							window.wp.admin.menu.refresh();
						}
					} else {
						// Fallback: recargar solo el menú lateral si existe
						const adminMenu = document.getElementById('adminmenu');
						if (adminMenu) {
							adminMenu.innerHTML = '';
							location.reload(); // Si no hay forma de recargar solo el menú, recarga la página
						}
					}
				} else {
					this.message = 'Error al guardar los cambios';
				}
			} catch (error) {
				this.message = 'Error al guardar los cambios';
			}
			this.isSaving = false;
			this.saveTimeout = setTimeout(() => {
				this.message = '';
			}, 2000);
		},
		setOpenSection(section) {
			this.openSection = this.openSection === section ? null : section;
		}
	},
	template: `
		<div class="admin">
			<h1 class="section-title">Ajustes del sistema</h1>

			<div class="content-area">
				<details class="toggle-section snippet-group" :open="openSection === 'General'">
					<summary @click.prevent="setOpenSection('General')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
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

				<details class="toggle-section snippet-group" :open="openSection === 'CSS'">
					<summary @click.prevent="setOpenSection('CSS')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
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

				<details class="toggle-section snippet-group" :open="openSection === 'Integraciones'">
					<summary @click.prevent="setOpenSection('Integraciones')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
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
