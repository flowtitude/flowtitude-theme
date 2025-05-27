<?php

class Flowtitude_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_flowtitude_save_colors', array($this, 'save_colors'));
        add_action('wp_ajax_flowtitude_save_spacing', array($this, 'save_spacing'));
        add_action('wp_ajax_flowtitude_save_layout', array($this, 'save_layout'));
        add_action('wp_ajax_flowtitude_save_typography', array($this, 'save_typography'));
        add_action('wp_ajax_flowtitude_update_tailwind_config', array($this, 'update_tailwind_config'));
    }

    public function save_typography() {
        check_ajax_referer('flowtitude_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }

        $typography = json_decode(stripslashes($_POST['typography']), true);
        if (!$typography) {
            wp_send_json_error('Datos de tipografía inválidos');
        }

        // Guardar en opciones de WordPress
        update_option('flowtitude_typography', $typography);

        // Actualizar el archivo CSS
        $css_file = WP_CONTENT_DIR . '/uploads/windpress/data/theme/flowtitude.css';
        if (!file_exists($css_file)) {
            wp_send_json_error('Archivo CSS no encontrado');
            return;
        }

        $css = file_get_contents($css_file);
        if ($css === false) {
            wp_send_json_error('No se pudo leer el archivo CSS');
            return;
        }

        // Actualizar variables en :root
        $root_vars = [
            '--font-body' => $typography['fontBody'],
            '--font-display' => $typography['fontDisplay'],
            '--ft-text-value' => $typography['ftTextValue'],
            '--ft-text-scale' => $typography['ftTextScale'],
            '--ft-text-factor' => $typography['ftTextFactor']
        ];

        foreach ($root_vars as $var => $value) {
            $pattern = '/'. preg_quote($var) .'\s*:\s*[^;]+;/';
            $replacement = $var .': '. $value .';';
            $css = preg_replace($pattern, $replacement, $css);
            
            // Si no existe la variable, la agregamos al final de :root
            if (!preg_match($pattern, $css)) {
                $css = preg_replace('/:root\s*{([^}]*)}/', ':root{$1' . "\n    " . $var . ': ' . $value . ';}', $css);
            }
        }

        if (file_put_contents($css_file, $css) === false) {
            wp_send_json_error('Error al guardar el archivo CSS');
            return;
        }

        // Actualizar la configuración de Tailwind
        $config_file = get_template_directory() . '/tailwind.config.js';
        if (!file_exists($config_file)) {
            wp_send_json_error('Archivo de configuración de Tailwind no encontrado');
            return;
        }

        $current_config = file_get_contents($config_file);
        if ($current_config === false) {
            wp_send_json_error('No se pudo leer el archivo de configuración de Tailwind');
            return;
        }

        // Actualizar la sección de tipografía en la configuración
        $typography_config = [
            'fontFamily' => [
                'body' => $typography['fontBody'],
                'display' => $typography['fontDisplay']
            ],
            'fontSize' => [
                'base' => $typography['ftTextValue'],
                'factor' => $typography['ftTextScale'],
                'ratio' => $typography['ftTextFactor']
            ]
        ];

        // Convertir a formato JavaScript
        $typography_js = json_encode($typography_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $typography_js = preg_replace('/"([^"]+)":/i', '$1:', $typography_js);

        // Actualizar la sección de theme.extend
        $pattern = '/(theme:\s*{\s*extend:\s*{)([^}]+)(})/s';
        $replacement = sprintf('$1%s$3', trim($typography_js, '{}'));
        
        $new_config = preg_replace($pattern, $replacement, $current_config);
        if ($new_config === null) {
            wp_send_json_error('Error al procesar la configuración de Tailwind');
            return;
        }

        if (file_put_contents($config_file, $new_config) === false) {
            wp_send_json_error('Error al guardar la configuración de Tailwind');
            return;
        }

        // Forzar una actualización del timestamp del archivo CSS
        touch($css_file);

        wp_send_json_success('Tipografía guardada correctamente');
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('flowtitude-admin', get_template_directory_uri() . '/admin-panel/js/admin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('flowtitude-admin', 'flowtitude_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('flowtitude_nonce'),
            'upload_url' => wp_upload_dir()['baseurl']
        ));
    }
} 