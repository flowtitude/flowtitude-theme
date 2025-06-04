window.Bricks = {
	data() {
		return {
			loading: true,
			error: null,
			groups: {},
			showUploadModal: false,
			uploadGroup: 'custom-elements',
			uploading: false,
			active: []
		}
	},
	computed: {
		hasComponents() {
			return Object.values(this.groups).some(group => group.length > 0)
		}
	},
	methods: {
		async loadComponents() {
			try {
				this.loading = true;
				this.error = null;
				
				const response = await fetch('/wp-json/flowtitude/v1/bricks', {
					headers: {
						'X-WP-Nonce': flowtitude_data.rest_nonce
					}
				});
				
				if (!response.ok) {
					throw new Error('Error al cargar los componentes');
				}
				
				const data = await response.json();
				this.groups = data.components || {};
				this.active = data.active || [];
			} catch (error) {
				console.error('Error al cargar componentes:', error);
				this.error = error.message;
			} finally {
				this.loading = false;
			}
		},
		async handleComponentToggle(file, active) {
			try {
				const newActive = active 
					? [...this.active, file]
					: this.active.filter(f => f !== file);
				
				const response = await fetch('/wp-json/flowtitude/v1/bricks', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify(newActive)
				});
				
				if (!response.ok) {
					throw new Error('Error al guardar los cambios');
				}
				
				this.active = newActive;
				this.showNotice('Cambios guardados correctamente', 'success');
			} catch (error) {
				console.error('Error al guardar cambios:', error);
				this.showNotice(error.message, 'error');
			}
		},
		async handleComponentDelete(file) {
			if (!confirm('¿Estás seguro de que quieres eliminar este componente?')) return;
			
			try {
				const response = await fetch('/wp-json/flowtitude/v1/bricks/delete', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify({ file })
				});
				
				if (!response.ok) {
					throw new Error('Error al eliminar el componente');
				}
				
				await this.loadComponents();
				this.showNotice('Componente eliminado correctamente', 'success');
			} catch (error) {
				console.error('Error al eliminar componente:', error);
				this.showNotice(error.message, 'error');
			}
		},
		showNotice(msg, type = 'info') {
			window.FlowtitudeNotify.show(msg, type);
		}
	},
	created() {
		this.loadComponents();
	},
	template: `
		<div class="admin">
			<h1 class="section-title">Componentes Bricks</h1>

			<div v-if="!hasComponents" class="snippet-empty">
				<p>No hay componentes disponibles.</p>
				<router-link to="/upload?tab=bricks" class="btn">
					Subir Nuevo Componente
				</router-link>
			</div>

			<div v-else class="content-area">

				<details v-for="(items, group) in groups" :key="group" class="toggle-section snippet-group" open>
					<summary style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
					{{ group.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') }}
						<svg class="arrow-icon" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
						</svg>
						
					</summary>

					<div v-for="item in items" :key="item.file" class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">{{ item.title }}</div>
							<div class="snippet-desc">{{ item.description || 'Sin descripción' }}</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input type="checkbox" 
									:checked="active.includes(item.file)"
									@change="handleComponentToggle(item.file, $event.target.checked)" />
								<span class="slider"></span>
							</label>
							<button class="delete" @click="handleComponentDelete(item.file)">Eliminar</button>
						</div>
					</div>
				</details>

			</div>

			<div v-if="showUploadModal" class="flowtitude-modal">
				<div class="flowtitude-modal-content">
					<h3>Subir Nuevo Componente</h3>
					<form @submit.prevent="handleComponentUpload">
						<div class="flowtitude-form-group">
							<label>Grupo:</label>
							<select v-model="uploadGroup" required>
								<option value="custom-elements">Elementos Personalizados</option>
								<option value="conditionals">Condicionales</option>
								<option value="dynamic-tags">Etiquetas Dinámicas</option>
							</select>
						</div>
						<div class="flowtitude-form-group">
							<label>Archivo PHP:</label>
							<input type="file" accept=".php" required>
						</div>
						<div class="flowtitude-form-actions">
							<button type="submit" class="flowtitude-button" :disabled="uploading">
								{{ uploading ? 'Subiendo...' : 'Subir' }}
							</button>
							<button type="button" class="flowtitude-button" @click="showUploadModal = false">
								Cancelar
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	`
};