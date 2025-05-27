<?php

if (!defined('ABSPATH')) exit;

// <tag>Intersect</tag> Observer JS
add_action('init', function () {
	$settings = get_option('flowtitude_settings', []);

	if (!empty($settings['intersect_enabled'])) {
		add_action('wp_footer', 'flowtitude_intersect_script', 100);
	}
});

/**
 * Inserta el script de Intersection Observer al final del <body> para activar clases Tailwind con el prefijo intersect.
 *
 * Añade logs de depuración en consola para navegadores sin soporte y casos de error.
 *
 * @return void
 */
function flowtitude_intersect_script() {
	echo <<<HTML
<script>
(() => {
	try {
		if (typeof IntersectionObserver === 'undefined') {
			console.warn('[Flowtitude] Este navegador no soporta IntersectionObserver. Las animaciones intersect no funcionarán.');
			return;
		}
		var i = {
			start() {
				if (document.readyState === "loading") {
					document.addEventListener("DOMContentLoaded", () => this.observe());
					return;
				}
				this.observe();
			},
			observe() {
				let s = [
					'[class*=" intersect:"]',
					'[class*=":intersect:"]',
					'[class^="intersect:"]',
					'[class="intersect"]',
					'[class*=" intersect "]',
					'[class^="intersect "]',
					'[class$=" intersect"]'
				];
				const nodes = document.querySelectorAll(s.join(","));
				if (nodes.length === 0) {
					console.info('[Flowtitude] No se encontraron elementos con clases intersect para observar.');
					return;
				}
				nodes.forEach(t => {
					let e = new IntersectionObserver(c => {
						c.forEach(n => {
							if (!n.isIntersecting) {
								t.setAttribute("no-intersect", "");
								return;
							}
							t.removeAttribute("no-intersect");
							if (t.classList.contains("intersect-once")) e.disconnect();
						});
					}, { threshold: this.getThreshold(t) });
					e.observe(t);
				});
			},
			getThreshold(s) {
				return s.classList.contains("intersect-full") ? 0.99 :
				   s.classList.contains("intersect-half") ? 0.5 : 0;
			}
		};
		i.start();
	} catch (e) {
		console.error('[Flowtitude] Error en el script de Intersection Observer:', e);
	}
})();
</script>
HTML;
}
