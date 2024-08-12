<?php 
if (class_exists('SIUL')):
    function ft_disable_plugin_updates( $value ) {
        if ( isset($value) && is_object($value) ) {
            unset( $value->response['yabe-siul/yabe-siul.php'] );
        }
        return $value;
    }
    add_filter( 'site_transient_update_plugins', 'ft_disable_plugin_updates' );
endif;