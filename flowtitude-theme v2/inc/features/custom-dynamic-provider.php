<?php
namespace Flowtitude\Features;

use Bricks\Integrations\Dynamic_Data\Providers\Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Custom_Dynamic_Provider extends Base {
    /**
     * Constructor
     *
     * @param string $name Provider name.
     */
    public function __construct( $name = 'custom' ) {
        parent::__construct( $name );
        error_log('Custom_Dynamic_Provider initialized');
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
        error_log('Custom tags registered');
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
        error_log('Getting tag value for: ' . $tag);
        // Implement custom logic to return the tag value
        return 'Valor personalizado';
    }
}

// Register the custom provider
add_action( 'after_setup_theme', function() {
    if ( class_exists( 'Bricks\Integrations\Dynamic_Data\Providers' ) ) {
        error_log('Registering custom provider');
        Bricks\Integrations\Dynamic_Data\Providers::register( [ 'custom' ] );
    } else {
        error_log('Bricks\Integrations\Dynamic_Data\Providers class not found');
    }
}); 