<?php

function flowtitude_get_colors() {
    // Verificar nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Ruta al archivo de configuración
    $config_file = get_template_directory() . '/tailwind.config.js';

    try {
        // Leer el archivo actual
        if (!file_exists($config_file)) {
            wp_send_json_error('Configuration file not found');
            return;
        }

        $current_config = file_get_contents($config_file);
        if ($current_config === false) {
            throw new Exception('Unable to read configuration file');
        }

        // Encontrar la sección de theme.colors
        if (preg_match('/theme:\s*{\s*colors:\s*{([^}]+)}/s', $current_config, $matches)) {
            $colors_json = $matches[1];
            // Limpiar el JSON para que sea válido
            $colors_json = preg_replace('/(\w+):/i', '"$1":', $colors_json);
            $colors_json = '{' . $colors_json . '}';
            
            $colors = json_decode($colors_json, true);
            if ($colors === null) {
                throw new Exception('Invalid JSON in configuration file');
            }
            
            wp_send_json_success($colors);
        } else {
            throw new Exception('Colors section not found in configuration');
        }
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function flowtitude_save_colors() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Obtener y validar los colores
    $colors = isset($_POST['colors']) ? json_decode(stripslashes($_POST['colors']), true) : null;
    if (!$colors) {
        wp_send_json_error('Invalid color data');
        return;
    }

    // Ruta al archivo de configuración
    $config_file = get_template_directory() . '/tailwind.config.js';

    try {
        // Leer el archivo actual
        if (!file_exists($config_file)) {
            wp_send_json_error('Configuration file not found');
            return;
        }

        $current_config = file_get_contents($config_file);
        if ($current_config === false) {
            throw new Exception('Unable to read configuration file');
        }

        // Convertir los colores a formato JavaScript
        $colors_js = json_encode($colors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        // Eliminar las comillas de las claves de objeto
        $colors_js = preg_replace('/"([^"]+)":/i', '$1:', $colors_js);

        // Encontrar y reemplazar la sección de theme.colors
        $pattern = '/(theme:\s*{\s*colors:\s*{)([^}]+)(})/s';
        $replacement = sprintf('$1%s$3', trim($colors_js, '{}'));
        
        $new_config = preg_replace($pattern, $replacement, $current_config);
        if ($new_config === null) {
            throw new Exception('Error processing configuration');
        }

        // Guardar el archivo
        if (file_put_contents($config_file, $new_config) === false) {
            throw new Exception('Unable to write configuration file');
        }

        wp_send_json_success('Colors saved successfully');
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function flowtitude_get_typography() {
    // Verificar nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Ruta al archivo de configuración
    $config_file = get_template_directory() . '/tailwind.config.js';

    try {
        // Leer el archivo actual
        if (!file_exists($config_file)) {
            wp_send_json_error('Configuration file not found');
            return;
        }

        $current_config = file_get_contents($config_file);
        if ($current_config === false) {
            throw new Exception('Unable to read configuration file');
        }

        // Encontrar la sección de theme.typography
        if (preg_match('/theme:\s*{\s*typography:\s*{([^}]+)}/s', $current_config, $matches)) {
            $typography_json = $matches[1];
            // Limpiar el JSON para que sea válido
            $typography_json = preg_replace('/(\w+):/i', '"$1":', $typography_json);
            $typography_json = '{' . $typography_json . '}';
            
            $typography = json_decode($typography_json, true);
            if ($typography === null) {
                throw new Exception('Invalid JSON in configuration file');
            }
            
            wp_send_json_success($typography);
        } else {
            throw new Exception('Typography section not found in configuration');
        }
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function flowtitude_save_typography() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Obtener y validar la tipografía
    $typography = isset($_POST['typography']) ? json_decode(stripslashes($_POST['typography']), true) : null;
    if (!$typography) {
        wp_send_json_error('Invalid typography data');
        return;
    }

    // Ruta al archivo CSS
    $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';

    try {
        // Leer el archivo actual
        if (!file_exists($css_file)) {
            wp_send_json_error('CSS file not found');
            return;
        }

        $css = file_get_contents($css_file);
        if ($css === false) {
            throw new Exception('Unable to read CSS file');
        }

        // Actualizar variables en @theme
        $theme_vars = [
            '--font-body' => "'" . $typography['fontBody'] . "'",
            '--font-display' => "'" . $typography['fontDisplay'] . "'"
        ];

        foreach ($theme_vars as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            $css = preg_replace($pattern, $replacement, $css);
            
            // Si no existe la variable, la agregamos al final de @theme
            if (!preg_match($pattern, $css)) {
                $css = preg_replace('/@theme\s*{([^}]*)}/', '@theme{$1' . "\n    " . $var . ': ' . $value . ';}', $css);
            }
        }

        // Actualizar variables en :root
        $root_vars = [
            '--ft-text-value' => $typography['ftTextValue'],
            '--ft-text-scale' => $typography['ftTextScale'],
            '--ft-text-factor' => $typography['ftTextFactor']
        ];

        foreach ($root_vars as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            $css = preg_replace($pattern, $replacement, $css);
        }

        // Guardar el archivo
        if (file_put_contents($css_file, $css) === false) {
            throw new Exception('Unable to write CSS file');
        }

        wp_send_json_success('Typography saved successfully');
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function flowtitude_get_tailwind_config() {
    check_ajax_referer('flowtitude_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $config_file = get_template_directory() . '/tailwind.config.js';
    
    if (!file_exists($config_file)) {
        wp_send_json_error('Configuration file not found');
        return;
    }

    $config_content = file_get_contents($config_file);
    
    // Extraer el contenido entre module.exports = { ... }
    if (preg_match('/module\.exports\s*=\s*({[\s\S]*})/', $config_content, $matches)) {
        $config_json = $matches[1];
        
        // Convertir el JS a JSON válido
        $config_json = preg_replace('/(\w+):/i', '"$1":', $config_json); // Añadir comillas a las keys
        $config_json = preg_replace('/,(\s*[}\]])/i', '$1', $config_json); // Eliminar comas trailing
        
        $config = json_decode($config_json, true);
        
        if ($config === null) {
            wp_send_json_error('Invalid configuration format');
            return;
        }
        
        wp_send_json_success($config);
    } else {
        wp_send_json_error('Invalid configuration file format');
    }
}

function flowtitude_update_tailwind_config() {
    check_ajax_referer('flowtitude_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    if (!isset($_POST['config'])) {
        wp_send_json_error('No configuration provided');
        return;
    }

    $config_file = get_template_directory() . '/tailwind.config.js';
    $new_config = json_decode(stripslashes($_POST['config']), true);
    
    if ($new_config === null) {
        wp_send_json_error('Invalid configuration format');
        return;
    }

    // Leer el archivo actual
    $current_content = file_get_contents($config_file);
    
    // Convertir el nuevo config a formato JS
    $config_js = json_encode($new_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $config_js = preg_replace('/"([^"]+)":/i', '$1:', $config_js); // Quitar comillas de las keys
    
    // Reemplazar la configuración manteniendo el resto del archivo
    $new_content = preg_replace(
        '/module\.exports\s*=\s*{[\s\S]*}/',
        'module.exports = ' . $config_js,
        $current_content
    );

    if (file_put_contents($config_file, $new_content) === false) {
        wp_send_json_error('Failed to save configuration');
        return;
    }

    wp_send_json_success('Configuration updated successfully');
}

function flowtitude_get_theme_colors() {
    // Verificar nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Ruta al archivo flowtitude.css
    $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';

    if (!file_exists($css_file)) {
        wp_send_json_error('Archivo de configuración no encontrado');
        return;
    }

    $css = file_get_contents($css_file);
    if ($css === false) {
        wp_send_json_error('No se pudo leer el archivo de configuración');
        return;
    }

    $result = [
        'primary' => [],
        'secondary' => [],
        'text' => null,
        'background' => null,
        'customColors' => []
    ];

    // Primero, extraer valores de :root si existen
    if (preg_match('/:root\s*{([^}]*)}/s', $css, $rootMatch)) {
        $rootVars = $rootMatch[1];
        if (preg_match('/--ft-color-text:\s*([^;]+);/', $rootVars, $textMatch)) {
            $result['text'] = trim($textMatch[1]);
        }
        if (preg_match('/--ft-color-background:\s*([^;]+);/', $rootVars, $bgMatch)) {
            $result['background'] = trim($bgMatch[1]);
        }
        if (preg_match('/--ft-color-primary:\s*([^;]+);/', $rootVars, $primaryMatch)) {
            $result['primary']['500'] = trim($primaryMatch[1]);
        }
        if (preg_match('/--ft-color-secondary:\s*([^;]+);/', $rootVars, $secondaryMatch)) {
            $result['secondary']['500'] = trim($secondaryMatch[1]);
        }
    }

    // Extraer variables de @theme SOLO si no existen en :root
    if (preg_match('/@theme\s*{([^}]*)}/s', $css, $themeMatch)) {
        $themeVars = $themeMatch[1];
        if (preg_match_all('/--color-([a-zA-Z0-9]+)-(\d+):\s*([^;]+);/', $themeVars, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = strtolower($m[1]);
                $scale = $m[2];
                $value = trim($m[3]);
                if ($scale === '500') {
                    if ($name === 'primary' && empty($result['primary']['500'])) {
                        $result['primary']['500'] = $value;
                    } elseif ($name === 'secondary' && empty($result['secondary']['500'])) {
                        $result['secondary']['500'] = $value;
                    } elseif ($name === 'text' && empty($result['text'])) {
                        $result['text'] = $value;
                    } elseif ($name === 'background' && empty($result['background'])) {
                        $result['background'] = $value;
                    } else if (!in_array($name, ['primary','secondary','text','background'])) {
                        $result['customColors'][$name] = [ '500' => $value ];
                    }
                }
            }
        }
    }

    wp_send_json_success($result);
}

function flowtitude_get_theme_typography() {
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Nonce inválido - Por favor, recarga la página');
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permisos insuficientes - Necesitas ser administrador');
        return;
    }
    
    $upload_dir = wp_upload_dir();
    $css_file = $upload_dir['basedir'] . '/windpress/data/theme/flowtitude.css';
    
    if (!file_exists($css_file)) {
        wp_send_json_error('Archivo CSS no encontrado en: ' . $css_file);
        return;
    }
    
    $css = file_get_contents($css_file);
    if ($css === false) {
        wp_send_json_error('No se pudo leer el archivo CSS');
        return;
    }
    
    $result = [
        'fontBody' => null,
        'fontDisplay' => null,
        'ftTextValue' => null,
        'ftTextScale' => null,
        'ftTextFactor' => null
    ];
    // @theme
    if (preg_match('/@theme\s*{([^}]*)}/s', $css, $themeMatch)) {
        $themeVars = $themeMatch[1];
        if (preg_match('/--font-body:\s*([\'\"]?[^;\'\"]+[\'\"]?);/', $themeVars, $bodyMatch)) {
            $result['fontBody'] = trim($bodyMatch[1], "'\"");
        }
        if (preg_match('/--font-display:\s*([\'\"]?[^;\'\"]+[\'\"]?);/', $themeVars, $displayMatch)) {
            $result['fontDisplay'] = trim($displayMatch[1], "'\"");
        }
    }
    // :root
    if (preg_match('/:root\s*{([^}]*)}/s', $css, $rootMatch)) {
        $rootVars = $rootMatch[1];
        if (preg_match('/--ft-text-value:\s*([^;]+);/', $rootVars, $valMatch)) {
            $result['ftTextValue'] = trim($valMatch[1]);
        }
        if (preg_match('/--ft-text-scale:\s*([^;]+);/', $rootVars, $scaleMatch)) {
            $result['ftTextScale'] = trim($scaleMatch[1]);
        }
        if (preg_match('/--ft-text-factor:\s*([^;]+);/', $rootVars, $factorMatch)) {
            $result['ftTextFactor'] = trim($factorMatch[1]);
        }
    }
    wp_send_json_success($result);
}

function flowtitude_get_theme_spacing() {
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';
    if (!file_exists($css_file)) {
        wp_send_json_error('Archivo de configuración no encontrado');
        return;
    }
    $css = file_get_contents($css_file);
    if ($css === false) {
        wp_send_json_error('No se pudo leer el archivo de configuración');
        return;
    }
    $result = [
        'ftSpaceValue' => null,
        'ftSpaceScale' => null,
        'ftSpaceFactor' => null,
        'ftContentSpace' => null,
        'ftWideSpace' => null,
        'spacingBlock' => null,
        'spacingColumns' => null,
        'spacingSection' => null
    ];
    // :root
    if (preg_match('/:root\s*{([^}]*)}/s', $css, $rootMatch)) {
        $rootVars = $rootMatch[1];
        if (preg_match('/--ft-space-value:\s*([^;]+);/', $rootVars, $valMatch)) {
            $result['ftSpaceValue'] = trim($valMatch[1]);
        }
        if (preg_match('/--ft-space-scale:\s*([^;]+);/', $rootVars, $scaleMatch)) {
            $result['ftSpaceScale'] = trim($scaleMatch[1]);
        }
        if (preg_match('/--ft-space-factor:\s*([^;]+);/', $rootVars, $factorMatch)) {
            $result['ftSpaceFactor'] = trim($factorMatch[1]);
        }
        if (preg_match('/--ft-content-space:\s*([^;]+);/', $rootVars, $contentMatch)) {
            $result['ftContentSpace'] = trim($contentMatch[1]);
        }
        if (preg_match('/--ft-wide-space:\s*([^;]+);/', $rootVars, $wideMatch)) {
            $result['ftWideSpace'] = trim($wideMatch[1]);
        }
    }
    // @theme
    if (preg_match('/@theme\s*{([^}]*)}/s', $css, $themeMatch)) {
        $themeVars = $themeMatch[1];
        if (preg_match('/--spacing-block:\s*([^;]+);/', $themeVars, $blockMatch)) {
            $result['spacingBlock'] = trim($blockMatch[1]);
        }
        if (preg_match('/--spacing-columns:\s*([^;]+);/', $themeVars, $columnsMatch)) {
            $result['spacingColumns'] = trim($columnsMatch[1]);
        }
        if (preg_match('/--spacing-section:\s*([^;]+);/', $themeVars, $sectionMatch)) {
            $result['spacingSection'] = trim($sectionMatch[1]);
        }
    }
    wp_send_json_success($result);
}

function flowtitude_get_theme_layout() {
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';
    if (!file_exists($css_file)) {
        wp_send_json_error('Archivo de configuración no encontrado');
        return;
    }
    $css = file_get_contents($css_file);
    if ($css === false) {
        wp_send_json_error('No se pudo leer el archivo de configuración');
        return;
    }
    $result = [
        'ftContainer' => null,
        'ftPaddingContentX' => null,
        'ftPaddingContentY' => null,
        'ftMobileColumns' => null,
        'ftTabletColumns' => null,
        'ftCard' => [
            'xs' => null,
            'sm' => null,
            'md' => null,
            'lg' => null,
            'xl' => null
        ]
    ];
    // :root
    if (preg_match('/:root\s*{([^}]*)}/s', $css, $rootMatch)) {
        $rootVars = $rootMatch[1];
        if (preg_match('/--ft-container:\s*([^;]+);/', $rootVars, $containerMatch)) {
            $result['ftContainer'] = trim($containerMatch[1]);
        }
        if (preg_match('/--ft-padding-content-x:\s*([^;]+);/', $rootVars, $padXMatch)) {
            $result['ftPaddingContentX'] = trim($padXMatch[1]);
        }
        if (preg_match('/--ft-padding-content-y:\s*([^;]+);/', $rootVars, $padYMatch)) {
            $result['ftPaddingContentY'] = trim($padYMatch[1]);
        }
        if (preg_match('/--ft-mobile-columns:\s*([^;]+);/', $rootVars, $mobileMatch)) {
            $result['ftMobileColumns'] = trim($mobileMatch[1]);
        }
        if (preg_match('/--ft-tablet-columns:\s*([^;]+);/', $rootVars, $tabletMatch)) {
            $result['ftTabletColumns'] = trim($tabletMatch[1]);
        }
        if (preg_match('/--ft-card-xs:\s*([^;]+);/', $rootVars, $xsMatch)) {
            $result['ftCard']['xs'] = trim($xsMatch[1]);
        }
        if (preg_match('/--ft-card-sm:\s*([^;]+);/', $rootVars, $smMatch)) {
            $result['ftCard']['sm'] = trim($smMatch[1]);
        }
        if (preg_match('/--ft-card-md:\s*([^;]+);/', $rootVars, $mdMatch)) {
            $result['ftCard']['md'] = trim($mdMatch[1]);
        }
        if (preg_match('/--ft-card-lg:\s*([^;]+);/', $rootVars, $lgMatch)) {
            $result['ftCard']['lg'] = trim($lgMatch[1]);
        }
        if (preg_match('/--ft-card-xl:\s*([^;]+);/', $rootVars, $xlMatch)) {
            $result['ftCard']['xl'] = trim($xlMatch[1]);
        }
    }
    wp_send_json_success($result);
}

function flowtitude_save_theme_colors() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Obtener y validar los colores
    $colors = isset($_POST['colors']) ? json_decode(stripslashes($_POST['colors']), true) : null;
    if (!$colors) {
        wp_send_json_error('Invalid color data: ' . print_r($_POST['colors'], true));
        return;
    }

    try {
        // Verificar que la clase FlowtitudeColor existe
        if (!class_exists('FlowtitudeColor')) {
            throw new Exception('La clase FlowtitudeColor no está disponible');
        }

        // Generar matices para los colores primarios y secundarios
        $colorGenerator = new FlowtitudeColor();
        
        // Procesar colores primarios y secundarios
        foreach (['primary', 'secondary'] as $type) {
            if (isset($colors[$type]) && isset($colors[$type]['500'])) {
                $baseColor = $colors[$type]['500'];
                if (preg_match('/^#[0-9a-f]{6}$/i', $baseColor)) {
                    try {
                        $shades = $colorGenerator->generateColorScale($baseColor);
                        if (empty($shades)) {
                            throw new Exception('No se pudieron generar los matices para ' . $type);
                        }
                        $colors[$type] = $shades;
                    } catch (Exception $e) {
                        wp_send_json_error('Error generando matices para ' . $type . ': ' . $e->getMessage());
                        return;
                    }
                } else {
                    wp_send_json_error('Formato de color inválido para ' . $type . ': ' . $baseColor);
                    return;
                }
            }
        }

        // 1. Guardar en flowtitude.css
        $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';
        if (!file_exists($css_file)) {
            wp_send_json_error('Archivo CSS no encontrado: ' . $css_file);
            return;
        }

        $css = file_get_contents($css_file);
        if ($css === false) {
            throw new Exception('No se pudo leer el archivo CSS');
        }

        // Asegurarnos de que existe la sección :root
        if (!preg_match('/:root\s*{([^}]*)}/s', $css)) {
            $css = ":root {\n}\n" . $css;
        }

        // Asegurarnos de que existe la sección @theme
        if (!preg_match('/@theme\s*{([^}]*)}/s', $css)) {
            $css .= "\n@theme {\n}\n";
        }

        // Actualizar colores en :root
        $rootVars = [
            '--ft-color-text' => $colors['text'],
            '--ft-color-background' => $colors['background'],
            '--ft-color-primary' => isset($colors['primary']['500']) ? $colors['primary']['500'] : null,
            '--ft-color-secondary' => isset($colors['secondary']['500']) ? $colors['secondary']['500'] : null
        ];

        foreach ($rootVars as $var => $value) {
            if ($value !== null) {
                $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
                $replacement = $var .': '. $value .';';
                $css = preg_replace($pattern, $replacement, $css);
                
                // Si no existe la variable, la agregamos al final de :root
                if (!preg_match($pattern, $css)) {
                    $css = preg_replace('/:root\s*{([^}]*)}/', ':root{$1' . "\n    " . $var . ': ' . $value . ';}', $css);
                }
            }
        }

        // Actualizar colores en @theme
        $themeColors = [];
        
        // Procesar colores primarios y secundarios
        foreach (['primary', 'secondary'] as $type) {
            if (isset($colors[$type]) && is_array($colors[$type])) {
                foreach ($colors[$type] as $scale => $value) {
                    if ($scale !== 'DEFAULT') {
                        $themeColors["--color-{$type}-{$scale}"] = $value;
                    }
                }
            }
        }

        // Procesar colores personalizados
        foreach ($colors as $name => $value) {
            if (!in_array($name, ['primary', 'secondary', 'text', 'background']) && is_array($value)) {
                foreach ($value as $scale => $color) {
                    if ($scale !== 'DEFAULT') {
                        $themeColors["--color-{$name}-{$scale}"] = $color;
                    }
                }
            }
        }

        // Actualizar variables en @theme
        foreach ($themeColors as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            $css = preg_replace($pattern, $replacement, $css);
            
            // Si no existe la variable, la agregamos al final de @theme
            if (!preg_match($pattern, $css)) {
                $css = preg_replace('/@theme\s*{([^}]*)}/', '@theme{$1' . "\n    " . $var . ': ' . $value . ';}', $css);
            }
        }

        // Guardar el archivo CSS
        if (file_put_contents($css_file, $css) === false) {
            throw new Exception('No se pudo escribir el archivo CSS');
        }

        // Forzar una actualización del timestamp del archivo CSS
        touch($css_file);

        wp_send_json_success('Colores guardados exitosamente');
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function flowtitude_save_spacing() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $spacing = isset($_POST['spacing']) ? json_decode(stripslashes($_POST['spacing']), true) : null;
    if (!$spacing) {
        wp_send_json_error('Invalid spacing data');
        return;
    }

    $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';
    if (!file_exists($css_file)) {
        wp_send_json_error('CSS file not found');
        return;
    }

    try {
        $css = file_get_contents($css_file);
        if ($css === false) {
            throw new Exception('Unable to read CSS file');
        }

        // Actualizar variables en :root
        $root_vars = [
            '--ft-space-value' => $spacing['ftSpaceValue'],
            '--ft-space-scale' => $spacing['ftSpaceScale'],
            '--ft-space-factor' => $spacing['ftSpaceFactor']
        ];

        foreach ($root_vars as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            $css = preg_replace($pattern, $replacement, $css);
        }

        // Actualizar variables en @theme
        $theme_vars = [
            '--spacing-block' => 'calc(var(--ft-space-value) * ' . $spacing['spacingBlock'] . ')',
            '--spacing-columns' => 'calc(var(--ft-space-value) * ' . $spacing['spacingColumns'] . ')',
            '--spacing-section' => 'calc(var(--ft-space-value) * ' . $spacing['spacingSection'] . ')'
        ];

        foreach ($theme_vars as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            $css = preg_replace($pattern, $replacement, $css);
            
            // Si no existe la variable, la agregamos al final de @theme
            if (!preg_match($pattern, $css)) {
                $css = preg_replace('/@theme\s*{([^}]*)}/', '@theme{$1' . "\n    " . $var . ': ' . $value . ';}', $css);
            }
        }

        if (file_put_contents($css_file, $css) === false) {
            throw new Exception('Unable to write CSS file');
        }

        wp_send_json_success('Spacing saved successfully');
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

function flowtitude_save_layout() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'flowtitude_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $layout = isset($_POST['layout']) ? json_decode(stripslashes($_POST['layout']), true) : null;
    if (!$layout) {
        wp_send_json_error('Invalid layout data');
        return;
    }

    $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';
    if (!file_exists($css_file)) {
        wp_send_json_error('CSS file not found');
        return;
    }

    try {
        $css = file_get_contents($css_file);
        if ($css === false) {
            throw new Exception('Unable to read CSS file');
        }

        // Actualizar variables en :root
        $root_vars = [
            '--ft-container' => $layout['ftContainer'],
            '--ft-padding-content-x' => $layout['ftPaddingContentX'],
            '--ft-padding-content-y' => $layout['ftPaddingContentY'],
            '--ft-mobile-columns' => $layout['ftMobileColumns'],
            '--ft-tablet-columns' => $layout['ftTabletColumns']
        ];

        // Agregar variables de tarjetas
        foreach ($layout['ftCard'] as $size => $value) {
            $root_vars['--ft-card-' . $size] = $value;
        }

        // Asegurarnos de que existe la sección :root
        if (!preg_match('/:root\s*{([^}]*)}/s', $css)) {
            $css = ":root {\n}\n" . $css;
        }

        // Reemplazar o agregar cada variable
        foreach ($root_vars as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            
            if (preg_match($pattern, $css)) {
                $css = preg_replace($pattern, $replacement, $css);
            } else {
                $css = preg_replace('/:root\s*{([^}]*)}/', ':root{$1' . "\n    " . $var . ': ' . $value . ';}', $css);
            }
        }

        // Guardar el archivo CSS
        if (file_put_contents($css_file, $css) === false) {
            throw new Exception('Unable to write CSS file');
        }

        // Forzar una actualización del timestamp del archivo CSS
        touch($css_file);

        wp_send_json_success('Layout saved successfully');
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

add_action('wp_ajax_flowtitude_get_colors', 'flowtitude_get_colors');
add_action('wp_ajax_flowtitude_save_colors', 'flowtitude_save_colors');
add_action('wp_ajax_flowtitude_get_typography', 'flowtitude_get_typography');
add_action('wp_ajax_flowtitude_save_typography', 'flowtitude_save_typography');
add_action('wp_ajax_flowtitude_get_tailwind_config', 'flowtitude_get_tailwind_config');
add_action('wp_ajax_flowtitude_update_tailwind_config', 'flowtitude_update_tailwind_config');
add_action('wp_ajax_flowtitude_get_theme_colors', 'flowtitude_get_theme_colors');
add_action('wp_ajax_flowtitude_get_theme_typography', 'flowtitude_get_theme_typography');
add_action('wp_ajax_flowtitude_get_theme_spacing', 'flowtitude_get_theme_spacing');
add_action('wp_ajax_flowtitude_get_theme_layout', 'flowtitude_get_theme_layout');
add_action('wp_ajax_flowtitude_save_theme_colors', 'flowtitude_save_theme_colors');
add_action('wp_ajax_flowtitude_save_spacing', 'flowtitude_save_spacing');
add_action('wp_ajax_flowtitude_save_layout', 'flowtitude_save_layout'); 