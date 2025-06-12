window.UploadBricks = {
	data() {
		return {
			file: null,
			isUploading: false,
			result: null,
		};
	},

	methods: {
		handleFileChange(e) {
			this.file = e.target.files[0];
			this.result = null;
		},

		async uploadFile() {
			if (!this.file) {
				this.showNotice("Selecciona un archivo PHP o ZIP", 'error');
				return;
			}

			this.isUploading = true;

			const formData = new FormData();
			formData.append('file', this.file);

			try {
				const res = await fetch('/wp-json/flowtitude/v1/upload-bricks-component', {
					method: 'POST',
					headers: {
						'X-WP-Nonce': flowtitude_data.rest_nonce
					},
					body: formData
				});

				const result = await res.json();

				if (result.success) {
					this.showNotice(result.message || 'Componentes subidos correctamente', 'success');
					this.file = null;
					this.$refs.fileInput.value = '';
				} else {
					this.showNotice(result.message || 'No se pudo subir el componente', 'error');
				}
				this.result = result;
			} catch (error) {
				this.showNotice('Error al subir el componente', 'error');
			}

			this.isUploading = false;
		},

		showNotice(msg, type = 'info') {
			window.FlowtitudeNotify.show(msg, type);
		}
	},

	template: `
		<div class="admin">
			<h1 class="section-title">Subir componente Bricks</h1>

			<div class="section-block">
				<form class="flowtitude-form" @submit.prevent="uploadFile">

					<div class="flowtitude-form-group">
						<label>Archivo PHP o ZIP:</label>
						<input 
							type="file" 
							@change="handleFileChange" 
							ref="fileInput" 
							accept=".php,.zip"
							class="input-file" />
					</div>

					<div style="margin-top: 1rem;">
						<button type="submit" 
							class="btn btn-inline" 
							:disabled="isUploading">
							{{ isUploading ? "Subiendo..." : "Subir componente(s)" }}
						</button>
					</div>
				</form>

				<div v-if="result" style="margin-top:2rem;">
					<h3>Resumen de la subida</h3>
					<div v-if="result.files && result.files.length">
						<p><strong>Instalados correctamente:</strong></p>
						<ul>
							<li v-for="f in result.files" :key="f.file">
								✔️ {{ f.file }} <span v-if="f.folder">({{ f.folder }})</span>
							</li>
						</ul>
					</div>
					<div v-if="result.errors && result.errors.length">
						<p><strong>Errores:</strong></p>
						<ul>
							<li v-for="e in result.errors" :key="e.file">
								❌ {{ e.file }} — {{ e.error }}
							</li>
						</ul>
					</div>
					<div v-if="(!result.files || !result.files.length) && (!result.errors || !result.errors.length)">
						<p>No se procesó ningún archivo.</p>
					</div>
				</div>
			</div>
		</div>
	`
};
