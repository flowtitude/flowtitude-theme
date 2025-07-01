<?php
// inc/dynamic-providers/provider-jetengine.php

class Flowtitude_Provider_JetEngine {
    public static function get_value($tag, $context = []) {
        // Ejemplo: {jetengine.campo}
        if (preg_match('/^jetengine\\.(.+)$/', $tag, $matches)) {
            $field = $matches[1];
            // Usar la función de JetEngine para obtener el valor del campo dinámico
            if (function_exists('jet_engine')) {
                // Puedes ajustar el contexto según lo que necesites (post, user, etc.)
                $value = jet_engine()->listings->data->get_field_value($field, $context);
                return $value !== null ? $value : '';
            }
        }
        return null;
    }
} 