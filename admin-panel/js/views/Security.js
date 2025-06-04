window.Security = {
	data() {
		return {
			settings: {
				disable_wp_api: false,
				hide_wp_version: false,
				disable_xmlrpc: false,
				secure_login: false,
			}
		};
	},
	created() {
		fetch('/wp-json/flowtitude/v1/security', {
			headers: { 'X-WP-Nonce': flowtitude_data.rest_nonce }
		})
		.then(res => res.json())
		.then(data => {
			this.settings = { ...this.settings, ...data };
		});
	},
	methods: {
		async handleToggle() {
			try {
				const response = await fetch('/wp-json/flowtitude/v1/security', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify(this.settings)
				});

				if (!response.ok) {
					throw new Error('Error al guardar los cambios');
				}

				const result = await response.json();
				if (result.success) {
					this.showNotice('Cambios guardados correctamente', true);
				}
			} catch (error) {
				console.error('Error al guardar:', error);
				this.showNotice(error.message, false);
				// Revertir el cambio si hay error
				this.settings.disable_wp_api = !this.settings.disable_wp_api;
			}
		},
		showNotice(msg, isSuccess = true) {
			window.FlowtitudeNotify.show(msg, isSuccess ? 'success' : 'error');
		}
	},
	template: `
		<div class="admin">
			<h1 class="section-title">Seguridad</h1>

			<div class="content-area">
				<div class="snippet-item">
					<div class="snippet-info">
						<div class="snippet-title">Desactivar REST API para visitantes no logeados</div>
						<div class="snippet-desc">Bloquea el acceso a la API REST de WordPress para usuarios que no han iniciado sesión, mejorando la seguridad de tu sitio.</div>
					</div>
					<div style="display: flex; gap: 12px; align-items: center;">
						<label class="switch">
							<input type="checkbox" 
								v-model="settings.disable_wp_api"
								@change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
				</div>

				<div class="snippet-item">
					<div class="snippet-info">
						<div class="snippet-title">Ocultar versión de WordPress</div>
						<div class="snippet-desc">Elimina la versión de WordPress de los metadatos y el código fuente, dificultando la identificación de vulnerabilidades específicas.</div>
					</div>
					<div style="display: flex; gap: 12px; align-items: center;">
						<label class="switch">
							<input type="checkbox" 
								v-model="settings.hide_wp_version"
								@change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
				</div>


				<div class="snippet-item">
					<div class="snippet-info">
						<div class="snippet-title">Desactivar XML-RPC</div>
						<div class="snippet-desc">Desactiva el protocolo XML-RPC, que puede ser utilizado para ataques de fuerza bruta y otras vulnerabilidades.</div>
					</div>
					<div style="display: flex; gap: 12px; align-items: center;">
						<label class="switch">
							<input type="checkbox" 
								v-model="settings.disable_xmlrpc"
								@change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
				</div>


				<div class="snippet-item">
					<div class="snippet-info">
						<div class="snippet-title">Mejorar seguridad del login</div>
						<div class="snippet-desc">Implementa límites de intentos de inicio de sesión y mensajes de error genéricos para prevenir ataques de fuerza bruta.</div>
					</div>
					<div style="display: flex; gap: 12px; align-items: center;">
						<label class="switch">
							<input type="checkbox" 
								v-model="settings.secure_login"
								@change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
				</div>
			</div>
		</div>
	`
};
