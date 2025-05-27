window.Home = {
	data() {
		return {
			activeSnippets: 0,
			activeBricksCounts: {
				'custom-elements': 0,
				'dynamic-tags': 0,
				'conditionals': 0
			},
			windPressActive: false,
			tailwindEnabled: false,
			tailwindStatus: {
				has_main_css: false,
				has_theme_css: false,
				has_import: false
			},
			darkMode: false,
			intersect: false,
			bricksLayer: false,
			wpLayer: false,
			version: 'v2.0.0',
			themeUrl: flowtitude_data.theme_url
		};
	},
	computed: {
		canEnableTailwind() {
			return this.windPressActive && 
				   this.tailwindStatus.has_main_css && 
				   this.tailwindStatus.has_theme_css && 
				   this.tailwindStatus.has_import;
		},
		tailwindStatusMessage() {
			if (!this.windPressActive) return 'WindPress no está activado';
			if (!this.tailwindStatus.has_main_css) return 'No se encuentra main.css (Tailwind 4)';
			if (!this.tailwindStatus.has_theme_css) return 'Sistema de diseño no encontrado';
			if (!this.tailwindStatus.has_import) return 'Sistema de diseño no importado';
			return 'Todo correcto';
		}
	},
	async created() {
		try {
			const headers = { 'X-WP-Nonce': flowtitude_data.rest_nonce };
			
			// Cargar settings generales
			const settingsRes = await fetch('/wp-json/flowtitude/v1/settings', { headers });
			const settings = await settingsRes.json();
			
			// Cargar componentes Bricks
			const bricksRes = await fetch('/wp-json/flowtitude/v1/bricks', { headers });
			const bricksData = await bricksRes.json();
			
			// Cargar snippets
			const snippetsRes = await fetch('/wp-json/flowtitude/v1/snippets', { headers });
			const snippetsData = await snippetsRes.json();
			
			// Actualizar estado
			this.windPressActive = !!settings.windpress_active;
			this.tailwindStatus = settings.tailwind_status || {
				has_main_css: false,
				has_theme_css: false,
				has_import: false
			};
			this.darkMode = !!settings.dark_mode_enabled;
			this.intersect = !!settings.intersect_enabled;
			this.bricksLayer = !!settings.bricks_layer;
			this.wpLayer = !!settings.wp_layer;
			this.tailwindEnabled = settings.tailwind_integration == 1;
			
			// Contar snippets activos
			this.activeSnippets = Object.values(snippetsData).flat().filter(s => s.active).length;
			
			// Contar componentes Bricks activos
			this.activeBricksCounts = {
				'custom-elements': 0,
				'dynamic-tags': 0,
				'conditionals': 0
			};
			
			if (bricksData.components) {
				for (const type in bricksData.components) {
					this.activeBricksCounts[type] = bricksData.components[type].filter(f => f.active).length;
				}
			}
		} catch (error) {
			console.error('Error al cargar datos del dashboard:', error);
		}
	},

	methods: {
		async updateTailwindIntegration() {
			const headers = {
				'Content-Type': 'application/json',
				'X-WP-Nonce': flowtitude_data.rest_nonce
			};

			try {
				const response = await fetch('/wp-json/flowtitude/v1/settings', {
					method: 'POST',
					headers,
					body: JSON.stringify({
						'tailwind_integration': this.tailwindEnabled ? 1 : 0
					})
				});

				if (!response.ok) {
					throw new Error('Error al guardar la configuración');
				}

				// Emitimos el evento global para notificar al componente raíz
				window.dispatchEvent(new CustomEvent('tailwind-setting-updated', {
					detail: { enabled: this.tailwindEnabled }
				}));

			} catch (error) {
				console.error('Error al actualizar la integración de Tailwind:', error);
				this.tailwindEnabled = !this.tailwindEnabled; // Revertimos el cambio si hay error
			}
		}
	},

	template: `
		<div class="admin">
			<div class="dashboard-logo-wrap">
				<img :src="themeUrl + '/admin-panel/assets/flowtitude.svg'" alt="Flowtitude" class="dashboard-logo" />
				<p class="dashboard-version">Versión {{ version }}</p>
			</div>

			<div class="dashboard-boxes">
				<!-- Snippets activos -->
				<div class="info-box">
					<h3>Snippets activos</h3>
					<p class="info-value large">{{ activeSnippets }}</p>
					<p class="info-value">
						<span>Widgets personalizados</span>
						<span>{{ activeBricksCounts['custom-elements'] }}</span>
					</p>
					<hr>
					<p class="info-value">
						<span>Etiquetas dinámicas</span>
						<span>{{ activeBricksCounts['dynamic-tags'] }}</span>
					</p>
					<hr>
					<p class="info-value">
						<span>Condiciones</span>
						<span>{{ activeBricksCounts['conditionals'] }}</span>
					</p>
				</div>

				<!-- WindPress -->
				<div class="info-box">
					<h3>WindPress</h3>
					<p class="info-value">
						<span>Estado</span>
						<span :class="{ 'badge badge-success': windPressActive, 'badge badge-danger': !windPressActive }">
							{{ windPressActive ? 'Activado' : 'Sin activar' }}
						</span>
					</p>
					<template v-if="windPressActive">
						<hr>
						<p class="info-value">
							<span>Tailwind 4</span>
							<span :class="{ 'badge badge-success': tailwindStatus.has_main_css, 'badge badge-danger': !tailwindStatus.has_main_css }">
								{{ tailwindStatus.has_main_css ? 'Detectado' : 'No encontrado' }}
							</span>
						</p>
						<hr>
						<p class="info-value">
							<span>Sistema de diseño</span>
							<span :class="{ 'badge badge-success': tailwindStatus.has_theme_css && tailwindStatus.has_import, 'badge badge-danger': !tailwindStatus.has_theme_css || !tailwindStatus.has_import }">
								{{ (tailwindStatus.has_theme_css && tailwindStatus.has_import) ? 'Activo' : 'No activo' }}
							</span>
						</p>
						<hr v-if="canEnableTailwind">
						<div v-if="canEnableTailwind" class="info-value toggle-wrap">
							<span>Integrar Tailwind</span>
							<label class="switch">
								<input type="checkbox" v-model="tailwindEnabled" @change="updateTailwindIntegration" />
								<span class="slider"></span>
							</label>
						</div>
					</template>
				</div>

				<!-- Integraciones -->
				<div class="info-box">
					<h3>Integraciones</h3>
					<p class="info-value">
						<span>Modo oscuro</span>
						<span :class="darkMode ? 'badge badge-success' : 'badge badge-danger'">
							{{ darkMode ? 'SI' : 'NO' }}
						</span>
					</p>
					<hr>
					<p class="info-value">
						<span>Intersect.js</span>
						<span :class="intersect ? 'badge badge-success' : 'badge badge-danger'">
							{{ intersect ? 'SI' : 'NO' }}
						</span>
					</p>
				</div>

				<!-- Capas CSS -->
				<div class="info-box">
					<h3>Capas CSS</h3>
					<p class="info-value">
						<span>Bricks</span>
						<span :class="bricksLayer ? 'badge badge-success' : 'badge badge-danger'">
							{{ bricksLayer ? 'SI' : 'NO' }}
						</span>
					</p>
					<hr>
					<p class="info-value">
						<span>WordPress</span>
						<span :class="wpLayer ? 'badge badge-success' : 'badge badge-danger'">
							{{ wpLayer ? 'SI' : 'NO' }}
						</span>
					</p>
				</div>
			</div>

			<div class="dashboard-docs-link">
				<a href="https://webyblog.es/docs/flowtitude" target="_blank">
					📖 Ver documentación oficial
				</a>
			</div>
		</div>
	`
};
