<?php
namespace Flowtitude\Features;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Solo cargar cuando Bricks esté disponible
if ( ! class_exists( 'Bricks\Integrations\Dynamic_Data\Providers\Base' ) ) {
    return;
}

use Bricks\Integrations\Dynamic_Data\Providers\Base;

class Custom_Dynamic_Provider extends Base {
    /**
     * Constructor
     *
     * @param string $name Provider name.
     */
    public function __construct( $name = 'custom' ) {
        parent::__construct( $name );
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Custom_Dynamic_Provider initialized', 'info');
        }
    }

    /**
     * Register custom tags
     */
    public function register_tags() {
        $this->tags = [
            [
                'name' => 'custom_tag',
                'label' => __( 'Custom Tag', 'flowtitude' ),
                'group' => __( 'Custom', 'flowtitude' ),
                'provider' => 'custom',
            ],
        ];
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Custom tags registered', 'info');
        }
    }

    /**
     * Get the tag value based on the context
     *
     * @param string  $tag
     * @param WP_Post $post
     * @param array   $args
     * @param string  $context text, link, image, media.
     * @return array|string
     */
    public function get_tag_value( $tag, $post, $args, $context ) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Getting tag value for: ' . $tag, 'debug');
        }
        // Implement custom logic to return the tag value
        return 'Valor personalizado';
    }
}

// Solo registrar cuando Bricks esté renderizando una plantilla
add_action( 'bricks_before_render', function() {
    if ( class_exists( 'Bricks\Integrations\Dynamic_Data\Providers' ) ) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Registering custom provider for Bricks render', 'info');
        }
        Bricks\Integrations\Dynamic_Data\Providers::register( [ 'custom' ] );
    }
});

/**
 * Procesa etiquetas dinámicas de Bricks usando sus providers, incluso fuera del render nativo.
 */
class Flowtitude_Bricks_Dynamic_Resolver {
    protected static $custom_providers = [
        'Flowtitude_Provider_User',
        'Flowtitude_Provider_Site',
        'Flowtitude_Provider_ACF',
        // Añade aquí más providers personalizados
    ];

    public static function parse($content, $context = []) {
        // Cargar providers personalizados si no existen
        foreach (self::$custom_providers as $provider) {
            if (!class_exists($provider)) {
                $file = __DIR__ . '/../dynamic-providers/' . strtolower(str_replace('Flowtitude_Provider_', 'provider-', $provider)) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }
        return preg_replace_callback('/{([^}]+)}/', function($matches) use ($context) {
            $tag = $matches[1];
            foreach (Flowtitude_Bricks_Dynamic_Resolver::$custom_providers as $provider) {
                if (class_exists($provider) && method_exists($provider, 'get_tag_value')) {
                    $value = $provider::get_tag_value($tag, $context);
                    if ($value !== null) return $value;
                }
            }
            return $matches[0];
        }, $content);
    }
}

// === Ejemplo de uso ===
/*
$post = get_post(123);
$user = wp_get_current_user();
$html = '<p>Bienvenido, {user_email}. Tu campo ACF: {acf.mi_campo}. Jet: {jetengine.mi_campo}</p>';
echo Flowtitude_Bricks_Dynamic_Resolver::parse($html, ['user' => $user, 'post' => $post]);
*/ 