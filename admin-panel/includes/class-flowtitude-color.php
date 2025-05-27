<?php

class FlowtitudeColor {
    public function __construct() {}

    public function generateColorScale($baseColor) {
        if (!preg_match('/^#[0-9a-f]{6}$/i', $baseColor)) {
            throw new Exception('Invalid color format. Expected hex color (e.g. #ff0000)');
        }
        return $this->generateShades($baseColor);
    }

    private function generateShades($baseColor) {
        // Extraer componentes RGB
        $r = hexdec(substr($baseColor, 1, 2));
        $g = hexdec(substr($baseColor, 3, 2));
        $b = hexdec(substr($baseColor, 5, 2));

        // Definir los factores de ajuste para cada matiz
        $shades = [
            50 => 0.95,  // Muy claro
            100 => 0.90,
            200 => 0.85,
            300 => 0.80,
            400 => 0.75,
            500 => 1.00, // Color base
            600 => 0.70,
            700 => 0.65,
            800 => 0.60,
            900 => 0.55,
            950 => 0.50  // Muy oscuro
        ];

        $result = [];
        foreach ($shades as $shade => $factor) {
            // Ajustar cada componente RGB
            $newR = min(255, max(0, round($r * $factor)));
            $newG = min(255, max(0, round($g * $factor)));
            $newB = min(255, max(0, round($b * $factor)));

            // Convertir de vuelta a HEX
            $result[$shade] = sprintf("#%02x%02x%02x", $newR, $newG, $newB);
        }

        return $result;
    }
} 