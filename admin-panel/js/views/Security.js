window.Security = {
	data() {
		return {
			settings: {
				disable_wp_api: false,
				hide_wp_version: false,
				disable_xmlrpc: false,
				secure_login: false,
				// Opciones de debug
				wp_debug: false,
				wp_debug_display: false,
				wp_debug_log: false,
				wp_debug_log_path: '',
				script_debug: false,
				savequeries: false,
				disable_wp_cron: false,
				wp_cache: false,
				disable_transients: false,
				disable_heartbeat: false,
				disable_autosave: false,
				revision_limit: 0,
				log_hooks: false,
				allowed_ips: '',
				disable_2fa: false,
				disable_upload_restrictions: false,
				migration_mode: false,
				plugins_to_deactivate: '',
			},
			migrationOldUrl: '',
			migrationNewUrl: '',
			showMigrationConfirm: false,
			openSection: 'General', // Sección abierta por defecto
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
					this.showNotice('Cambios guardados correctamente', 'success');
				}
			} catch (error) {
				console.error('Error al guardar:', error);
				this.showNotice(error.message, 'error');
				// Revertir el cambio si hay error
				this.settings.disable_wp_api = !this.settings.disable_wp_api;
			}
		},
		showNotice(msg, type = 'info') {
			window.FlowtitudeNotify.show(msg, type);
		},
		async clearTransients() {
			try {
				const response = await fetch('/wp-json/flowtitude/v1/security/clear-transients', {
					headers: { 'X-WP-Nonce': flowtitude_data.rest_nonce }
				});

				if (!response.ok) {
					throw new Error('Error al limpiar transients');
				}

				const result = await response.json();
				if (result.success) {
					this.showNotice('Transients limpiados correctamente', 'success');
				}
			} catch (error) {
				console.error('Error al limpiar transients:', error);
				this.showNotice(error.message, 'error');
			}
		},
		confirmReplaceUrls() {
			this.showMigrationConfirm = true;
		},
		async replaceUrlsInDb() {
			this.showMigrationConfirm = false;
			try {
				const response = await fetch('/wp-json/flowtitude/v1/security/replace-urls', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify({
						old_url: this.migrationOldUrl,
						new_url: this.migrationNewUrl
					})
				});
				const result = await response.json();
				if (result.success) {
					this.showNotice('URLs reemplazadas correctamente', 'success');
				} else {
					this.showNotice(result.message || 'Error al reemplazar URLs', 'error');
				}
			} catch (error) {
				console.error('Error al reemplazar URLs:', error);
				this.showNotice(error.message, 'error');
			}
		},
		setOpenSection(section) {
			this.openSection = this.openSection === section ? null : section;
		}
	},
	template: `
		<div class="admin">
			<h1 class="section-title">Seguridad</h1>

			<div class="content-area">
				<details class="toggle-section snippet-group" :open="openSection === 'General'">
					<summary @click.prevent="setOpenSection('General')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>General</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar REST API para visitantes no logeados</div>
							<div class="snippet-desc">Bloquea el acceso a la API REST de WordPress para usuarios que no han iniciado sesión, mejorando la seguridad de tu sitio.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_wp_api" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Ocultar versión de WordPress</div>
							<div class="snippet-desc">Elimina la versión de WordPress de los metadatos y el código fuente, dificultando la identificación de vulnerabilidades específicas.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.hide_wp_version" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar XML-RPC</div>
							<div class="snippet-desc">Desactiva el protocolo XML-RPC, que puede ser utilizado para ataques de fuerza bruta y otras vulnerabilidades.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_xmlrpc" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Mejorar seguridad del login</div>
							<div class="snippet-desc">Implementa límites de intentos de inicio de sesión y mensajes de error genéricos para prevenir ataques de fuerza bruta.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.secure_login" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Permitir acceso solo desde estas IPs</div>
							<div class="snippet-desc">Solo los usuarios con una IP en esta lista podrán acceder al panel de administración. Separa varias IPs por coma o salto de línea.</div>
						</div>
						<input type="text" v-model="settings.allowed_ips" @blur="handleToggle" placeholder="127.0.0.1, 192.168.1.10" style="min-width:300px; width:50%; max-width:100%;" />
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar autenticación de dos factores</div>
							<div class="snippet-desc">Desactiva la autenticación de dos factores para todos los usuarios. Útil para pruebas en desarrollo.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_2fa" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar restricciones de subida de archivos</div>
							<div class="snippet-desc">Permite subir cualquier tipo de archivo en el entorno de desarrollo, sin restricciones de WordPress.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_upload_restrictions" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
				</details>

				<details class="toggle-section snippet-group" :open="openSection === 'Debug'">
					<summary @click.prevent="setOpenSection('Debug')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>Debug</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Activar modo debug</div>
							<div class="snippet-desc">Activa el modo de depuración global de WordPress. (WP_DEBUG)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.wp_debug" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Mostrar errores en pantalla</div>
							<div class="snippet-desc">Muestra los errores y avisos directamente en el navegador. (WP_DEBUG_DISPLAY)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.wp_debug_display" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Escribir errores en log</div>
							<div class="snippet-desc">Guarda los errores en un archivo de log. (WP_DEBUG_LOG)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.wp_debug_log" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item" v-if="settings.wp_debug_log">
						<div class="snippet-info">
							<div class="snippet-title">Ruta del archivo de log</div>
							<div class="snippet-desc">Define la ubicación del archivo de log (por defecto: wp-content/debug.log).</div>
						</div>
						<input type="text" v-model="settings.wp_debug_log_path" @blur="handleToggle" placeholder="/ruta/absoluta/debug.log" style="min-width:260px;" />
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Forzar scripts no minificados</div>
							<div class="snippet-desc">Carga los archivos JS y CSS sin minificar para facilitar la depuración. (SCRIPT_DEBUG)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.script_debug" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Guardar queries SQL</div>
							<div class="snippet-desc">Guarda todas las consultas SQL en $wpdb->queries (puede afectar al rendimiento). (SAVEQUERIES)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.savequeries" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar cron interno</div>
							<div class="snippet-desc">Desactiva el cron interno de WordPress (útil si usas un cron externo). (DISABLE_WP_CRON)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_wp_cron" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Registrar hooks y acciones</div>
							<div class="snippet-desc">Registra en un log todos los hooks y acciones ejecutados durante la carga de WordPress. Útil para desarrolladores avanzados.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.log_hooks" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
				</details>

				<details class="toggle-section snippet-group" :open="openSection === 'Caché'">
					<summary @click.prevent="setOpenSection('Caché')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>Caché y rendimiento</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Activar caché de objetos</div>
							<div class="snippet-desc">Activa o desactiva la caché de objetos de WordPress. (WP_CACHE)</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.wp_cache" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info" style="flex: 1 1 auto; min-width: 0;">
							<div class="snippet-title">Desactivar generación de transients</div>
							<div class="snippet-desc">Evita que WordPress y los plugins guarden nuevos transients en la base de datos. Puede afectar a algunas funcionalidades. Puedes limpiar los transients existentes manualmente.</div>
						</div>
						<div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; min-width: 220px;">
							<button class="btn btn-small" @click="clearTransients">Limpiar transients</button>
							<label class="switch">
								<input type="checkbox" v-model="settings.disable_transients" @change="handleToggle" />
								<span class="slider"></span>
							</label>
						</div>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar Heartbeat API</div>
							<div class="snippet-desc">Reduce las peticiones AJAX internas de WordPress, útil para ahorrar recursos en desarrollo.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_heartbeat" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar guardado automático</div>
							<div class="snippet-desc">Evita que WordPress guarde borradores automáticamente mientras editas entradas o páginas.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.disable_autosave" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Número máximo de revisiones por post</div>
							<div class="snippet-desc">Limita cuántas revisiones se guardan por cada entrada o página. (0 para desactivar revisiones)</div>
						</div>
						<input type="number" v-model="settings.revision_limit" min="0" max="20" style="width: 80px;" @blur="handleToggle" />
					</div>
				</details>

				<details class="toggle-section snippet-group" :open="openSection === 'Migraciones'">
					<summary @click.prevent="setOpenSection('Migraciones')" style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>Migraciones</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Activar modo de migración</div>
							<div class="snippet-desc">Activa el modo migración para mostrar avisos y habilitar herramientas especiales de migración.</div>
						</div>
						<label class="switch">
							<input type="checkbox" v-model="settings.migration_mode" @change="handleToggle" />
							<span class="slider"></span>
						</label>
					</div>
					<div class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Desactivar plugins de producción</div>
							<div class="snippet-desc">Introduce los slugs de los plugins a desactivar en entornos de desarrollo, uno por línea.</div>
						</div>
						<textarea v-model="settings.plugins_to_deactivate" @blur="handleToggle" placeholder="wordfence\nwp-rocket\nmailgun" style="min-width:260px; min-height:40px;"></textarea>
					</div>
					<div v-if="settings.migration_mode" class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">Reemplazar URLs en la base de datos</div>
							<div class="snippet-desc">Introduce la URL antigua y la nueva. Esta acción es irreversible, haz un backup antes de continuar.</div>
						</div>
						<div>
							<input type="text" v-model="migrationOldUrl" placeholder="URL antigua (ej: https://produccion.com)" style="min-width: 220px; width: 32%; max-width: 100%; margin-right: 8px;" />
							<input type="text" v-model="migrationNewUrl" placeholder="URL nueva (ej: https://staging.com)" style="min-width: 220px; width: 32%; max-width: 100%; margin-right: 8px;" />
							<button class="btn btn-inline" @click="confirmReplaceUrls">Reemplazar URLs</button>
						</div>
					</div>
					<div v-if="showMigrationConfirm" class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">¿Estás seguro?</div>
							<div class="snippet-desc">Esta acción reemplazará todas las apariciones de la URL antigua por la nueva en la base de datos. Haz un backup antes de continuar.</div>
						</div>
						<button class="btn btn-panel" @click="replaceUrlsInDb">Sí, reemplazar ahora</button>
						<button class="btn btn-panel" @click="showMigrationConfirm = false">Cancelar</button>
					</div>
				</details>
			</div>
		</div>
	`
};
