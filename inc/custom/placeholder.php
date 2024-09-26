<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function generate_placeholder($width = 900, $height = 500, $theme = 'light') {
    // Definir colores según el tema (oscuro o claro)
    if ($theme === 'dark') {
        $bg_color = '#333333';  // Color de fondo oscuro
        $stroke_color = '#444444';  // Color de las líneas para tema oscuro
        $icon_color = '#bbbbbb';  // Color del ícono
    } else {
        $bg_color = '#dedede';  // Color de fondo claro
        $stroke_color = '#efefef';  // Color de las líneas para tema claro
        $icon_color = '#cccccc';  // Color del ícono
    }

    // Ajuste dinámico para generar líneas diagonales que cubran el espacio completamente
    $lines = "";
    $spacing = 100; // Espaciado entre las líneas diagonales
    for ($i = -$width; $i < $width * 2; $i += $spacing) {
        $lines .= "<path d='M$i 0 L" . ($i + $width) . " $height' />";
    }

    // El ícono se mantiene igual
    $icon_svg = "
        <svg xmlns='http://www.w3.org/2000/svg' width='90' height='90' viewBox='0 0 256 256'>
            <rect width='256' height='256' fill='$bg_color'/>
            <path fill='$icon_color' d='M216 42H40a14 14 0 0 0-14 14v144a14 14 0 0 0 14 14h176a14 14 0 0 0 14-14V56a14 14 0 0 0-14-14M40 54h176a2 2 0 0 1 2 2v107.57l-29.47-29.47a14 14 0 0 0-19.8 0l-21.42 21.42l-45.41-45.42a14 14 0 0 0-19.8 0L38 154.2V56a2 2 0 0 1 2-2m-2 146v-28.83l52.58-52.58a2 2 0 0 1 2.84 0L176.83 202H40a2 2 0 0 1-2-2m178 2h-22.2l-38-38l21.41-21.42a2 2 0 0 1 2.83 0l38 38V200a2 2 0 0 1-2.04 2m-70-102a10 10 0 1 1 10 10a10 10 0 0 1-10-10'/>
        </svg>";

    // Cabecera de la respuesta tipo imagen SVG
    header("Content-type: image/svg+xml");

    // Generación del SVG dinámico
    echo '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">';
    echo '<defs>';
    // Añadir el patrón de líneas dinámicas basado en el tamaño del contenedor
    echo '<pattern id="backgroundPattern" patternUnits="userSpaceOnUse" width="' . $width . '" height="' . $height . '">';
    echo '<g fill="none" stroke="' . $stroke_color . '" stroke-width="3" stroke-opacity="1">';
    echo $lines; // Dibujar las líneas calculadas
    echo '</g>';
    echo '</pattern>';
    echo '</defs>';
    // El rectángulo de fondo ahora cubre todo el espacio correctamente
    echo '<rect width="100%" height="100%" fill="' . $bg_color . '" />';
    echo '<rect width="100%" height="100%" fill="url(#backgroundPattern)" />';
    echo '<foreignObject x="0" y="0" width="100%" height="100%">';
    echo '<div xmlns="http://www.w3.org/1999/xhtml" style="position: relative; width: 100%; height: 100%;">';
    echo '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90px; height: 90px;">' . $icon_svg . '</div>';
    echo '</div>';
    echo '</foreignObject>';
    echo '</svg>';
    exit;
}

// Hooks para query vars y redirección
add_filter('query_vars', function($query_vars) {
    $query_vars[] = 'placeholder';
    $query_vars[] = 'theme';
    return $query_vars;
});
add_action('template_redirect', function() {
    if (get_query_var('placeholder')) {
        $width = isset($_GET['width']) ? intval($_GET['width']) : 100;
        $height = isset($_GET['height']) ? intval($_GET['height']) : 100;
        $theme = isset($_GET['theme']) ? sanitize_text_field($_GET['theme']) : 'light';

        generate_placeholder($width, $height, $theme);
    }
});