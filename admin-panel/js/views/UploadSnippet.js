window.UploadSnippet = {
	data() {
		return {
			folders: [],
			selectedFolder: '',
			newFolderName: '',
			snippetFile: null,
			uploading: false,
			snippets: [],
			showNewFolderInput: false,
			isLoading: false,
			error: null
		};
	},
	created() {
		this.loadSnippets();
	},
	methods: {
		async loadSnippets() {
			this.isLoading = true;
			this.error = null;
			
			// Solo la carpeta 'custom' debe estar disponible por defecto
			const defaultFolders = ['custom'];
			let snippetFiles = [];
			let foldersFromAPI = [];
			
			try {
				const response = await fetch('/wp-json/flowtitude/v1/snippet-files', {
					headers: { 
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce 
					}
				});
				
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				
				const data = await response.json();
				console.log('Respuesta de snippet-files:', data);
				
				// Si la respuesta contiene un array de archivos, usarlo
				if (data.files && Array.isArray(data.files)) {
					snippetFiles = data.files;
				} else if (Array.isArray(data)) {
					snippetFiles = data;
				}
				
				// Filtrar snippets del sistema
				this.snippets = snippetFiles.filter(s => s && s.folder !== 'utils');
			} catch (e) {
				console.error('Error cargando snippets:', e);
				this.error = `Error cargando snippets: ${e.message}`;
				this.snippets = [];
			}
		
			// Intentar cargar las carpetas
			try {
				const res = await fetch('/wp-json/flowtitude/v1/snippet-folders', {
					headers: { 
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce 
					}
				});
				
				if (!res.ok) {
					throw new Error(`HTTP error! status: ${res.status}`);
				}
				
				const data = await res.json();
				console.log('Respuesta de snippet-folders:', data);
				
				if (data.success) {
					foldersFromAPI = data.folders;
					if (data.message) {
						console.log('Mensaje del servidor:', data.message);
					}
				} else {
					throw new Error(data.message || 'Error al cargar carpetas');
				}
			} catch (e) {
				console.error('Error cargando carpetas:', e);
				this.error = `Error cargando carpetas: ${e.message}`;
				foldersFromAPI = ['custom']; // Usar carpeta por defecto en caso de error
			}
			
			// Combinar todas las fuentes de carpetas
			const folderSources = [
				...defaultFolders,
				...foldersFromAPI,
				...(this.snippets.map(s => s && s.folder).filter(Boolean) || [])
			];
			
			// Eliminar duplicados y carpetas del sistema
			this.folders = [...new Set(folderSources)].filter(f => f && f !== 'utils');
			
			// Si no hay carpetas seleccionadas, seleccionar la primera por defecto
			if (this.folders.length > 0 && !this.selectedFolder) {
				this.selectedFolder = this.folders[0];
			}
			
			this.isLoading = false;
		},

		async createFolder() {
			if (!this.newFolderName) {
				this.showNotice('Por favor ingrese un nombre de carpeta', 'error');
				return;
			}

			try {
				const response = await fetch('/wp-json/flowtitude/v1/snippet-folders', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify({
						folder_name: this.newFolderName
					})
				});

				let data;
				const contentType = response.headers.get('content-type');
				if (contentType && contentType.includes('application/json')) {
					data = await response.json();
				} else {
					throw new Error('La respuesta del servidor no es JSON válido');
				}

				if (!response.ok) {
					throw new Error(data.message || `Error ${response.status}: ${response.statusText}`);
				}

				if (data.success && data.folders) {
					this.folders = data.folders;
					this.selectedFolder = this.newFolderName;
					this.showNotice('Carpeta creada correctamente', 'success');
					this.newFolderName = '';
					this.showNewFolderInput = false;
				} else {
					throw new Error(data.message || 'Error desconocido al crear la carpeta');
				}

			} catch (error) {
				console.error('Error al crear carpeta:', error);
				this.showNotice(error.message, 'error');
			}
		},

		async uploadSnippet() {
			if (!this.snippetFile) {
				this.showNotice("Selecciona un archivo", false);
				return;
			}

			const formData = new FormData();
			formData.append('file', this.snippetFile);
			formData.append('folder', this.selectedFolder || 'custom');

			this.uploading = true;

			try {
				const res = await fetch('/wp-json/flowtitude/v1/upload-snippet', {
					method: 'POST',
					headers: { 'X-WP-Nonce': flowtitude_data.rest_nonce },
					body: formData
				});
				const result = await res.json();
				this.showNotice(result.message || 'Snippet subido correctamente', true);
				this.snippetFile = null;
				this.loadSnippets();
			} catch (e) {
				this.showNotice("Error al subir", false);
			}

			this.uploading = false;
		},

		async moveSnippet(snippet, newFolder) {
			if (!newFolder || newFolder === snippet.folder) return;

			try {
				const res = await fetch('/wp-json/flowtitude/v1/snippet-files/move', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify({ file: snippet.file, to: newFolder })
				});

				const result = await res.json();

				if (result.success) {
					snippet.folder = newFolder;
					this.showNotice('Snippet movido con éxito', true);
					this.loadSnippets();
				} else {
					this.showNotice('Error al mover snippet', false);
				}
			} catch (error) {
				this.showNotice('Error al mover snippet', false);
			}
		},

		async deleteSnippet(snippet) {
			if (!confirm("¿Seguro que deseas eliminar este snippet?")) return;

			try {
				const res = await fetch('/wp-json/flowtitude/v1/snippet-files/delete', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify({ file: snippet.file })
				});
				const result = await res.json();
				if (result.success) {
					this.showNotice("Snippet eliminado correctamente", true);
					this.loadSnippets();
				}
			} catch (error) {
				this.showNotice("Error al eliminar el snippet", false);
			}
		},

		showNotice(msg, isSuccess = true) {
			const div = document.createElement('div');
			div.className = 'notice-popup show';
			div.style.background = isSuccess ? '#F59E0B' : '#DC2626';
			div.textContent = msg;
			document.body.appendChild(div);

			setTimeout(() => {
				div.classList.remove('show');
				setTimeout(() => div.remove(), 300);
			}, 2500);
		}
	},
	template: `
	<div class="admin">
		<h1 class="section-title">Gestión de Snippets</h1>

		<div class="section-block">
			<form class="flowtitude-form" @submit.prevent="uploadSnippet">
				<div class="flowtitude-form-group">
					<label>Carpeta de destino:</label>
					<div style="display: flex; gap: 8px; align-items: center;">
						<select v-if="!showNewFolderInput" 
							v-model="selectedFolder" 
							class="small" >
							<option value="">(Default: custom)</option>
							<option v-for="folder in folders" :value="folder">{{ folder }}</option>
						</select>
						<input v-else
							type="text"
							v-model="newFolderName"
							placeholder="Nombre de la carpeta"
							class="small"
							@keyup.enter="createFolder"
						/>
						<button type="button" 
							class="btn btn-inline"
							@click="showNewFolderInput ? createFolder() : showNewFolderInput = true">
							{{ showNewFolderInput ? 'Crear' : 'Nueva carpeta' }}
						</button>
						<button v-if="showNewFolderInput" 
							type="button"
							class="btn btn-inline"
							@click="showNewFolderInput = false">
							Cancelar
						</button>
					</div>
				</div>

				<div class="flowtitude-form-group">
					<label>Archivo PHP:</label>
					<input type="file" 
						accept=".php" 
						class="input-file" 
						@change="e => snippetFile = e.target.files[0]" />
				</div>

				<div style="margin-top: 1rem;">
					<button type="submit" 
						class="btn btn-inline" 
						:disabled="uploading">
						{{ uploading ? 'Subiendo...' : 'Subir snippet' }}
					</button>
				</div>
			</form>
		</div>

		<h2 class="section-subtitle">Snippets existentes</h2>
		
		<div v-if="snippets.length === 0" style="padding: 20px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; margin-top: 10px;">
			<p style="margin: 0; color: #b91c1c; font-weight: 500;">
				⚠️ No hay snippets personalizados cargados actualmente.
			</p>
		</div>
		
		<table v-else class="upload-snippet-table">
			<thead>
				<tr>
					<th>Archivo</th>
					<th>Carpeta</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="snippet in snippets" :key="snippet.file">
					<td>{{ snippet.name }}</td>
					<td>
						<select 
							:value="snippet.folder" 
							@change="moveSnippet(snippet, $event.target.value)" 
							class="small">
							<option v-for="folder in folders" :value="folder">{{ folder }}</option>
						</select>
					</td>
					<td>
						<button @click="deleteSnippet(snippet)" class="delete">Eliminar</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	`
};
