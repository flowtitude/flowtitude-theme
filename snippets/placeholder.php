<?php
// === PLACEHOLDER GENERATOR ===
// Genera una imagen SVG accesible por URLs como /placeholder/light o /placeholder/1200x600/dark

if (!defined('ABSPATH')) exit;

function flowtitude_generate_placeholder($width = 900, $height = 500, $theme = 'dark') {
	$theme = $theme === 'light' ? 'light' : 'dark';

	$bg_color     = $theme === 'dark' ? '#333333' : '#dedede';
	$icon_color   = $theme === 'dark' ? '#bbbbbb' : '#666666';

	$icon_svg = "
	<svg xmlns='http://www.w3.org/2000/svg' width='90' height='90' viewBox='0 0 256 256'>
		<rect width='256' height='256' fill='$bg_color'/>
		<path fill='$icon_color' d='M216 42H40a14 14 0 0 0-14 14v144a14 14 0 0 0 14 14h176a14 14 0 0 0 14-14V56a14 14 0 0 0-14-14M40 54h176a2 2 0 0 1 2 2v107.57l-29.47-29.47a14 14 0 0 0-19.8 0l-21.42 21.42l-45.41-45.42a14 14 0 0 0-19.8 0L38 154.2V56a2 2 0 0 1 2-2m-2 146v-28.83l52.58-52.58a2 2 0 0 1 2.84 0L176.83 202H40a2 2 0 0 1-2-2m178 2h-22.2l-38-38l21.41-21.42a2 2 0 0 1 2.83 0l38 38V200a2 2 0 0 1-2.04 2m-70-102a10 10 0 1 1 10 10a10 10 0 0 1-10-10'/>
	</svg>
	";

	header("Content-type: image/svg+xml");

	echo '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">';
	echo '<rect width="100%" height="100%" fill="' . $bg_color . '" />';
	echo '<foreignObject x="0" y="0" width="100%" height="100%">';
	echo '<div xmlns="http://www.w3.org/1999/xhtml" style="position: relative; width: 100%; height: 100%; padding: 1rem;">';
	echo '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90px; height: 90px;">' . $icon_svg . '</div>';
	echo '</div>';
	echo '</foreignObject>';
	echo '</svg>';
	exit;
}

// === Query vars y parsing de URLs amigables ===
function flowtitude_handle_placeholder() {
	if (get_query_var('placeholder')) {
		$width = 900;
		$height = 500;
		$theme = 'dark';

		$sizes = [
			'sv' => [400, 600], 'sh' => [600, 400], 'sc' => [400, 400],
			'mv' => [600, 900], 'mh' => [900, 600], 'mc' => [600, 600],
			'lv' => [900, 1200], 'lh' => [1200, 900], 'lc' => [900, 900],
			'hd' => [1920, 1080],
		];

		$size_param = get_query_var('size');

		if ($size_param === 'light' || $size_param === 'dark') {
			$theme = $size_param;
		} elseif (preg_match('/^(\d+)x(\d+)$/', $size_param, $matches)) {
			$width = (int)$matches[1];
			$height = (int)$matches[2];
		} elseif (isset($sizes[$size_param])) {
			[$width, $height] = $sizes[$size_param];
		}

		if ($w = intval(get_query_var('width'))) $width = $w;
		if ($h = intval(get_query_var('height'))) $height = $h;
		if ($t = get_query_var('theme')) $theme = sanitize_key($t);

		flowtitude_generate_placeholder($width, $height, $theme);
	}
}
add_action('template_redirect', 'flowtitude_handle_placeholder', 1);
