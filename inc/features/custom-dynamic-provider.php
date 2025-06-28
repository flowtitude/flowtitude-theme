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