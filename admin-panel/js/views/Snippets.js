window.Snippets = {
	data() {
		return {
			snippets: {},
			activeSnippets: [],
			systemSnippets: [],
			message: '',
		};
	},

	created() {
		fetch('/wp-json/flowtitude/v1/snippets', {
			headers: { 'X-WP-Nonce': flowtitude_data.rest_nonce }
		})
		.then(res => res.json())
		.then(data => {
			this.systemSnippets = Object.values(data)
				.flat()
				.filter(s => s.folder === 'utils')
				.map(s => s.file);
			
			this.snippets = Object.fromEntries(
				Object.entries(data).map(([key, snippets]) => [
					key,
					snippets.filter(s => s.folder !== 'utils')
				])
			);
			
			this.activeSnippets = [
				...this.systemSnippets,
				...Object.values(data)
					.flat()
					.filter(s => s.active && s.folder !== 'utils')
					.map(s => s.file)
			];
		});
	},

	methods: {
		isSystemSnippet(file) {
			return this.systemSnippets.includes(file);
		},

		toggle(file) {
			if (this.isSystemSnippet(file)) {
				this.showNotice('Los snippets del sistema no se pueden desactivar', 'error');
				return;
			}

			const index = this.activeSnippets.indexOf(file);
			if (index > -1) {
				this.activeSnippets.splice(index, 1);
			} else {
				this.activeSnippets.push(file);
			}
			this.saveActiveSnippets();
		},

		async saveActiveSnippets() {
			try {
				const snippetsToSave = [...new Set([...this.systemSnippets, ...this.activeSnippets])];
				
				const response = await fetch('/wp-json/flowtitude/v1/snippets', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify(snippetsToSave)
				});

				if (!response.ok) {
					throw new Error('Error al guardar los snippets');
				}

				this.showNotice('Cambios guardados correctamente', 'success');
			} catch (error) {
				console.error('Error al guardar:', error);
				this.showNotice(error.message, 'error');
			}
		},

		async remove(file) {
			if (this.isSystemSnippet(file)) {
				this.showNotice('Los snippets del sistema no se pueden eliminar', 'error');
				return;
			}

			if (!confirm(`¿Eliminar el archivo ${file}?`)) return;

			try {
				const res = await fetch('/wp-json/flowtitude/v1/snippet-files/delete', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: JSON.stringify({ file })
				});

				const result = await res.json();
				if (result.success) {
					for (let key in this.snippets) {
						this.snippets[key] = this.snippets[key].filter(s => s.file !== file);
					}
					this.activeSnippets = this.activeSnippets.filter(f => f !== file);
					this.showNotice('Snippet eliminado correctamente', 'success');
				}
			} catch (error) {
				this.showNotice('Error al eliminar el snippet', 'error');
			}
		},

		showNotice(msg, type = 'info') {
			window.FlowtitudeNotify.show(msg, type);
		}
	},

	template: `
		<div class="admin">
			<h1 class="section-title">Snippets</h1>

			<div v-if="Object.keys(snippets).length === 0" class="snippet-empty">
				<p>
					⚠️ No hay snippets personalizados cargados actualmente.
				</p>
				<router-link to="/upload" class="btn">
					Subir Primer Snippet
				</router-link>
			</div>

			<div v-else class="content-area">
				<details v-for="(group, key) in snippets" :key="key" class="toggle-section snippet-group" open>
					<summary style="display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
						<span>{{ key }}</span>
						<svg class="caret" viewBox="0 0 20 20"><polyline points="6,8 10,12 14,8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>

					<div v-for="snippet in group" :key="snippet.file" class="snippet-item">
						<div class="snippet-info">
							<div class="snippet-title">{{ snippet.title }}</div>
							<div class="snippet-desc">{{ snippet.description }}</div>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<label class="switch">
								<input 
									type="checkbox" 
									:checked="activeSnippets.includes(snippet.file)" 
									@change="toggle(snippet.file)"
									:disabled="isSystemSnippet(snippet.file)" />
								<span class="slider"></span>
							</label>
							<button 
								class="delete" 
								@click="remove(snippet.file)"
								:disabled="isSystemSnippet(snippet.file)"
								:style="isSystemSnippet(snippet.file) ? 'opacity: 0.5; cursor: not-allowed;' : ''">
								Eliminar
							</button>
						</div>
					</div>
				</details>
			</div>

			<p v-if="message" class="notice-popup show">{{ message }}</p>
		</div>
	`
};
