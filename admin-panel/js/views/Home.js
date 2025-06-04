window.Home = {
	data() {
		return {
			activeSnippets: 0,
			activeBricksCounts: {
				'custom-elements': 0,
				'dynamic-tags': 0,
				'conditionals': 0
			},
			settings: {
				bricks_layer: false,
				wp_layer: false
			},
			security: {
				disable_wp_api: false
			},
			version: 'v2.0.0',
			themeUrl: flowtitude_data.theme_url
		};
	},
	async created() {
		try {
			const headers = { 'X-WP-Nonce': flowtitude_data.rest_nonce };
			
			// Cargar ajustes generales
			const settingsRes = await fetch('/wp-json/flowtitude/v1/settings', { headers });
			const settings = await settingsRes.json();
			this.settings = { ...this.settings, ...settings };
			
			// Cargar componentes Bricks
			const bricksRes = await fetch('/wp-json/flowtitude/v1/bricks', { headers });
			const bricksData = await bricksRes.json();
			
			// Cargar snippets
			const snippetsRes = await fetch('/wp-json/flowtitude/v1/snippets', { headers });
			const snippetsData = await snippetsRes.json();
			
			// Cargar ajustes de seguridad
			const securityRes = await fetch('/wp-json/flowtitude/v1/security', { headers });
			this.security = await securityRes.json();
			
			// Actualizar estado de snippets y componentes Bricks
			this.activeSnippets = Object.values(snippetsData).flat().filter(s => s.active).length;
			
			if (bricksData.components) {
				for (const type in this.activeBricksCounts) {
					this.activeBricksCounts[type] = (bricksData.components[type] || []).filter(f => f.active).length;
				}
			}
		} catch (error) {
			console.error('Error al cargar datos del dashboard:', error);
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

				<!-- Seguridad -->
				<div class="info-box">
					<h3>Seguridad</h3>
					<p class="info-value">
						<span>API REST (visitantes)</span>
						<span :class="security.disable_wp_api ? 'badge badge-success' : 'badge badge-danger'">
							{{ security.disable_wp_api ? 'SI' : 'NO' }}
						</span>
					</p>
					<hr>
					<p class="info-value">
						<span>Ocultar versión WP</span>
						<span :class="security.hide_wp_version ? 'badge badge-success' : 'badge badge-danger'">
							{{ security.hide_wp_version ? 'SI' : 'NO' }}
						</span>
					</p>
					<hr>
					<p class="info-value">
						<span>Desactivar XML-RPC</span>
						<span :class="security.disable_xmlrpc ? 'badge badge-success' : 'badge badge-danger'">
							{{ security.disable_xmlrpc ? 'SI' : 'NO' }}
						</span>
					</p>
					<hr>
					<p class="info-value">
						<span>Login seguro</span>
						<span :class="security.secure_login ? 'badge badge-success' : 'badge badge-danger'">
							{{ security.secure_login ? 'SI' : 'NO' }}
						</span>
					</p>
				</div>

				<!-- Capas CSS -->
				<div class="info-box">
					<h3>Capas CSS</h3>
					<p class="info-value">
						<span>Bricks</span>
						<span :class="settings.bricks_layer ? 'badge badge-success' : 'badge badge-danger'">
							{{ settings.bricks_layer ? 'SI' : 'NO' }}
						</span>
					</p>
					<hr>
					<p class="info-value">
						<span>WordPress</span>
						<span :class="settings.wp_layer ? 'badge badge-success' : 'badge badge-danger'">
							{{ settings.wp_layer ? 'SI' : 'NO' }}
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
