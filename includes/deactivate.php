<?php
/**
 * @package CarabusPlugin
 */

class Deactivate {
    function __construct() {
    
        /* Do nothing here */
        
    }
    
    public static function deactivate() {
        if ( get_option( 'carabus_flush_rewrite_rules_flag' ) ) {
            delete_option( 'carabus_flush_rewrite_rules_flag' );
        } 
        
        flush_rewrite_rules();

        $users = get_transient('users_api');
        $users_transient = 'users_api';
        $data_timeout = get_option('_transient_timeout_' . $users_transient);
        if ($data_timeout > time())
            delete_transient($users_transient);
    }
}