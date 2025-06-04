document.addEventListener("DOMContentLoaded", function () {
	const { createApp } = Vue;
	const { createRouter, createWebHashHistory } = VueRouter;

	const routes = [
		{ path: "/", component: window.Home },
		{ path: "/snippets", component: window.Snippets },
		{ path: "/security", component: window.Security },
		{ path: "/settings", component: window.Settings },
		{ path: '/bricks', component: window.Bricks },
		{ path: "/upload", component: window.Upload }
	];

	const router = createRouter({
		history: createWebHashHistory(),
		routes,
	});

	const App = {
		template: `
			<div class="admin-container">
				<nav class="admin-nav">
					<ul>
						<li><img :src="icon" alt="Icon" style="height: 1.5rem; margin-right: 1.5rem; filter: invert(1);" /></li>
						<li><router-link to="/">Home</router-link></li>
						<li><router-link to="/snippets">Snippets</router-link></li>
						<li><router-link to="/security">Security</router-link></li>
						<li><router-link to="/settings">Settings</router-link></li>
						<li><router-link to="/bricks">Bricks</router-link></li>
						<li><router-link to="/upload">Uploads</router-link></li>
					</ul>
				</nav>

				<div class="admin-content">
					<div v-if="error" class="notice notice-error" role="alert">
						<p>{{ error.message }}</p>
					</div>
					<router-view @error="handleError"></router-view>
				</div>
			</div>
		`,
		data() {
			return {
				icon: flowtitude_data.icon_url || '',
				error: null,
				errorTimeout: null
			};
		},
		methods: {
			handleError(error) {
				if (this.errorTimeout) {
					clearTimeout(this.errorTimeout);
				}

				if (!(error.type && error.message)) {
					error = window.FlowtitudeErrorHandler.handleError(error);
				}

				this.error = error;

				this.errorTimeout = setTimeout(() => {
					this.dismissError();
				}, 5000);
			},
			dismissError() {
				this.error = null;
				if (this.errorTimeout) {
					clearTimeout(this.errorTimeout);
					this.errorTimeout = null;
				}
			}
		}
	};

	// Registrar los componentes globalmente
	const app = createApp(App);
	
	// Registrar componentes
	
	app.use(router);
	app.mount('#flowtitude-admin-app');
});
