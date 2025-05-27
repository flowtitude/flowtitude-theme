window.UploadBricks = {
	data() {
		return {
			file: null,
			isUploading: false,
		};
	},

	methods: {
		handleFileChange(e) {
			this.file = e.target.files[0];
		},

		async uploadFile() {
			if (!this.file) {
				this.showNotice("Selecciona un archivo PHP", false);
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
					this.showNotice('Componente subido correctamente', true);
					this.file = null;
					this.$refs.fileInput.value = '';
				} else {
					this.showNotice(result.message || 'No se pudo subir el componente', false);
				}
			} catch (error) {
				this.showNotice('Error al subir el componente', false);
			}

			this.isUploading = false;
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
			<h1 class="section-title">Subir componente Bricks</h1>

			<div class="section-block">
				<form class="flowtitude-form" @submit.prevent="uploadFile">

					<div class="flowtitude-form-group">
						<label>Archivo PHP:</label>
						<input 
							type="file" 
							@change="handleFileChange" 
							ref="fileInput" 
							accept=".php"
							class="input-file" />
					</div>

					<div style="margin-top: 1rem;">
						<button type="submit" 
							class="btn btn-inline" 
							:disabled="isUploading">
							{{ isUploading ? "Subiendo..." : "Subir componente" }}
						</button>
					</div>
				</form>
			</div>
		</div>
	`
};
